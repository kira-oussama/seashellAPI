<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbonneExemplaireTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonne_exemplaire', function (Blueprint $table) {
            $table->id();
            // abonne foreign key
            $table->string('abonne_numcarte');
            $table->foreign('abonne_numcarte')->references('numcarte')->on('abonnes')->onDelete('cascade');
            // abonne foreign key

            // exemplaire foreign key
            $table->string('exemplaire_id');
            $table->foreign('exemplaire_id')->references('id')->on('exemplaire')->onDelete('cascade');
            // exemplaire foreign key

            $table->date('date_emprunt');
            $table->date('date_retour');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abonne_exemplaire');
    }
}
