<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class FavoriteController extends Controller
{

    

    public function __construct()
    {
        // $this->auth = new AuthController();
        $this->stats = new StatsController();
    }

   protected function setFavorite(Request $request) 
   {
       $favorite = Favorite::firstOrNew(
           ['user_id' => Auth::id()],
           ['resource_id' => $request->id]
       );
       if ($request->delete) {
            $favorite->delete();
            return response('deleted',200);
       }
       $favorite->save();
       $favorite->refresh()->load('resource');
       if ($favorite->resource->user_id !== Auth::id()) {
           $this->stats->setEvent('favorite',$request->id);
       }
       return response('favorite add',200);
   }

   protected function getFavorite()
   {
    //    ,'resource.images:id,resource_id,url'
       return Favorite::with('resource:id,address')->where('user_id',Auth::id())->get();
   }
}
