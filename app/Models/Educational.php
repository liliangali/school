<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Educational extends BaseModel
{
    public $table = 'educational';
    public $primaryKey = "EduID";
    public  $timestamps = false;//去掉update_time等三个字段
}
