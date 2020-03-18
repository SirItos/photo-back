<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected function all()
    {
        return DB::table('roles')->get();
    }
}
