<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Cabinet;
use App\Models\LightPoint;


class ImportPlant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-plant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // qiua importo le anagrafiche di plants.csv dentro al db
        // carico il file plants.csv
        $plantsCsv = Storage::get('plant.csv');
        if($plantsCsv && strlen($plantsCsv)) {
            $rows = explode("\n", $plantsCsv);
            $i=0;
            foreach($rows as $row) {
                if($i>0 && strlen($row)) {
                    $data = explode(',', $row);
                    $cabinet = $this->createCabinet($data);
                    $this->createLightPoint($data, $cabinet);
                }
                $i++;
            }
            $this->info('Importazione completata');
        }
    }
    private function createCabinet(array $data): Cabinet {
        
        $cabinet = Cabinet::updateOrCreate(
            [
                'external_code' => $data[1],
            ],
            [
                'lot' => $data[0],
                'name' => $data[2],
            ]
        );

        
        return $cabinet;
    }

    private function createLightPoint(array $data,Cabinet $cabinet) {
        
        $lightPoint = LightPoint::updateOrCreate(
            [
                'point_code' => $data[4],
            ],
            [
                'cabinet_id' => $cabinet->id,
                'external_device_id' => $data[5] ?: null,
                'line' => $data[3],
                'rated_power_w' => (int) $data[6],
                'lamp_type' => $data[7],
                'lat' => (float) $data[8],
                'lng' => (float) $data[9],
                'pole_id' => $data[10],
            ]
        );
        
    }
}
