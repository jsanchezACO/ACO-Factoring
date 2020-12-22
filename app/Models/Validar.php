<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Validar extends Model
{
    //
    protected $table='validar_facturas';

    protected $fillable=[
        'Idtransaccion'
        ,'NIT'
        ,'Total'
        ,'NoFacturas'
    ];
}
