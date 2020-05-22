<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Livre extends Model
{
    //

    public function cotegories(){
        return $this->hasMany('App\Category','livre_id');
    }
}
