<?php

namespace App\Services\Commands;

class Cp3000CommandSender implements CommandSenderInterface
{
    public function supports(string $lot): bool
    {
        return $lot === 'C';
    }

    public function send(array $command): void
    {
        // TODO: invio MQTT
    }
}