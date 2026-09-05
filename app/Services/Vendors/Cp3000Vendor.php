<?php

namespace App\Services\Vendors;

use App\Contracts\VendorInterface;

class Cp3000Vendor implements VendorInterface
{

    public function parse(string $topic, string $payload): array
    {
        //CP3000;0044;20260903122043;L1:0.0,0.00,0.0;L2:0.0,0.00,0.0;L3:0.0,0.00,0.0;EN:346472;DI:0;AL:PF3
        // esempio
        $data = explode(';', $payload);
        $phases = [];
        $energy_wh = 0;
        $door_open = 0;
        $alarm = [];
        foreach ($data as $part) {
            if(!str_contains($part, ':')) {
                continue;
            }
            [$key, $value] = explode(':', $part, 2);
            if ($key === 'L1' || $key === 'L2' || $key === 'L3') {
                $exolodedValues = explode(',', $value);
                $phases[$key] = [
                    'voltage' => floatval($exolodedValues[0]),
                    'current' => floatval($exolodedValues[1]),
                    'power' => floatval($exolodedValues[2]),
                ];
            }
            if ($key === 'EN') {
                $energy_wh = $value;
            }
            if ($key === 'DI') {
                $door_open = $value;
            }
            if ($key === 'AL' && !empty($value)) {
                $alarm = explode(',', $value);
            }
        }
        return [
            'type' => 'cabinet',
            'external_cabinet_code' => $data[1],
            'measured_at' => $data[2],
            'phases' => $phases,
            'energy_wh' => (int)$energy_wh,
            'door_open' => $door_open,
            'errors' => $alarm,
        ];
    }


    public function supports(string $topic): bool
    {   
        return str_starts_with($topic, 'cp3000/');
    }
}