<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends BaseModel
{
    public $table = 'subject';
    public $primaryKey = "CourseNO";
    public  $timestamps = false;//去掉update_time等三个字段

}
