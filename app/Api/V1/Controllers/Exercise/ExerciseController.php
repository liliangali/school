<?php

namespace App\Api\V1\Controllers\Classes;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Error;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Http\Request;
use Validator;

use JWTAuth;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;
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

    public function rep(Request $request)
    {

//        $postStr = file_get_contents("php://input");
//        $path = "log.txt";
//        echo '<pre>';print_r($_POST);exit;
//        $aa = file_get_contents("php://input");
//        $rs = file_put_contents($path,$aa);
        $all = $request->all();
//        Storage::disk('local')->put('public/log1.txt', ($all));
        $CONTENT = $all['CONTENT'];
//        $convert = mb_convert_encoding($CONTENT,  "Big5","UTF-8"); // 將原來的 big5 轉換成 UTF-8
//        echo '<pre>';print_r($convert);exit;
        
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xml= json_decode(json_encode(simplexml_load_string($CONTENT, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        foreach ($xml as $index => $item)
        {
            foreach ($item as $index1 => $item1)
            {

                foreach ($item1 as $index2 => $item2)
                {
                    if(is_string($item2))
                    {
                        $convert = mb_convert_encoding($item2,"Big5", "UTF-8") ; // 將原來的 big5 轉換成 UTF-8
                        $xml[$index][$index1][$index2] = $convert;
//                        echo $convert;
                    }

                }
                if($item1['ITEMCOUNT'] > 0)
                {
//                    $a = $item1['ITEMS']['ITEMINFO'];
                    foreach ($item1['ITEMS']['ITEMINFO'] as $index3 => $item4)
                    {
                        if(is_string($item4))
                        {
                            $convert = mb_convert_encoding($item4,"Big5", "UTF-8") ; // 將原來的 big5 轉換成 UTF-8
                            $xml[$index][$index1]['ITEMS']['ITEMINFO'][$index3] = $convert;
                        }
//                        echo $convert;
                    }
                }

//                echo '<pre>';print_r($index1);
//                if($index1)
//                {
//
//                }
//
//                echo $item1['CLASSNAME'];
//                $encode = mb_detect_encoding($item1['CLASSNAME'], array("ASCII","UTF-8","GB2312","GBK","BIG5"));
//                echo $encode;


            }
        }
//echo '<pre>';print_r($xml);exit;
        
        $all['CONTENT'] = $xml;
        if(is_array($all)) 
        {
        }
        $re = json_encode($all);

        echo '<pre>';print_r(json_encode($all['CONTENT']));
        echo '<pre>';print_r($all['CONTENT']);

        Storage::disk('local')->put('public/log1.txt', base64_encode(serialize($all['CONTENT'])));
        Error::insert(['content'=>($re)]);
//        echo '<pre>';print_r($all);exit;

        return $xml;


//        Error::insert(['content'=>json_encode($request->all())]);
echo '<pre>';print_r($request->all());exit;
        
        Error::insert(['content'=>$postStr]);
        echo '<pre>';print_r(11);exit;
    }



}