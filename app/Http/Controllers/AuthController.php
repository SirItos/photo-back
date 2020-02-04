<?php

namespace App\Http\Controllers;

use App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use GuzzleHttp\Client;  

class AuthController extends Controller
{
    /**
     * Auth API
     * 
     * @return response
     */
    protected function auth(Request $request)
    {
       if (Auth::attempt(
           [
               'phone'=>$request->phone,
               'password'=>$request->code
           ]
       ))  {
           return $this->generateToken([
           'phone'=> $request->phone, 
           'password'=>$request->code
           ]);
       }
       return response('Пользователь с таким номером телефона не зарегистрирован',401);

      
    }

    /**
     * Refresh user token [POST]
     * 
     * @param Request
     * @return response
     */
    protected function refreshToken(Request $request) 
    {
          $http = new Client;
          $oAuth_client = Models\Client::getClient('custom_client');
          $response = $http->post(env('APP_URL') . '/oauth/token', [
              'form_params' => [
                  'grant_type' => 'refresh_token',
                  'refresh_token' => $request->refreshToken,
                  'client_id' => $oAuth_client->id,
                  'client_secret' => $oAuth_client->secret,
                  'scope' => '*',
              ],
          ]);
          return response($response->getBody(),$response->getStatusCode());
    }

       /**
     * Generate oAuth2 token for user (password)
     * 
     * @param array $userInfo
     * @return mixed
     */
    public function generateToken(array $userInfo)
    {
        
        $http = new Client;
        $oAuth_client = Models\Client::getClient('custom_client');
        $response = $http->post(env('APP_URL') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oAuth_client->id,
                'client_secret' => $oAuth_client->secret,
                'username' => $userInfo['phone'],
                'password' => $userInfo['password'],
                'scope' => '*'
            ]
        ]);
        $body = $response->getBody();

        $status = $response->getStatusCode();
        switch ($status) {
            case HttpResponse::HTTP_OK:
            case HttpResponse::HTTP_CREATED:
            case HttpResponse::HTTP_ACCEPTED:
                $user = Models\User::where('phone',$userInfo['phone'])->first();
                if (!$user->phone_verificated) {
                    $user->phone_verificated = Carbon::now();
                    $user->save();
                }
                
                return response($body,$status);
            break;
            default:
                return response('error',401);
            break;
        }
    }

    
}
