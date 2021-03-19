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

// Route::middleware('auth:api')->group(function() {
//   Route::get('/user', function (Request $request) {
//     return $request->user();
//   });  
// });

Route::get('/reservations/{bike_id}','BikeController@apiReservations');

Route::middleware(['cors'])->group(function() {
  Route::prefix('embed')->group(function() {
    Route::name('embed.')->group(function() {
      Route::match(['OPTIONS','POST'],'search', 'SearchController@apiSearch');
      Route::match(['OPTIONS','POST'],'browse', 'SearchController@apiBrowse'); 
      Route::match(['OPTIONS','GET'],'client_config', 'EmbedController@apiClientConfig');
    });
  });
});