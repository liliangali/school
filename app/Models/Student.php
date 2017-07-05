<?php

namespace App\Models;

use App\User;

class Student extends BaseModel
{
    public  $table = 'student';
    public  $primaryKey = "StuID";
    public  $timestamps = false;//去掉update_time等三个字段


}
