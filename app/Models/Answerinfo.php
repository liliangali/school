<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answerinfo extends Model
{
    public $table = 'answerinfo';
    public  $timestamps = false;//去掉update_time等三个字段


    public  static  function getRate($ExNO,$ItemIndex)
    {
        $return = [];
        $anw = Answerinfo::where("ExNO",$ExNO)->where("ItemIndex",$ItemIndex)->get()->toArray();
        if(!$anw)
        {
            return $return;
        }
        $AnsNum = 0;
        $TrueNum = 0;
        $TrueRate = 0;
        foreach ((array)$anw as $index => $item)
        {
            
        }

    }
}
