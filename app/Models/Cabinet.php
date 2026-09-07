<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CurrentState;
class Cabinet extends Model
{
    protected $fillable = [
        'external_code',
        'lot',
        'name',
        'dimmable',
        'lat',
        'lng'
    ];

    protected $table = 'cabinets';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    public function currentState()
    {
        return $this->hasOne(CurrentState::class);
    }
    public function lightPoints()
    {
        return $this->hasMany(LightPoint::class);
    } 
}
