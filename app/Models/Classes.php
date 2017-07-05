<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends BaseModel
{    public $table = 'classes';
    public $primaryKey = "ClassID";
    public  $timestamps = false;//去掉update_time等三个字


}


