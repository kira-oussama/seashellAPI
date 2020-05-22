<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('livres', function (Blueprint $table) {
            $table->id();
            // document foreign key
            $table->biginteger('document_id')->unsigned()->default(null);
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            // document foreign key
            $table->string('editeur')->default(null);
            $table->double('prix')->default(null);
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
        Schema::dropIfExists('livres');
    }
}
