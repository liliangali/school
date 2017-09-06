<?php

namespace App\Api\V1\Controllers\School;
use App\Api\V1\Controllers\BaseController;
use App\Models\Admin;
use App\Models\Classes;
use App\Models\Course;
use App\Models\Educational;
use App\Models\Error;
use App\Models\Exercise;
use App\Models\School;
use App\Models\Semester;
use App\Models\Student;
use App\User;
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

class SchoolController extends BaseController {

    public function __construct()
    {
        $this->model = new School();
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
        $user = JWTAuth::parseToken()->authenticate();
        $admin = Admin::where("UserID",$user->UserID)->first();
        $lists = School::orderBy('SchoolID', 'desc')->paginate($request->page_size)->toArray();
        $edu = Educational::get()->keyBy("EduID")->toArray();
        $list = $lists['data'];
        $lists['data'] = collect($list)->map(function ($item) use($admin,$edu){
//            $item['UName'] = $admin->UName;
//            $item['AdminID'] = $admin->AdminID;
            $item['EduName'] = '';
            if(isset($edu[$item['Type']]))
            {
                $item['EduName'] = $edu[$item['Type']]['EduName'];
            }
            return $item;
        })->toArray();
        return $this->successResponse($lists);
    }

    public function listaT(Request $request)
    {
        $err = [
            'page'=>"required|integer",
            'page_size'=>"required|integer",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $SchoolID = isset($request->SchoolID) ? $request->SchoolID : 0;
        $user = JWTAuth::parseToken()->authenticate();
        if($SchoolID)
        {
            $lists = Admin::where("SchoolID",$SchoolID)->orderBy('SchoolID', 'desc')->paginate($request->page_size)->toArray();
        }
        else
        {
            if($user == "U")
            {
                $admin = Admin::where("UserID",$user->UserID)->first();
                $lists = Admin::where("SchoolID",$admin->SchoolID)->orderBy('SchoolID', 'desc')->paginate($request->page_size)->toArray();
            }
            else
            {
                $lists = Admin::orderBy('SchoolID', 'desc')->paginate($request->page_size)->toArray();
            }
        }

        return $this->successResponse($lists);
    }

    public function tokenT(Request $request)
    {
        $SchoolID = User::getSchool();
        if($SchoolID)
        {
            $list['scount'] = Student::where("SchoolID",$SchoolID)->count();
            $list['tcount'] = Teacher::where("SchoolID",$SchoolID)->count();
            $list['acount'] = $list['tcount'] + $list['scount'];
            $list['ccount'] = Classes::where("SchoolID",$SchoolID)->count();
            $list['schoolcount'] = 0;
        }
        else
        {
            $list['scount'] = Student::count();
            $list['tcount'] = Teacher::count();
            $list['acount'] = $list['tcount'] + $list['scount'];
            $list['ccount'] = Classes::count();
            $list['schoolcount'] = School::count();
        }

        return $this->successResponse($list);
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
            'SchoolName'=>"required",
            'SchoolCode'=>"required|unique:school",
            'Address'=>"required",
            'Telephone'=>"required",
            'EduID'=>"required",
        ];
        if(!(Educational::find($request->Type)))
        {
            return $this->errorResponse("学制ID不存在");
        }
        if($this->validateResponse($request,$err,['unique' => '学校编号号已经注册!请勿重复添加']))
        {
            return $this->errorResponse();
        }
        $all = $request->all();
        $all['Type'] = $all['EduID'];
        unset($all['token']);
        unset($all['EduID']);
        $id = School::insertGetId($all);
        return $this->successResponse(['id'=>$id]);
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

    public function addA(Request $request) {
        $err = [
            'UName'=>"required",
            'CivilID'=>"required|unique:admin",
            'Gender'=>"required",
//            'password' => 'required|max:12|min:6',
        ];
        if($this->validateResponse($request,$err,['unique' => '学校编号号已经注册!请勿重复添加']))
        {
            return $this->errorResponse();
        }
        $user = JWTAuth::parseToken()->authenticate();
        if($user->IDLevel == 'U')
        {
            $SchoolID = User::getSchool();
        }
        else
        {
            if(!isset($request->SchoolID))
            {
                return $this->errorResponse('SchoolID is not empty');
            }
            $SchoolID = $request->SchoolID;
        }

        if(User::where("LoginID",$request->CivilID)->first())
        {
            return $this->errorResponse('登陆账号重复');
        }

        
        $udata['LoginID'] = $request->CivilID;
        $udata['IDLevel'] = "U";
        $udata['password'] = 123456;
        if(!User::add($udata))
        {
            return $this->errorResponse('失败');
        }
        $user = User::where("LoginID",$request->CivilID)->first();
        $all = $request->all();
        unset($all['token']);
        unset($all['password']);
        $all['SchoolID'] = $SchoolID;
        $all['UserID'] = $user->UserID;
        Admin::insert($all);
        return $this->successResponse();
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

    public function putA(Request $request) {
        $err = [
//            'SchoolID'=>"required",
            'UName'=>"required",
            'CivilID'=>"required",
            'Gender'=>"required",
//            'password' => 'max:12|min:6',
            'AdminID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }

        $user = JWTAuth::parseToken()->authenticate();
        if($user->IDLevel == 'U')
        {
            $SchoolID = User::getSchool();
        }
        else
        {
            if(!isset($request->SchoolID))
            {
                return $this->errorResponse('SchoolID is not empty');
            }
            $SchoolID = $request->SchoolID;
        }


        $admin = Admin::find($request->AdminID);
        if(!$admin)
        {
            return $this->errorResponse('empty');
        }
        if(Admin::where("CivilID",$request->CivilID)->where("AdminID",'!=',$request->AdminID)->first())
        {
            return $this->errorResponse('登陆账号已存在');
        }
        if(isset($request->password))
        {
            $udata['password'] = $request->password;
        }
        $udata['UserID'] = $admin->UserID;
        $udata['LoginID'] = $request->CivilID;
        $udata['IDLevel'] = "U";
        if(!User::add($udata))
        {
            return $this->errorResponse('失败,编号已存在');
        }
        $all = $request->all();
        $all['SchoolID'] = $SchoolID;
        unset($all['token']);
        unset($all['password']);
        unset($all['AdminID']);
        Admin::where("AdminID",$request->AdminID)->update($all);
        return $this->successResponse();
    }

    public function putT(Request $request) {

        $err = [
            'SchoolID'=>"required",
            'SchoolCode'=>"required",
            'Address'=>"required",
            'Telephone'=>"required",
            'EduID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!School::find($request->SchoolID))
        {
            return $this->errorResponse("此学校不存在");
        }
        if(School::where("SchoolID",'!=',$request->SchoolID)->where("SchoolCode",$request->SchoolCode)->first())
        {
            return $this->errorResponse("此编号重复");
        }
        $all = $request->all();
        $all['Type'] = $all['EduID'];
        $SchoolID = $all['SchoolID'];
        unset($all['token']);
        unset($all['SchoolID']);
        unset($all['EduID']);
        School::where("SchoolID",$SchoolID)->update($all);
        return $this->successResponse();
    }
    public function delA(Request $request)
    {
        $err = [
            'AdminID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        $admin = Admin::find($request->AdminID);
        if(!$admin)
        {
            return $this->errorResponse("不存在");
        }
        User::where("UserID",$admin->UserID)->delete();
        $admin->delete();
        return $this->successResponse();
    }
    public function delT(Request $request)
    {
        $err = [
            'SchoolID'=>"required",
        ];
        if($this->validateResponse($request,$err))
        {
            return $this->errorResponse();
        }
        if(!(School::find($request->SchoolID)))
        {
            return $this->errorResponse('删除失败');
        }
        $SchoolID = $request->SchoolID;
        //删除Admin、Teacher、Studen User Semester  Course
        School::delUser([new Admin(),new Teacher(),new Student(),new Semester(),new Course()],$SchoolID);
        //=====   删除class 和活动表 =====
        Exercise::delByClassID($SchoolID);
        School::where("SchoolID",$SchoolID)->delete();
        return $this->successResponse();
    }
    
    public function semesterT(Request $request)
    {
        $SchoolID = User::getSchool();
        if(!$SchoolID)
        {
            return $this->errorResponse('学校失败');
        }
        $AcademicYear = date("Y");
        $m = date("n");
        if($m >= 9 && $m <= 12) //上学期
        {
            $SOrder = "上";
        }
        else
        {
            $AcademicYear = $AcademicYear-1;
            $SOrder = "下";
        }
        $data['SchoolID'] = $SchoolID;
        $data['AcademicYear'] = $AcademicYear;
        $data['SOrder'] = $SOrder;
        if(Semester::where($data)->first())
        {
            return $this->errorResponse('学期已经创建成功,不可重复创建');
        }
        $se_id = Semester::insertGetId($data);
        if(!$se_id)
        {
            return $this->errorResponse('学期创建失败');
        }
        $se_list = Semester::where("SchoolID",$SchoolID)->orderBy("SNO","DESC")->get()->toArray();
        $lastt = isset($se_list[1]) ? $se_list[1] : [];
        if(!$lastt)
        {
            return $this->successResponse();
        }
        $l_se_id = $se_list[1]['SNO'];
        $course_list = Course::where("SchoolID",$SchoolID)->where("SNO",$l_se_id)->get();
        if(!$course_list)
        {
            return $this->successResponse();
        }
        $couse = new Course();
        $course_list->map(function ($item) use ($couse,$se_id){
            $data['TID'] = $item['TID'];
            $data['ClassID'] = $item['ClassID'];
            $data['CourseName'] = $item['CourseName'];
            $data['SchoolID'] = $item['SchoolID'];
            $data['TID'] = $item['TID'];
            $data['SNO'] = $se_id;
            $couse->insert($data);
        });
        return $this->successResponse();
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

    public function eduT(Request $request)
    {
        return $this->successResponse(Educational::get());
    }


}
