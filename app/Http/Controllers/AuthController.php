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
        $attempt = $request->type === 'phone' ? 
           [
               'phone'=>$request->phone,
               'password'=>$request->code
           ] :
           [
               'login'=>$request->login,
               'password'=>$request->password
           ];
       if (Auth::attempt($attempt))  {
           if (Auth::user()->status !== 5) {
                return response('Пользователь заблокирован',403); 
            }
           return $this->generateToken([
           'find_for_passport'=> (object) array(
               'type'=>$request->type,
               'needle' => isset($request->phone) ? $request->phone :  $request->login
            ),
           'password'=>isset($request->code) ? $request->code : $request->password
           ]);
       };
       
       $message = $request->type === 'phone'? 'Пользователь с таким номером телефона не зарегистрирован' : 'Неверный логин или пароль';
       $status = 401;
       
       if ($this->checkUserExist(Auth::id())){
          $message = 'Неверный номер телефона или пароль';
          $status = 403;  
       }
       return response($message,$status);

      
    }

    private function checkUserExist($id) {
        return (Models\User::where('id',$id)->first() === null);
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
        
        if (!$oAuth_client) {
            return response('Что-то пошло не так =(',500);
        }
        $response = $http->post(env('APP_URL') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oAuth_client->id,
                'client_secret' => $oAuth_client->secret,
                'username' => $userInfo['find_for_passport'],
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
                if ($userInfo['find_for_passport']->type === "phone") {
                $user = Models\User::where('phone',$userInfo['find_for_passport']->needle)->first();
                    if (!$user->phone_verificated) {
                        $user->phone_verificated = Carbon::now();
                        $user->save();
                    }
                }    
                return response($body,$status);
            break;
            default:
                return response('error',401);
            break;
        }
    }

    
}
