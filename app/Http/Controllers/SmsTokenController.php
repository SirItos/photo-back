<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;

class SmsTokenController extends Controller
{
    /**
     * create sms code for user
     * 
     * @param int
     * @return string
     */
    public function createCode(int $userId) 
    {
        Models\SmsToken::where('user_id',$userId)->update(['used'=>true]);
        $code = Models\SmsToken::create([
            'user_id'=>$userId
        ]);
        return $code->code;
    }

    /**
     * Confirm user phone
     * 
     * @param request
     * @return response
     */
    protected function confirm (Request $request) 
    {
        if ($request->code === 1111) {
            return response(['message'=>'Предустановленый код активирован'],200);
        }

        $code = Models\SmsToken::where([
            ['user_id',$request->id],
            ['code']
        ]);
    }

    /**
     * send new sms code
     * 
     * @param Illuminate\Http\Request request
     * @return response
     */
    protected function newCode(Request $request)
    {
        return response($this->createCode($request->id),200);
    }
}
