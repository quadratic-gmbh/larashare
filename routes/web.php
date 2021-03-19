<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/info', 'HomeController@info')->name('info');
Route::get('/impressum', 'HomeController@impressum')->name('impressum');
Route::get('/data_protection', 'HomeController@dataProtection')->name('data_protection');
Route::post('/calendar_usage_ok','HomeController@calendarUsageOk');
Route::get('/select_locale', 'HomeController@selectLocale')->name('select_locale');

Route::middleware(['debug_only'])->group(function() {
  Route::prefix('debug')->group(function() {
    Route::prefix('mail')->group(function() {
      Route::get('survey','DebugController@mailSurvey');
      Route::get('bike_cancelled_reservation','DebugController@mailBikeCancelledReservation');
      Route::get('user_cancelled_reservation','DebugController@mailUserCancelledReservation');
      Route::get('user_confirmed_reservation','DebugController@mailUserConfirmedReservation');
      Route::get('user_new_reservation','DebugController@mailUserNewReservation');
      Route::get('rental_period_reminder','DebugController@mailRentalPeriodReminder');
      Route::get('rental_place_cancelled_reservation','DebugController@mailRentalPlaceCancelledReservation');
      Route::get('rental_place_new_reservation','DebugController@mailRentalPlaceNewReservation');
      Route::get('chat_new_message','DebugController@mailChatNewMessage');
      Route::get('newsletter_confirmation','DebugController@mailNewsletterConfirmation');
    });
  });
});

Route::middleware(['embed_css_handler'])->group(function() {
  Route::prefix('embed/{embed}')->group(function() {
    Route::name('embed.')->group(function() {
      Route::get('search','SearchController@embedIndex')->name('search');
      Route::get('map','SearchController@embedMap')->name('search_map');
    });
  });
});

Route::prefix('search')->group(function() {
  Route::name('search.')->group(function() {
    Route::get('/','SearchController@index')->name('index');
    Route::get('/map','SearchController@map')->name('map');
  });
});

  Route::middleware('throttle:60,1')->group(function() {
    Route::prefix('api')->group(function() {
      Route::name('api.')->group(function() {
        Route::get('/rental_period/{bike_id}','BikeController@apiRentalPeriods');
      });
    });
  });

  Route::prefix('api')->group(function() {
    Route::name('api.')->group(function() {
      Route::get('/check_selection/{bike_id}','BikeController@apiCheckSelection');
    });
  });

