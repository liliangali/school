<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fileformat extends BaseModel
{
    public $table = 'fileformat';
    public $primaryKey = "FileNO";
    public $timestamps = false;//去掉update_time等三个字段
}
