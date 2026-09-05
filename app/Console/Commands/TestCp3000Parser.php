<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Vendors\Cp3000Vendor;

class TestCp3000Parser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-cp3000-parser';

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
        $payload = 'CP3000;0044;20260903122043;L1:0.0,0.00,0.0;L2:0.0,0.00,0.0;L3:0.0,0.00,0.0;EN:346472;DI:0;AL:PF3';
        $vendor = new Cp3000Vendor();

        $result = $vendor->parse(
            'cp3000/0044/data',
            $payload
        );

        dd($result);
    }
}
