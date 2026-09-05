<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CurrentState;

class CheckOfflineAssets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-offline-assets';

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
        $minutes = 5; // 5 minuti da quando non ricevo info per considerare offline 
        // da schedulare. In sostanza "il solenzio è un dato" significa che se non ricevo info per più di X allora il dispositivo non è più online
        CurrentState::where('last_seen_at', '<', now()->subMinutes($minutes))->update(['status' => 'offline']);
    }
}
