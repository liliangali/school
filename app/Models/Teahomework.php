<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teahomework extends BaseModel
{
    public  $table = 'teahomework';
    public  $primaryKey = "HomeWorkNO";
    public  $timestamps = false;//去掉update_time等三个字段

}
