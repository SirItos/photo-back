<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{

    /**
     * Set feedback for auth user
     * 
     * @return void
     */

    protected function setFeedbackAuth(Request $request)
    {
        
        if (Auth::user()->hasRole('customer')) {
            $userDetails = UserDetails::where('user_id',Auth::id())->first();
            if  (!$userDetails->email) {
                $userDetails->email = $request->email;
                $userDetails->save();
            }
        }
        
        Feedback::create(['user_id'=>Auth::id(),'email'=>$request->email, 'description'=>$request->description]);
    }

    /**
     * Set feedback for anonymous user
     * 
     * @return void 
     */

    protected function setFeedback(Request $request)
    {
        Feedback::create(['email'=>$request->email, 'description'=>$request->description]);
        
        return response(['message' => 'сообщение отправлено'],200);
    }

    protected function getFeedback(Request $request) 
    {
        $query = Feedback::with('statustitle:id,code,status_title','user')->orderBy($request->sortBy ? $request->sortBy : 'id',
                                        $request->sortDesc ? $request->sortDesc : 'desc' );
                if ($request->search) 
                {
                    $query->whereHas('statustitle',function($query) use ($request)  {
                        $query->where('status_title','LIKE','%'.$request->search.'%');
                    })->orWhereHas('user', function($query) use($request) {
                        $query->where('phone','LIKE','%'.$request->search.'%');  
                    })->orWhere('id','LIKE','%'.$request->search.'%')   
                        ->orWhere('email','LIKE','%'.$request->search.'%')
                        ->orWhere('created_at','LIKE','%'.$request->search.'%');
                }
                
            return $query->paginate($request->paginate,['*'],'page',$request->page);
    }

     protected function changeFeedbackStatus(Request $request)
    {
  
        Feedback::whereIn('id',$request->obj)->update(['status'=>$request->status]);  
        return response(['obj' =>Feedback::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');

                        // TODO add email sender
    }
}
