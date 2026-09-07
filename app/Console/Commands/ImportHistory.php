<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vendors\Cp3000Vendor;
use App\Models\Cabinet;
use App\Models\CabinetReading;
use Carbon\Carbon;
class ImportHistory extends Command
{
    protected $signature = 'app:import-history {file}';

    protected $description = 'Importa lo storico del simulatore';
    public function __construct(
        private Cp3000Vendor $vendor
    ) {
        parent::__construct();
    }
    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File non trovato: {$file}");
            return self::FAILURE;
        }

        $handle = fopen($file, 'r');

        $count = 0; 
        while (($line = fgets($handle)) !== false) {
            $record = json_decode($line, true);

            if (!$record) {
                continue;
            }

            if ($record['lot'] !== 'C') {
                continue;
            }
            $data = $this->vendor->parse(
                $record['topic'],
                $record['payload']
            );
            $cabinet = Cabinet::where(
                'external_code',
                $data['external_cabinet_code']
            )->first();

            if (!$cabinet) {
                $this->error(
                    "Cabinet non trovato: {$data['external_cabinet_code']}"
                );

                continue;
            }
            foreach ($data['phases'] as $line => $phase) {
                CabinetReading::updateOrCreate(
                    [
                        'cabinet_id' => $cabinet->id,
                        'line' => $line,
                        'measured_at' => Carbon::createFromFormat(
                            'YmdHis',
                            $data['measured_at'],
                            'Europe/Rome'
                        )->utc(),
                    ],
                    [
                        'received_at' => Carbon::parse($record['received_at'])->utc(),
                        'voltage_v' => $phase['voltage'],
                        'current_a' => $phase['current'],
                        'power_w' => $phase['power'],
                        'energy_wh' => $line === 'L1' ? $data['energy_wh'] : 0,
                        'energy_delta_wh' => null,
                        'door_open' => $data['door_open'] ?? false,
                    ]
                );
            }
            $count++;
 
        }

        fclose($handle);

        $this->info("Record Lot C trovati: {$count}");

        return self::SUCCESS;
    }
}