<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysAdmin extends Model
{
    public  $table = 'sysadmin';
    public  $primaryKey = "AdminID";
    public  $timestamps = false;//去掉update_time等三个字段
}
