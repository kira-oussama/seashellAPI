<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('admin')->group(function(){

    Route::post('login','api\AdminLoginController@login');

    //controlle users(abonnes)
    Route::post('abonne/store','api\AbonneController@store');
    Route::put('abonne/update/{numcarte}','api\AbonneController@update');
    Route::delete('abonne/delete/{numcarte}' , 'api\AbonneController@destroy');
    Route::get('abonne/search' , 'api\AbonneController@search');
    Route::get('abonne/show/{numcarte}','api\AbonneController@show');
    Route::put('abonne/reinitialiser','api\AbonneController@reinitialiser');
    Route::put('abonne/depinalise','api\ExemplaireController@depinliser');


    //controlle documents
    Route::post('document/store','api\DocumentController@store');
    Route::post('document/storeimg/{documentid}','api\DocumentController@storeImage');
    Route::put('document/update/{id}','api\DocumentController@update');
    Route::delete('document/delete/{id}','api\DocumentController@destroy');
    Route::get('document/all','api\DocumentController@index');
    Route::get('document/show/{id}','api\DocumentController@show');
    Route::get('document/search','api\DocumentController@search');

    //controlle exemplaires
    Route::post('exemplaire/store','api\ExemplaireController@store');
    Route::get('exemplaire/show/{id}','api\ExemplaireController@show');
    Route::delete('exemplaire/{id}','api\ExemplaireController@destroy');
    Route::post('exemplaire/pret/','api\ExemplaireController@preter');
    Route::get('exemplaire/pret/','api\ExemplaireController@exPreter');
    Route::post('exemplaire/rendre','api\ExemplaireController@rendre');
    Route::get('exemplaire/stats','api\ExemplaireController@stats');
    
});


//CLIENT ROUTES
Route::post('/login','api\AbonneLoginController@login');
Route::get('search','api\ClientController@search');
Route::get('consulte/{id}','api\ClientController@consulte');
Route::get('showExemplaire','api\ClientController@consulteExemplaires');
Route::post('updatePassword','api\ClientController@updatePassword');
Route::put('reserver','api\ClientController@reserver');
Route::put('ameliorer','api\ClientController@ameliorer');
Route::post('annulerReservation','api\ClientController@annulerReservation');
Route::post('moreTime','api\ClientController@moreTime');
Route::get('afficherReservation','api\ClientController@afficherReservation');
Route::get('exemplaire/pret','api\ClientController@exPreter');