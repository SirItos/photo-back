<?php

use Illuminate\Http\Request;


Route::post('registrate-user','UserController@createUser');

Route::post('confirm-phone','UserController@confirm');

Route::post('ask-code','SmsTokenController@newCode');

Route::middleware('auth:api')->group(function () {
  Route::post('set-pin','UserController@setPin');
});