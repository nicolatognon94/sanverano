<?php

namespace App\Services\Commands;

use RuntimeException;

class CommandSenderResolver
{
    public function __construct(
        private iterable $senders
    ) {}

    public function resolve(string $lot): CommandSenderInterface
    {
        foreach ($this->senders as $sender) {
            if ($sender->supports($lot)) {
                return $sender;
            }
        }

        throw new RuntimeException(
            "Nessun command sender trovato per il lotto: {$lot}"
        );
    }
}