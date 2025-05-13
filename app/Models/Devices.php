<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Mapping\ConsumenDevice;

class Devices extends Model
{
    protected $table = 'devices';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'serial_catalog',
        'serial_number',
        'token',
        'modified_by',
        'modified_at',
    ];

    protected $dates = [
        'modified_at',
    ];

    public function controller()
    {
        return $this->hasOne(Controller::class, 'id_device');
    }

    public function consumen_device()
    {
        return $this->hasMany(ConsumenDevice::class, 'id_device');
    }
}
