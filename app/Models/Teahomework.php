<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Teahomework extends BaseModel
{
    public  $table = 'teahomework';
    public  $primaryKey = "HomeWorkNO";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function fdata()
    {
        Teahomework::truncate();
        $list = DB::connection('old')->table('teahomework')->get();
        $f_data = [];
        foreach ($list as $index => $item)
        {
            $data = [];
            $TID = 0;
            $ClassID = 0;
            $SNO = 0;
            $user_info = User::where("old_id",$item->MemberID)->first();
            if($user_info)
            {
                $teacher = Teacher::where("UserID",$user_info->UserID)->first();
                if($teacher)
                {
                    $TID = $teacher->TID;
                }
            }
            $class_info = Classes::where("old_id",$item->ClassID)->first();
            if($class_info)
            {
                $ClassID = $class_info->ClassID;
                $old_class =  DB::connection('old')->table('classes')->where("ClassID",$item->ClassID)->first();
                if($old_class)
                {
                    $seme = Semester::where("SNO",$old_class->SNO)->first();
                    if($seme)
                    {
                        $SNO = $seme->SNO;
                    }
                }
            }
            $data['TID'] = $TID;
            $data['ClassID'] = $ClassID;
            $data['SNO'] = $SNO;
            $data['HomeWorkType'] = $item->HomeWorkType;
            $data['HomeWorkTitle'] = $item->HomeWorkTitle;
            $data['CourseName'] = $item->CourseName;
            $data['BeginTime'] = $item->BeginTime;
            $data['DataLine'] = $item->DataLine;
            $data['HomeMode'] = $item->HomeMode;
            $data['Format'] = $item->Format;
            $data['Description'] = $item->Description;
            $data['HomeWorkView'] = $item->HomeWorkView;
            $f_data[] = $data;
//            $data['Evaluation'] = $item->ID;
//            $data['EvalNO'] = $item->ID;
//            $data['CourseID'] = $item->ID;
//            $data['EvalScore'] = $item->ID;
        }
        Teahomework::insert($f_data);
    }
}
