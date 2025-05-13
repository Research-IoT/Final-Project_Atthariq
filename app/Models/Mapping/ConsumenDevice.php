<?php

namespace App\Models\Mapping;

use Illuminate\Database\Eloquent\Model;

use App\Models\Devices;
use App\Models\Consumen;

class ConsumenDevice extends Model
{
    protected $table = 'mapping_consumen_device';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_consumen',
        'id_device',
        'added_at',
    ];

    protected $dates = [
        'added_at',
    ];

    public function consumen()
    {
        return $this->belongsTo(Consumen::class, 'id_consumen');
    }

    public function device()
    {
        return $this->belongsTo(Devices::class, 'id_device');
    }
}
