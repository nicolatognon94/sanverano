<?php

namespace App\Services\Commands;

interface CommandSenderInterface
{
    public function supports(string $lot): bool;

    public function send(array $command): void;
}