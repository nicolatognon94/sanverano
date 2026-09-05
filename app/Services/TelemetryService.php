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
                return $this->storePoint($data); break;
            case 'cabinet':
                return $this->storeCabinet($data);break;
        }
    }
    public function storePoint(array $data) {
        $lightPoint = LightPoint::where('external_device_id', $data['external_device_id'])->first();

        if (!$lightPoint) {
            return;
        }
        $pointReadings = [
            'light_point_id' => $lightPoint->id,
            'measured_at' => Carbon::parse($data['measured_at'])->utc(),
            'received_at' => Carbon::parse(date('Y-m-d H:i:s'))->utc(),
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
                        'status'=>empty($res->errors) ? 'online' : 'fault',
                        'power_w'=>$res->power_w,
                        'dimming_percent'=>$res->dimming_percent,
                        'last_seen_at'=>Carbon::parse(date('Y-m-d H:i:s'))->utc()
                    ]
                );

                if(!empty($data['errors'])) {
                    Alarm::create([
                        'light_point_id' => $res->light_point_id,
                        'cabinet_id' => null,
                        'code' => $data['errors'],
                        'severity' => 'high',
                        'message'=>"Errore rilevato dal lampione " . $lightPoint->code_id.$data['errors'],
                        'occurred_at'=>Carbon::parse(date('Y-m-d H:i:s'))->utc(),
                        'resolved_at'=>null
                    ]);
                }
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
            // if($data['external_cabinet_code'] == "0044") dd($data);
            $cabinetReadingsArray = [];
            $totalPower = 0;
            if(isset($data['phases']) && is_array($data['phases'])) {
                $measuredAt = Carbon::createFromFormat(
                    'YmdHis',
                    $data['measured_at'],
                    'Europe/Rome'
                )->utc();
                foreach($data['phases'] as $line=>$phase){
                    $energyWh = $line == 'L1' ? $data['energy_wh'] : 0;
                    $cabinetReadingsArray = [
                        'cabinet_id' => $cabinet->id,
                        'line' => $line,
                        'measured_at' => $measuredAt,
                        'received_at' => now()->utc(),
                        'ingested_at' => now()->utc(),
                        'voltage_v' => $phase['voltage'],
                        'current_a' => $phase['current'],
                        'power_w' => $phase['power'],
                        'energy_wh' => $energyWh,
                        'door_open' => $data['door_open'] ?? 0,
                    ]; 
                    $totalPower += $phase['power'];
                    
                    $res = CabinetReading::create($cabinetReadingsArray);        
                } 
                $resCurrentState = CurrentState::updateOrCreate(
                    [
                        'cabinet_id' => $cabinet->id,
                    ],
                    [
                        'light_point_id' => null,
                        'status' => $data['errors'] ? 'fault' : 'online',
                        'power_w' => $totalPower,
                        'dimming_percent' => null,
                        'last_seen_at' => now()->utc(),
                    ]
                );

                // controllo se c'è un alarm per questo cabinet. Se c'è e ora vedo che non ci sono errori allora lo marko come resolved
                if (!empty($data['errors'])) {
                    $activeCodes = $data['errors'];

                    $openAlarms = Alarm::where('cabinet_id', $cabinet->id)
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

                        $alarmExists = Alarm::where('cabinet_id', $cabinet->id)
                            ->where('code', $code)
                            ->whereNull('resolved_at')
                            ->exists();

                        if (!$alarmExists) {
                            Alarm::create([
                                'cabinet_id' => $cabinet->id,
                                'code' => $code,
                                'severity' => 'critical',
                                'message' => 'Errore nel quadro elettrico',
                                'occurred_at' => $measuredAt,
                            ]);
                        }
                    }
                }
            } 
        } catch (\Exception $e) {
            \Log::error('Errore nel salvataggio del quadro: ' . $e->getMessage());
        }
    }
}
