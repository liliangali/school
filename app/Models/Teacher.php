<?php

namespace App\Models;

class Teacher extends BaseModel
{
    public  $table = 'teacher';
    public  $primaryKey = "TID";
    public  $timestamps = false;//去掉update_time等三个字段
    public  $gender = ['男','女'];

}
