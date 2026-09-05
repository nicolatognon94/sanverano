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
        Schema::create('current_states', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('light_point_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        
            $table->foreignId('cabinet_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        
            $table->string('status');
            $table->decimal('power_w', 10, 2)->nullable();
            $table->unsignedTinyInteger('dimming_percent')->nullable();
        
            $table->timestamp('last_seen_at')->nullable();
        
            $table->timestamps();
        
            $table->unique('light_point_id');
            $table->unique('cabinet_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('current_states');
    }
};
