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
        Schema::create('light_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabinet_id')->constrained()->cascadeOnDelete();
            $table->string('point_code')->unique();
            $table->string('pole_id')->nullable();
            $table->string('external_device_id')->nullable()->unique(); 
            $table->string('line', 2)->nullable();
            $table->unsignedInteger('rated_power_w')->nullable();
            $table->string('lamp_type')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        
            $table->index(['cabinet_id']);  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('light_points');
    }
};
