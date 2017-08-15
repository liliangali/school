<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evalstandard extends BaseModel
{
    public  $table = 'evalstandard';
    public  $primaryKey = "StandardNO";
    public  $timestamps = false;//去掉update_time等三个字段
}
