<?php

namespace App\Models;


class Course extends BaseModel
{
    public $table = 'course';
    public $primaryKey = "CourseID";
    public  $timestamps = false;//去掉update_time等三个字段
}
