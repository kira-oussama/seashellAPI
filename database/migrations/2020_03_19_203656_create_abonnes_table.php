<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class CreateAbonnesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abonnes', function (Blueprint $table) {
            $table->string('numcarte');
            $table->primary('numcarte');
            // user foreign key
            $table->biginteger('user_id')->unsigned()->default(1);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // user foreign key
            
            $table->string('nom');
            $table->string('prenom');
            $table->string('adresse');
            $table->string('password');
            $table->enum('sexe',['male','female']);
            $table->string('numtel');
            $table->string('email')->unique();
            $table->enum('grade',['bronze','silver','gold'])->default('bronze');
            $table->integer('nombre_emprunts')->default(0);
            $table->integer('points')->default(50);
            $table->boolean('est_penalize')->default(false);
            $table->date('date_de_depinalization')->default(Carbon::now()->format('Y-m-d'));
            $table->integer('max_nombre_emprunts')->default(1);
            $table->rememberToken();
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
        Schema::dropIfExists('abonnes');
    }
}
