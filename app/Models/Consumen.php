<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Consumen extends Authenticatable
{
    use Notifiable;

    protected $table = 'consumen';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'username',
        'password',
        'address',
        'phone',
        'token',
        'expired_at',
    ];

    protected $hidden = [
        'password',
        'token',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function devices()
    {
        return $this->belongsToMany(Devices::class, 'mapping_consumen_device', 'id_consumen', 'id_device');
    }
}
