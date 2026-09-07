<?php

namespace App\Services\Commands;
use App\Models\LightPoint;
use PhpMqtt\Client\MqttClient;
class LuminaCommandSender implements CommandSenderInterface
{
    public function supports(string $lot): bool
    {
        return $lot === 'A';
    }

    public function send(array $command): void
    {
        if ($command['asset_type'] === 'point') {
            $lightPoint = LightPoint::find($command['asset_id']);
            $mqtt = new MqttClient(
                '127.0.0.1',
                1883,
                'sanverano-command-' . $command['id']
            );

            $mqtt->connect();

            $topic = 'lumina/v2/sanverano/' . $lightPoint->external_device_id . '/command';

            $payload = json_encode([
                'id' => (string) $command['correlation_id'],
                'op' => $command['action'],
                'value' => $command['target_value'],
                'ttl_s' => 30,
            ]);

            $mqtt->publish($topic, $payload, 0);

            $mqtt->disconnect();
        }
        else return;
    }
}