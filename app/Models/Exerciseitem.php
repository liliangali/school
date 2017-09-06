<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Exerciseitem extends BaseModel
{
    public $table = 'exerciseitem';
    public  $timestamps = false;//去掉update_time等三个字段

    public static function fdata()
    {
        $testiteminfo_list = DB::connection('old')->table('testiteminfo')->get();
        collect($testiteminfo_list)->map(function($item){
            $data = [];
            $exitem = Exercise::where("old_id",$item->ExNO)->first();
            if(!$exitem)
            {
                return $item;
            }
            $data['ExNO'] = $exitem->ExNO;
            $data['ItemIndex'] = $item->ItemIndex;
            $data['Point'] = $item->Point;
            $data['AvgSpendTime'] = $item->AvgSpendTime;
            $data['AnsSpendTime'] = $item->AnsSpendTime;
            $data['TrueRate'] = $item->TrueRate;
            $data['TrueNum'] = $item->TrueNum;
            $data['AnsNum'] = $item->AnsNum;
            $data['Url'] = $item->Picture;
            $data['Answer'] = $item->Answer;
            $data['Type'] = $item->Type;
            $data['Question'] = $item->Question;
            Exerciseitem::insert($data);
        });
    }

}
