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
        return Semester::orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->orderBy("SNO","DESC")->first();
//        return Semester::where("SchoolID",User::getSchool())->orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
    }

    public static function getAuthLast()
    {
//        return Semester::orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
        return Semester::where("SchoolID",User::getSchool())->orderBy("AcademicYear","DESC")->orderBy("SNO","DESC")->first();
    }
}
