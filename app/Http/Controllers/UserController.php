<?php

namespace App\Http\Controllers;

use App\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
     * Set user pin for auth
     */
    protected function setPin(Request $request)
    {
        Models\User::find(Auth::user()->id)
                   ->update([
                       'password'=>$request->pin,
                       'password_set'=>1
                   ]);         
        Models\UserDetails::updateOrCreate(['user_id'=>Auth::id()],['display_phone'=>Auth::user()->phone]);
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
        
        $user = Models\User::with('roles','userDetails','resource','resource.images')->where('id',$id)->first();
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
