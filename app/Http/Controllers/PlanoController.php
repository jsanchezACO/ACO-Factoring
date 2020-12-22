<?php

namespace App\Http\Controllers;

use App\Models\Bloqueados;
use App\Models\Facturas;
use App\Models\Planos;
use App\Models\Consecutivos;
use Illuminate\Support\Facades\DB;
use SoapFault;

class PlanoController extends Controller
{
    //    
    public function Plano()
    {
        try {

            $Bloqueados = Bloqueados::select('NIT', 'Transaccion', 'estado', 'CIA')->where('Procesar', 0)
                ->groupByRaw('NIT,Transaccion,CIA,estado')->get();

            foreach ($Bloqueados as $Bloquear) {
                //Instancia a concultar
                switch ($Bloquear->CIA) {
                    case 'AIIR':
                        $cia = 'CorralC';
                        $db = 'database.Corral';
                        break;
                    case 'AILS':
                        $cia = 'LenoslC';
                        $db = 'database.Lenos';
                        break;
                    case 'AIPJ':
                        $cia = 'PapaC';
                        $db = 'database.PJ';
                        break;
                }

                //Encabezado de xml
                $xml = "<?xml version='1.0' encoding='utf-8'?>\r\n" .
                    "<Importar>\r\n" .
                    "<NombreConexion>$cia</NombreConexion>\r\n" .
                    "<IdCia>1</IdCia>\r\n" .
                    "<Usuario>jsanchez</Usuario>\r\n" .
                    "<Clave>#E420lenovo</Clave>\r\n" .
                    "<Datos>\r\n";

                //Declaración de constantes Documento
                $F_NUMERO_REG = 0;
                $F_TIPO_REG = $this->completaCampo(350, 4, 'NUM'); //Tipo de registro;
                $F_SUBTIPO_REG = $this->completaCampo(0, 2, 'NUM'); //Subtipo de registro
                $F_VERSION_REG = $this->completaCampo(5, 2, 'NUM'); //Version del tipo de registro
                $F_CIA = $this->completaCampo(1, 3, 'NUM'); //Version del tipo de registro
                $F_CONSEC_AUTO_REG = $this->completaCampo(1, 1, 'NUM'); //Indica si el número consecutivo de docto es manual(0) o automático(1)
                $ID_CO = $this->completaCampo(1, 3, 'NUM'); //Centro de operación del documento
                $ID_TIPO_DOCTO = $this->completaCampo('RFF', 3, 'ALFA'); //Tipo de documento;
                $CONSEC_DOCTO = $this->completaCampo(1, 8, 'NUM'); //Consecutivo de documento
                $Fecha = date('yymd');
                $ID_TERCERO = $this->completaCampo($Bloquear->NIT, 15, 'ALFA'); //Tercero del documento
                $ID_CLASE_DOCTO = $this->completaCampo(30, 5, 'NUM');
                $IND_ESTADO = $Bloquear->estado; //1 para aprobado, 2 para anular
                $IND_IMPRESION = 0; //siempre debe ir en 0
                $NOTAS = $this->completaCampo($Bloquear->Transaccion, 255, 'ALFA'); //Observaciones del documento            
                $ID_MANDATO = $this->completaCampo('', 15, 'ALFA'); //Mandato vacio no es obligatorio
                $RECALCULAR_RX = 0; //Indica si se recalcula la re-expresión del documento con el tipo de cambio enviado
                $ID_TIPO_CAMBIO_RX = $this->completaCampo('', 3, 'ALFA'); //
                $TASA_CONV_RX = $this->formato(1, 8);    //Tasa de conversión Reexpresion
                $TASA_LOCAL_RX = $this->formato(1, 8);    //Tasa local Reexpresion
                $ID_CO_RP = $this->completaCampo('', 3, 'ALFA'); //
                $ID_TIPO_DOCTO_RP = $this->completaCampo('', 3, 'ALFA'); //
                $CONSEC_DOCTO_RP = $this->completaCampo(0, 8, 'NUM'); //            

                //Construcción Primera Linea
                $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                $linea = $F_NUMERO_REG . "00000001" . $F_CIA;
                $xml .= "<Linea>" . $linea . "</Linea>\r\n";

                if ($Bloquear->estado == 2) {
                    $Consecutivo = Consecutivos::select('Consecutivo')->where('Nit', $Bloquear->NIT)
                        ->where('Transaccion', $Bloquear->Transaccion)->where('DB', $Bloquear->CIA)->first();

                    $CONSEC_DOCTO = $this->completaCampo($Consecutivo->Consecutivo, 8, 'NUM'); //Consecutivo de documento
                    $ID_MOTIVO_OTRO = $this->completaCampo('003', 20, 'ALFA'); //Vacio si no es anulación
                    //Construcción Linea Documento
                    $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                    $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $F_CONSEC_AUTO_REG . $ID_CO . $ID_TIPO_DOCTO . $CONSEC_DOCTO . $Fecha . $ID_TERCERO . $ID_CLASE_DOCTO . $IND_ESTADO .
                        $IND_IMPRESION . $NOTAS . $ID_MANDATO . $RECALCULAR_RX . $ID_TIPO_CAMBIO_RX . $TASA_CONV_RX . $TASA_LOCAL_RX . $ID_CO_RP . $ID_TIPO_DOCTO_RP . $CONSEC_DOCTO_RP . $ID_MOTIVO_OTRO;
                    $xml .= "<Linea>" . $linea . "</Linea>\r\n";
                    //Actualizar Registro
                    Bloqueados::where('Transaccion', $Bloquear->Transaccion)
                        ->where('estado', 2)->update(['Procesar' => 1]);
                } else {
                    $ID_MOTIVO_OTRO = $this->completaCampo("", 20, 'ALFA'); //Vacio si no es anulación
                    //Construcción Linea Documento
                    $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                    $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $F_CONSEC_AUTO_REG . $ID_CO . $ID_TIPO_DOCTO . $CONSEC_DOCTO . $Fecha . $ID_TERCERO . $ID_CLASE_DOCTO . $IND_ESTADO .
                        $IND_IMPRESION . $NOTAS . $ID_MANDATO . $RECALCULAR_RX . $ID_TIPO_CAMBIO_RX . $TASA_CONV_RX . $TASA_LOCAL_RX . $ID_CO_RP . $ID_TIPO_DOCTO_RP . $CONSEC_DOCTO_RP . $ID_MOTIVO_OTRO;
                    $xml .= "<Linea>" . $linea . "</Linea>\r\n";

                    //Construcción Linea Movimiento Credito
                    $F_TIPO_REG = $this->completaCampo(351, 4, 'NUM'); //Tipo de registro;
                    $F_SUBTIPO_REG = $this->completaCampo(0, 2, 'NUM'); //Subtipo de registro
                    $CONSEC_DOCTO = $this->completaCampo(1, 8, 'NUM'); //Consecutivo de documento
                    $F_VERSION_REG = $this->completaCampo(5, 2, 'NUM'); //Version del tipo de registro
                    $ID_TERCERO = $this->completaCampo('', 15, 'ALFA'); //
                    $ID_UN = $this->completaCampo('01', 20, 'ALFA'); //Unidad de negocio
                    $ID_CCOSTO = $this->completaCampo('', 15, 'ALFA'); //Auxiliar de centro de costos
                    $ID_FE = $this->completaCampo('', 10, 'ALFA'); //Auxiliar de concepto de fuljo de efectivo
                    $VALOR_DB = $this->formatoSigno(0, 15);    //Valor debito se deja en cero
                    $VALOR_DB_ALT = $this->formatoSigno(0, 15);    //Valor debito alterno
                    $VALOR_CR_ALT = $this->formatoSigno(0, 15);    //Valor crédito alterno            
                    $VALOR_DB2 = $this->formatoSigno(0, 15);    //Valor debito
                    $VALOR_DB_ALT2 = $this->formatoSigno(0, 15);    //Valor debito alterno
                    $VALOR_CR_ALT2 = $this->formatoSigno(0, 15);    //Valor crédito alterno
                    $VALOR_DB3 = $this->formatoSigno(0, 15);    //Valor debito
                    $VALOR_DB_ALT3 = $this->formatoSigno(0, 15);    //Valor debito alterno
                    $VALOR_CR_ALT3 = $this->formatoSigno(0, 15);    //Valor crédito alterno
                    $DOCTO_BANCO = $this->completaCampo('', 2, 'ALFA'); //Tipo de documento de banco
                    $NRO_DOCTO_BANCO = $this->completaCampo(0, 8, 'NUM'); //Número de documento de banco
                    $NRO_ALT_DOCTO_BANCO  = $this->completaCampo('', 30, 'ALFA'); //Número docto alterno banco
                    $ID_RUBRO = $this->completaCampo('', 20, 'ALFA'); //Rubro presupuestal
                    $NOTAS = $this->completaCampo('Documento generado por plataforma factoring', 255, 'ALFA'); //Observaciones del documento                        

                    $Facturas = Facturas::select('ValorFactura', 'CO', 'Impuesto', 'Base', 'bloqueados.Documento')
                        ->join('bloqueados', 'listar_facturas.Documento', 'bloqueados.Documento')
                        ->where('Idtransaccion', $Bloquear->Transaccion)->get();

                    foreach ($Facturas as $factura) {

                        $Documento = $factura->Documento;

                        $cuenta = DB::connection(config($db))->select('SELECT SUBSTRING([f253_id],1,2) AS Cuenta FROM [t351_co_mov_docto] INNER JOIN [t253_co_auxiliares] ON [f253_rowid]=[f351_rowid_auxiliar] WHERE [f351_rowid]=?', [$Documento]);

                        $ID_AUXILIAR_M = $cuenta[0]->Cuenta . '0510';
                        $ID_CO_MOV = $this->completaCampo($factura->CO, 3, 'NUM'); //Centro de operación del movimiento
                        $ID_AUXILIAR = $this->completaCampo($ID_AUXILIAR_M, 20, 'ALFA'); //cuenta contable con la que se realizara el movimiento
                        $VALOR_CR = $this->formatoSigno($factura->ValorFactura, 15);    //Valor de factura a pagar
                        $VALOR_CR2 = $this->formatoSigno($factura->ValorFactura, 15);    //Valor de factura a pagar
                        $VALOR_CR3 = $this->formatoSigno($factura->ValorFactura, 15);    //Valor de factura a pagar
                        $BASE_GRAVABLE = $this->formatoSigno($factura->Base, 15);    //Valor base gravable
                        $BASE_GRAVABLE2 = $this->formatoSigno($factura->Base, 15);    //Valor base gravable
                        $BASE_GRAVABLE3 = $this->formatoSigno($factura->Base, 15);    //Valor base gravable
                        $VALOR_IMP_ASUM = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido
                        $VALOR_IMP_ASUM2 = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido
                        $VALOR_IMP_ASUM3 = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido

                        $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                        $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $ID_CO . $ID_TIPO_DOCTO . $CONSEC_DOCTO . $ID_AUXILIAR .
                            $ID_TERCERO . $ID_CO_MOV . $ID_UN . $ID_CCOSTO . $ID_FE . $VALOR_DB . $VALOR_CR . $VALOR_DB_ALT . $VALOR_CR_ALT . $BASE_GRAVABLE . $VALOR_DB2 .
                            $VALOR_CR2 . $VALOR_DB_ALT2 . $VALOR_CR_ALT2 . $BASE_GRAVABLE2 . $VALOR_DB3 . $VALOR_CR3 . $VALOR_DB_ALT3 . $VALOR_CR_ALT3 . $BASE_GRAVABLE3 .
                            $VALOR_IMP_ASUM . $VALOR_IMP_ASUM2 . $VALOR_IMP_ASUM3 . $DOCTO_BANCO . $NRO_DOCTO_BANCO . $NRO_ALT_DOCTO_BANCO . $ID_RUBRO . $NOTAS;
                        $xml .= "<Linea>" . $linea . "</Linea>\r\n";
                    }

                    //Construcción Linea Cuenta x Pagar
                    $F_TIPO_REG = $this->completaCampo(351, 4, 'NUM'); //Tipo de registro;
                    $F_SUBTIPO_REG = $this->completaCampo(2, 2, 'NUM'); //Subtipo de registro
                    $F_VERSION_REG = $this->completaCampo(6, 2, 'NUM'); //Version del tipo de registro
                    $CONSEC_DOCTO = $this->completaCampo(1, 8, 'NUM'); //Consecutivo de documento                
                    $ID_TERCERO = $this->completaCampo($Bloquear->NIT, 15, 'ALFA'); //
                    $ID_UN = $this->completaCampo('01', 20, 'ALFA'); //Unidad de negocio
                    $ID_CCOSTO = $this->completaCampo('', 15, 'ALFA'); //Auxiliar de centro de costos
                    $VALOR_CR = $this->formatoSigno(0, 15);    //Valor de factura a pagar
                    $VALOR_CR2 = $this->formatoSigno(0, 15);    //Valor de factura a pagar              
                    $VALOR_DB_ALT = $this->formatoSigno(0, 15);    //Valor debito alterno
                    $VALOR_CR_ALT = $this->formatoSigno(0, 15);    //Valor crédito alterno 
                    $VALOR_DB_ALT2 = $this->formatoSigno(0, 15);    //Valor debito alterno
                    $VALOR_CR_ALT2 = $this->formatoSigno(0, 15);    //Valor crédito alterno               
                    $NRO_CUOTA_CRUCE = $this->completaCampo(0, 3, 'NUM'); //Numero de cuota de documento de cruce
                    $ID_FE = $this->completaCampo(1202, 10, 'ALFA'); //Auxiliar de concepto de fuljo de efectivo                    
                    $VLR_DSCTO_PP = $this->formatoSigno(0, 15); //
                    $VALOR_APLICADO_PP = $this->formatoSigno(0, 15); //
                    $VALOR_APLICADO_PP_ALT = $this->formatoSigno(0, 15); //
                    $VALOR_RETENCION = $this->formatoSigno(0, 15); //
                    $VALOR_RETENCION_ALT = $this->formatoSigno(0, 15); //
                    $FECHA_RADICACION = $this->completaCampo($Fecha, 8, 'ALFA'); //
                    $NOTAS = $this->completaCampo('Documento generado por plataforma factoring, Transaccion: ' . $Bloquear->Transaccion, 255, 'ALFA'); //Observaciones del documento                        

                    $Facturas = Facturas::select('bloqueados.id', 'ValorFactura', 'Sucursal', 'CO', 'Prefijo',  'Factura', 'FechaFactura', 'Vencimiento', 'PP', 'Impuesto', 'Base', 'bloqueados.Documento')
                        ->join('bloqueados', 'listar_facturas.Documento', 'bloqueados.Documento')
                        ->where('Idtransaccion', $Bloquear->Transaccion)->get();
                    foreach ($Facturas as $factura) {
                        $Documento = $factura->Documento;
                        $cuenta = DB::connection(config($db))->select('SELECT SUBSTRING([f253_id],1,2) as Cuenta FROM [t351_co_mov_docto] INNER JOIN [t253_co_auxiliares] ON [f253_rowid]=[f351_rowid_auxiliar] WHERE [f351_rowid]=?', [$Documento]);

                        $ID_AUXILIAR_P = $cuenta[0]->Cuenta . '0505';
                        $ID_CO_MOV = $this->completaCampo($factura->CO, 3, 'NUM'); //Centro de operación del movimiento
                        $ID_AUXILIAR = $this->completaCampo($ID_AUXILIAR_P, 20, 'ALFA'); //cuenta contable con la que se realizara el movimiento
                        $ID_SUCURSAL = $this->completaCampo($factura->Sucursal, 3, 'ALFA'); //Sucursal proveedor
                        $PREFIJO_CRUCE = $this->completaCampo($factura->Prefijo, 20, 'ALFA'); //Prefijo de documento de cruce
                        $CONSEC_DOCTO_CRUCE = $this->completaCampo($factura->Factura, 8, 'NUM'); //Número de documento de cruce
                        $FECHA_VCTO = $this->completaCampo($factura->Vencimiento, 8, 'ALFA'); //
                        $FECHA_DOCTO_CRUCE = $this->completaCampo($factura->FechaFactura, 8, 'ALFA'); //
                        $FECHA_DSCTO_PP = $this->completaCampo($factura->PP, 8, 'ALFA'); //       
                        $VALOR_DB = $this->formatoSigno($factura->ValorFactura, 15);    //Valor debito se deja en cero
                        $VALOR_DB2 = $this->formatoSigno($factura->ValorFactura, 15);    //Valor debito                
                        $VALOR_IMP_ASUM = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido
                        $VALOR_IMP_ASUM2 = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido

                        $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                        $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $ID_CO . $ID_TIPO_DOCTO .
                            $CONSEC_DOCTO . $ID_AUXILIAR . $ID_TERCERO . $ID_CO_MOV . $ID_UN . $ID_CCOSTO . $VALOR_DB . $VALOR_CR . $VALOR_DB_ALT .
                            $VALOR_CR_ALT . $VALOR_DB2 . $VALOR_CR2 . $VALOR_DB_ALT2 . $VALOR_CR_ALT2 . $VALOR_IMP_ASUM . $VALOR_IMP_ASUM2 . $NOTAS .
                            $ID_SUCURSAL . $PREFIJO_CRUCE . $CONSEC_DOCTO_CRUCE . $NRO_CUOTA_CRUCE . $ID_FE . $FECHA_VCTO . $FECHA_DSCTO_PP .
                            $FECHA_DOCTO_CRUCE . $VLR_DSCTO_PP . $VALOR_APLICADO_PP . $VALOR_APLICADO_PP_ALT . $VALOR_RETENCION . $VALOR_RETENCION_ALT .
                            $FECHA_RADICACION . $NOTAS;
                        $xml .= "<Linea>" . $linea . "</Linea>\r\n";

                        //Actualizar Registro
                        Bloqueados::where('id', $factura->id)->update(['Procesar' => 1]);
                    }
                }
                //Linea Final
                $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                $linea = $F_NUMERO_REG . "99990001" . $F_CIA;
                $xml .= "<Linea>" . $linea . "</Linea>\r\n";
                $xml .= "</Datos>\r\n" . "</Importar>";

                Planos::create([
                    'NIT' => $Bloquear->NIT,
                    'Transaccion' => $Bloquear->Transaccion,
                    'estado' => $Bloquear->estado,
                    'CIA' => $Bloquear->CIA,
                    'Consecutivo' => $CONSEC_DOCTO,
                    'XMLs' => $xml
                ]);

                return response()->json([
                    'respuesta' => true,
                    'message' => 'Ejecutado correctamente'
                ], 200);
            }
        } catch (SoapFault $fault) {
            echo $fault;
        }
    }

