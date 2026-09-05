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
        Schema::create('cabinets_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained()->cascadeOnDelete();
            $table->string('line', 2);                      
            $table->timestampTz('measured_at');
            $table->timestampTz('received_at')->nullable(); 
            $table->decimal('voltage_v', 6, 2);
            $table->decimal('current_a', 6, 2);
            $table->decimal('power_w', 8, 2);
            $table->decimal('energy_wh', 10, 3)->default(0); 
            $table->boolean('door_open')->default(false);   
            $table->timestamp('ingested_at')->useCurrent();
            $table->timestamps();
            $table->index(['cabinet_id', 'line', 'measured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabinets_readings');
    }
};
