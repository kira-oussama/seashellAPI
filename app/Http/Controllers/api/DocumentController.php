<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Document;
use App\Memoire;
use App\Livre;
use Validator;
use Storage;
use DB;

class DocumentController extends Controller
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
        $doc = Document::all();
        return $doc;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $doc = new Document;
        $doc->titre = $request->titre;
        $doc->auteur = $request->auteur;

        if($request->doctype == 'memoire'){
            $validator = Validator::make($request->all(),[
                'niveau' => 'required|in:licence,master1,master2',
                'theme' => 'required|regex:/^[\pL\s\-]+$/u',
                'encadreur' => 'required|regex:/^[\pL\s\-]+$/u',
				
				'titre' => 'required|min:3|max:30',
				'auteur' => 'required|min:3|max:30',
            ],[
                'required' => 'Le :attribute est obligatoire.',
				'regex' => 'Le :attribute est de format incorrect.',
				'min' => 'Le :attribute est trop court.',
				'max' => 'Le :attribute est trop long.',
            ]);
    
            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()->all()]);
            }
			$doc->save();
            $memoire = new Memoire;
            $memoire->document_id = $doc->id;
            $memoire->niveau = $request->niveau;
            $memoire->theme = $request->theme;
            $memoire->encadreur = $request->encadreur;
            $memoire->save();
        }

        if($request->doctype == 'livre'){
            $validator = Validator::make($request->all(),[
                'editeur' => 'required|regex:/^[\pL\s\-]+$/u',
                'prix' => 'required|numeric',
				
				'titre' => 'required|min:3|max:30',
				'auteur' => 'required|min:3|max:30',
            ],[
                'required' => 'Le :attribute est obligatoire.',
				'regex' => 'Le :attribute est de format incorrect.',
				'numeric'=>'Le :attribute doit etre un numero',
				
				'min' => 'Le :attribute est trop court.',
				'max' => 'Le :attribute est trop long.',
            ]);
    
            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()->all()]);
            }
			
			$doc->save();
            $livre = new Livre;
            $livre->document_id = $doc->id;
            $livre->editeur = $request->editeur;
            $livre->prix = $request->prix;
            $livre->save();
        }

        return $doc;

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function storeImage(Request $request,$documentid){

        $host = 'http://localhost:8000';

        $validator = Validator::make($request->all(),[
            'file' => 'required|image|mimes:jpeg,png,jpg,svg|max:10240'
        ],[
            'max' => 'Le :attribute est trop long.',
            'mimes' => 'Le fichier n\est pas une image',
            'image' => 'Le fichier n\est pas une image'
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $path = Storage::putFile('public',$request->file('file'));
        $url = Storage::url($path);

        $store = DB::table('documents')->where('id','=',$documentid)
                                        ->update(['photo'=>$host.$url]);
        return 'uploaded';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $doc = Document::find($id);

        if(!empty($doc->livre)){
            $doc->livre->get();
        }

        if(!empty($doc->memoire)){
            $doc->memoire->get();
        }

        return $doc;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            'titre' => 'required|min:3|max:30',
            'auteur' => 'required|min:3|max:30',
            'doctype' => 'required|in:memoire,livre'
        ],[
            'required' => 'Le :attribute est obligatoire.',
            'min' => 'Le :attribute est trop court.',
            'max' => 'Le :attribute est trop long.',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $doc = Document::find($id);
        $doc->titre = $request->titre;
        $doc->auteur = $request->auteur;
        $doc->update();

        if($doc->doctype == 'memoire'){
            $validator = Validator::make($request->all(),[
                'niveau' => 'required|in:licence,master1,master2',
                'theme' => 'required|regex:/^[\pL\s\-]+$/u',
                'encadreur' => 'required|regex:/^[\pL\s\-]+$/u',
            ],[
                'required' => 'Le :attribute est obligatoire.',
            ]);
    
            if($validator->fails()){
                return response()->json(['errors' => $validator->errors()->all()]);
            }
            $memoire = $doc->memoire;
            $memoire->document_id = $doc->id;
            $memoire->niveau = $request->niveau;
            $memoire->theme = $request->theme;
            $memoire->encadreur = $request->encadreur;
            $memoire->update();
        }
        
        if($request->doctype == 'livre'){
            $validator = Validator::make($request->all(),[
                'editeur' => 'required|regex:/^[\pL\s\-]+$/u',
                'prix' => 'required|numeric',
            ],[
                'required' => 'Le :attribute est obligatoire.',
                ]);
                
                if($validator->fails()){
                    return response()->json(['errors' => $validator->errors()->all()]);
                }
                
            $livre = $doc->livre;
            $livre->document_id = $doc->id;
            $livre->editeur = $request->editeur;
            $livre->prix = $request->prix;
            $livre->update();
        }

        return $doc;

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $doc = Document::find($id);
        $doc->delete();   
        if(!empty($doc->livre)){
            $doc->livre->delete();
        }

        if(!empty($doc->memoire)){
            $doc->memoire->delete();
        }
        return $doc;
    }

    /**
     * a function tells you if the document is a 'livre' or 'memoire'
     */

    public function is_livre($document){
        $live = Livre::find($document->id);
        if(empty($livre)){
            return false;
        }
        return true;
    }


    /**
     * search the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
}