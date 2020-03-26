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
            'code'=>$request->no_sms ? null : $this->sms->createCode($user->id)
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
        $id = $request->id ? $request->id : Auth::id();
        $user = Models\User::with('roles','userDetails','resource','resource.images','statustitle','notification')->where('id',$id)->first();
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

    protected function getAll(Request $request)
    {
        $query =  Models\User::with('roles','userDetails','statustitle')
        ->orderBy('id',  'desc' )->withTrashed();

            if ($request->search) 
                {
                    $query->whereHas('roles',function($query) use ($request)  {
                        $query->where('name','LIKE','%'.$request->search.'%')
                        ->orWhere('name_ru','LIKE','%'.$request->search.'%');
                    })->orWhereHas('userDetails', function($query) use($request) {
                        $query->where('phone','LIKE','%'.$request->search.'%');
                    })->orWhereHas('statustitle',function($query) use ($request) {
                        $query->where('status_title','LIKE','%'.$request->search.'%');
                    })->orWhere('id','LIKE','%'.$request->search.'%')   
                        ->orWhere('login','LIKE','%'.$request->search.'%')
                        ->orWhere('phone','LIKE','%'.$request->search.'%');
                        // ->orWhere('created_at','LIKE','%'.$request->search.'%')
                }

               
        if (isset($request->filter)) {
            if( array_key_exists ('role', (array)$request->filter)) {
                $query->whereHas('roles',function($query) use ($request) {
                    $query->whereIn('id',$request->filter['role']);
                });
            }
            if( array_key_exists ('status', (array)$request->filter)) {
                $query->whereHas('statustitle',function($query) use ($request) {
                    $query->whereIn('code',$request->filter['status']);
                });
            }
        }
        return $query->paginate($request->paginate,['*'],'page',$request->page);
    }


     protected function changeUserStatus(Request $request)
    {
        //TODO CHECK USER ROLE 
        Models\User::whereIn('id',$request->obj)->update(['status'=>$request->status]);  
        return response(['obj' =>Models\User::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');
                       
    }


    protected function editUserAdmin(Request $request)
    {
        $newAttr = array( 
            Auth::user()->hasRole(['admin','manager']) ? 'login' : 'phone' => $request->login,
            'status'=> $request->status ? 5 : 6
        );
        
        $details = array (
           'name'=>$request->details['name'],
           'email'=>$request->details['email'],

        );
        if ($request->password) {
            $newAttr['password'] = $request->password;
        };



        $user = Models\User::where('id',$request->id)->first();
        $detail = Models\UserDetails::updateOrCreate(['user_id'=>$request->id],$details);
        $user->syncRoles([$request->role['value']]);
        $user->update($newAttr);
      
        return $user->refresh()->load('userDetails')->userDetails->name;        
    }

    protected function createUserAdmin(Request $request)
    {
        $user = new Models\User();
        $user->login = $request->login;
        $user->password = $request->password;
        $user->status = 5;
        $user->save();
        $user->refresh();
        $user->syncRoles([$request->role['value']]);
        $details = array (
           'name'=>$request->details['name'],
           'email'=>$request->details['email'],

        );
        $detail = Models\UserDetails::updateOrCreate(['user_id'=>$user->id],$details);

    }

    protected function rememberPassword(Request $request) 
    {
        $user = Models\User::where('phone', $request->phone)->first();
        if ($user === null) {
            return response('Пользователя с таким номером не зарегестрирован',403);
        }
        return response([
            'user_id'=>$user->id,
            'code'=>$request->no_sms ? null : $this->sms->createCode($user->id)
        ]);
                
    }

    protected function sawNotification(Request $request) 
    {
        Models\Notifications::where('id',$request->id)->delete();
    }
    
 
}
