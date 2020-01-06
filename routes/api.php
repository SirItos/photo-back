<?php

use Illuminate\Http\Request;


Route::post('registrate-user','UserController@createUser');

Route::post('confirm-phone','SmsTokenController@confirm');

Route::post('ask-code','SmsTokenController@newCode');

Route::post('auth','AuthController@auth');

Route::post('points','ResourceController@pointsInBound');

Route::post('get-resource-params','ResourceController@getResourceParams');


Route::middleware('auth:api')->group(function () {
  Route::post('set-pin','UserController@setPin');
  Route::post('user-params','UserController@getUserParams');
  Route::post('set-role','UserController@setRole');
  Route::post('set-user-details','UserController@setUserParams');

  Route::post('set-resource-params','ResourceController@setResourceParams');

  Route::post('set-favorite','FavoriteController@setFavorite');


  Route::post('geosearch','GeocoderController@Geosearch');
  
});