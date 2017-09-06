<?php

namespace App\Models;


use Illuminate\Support\Facades\DB;

class Course extends BaseModel
{
    public $table = 'course';
    public $primaryKey = "CourseID";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function fdata()
    {
        $course_list = DB::connection('old')->table('course')->get();
        Course::truncate();
        foreach ($course_list as $index => $item)
        {
            $TID = 0;
            $ClassID = 0;
            $SNO = 0;
            $member_info = DB::connection('old')->table('member')->where("MemberID",$item->MemberID)->first();
            if($member_info) 
            {
                $teacher = Teacher::where("CivilID",$member_info->LoginID)->first();
                if($teacher)
                {
                   $TID = $teacher->TID;
                }
            }
            $data['TID'] = $TID;
//            >whereRaw('FIND_IN_SET(?,Tags)', [$colname])
            $class_info = Classes::whereRaw('FIND_IN_SET(?,old_id)', [$item->ClassID])->first();
            if($class_info)
            {
                $ClassID = $class_info->ClassID;
            }
            $data['ClassID'] = $ClassID;

            $sno_info = Semester::where("old_id",$item->SNO)->first();
            if($sno_info)
            {
                $SNO = $sno_info->SNO;
            }
            $new_course_info = Course::where("CourseName",$item->CourseName)->where("ClassID",$ClassID)->where("SNO",$SNO)->first();
            if($new_course_info)
            {
                $new_course_info->old_id = $new_course_info->old_id.",".$item->CourseNO;
                $new_course_info->save();
                continue;
            }

            $data['SNO'] = $SNO;
            $data['CourseName'] = $item->CourseName;
            $data['SchoolID'] = 26;
            $data['old_id'] = $item->CourseNO;
            Course::insert($data);
        }
    }
}
