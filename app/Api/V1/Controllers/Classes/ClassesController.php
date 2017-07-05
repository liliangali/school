<?php

namespace App\Api\V1\Controllers\Classes;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
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

class ClassesController extends BaseController {

    public function __construct()
    {
        $this->model = new Classes();
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
        $sarr  = [];
        $semester = Semester::getLast();
        $sarr[] = ['SchoolID', Admin::getSchoolId()];
        if(isset($request->CreatTime) && $request->CreatTime)
        {
            $CreatTime =  $semester->AcademicYear - $request->CreatTime + 1;
            $sarr[] = ['CreatTime', $CreatTime];
        }
        $lists = Classes::where($sarr)->orderBy('ClassID', 'desc')->paginate($request->page_size)->toArray();

        $list = $lists['data'];
        foreach ((array)$list as $index => $item)
        {
            $list[$index]['AcademicYear'] = $semester->AcademicYear;
            $list[$index]['SOrder'] = $semester->SOrder;
            $list[$index]['grade'] = $semester->AcademicYear - $item['CreatTime'] + 1  ;
            $list[$index]['Stucount'] = Student::where("ClassID",$item['ClassID'])->count();
            $techer = Teacher::find($item['TID']);
            $list[$index]['uname'] = isset($techer->UName) ? $techer->UName : '';
            $school = School::find($item['SchoolID']);
            $list[$index]['school'] = isset($school->SchoolName) ? $school->SchoolName : '';
        }
        $lists['data'] = $list;
        return $this->successResponse($lists);
    }

    public function getT(Request $request)
    {
        $err = [
            'ClassID'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $list = Classes::find($request->ClassID)->toArray();
        $semester = Semester::getLast();
        $list['SOrder'] = $semester->SOrder;
        $list['AcademicYear'] = $semester->AcademicYear;
        $list['grade'] = $semester->AcademicYear - $list['CreatTime'] + 1  ;;
        $list['Stucount'] = Student::where("ClassID",$list['ClassID'])->count();
        $techer = Teacher::find($list['TID']);
        $list['uname'] = isset($techer->UName) ? $techer->UName : '';
        $school = School::find($list['SchoolID']);
        $list['school'] = isset($school->SchoolName) ? $school->SchoolName : '';
        return $this->successResponse($list);
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
