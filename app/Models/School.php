<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class School extends BaseModel
{
    public $table = 'school';
    public $primaryKey = "SchoolID";
    public  $timestamps = false;//去掉update_time等三个字段


    public static function getDefault()
    {
        return School::find();
    }

    public static function delUser($model,$school_id)
    {
//        echo '<pre>';print_r($model);exit;
        
        if(is_array($model)) 
        {
            foreach ((array)$model as $index => $item)
            {
                School::delItem($item,$school_id);
            }
        }
        else
        {
            School::delItem($model,$school_id);
        }

    }
    public static function delItem($model,$school_id)
    {
        $list = $model->where("SchoolID",$school_id)->get();
        if(!$list)
        {
            return;
        }
        foreach ($list as $index => $item)
        {
            if(isset($item->UserID) && $item->UserID)
            {
                User::where("UserID",$item->UserID)->delete();
            }
            $item->delete();
        }
        return true;
    }
}
