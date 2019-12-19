<?php

use Illuminate\Http\Request;


Route::post('registrate-user','UserController@createUser');

Route::post('confirm-phone','UserController@confirm');

Route::post('ask-code','SmsTokenController@newCode');

Route::post('auth','UserController@auth');

Route::middleware('auth:api')->group(function () {
  Route::post('set-pin','UserController@setPin');
  Route::post('user-params','UserController@getUserParams');
  Route::post('set-role','UserController@setRole');
});