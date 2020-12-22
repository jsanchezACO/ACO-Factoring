<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planos extends Model
{
    //
    protected $table='planos';

    protected $fillable=[
        'NIT' ,
        'Transaccion' ,
        'estado' ,
        'CIA',
        'Consecutivo',
        'XMLs',
        'respuesta'
    ];
}
