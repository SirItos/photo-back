<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommandController extends Controller
{
    protected function callCommand(Request $request) {
        Artisan::call('storage:link');
        dd('The [public/storage] directory has been linked.');
    }
}
