<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/2
 * Time: 16:22
 */
namespace app\Manage\Controller;

use app\Manage\model\acc_acinfo;
use app\Manage\model\club;

class User extends Common
{
    protected $login_status=false;
    /**
     * 公共方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->login_status=session('login_status');
        if(session('login_flag')!=config('config.salt')){
            abort(404,'页面不存在');
        }
//        p(session(''));
//        exit;
        if($this->login_status==false){
            exit('身份丢失');
        }
    }
    /**
     * 用户列表
     * 茶楼
     *
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function user(){
        $user_list=acc_acinfo::getAllUserList();
//        p($user_list);
//        exit;
        $page = $user_list->render();
        $data=$user_list->toArray();
//        p($data);
//        exit;
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch();
    }
    /**
     * 茶楼列表
     * 茶楼
     *
     * $ClubID      俱乐部id
     * $DeletedUserID    被删除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function teaHouse(){
        $type=input('type/s');
//        查询
        if($type=='search'){
            $condition=input('condition/s');
            $SearchList=club::teaHouseSearch($condition);
            $page = $SearchList->render();
            $data=$SearchList->toArray();
            $this->assign('list',$data['data']);
            $this->assign('page',$page);
            return $this->fetch();
        }
        $user_list=club::getAllTeaHouseList();
        $page = $user_list->render();
        $data=$user_list->toArray();
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch();
    }
    public function presented(){
        $user_id=input('get.userid/d');
        $user_info=acc_acinfo::getUserInfo($user_id);
//        p($user_info);
//        exit;

        $this->assign('data',$user_info);
        return $this->fetch();
    }

    /**
     * 某个茶楼的用户列表
     * 茶楼
     *
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function ClubUser(){
        $type=input('type/s');
        $ClubID=input('ClubID/d');
//        查询
        if($type=='search'){
            $condition=input('condition/s');
            $SearchList=club::teaHouseSearch($condition);
            $page = $SearchList->render();
            $data=$SearchList->toArray();
            $this->assign('list',$data['data']);
            $this->assign('page',$page);
            return $this->fetch();
        }
        $user_list=club::getClubUserListByClubID($ClubID);
        $page = $user_list->render();
        $data=$user_list->toArray();
//        p($data);
//        exit;
//        p($data);
//        exit;
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch('user');
    }
//    private function teaHouseSearch($condition){
//        $user_list=club::getAllTeaHouseList();
//    }
}