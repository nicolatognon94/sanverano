<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LightPoint extends Model
{
    protected $fillable = [
        'cabinet_id',
        'point_code',
        'external_device_id',
        'line',
        'rated_power_w',
        'lamp_type',
        'lat',
        'lng',
        'pole_id'
    ];

    protected $table = 'light_points';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

}
