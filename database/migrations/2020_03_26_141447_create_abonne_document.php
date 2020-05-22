<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbonneDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonne_document', function (Blueprint $table) {
            $table->id();
            // abonne foreign key
            $table->string('abonne_numcarte');
            $table->foreign('abonne_numcarte')->references('numcarte')->on('abonnes')->onDelete('cascade');
            // abonne foreign key
            // document foreign key
            $table->biginteger('document_id')->unsigned()->default(null);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            // document foreign key
            $table->date('date_reservation');
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
        Schema::dropIfExists('abonne_document');
    }
}
