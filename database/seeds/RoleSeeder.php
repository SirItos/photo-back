<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['guard_name'=>'api','name' =>'admin']);
        Role::create(['guard_name'=>'api','name'=>'manager']);
        Role::create(['guard_name'=>'api','name' =>'customer']);
        Role::create(['guard_name'=>'api','name' =>'provider']);
    }
}
