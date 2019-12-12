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
            return response(['message'=>'Предустановленый код активирован'],200);
        }

        $code = Models\SmsToken::where([
            ['user_id',$request->id],
            ['code', $request->code]
        ])->first();

        if (!$code) {
            return response(['message'=>'Указанный код не сущуствует'],401);
        }
        $validation = $code->isValid();
        if (!$validation['valid']) {
            return response($validation,401);
        }
        $user = Models\User::find($request->id);
        $user->update(['password'=>$request->code]);
        return $this->generateToken(['phone'=> $user->phone, 'password'=>$request->code]);
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
                Models\User::where('phone',$userInfo['phone'])
                            ->update(['phone_verificated'=>Carbon::now()]);
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
                       'password'=>$request->pass,
                       'password_set'=>1
                   ]);
        return response('new password set',200);
    }

    protected function getUserParams(Request $request)
    {
        $user = Auth::user();
        $result = [];
        forEach($request->params as $param) {
            $result[$param] = $user[$param];
        }
        return $result;
    }

 
}
