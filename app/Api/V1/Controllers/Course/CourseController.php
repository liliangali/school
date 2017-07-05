<?php

namespace App\Api\V1\Controllers\Course;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
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

class CourseController extends BaseController {

    public function __construct()
    {
        $this->model = new Course();
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

        if($request->AcademicYear && $request->SOrder)
        {
            $semester = Semester::where('AcademicYear',$request->AcademicYear)->where('SOrder',$request->SOrder)->first();
            if(!$semester)
            {
                $semester = Semester::first();
            }
        }
        else
        {
            $semester = Semester::first();
        }
        $lists = Course::where('ClassID',$request->ClassID)->where('SNO',$semester->SNO)->orderBy('ClassID', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        foreach ((array)$list as $index => $item)
        {
            $teacher = Teacher::find($item['TID']);
            $list[$index]['UName'] = isset($teacher->UName) ? $teacher->UName : '';
        }
        $lists['data'] = $list;
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
        $err = [
            'SNO'=>"required",
            'ClassID'=>"required",
            'CourseName'=>"required",
            'TID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!Classes::find($request->ClassID))
        {
            return $this->errorResponse("此班级ID不存在");
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
            'TID'=>"required",
            'CourseID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

//        if(!Teacher::find($request->TID))
//        {
//            return $this->errorResponse("此老师ID不存在");
//        }
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
