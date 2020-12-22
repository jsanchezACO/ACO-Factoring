<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facturas;
use App\Models\Planos;
use Illuminate\Support\Arr;
use SoapFault;

class AsignarController extends Controller
{
    //
    public function Tercero(Request $request)
    {
        try {
            //Instancia a concultar
            switch ($request->CIA) {
                case 'AIIR':
                    $cia = 'CorralC';
                    break;
                case 'AILS':
                    $cia = 'LenoslC';
                    break;
                case 'AIPJ':
                    $cia = 'PapaC';
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
            $ID_TIPO_DOCTO = $this->completaCampo('PFF', 3, 'ALFA'); //Tipo de documento;
            $CONSEC_DOCTO = $this->completaCampo(1, 8, 'NUM'); //Consecutivo de documento
            $Fecha = date('yymd');
            $ID_TERCERO = $this->completaCampo($request->Banco, 15, 'ALFA'); //Tercero del documento
            $ID_CLASE_DOCTO = $this->completaCampo(30, 5, 'NUM');
            $IND_ESTADO = 1; //; //1 para aprobado, 2 para anular
            $IND_IMPRESION = 0; //siempre debe ir en 0
            $NOTAS = $this->completaCampo($request->Transaccion, 255, 'ALFA'); //Observaciones del documento            
            $ID_MANDATO = $this->completaCampo('', 15, 'ALFA'); //Mandato vacio no es obligatorio
            $RECALCULAR_RX = 0; //Indica si se recalcula la re-expresión del documento con el tipo de cambio enviado
            $ID_TIPO_CAMBIO_RX = $this->completaCampo('', 3, 'ALFA'); //
            $TASA_CONV_RX = $this->formato(1, 8);    //Tasa de conversión Reexpresion
            $TASA_LOCAL_RX = $this->formato(1, 8);    //Tasa local Reexpresion
            $ID_CO_RP = $this->completaCampo('', 3, 'ALFA'); //
            $ID_TIPO_DOCTO_RP = $this->completaCampo('', 3, 'ALFA'); //
            $CONSEC_DOCTO_RP = $this->completaCampo(0, 8, 'NUM'); //    
            $ID_MOTIVO_OTRO = $this->completaCampo("", 20, 'ALFA'); //Vacio si no es anulación      

            //Construcción Primera Linea
            $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
            $linea = $F_NUMERO_REG . "00000001" . $F_CIA;
            $xml .= "<Linea>" . $linea . "</Linea>\r\n";

            //Construcción Linea Documento
            $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
            $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $F_CONSEC_AUTO_REG . $ID_CO . $ID_TIPO_DOCTO . $CONSEC_DOCTO . $Fecha . $ID_TERCERO . $ID_CLASE_DOCTO . $IND_ESTADO .
            $IND_IMPRESION . $NOTAS . $ID_MANDATO . $RECALCULAR_RX . $ID_TIPO_CAMBIO_RX . $TASA_CONV_RX . $TASA_LOCAL_RX . $ID_CO_RP . $ID_TIPO_DOCTO_RP . $CONSEC_DOCTO_RP . $ID_MOTIVO_OTRO;
            $xml .= "<Linea>" . $linea . "</Linea>\r\n";

            //Construcción Linea Cuenta x Pagar
            $F_TIPO_REG = $this->completaCampo(351, 4, 'NUM'); //Tipo de registro;
            $F_SUBTIPO_REG = $this->completaCampo(2, 2, 'NUM'); //Subtipo de registro
            $F_VERSION_REG = $this->completaCampo(6, 2, 'NUM'); //Version del tipo de registro
            $F_CIA = $this->completaCampo(1, 3, 'NUM'); //Version del tipo de registro
            $ID_CO = $this->completaCampo(1, 3, 'NUM'); //Centro de operación del documento
            $ID_TIPO_DOCTO = $this->completaCampo('PFF', 3, 'ALFA'); //Tipo de documento;                
            $CONSEC_DOCTO = $this->completaCampo(1, 8, 'NUM'); //Consecutivo de documento                
            $ID_UN = $this->completaCampo('01', 20, 'ALFA'); //Unidad de negocio
            $ID_CCOSTO = $this->completaCampo('', 15, 'ALFA'); //Auxiliar de centro de costos
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
            $NOTAS = $this->completaCampo('Documento generado por plataforma factoring, Transaccion: ' . $request->Transaccion, 255, 'ALFA'); //Observaciones del documento                        

            $Facturas = Facturas::select( 'ValorFactura', 'Sucursal', 'Cuenta','CO', 'Prefijo', 'Factura', 'FechaFactura', 'Vencimiento', 'PP', 'Impuesto', 'Base'
            )->where('Idtransaccion', $request->Transaccion)->get();
            foreach ($Facturas as $factura) {
                //Primer Registro
                $ID_AUXILIAR_P = $factura->Cuenta . '0511';
                $ID_TERCERO = $this->completaCampo($request->NIT, 15, 'ALFA'); //                
                $ID_CO_MOV = $this->completaCampo($factura->CO, 3, 'NUM'); //Centro de operación del movimiento
                $ID_AUXILIAR = $this->completaCampo($ID_AUXILIAR_P, 20, 'ALFA'); //cuenta contable con la que se realizara el movimiento
                $ID_SUCURSAL = $this->completaCampo($factura->Sucursal, 3, 'ALFA'); //Sucursal proveedor
                $PREFIJO_CRUCE = $this->completaCampo($factura->Prefijo, 20, 'ALFA'); //Prefijo de documento de cruce
                $CONSEC_DOCTO_CRUCE = $this->completaCampo($factura->Factura, 8, 'NUM'); //Número de documento de cruce
                $FECHA_VCTO = $this->completaCampo($factura->Vencimiento, 8, 'ALFA'); //
                $FECHA_DOCTO_CRUCE = $this->completaCampo($factura->FechaFactura, 8, 'ALFA'); //
                $FECHA_DSCTO_PP = $this->completaCampo($factura->PP, 8, 'ALFA'); // 
                $VALOR_CR = $this->formatoSigno(0, 15);    //Valor de factura a pagar
                $VALOR_CR2 = $this->formatoSigno(0, 15);    //Valor de factura a pagar  
                $VALOR_DB = $this->formatoSigno($factura->ValorFactura, 15);    //Valor debito se deja en cero
                $VALOR_DB2 = $this->formatoSigno($factura->ValorFactura, 15);    //Valor debito                
                $VALOR_IMP_ASUM = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido
                $VALOR_IMP_ASUM2 = $this->formatoSigno($factura->Impuesto, 15);    //Valor impuesto asumido

                //Construccion linea Reclacifica Banco
                $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $ID_CO . $ID_TIPO_DOCTO .
                    $CONSEC_DOCTO . $ID_AUXILIAR . $ID_TERCERO . $ID_CO_MOV . $ID_UN . $ID_CCOSTO . $VALOR_DB . $VALOR_CR . $VALOR_DB_ALT .
                    $VALOR_CR_ALT . $VALOR_DB2 . $VALOR_CR2 . $VALOR_DB_ALT2 . $VALOR_CR_ALT2 . $VALOR_IMP_ASUM . $VALOR_IMP_ASUM2 . $NOTAS .
                    $ID_SUCURSAL . $PREFIJO_CRUCE . $CONSEC_DOCTO_CRUCE . $NRO_CUOTA_CRUCE . $ID_FE . $FECHA_VCTO . $FECHA_DSCTO_PP .
                    $FECHA_DOCTO_CRUCE . $VLR_DSCTO_PP . $VALOR_APLICADO_PP . $VALOR_APLICADO_PP_ALT . $VALOR_RETENCION . $VALOR_RETENCION_ALT .
                    $FECHA_RADICACION . $NOTAS;
                $xml .= "<Linea>" . $linea . "</Linea>\r\n";

                $ID_TERCERO = $this->completaCampo($request->NIT, 15, 'ALFA'); //                
                $VALOR_CR = $this->formatoSigno($factura->ValorFactura, 15);    //Valor de factura a pagar
                $VALOR_CR2 = $this->formatoSigno($factura->ValorFactura, 15);    //Valor de factura a pagar   
                $VALOR_DB = $this->formatoSigno(0, 15);    //Valor debito se deja en cero
                $VALOR_DB2 = $this->formatoSigno(0, 15);    //Valor debito   
                
                //Construccion linea Reclacifica Tercero
                $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
                $linea =  $F_NUMERO_REG . $F_TIPO_REG . $F_SUBTIPO_REG . $F_VERSION_REG . $F_CIA . $ID_CO . $ID_TIPO_DOCTO .
                    $CONSEC_DOCTO . $ID_AUXILIAR . $ID_TERCERO . $ID_CO_MOV . $ID_UN . $ID_CCOSTO . $VALOR_DB . $VALOR_CR . $VALOR_DB_ALT .
                    $VALOR_CR_ALT . $VALOR_DB2 . $VALOR_CR2 . $VALOR_DB_ALT2 . $VALOR_CR_ALT2 . $VALOR_IMP_ASUM . $VALOR_IMP_ASUM2 . $NOTAS .
                    $ID_SUCURSAL . $PREFIJO_CRUCE . $CONSEC_DOCTO_CRUCE . $NRO_CUOTA_CRUCE . $ID_FE . $FECHA_VCTO . $FECHA_DSCTO_PP .
                    $FECHA_DOCTO_CRUCE . $VLR_DSCTO_PP . $VALOR_APLICADO_PP . $VALOR_APLICADO_PP_ALT . $VALOR_RETENCION . $VALOR_RETENCION_ALT .
                    $FECHA_RADICACION . $NOTAS;
                $xml .= "<Linea>" . $linea . "</Linea>\r\n";
            }
            //Linea Final
            $F_NUMERO_REG = $this->completaCampo($F_NUMERO_REG + 1, 7, 'NUM');
            $linea = $F_NUMERO_REG . "99990001" . $F_CIA;
            $xml .= "<Linea>" . $linea . "</Linea>\r\n";
            $xml .= "</Datos>\r\n" . "</Importar>";

            $input = $request->all();
            $input = Arr::add($input, 'CIA', 'AIIR');
            $input = Arr::add($input, 'XMLs', $xml);
            Planos::create($input);

            return response()->json([
                'respuesta' => true,
                'message' => 'Ejecutado correctamente'
            ], 200);
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
