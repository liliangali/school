<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answerinfo extends Model
{
    public $table = 'answerinfo';
    public $primaryKey = "ExNO";
    public  $timestamps = false;//去掉update_time等三个字段
}
