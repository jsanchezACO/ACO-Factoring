<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloqueados extends Model
{
    protected $table='bloqueados';

    protected $fillable=[
    'NIT',
    'Transaccion',
    'Documento',
    'estado',
    'CIA',
    'Procesar'];
}
