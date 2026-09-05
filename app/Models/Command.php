<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    protected $fillable = [
        'correlation_id',
        'asset_type',
        'asset_id',
        'action',
        'target_value',
        'status',
        'requested_at',
        'acked_at',
        'ack_payload'
    ];

    protected $table = 'commands';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

}
