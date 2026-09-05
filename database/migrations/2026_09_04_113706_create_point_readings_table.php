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
        Schema::create('point_readings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('light_point_id')
                ->constrained()
                ->cascadeOnDelete();
        
            $table->timestamp('measured_at');
            $table->timestamp('received_at');
        
            $table->unsignedBigInteger('sequence')->nullable();
        
            $table->decimal('voltage_v', 8, 2)->nullable();
            $table->decimal('current_a', 8, 3)->nullable();
            $table->decimal('power_w', 10, 2)->nullable();
            $table->decimal('power_factor', 5, 3)->nullable();
        
            $table->unsignedBigInteger('energy_wh')->nullable();
        
            $table->unsignedTinyInteger('dimming_percent')->nullable();
            $table->decimal('internal_temperature', 6, 2)->nullable();
        
            $table->string('relay_status')->nullable();
            $table->string('lamp_status')->nullable();
        
            $table->timestamps();
             $table->unique(['light_point_id', 'sequence']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_readings');
    }
};
