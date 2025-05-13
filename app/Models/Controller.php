<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Controller extends Model
{
    protected $table = 'controller';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_device',
        'controller',
        'modified_at',
    ];

    protected $casts = [
        'controller' => 'array',
        'modified_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(Devices::class, 'id_device');
    }
}
