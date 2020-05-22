<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exemplaire extends Model
{
    //

    public function abonnes(){
        return $this->belongsToMany('App\Abonne')->withTimestamps();
    }

    public function document(){
        return $this->belongsTo('App\Document');
    }

}
