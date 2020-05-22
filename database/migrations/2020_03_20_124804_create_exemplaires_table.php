<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExemplairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exemplaires', function (Blueprint $table) {
            $table->id();
            // document foreign key
            $table->biginteger('document_id')->unsigned()->default(null);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            // document foreign key
            $table->String('reference_exemplaire')->unique();
            $table->enum('etat',['mauvais','bien','exellent']);
            $table->boolean('est_prete')->default(false);
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
        Schema::dropIfExists('exemplaires');
    }
}
