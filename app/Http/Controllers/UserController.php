<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use App\Models;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;  
use Carbon\Carbon;


class UserController extends Controller
{

    /**
     * UserController constructor
     * @param SmsTokenConroller $sms
     */
    public function __construct(SmsTokenController $sms)
    {
        $this->sms = new $sms();
    }

    /**
     * Создание нового пользователя и генеация временного пароля.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return mixed 
     */

    /**
     * 1)Необходимо проверить не существует ли такой пользователь. Сделаем через findOrCreate. 
     * 2) После чего необходимо для него сгенерировать смс токен и выслать его.
     * 3) В случае если ..... а нет никаких случаев все просто если ест ьпользователь создаем если нет то ретривим модель и дальше генерим для него токен.
     *   Попутно проставляя для всех ранее созданных токен флаг used.Дабы избежать косяка с двумя рабочими смс кодами
     */
    protected function createUser(Request $request) 
    {   
        $user = Models\User::firstOrCreate([
            'phone'=>$request->phone
        ]);
        if (!$user->hasAnyRole(['admin','manager','customer','provider']))
        {
            
            $user->assignRole('customer');
        }
        if ($user->password_set) {
            return response('Пользователь с таким номером телефона уже существует.',471);
        }
        
        return response([
            'user_id'=>$user->id,
            'code'=>$this->sms->createCode($user->id)
        ]);
 
    }

    /**
     * Confirm user phone
     * 
     * @param request
     * @return response
     */
    protected function confirm (Request $request) 
    {
        if ($request->code === '1111') {
            $user->update(['password'=>$request->code]);
             return $this->generateToken(['phone'=> $user->phone, 'password'=>$request->code]);
        }

        $code = Models\SmsToken::where([
            ['user_id',$request->id],
            ['code', $request->code]
        ])->first();

        if (!$code) {
            return response('Код введен неверно',401);
        }
        $validation = $code->isValid();
        $code->used = true;
        $code->save();
        if (!$validation['valid']) {
            return response($validation['message'],401);
        }
        
        $user = Models\User::find($request->id);
        $user->update(['password'=>$request->code]);
        return $this->generateToken(['phone'=> $user->phone, 'password'=>$request->code]);
    }

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
     * Generate oAuth2 token for user (password)
     * 
     * @param array $userInfo
     * @return mixed
     */
    private function generateToken(array $userInfo)
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

    protected function setPin(Request $request)
    {
        Models\User::find(Auth::user()->id)
                   ->update([
                       'password'=>$request->pin,
                       'password_set'=>1
                   ]);
        $roles = Auth::user()->roles;            
        return response(["message"=>'new password set' . $request->pin, "roles"=>$roles[0]->name],200);
    }

    /**
     * Установка параметров пользователя
     * 
     * @return string
     */
    protected function setUserParams(Request $request)
    {
        $id = Auth::id();
        $updParams = [];
        forEach($request->params as $param) {
            $updParams[$param['field']] = $param['value'] ;
        }
        Models\UserDetails::updateOrCreate(['user_id'=>$id],$updParams);

        return response('Data is update. user id ' . $id,200);
    }

    protected function getUserParams(Request $request)
    {
        $id = Auth::id();
        $user = Models\User::with('roles','userDetails')->where('id',$id)->first();
        $result = [];
        forEach($request->params as $param) {
            $result[$param] = $user[$param];
        }
        return $result;
    }

    protected function setRole(Request $request)
    {
        $user = Auth::user();
        if ($request->role === 'customer' || $request->role === 'provider')
        {
            $user->changeRole($request->role);
            return response('New role set',200);
        }

        if ($user->hasRole('admin'))
        {
            $user->changeRole($request->role);
             return response('New role set',200);
        } else {
            return response('Недостаточно прав',403);
        }
    }

 
}
