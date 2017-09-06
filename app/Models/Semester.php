<?php

namespace App\Models;
use App\User;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class Semester extends BaseModel
{
    public $table = 'semester';
    public $primaryKey = "SNO";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function fdata()
    {
        $semesterinfo_list = DB::connection('old')->table('semesterinfo')->get();
        Semester::truncate();
        foreach ($semesterinfo_list as $index => $item)
        {
            $SOrder = "上";
            if($item->SOrder == 1)
            {
                $SOrder = "下";
            }
            $data['AcademicYear'] = $item->AcademicYear;
            $data['SOrder'] = $SOrder;
            $data['SchoolID'] = 26;
            $data['old_id'] = $item->SNO;
            Semester::insert($data);
        }
    }

    public static function getLast()
    {
        return Semester::orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
//        return Semester::where("SchoolID",User::getSchool())->orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
    }

    public static function getSe($AcademicYear,$SOrder)
    {
        return Semester::where("AcademicYear",$AcademicYear)->where("SOrder",$SOrder)->where("SchoolID",User::getSchool())->orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
    }

    public static function getAuthLast()
    {
//        return Semester::orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
        return Semester::where("SchoolID",User::getSchool())->orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
    }

    public static function getGrade($SchoolID,$grade)
    {
        $m   = date("n");
        if($grade >= 2)
        {
            if($m < 9)//=====  过了9月份才开学  =====
            {
                $grade = $grade - 1;
            }
        }
        $arr_n = array("零","一","二","三","四","五","六","七","八","九","十");
        $schoolInfo = School::find($SchoolID);
        $edu = Educational::find($schoolInfo['Type']);
        if(!$edu)
        {
            return '';
        }
        $EduID= $edu->EduID;
        if($EduID == 1 || $EduID == 2)
        {
            $grade = $arr_n[$grade];
        }
        elseif ($EduID == 3 || $EduID == 4)
        {
            $grade = "初".$arr_n[$grade];
        }
        elseif ($schoolInfo['Type'] == 5)
        {
            $grade = "高".$arr_n[$grade];
        }
        elseif ($schoolInfo['Type'] == 6)//=====  完中六年制  =====
        {
            $grade = "初".$arr_n[$grade];;
            if($grade > 3)
            {
                $grade = "高".$arr_n[$grade -3];
            }
            $grade = $grade;
        }
        elseif ($schoolInfo['Type'] == 7)//=====  九年义务教育制 =====
        {
            if($grade <= 6)
            {
                $grade = $arr_n[$grade];
            }
            else
            {
                $grade = "初".$arr_n[$grade-6];;
            }
        }
        elseif ($schoolInfo['Type'] == 8)//=====  实验学校12年制  =====
        {
            if($grade <=6)
            {
                $grade = $arr_n[$grade];
            }
            elseif ($grade <= 9)
            {
                $grade = "初".$arr_n[$grade-6];;
            }
            else
            {
                $grade = "高".$arr_n[$grade -9];
            }
        }
        return $grade;
    }

    public static function getYear($year,$grade)
    {
        $m   = date("n");
        if($m < 9)//=====  过了9月份才开学  =====
        {
            $creat_year = $year - $grade;
        }
        else
        {
            $creat_year = $year - $grade + 1;
        }
        return $creat_year;
    }
}
