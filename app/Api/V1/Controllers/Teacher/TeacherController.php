<?php

namespace App\Api\V1\Controllers\Teacher;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
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

class TeacherController extends BaseController {


    public function __construct()
    {
        $this->model = new  Teacher();
    }

    public function listT(Request $request)
    {

        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $list = Teacher::where('SchoolID',Admin::getSchoolId())->orderBy('TID', 'desc')->paginate($request->page_size)->toArray();
        return $this->successResponse($list);
    }


    public function getT(Request $request)
    {
        $err = [
            'TID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        return $this->successResponse($this->model->find($request->TID)->toArray());
        $err = [
            'TID'=>"required",
        ];
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
            'UName'=>"required|max:255",
            'CivilID'=>"required|unique:teacher",
            'password'=>"required|max:12|min:6",
            'Email'=>"required",
            'Phone'=>"required",
            'Gender'=>"required",
        ];
        if($this->validateResponse($request,$err,['unique' => '此编号号已经注册!请勿重复注册']))
        {
            return $this->errorResponse();
        }
        $newItem =$request->all();
        unset($newItem['password']);

        $id = $this->model->addU($request,$newItem);
        if($id)
        {
            return $this->successResponse(['id'=>$id]);
        }
        return $this->errorResponse('添加失败,请检查编号是否重复');
    }

    public function delT(Request $request)
    {
        $err = [
            'TID'=>"required",
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
            'TID'=>"required",
            'UName'=>"required|max:255",
            'CivilID'=>"required",
            'password'=>"max:12|min:6",
            'Email'=>"required",
            'Phone'=>"required",
            'Gender'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $newItem =$request->all();
        unset($newItem['password']);
        if($this->model->addU($request,$newItem))
        {
            return $this->successResponse();
        }
        return $this->errorResponse('添加失败');
    }

}
