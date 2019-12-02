<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Создание нового пользователя и генеация временного пароля.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return string 
     */
    protected function createUser(Request $request) 
    {   
        $userCheck = User::where('phone',$request->phone)-first();
        if ($userCheck->phone_verificated) {
            return response(['error'=>[
                'message'=>'User already exist',
                'code'=>'01'
            ]],200);
        }
        // SmsToken::create([
        //     'user_id'=>
        // ])
    }

}
