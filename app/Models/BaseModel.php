<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    public $schoolID = "SchoolID";
    public $userID = "UserID";
    /**
     * 统一保存方法
     * @param $data
     * @return mixed
     */
    public function saveModel($data)
    {
        $columes = $this->getTableColumns();
        //添加学校ID
        if(in_array($this->schoolID,$columes))
        {
            $data['SchoolID'] = Admin::getSchoolId();
        }
        foreach ($data as $index => $item)
        {
            if(!in_array($index,$columes))
            {
                unset($data[$index]);
            }
        }

        if(isset($data[$this->getKeyName()]) && $data[$this->getKeyName()])
        {
            $key_name = $data[$this->getKeyName()];
            if(!$this->find($key_name))
            {
                return false;
            }
            unset($data[$this->getKeyName()]);
            if($this->where($this->getKeyName(),$key_name)->update($data) === false)
            {
                return false;
            }
            return true;
        }
        else
        {
            unset($data[$this->getKeyName()]);
            return $this->insertGetId($data);
            
        }
    }

    public function addU($req,$newItem)
    {
        if(isset($req->{$this->primaryKey}) && ! ($req->{$this->primaryKey}))
        {
            unset($req->{$this->primaryKey});
        }

        
        if(isset($req->{$this->primaryKey}) && $req->{$this->primaryKey})//更新
        {
            $newItem[$this->primaryKey] = $req->{$this->primaryKey};
            $item = $this->find($req->{$this->primaryKey});
            if(!$item)
            {
                return false;
            }
            $user = $this->saveUser($req,$item);

            if($user === false)
            {
                return false;
            }
        }
        else//新增
        {
            $user = $this->saveUser($req);
            if($user === false)
            {
                return false;
            }
            $newItem['UserID'] = $user->UserID;
        }
        
        return $this->saveModel($newItem);
    }

    public function saveUser($req,$item='')
    {

        $newUser = [
            'LoginID' => $req->CivilID,
            'IDLevel' => "S",
        ];
        if(isset($req->password))
        {
            $newUser['password'] = $req->password;
        }


        if($item)//更新
        {
            $newUser['UserID'] = $item->UserID;
        }

        return User::add($newUser);
    }

    public function del($req)
    {
        $item = $this->find($req->{$this->primaryKey});
        if(!$item)
        {
            return false;
        }

        if(isset($item->UserID) && $item->UserID)
        {
            $user = User::find($item->UserID);
            $user->delete();
        }
        $item->delete();
        return true;
    }


    /**
     * 获取表结构
     * @return array
     */
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }
}
