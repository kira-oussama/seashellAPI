<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Document;
use App\Exemplaire;
use App\Abonne;
use Validator;
use Auth;
use Carbon\Carbon;
class ClientController extends Controller
{
    function __construct(){
        $this->middleware('auth:abonne_api');
    }

    /**
     * search for a specific document
     */
    public function search(Request $request)
    {
        $titre = $request->get('titre');
        $auteur = $request->get('auteur');

        if(isset($titre) && isset($auteur)){
                $document = DB::table('documents')->where('titre','like','%'.$titre.'%')->where('auteur','like','%'.$auteur.'%')->paginate(6);
        }else
        if(isset($titre)){
            $document = DB::table('documents')->where('titre','like','%'.$titre.'%')->paginate(6);
        }else
        if(isset($auteur)){
            $document = DB::table('documents')->where('auteur','like','%'.$auteur.'%')->paginate(6);   
        }else{
            $document = DB::table('documents')->all()->paginate(6);   
        }

        return $document;

    }

    public function consulte(Request $request,$id){
        $document = Document::find($id);
        return $document;
    }

    public function consulteExemplaires(Request $request){
        $document = Document::find($request->id);
        $available = $document->exemplaires()->where('est_prete','=',false)->count();
        $both = $document->exemplaires->count();
        $reserved = false;
        if($document->abonnes()->count() > 0){
            if($document->abonnes[0]->pivot->count() > 0){
                $reserved = true;
            }
        }
        return response()->json(['exemplaires'=>$document->exemplaires,
                                'available'=>$available,
                                'both'=>$both,
                                'reserved'=>$reserved
                                ]);
    }

    public function updatePassword(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:8',
            'newpassword' =>'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        if(!Auth::guard('abonne')->attempt(['email'=> $request->email , 'password' => $request->password])){
            return response(['errors' => 'l\'ancien mot de passe est incorrect' ]);
        }

        $abonne = Abonne::where('email','=',$request->email)->first();
        
        $abonne->password = bcrypt($request->newpassword);
        $abonne->update();

        return $abonne;
    }

    /**
     * Reserver un document  
    */

    public function reserver(Request $request){
        $validator = Validator::make($request->all(),[
            'document_id'=> 'required',
            'abonne_numcarte'=> 'required|max:12',
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }
        
        $abonne = Abonne::find($request->abonne_numcarte);
        if(!$abonne){
            return response()->json(['Errors'=> 'il ya pas un etudiant avec ce numéro de carte']);
        }

        if($abonne->est_penalize){
            return response()->json(['Errors'=> 'cette abonne est penalizé']);
        }

        $document = Document::find($request->document_id);
        
        if(!$document){
            return response()->json(['Errors'=> 'il ya pas un document avec cette reference']);
        }
        
        $abonne->documents()->attach($request->document_id,['date_reservation'=>Carbon::now()->format('Y-m-d')]);
        
        return $abonne->documents;
    }

    /**
     * 
     */

    public function upgrade(Request $request){
        $validator = Validator::make($request->all(),[
            'numcarte' => 'required',
            'newGrade' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $abonne = Abonne::find($request->numcarte);
        $abonne->grade = $request->newGrade;
        $abonne->update();
        return $abonne;
    }

    /**
     * Annuler reservation d'un document  
    */

    public function annulerReservation(Request $request){
        $validator = Validator::make($request->all(),[
            'document_id'=> 'required',
            'abonne_numcarte'=> 'required|max:12',
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }
        
        $abonne = Abonne::find($request->abonne_numcarte);
        if(!$abonne){
            return response()->json(['Errors'=> 'il ya pas un etudiant avec ce numéro de carte']);
        }

        if($abonne->est_penalize){
            return response()->json(['Errors'=> 'cette abonne est penalizé']);
        }

        $document = Document::find($request->document_id);
        
        if(!$document){
            return response()->json(['Errors'=> 'il ya pas un document avec cette reference']);
        }
        
        $abonne->documents()->detach($request->document_id);
        
        return $abonne->documents;
    }


    /**
     * affichier la liste des documents reservé par un abonné
     */


    public function afficherReservation(Request $request){
        $validator = Validator::make($request->all(),[
            'abonne_numcarte'=> 'required|max:12',
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }

        $abonne = Abonne::find($request->abonne_numcarte);


        return $abonne->documents;
    }

    /**
     * afficher les exemplaire preter pour un abonne avec abonne_numcarte
     */
    public function exPreter(Request $request){
        $abonne = Abonne::find($request->numcarte);
        return $abonne->exemplaires;
    }

    /**
     * 
     */

    public function ameliorer(Request $request){
        $validator = Validator::make($request->all(),[
            'numcarte' => 'required',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $abonne = Abonne::find($request->numcarte);
        if($abonne->grade === 'bronze'){
            
            if( ($abonne->points-1500) > 0){
                $abonne->points -= 1500;
                $abonne->grade = 'silver';
                $abonne->max_nombre_emprunts +=1;
            }else{
                return response()->json(['errors'=>'votre points sont insuffisant']);
            }

        }else
        if($abonne->grade === 'silver'){
            
            if( ($abonne->points-3000) > 0){
                $abonne->points -= 3000;
                $abonne->grade = 'gold';
                $abonne->max_nombre_emprunts +=1;
            }else{
                return response()->json(['errors'=>'votre points sont insuffisant']);
            }

        }else{
            return response()->json(['errors'=>'Votre Grade est améliorer']);
        }

        $abonne->update();
        return $abonne;
    }


    /**
     * 
     */

    public function moreTime(Request $request){
        $validator = Validator::make($request->all(),[
            'numcarte' => 'required',
            'reference_exemplaire' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $abonne = Abonne::find($request->numcarte);
        $oldDate = Carbon::parse($abonne->exemplaires->where('reference_exemplaire','=',$request->reference_exemplaire)->first()->pivot->date_retour);
        $empruntDate = Carbon::parse($abonne->exemplaires->where('reference_exemplaire','=',$request->reference_exemplaire)->first()->pivot->date_emprunt);
        $newDate = $oldDate->addDays(7)->format('Y-m-d');
        $exemplaire_id = $abonne->exemplaires->where('reference_exemplaire','=',$request->reference_exemplaire)->first()->id;

        $abonne->exemplaires()->detach($exemplaire_id);
        $abonne->exemplaires()->attach($exemplaire_id,['date_retour'=> $newDate
                                                     ,'date_emprunt'=> $empruntDate]);

        $abonne->points -= 200;
        $abonne->update();

        return $abonne;
    }

}