Route::middleware(['auth','check_user_warnings'])->group(function() {
  Route::prefix('embed')->group(function() {
    Route::name('embed.')->group(function() {
      Route::get('/','EmbedController@index')->name('index');
      Route::get('/create','EmbedController@create')->name('create');
      Route::post('/','EmbedController@store')->name('store');
      Route::get('/{id}/edit','EmbedController@edit')->name('edit');
      Route::post('/{id}/update','EmbedController@update')->name('update');
      Route::get('/{id}/edit_bikes','EmbedController@editBikes')->name('edit_bikes');
      Route::post('/{id}/update_bikes','EmbedController@updateBikes')->name('update_bikes');
      Route::post('/{id}/update_bikes_allow_all','EmbedController@updateBikesAllowAll')->name('update_bikes_allow_all');
      Route::get('/{id}/delete','EmbedController@destroyAsk')->name('destroy_ask');
      Route::delete('/{id}/delete','EmbedController@destroy')->name('destroy');
      Route::get('/{id}/show','EmbedController@show')->name('show');
    });
  });
  Route::prefix('bike')->group(function() {
    Route::name('bike.')->group(function() {
      Route::get('/','BikeController@index')->name('index');
      Route::get('/create','BikeController@create')->name('create');
      Route::post('/','BikeController@store')->name('store');
      Route::get('/{bike_id}/edit','BikeController@edit')->name('edit');
      Route::match(['put','patch'],'/{bike_id}','BikeController@update')->name('update');
      Route::delete('/{bike_id}','BikeController@destroy')->name('destroy');
      Route::get('/{bike_id}/destroy_ask','BikeController@destroyAsk')->name('destroy_ask');
      Route::delete('/{bike_id}/rental_place/{rental_place_id}/destroy','BikeController@rentalPlaceDestroy')->name('rental_place_destroy');
      Route::get('/{bike_id}/rental_place/{rental_place_id}/destroy_ask','BikeController@rentalPlaceDestroyAsk')->name('rental_place_destroy_ask');
      Route::post('/{bike_id}/reserve','BikeController@reserve')->name('reserve');
      Route::get('/{bike_id}/reservations','BikeController@reservations')->name('reservations');
      Route::get('/{bike_id}/reservations/{reservation_id}','BikeController@reservation')->name('reservation');
      Route::get('/{bike_id}/reservations/{reservation_id}/cancel','BikeController@reservationCancel')->name('reservation_cancel');
      Route::post('/{bike_id}/reservations/{reservation_id}/cancel','BikeController@reservationCancelSubmit');
      Route::post('/{bike_id}/reservations/{reservation_id}/confirm','BikeController@reservationConfirm')->name('reservation_confirm');
      Route::get('/{bike_id}/publish','BikeController@publish')->name('publish');
      Route::post('/{bike_id}/publish','BikeController@publishSubmit');
      Route::get('/{bike_id}/images','BikeController@images')->name('images');
      Route::post('/{bike_id}/images/upload','BikeController@imageUpload')->name('image_upload');
      Route::delete('/{bike_id}/images/{id}/delete','BikeController@imageDelete')->name('image_delete');
      Route::post('/{bike_id}/images','BikeController@imagesSubmit');
      Route::get('/{bike_id}/rental_period','BikeController@rentalPeriodShow')->name('rental_period');
      Route::post('/{bike_id}/rental_period','BikeController@rentalPeriodSubmit');
      Route::get('/{bike_id}/rental_period_review','BikeController@review')->name('rental_period_review');
      Route::get('{bike_id}/rental_period_exception','BikeController@exception')->name('rental_period_exception');
      Route::post('{bike_id}/rental_period_exception','BikeController@exceptionSubmit');
      Route::post('{bike_id}/rental_period_exception_instant','BikeController@exceptionInstant')->name('rental_period_exception_instant');
      Route::delete('/{bike_id}/rental_period_delete','BikeController@rentalPeriodDeleteAll')->name('rental_period_delete_all');
      Route::delete('/{bike_id}/rental_period_delete/{rp_id}','BikeController@rentalPeriodDelete')->name('rental_period_delete');
      Route::get('/{bike_id}/editors','BikeController@editors')->name('editors');
      Route::post('/{bike_id}/editors/add','BikeController@editorsAdd')->name('editors_add');
      Route::get('/{bike_id}/editors/{user_id}/remove','BikeController@editorsRemoveAsk')->name('editors_remove_ask');
      Route::delete('/{bike_id}/editors/{user_id}/remove','BikeController@editorsRemove')->name('editors_remove');
      Route::get('/{bike_id}/download_tos','BikeController@downloadTOS')->name('download_tos');
    });
  });

  Route::prefix('user')->group(function() {
    Route::name('user.')->group(function() {
      Route::get('edit','UserController@edit')->name('edit');
      Route::post('edit','UserController@update');
      Route::get('delete','UserController@delete')->name('delete');
      Route::delete('delete','UserController@deleteSubmit');
      Route::get('reservations','UserController@reservations')->name('reservations');
      Route::get('reservation/{id}','UserController@reservation')->name('reservation');
      Route::get('reservation/{id}/cancel','UserController@reservationCancel')->name('reservation_cancel');
      Route::post('reservation/{id}/cancel','UserController@reservationCancelSubmit');
      Route::get('confirm_newsletter','UserController@confirmNewsletter')->name('confirm_newsletter');
    });
  });

  Route::prefix('chat')->group(function() {
    Route::name('chat.')->group(function() {
      Route::get('/','ChatController@index')->name('index');
      Route::get('reservation/{id}', 'ChatController@reservation')->name('reservation');
      Route::get('bikeuser/{bike_id}/{user_id}', 'ChatController@bikeuser')->name('bikeuser');
      Route::match(['post','get'],'/{chat_id}', 'ChatController@show')->name('show');
    });
  });

  Route::middleware('throttle:60,1')->group(function() {
    Route::prefix('api')->group(function() {
      Route::name('api.')->group(function() {
        Route::get('/reservations_backend/{bike_id}','BikeController@apiReservationsBackend');
      });
    });
  });
});

Route::prefix('bike')->group(function() {
  Route::name('bike.')->group(function() {
    Route::get('/{bike_id}','BikeController@show')->name('show');
  });
});


