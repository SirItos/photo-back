<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\UserDetails;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ResourceController extends Controller
{
    protected function setResourceParams(Request $request)
    {
        $id = Auth::id();
        $updParams = [];
        forEach($request->params as $param) {
                $updParams[$param['field']] = $param['value'];      
        }
        $resource = Resource::updateOrCreate(['user_id'=>$id],$updParams);

        return response(['message'=>'Data is update.', 'id' => $resource->id],200);
    }


    /**
     * Retrive resource params
     * 
     * @return array
     */
    protected function getResourceParams(Request $request)
    {
        $query =  Resource::where('id',$request->id);
        if ($request->all) {
            return $query->with('user:id,phone','favorite:id,resource_id','images:id,resource_id,url')->first();
        }
        return $query->first($request->params);
     
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
        
     
        
        if ($request->filters) {
            $query = $this->setFilters($query,$request->filters);
        }
        
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
        $collection = $query->get(['id','lat','long','resource_type']); 
        return $collection->map(function ($item) {
            
            return ['id'=>$item->id, 
                    'lat'=>$item->lat, 
                    'long'=>$item->long,
                    'resource_type'=>$item->resource_type
                    ];
        });
    }

    /**
     * Enable all fillters
     * 
     * @param $query
     * @param array[array] $filters
     * 
     * @return $query
     */
    private function setFilters($query, $filters)
    {
        
        // Resource Type filter start
        $typeArray = [];
        if ($filters['type']['individual']) {
            $typeArray[]=1;
        }
        if ($filters['type']['showroom']) {
            $typeArray[]=0;
        }
        if (!empty($typeArray)) {
            $query->whereIn('resource_type',$typeArray);
        } else {
            $query->where('resource_type',999);
        }

        // Resource Type filter end
        
        // Age range filters start
        $rangeParams = [];
        foreach($filters['age'] as $range) {
            if ($range) {
                $rangeParams[]=$range;
            }
        }
        $query->whereHas('user.userDetails', function($query) use($rangeParams) {
           return $query->whereIn('age_range',$rangeParams);
        });
  
        // Price range filter start

        $query->where('min_cost','>=', $filters['price'][0]);
        $query->where('max_cost','<=', $filters['price'][1]);

        // Price range filter end


        return $query;
    }

    protected function softDelete(Request $request) 
    {
        $resource = Resource::where('id',$request->id)->first();
        
        UserDetails::where('user_id',$resource->user_id)->update(['age_range'=>null, 'display_phone'=>null, 'name'=>null, 'email'=>null]);
        $resource->delete();
        return response(['deleted' => $request->id],200);
    }

    protected function restore(Request $request) 
    {
        Resource::where('id',$request->id)->restore();
    }
}
