<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CabinetReading extends Model
{

    protected $fillable = [
        'cabinet_id',
        'line',
        'measured_at',
        'received_at',
        'voltage_v',
        'current_a',
        'power_w',
        'energy_wh',
        'energy_delta_wh',
        'door_open',
        'ingested_at'
    ];

    protected $table = 'cabinets_readings';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $casts = [
        'measured_at' => 'datetime',
    ];
}
