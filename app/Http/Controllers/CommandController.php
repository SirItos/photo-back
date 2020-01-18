<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommandController extends Controller
{
    protected function callCommand(Request $request) {
        Artisan::call($request->command);
        dd('Jobs done');
    }
}
