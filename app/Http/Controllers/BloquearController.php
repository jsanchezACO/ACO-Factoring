<?php

namespace App\Http\Controllers;

use App\Models\Bloqueados;
use App\Models\Facturas;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BloquearController extends Controller
{
    public function store()
    {
        $Bloquear=request()->validate([
            'NIT'=>'required',
            'Transaccion'=>'required',
            'Documento'=>'required',
            'estado'=>'required',
            'CIA'=>'required'
        ]);

        $count=Facturas::where('Idtransaccion',request()->Transaccion)
        ->where('Documento',request()->Documento)->where('DB',request()->CIA)
        ->count();
        if ($count==1)
        {
            $Bloquear=Arr::add($Bloquear,'Procesar',0);
            Bloqueados::create($Bloquear);
            return response()->json([
                'respuesta'=>true,
                'message'=>'Datos Procesados'
            ],200);
        }elseif($count==0)
        {
            return response()->json([
                'respuesta'=>true,
                'message'=>'Factura no existe con estos parametros'
            ],200);
        }
        
    }
}
