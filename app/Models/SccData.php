<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SccData extends Model
{
    protected $table = 'scc_data';

    protected $fillable = [
        'vpv',
        'ipv',
        'vbat',
        'ibat',
        'soc',
        'duty_cycle',
        'fase',
        'label_e',
        'label_de',
        'load_name',
        'load_status',
        'load_power',
        'load_current',
        'net_power',
        'load_reason',
    ];

    protected $casts = [
        'vpv'        => 'float',
        'ipv'        => 'float',
        'vbat'       => 'float',
        'ibat'       => 'float',
        'soc'        => 'float',
        'duty_cycle' => 'float',
        'load_power' => 'float',
        'load_current' => 'float',
        'net_power' => 'float',
    ];
}
