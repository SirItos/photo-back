<?php

use Illuminate\Database\Seeder;
use App\Models\UserDetails;

class ChangeAge extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserDetails::where('age_range','20-30')->update(['age_range'=>'18-30']);
    }
}
