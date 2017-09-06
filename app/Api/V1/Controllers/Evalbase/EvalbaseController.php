<?php

namespace App\Api\V1\Controllers\Evalbase;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Evalbase;
use App\Models\Evalstandard;
use App\Models\Exercise;
use App\Models\Fileformat;
use App\Models\Marking;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Stueavl;
use App\Models\Stuhomework;
use App\Models\Teahomework;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

use JWTAuth;
use App\Models\Teacher;

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

class EvalbaseController extends BaseController {

    public function __construct()
    {
        $this->model = new Evalbase();
    }

    public function listT(Request $request)
    {
        $err = [
            'EvalNO'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $user = JWTAuth::parseToken()->authenticate();
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        $course = Course::where("TID",$teacher->TID)->first();
        if(!$course)
        {
            return $this->errorResponse('此老师账号没有对应的课程数据');
        }
        $evalbase = Evalbase::where("EvalNO",$request->EvalNO)->get();
        if(!$evalbase)
        {
            return $this->successResponse();
        }
        $evalstandard = Evalstandard::whereIn("EvalNO",$evalbase->pluck("EvalNO")->all())->get()->toArray();
        $evalbase = $evalbase->keyBy("EvalNO");
        foreach ((array)$evalstandard as $index => $item)
        {
            $evalstandard[$index]['EvalName'] = "";
            if(isset($evalbase[$item['EvalNO']]))
            {
                $evalstandard[$index]['EvalName'] = $evalbase[$item['EvalNO']]['EvalName'];
            }
        }    
        return $this->successResponse($evalstandard);
    }
    public function getT(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $student = Student::where("UserID",$user->UserID)->first();
        $err = [
            "HomeWorkNO"=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        $info = $this->model->where("HomeWorkNO",$request->HomeWorkNO)->where("StuID",$student->StuID)->first();
        if(!$info)
        {
            return $this->errorResponse("无此作业");
        }
        return $this->successResponse($info->toArray());
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
        $user = JWTAuth::parseToken()->authenticate();
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        $err = [
            'EvalName'=>"required",
            'TotalWeight'=>"required",
            'CourseName'=>"required",
            'StudySection'=>"required",
        ];

        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $all = $request->all();
        $id = $this->model->saveModel($all);
        if(!$id)
        {
            return $this->errorResponse();
        }

        return $this->successResponse(['id'=>$id]);
    }

    public function saddT(Request $request) {
        $err = [
            'EvalNO'=>"required",
            'StandardName'=>"required",
            'Weight'=>"required",
            'Description'=>"required",
            'StandardType'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!($this->model->find($request->EvalNO)))
        {
            return $this->errorResponse("EvalNO 不存在");
        }
        $estand = new Evalstandard();
        $all = $request->all();
        $id = $estand->saveModel($all);
        if(!$id)
        {
            return $this->errorResponse();
        }
        return $this->successResponse(['id'=>$id]);
    }

    public function dfT(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        $err = [
            'HomeWorkNO'=>"required|integer",
            'StuID'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $mID = Student::find($request->StuID);
        if(!$mID)
        {
            return $this->errorResponse("学生ID不存在");
        }
        $hwork = Teahomework::find($request->HomeWorkNO);
        if(!$hwork)
        {
            return $this->errorResponse("这个作业不存在");
        }
        $model = new Marking();
        echo '<pre>';print_r($_POST);exit;
        
        $all = $request->all();
        echo '<pre>';print_r($all);exit;

        echo '<pre>';print_r($StandardNO);exit;

        $Score = $request->Score;
        

        
        


        if($model->where("HomeWorkNO",$request->HomeWorkNO)->where("MemberID",$request->MemberID)->where("StuID",$student->StuID)->first())
        {
            return $this->errorResponse("已评价过该作业,不可重复评价");
        }
        
        $all['HomeWorkNO'] = $request->HomeWorkNO;
        $all['MemberID'] = $request->MemberID;
        $all['StuID'] = $student->StuID;
        $all['Mark'] = $request->Mark;
        $id = $model->saveModel($all);
        return $this->successResponse(['id'=>$id]);
    }

    public function getArr(Request $request)
    {
        $err = [
            'CourseName'=>"required",
            'ClassID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $class = Classes::find($request->ClassID);
        if(!$class) 
        {
            return $this->errorResponse('ClassID错误');
        }
        $semester = Semester::getAuthLast();
        $grade = $semester->AcademicYear -  $class->CreatTime + 1;
        $grade = Semester::getGrade($class->SchoolID,$grade);
        //StudySection
//        $grade = '一';
        $list = Evalbase::where("CourseName",$request->CourseName)->whereRaw('FIND_IN_SET(?,StudySection)',[$grade])->get();
        return $this->successResponse($list);
    }

    
    public function upEval(Request $request)
    {
        $err = [
            'EvalNO'=>"required",
            'TID'=>"required",
            'ClassID'=>"required",
            'AcademicYear'=>"required",
            'SOrder'=>"required",
            'Type'=>"required",
            'CourseID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $sno = Semester::getSe($request->AcademicYear,$request->SOrder);
        if(!$sno) 
        {
            return $this->errorResponse('学期不存在');
        }
        if($request->Type == 1)
        {
            $model = new Exercise();
        }
        else
        {
            $model = new Teahomework();
        }
        $list  =  $model->where("TID",$request->TID)->where("ClassID",$request->ClassID)->where("CourseID",$request->CourseID)->where("SNO",$sno->SNO)->get();
        $EvalNO = $request->EvalNO;
        $list->map(function ($item) use($EvalNO){
            $item->EvalNO = $EvalNO;
            $item->save();
        });
        return $this->successResponse();
    }
    
    public function upFileformat(Request $request)
    {
        return $this->successResponse(Fileformat::get()->toArray());
    }





}
