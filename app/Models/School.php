<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    public $table = 'school';
    public $primaryKey = "SchoolID";
    public  $timestamps = false;//去掉update_time等三个字段


    public static function getDefault()
    {
        return School::find();
    }
}
