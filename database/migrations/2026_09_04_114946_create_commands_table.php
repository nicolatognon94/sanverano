<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->id();
            $table->uuid('correlation_id')->unique(); 
            $table->string('asset_type', 20);
            $table->unsignedBigInteger('asset_id');
            $table->string('action', 10);
            $table->unsignedTinyInteger('target_value')->nullable(); 
            $table->string('status', 15)->default('pending');
            $table->timestampTz('requested_at');
            $table->timestampTz('acked_at')->nullable();
            $table->json('ack_payload')->nullable();
            $table->timestamps();
        
            $table->index(['asset_type', 'asset_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};
