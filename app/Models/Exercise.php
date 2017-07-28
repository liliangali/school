<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Exercise extends Model
{
    public $table = 'exercise';
    public $primaryKey = "ExNO";
    public  $timestamps = false;//去掉update_time等三个字段

    
    public static function addT($request)
    {
        //=====  格式化数据  =====
        $all = $request->all();
        $CONTENT = $all['CONTENT'];
        //处理编码(因为用lib函数解析出来的xml乱码,所以只能先匹配出来)
        preg_match('/<EXNAME>([\S\s\d]*?)<\/EXNAME>/', $CONTENT, $titleArr);
       $ExName = $titleArr[1];
        $ExName= mb_convert_encoding($ExName,"UTF-8","EUC-CN");

        preg_match_all('/<QUESTION>([\S\s\d]*?)<\/QUESTION>/', $CONTENT, $titleArr);
        $QuestionAll = $titleArr[1];//如果是多个题目 这是个数组
        preg_match('/<CLASSNAME>([\S\s\d]*?)<\/CLASSNAME>/', $CONTENT, $titleArr);
        $CLASSNAME = $titleArr[1];
        $CLASSNAME = mb_convert_encoding($CLASSNAME,"UTF-8","EUC-CN");
        libxml_disable_entity_loader(true);
        $xml= json_decode(json_encode(simplexml_load_string($CONTENT, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        $all['CONTENT'] = $xml;
        $dir = storage_path('app/public/upload/');
        $item = [];
        $ereturn = ['status'=>0,'msg'=>''];
        $sreturn = ['status'=>1,'msg'=>''];
        $CONTENT = $all['CONTENT'];
        $EXAMINFO = $CONTENT['EXERCISES']['EXAMINFO'];//答题详情
        $EXAMINFO['EXNAME'] = $ExName;
        //=====  获得Teacher信息  =====
        $user = User::where("LoginID",$all['LOGINID'])->where("password",bcrypt($all['LoginPasswd']))->first();
        if(!$user)
        {
            $ereturn['msg'] = '账号密码00';
            return $ereturn;
        }
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        if(!$teacher)
        {
            $ereturn['msg'] = '账号登陆错误11';
            return $ereturn;
        }
        //=====  获得班级信息 年级号=当前学年 -班级创建时间+1   班级创建时间 = 当前学年-年级号+1  =====
        $CreatTime = $EXAMINFO['AcademicYear'] - $EXAMINFO['GRADENAME'] + 1;
        $class = Classes::where('CreatTime',$CreatTime)->where("ClassName",$CLASSNAME)->first();
        if(!$class)
        {
            $ereturn['msg'] = '此班级不存在,班级名字:'.$CLASSNAME."年份和年级:".$CreatTime;
            return $ereturn;
        }
        $course = Course::where("TID",$teacher->TID)->where("ClassID",$class->ClassID)->first();
        if(!$course)
        {
            $ereturn['msg'] = '此课程不存在33';
            return $ereturn;
        }
        $semester = Semester::getLast();
        $exercise = Exercise::where("EXNAME",$ExName)->first();
        if($exercise) //=====  如果这个数据已经存在 只Qnumber 数量加1  =====
        {
            $exercise->increment("Qnumber",1);
        }
        else
        {
            $item['TID'] = $teacher->TID;
            $item['ClassID'] = $class->ClassID;
            $item['SNO'] = $semester->SNO;
            $item['CourseID'] = $course->CourseID;
            $item['ExName'] = $EXAMINFO['EXNAME'];
            $item['Qnumber'] = 1;
            $item['SubmitTime'] = date("Y-m-d H:i:s");
            Exercise::insert($item);
            $exercise = Exercise::where("EXNAME",$ExName)->first();
        }
        $ExNO = $exercise->ExNO;
        $timItem = [];//匹配题目类型
        //=====  聚合运算  =====
        $ITEMINFO = $EXAMINFO['ITEMS']['ITEMINFO'];//题目详情
        $ITEMCOUNT = $EXAMINFO['ITEMCOUNT'];//题目个数
        $TypeA = $EXAMINFO['ITEMS']['TYPE'];//题目类型

        if($ITEMCOUNT >= 2)
        {
            for ($x=0; $x<$ITEMCOUNT; $x++)
            {
                $Question = mb_convert_encoding($QuestionAll[$x],"UTF-8","EUC-CN");
                $Type = $TypeA[$x];
                $POINT = $ITEMINFO[$x]['POINT'];//题目分数
                $ITEMORDER = $ITEMINFO[$x]['ITEMORDER'];//题目顺序号
                $Answer = $ITEMINFO[$x]['ANSWER'];//标准答案
                $conter = $ITEMINFO[$x]['conter'];//目标描述
                if(is_array($ITEMINFO[$x]['conter']))
                {
                    $conter = "";//目标描述
                }
                $picture = $ITEMINFO[$x]['picture'];//题目图片
                $timItem[$ITEMORDER] = $Type;
                //=====  处理题目表  =====
                if(!(Exerciseitem::where("ItemIndex",$ITEMORDER)->where("ExNO",$ExNO)->first()))//只有这个题目不存在才可以重新添加
                {
                    $eitem['ExNO'] = $ExNO;
                    $eitem['ItemIndex'] = $ITEMORDER;
                    $eitem['Question'] = $Question;
                    $eitem['Type'] = $Type;
                    $eitem['Answer'] = $Answer;
                    $eitem['Point'] = $POINT;
                    $eitem['Conter'] = $conter;
                    //获取图片
                    $pictureObj = $request->file($picture);
                    $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).$ITEMORDER.'.png';
                    if ($pictureObj->isValid())
                    {
                        $pictureObj->move($dir, $string);
                        $eitem['Url'] =  asset('storage/upload/'.$string);
                    }
                    $eitem_id = Exerciseitem::insert($eitem);
                    if(!$eitem_id)
                    {
                        $ereturn['msg'] = "题目添加失败66";
                        return $ereturn;
                    }
                }
            }
        }
        else
        {
            $Question = mb_convert_encoding($QuestionAll[0],"UTF-8","EUC-CN");
            $Type = $TypeA;
            $POINT = $ITEMINFO['POINT'];//题目分数
            $ITEMORDER = $ITEMINFO['ITEMORDER'];//题目顺序号
            $Answer = $ITEMINFO['ANSWER'];//标准答案
            $conter = $ITEMINFO['conter'];//目标描述
            if(is_array($ITEMINFO['conter']))
            {
                $conter = "";//目标描述
            }
            $picture = $ITEMINFO['picture'];//题目图片
            $timItem[$ITEMORDER] = $Type;
            //=====  处理题目表  =====
            if(!(Exerciseitem::where("ItemIndex",$ITEMORDER)->where("ExNO",$ExNO)->first()))//只有这个题目不存在才可以重新添加
            {
                $eitem['ExNO'] = $ExNO;
                $eitem['ItemIndex'] = $ITEMORDER;
                $eitem['Question'] = $Question;
                $eitem['Type'] = $Type;
                $eitem['Answer'] = $Answer;
                $eitem['Point'] = $POINT;
                $eitem['Conter'] = $conter;
                //获取图片
                $pictureObj = $request->file($picture);
                $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).$ITEMORDER.'.png';
                if ($pictureObj->isValid())
                {
                    $pictureObj->move($dir, $string);
                    $eitem['Url'] =  asset('storage/upload/'.$string);
                }
                $eitem_id = Exerciseitem::insert($eitem);
                if(!$eitem_id)
                {
                    $ereturn['msg'] = "题目添加失败";
                    return $ereturn;
                }
            }

        }

        //=====  学生作答表  =====
        $STUANS = $EXAMINFO['TESTANS']['STUANS'];//学生作答情况
        foreach ((array)$STUANS as $index1 => $val1)
        {
            //=====  查询在此班级下有没有这个座位号  =====
            $stu = Student::where("ClassID",$class->ClassID)->where("SeatNO",$val1['SEATNO'])->first();
            if(!$stu)
            {
                continue;
            }
            if(Answerinfo::where("StuID",$stu->StuID)->where("ItemIndex",$ITEMORDER)->where("ExNO",$ExNO)->first())
            {
                continue;
            }
            if($stu)
            {
                $aitem['StuID'] = $stu->StuID;
                $aitem['ItemIndex']  = $ITEMORDER;
                $aitem['ExNO'] = $ExNO;
                $aitem['SpendTime'] = $val1['ANSSPENDTIME'];
                $aitem['Score'] = $val1['SCORE'];
                #TODO 创作题 图片地址
                if(is_array($val1['SELECTION']))
                {
                    $val1['SELECTION'] = "";
                }
                $SELECTION =  $val1['SELECTION'];
                $aitem['Selection'] = $SELECTION;
//                $timItem[$ITEMORDER] = $Type;
                if($timItem[$ITEMORDER] == 6)
                {
                    $pictureObj = $request->file($SELECTION);
                    $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).$stu->StuID.$ITEMORDER.$ExNO.'.png';
                    if ($pictureObj->isValid())
                    {
                        $pictureObj->move($dir, $string);
                        $aitem['Selection'] =  asset('storage/upload/'.$string);
                    }
                }
                Answerinfo::insert($aitem);
            }
        }
        //=====  针对每个学生的作答统计表  	exscore =====
        return $sreturn;
    }

    public static function addItem($ITEMINFO)
    {
        //=====  聚合运算  =====
//        $ITEMINFO = $EXAMINFO['ITEMS']['ITEMINFO'];//题目详情
//        echo '<pre>';print_r($ITEMINFO);exit;
        $POINT = isset($ITEMINFO['POINT']) ? $ITEMINFO['POINT'] : 0;//题目分数
        $ITEMORDER = $ITEMINFO['ITEMORDER'];//题目顺序号



        $Answer = $ITEMINFO['ANSWER'];//标准答案
        $conter = $ITEMINFO['conter'];//目标描述
        if(is_array($ITEMINFO['conter']))
        {
            $conter = "";//目标描述
        }
        $Type = $EXAMINFO['ITEMS']['TYPE'];//题目类型
        $picture = $ITEMINFO['picture'];//题目图片
        $STUANS = $EXAMINFO['TESTANS']['STUANS'];//学生作答情况
//        $AnsNum = 0;//全班答题总数
//        $TrueNum = 0;//全班答对题总数
//        $AvgScore = 0;//全班活动平均成绩
//        $AvgSpendTime = 0;
//        $TrueRate = 0.00;//全班答对率
//        $SCORE = 0;
//        $TotalSpendTime = 0;//总花费时间
//        foreach ((array)$STUANS as $index => $val)
//        {
//            //=====  如果SELECTION不为空  AnsNum+1   =====
//            if($val['SELECTION'])
//            {
//                $AnsNum++;
//            }
//            //=====  如果SCORE=point   TrueNum+1   =====
//            if($val['SCORE'] == $POINT)
//            {
//                $TrueNum++;
//            }
//            $SCORE += $val['SCORE'];
//            $TotalSpendTime += $val['ANSSPENDTIME'];
//        }
//        if($AnsNum)
//        {
//            $TrueRate = number_format($TrueNum/$AnsNum,2);
//            $AvgScore = number_format($SCORE/$AnsNum,2);
//            $AvgSpendTime = number_format($TotalSpendTime/$AnsNum,2);
//        }
////        $item['QNumber'] = $EXAMINFO['ITEMCOUNT'];
//        $item['AvgScore'] = $AvgScore;
//        $item['AnsNum'] = $AnsNum;
//        $item['TrueNum'] = $TrueNum;
//        $item['TrueRate'] = $TrueRate;
//        $item['TotalSpendTime'] = $TotalSpendTime;
//        $item['AvgSpendTime'] = $AvgSpendTime;

//        if(Exerciseitem::where("ExNO",$ExNO)->first())
//        {
//            $ereturn['msg'] = '添加题表失败,已经存在';
//            return $ereturn;
//        }

        //=====  处理题目表  =====
        if(Exerciseitem::where("ItemIndex",$ITEMORDER)->where("ExNO",$ExNO)->first())
        {
            $ereturn['msg'] = "此题目已经存在,无法重复提交55";
            return $ereturn;
        }
        $eitem['ExNO'] = $ExNO;
        $eitem['ItemIndex'] = $ITEMORDER;
        $eitem['Question'] = $Question;
        $eitem['Type'] = $Type;
        $eitem['Answer'] = $Answer;
        $eitem['Point'] = $POINT;
        $eitem['Conter'] = $conter;
        //获取图片
        $pictureObj = $request->file($picture);
        $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).$ITEMORDER.'.png';
        if ($pictureObj->isValid())
        {
            $pictureObj->move($dir, $string);
            $eitem['Url'] =  asset('storage/upload/'.$string);
        }
//        $eitem['AnsNum'] = $AnsNum;
//        $eitem['TrueNum'] = $TrueNum;
//        $eitem['TrueRate'] = $TrueRate;
//        $eitem['AnsSpendTime'] = $TotalSpendTime;
//        $eitem['AvgSpendTime'] = $AvgSpendTime;
        $eitem_id = Exerciseitem::insert($eitem);
        if(!$eitem_id)
        {
            $ereturn['msg'] = "题目添加失败";
            return $ereturn;
        }
//echo '<pre>';print_r($eitem_id);exit;
    }
    
    public static function getRate($ExNO)
    {
        //=====  答对率  =====
        $answerinfo = Answerinfo::where("ExNO",$ExNO)->get()->toArray();
        $exerciseitem = collect(Exerciseitem::where("ExNO",$ExNO)->get()->toArray())->keyBy('ItemIndex')->all();
        $dati_num = 0;
        $dadui_num = 0;
        foreach ((array)$answerinfo as $index1 => $item1)
        {
            if($item1['Selection'])
            {
                if($item1['Score'] == $exerciseitem[$item1['ItemIndex']]['Point'])
                {
                    $dadui_num++;
                }
                $dati_num++;
            }
        }
        if($dati_num)
        {
           return  number_format($dadui_num/$dati_num,2);
        }
        return 0.00;
    }

    public static function getAllRate($ExNO)
    {
        //=====  答对率  =====
        $answerinfo = Answerinfo::where("ExNO",$ExNO)->get()->toArray();
        $exerciseitem = collect(Exerciseitem::where("ExNO",$ExNO)->get()->toArray())->keyBy('ItemIndex')->all();
        $dati_num = 0;
        $dadui_num = 0;
        foreach ((array)$answerinfo as $index1 => $item1)
        {
            if($item1['Selection'])
            {
                if($item1['Score'] == $exerciseitem[$item1['ItemIndex']]['Point'])
                {
                    $dadui_num++;
                }
                $dati_num++;
            }
        }
        if($dati_num)
        {
            return  number_format($dadui_num/$dati_num,2);
        }
        return 0.00;
    }


    /**
     * @param $ExNO
     * @param $StuID
     * @param int $ItemIndex 不为空则代表求的是此活动的此题目的概率
     * @return array
     */
    public static function getStuRate($ExNO,$StuID,$ItemIndex=0)
    {
        $return = [];
        //=====  答对率  =====
        if($ItemIndex)
        {
            $answerinfo = Answerinfo::where("ExNO",$ExNO)->where('StuID',$StuID)->where('ItemIndex',$ItemIndex)->get()->toArray();
        }
        else
        {
            $answerinfo = Answerinfo::where("ExNO",$ExNO)->where('StuID',$StuID)->get()->toArray();
        }

        $exerciseitem = collect(Exerciseitem::where("ExNO",$ExNO)->get()->toArray())->keyBy('ItemIndex')->all();
        $dati_num = 0;
        $dadui_num = 0;
        $TrueNum = 0;
        $chengji = 0;
        $SpendTime = 0;
        $AvgSpendTime = 0;
        $TrueRate = 0;
        foreach ((array)$answerinfo as $index1 => $item1)
        {
            if($item1['Selection'])
            {
                if($item1['Score'] == $exerciseitem[$item1['ItemIndex']]['Point'])
                {
                    $dadui_num++;
                }
                $dati_num++;
            }
            $chengji += $item1['Score'];
            $SpendTime += $item1['SpendTime'];
        }
        $return['TrueRate'] = 0.00;
        if($dati_num)
        {
            $TrueRate = number_format($dadui_num/$dati_num,2);
        }
        $return['TrueRate'] = $TrueRate;
        if($SpendTime)
        {
            $AvgSpendTime = number_format($SpendTime/$dati_num,2);
        }
        $return['AvgSpendTime'] = $AvgSpendTime;
        $return['TrueNum'] = $dadui_num;
        $return['SpendTime'] = $SpendTime;
        $return['Score'] = $chengji;
        $return['AnsNum'] = $dati_num;
        return $return;
    }

    public static function delByClassID($schID)
    {

        $class = Classes::where("SchoolID",$schID)->get();
        if(!$class)
        {
            return;
        }
        foreach ($class as $index => $item)
        {
            $ex_list = Exercise::where("ClassID",$item->ClassID)->get();
            $item->delete();
            if(!$ex_list) 
            {
                continue;
            }
            foreach ($ex_list as $index1 => $item1)
            {
                //=====  删除ExNO exerciseitem answerinfo =====
                Exerciseitem::where("ExNO",$item1->ExNO)->delete();
                Answerinfo::where("ExNO",$item1->ExNO)->delete();
                $item1->delete();
            }
        }
    }

}
