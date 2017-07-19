<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exerciseitem extends Model
{
    public $table = 'exerciseitem';
    public  $timestamps = false;//去掉update_time等三个字段
}
