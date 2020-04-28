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
use think\Controller;
use Think\Db;

class UserBusiness extends Controller
{
    protected $login_status=false;
    /**
     * 公共方法
     */
    public function initialize()
    {
//        p($_SESSION);
//        exit;
//        parent::initialize();
        $this->login_status=session('login_status','','think');
//        p(session(''));
//        exit;
        if($this->login_status==false){
//            exit('身份丢失');
        }
    }
    /**
     * 冻结和解冻茶楼
     * 茶楼
     *
     * $ClubID      俱乐部id
     * $DeletedUserID    被删除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function thaw_freeze(){
        $ClubID = input('ClubID/d');
        writeLog($ClubID);
//        验证参数
        if(empty($ClubID)){
            return json([
                'code'=>400,
                'msg'=>'参数错误'
            ]);
        }
//        验证茶楼是否存在
        $ClubInfo=club::getClubInfo($ClubID);
//        p($ClubInfo);
//        exit;
        if(empty($ClubInfo)){
            return json([
                'code'=>400,
                'msg'=>'俱乐部不存在'
            ]);
        }
//        判断茶楼当前状态，若是1则改为0，若是0则改为1
        if($ClubInfo['ClubStatus']==1){
            $updateStatus=0;
        }else{
            $updateStatus=1;
        }
        Db::startTrans();
        $updateResult=club::conn_platform()->table('clubinfo')->where(['ClubID'=>$ClubID])->update(['ClubStatus'=>$updateStatus]);
        if($updateResult==1){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>'冻结成功']);
        }else{
            Db::rollback();//回滚
            return json([404,'冻结失败']);
        }
    }

    /**
     * 冻结和解冻用户
     * 茶楼
     *
     * $ClubID      俱乐部id
     * $DeletedUserID    被删除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function user_thaw_freeze(){
        $UserID = input('UserID/d');
//        writeLog($UserID);
//        验证参数
        if(empty($UserID)){
            return json([
                'code'=>400,
                'msg'=>'参数错误'
            ]);
        }
//        验证用户是否存在
        $UserInfo=acc_acinfo::conn_accounts()->table('accountsinfo')->where(['UserID'=>$UserID])->field('StunDown')->findOrEmpty();
//        p($UserInfo);
//        exit;
//        writeLog($UserInfo);
        if(empty($UserInfo)){
            return json([
                'code'=>400,
                'msg'=>'俱乐部不存在'
            ]);
        }
//        判断茶楼当前状态，若是1则改为0，若是0则改为1
        if($UserInfo['StunDown']==1){
            $updateStatus=0;
            $msg='解冻';
        }else{
            $updateStatus=1;
            $msg='冻结';
        }
        Db::startTrans();
        $updateResult=acc_acinfo::conn_accounts()->table('accountsinfo')->where(['UserID'=>$UserID])->update(['StunDown'=>$updateStatus]);
        if($updateResult==1){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>$msg.'成功']);
        }else{
            Db::rollback();//回滚
            return json(['code'=>404,'msg'=>$msg.'失败']);
        }
    }
}