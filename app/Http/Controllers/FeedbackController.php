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
    }

    protected function getFeedback(Request $request) 
    {

    }
}
