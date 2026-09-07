<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use App\Services\VendorResolver;
use App\Services\TelemetryService;

class MqttListen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mqtt-listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */

    public function __construct(
    private VendorResolver $resolver,
    private TelemetryService $telemetryService
) {
    parent::__construct();
    
}

    public function handle()
    {
         $mqtt = new MqttClient(
            '127.0.0.1',
            1883,
            'sanverano-listener'
        );
        $mqtt->connect();
        $callback = function (string $topic, string $message) {
             try {
                $vendor = $this->resolver->resolve($topic);
                $data = $vendor->parse($topic, $message);

                $this->telemetryService->store($data);

            } catch (\Throwable $e) {
                $this->error("Errore elaborando il messaggio MQTT:");
                $this->error("Topic: {$topic}");
                $this->error("Errore: {$e->getMessage()}");

            }
        };

        $this->info('Connesso a MQTT!');
        $mqtt->subscribe(
            'lumina/v2/sanverano/+/telemetry',
            $callback,
            0
        );
        
        $mqtt->subscribe(
            'cp3000/+/data',
            $callback,
            0
        );
        $mqtt->loop(true);
    }
}
