<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    protected function setResourceParams(Request $request)
    {
        $id = Auth::id();
        $updParams = [];
        forEach($request->params as $param) {
            $updParams[$param['field']] = $param['value'] ;
        }
        Resource::updateOrCreate(['user_id'=>$id],$updParams);

        return response('Data is update.',200);
    }


    /**
     * Retrive resource params
     * 
     * @return array
     */
    protected function getResourceParams(Request $request)
    {
        return  Resource::where('id',$request->id)->first();
    }


    /**
     * Retrive points in customer view
     * 
     * @return array
     */
    protected function pointsInBound(Request $request)
    {
        $query = Resource::where([['activated',1]])
                         ->whereHas('user.userDetails', function($query) {
                             return $query->where('online',1);
                         });

        if ($request->sw['lat'] > $request->ne['lat']) {
            $query->whereBetween('lat',[$request->ne['lat'], $request->sw['lat']]);
        } else {
            $query->whereBetween('lat',[$request->sw['lat'], $request->ne['lat']]);
        }
        if ($request->sw['lng'] > $request->ne['lng']) {
            $query->whereBetween('long',[$request->ne['lng'], $request->sw['lng']]);
        } else {
            $query->whereBetween('long',[$request->sw['lng'], $request->ne['lng']]);
        }
        return $query->get(['id','lat','long']); 
    }
}
