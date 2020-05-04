<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Notifications;
use App\Models\UserDetails;
use App\Models\Favorite;
use App\Mail\NotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;


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
        if ($resource->status === 3 || $resource->status === 1) {
            $resource->update(['status'=>0]);
        }
        return response(['message'=>'Data is update.', 'id' => $resource->id],200);
    }


    /**
     * Retrive resource params
     * 
     * @return array
     */
    protected function getResourceParams(Request $request)
    {
        $query =  Resource::where('id',$request->id)->withTrashed();
        
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
        
        // UserDetails::where('user_id',$resource->user_id)->update(['age_range'=>null, 'display_phone'=>null, 'name'=>null, 'email'=>null]);
        $resource->update(['status'=>7]);
        $resource->delete();
        return response(['deleted' => $request->id],200);
    }

    protected function restore(Request $request) 
    {
        
        
        Resource::whereIn('id',$request->obj)->restore();
        Resource::whereIn('id',$request->obj)->update(
            ['activated'=> Auth::user()->hasRole(['admin','manager']) ? 1 : 0, 
            'status'=> Auth::user()->hasRole(['admin','manager']) ? 2 : 0]);
       
        return  response(['obj' =>Resource::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');
    }



    /**
     * Get all resources for admin panel
     * 
     * @param request
     * @return resonse (array)
     */
    protected function getAll(Request $request) 
    {   
        
        $query = Resource::with('statustitle:id,code,status_title','user.userDetails:id,user_id,name')
                                ->withTrashed()
                                ->orderBy($request->sortBy ? $request->sortBy : 'id',
                                   $request->sortDesc ? $request->sortDesc : 'desc' );
        if ($request->search) 
        {
             $query->whereHas('statustitle',function($query) use ($request)  {
                $query->where('status_title','LIKE','%'.$request->search.'%');
             })->orWhere('id','LIKE','%'.$request->search.'%')
            //    ->orWhere('title','LIKE','%'.$request->search.'%')
               ->orWhereHas('user.userDetails', function($query) use ($request) {
                    $query->where('name','LIKE','%'.$request->search.'%');
               });
//                   ->orWhere('created_at','LIKE','%'.$request->search.'%');
        }

          if (isset($request->filter)) {
            if( array_key_exists ('status', (array)$request->filter)) {
                $query->whereHas('statustitle',function($query) use ($request) {
                    $query->whereIn('code',$request->filter['status']);
                });
            }
        }
        
       return $query->paginate($request->paginate,['*'],'page',$request->page);
    }

    protected function changeResourceStatus(Request $request)
    {
        $updateParams['status'] = $request->status;
        if ($request->status === 2){
            $updateParams['activated'] = 1;
            $this->saveSendUserNotification($request->obj, $request->status);
        } 
        // return $request->status;
        if ($request->status === 3 || $request->status === 6){
            $updateParams['activated'] = 0;
            $this->saveSendUserNotification($request->obj, $request->status, $request->reason);
        }
        
        Resource::whereIn('id',$request->obj)->update($updateParams);  
        return response(['obj' =>Resource::with('statustitle:id,code,status_title')
                        ->whereIn('id',$request->obj)->get()],'200');
    }

    private function saveSendUserNotification(array $ids, $status, $reason = null) 
    {
        foreach($ids as $id) {
            $user = Resource::where('id',$id)->with('user.userDetails')->first();
            if(isset($user->user->userDetails->email)) {
                $content = [];
                $title = 'Анкета активирована!';
                if ($status === 6 ) {
                    $title = 'Анкета заблокирована!';
                    $content['title'] = 'Анкета заблокирована!';
                    $content['result'] = 'заблокирована!';
                    $content['good'] = false;
                    $content['reason'] = isset($reason) ? $reason : 'Без указания причины. По всем вопросам обращайтесь по адресу info.bazabab@gmail.com';
                } else if ($status === 3) {
                    $title = 'Анкета отклонена!';
                    $content['title'] = 'Анкета не прошла проверку';
                    $content['result'] = 'не прошла проверку';
                    $content['good'] = false;
                    $content['reason'] = isset($reason) ? $reason : 'Без указания причины. По всем вопросам обращайтесь по адресу info.bazabab@gmail.com';
                } else {
                    $content['title'] = 'Анкета активирована!';
                    $content['result'] = 'активирована!';
                    $content['good'] = true;
                }
               try {
                    Mail::to($user->user->userDetails->email)->send(new NotificationMail((object) $content)); 
                    $notificationsOlad = Notifications::where('user_id',$user->user->id)->delete();
                    if ($user->status === 0 || $user->status === 1 || $user->status === 2) {
                        Notifications::create([
                            'title'=>$title,
                            'status'=>$status,
                            'user_id' => $user->user->id,
                            'description'=>$reason ? $reason : null
                        ]);
                    }
                } catch (\Exception  $th) {
                    // Тут должен быть обработчик ошибки
                    // ключевой момент должен быть
                } 
            }
        }
         
    }
}
 