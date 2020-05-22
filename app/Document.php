<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{

    public function exemplaires(){
        return $this->hasMany('App\Exemplaire','document_id');
    }

    public function abonnes(){
        return $this->belongsToMany('App\Abonne')->withTimestamps();
    }

    public function memoire(){
        return $this->hasOne('App\Memoire','document_id');
    }

    public function livre(){
        return $this->hasOne('App\Livre','document_id');
    }

}
