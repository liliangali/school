<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JWTAuth;
class Admin extends Model
{
    public $table = 'admin';
    public $primaryKey = "AdminID";
    public  $timestamps = false;//去掉update_time等三个字段

    public static function getSchoolId()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $admin = Admin::where("UserID",$user->UserID)->first();
        if(!$admin)
        {
            return false;
        }
        return $admin->SchoolID;
    }
}
