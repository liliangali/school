<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    public $table = 'exercise';
    public $primaryKey = "ExNO";
    public  $timestamps = false;//去掉update_time等三个字段

    
    public static function addT($all)
    {
        $item = [];
        $ereturn = ['status'=>0,'msg'=>''];
        $sreturn = ['status'=>1,'msg'=>''];
        $CONTENT = $all['CONTENT'];
        $EXAMINFO = $CONTENT['EXERCISES']['EXAMINFO'];//答题详情

        $ExName = $all['ExName'];
        $exercise = Exercise::where("ExName",$ExName)->first();
        if($exercise) //=====  如果这个数据已经存在 只Qnumber 数量加1  =====
        {
            $exercise->increment("Qnumber",1);
            return $sreturn;
        }

        //=====  获得Teacher信息  =====
        $teacher = Teacher::where("LoginID",$all['LOGINID'])->where("password",bcrypt($all['LoginPasswd']))->first();
        if(!$teacher)
        {
            $ereturn['msg'] = '账号登陆错误';
            return $ereturn;
        }
        
        //=====  获得班级信息 年级号=当前学年 -班级创建时间+1   班级创建时间 = 当前学年-年级号+1  =====
        $CreatTime = $EXAMINFO['AcademicYear'] - $EXAMINFO['GRADENAME'] + 1;
        $class = Classes::where('CreatTime',$CreatTime)->where("ClassName",$EXAMINFO['CLASSNAME'])->first();
        if(!$class)
        {
            $ereturn['msg'] = '此班级不存在';
            return $ereturn;
        }
        
        //=====  获得课程  =====
        $course = Course::where("TID",$teacher->TID)->where("ClassID",$class->ClassID)->first();
        if(!$course)
        {
            $ereturn['msg'] = '此课程不存在';
            return $ereturn;
        }
        //=====  最新课程  =====
        $semester = Semester::getLast();
        
        //=====  获得学生人数  =====
        $StuCount = Student::where("ClassID",$class->ClassID)->count();
        
        $item['TID'] = $teacher->TID;
        $item['ClassID'] = $class->ClassID;
        $item['SNO'] = $semester->SNO;
        $item['CourseID'] = $course->CourseID;
        $item['ExName'] = $EXAMINFO['ExName'];
        $item['Qnumber'] = 1;
        $item['StuCount'] = $StuCount;//学生数量
        
        //=====  聚合运算  =====
        $ITEMINFO = $EXAMINFO['ITEMS']['ITEMINFO'];//题目详情
        $POINT = $ITEMINFO['POINT'];//题目分数
        $STUANS = $EXAMINFO['TESTANS']['STUANS'];//学生作答情况
        $AnsNum = 0;//全班答题总数
        $TrueNum = 0;//全班答对题总数
        $AvgScore = 0;//全班活动平均成绩
        $TrueRate = 0.00;//全班答对率
        $SCORE = 0;
        $TotalSpendTime = 0;//总花费时间
        foreach ((array)$STUANS as $index => $val)
        {
            //=====  如果SELECTION不为空  AnsNum+1   =====
            if($val['SELECTION'])
            {
                $AnsNum++;
            }
            //=====  如果SCORE=point   TrueNum+1   =====
            if($val['SCORE'] == $POINT)
            {
                $TrueNum++;
            }
            $SCORE += $val['SCORE'];
            $TotalSpendTime += $val['ANSSPENDTIME'];
        }
        if($AnsNum)
        {
            $TrueRate = number_format($TrueNum/$AnsNum,2);
            $AvgScore = number_format($SCORE/$AnsNum,2);
            $AvgSpendTime = number_format($TotalSpendTime/$AnsNum,2);
        }
        $item['QNumber'] = $all['ITEMCOUNT'];
        $item['StuCount'] = $StuCount;
        $item['AvgScore'] = $AvgScore;
        $item['AnsNum'] = $AnsNum;
        $item['TrueNum'] = $TrueNum;
        $item['TrueRate'] = $TrueRate;
        $item['TotalSpendTime'] = $TotalSpendTime;
        $item['AvgSpendTime'] = $AvgSpendTime;

        if(Exercise::insert($item))
        {
            return $sreturn;
        }
        return $ereturn;
    }

}
