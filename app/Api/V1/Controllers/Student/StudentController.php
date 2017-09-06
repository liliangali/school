<?php

namespace App\Api\V1\Controllers\Student;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Student;
use App\User;
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

class StudentController extends BaseController {

    public function __construct()
    {
        $this->model = new  Student();
    }
    public function listT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'ClassID'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $list = Student::where("ClassID",$request->ClassID)->orderBy('StuID', 'desc')->paginate($request->page_size)->toArray();
        return $this->successResponse($list);
    }

    public function getT(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
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
            return $this->errorResponse('StuID不存在');
        }

        return $this->successResponse($this->model->find($StuID)->toArray());
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
            'ClassID'=>"required|max:255",
            'CivilID'=>"required|unique:student",
            'SeatNO'=>"required",
//            'password'=>"required|max:12|min:6",
            'UName'=>"required",
            'Gender'=>"required",
        ];
        if($this->validateResponse($request,$err,['unique' => '此编号号或者座位号已经存在']))
        {
            return $this->errorResponse();
        }
        if((Student::where("SeatNO",$request->SeatNO)->where("ClassID",$request->ClassID)->first()))
        {
            return $this->errorResponse('次班级的座位号已经存在');
        }

        if(!Classes::find($request->ClassID))
        {
            return $this->errorResponse("存班级ID不存在");
        }
        $newItem =$request->all();
//        unset($newItem['password']);
        $id = $this->model->addU($request,$newItem);
        User::where("LoginID",$request->CivilID)->update(["IDLevel"=>"S"]);
        if($id)
        {
            return $this->successResponse(['id'=>$id]);
        }
        return $this->errorResponse('添加失败');
    }

    public function delT(Request $request)
    {

        $err = [
            'StuID'=>"required",
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

    public function putT(Request $request) {
        $err = [
            'StuID'=>"required",
            'CivilID'=>"required",
            'SeatNO'=>"required",
            'UName'=>"required",
            'Gender'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        if(!Classes::find($request->ClassID))
        {
            return $this->errorResponse("存班级ID不存在");
        }
        $newItem =$request->all();
        unset($newItem['password']);
        if($this->model->addU($request,$newItem))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('添加失败');
    }

    public function changeT(Request $request) {
        $err = [
            'StuID'=>"required",
            'ClassID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!Classes::find($request->ClassID))
        {
            return $this->errorResponse("存班级ID不存在");
        }
        $all = $request->all();
        $student = Student::where("ClassID",$request->ClassID)->orderBy('SeatNO', 'desc')->first();
        $SeatNO = isset($student->SeatNO)? $student->SeatNO : 0;
        $new_SeatNO = $SeatNO + 1;
        $all['SeatNO'] = $new_SeatNO;
        if($this->model->saveModel($all))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('添加失败');
    }

}
