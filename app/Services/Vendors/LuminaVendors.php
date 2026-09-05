<?php

namespace App\Services\Vendors;

use App\Contracts\VendorInterface;

class LuminaVendors implements VendorInterface
{

    public function parse(string $topic, string $payload): array
    {
        $data = json_decode($payload, true);
         
        return [
            'type' => 'point',
            'external_device_id' => $data['node'],
            'measured_at' => $data['ts'],
            'sequence' => $data['seq'] ?? null,

            'voltage_v' => $data['meas']['v'] ?? null,
            'current_a' => $data['meas']['i'] ?? null,
            'power_w' => $data['meas']['p'] ?? null,
            'power_factor' => $data['meas']['pf'] ?? null,
            'energy_wh' => $data['meas']['e_wh'] ?? null,
            'dimming_percent' => $data['meas']['dim'] ?? null,
            'internal_temperature' => $data['meas']['t_int'] ?? null,

            'relay_status' => $data['st']['relay'] ?? null,
            'lamp_status' => $data['st']['lamp'] ?? null,

            'errors' => $data['st']['err'] ?? [],
        ];
    }


    public function supports(string $topic): bool
    {   
        return str_starts_with($topic, 'lumina/v2/');
    }
}