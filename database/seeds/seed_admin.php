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
        $user->phone = 12321312;
        $user->save();
    }
}
