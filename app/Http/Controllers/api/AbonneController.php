<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Abonne;
use Validator;
use DB;

class AbonneController extends Controller
{
    
    function __construct(){
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'numcarte' => 'required|min:12|max:12|unique:abonnes|regex:/\d/',
            'nom' => 'required|max:20|min:4|regex:/^[\pL\s\-]+$/u',
            'prenom' => 'required|max:20|min:4|regex:/^[\pL\s\-]+$/u',
            'adresse' => 'required|max:100',
            'sexe' => 'required|in:male,female',
            'numtel' => 'required|min:10|max:10|unique:abonnes|regex:/\d$/',
            'email' => 'required|e-mail|unique:abonnes',
        ],[
            'required' => 'Le :attribute est obligatoire.',
            'min' => 'Le :attribute est trés court.',
            'max' => 'Le :attribute est trés long.',
            'regex' => 'Le :attribute est de format incorrect.',
            'unique' => 'Le :attribute est existe deja.',
            'e_mail' => 'Le :attribute est de format incorrect.',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        
        $abonne = new Abonne;
        $abonne->numcarte = $request->numcarte;
        $abonne->nom = $request->nom; 
        $abonne->prenom = $request->prenom; 
        $abonne->adresse = $request->adresse;
        $abonne->password = bcrypt($request->numcarte); 
        $abonne->sexe = $request->sexe; 
        $abonne->numtel = $request->numtel; 
        $abonne->email = $request->email; 

        $abonne->save();

        return $abonne;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($numcarte)
    {
        $abonne = Abonne::find($numcarte);
        return $abonne;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'numcarte' => 'required|min:12|max:12|regex:/\d$/|unique:abonnes,email,'.$request->numcarte.',numcarte',
            'nom' => 'required|max:20|min:4|regex:/^[\pL\s\-]+$/u',
            'prenom' => 'required|max:20|min:4|regex:/^[\pL\s\-]+$/u',
            'adresse' => 'required|max:100',
            'sexe' => 'required|in:male,female',
            'numtel' => 'required|min:10|max:10|regex:/\d$/',
            'email' => 'required|e-mail|unique:abonnes,email,'.$request->numcarte .',numcarte',
        ],[
            'required' => 'Le :attribute est obligatoire.',
            'min' => 'Le :attribute est trés court.',
            'max' => 'Le :attribute est trés long.',
            'regex' => 'Le :attribute est de format incorrect.',
            'unique' => 'Le :attribute est existe deja.',
            'e_mail' => 'Le :attribute est de format incorrect.',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $abonne = Abonne::find($id);
        $abonne->numcarte = $request->numcarte;
        $abonne->nom = $request->nom; 
        $abonne->prenom = $request->prenom; 
        $abonne->adresse = $request->adresse;
        $abonne->password = bcrypt($request->numcarte); 
        $abonne->sexe = $request->sexe; 
        $abonne->numtel = $request->numtel; 
        $abonne->email = $request->email; 

        $abonne->update();

        return $abonne;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $abonne = Abonne::find($id);
        $abonne->delete();
        return response(['message' => 'l\'abonné a etait suprimé']);
    }

    /**
     * Search the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $nom = $request->get('nom');
        $prenom = $request->get('prenom');
        $sexe = $request->get('sexe');

        //name exists
        if(isset($nom)){
            $users = DB::table('abonnes')->where('nom','like',$nom)->paginate(6);

            //name and sname exists
            if(isset($prenom)){
                $users = DB::table('abonnes')->where('prenom','like',$prenom)
                                            ->where('nom','like',$nom)
                                            ->paginate(6);
                //name and sname and sexe exists                            
                if(isset($sexe)){
                    $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                                ->where('nom','like',$nom)
                                                ->where('prenom','like',$prenom)
                                                ->paginate(6);
                }
 
            }

            //nom then sexe
            if(isset($sexe)){
                $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                            ->where('nom','like',$nom)
                                            ->paginate(6);
            }
        }
        
        //only sname exists
        if(isset($prenom)){
            $users = DB::table('abonnes')->where('prenom','like',$prenom)->paginate(6);

            if(isset($sexe)){
                $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                            ->where('prenom','like',$prenom)
                                            ->paginate(6);
                if(isset($nom)){
                    $users = DB::table('abonnes')->where('nom','like',$nom)
                                                ->where('prenom','like',$prenom)
                                                ->where('sexe','like',$sexe)
                                                ->paginate(6);
                }
            }

            if(isset($nom)){
                $users = DB::table('abonnes')->where('nom','like',$nom)
                                            ->where('prenom','like',$prenom)
                                            ->paginate(6);
            }
        }

        //only sexe exits
        if(isset($sexe)){
            $users = DB::table('abonnes')->where('sexe','like',$sexe)->paginate(6);
            
            if(isset($prenom)){
                $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                            ->where('prenom','like',$prenom)
                                            ->paginate(6);

                if(isset($nom)){
                    $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                                ->where('prenom','like',$prenom)
                                                ->where('nom','like',$nom)
                                                ->paginate(6);
                }
            }

            if(isset($nom)){
                $users = DB::table('abonnes')->where('sexe','like',$sexe)
                                            ->where('nom','like',$nom)
                                            ->paginate(6);
            }

        }
        
        return $users;
    }


    /**
     * réinitialiser le mot de passe de l'utilisateur
     */

    public function reinitialiser(Request $request){
        $validator = Validator::make($request->all(),[
            'numcarte' => 'required|min:12|max:12|unique:abonnes|regex:/\d/',
        ],[
            'required' => 'Le :attribute est obligatoire.',
            'min' => 'Le :attribute est tree court.',
            'max' => 'Le :attribute est tree long.',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        
        $abonne = Abonne::find($request->numcarte);
        $abonne->password = bcrypt($request->numcarte); 
        
        $abonne->update();

        return $abonne;
    }


}
