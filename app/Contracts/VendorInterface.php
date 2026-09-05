<?php
// intertfaccia che verrà usato da ogni vendor quando verranno implementati
namespace App\Contracts;

interface VendorInterface
{
    // questo mi servirà per prendere il messaggio che arriva e parsarlo 
    public function parse(string $topic, string $payload): array;
    // questo mi serve per capire se quel topic è supportato da quel vendor. il topic è il messaggio che arriva dal broker mqtt
    public function supports(string $topic): bool; 
}