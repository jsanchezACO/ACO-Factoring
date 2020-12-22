<?php

namespace App\Http\Controllers;

use App\Mail\MenssgeReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;

class MailController extends Controller
{
    //
    public static function store($Asunto, $Transaccion, $mensaje)
    {
        $msg=['Asunto'=>$Asunto];
        $msg=Arr::add($msg,'Transaccion',$Transaccion);
        $msg=Arr::add($msg,'mensaje',$mensaje);
        Mail::to('jsanchez@alimentosalconsumidor.com')->queue(new MenssgeReceived($msg) );
    }
}
