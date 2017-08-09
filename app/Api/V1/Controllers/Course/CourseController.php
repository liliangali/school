<?php

namespace App\Api\V1\Controllers\Course;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
use App\Models\School;
use App\Models\Semester;
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
        $lists = Course::where('ClassID',$request->ClassID)->where('SNO',$semester->SNO)->orderBy('CourseID', 'desc')->paginate($request->page_size)->toArray();
        $list = $lists['data'];
        foreach ((array)$list as $index => $item)
        {

            $teacher = Teacher::find($item['TID']);
            $list[$index]['UName'] = isset($teacher->UName) ? $teacher->UName : '';
        }
        $lists['data'] = $list;
        return $this->successResponse($lists);
    }

    public function listAT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        //=====  获取最新学年  =====
        if($request->AcademicYear && $request->SOrder)
        {
            $semester = Semester::where('AcademicYear',$request->AcademicYear)->where('SOrder',$request->SOrder)->first();
            if(!$semester)
            {
                return $this->errorResponse('当前学年不存在');
                $semester = Semester::getAuthLast();
            }
        }
        else
        {
            $semester = Semester::getAuthLast();
        }
        $Teacher =Teacher::where("SchoolID",$semester->SchoolID)->get()->keyBy("TID")->toArray();
        $course = Course::where("SNO",$semester->SNO)->get()->map(function ($item) use($Teacher) {
             $item['UName'] = '';
            if(isset($Teacher[$item['TID']]))
            {
                $item['UName'] = $Teacher[$item['TID']]['UName'];
            }
            return $item;
        })->groupBy("CourseName")->keys()->toArray();
        $AcademicYear = $semester->AcademicYear;
        $class = Classes::where("SchoolID",$semester->SchoolID)->get()->groupBy('CreatTime')->toArray();
        $grade_list = [];
        $AcademicYear = date("Y");
        foreach ((array)$class as $index => $item)//按照年级来分组整理所有的班级
        {
            $grade  = $AcademicYear -  $index + 1;
            $grade_list[$grade]['grade'] = $grade;
            $gr = [];
            $course_a = [];//这样得出年级下所有班级的所有课程
            foreach ($item as $index1 => $item1)//该年级下的所有班级
            {
                $a = $course;
                $b = [];
                foreach ((array)$a as $index3 => $item3)
                {
                    $i = [];
                    $i['CourseName'] = $item3;
                    $cu_Info = Course::where("CourseName",$item3)->where("ClassID",$item1['ClassID'])->first();
                    if(!($cu_Info))
                    {
                        $i['UName'] = "";
                    }
                    else
                    {
                        $i['UName'] = $Teacher[$cu_Info->TID]['UName'];
                    }
                    $b[] = $i;
                }

                $item1['Course'] = $b;
                $grade_list[$grade]['class_list'][] = $item1;

            }
            $grade_list[$grade]['course_list'] = $course;
        }
        return $this->successResponse(array_values($grade_list));
    }
    public function listIT(Request $request)
    {
        $err = [
            "CourseID"=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $info = $this->model->find($request->CourseID)->toArray();
        if(!$info)
        {
            return $this->errorResponse();
        }
        $teacher = Teacher::find($info['TID']);
        $info["UName"] = isset($teacher->UName) ? $teacher->UName : '';
        return $this->successResponse($info);
    }

    public function listST(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
            'SchoolID'=>"required|integer",
        ];
        $SchoolID = User::getSchool();
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
        $lists = Course::where('SchoolID',$SchoolID)->where('SNO',$semester->SNO)->orderBy('CourseID', 'desc')->paginate($request->page_size)->toArray();
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
        
 
//        $ClassID = [52,45];
//        $course = [['TID'=>8,'CourseName'=>'语文课'],['TID'=>13,'CourseName'=>'数学课']];
//        $a = json_encode($ClassID);
//
//        echo '<pre>';print_r(json_encode($course));exit;
        
        $err = [
            'ClassID'=>"required",
            'course'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

//        if(!Classes::find($request->ClassID))
//        {
//            return $this->errorResponse("此班级ID不存在");
//        }
//        if(!Teacher::find($request->TID))
//        {
//            return $this->errorResponse("此老师ID不存在");
//        }
        $seme = Semester::getAuthLast();

        $ClassID = $request->ClassID;
        $course = $request->course;
        if(!$ClassID || !$course)
        {
            return $this->errorResponse("ClassID和Course 必须是json形式的数组格式");
        }
        $adata['SNO'] =$seme->SNO;
        foreach ($ClassID as $index => $item)
        {
            foreach ($course as $index1 => $item1)
            {
                $adata['ClassID'] = $item;
                $adata['CourseName'] = $item1['CourseName'];
                $adata['TID'] = $item1['TID'];
                $this->model->saveModel($adata);
            }
        }

        return $this->successResponse();
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
        $TID = json_decode($request->TID,1);
        $CourseID = json_decode($request->CourseID,1);
        foreach ((array)$CourseID as $index => $item)
        {
            $adata['TID'] = $item;
            $adata['CourseID'] = $CourseID[$index];
            $this->model->saveModel($adata);
        }
        return $this->successResponse();
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
