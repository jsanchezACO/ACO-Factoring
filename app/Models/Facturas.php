<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facturas extends Model
{
    //
    protected $table='listar_facturas';

    protected $fillable=[
    'Idtransaccion',
    'NIT',
    'RazonSocial',
    'Cuenta',
    'CO',
    'Sucursal',
    'Prefijo',
    'Factura',
    'FechaFactura',
    'Vencimiento',
    'PP',
    'ValorFactura',
    'Impuesto',
    'Base',
    'Documento',
    'rowid_sa',
    'DB'
    ];      
}