    public function completaCampo($valor, $tam, $tipo)
    {
        $conteo = strlen($valor);
        if ($conteo > $tam) {
            $valor = substr($valor, 0, $tam);
        }
        if ($tipo == "ALFA") {
            while ($conteo < $tam) {
                $valor = $valor . " ";
                $conteo++;
            }
        } else {
            while ($conteo < $tam) {
                $valor = "0" . $valor;
                $conteo++;
            }
        }
        return $valor;
    }
    public function formato($valor, $tam)
    {
        $total = explode(".", $valor);
        $valor = $total[0];

        if (isset($total[1])) {
            $decimal = $total[1];
        } else {
            $decimal = 0;
        }
        $conteo = strlen($valor);
        while ($conteo < $tam) {
            $valor = "0" . $valor;
            $conteo++;
        }
        $cont = strlen($decimal);
        while ($cont < 4) {
            $decimal =  $decimal . "0";
            $cont++;
        }

        $valor = $valor . "." . $decimal;
        return $valor;
    }

    public function formatoSigno($valor, $tam)
    {
        $total = explode(".", $valor);
        $valor = $total[0];

        if (isset($total[1])) {
            $decimal = $total[1];
        } else {
            $decimal = 0;
        }
        $conteo = strlen($valor);
        while ($conteo < $tam) {
            $valor = "0" . $valor;
            $conteo++;
        }
        $cont = strlen($decimal);
        while ($cont < 4) {
            $decimal =  $decimal . "0";
            $cont++;
        }

        $valor = '+' . $valor . "." . $decimal;
        return $valor;
    }
}
