<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exemplaire;
use App\Document;
use App\Abonne;
use Validator;
use DB;
use Carbon\Carbon;

class ExemplaireController extends Controller
{

    function __construct(){
        $this->middleware('auth:api');
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
            'document_id'=> 'required',
            'reference_exemplaire'=> 'required|unique:exemplaires',
            'etat'=>'required|in:mauvais,bien,exellent'
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }

        $exemplaire = new Exemplaire;
        $exemplaire->document_id = $request->document_id;
        $exemplaire->reference_exemplaire = $request->reference_exemplaire;
        $exemplaire->etat = $request->etat;
        $exemplaire->save();
        return $exemplaire;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $document = Document::find($id);
        return $document->exemplaires;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $exemplaire = DB::table('exemplaires')->where('reference_exemplaire','like',$id);
        $exemplaire->delete();
        return response()->json(['id'=>$id]);
    }



    /**
     * preter un exemplaire avec le abonne_numcarte et reference_exemplaire
     */

    public function preter(Request $request){

        $validator = Validator::make($request->all(),[
            'reference_exemplaire'=> 'required',
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

        if($abonne->max_nombre_emprunts === $abonne->nombre_emprunts){
            return response()->json(['Errors'=> 'vous ne pouvez pas obtenir un autre livre jusqu\'à ce que vous retourniez celui que vous avez']);
        }

        $exemplaire = DB::table('exemplaires')->where('reference_exemplaire','like',$request->reference_exemplaire);
        
        if(count($exemplaire->get())<1){
            return response()->json(['Errors'=> 'il ya pas un document avec cette reference']);
        }
        
        $is_pret = \json_decode( $exemplaire->get('est_prete') );
        $is_pret = $is_pret[0]->est_prete;

        if($is_pret){
            return response()->json(['Errors'=> 'cette exemplaire est déja preté.']);
        }

        $exemplaire_id = \json_decode($exemplaire->get('id'));
        $exemplaire_id = $exemplaire_id[0]->id;
        $abonne->exemplaires()->attach($exemplaire_id,['date_retour'=> Carbon::now()->addDays(7)->format('Y-m-d')
                                                     ,'date_emprunt'=> Carbon::now()->format('Y-m-d') ]);
        
        $exem = Exemplaire::find($exemplaire_id);
        $exem->est_prete = 1;
        $exem->update();

        $abonne->nombre_emprunts += 1;
        $abonne->update();
        return $abonne->exemplaires;                      
    }


    /**
     * afficher les exemplaire preter pour un abonne avec abonne_numcarte
     */
    public function exPreter(Request $request){
        $abonne = Abonne::find($request->abonne_numcarte);
        return $abonne->exemplaires;
    }


    /**
     * rendre un exemplaire deja preter avec reference_exmplaire
     */

     public function rendre(Request $request){
        $validator = Validator::make($request->all(),[
            'reference_exemplaire'=> 'required',
            'abonne_numcarte'=> 'required|max:12',
            'etat'=> 'required|in:mauvais,bien,exellent'
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }
        
        $abonne = Abonne::find($request->abonne_numcarte);
        if(!$abonne){
            return response()->json(['Errors'=> 'il ya pas un etudiant avec ce numéro de carte']);
        }

        $exemplaire = DB::table('exemplaires')->where('reference_exemplaire','like',$request->reference_exemplaire);
        
        if(count($exemplaire->get())<1){
            return response()->json(['Errors'=> 'il ya pas un document avec cette reference']);
        }
        
        $is_pret = \json_decode( $exemplaire->get('est_prete') );
        $is_pret = $is_pret[0]->est_prete;

        if(!$is_pret){
            return response()->json(['Errors'=> 'cette exemplaire est n\'est pas preter.']);
        }

        //punish if the book isn't good or you are late
        $date_depinalization = '';
        if($abonne->exemplaires[0]->pivot->date_retour < Carbon::now()->format('Y-m-d')){
            //punition de retard
            $abonne->est_penalize = 1;
            $date_retour = Carbon::parse($abonne->exemplaires[0]->pivot->date_retour);
            $pinalize_days = $date_retour->diffInDays(Carbon::now())*2;
            $date_depinalization = Carbon::now()->addDays($pinalize_days)->format('Y-m-d');
            
            //punition de letat
            $exemplaire_etat = json_decode($exemplaire->get('etat'));
            $exemplaire_etat = $exemplaire_etat[0]->etat;
            if($exemplaire_etat !== $request->etat){
                $date_depinalization = Carbon::parse($date_depinalization)->addDays(31)->format('Y-m-d');
            }
            $abonne->date_de_depinalization=$date_depinalization;
        }else
        if($abonne->exemplaires[0]->pivot->date_retour >= Carbon::now()->format('Y-m-d')){
            //punition de letat
            $exemplaire_etat = json_decode($exemplaire->get('etat'));
            $exemplaire_etat = $exemplaire_etat[0]->etat;
            if($exemplaire_etat !== $request->etat){
                $abonne->est_penalize = 1;
                $date_depinalization = Carbon::parse($date_depinalization)->addDays(31)->format('Y-m-d');
                $abonne->date_de_depinalization=$date_depinalization;
            }else{
                $abonne->points += 50;
            }
        }
        
        //punish if the book isn't good or you are late

        $exemplaire_id = \json_decode($exemplaire->get('id'));
        $exemplaire_id = $exemplaire_id[0]->id;
        $abonne->exemplaires()->detach($exemplaire_id);
        
        $exem = Exemplaire::find($exemplaire_id);
        $exem->est_prete = 0;
        $exem->etat = $request->etat;
        $exem->update();

        if($abonne->nombre_emprunts > 0){
            $abonne->nombre_emprunts -= 1;
        }

        $abonne->update();


        return $abonne;
     }


     /**
      * depinalizer un abonner
      */

    public function depinliser(Request $request){
        $validator = Validator::make($request->all(),[
            'abonne_numcarte'=> 'required|max:12',
        ]);

        if($validator->fails()){
            return response()->json(['Errors'=> $validator->errors()->all()]);
        }
        $abonne = Abonne::find($request->abonne_numcarte);
        $abonne->est_penalize = 0;
        $date = new \DateTime();
        $date->setDate(1, 0, 0);
        $abonne->date_de_depinalization = $date->format('Y-m-d');
        $abonne->update();
        return $abonne;
    }


    public function stats(Request $request){
        $abonnes = count(Abonne::all());
        $exemplaires = count(Exemplaire::all());
        $emprunts = DB::table('abonne_exemplaire')->get()->count();
        $penalise = Abonne::where('est_penalize','like',1)->count();

        $mauvais = Exemplaire::where('etat','=','mauvais')->count();
        $bien = Exemplaire::where('etat','=','bien')->count();
        $exellent = Exemplaire::where('etat','=','exellent')->count();

        return response()->json(['abonnes'=>$abonnes,
        'exemplaires'=>$exemplaires,
        'emprunts'=>$emprunts,
        'penalise'=>$penalise,
        'mauvais'=>$mauvais,
        'bien'=>$bien,
        'exellent'=>$exellent,
        ]);
    }
    
}
