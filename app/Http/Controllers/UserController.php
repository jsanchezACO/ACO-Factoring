<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $input = $request->all();
        $input['password']=Hash::make($request->password);
        User::create($input);
        return response()->json([
            'respuesta'=>true,
            'message'=>'Usuario creado correctamente'
        ],200);
    }

    public function login(Request $request)
    {
        $user=User::whereEmail($request->email)->first();
        if(!is_null($user) && Hash::check($request->password,$user->password))
        {
            $token=$user->createToken('FactoryACO')->accessToken;
            return response()->json([
                'respuesta'=>true,
                'token'=>$token,
                'message'=>'Bienvenido al Sistema'
            ],200);
        }
        else{
            return response()->json([
                'respuesta'=>false,
                'message'=>'Usuario o contraseña incorrecto'
            ],200);
        }
    }

    public function logout()
    {
        $user=auth()->user();
        $user->tokens->each(function($token, $Key){
            $token->delete();
        });
        $user->save();
        return response()->json([
            'respuesta'=>true,
            'message'=>'Gracias por usar el Sistema'
        ],200);
    }
}
