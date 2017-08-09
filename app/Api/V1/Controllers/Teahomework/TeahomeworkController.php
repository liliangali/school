<?php

namespace App\Api\V1\Controllers\Teahomework;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
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

class TeahomeworkController extends BaseController {

    public function __construct()
    {
        $this->model = new Teahomework();
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

        $user = JWTAuth::parseToken()->authenticate();

        //=====  获取学期SNO  =====
        $semester = Semester::where('AcademicYear',$request->AcademicYear)->where('SOrder',$request->SOrder)->first();
        if(!$semester)
        {
            return $this->errorResponse('学期不存在');
        }
        $where['SNO']  = $semester->SNO;
        $where['ClassID']  = $request->ClassID;
        if($user->IDLevel == "T")
        {
            $teacher = Teacher::where("UserID",$user->UserID)->first();
            $where['TID'] = $teacher->TID;
        }

        $lists = Teahomework::where($where)->where('SNO',$semester->SNO)->orderBy('HomeWorkNO', 'desc')->paginate($request->page_size)->toArray();
        $lists['data'] = collect($lists['data'])->map(function ($item){
            $item['SubmitCount'] = Stuhomework::where("HomeWorkNO",$item['HomeWorkNO'])->where("Status",1)->count();
            return $item;
        })->toArray();
        return $this->successResponse($lists);
    }

    public function getT(Request $request)
    {
        $err = [
            'HomeWorkNO'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $lists = Teahomework::find($request->HomeWorkNO)->toArray();
        return $this->successResponse($lists);
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
        if($user->IDLevel != "T") //=====  学生登陆  =====
        {
            return $this->errorResponse('此接口只允许学生使用');
        }
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        if(!$teacher)
        {
            return $this->errorResponse('错误');
        }
        $err = [
            'ClassID'=>"required",
            'SNO'=>"required",
            'HomeWorkType'=>"required",
            'HomeWorkTitle'=>"required",
            'CourseName'=>"required",
            'BeginTime'=>"required",
            'DataLine'=>"required",
            'HomeMode'=>"required",
            'Format'=>"required",
            'Description'=>"required",
            'Evaluation'=>"required",
            'HomeWorkView'=>"required",
            'EvalNO'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        if(!Classes::find($request->ClassID))
        {
            return $this->errorResponse("此班级ID不存在");
        }
//        $seme = Semester::getAuthLast();
        $all = $request->all();
//        $all['SNO'] = $seme->SNO;
        $all["TID"] = $teacher->TID;
        $dir = storage_path('app/public/upload');
        $pictureObj = $request->file("File");
        $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).'.png';
        if ($pictureObj && $pictureObj->isValid())
        {
            $pictureObj->move($dir, $string);
            $all['FilePath'] = asset('storage/upload/' . $string);
        }
        $id = $this->model->saveModel($all);
        if(!$id)
        {
            return $this->errorResponse();
        }
        $tall['HomeWorkNO'] = $id;
        $tall['HomeMode'] = $request->HomeMode;
        $tall['Status'] = 2;
        $studentList = Student::where("ClassID",$request->ClassID)->get();
        if($studentList)//=====  学生添加作业  =====
        {
            foreach ($studentList as $index => $item)
            {
                $tall['StuID'] = $item->StuID;
//                $tall['SubTime'] = $item->StuID;
//                $tall['FilePath'] = $item->StuID;
//                $tall['ViewCounter'] = $item->StuID;
//                $tall['Score'] = $item->StuID;
//                $tall['TeaEval'] = $item->StuID;
                Stuhomework::insert($tall);
            }
        }
        return $this->successResponse(['id'=>$id]);
    }




}
