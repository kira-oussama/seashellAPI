<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;


class AdminLoginController extends Controller
{

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        if(!Auth::attempt(['email'=> $request->email , 'password' => $request->password])){
            return response(['error' => 'vos informations d\'identification ne sont pas correctes' ]);
        }

        $token = Auth::user()->createToken('auth_token')->accessToken;

        return response(['user' => Auth::user() , 'acessToken' => $token ]);

    }

}
