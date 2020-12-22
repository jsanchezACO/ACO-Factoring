<?php

namespace App\Http\Controllers;

use SoapClient;
use SoapFault;
use App\Models\Planos;
use App\Http\Controllers\MailController;
use App\Http\Controllers\PlanoController;
use SimpleXMLElement;

class SiesaWSController extends Controller
{
    //
    public function WS_SIESA($estado){
        try{

            if ($estado==1 or $estado==2)
            {
              $plano=(new PlanoController)->Plano();
            }
            $params=array ('ciphers'=>'UTF-8','verifypeer'=>false,'verifyhost'=>false);
            $url="http://wscorralpruebas.siesacloud.com:8043/wsunoee/WSUNOEE.asmx?wsdl";
            //$url="http://iisircc.siesacloud.com:8043/wsunoee/WSUNOEE.asmx?wsdl";
		
            $client= new SoapClient($url,$params);
    
            $Generar=Planos::whereNull('respuesta')->where('estado',$estado)->get();  

            foreach($Generar as $Importar){
                $mierror = 0;
                $Datos = array('pvstrDatos' => $Importar->XMLs, 'printTipoError' => $mierror);
                $Res=$client->ImportarXML($Datos);
 
                $Mensaje=json_decode(json_encode($Res->ImportarXMLResult));
                $Mensaje= $Mensaje->any;
                //$Mensaje= new SimpleXMLElement($Mensaje->any);
                //$Mensaje->xpath("//NewDataSet");

                if ($Res->printTipoError<>0)
                {
                    $Asunto='Error procesando XML Factoring';
                    $Transaccion=$Importar->Transaccion;
                    $Contenido=$Mensaje;

                    MailController::store($Asunto,$Transaccion,$Contenido);

                }
                Planos::where('id',$Importar->id)->update(['printTipoError'=>$Res->printTipoError]);
                Planos::where('id',$Importar->id)->update(['respuesta'=>$Mensaje]);
            }
        }
        catch(SoapFault $fault) {
            echo $fault;
        }   
    }
}
