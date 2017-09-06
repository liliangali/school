<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Answerinfo extends Model
{
    public $table = 'answerinfo';
    public  $timestamps = false;//去掉update_time等三个字段


    public  static  function getRate($ExNO,$ItemIndex)
    {
        $return = [];
        $anw = Answerinfo::where("ExNO",$ExNO)->where("ItemIndex",$ItemIndex)->get()->toArray();
        if(!$anw)
        {
            return $return;
        }
        $AnsNum = 0;
        $TrueNum = 0;
        $TrueRate = 0;
        foreach ((array)$anw as $index => $item)
        {
            
        }

    }

    public  static  function fdata()
    {
        DB::connection('old')->table('answerinfo')->chunk(1000,function ($list){
            $fdata = [];
            foreach ($list as $index => $item)
            {
                $data = [];
                $StuID = 0;
                $ex_info = Exercise::where("old_id",$item->ExNO)->first();
                if(!$ex_info)
                {
                    return;
                }
                $ExNO = $ex_info->ExNO;
                $user_info = User::where("UserID",$item->MemberID)->first();
                if($user_info)
                {
                    $student = Student::where("UserID",$user_info->UserID)->first();
                    if($student)
                    {
                        $StuID = $student->StuID;
                    }
                }
                $data['StuID'] = $StuID;
                $data['ItemIndex'] = $item->ItemIndex;
                $data['ExNO'] = $ExNO;
                $data['SpendTime'] = $item->SpendTime;
                $data['Selection'] = $item->Selection;
                $data['Score'] = $item->Score;
                $fdata[] = $data;
            }
            Answerinfo::insert($fdata);
        });

    }
}
