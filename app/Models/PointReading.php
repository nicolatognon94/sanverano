<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointReading extends Model
{
    protected $fillable = [
        'light_point_id',
        'measured_at',
        'received_at',
        'sequence',
        'voltage_v',
        'current_a',
        'power_w',
        'power_factor',
        'energy_wh',
        'dimming_percent',
        'internal_temperature',
        'relay_status',
        'lamp_status'
    ];

    protected $table = 'point_readings';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

}
