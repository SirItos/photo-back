<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class seed_admin extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
  
        $user = new User();
        $user->login = 'admin';
        $user->password = "admin";
        // $user->name = 'Администратор';
        $user->save();
        $user->assignRole('admin');
    }
}
