<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrentState;
use App\Models\Alarm;
use App\Models\Cabinet;
use App\Models\CabinetReading;
use App\Models\Command;
use Illuminate\Support\Str;
use App\Services\Commands\CommandSenderResolver;
use App\Models\LightPoint;
class CabinetController extends Controller{
    public function index(Request $request){
        $states = CurrentState::query()->get();

        // elenco quadri 
        $cabinets = Cabinet::with([
            'currentState',
            'lightPoints.currentState',
            'lightPoints.alarms',
        ])->where('lot','!=',"B")->get();
      
        $cabinetsReturnArray = [];
        foreach ($cabinets as $cabinet) {
    
            $currentStateArray = array();
            $alarmsCount = 0; 
            if($cabinet->lot == 'C' && $cabinet->currentState){
                $currentStateArray = [ 
                    'cabinet_id' => $cabinet->id,
                    'status' => $cabinet->currentState->status,
                    'power_w' => $cabinet->currentState->power_w,
                ];
                // controllo gli allarmi dei cabinet 
                $alarmsCount = Alarm::where('cabinet_id', $cabinet->id)->whereNull('resolved_at')->count();
            }
            else if($cabinet->lot == 'A'){
                $currentStateArray = $cabinet->lightPoints->pluck('currentState')->filter()->values();
                $total_power = 0;
                $statusArray = array();
                $lightPointAlarms = 0;
                foreach($currentStateArray as $state){

                    $total_power += $state->power_w;
                    $statusArray[] = $state->status;
                    $lightPointAlarms += $state->lightPoint->alarms->whereNull('resolved_at')->count();
                }
                $alarmsCount = $lightPointAlarms;
                $currentStateArray = [ 
                    'cabinet_id' => $cabinet->id,
                    'status' => in_array('fault', $statusArray) ? 'fault' : (in_array('offline', $statusArray) ? 'offline' : 'online'), // se sono tutti online allora il cambinet è online, se almeno uno è in fault allora metto fault altrimenti se almeno uno è offline metto offline
                    'power_w' => $total_power
                ];

                
            }
            $cabinetsReturnArray[] = [
                'id' => $cabinet->id,
                'external_code' => $cabinet->external_code,
                'lot' => $cabinet->lot,
                'name' => $cabinet->name,
                'dimmable' => $cabinet->dimmable,
                'lat' => $cabinet->lat,
                'lng' => $cabinet->lng,
                'currentState' => $currentStateArray,
                'alarms' => $alarmsCount,
            ];
        }
        return response()->json([
            'kpis' => [
                'online' => $states->where('status', 'online')->count(),
                'offline' => $states->where('status', 'offline')->count(),
                'total_power_w' => number_format($states->sum('power_w'), 2, '.', ''),
                'active_alarms' => Alarm::whereNull('resolved_at')->count(),
            ],
            'cabinets' => $cabinetsReturnArray,
        ]);
    } 
    public function show($id){
    
        $cabinet = Cabinet::where('id', $id)
        ->with([
            'currentState',
            'lightPoints.currentState',
            'lightPoints.alarms',
        ])
        ->first(); 
        if(!$cabinet)    return response()->json([
            'message' => 'Cabinet not found',
        ], 404);
        
        $anagraficaCabinet = [
            'code' => $cabinet->external_code,
            'name' => $cabinet->name,
            'lot' => $cabinet->lot,
            'dimmable' => $cabinet->dimmable,
            'lat' => $cabinet->lat,
            'lng' => $cabinet->lng,
        ];
        $cabinetStatusInfo = [];
        if($cabinet->lot == 'C' && $cabinet->currentState){
            $cabinetStatusInfo = [ 
                'status' => $cabinet->currentState->status,
                'power_w' => number_format($cabinet->currentState->power_w, 2, '.', ''),
                'last_seen_at' => $cabinet->currentState->last_seen_at,
            ];
            // controllo gli allarmi dei cabinet 
            $alarmsCount = Alarm::where('cabinet_id', $cabinet->id)->whereNull('resolved_at')->count();
            $cabinetStatusInfo['alarms'] = $alarmsCount;
        }
        else if($cabinet->lot == 'A'){
            $currentStateArray = $cabinet->lightPoints->pluck('currentState')->filter()->values();
        
            $total_power = 0;
            $statusArray = array();
            $lightPointAlarms = 0;
            foreach($currentStateArray as $state){
                $total_power += $state->power_w;
                $statusArray[] = $state->status;
                $lightPointAlarms += $state->lightPoint->alarms->whereNull('resolved_at')->count();
            } 
            $cabinetStatusInfo = [ 
                'status' => in_array('fault', $statusArray) ? 'fault' : (in_array('offline', $statusArray) ? 'offline' : 'online'), // se sono tutti online allora il cambinet è online, se almeno uno è in fault allora metto fault altrimenti se almeno uno è offline metto offline
                'power_w' => number_format($total_power, 2, '.', ''),
                'last_seen_at' => $currentStateArray->max('last_seen_at'),
            ];
            $cabinetStatusInfo['alarms'] = $lightPointAlarms;
        }
        $cabinetDetail = [
            'anagraficaCabinet' => $anagraficaCabinet,
            'cabinetStatusInfo' => $cabinetStatusInfo,
        ];
        return response()->json($cabinetDetail);
    }
    public function power($cabinetId){ 
        ini_set('memory_limit', '-1'); // da sistemare sta cosa 
        $cabinet = Cabinet::where('id', $cabinetId)->first();
        if(!$cabinet)    return response()->json([
            'message' => 'Cabinet not found',
        ], 404);

        $readings = array();
        if($cabinet->lot == 'C'){
            $readings = CabinetReading::where('cabinet_id', $cabinet->id)->get();

            $grouped = [];

            foreach($readings as $reading){ 
                $timestamp = $reading->measured_at->format('Y-m-d H:i:00');

                $grouped[$timestamp] = ($grouped[$timestamp] ?? 0) + $reading->power_w;
            }

            $readings = $grouped;
        }
        if($cabinet->lot == 'A'){
            foreach($cabinet->lightPoints as $lightPoint){
                foreach($lightPoint->pointReadings as $reading){
                    $timestamp = $reading->measured_at->format('Y-m-d H:i:00');
                    $readings[$timestamp] = ($readings[$timestamp] ?? 0) + $reading->power_w;
                }
            }
        }

        return response()->json($readings);
    }
    public function command(Request $request){
        
        $request->validate([
            'asset_type' => 'required|in:point,cabinet',
            'asset_id' => 'required|integer',
            'action' => 'required|in:on,off,dim',
            'target_value' => 'nullable|integer|min:0|max:100',
        ]);
        if ($request->asset_type === 'point') {
            $asset = LightPoint::findOrFail($request->asset_id);
            $lot = $asset->cabinet->lot;
        }
 
        $sender = app(CommandSenderResolver::class)->resolve($lot);
        $command = Command::create([
            'correlation_id' => Str::uuid(),
            'asset_type' => $request->asset_type,
            'asset_id' => $request->asset_id,
            'action' => $request->action,
            'target_value' => $request->target_value ?? null,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
        $sender->send($command->toArray());

        return response()->json($command, 201);
    }
}