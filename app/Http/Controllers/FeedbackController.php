<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\UserDetails;
use App\Models\Answer;
use App\Mail\AnswerMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
        $query = Feedback::with('statustitle:id,code,status_title','user','answer')->orderBy($request->sortBy ? $request->sortBy : 'id',
                                        $request->sortDesc ? $request->sortDesc : 'desc' );
                if ($request->search) 
                {
                    $query->whereHas('statustitle',function($query) use ($request)  {
                        $query->where('status_title','LIKE','%'.$request->search.'%');
                    })->orWhereHas('user', function($query) use($request) {
                        $query->where('phone','LIKE','%'.$request->search.'%');  
                    })->orWhere('id','LIKE','%'.$request->search.'%')   
                        ->orWhere('email','LIKE','%'.$request->search.'%');
                        // ->orWhere('created_at','LIKE','%'.$request->search.'%');
                }
                
            return $query->paginate($request->paginate,['*'],'page',$request->page);
    }

    protected function changeFeedbackStatus(Request $request)
    {
  
        Feedback::whereIn('id',$request->obj)->update(['status'=>$request->status]);  
        return response(['obj' =>Feedback::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');

    } 

    protected function getById(Request $request)
    {

        $feedback =  Feedback::where('id',$request->id)->with('statustitle:id,code,status_title','answer')->first();
        $feedback->status = 1;
        $feedback->save();
        $feedback->refresh();
        return $feedback;
    }

    protected function answer(Request $request)
    {

        
        $feedback = Feedback::where('id', $request->id)->first();
       
        $content = (object) array(
            'title'=> $request->title ? $request->title : 'Ответ на обращение',
            'answer'=>$request->answer
        );

        try {
            Mail::to($feedback->email)->send(new AnswerMail($content));
            $feedback->status = 4;
            $feedback->save();
            $feedback->refresh();
            Answer::updateOrCreate(['feedback_id'=>$request->id],['answer'=>$content->answer, 'title'=>$content->title]);
            return response(['status'=>$feedback->status, 'status_title'=>$feedback->statustitle['status_title'], 'answer'=>$feedback->answer],200);
        } catch (\Exception  $th) {
            return response(['message'=>$th->getMessage(),'status'=>'erorr'],413);
        }
        
    }
}
