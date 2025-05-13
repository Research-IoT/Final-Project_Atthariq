<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $table = 'data';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_device',
        'data',
        'year',
        'month',
        'day',
        'time',
    ];

    protected $casts = [
        'data' => 'array',
        'time' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'id_device');
    }
}
