<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrentState extends Model
{
    protected $fillable = [
        'light_point_id',
        'cabinet_id',
        'status',
        'power_w',
        'dimming_percent',
        'last_seen_at',
    ];

    protected $table = 'current_states';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    public function lightPoint()
    {
        return $this->belongsTo(LightPoint::class);
    }
   
}
