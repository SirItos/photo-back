<?php

use Illuminate\Database\Seeder;
use App\Models\StatusCode;

class fill_status_table extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StatusCode::create(
            ['code'=>3,'status_title'=>'отклонен']
        );
    }
}
