<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consecutivos extends Model
{
    protected $table='V_Consecutivo';

    protected $fillable=[
        'Nit','Consecutivo','DB','Transaccion'
    ];
}
