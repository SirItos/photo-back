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
        Role::create(['guard_name'=>'api','name' =>'admin','name_ru'=>'Администратор']);
        Role::create(['guard_name'=>'api','name'=>'manager','name_ru'=>'менеджер']);
        Role::create(['guard_name'=>'api','name' =>'customer','name_ru'=>'Пользователь (муж)']);
        Role::create(['guard_name'=>'api','name' =>'provider','name_ru'=>'Пользователь (жен)']);
    }
}
