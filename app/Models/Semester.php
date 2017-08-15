<?php

namespace App\Models;
use App\User;
use JWTAuth;

class Semester extends BaseModel
{
    public $table = 'semester';
    public $primaryKey = "SNO";
    public  $timestamps = false;//去掉update_time等三个字段

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
        $m = date("n");
        if($grade >= 2)
        {
            if($m < 9)//=====  过了9月份才开学  =====
            {
                $grade = $grade - 1;
            }
        }
        $arr_n = array("零","一","二","三","四","五","六","七","八","九","十");
        $schoolInfo = School::find($SchoolID);
        if($schoolInfo['Type'] == 1)
        {
            $grade = $arr_n[$grade];
        }
        elseif ($schoolInfo['Type'] == 2)
        {
            $grade = "初".$arr_n[$grade];
        }
        elseif ($schoolInfo['Type'] == 3)
        {
            $grade = "高".$arr_n[$grade];
        }
        elseif ($schoolInfo['Type'] == 4)
        {
            $grade = $grade;
            if($grade > 6)
            {
                $grade = "初".$grade;
            }
        }
        elseif ($schoolInfo['Type'] == 5)
        {
            $grade = $grade;
            if($grade > 6 && $grade  <= 9)
            {
                $grade = "初".$grade;
            }
            elseif ($grade > 9)
            {
                $grade = "高".$grade;
            }
        }
        elseif ($schoolInfo['Type'] == 6)
        {
            $grade = "初".$grade;
            if($grade > 3)
            {
                $grade = "高".$grade;
            }
        }
        return $grade;
    }

    public static function getYear($SchoolID,$grade)
    {
        $schoolInfo = School::find($SchoolID);
    }
}
