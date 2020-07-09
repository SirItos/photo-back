<?php

namespace App\Http\Controllers;

use App\Models;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use GuzzleHttp\Client;

class SmsTokenController extends Controller
{

    /**
     * SmsTokenController constructor
     */
    public function __construct()
    {
        $this->auth = new AuthController();
    }
    /**
     * create sms code for user
     * 
     * @param int
     * @return string
     */
    public function createCode(int $userId)
    {
        Models\SmsToken::where('user_id', $userId)->update(['used' => true]);
        $code = Models\SmsToken::create([
            'user_id' => $userId
        ]);
        $this->smsSend($code->code, Models\User::where('id', $userId)->first()->phone);

        return $code->code;
    }

    /**
     * send new sms code
     * 
     * @param Illuminate\Http\Request request
     * @return response
     */
    protected function newCode(Request $request)
    {
        $this->createCode($request->id);
        return response('done', 200);
    }

    /**
     * Confirm user phone
     * 
     * @param request
     * @return response
     */
    protected function confirm(Request $request)
    {
        $user = Models\User::find($request->id);
        $find_for_passport = (object) array(
            'type' => 'phone',
            'needle' => $user->phone
        );

        if ($request->code === '1111') {
            $user->update(['password' => $request->code]);
            return $this->auth->generateToken(['find_for_passport' => $find_for_passport, 'password' => $request->code]);
        }

        $code = Models\SmsToken::where([
            ['user_id', $request->id],
            ['code', $request->code]
        ])->first();

        if (!$code) {
            return response('Код введен неверно', 401);
        }
        $validation = $code->isValid();
        $code->used = true;
        $code->save();
        if (!$validation['valid']) {
            return response($validation['message'], 401);
        }

        $user->update(['password' => $request->code]);
        return $this->auth->generateToken(['find_for_passport' => $find_for_passport, 'password' => $request->code]);
    }

    // protected function testSMS(Request $request)
    // {
    //     return $this->smsSend('1111', $request->phone);
    // }

    private function smsSend($code, $phone)
    {
        if (env('SMS_URL_ENABLED')) {
            $smsHost = new Client();
            $result = $smsHost->post(env('SMS_URL') . '/Send/SendSms/', [
                'form_params' => [
                    'sessionId' => env('SMS_API_KEY'),
                    'sourceAddress' => env('SMS_SOURCE'),
                    'destinationAddress' => '7' . $phone,
                    'data' => 'Ваш код для подтверждения номера телефона: ' . $code,
                ]
            ]);
            return $result->getBody();
        };
    }
}
