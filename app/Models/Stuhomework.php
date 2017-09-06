<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Stuhomework extends BaseModel
{
    public  $table = 'stuhomework';
    public  $primaryKey = "StuWorkNO";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function fdata()
    {
        Stuhomework::truncate();
        $list = DB::connection('old')->table('stuhomework')->get();
        $f_data = [];
        foreach ($list as $index => $item)
        {
            $data = [];
            $StuID = 0;
            $user_info = User::where("old_id",$item->MemberID)->first();
            if($user_info)
            {
                $teacher = Student::where("UserID",$user_info->UserID)->first();
                if($teacher)
                {
                    $StuID = $teacher->StuID;
                }
            }

            $data['StuID'] = $StuID;
            $data['HomeWorkNO'] = $item->ID;
            $data['TeaEval'] = $item->ID;
            $data['Score'] = $item->ID;
            $data['ViewCounter'] = $item->ID;
            $data['FilePath'] = $item->ID;
            $data['Status'] = $item->ID;
            $data['HomeMode'] = $item->ID;
            $data['SubTime'] = $item->ID;
            $f_data[] = $data;
        }
        Stuhomework::insert($f_data);
    }
}
