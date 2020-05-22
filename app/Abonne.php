<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Abonne extends Authenticatable
{
    use HasApiTokens , Notifiable;

    protected $primaryKey = 'numcarte';

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    Protected $fillable = [
        'numcarte' , 'nom' , 'prenom' , 'adresse'  , 'sexe', 'numtel' , 'email' ,
        'numbre_emprunts', 'grade' , 'points'
    ];

    Protected $hidden = [
        'max_nombre_emprunts' , 'rememberToken' , 'timeStamps', 'password'
    ];

    public function exemplaires(){
        return $this->belongsToMany('App\Exemplaire')->withPivot('date_emprunt','date_retour')->withTimestamps();
    }

    public function documents(){
        return $this->belongsToMany('App\Document')->withTimestamps();
    }
    

}
