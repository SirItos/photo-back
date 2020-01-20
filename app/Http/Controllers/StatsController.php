<?php

namespace App\Http\Controllers;

use App\Models\Stats;
use App\Models\Resource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatsController extends Controller
{
    

    public function setEvent($event,$resourse_id)
    {
        $resourse = Resource::where('id',$resourse_id)->first();
        Stats::create(['owner_id' =>$resourse->user_id,'event'=>$event]);
    }

    protected function setStat(Request $request)
    {
        $this->setEvent($request->event,$request->resourse_id);
    }

    protected function getStats(Request $request) 
    {
      
        $methodName = $request->period;

        $period = $this->$methodName($request->timestamp);
        
        $query = Stats::groupBy('event')
                               ->selectRaw('count(*) as  total,event');
                                
        $query->whereBetween('created_at',$period);
        
        $collection = $query->where('owner_id',$request->id)->get();
        $result = $collection->reduce(function($result,$group) {
            $result[$group['event']] = $group['total'];
            return $result;
        },[]);
        return $result;
    }


    private function today($timestamp) 
    {
      $timestamp = date('Y-m-d H:i:s');
      $date = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, '+3');
      $start = $date->startOfDay();
      $end = $start->copy()->endOfDay();  
      return array('start'=>$start,'last'=>$end);
    }


    private function quart() 
    {
        $quart = Carbon::now()->quarter;
        return array('start'=>$quart->firstOfQuarter(),'last'=>$quart->lastOfQuarter());
    }
}