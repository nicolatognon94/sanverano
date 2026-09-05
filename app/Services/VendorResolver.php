<?php

namespace App\Services;

use App\Contracts\VendorInterface;
use RuntimeException;

class VendorResolver
{
    public function __construct(
        private iterable $vendors
    ) {}

    public function resolve(string $topic): VendorInterface
    { 
        foreach ($this->vendors as $vendor) {
            if ($vendor->supports($topic)) {
                return $vendor;
            }
        }

        throw new RuntimeException(
            "Nessun vendor trovato per il topic: {$topic}"
        );
    }
}