<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facturas;
use App\Models\Validar;
USE Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function show(Request  $request)
    {
        //Ejecutar SP con las facturas
        $variables=[$request->NIT,$request->Inicial,$request->Final,$request->CIA];
        DB::INSERT("exec [ListarFactura_2] ?,?,?,?",$variables);

        //Listar Facturas
        return response()->json([
            'respuesta'=>true,
            'message'=>Facturas::select('NIT',
            'RazonSocial',
            'Prefijo',
            'Factura',
            'FechaFactura',
            'Vencimiento',
            'ValorFactura',
            'Impuesto',
            'Base',
            'Documento',
            'Idtransaccion',)->where('NIT',$request->NIT)
            ->wherebetween('FechaFactura',[$request->Inicial,$request->Final])
            ->where('Idtransaccion',Facturas::where('NIT',$request->NIT)->max('Idtransaccion'))
            ->get()
        ],200);
    }

    public function validacion(Request  $request)
    {
        //dd($request->all());
        $datos=Validar::select('NIT','Total','NoFacturas')->where('Idtransaccion',$request->Transaccion)->first();
        
        if(!is_null($datos) && $request->Total==$datos->Total && $request->Facturas==$datos->NoFacturas)
        {
            $valida='Validación Correcta';
            $Tipo='S';
        }
        else{
            $Tipo='E'; 
            $valida='error de validación';
        }
        return response()->json([
            'respuesta'=>$Tipo,
            'message'=>$valida
        ],200);
    }
    public function Bloquear(Request  $request)
    {
       return xml($request->Transaccion);
    }
}
