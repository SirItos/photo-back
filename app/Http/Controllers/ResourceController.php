<?php

namespace App\Http\Controllers;

use App\Models;
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
        Models\Resource::updateOrCreate(['user_id'=>$id],$updParams);

        return response('Data is update.',200);
    }
}
