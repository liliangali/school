<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public $table = 'subject';
    public $primaryKey = "CourseNO";
    public  $timestamps = false;//去掉update_time等三个字段

}
