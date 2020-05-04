<?php

use Illuminate\Database\Seeder;
use App\Models\StatusCode;

class statu_titles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            ['code'=>0,'title'=>'новый'],
            ['code'=>1,'title'=>'просмотрен'],
            ['code'=>2,'title'=>'проверена'],
            ['code'=>3,'title'=>'отклонено'],
            ['code'=>4,'title'=>'отработано'],
            ['code'=>5,'title'=>'активен'],
            ['code'=>6,'title'=>'заблокировано'],
            ['code'=>7,'title'=>'удалена пользователем']
        ];
        foreach($arr as $item) {
            StatusCode::create([
                'code'=>$item['code'],
                'status_title'=>$item['title']
            ]);
        };
    }
}
