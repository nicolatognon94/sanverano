<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $fillable = [
        'cabinet_id',
        'light_point_id',
        'code',
        'severity',
        'message',
        'occurred_at',
        'resolved_at'
    ];

    protected $table = 'alarms';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

}
