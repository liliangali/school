<?php

namespace App\Api\V1\Controllers\Exercise;
use App\Api\V1\Controllers\BaseController;
use App\Helper;
use App\Models\Admin;
use App\Models\Answerinfo;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Error;
use App\Models\Exercise;
use App\Models\Exerciseitem;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Validator;

use JWTAuth;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
/**
 * @SWG\Swagger(
 *   @SWG\Info(
 *     title="项目管理系统",
 *     version="1.0.0"
 *   ),
 *   @SWG\Tag(name="Auth", description="验证模块"),
 *   @SWG\Tag(name="Users", description="用户模块"),
 *   @SWG\Tag(name="Companys", description="公司模块"),
 *   @SWG\Tag(name="Departments", description="部门模块"),
 *   @SWG\Tag(name="Roles", description="角色模块"),
 *   @SWG\Tag(name="Projects", description="项目模块"),
 *   @SWG\Tag(name="Demands", description="需求模块"),
 *   @SWG\Tag(name="Groups", description="用户组模块"),
 *   @SWG\Tag(name="Pushs", description="消息推送模块"),
 *   schemes={"http"},
 *   host="pmsapi.turtletl.com",
 *   basePath="/api"
 * )
 */

class ExerciseController extends BaseController {

    public function __construct()
    {
        $this->model = new Exercise();
    }

    public function listT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'ClassID'=>"required|integer",
            'AcademicYear'=>"required",
            'SOrder'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        //=====  获取学期SNO  =====
        $semester = Semester::where('AcademicYear',$request->AcademicYear)->where('SOrder',$request->SOrder)->first();
        if(!$semester)
        {
            return $this->errorResponse('学期不存在');
        }
        $where['SNO']  = $semester->SNO;
        $user = JWTAuth::parseToken()->authenticate();
        $where['ClassID']  = $request->ClassID;
        if($user->IDLevel == "T")
        {
            $teacher = Teacher::where("UserID",$user->UserID)->first();
            $where['TID'] = $teacher->TID;
        }

        $class = Classes::find($request->ClassID);
        if(!$class)
        {
            return $this->errorResponse('班级不存在');
        }

        $lists = Exercise::where($where)->orderBy('ExNO', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        if(!$list)
        {
            return $this->successResponse($lists);
        }
        $teacher = Helper::getKeyList(new Teacher(),$list);
        $course =  Helper::getKeyList(new Course(),$list);

        $list = collect($list)->map(function ($item,$key) use ($teacher,$course){
            $item['uname']  = isset($teacher[$item['TID']]) ? $teacher[$item['TID']]['UName'] : '';
            $item['Coursename'] = isset($course[$item['CourseID']]) ? $course[$item['CourseID']]['CourseName'] : 0;
            $item['TrueRate'] = Exercise::getRate($item['ExNO']);
            return $item;
        })->toArray();
        $lists['data'] = $list;
        return $this->successResponse($lists);

    }

