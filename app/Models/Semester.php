<?php

namespace App\Models;


class Semester extends BaseModel
{
    public $table = 'semester';
    public $primaryKey = "SNO";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function getLast()
    {
        return Semester::orderBy("SNO","DESC")->first();
    }
}
