<?php

use Illuminate\Http\Request;


Route::post('test-sms', 'SmsTokenController@testSMS');

Route::post('registrate-user', 'UserController@createUser');

Route::post('confirm-phone', 'SmsTokenController@confirm');

Route::post('ask-code', 'SmsTokenController@newCode');

Route::post('auth', 'AuthController@auth');

Route::post('points', 'ResourceController@pointsInBound');

Route::post('get-resource-params', 'ResourceController@getResourceParams');

Route::post('feedback', 'FeedbackController@setFeedback')->middleware('reCaptcha');

Route::post('event', 'StatsController@setStat');

Route::post('refresh-token', 'AuthController@refreshToken');

Route::get('alt-locate', 'GeocoderController@ipLocation');

Route::post('reset-password', 'UserController@rememberPassword');


// Route::post('execute-command',function(Request $request) {

//   try{

//      Artisan::call($request->body);
//      dd('The [public/storage] directory has been linked.');
//   }
//    catch (Exception $e) {
//      return $e;
//       Response::make($e->getMessage(), 500);
//     }
// });
/* TODO ЗАЩИТА ЭТОГО МЕТОДА ЧЕРЕЗ ПАРОЛИ
 *
 * апи для вызовва cmd команд на сервере (т.к. необходим php 7+, а через панель ISP доступен глобальный 5.3)
 */
// 

Route::middleware('auth:api')->group(function () {

  Route::post('set-pin', 'UserController@setPin');
  Route::post('user-params', 'UserController@getUserParams');
  Route::post('set-role', 'UserController@setRole');
  Route::post('set-user-details', 'UserController@setUserParams');
  Route::post('feedback-auth', 'FeedbackController@setFeedbackAuth')->middleware('reCaptcha');
  Route::get('role-list', 'RoleController@all');
  Route::post('saw-notification', 'UserController@sawNotification');
  Route::post('restore-resources', 'ResourceController@restore');
  /**
   * Route fors provider
   */
  Route::group(['middleware' => ['role:provider']], function () {
    Route::post('set-resource-params', 'ResourceController@setResourceParams');
    Route::post('upload-images', 'ResourceIamgeController@upload');
    Route::post('delete-images', 'ResourceIamgeController@delete');
    Route::get('get-resourece-images', 'ResourceIamgeController@getSavedImages');
    Route::post('get-events', 'StatsController@getStats');
    Route::post('geosearch', 'GeocoderController@Geosearch');
    Route::post('delete-resource', 'ResourceController@softDelete');
  });

  /**
   * Routes for customer
   */
  Route::group(['middleware' => ['role:customer']], function () {
    Route::post('set-favorite', 'FavoriteController@setFavorite');
    Route::get('get-favorite', 'FavoriteController@getFavorite');
  });


  /**
   * Routes for admin, manager
   */
  Route::group(['middleware' => ['role:admin|manager']], function () {
    Route::get('get-counter', 'CountController@getCount');
    Route::post('/admin/resources', 'ResourceController@getAll');

    Route::post('/admin/resources/status', 'ResourceController@changeResourceStatus');

    Route::post('/admin/feedback', 'FeedbackController@getFeedback');
    Route::post('/admin/feedback/status', 'FeedbackController@changeFeedbackStatus');
    Route::post('/admin/feedback/details', 'FeedbackController@getById');
    Route::post('/admin/feedback/answer', 'FeedbackController@answer');

    Route::post('/admin/user', 'UserController@getAll');
    Route::post('/admin/user/status', 'UserController@changeUserStatus');
    Route::post('/admin/user/edit', 'UserController@editUserAdmin');
    Route::post('/admin/user/create', 'UserController@createUserAdmin');
  });
});
