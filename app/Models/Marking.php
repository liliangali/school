<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marking extends BaseModel
{
    public  $table = 'Marking';
    public  $primaryKey = "MarkNO";
    public  $timestamps = false;//去掉update_time等三个字段
}
