<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class FavoriteController extends Controller
{
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
       return response('favorite add',200);
   }

   protected function getFavorite()
   {
       return Favorite::where('user_id',Auth::id())->get();
   }
}
