<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
use App\Abonne;
use Carbon\Carbon;


class AbonneLoginController extends Controller
{

    public function login(Request $request){
        
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        if(!Auth::guard('abonne')->attempt(['email'=> $request->email , 'password' => $request->password])){
            return response(['errors' => 'your credentials are not correct' ]);
        }

        $user = Abonne::where('email','=',$request->email)->first();
        $token = $user->createToken('auth_token')->accessToken;

        if(Carbon::now() >= Carbon::parse($user->date_de_depinalization) ){
            $user->est_penalize = 0;
            $user->date_de_depinalization = Carbon::now()->format('Y-m-d');
            $user->update();
        }

        return response(['user' => $user , 'acessToken' => $token ]);


        
    }

}