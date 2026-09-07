<?php

namespace App\Services;

use App\Models\LightPoint;
use App\Models\Telemetry;
use App\Models\PointReading;
use App\Models\Cabinet;
use App\Models\CurrentState;
use App\Models\CabinetReading;
use App\Models\Alarm;
use Carbon\Carbon;
class TelemetryService {

    public function store(array $data) {

        switch ($data['type']) {
            case 'point':
                return $this->storePoint($data);
            case 'cabinet':
                return $this->storeCabinet($data);
        }
    }
    public function storePoint(array $data) {
        $lightPoint = LightPoint::where('external_device_id', $data['external_device_id'])->first();
        $measuredAt = Carbon::parse($data['measured_at'])->utc();
        $receivedAt = now()->utc();
        if (!$lightPoint) {
            return;
        }
        $pointReadings = [
            'light_point_id' => $lightPoint->id,
            'measured_at' => $measuredAt,
            'received_at' => $receivedAt,
            'sequence' => $data['sequence'],
            'voltage_v' => $data['voltage_v'],
            'current_a' => $data['current_a'],
            'power_w' => $data['power_w'],
            'power_factor' => $data['power_factor'],
            'energy_wh' => $data['energy_wh'],
            'dimming_percent' => $data['dimming_percent'],
            'internal_temperature' => $data['internal_temperature'],
            'relay_status' => $data['relay_status'],
            'lamp_status' => $data['lamp_status'],
        ];

        try {
            $res = PointReading::create($pointReadings);
            // una volta creato il punto di lettura, devo aggiornare il current_state 
            // ma devo tenere solo l'ultima riga per ogni lampione
            if($res){     
                $resCurrentState = CurrentState::updateOrCreate(
                    [
                        'light_point_id' => $res->light_point_id,
                    ],
                    [
                        'cabinet_id' => null,
                        'status'=>empty($data['errors']) ? 'online' : 'fault',
                        'power_w'=>$res->power_w,
                        'dimming_percent'=>$res->dimming_percent,
                        'last_seen_at'=>$receivedAt
                    ]
                );

                $this->handleAlarmsLightPoint($data['errors'] ?? [], $res->light_point_id, $measuredAt);
                
            }
        } catch (\Exception $e) {
        }
    }
    public function storeCabinet(array $data) {
        try {
            $cabinet = Cabinet::where('external_code', $data['external_cabinet_code'])->first();
            if (!$cabinet) {
                return;
            } 
            $totalPower = 0;
            if(isset($data['phases']) && is_array($data['phases'])) {
                $measuredAt = Carbon::createFromFormat(
                    'YmdHis',
                    $data['measured_at'],
                    'Europe/Rome'
                )->utc();
                $receivedAt = now()->utc();
                foreach($data['phases'] as $line=>$phase){
                    $energyWh = $line == 'L1' ? $data['energy_wh'] : 0;
                    $totalPower += $phase['power'];  
                    $delta = null;
                    if ($line === 'L1') {
                        $lastEnergyWh = $this->getLastEnergyWh($cabinet->id, $line);

                        if ($lastEnergyWh !== null) {
                            $delta = $energyWh - $lastEnergyWh;
                        }
                    }
                    try {
                    CabinetReading::create([
                        'cabinet_id' => $cabinet->id,
                        'line' => $line,
                        'measured_at' => $measuredAt,
                        'received_at' => $receivedAt,
                        'ingested_at' => $receivedAt,
                        'voltage_v' => $phase['voltage'],
                        'current_a' => $phase['current'],
                        'power_w' => $phase['power'],
                        'energy_wh' => $energyWh,
                        'energy_delta_wh' => $delta,
                        'door_open' => $data['door_open'] ?? 0,
                    ]);  
                    } catch (\Exception $e) {
                        dd($e->getMessage());
                        continue;
                    }
                } 
                $resCurrentState = CurrentState::updateOrCreate(
                    [
                        'cabinet_id' => $cabinet->id,
                    ],
                    [
                        'light_point_id' => null,
                        'status' => !empty($data['errors']) ? 'fault' : 'online',
                        'power_w' => $totalPower,
                        'dimming_percent' => null,
                        'last_seen_at' => $receivedAt,
                    ]
                );
                $this->handleAlarmsCabinet($data['errors'] ?? [], $cabinet->id, $measuredAt); 
            } 
        } catch (\Exception $e) {
            \Log::error('Errore nel salvataggio del quadro: ' . $e->getMessage());
        }
    }
    private function handleAlarmsCabinet( array $activeCodes=[], $cabinetId, $measuredAt) {
        $openAlarms = Alarm::where('cabinet_id', $cabinetId)
            ->whereNull('resolved_at')
            ->get();

        foreach ($openAlarms as $alarm) {
            if (!in_array($alarm->code, $activeCodes)) {
                $alarm->update([
                    'resolved_at' => $measuredAt,
                ]);
            }
        }

        foreach ($activeCodes as $code) {

            $alarmExists = Alarm::where('cabinet_id', $cabinetId)
                ->where('code', $code)
                ->whereNull('resolved_at')
                ->exists();

            if (!$alarmExists) {
                Alarm::create([
                    'cabinet_id' => $cabinetId,
                    'code' => $code,
                    'severity' => 'critical',
                    'message' => 'Errore nel quadro elettrico',
                    'occurred_at' => $measuredAt,
                ]);
            }
        }
    }
    private function handleAlarmsLightPoint( array $errors=[], $lightPointId, $measuredAt) {
        $openAlarms = Alarm::where('light_point_id', $lightPointId)
            ->whereNull('resolved_at')
            ->get();

        foreach ($openAlarms as $alarm) {
            if (!in_array($alarm->code, $errors)) {
                $alarm->update([
                    'resolved_at' => $measuredAt,
                ]);
            }
        }

        foreach ($errors as $code) {
            $alarmExists = Alarm::where('light_point_id', $lightPointId)
                ->where('code', $code)
                ->whereNull('resolved_at')
                ->exists();

            if (!$alarmExists) {
                Alarm::create([
                    'light_point_id' => $lightPointId,
                    'cabinet_id' => null,
                    'code' => $code,
                    'severity' => 'high',
                    'message' => "Errore rilevato dal lampione {$lightPointId}: {$code}",
                    'occurred_at' => $measuredAt,
                ]);
            }
        }
    }
    private function getLastEnergyWh($cabinetId, $line) {
        $lastReading = CabinetReading::where('cabinet_id', $cabinetId)
            ->where('line', $line)
            ->orderBy('measured_at', 'desc')
            ->first();

        return $lastReading ? $lastReading->energy_wh : null;
    }
}
