<?php

namespace App\Api\V1\Controllers\Teahomework;
use App\Api\V1\Controllers\BaseController;
use App\Helper;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Evalbase;
use App\Models\Fileformat;
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
        $semester = Semester::getSe($request->AcademicYear,$request->SOrder);
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
        $file = Fileformat::get()->keyBy("FileNO")->toArray();
        $eval = Evalbase::get()->keyBy("EvalNO")->toArray();
        $lists = Teahomework::where($where)->where('SNO',$semester->SNO)->orderBy('HomeWorkNO', 'desc')->paginate($request->page_size)->toArray();
        $lists['data'] = collect($lists['data'])->map(function ($item) use($file,$eval) {
            $item['SubmitCount'] = Stuhomework::where("HomeWorkNO",$item['HomeWorkNO'])->where("Status",1)->count();
            $item['Format'] = isset($file[$item['Format']]) ? $file[$item['Format']]['Description'] : '';
            $item['EvalName']  = isset($eval[$item['EvalNO']]) ? $eval[$item['EvalNO']]['EvalName'] : '';
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
        if($user->IDLevel != "T")
        {
            return $this->errorResponse('此接口只允许老师使用');
        }
        $teacher = Teacher::where("UserID",$user->UserID)->first();
        if(!$teacher)
        {
            return $this->errorResponse('错误');
        }
        $err = [
            'SNO'=>"required",
            'HomeWorkType'=>"required",
            'HomeWorkTitle'=>"required",
//            'CourseName'=>"required",
            'BeginTime'=>"required",
            'DataLine'=>"required",
            'HomeMode'=>"required",
            'Format'=>"required",
            'Description'=>"required",
            'Evaluation'=>"required",
            'HomeWorkView'=>"required",
//            'EvalNO'=>"required",
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
        $all["TID"] = $teacher->TID;
        unset($all['ClassID']);
        $ClassID = $request->ClassID;
        if(!is_array($ClassID) || !$ClassID)
        {
            return $this->errorResponse("ClassID参数错误");
        }
        foreach ($ClassID as $index => $item)
        {
            $all['ClassID'] = $item;
            $dir = storage_path('app/public/upload');
            $pictureObj = $request->file("File");
            $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).'.png';
            if ($pictureObj && $pictureObj->isValid())
            {
                $pictureObj->move($dir, $string);
                $all['FilePath'] = asset('storage/upload/' . $string);
            }

            //exercise表中评价编号EvalNO，在同一个班级、学期、科目的EvalNO要保持一致；
            $teachworkitem = $this->model->where("ClassID",$item)->where("SNO",$request->SNO)->where("CourseName",$request->CourseName)->first();
            if($teachworkitem)
            {
                $all['EvalNO'] = $teachworkitem->EvalNO;
            }
            $id = $this->model->saveModel($all);
            if(!$id)
            {
                return $this->errorResponse();
            }
            $tall['HomeWorkNO'] = $id;
            $tall['HomeMode'] = $request->HomeMode;
            $tall['Status'] = 2;
            $studentList = Student::where("ClassID",$item)->get();
            if($studentList)//=====  学生添加作业  =====
            {
                foreach ($studentList as $index1 => $item1)
                {
                    $tall['StuID'] = $item1->StuID;
                    Stuhomework::insert($tall);
                }
            }
        }
        return $this->successResponse(['id'=>$id]);
    }




}