    public function getT(Request $request)
    {
        $err = [
            'ExNO'=>"required|integer",
            'page'=>"required|integer",
            'page_size'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $exercise = Exercise::find($request->ExNO);
        if(!$exercise)
        {
            return $this->errorResponse('活动不存在');
        }
        $ex_list =  Exerciseitem::where("ExNO",$request->ExNO)->paginate($request->page_size)->toArray();//paginate($request->page_size)
        $eitem_list = $ex_list['data'];
        $ItemIndexList  = collect($eitem_list)->pluck('ItemIndex')->all();
        $answerinfo_list = Answerinfo::where("ExNO",$request->ExNO)->whereIn("ItemIndex",$ItemIndexList)->get()->toArray();
        $answerinfo_list = collect($answerinfo_list)->groupBy("ItemIndex")->toArray();
        $eitem_list = collect($eitem_list)->map(function ($item) use ($answerinfo_list){
            $Score = $item['Point'];
            $a_info = isset($answerinfo_list[$item['ItemIndex']]) ? $answerinfo_list[$item['ItemIndex']] : [];//当前所有学生答题记录
            $man_answer = 0;
            $true_answer = 0;
            $AnsSpendTime = 0;
            foreach ((array)$a_info as $index => $item1)
            {
                if($item1['Selection'])
                {
                    $man_answer++;
                }
                if($item1['Score'] == $Score)
                {
                    $true_answer = 0;
                }
                $AnsSpendTime += $item1['SpendTime'];
            }
            $item['AnsNum'] = count($man_answer);//答题人数
            $item['TrueNum'] = count($true_answer);//回答正确人数
            $TrueRate = 0.00;
            if($man_answer > 0)
            {
                $TrueRate = number_format($true_answer/$man_answer,2);
            }
            $AvgSpendTime = 0.00;
            if($AnsSpendTime)
            {
                $AvgSpendTime = number_format($AnsSpendTime/$man_answer,2);
            }
            $item['TrueRate'] = $TrueRate;//回答正确人数
            $item['AnsSpendTime'] = count($AnsSpendTime);//答题时间
            $item['AvgSpendTime'] = count($AvgSpendTime);//答题时间
            return $item;
        })->toArray();
        $ex_list['data'] = $eitem_list;
        return $this->successResponse($ex_list);
    }
    public function getIT(Request $request)
    {
        $err = [
            'ExNO'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $exercise = Exercise::find($request->ExNO);
        if(!$exercise)
        {
            return $this->errorResponse('活动不存在');
        }
        $sen = Semester::find($exercise->SNO);
        $class = Classes::find($exercise->ClassID);
        $teacher = Teacher::find($class->TID);
        $QNumber= Exerciseitem::where("ExNO",$request->ExNO)->get()->count();
        $anlist = Answerinfo::where("ExNO",$request->ExNO)->get();
        $stunum = $anlist->groupBy("StuID")->count();
        $stutotal = $anlist->sum("Score");
        $stu_list = Student::where("ClassID",$exercise->ClassID)->get()->count();
        $AvgScore = 0;
        if($stu_list > 0 )
        {
            $AvgScore =  number_format($stutotal/$stu_list,2);
        }
        $return['AcademicYear'] = $sen->AcademicYear;
        $return['SOrder'] = $sen->SOrder;
        $return['Grade'] = $sen->AcademicYear - $class['CreatTime'] + 1;//年级号=当前学年 -班级创建时间+1;
        $return['ClassName'] = $class->ClassName;
        $return['UName'] = $teacher->UName;
        $return['QNumber'] = $QNumber;
        $return['Stucount'] = $stunum;
        $return['TrueRate'] =  Exercise::getRate($request->ExNO);
        $return['AvgScore'] = $AvgScore;
        $return['ExName'] = $exercise->ExName;
        return $this->successResponse($return);

    }

    public function listST(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'AcademicYear'=>"required",
            'SOrder'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $user = JWTAuth::parseToken()->authenticate();
        if($user->IDLevel == "S")
        {
            $stu = Student::where("UserID",$user->UserID)->first();
            $StuID = $stu->StuID;
        }
        else
        {
            $StuID = $request->StuID;
        }
        if(!$StuID)
        {
            return $this->errorResponse("缺少StuID字段");
        }
        $semester = Semester::getSe($request->AcademicYear,$request->SOrder);
        if(!$semester)
        {
            return $this->errorResponse("年级不存在");
        }

        $student = Student::find($request->StuID);
        if(!$student)
        {
            return $this->errorResponse('班级不存在');
        }
        $an_list = Answerinfo::where("StuID",$StuID)->get()->toArray();
        $ExNO = collect($an_list)->pluck('ExNO');//所有活动

        $lists = Exercise::where("SNO",$semester->SNO)->whereIn("ExNO",$ExNO)->orderBy('ExNO', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        if(!$list)
        {
            return $this->successResponse($lists);
        }
        $teacher = Helper::getKeyList(new Teacher(),$list);
        $course =  Helper::getKeyList(new Course(),$list);
        $list = collect($list)->map(function ($item,$key) use ($teacher,$course,$request){
            $item['uname']  = isset($teacher[$item['TID']]) ? $teacher[$item['TID']]['UName'] : '';
            $item['Coursename'] = isset($course[$item['CourseID']]) ? $course[$item['CourseID']]['CourseName'] : '';
            $strRate = Exercise::getStuRate($item['ExNO'],$request->StuID);
            $item['TrueRate'] = $strRate['TrueRate'];
            $item['TrueNum'] = $strRate['TrueNum'];
            $item['Score'] = $strRate['Score'];
            $item['AnsNum'] = $strRate['AnsNum'];
            return $item;
        })->toArray();
        $lists['data'] = $list;
        return $this->successResponse($lists);
    }


    /**
     * 学生活动详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getST(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $err = [
            'ExNO'=>"required|integer",
        ];
        if($user->IDLevel == "S")
        {
            $student = Student::where("UserID",$user->UserID)->first();
            $StuID = $student->StuID;
        }
        else
        {
            $StuID = $request->StuID;
        }
        if(!$StuID)
        {
            return $this->errorResponse("学生ID不存在");
        }
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $exercise = Exercise::find($request->ExNO);
        if(!$exercise)
        {
            return $this->errorResponse('活动不存在');
        }

        $ItemIndexList  = collect(Exerciseitem::where("ExNO",$request->ExNO)->get()->toArray())->keyBy('ItemIndex')->all();
        $answerinfo_list = Answerinfo::where("ExNO",$request->ExNO)->where("StuID",$StuID)->get()->toArray();//学生对当前活动的答题列表
        $answerinfo_list = collect($answerinfo_list)->map(function ($item,$key) use ($ItemIndexList,$StuID,$request){
            $item['Question'] = isset($ItemIndexList[$item['ItemIndex']]) ? $ItemIndexList[$item['ItemIndex']]['Question'] : '';
            $item['Type'] = isset($ItemIndexList[$item['ItemIndex']]) ? $ItemIndexList[$item['ItemIndex']]['Type'] : '';
            $item['Answer'] = isset($ItemIndexList[$item['ItemIndex']]) ? $ItemIndexList[$item['ItemIndex']]['Answer'] : '';
            $item['Url'] = isset($ItemIndexList[$item['ItemIndex']]) ? $ItemIndexList[$item['ItemIndex']]['Url'] : '';
            $exer_info = Exercise::getStuRate($request->ExNO,$StuID,$item['ItemIndex']);
            $item['AnsNum'] = $exer_info['AnsNum'];
            $item['TrueNum'] = $exer_info['TrueNum'];
            $item['TrueRate'] = $exer_info['TrueRate'];
            $item['AvgSpendTime'] = $exer_info['AvgSpendTime'];
            $item['SpendTime'] = $exer_info['SpendTime'];
            return $item;
        })->all();
        return $this->successResponse($answerinfo_list);
    }


    public function getAT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'ExNO'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $exercise = Exercise::find($request->ExNO);
        if(!$exercise)
        {
            return $this->errorResponse('活动不存在');
        }
        $lists = Student::where("ClassID",$exercise->ClassID)->orderBy('StuID', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        if(!$list)
        {
            return $this->successResponse($lists);
        }
        $ItemIndex = collect(Exerciseitem::where("ExNO",$request->ExNO)->get()->toArray())->keyBy('ItemIndex')->all();
        $answerinfo_list = collect(Answerinfo::where("ExNO",$request->ExNO)->whereIn("StuID",collect($list)->pluck('StuID')->all())->get())->groupBy("StuID")->toArray();
        $list = collect($list)->map(function ($item) use ($answerinfo_list,$ItemIndex){
            $an_list = isset($answerinfo_list[$item['StuID']]) ? $answerinfo_list[$item['StuID']] : [];
            $TrueNum = 0;
            $Score = 0;
            $TrueRate = 0;
            if($an_list)
            {
                foreach ((array)$an_list as $index1 => $item1)
                {
                    if(isset($ItemIndex[$item1['ItemIndex']]['Point']) && $ItemIndex[$item1['ItemIndex']]['Point'] == $item1['Score'])
                    {
                        $TrueNum++;
                    }
                    $Score += $item1['Score'];
                }
                $TrueRate = number_format($TrueNum/count($an_list));
            }
            $item['Score'] = $Score;
            $item['TrueNum'] = $TrueNum;
            $item['TrueRate'] = $TrueRate;
            return $item;
        })->all();
        $lists['data'] = $list;
        return $lists;
    }


    /**
     * @SWG\Get(
     *   path="/users/all",
     *   summary="显示所有用户",
     *   tags={"Users"},
     *   @SWG\Parameter(name="Authorization", in="header", required=true, description="用户凭证", type="string"),
     *   @SWG\Response(
     *     response=200,
     *     description="all users"
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */

    public function addT(Request $request) {
        $err = [
            'TID'=>"required",
            'CreatTime'=>"required",
            'ClassName'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!Teacher::find($request->TID))
        {
            return $this->errorResponse("此老师ID不存在");
        }
        if($this->model->saveModel($request->all()))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('添加失败');
    }

    public function putT(Request $request) {
        $err = [
            'ClassID'=>"required",
            'TID'=>"required",
            'CreatTime'=>"required",
            'ClassName'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!Teacher::find($request->TID))
        {
            return $this->errorResponse("此老师ID不存在");
        }
        if($this->model->saveModel($request->all()))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('更新失败');
    }

    public function delT(Request $request)
    {
        $err = [
            'ClassID'=>"required",
        ];

        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        if($this->model->del($request))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('删除失败');
    }




}
