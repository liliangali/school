<?php

namespace App\Api\V1\Controllers\Stuhomework;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Answerinfo;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Evalbase;
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

class StuhomeworkController extends BaseController {

    public function __construct()
    {
        $this->model = new Stuhomework();
    }

    public function listAT(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if($user->IDLevel != "S") //=====  学生登陆  =====
        {
            return $this->errorResponse('此接口只允许学生使用');
        }
        $student = Student::where("UserID",$user->UserID)->first();
        if(!$student)
        {
            return $this->errorResponse('没有此学生');
        }
        $SNO = Semester::getAuthLast();
        $file_fomat = Fileformat::get()->keyBy("FileNO");
        $stu = Stuhomework::leftJoin("teahomework","stuhomework.HomeWorkNO",'=',"teahomework.HomeWorkNO")->where("StuID",$student->StuID)->where("SNO",$SNO->SNO)->
        select("stuhomework.*","stuhomework.Status","stuhomework.Status","stuhomework.SubTime",'teahomework.CourseName','Format','HomeWorkTitle','DataLine')->get()->map(function ($item) use($file_fomat){
            $item['Format'] = isset($file_fomat[$item['Format']]) ? $file_fomat[$item['Format']]['Description'] : '';
            return $item;
        })->groupBy("Status")->toArray();
        $return['finish'] = isset($stu['1']) ? $stu['1'] : [];
        $return['unfinish'] = isset($stu['2']) ? $stu['2'] : [];
        return $this->successResponse($return);
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
        $info = $info->toArray();
        $home = Teahomework::find($request->HomeWorkNO);
        $info['HomeWorkTitle'] = $home->HomeWorkTitle;
        $info['HomeWorkTitle'] = $home->HomeWorkTitle;
        $info['HomeWorkTitle'] = $home->HomeWorkTitle;
        return $this->successResponse($info);
    }

    /**
     * 老师查看学生作业详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function gettT(Request $request)
    {
        $err = [
            "HomeWorkNO"=>"required|integer",
            "StuID"=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        $info = $this->model->where("HomeWorkNO",$request->HomeWorkNO)->where("StuID",$request->StuID)->first();
        $home = Teahomework::find($request->HomeWorkNO);
        $evalbase = Evalbase::find($home->EvalNO);
        if(!$info)
        {
            return $this->errorResponse("无此作业");
        }
        $return['FilePath']  = $info->FilePath;
        $return['SubTime']  = $info->SubTime;
        $return['EvalName']  = $evalbase->EvalName;
        $return['TotalWeight']  = $evalbase->TotalWeight;
        return $this->successResponse($return);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 全班学生的作业提交情况
     */
    public function classT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'HomeWorkNO'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!(Teahomework::find($request->HomeWorkNO)))
        {
            return $this->errorResponse("作业不存在");
        }
        $lists = $this->model->where('HomeWorkNO',$request->HomeWorkNO)->orderBy('StuWorkNO', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        $student = Student::whereIn("StuID",collect($list)->pluck('StuID')->all())->get()->keyBy("StuID")->toArray();
        $steval = Stueavl::where("HomeWorkNO",$request->HomeWorkNO)->get()->groupBy("MemberID")->toArray();//互评表
        $lists['data'] = collect($list)->map(function ($item) use($student,$steval){
            $item['SeatNO'] = "";
            $item['UName'] = "";
            $item['CivilID'] = "";
            if(isset($student[$item['StuID']]))
            {
                $item['SeatNO'] = $student[$item['StuID']]['SeatNO'];
                $item['UName'] = $student[$item['StuID']]['UName'];
                $item['CivilID'] = $student[$item['StuID']]['CivilID'];
            }
            $item['EvalMark'] = 0;
            $item['EvalNum'] = 0;
            if(isset($steval[$item['StuID']]))
            {
                $ste = collect($steval[$item['StuID']]);
                $item['EvalMark'] = $ste->pluck('Mark')->avg();
                $item['EvalNum'] = $ste->count();
            }
            return $item;
        })->toArray();
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
        $student = Student::where("UserID",$user->UserID)->first();
        $err = [
            'HomeWorkNO'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $stu = $this->model->where("HomeWorkNO",$request->HomeWorkNO)->where("StuID",$student->StuID)->first();
        if(!$stu)
        {
            return $this->errorResponse("无此作业");
        }
        $dir = storage_path('app/public/upload/');
        $pictureObj = $request->file("File");

        $stu->Status = 1;
        $stu->SubTime = date("Y-m-d H:i:s");
        if ($pictureObj && $pictureObj->isValid())
        {
            $string = Carbon::now(config('app.timezone'))->timestamp.str_random(10).'.'.$pictureObj->extension();
            $pictureObj->move($dir, $string);
            $stu->FilePath = asset('storage/upload/' . $string);
        }
        $stu->save();
        return $this->successResponse(['id'=>$stu->StuWorkNO]);
    }

    public function hpT(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $student = Student::where("UserID",$user->UserID)->first();
        $err = [
            'HomeWorkNO'=>"required|integer",
            'MemberID'=>"required|integer",
            'Mark'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        
        $mID = Student::find($request->MemberID);
        if(!$mID)
        {
            return $this->errorResponse("被评价的学生ID不存在");
        }
        $hwork = Teahomework::find($request->HomeWorkNO);
        if(!$hwork)
        {
            return $this->errorResponse("这个作业不存在");
        }
        $model = new Stueavl();
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

    public function zpfT(Request $request) {
        $err = [
//            'HomeWorkNO'=>"required|integer",
//            'Score'=>"required|integer",
//            'TeaEval'=>"required",
            'TotalScore'=>"required",
            'StuScore'=>"required",
            'StuID'=>"required|integer",
            'ItemNO'=>"required",
            'ItemType'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if($request->ItemType == 1) //=====   评论活动 =====
        {
            Exercise::where("ExNO",$request->ItemNO)->update(['EvalScore'=>$request->TotalScore]);
            Answerinfo::where("ExNO",$request->ItemNO)->where("StuID",$request->StuID)->update(['EvalScore'=>$request->StuScore]);
        }
        else//作业
        {
            Teahomework::where("HomeWorkNO",$request->ItemNO)->update(['EvalScore'=>$request->TotalScore]);
            Stuhomework::where("HomeWorkNO",$request->ItemNO)->where("StuID",$request->StuID)->update(['Score'=>$request->StuScore]);
        }
        $StandardNO = $request->StandardNO;
        $Score = $request->Score;
        $data['StuID'] = $request->StuID;
        $data['ItemNO'] = $request->ItemNO;
        $data['ItemType'] = $request->ItemType;
        foreach ((array)$StandardNO as $index => $item)
        {
            $data['StandardNO'] = $item;
            $data['Score'] = isset($Score[$index]) ? $Score[$index] : 0;
            Marking::insert($data);
        }
        return $this->successResponse();
    }

    public function pfT(Request $request) {
        $err = [
            'HomeWorkNO'=>"required|integer",
            'StuID'=>"required|integer",
            'Score'=>"required|integer",
            'TeaEval'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $item = $this->model->where("HomeWorkNO",$request->HomeWorkNO)->where("StuID",$request->StuID)->first();
        if(!$item)
        {
            return $this->errorResponse("这个作业找不到");
        }
        $item->Score = $request->Score;
        $item->TeaEval = $request->TeaEval;
        $item->save();
        return $this->successResponse(['id'=>$item->StuWorkNO]);
    }

    public function putT(Request $request) {
        $err = [
            'TID'=>"required",
            'CourseID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
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
            $this->model->primaryKey=>"required",
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
