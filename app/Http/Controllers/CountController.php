<?php

namespace App\Http\Controllers;

use App\Models;
use Illuminate\Http\Request;

class CountController extends Controller
{
    /**
     * Get counter for all position to check new records
     * 
     * @param Request $request
     * @return response (array,status)
     */
    protected function getCount(Request $request) 
    {
        return response([
            'resources'=>Models\Resource::where('status',0)->get()->count(),
            'feedback'=>Models\Feedback::where('status',0)->get()->count()
        ]);
    }
}
