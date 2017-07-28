<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stueavl extends BaseModel
{
    public  $table = 'stueavl';
    public  $primaryKey = "EvalNO";
    public  $timestamps = false;//去掉update_time等三个字段
}
