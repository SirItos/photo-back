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
            $result =  $query->with('user:id,phone','user.userDetails','favorite:id,resource_id','images:id,resource_id,url','statustitle:id,code,status_title')->first();
            if ($result->status === 0 && $request->admin) {
                $result->status = 1;
                $result->save();
            }
            return $result;
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
                         })->whereHas('user', function($query){
                             return $query->where('status',5);
                         })->whereHas('user.roles', function($query){
                             return $query->where('name','provider');
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



    /**
     * Get all resources for admin panel
     * 
     * @param request
     * @return resonse (array)
     */
    protected function getAll(Request $request) 
    {   
        
        $query = Resource::with('statustitle:id,code,status_title')->orderBy($request->sortBy ? $request->sortBy : 'id',
                                   $request->sortDesc ? $request->sortDesc : 'desc' );
        if ($request->search) 
        {
             $query->whereHas('statustitle',function($query) use ($request)  {
                $query->where('status_title','LIKE','%'.$request->search.'%');
             })->orWhere('id','LIKE','%'.$request->search.'%')
                   ->orWhere('title','LIKE','%'.$request->search.'%')
                   ->orWhere('created_at','LIKE','%'.$request->search.'%');
        }
        
       return $query->paginate($request->paginate,['*'],'page',$request->page);
    }

    protected function changeResourceStatus(Request $request)
    {
        $updateParams['status'] = $request->status;
        if ($request->status === 2){
            $updateParams['activated'] = 1;
        } 
        if ($request->status === 3){
            $updateParams['activated'] = 0;
        }
        Resource::whereIn('id',$request->obj)->update($updateParams);  
        return response(['obj' =>Resource::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');
    }
}
 