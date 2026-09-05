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
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('cabinet_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        
            $table->foreignId('light_point_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        
            $table->string('code');
            $table->string('severity')->default('warning');
            $table->text('message')->nullable();
        
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable();
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alarms');
    }
};
