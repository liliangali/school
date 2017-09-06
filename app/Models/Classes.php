<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Classes extends BaseModel
{    public $table = 'classes';
    public $primaryKey = "ClassID";
    public  $timestamps = false;//去掉update_time等三个字

    public static function fdata()
    {
        $classes_list = DB::connection('old')->table('classes')->get();
        Classes::truncate();
        foreach ($classes_list as $index => $item)
        {
            $classinfo_info = DB::connection('old')->table('classinfo')->where("CNO",$item->CNO)->first();
            if(!$classinfo_info)
            {
                continue;
            }
            $create_time = "";
            if(date('n') < 9)
            {
                $create_time = date("Y") - $classinfo_info->GradeName;
            }
            else
            {
                $create_time = date("Y") - $classinfo_info->GradeName + 1;
            }
            $TID = 0;
            if($item->MemberID)
            {
                $member_info = DB::connection('old')->table('member')->where("MemberID",$item->MemberID)->first();
                if($member_info)
                {
                    $LoginID = $member_info->LoginID;
                    $teacher = Teacher::where("CivilID",$LoginID)->first();
                    if($teacher)
                    {
                        $TID = $teacher->TID;
                    }
                }
            }
            $ClassName = isset($classinfo_info->ClassName) ? $classinfo_info->ClassName : '';
            $new_class_info = Classes::where("ClassName",$ClassName)->first();
            if($new_class_info)
            {
                $new_class_info->old_id = $new_class_info->old_id.','.$item->ClassID;
                $new_class_info->save();
                continue;
            }

            $data['SchoolID'] = 26;
            $data['old_id'] = $item->ClassID;
            $data['TID'] = $TID;
            $data['CreatTime'] = $create_time;
            $data['ClassName'] = $ClassName;
            Classes::insert($data);
        }
    }

}


