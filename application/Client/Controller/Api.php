<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/3/16
 * Time: 17:20
 */

namespace app\Client\Controller;


use app\Client\model\clubinfo;
use app\Client\model\clubuser;
use app\Client\model\connect;
use think\Controller;
use think\Config;

class Api extends Controller
{



    /**
     * 获取用户头像
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getImage(){
        $userid=input('post.userid');//用户id
        if(empty($userid)){
            exitJson(404,'参数为空');
        }
        $user_image=connect::conn_platform()
            ->table('accountssend')
            ->where(['UserID'=>$userid])
            ->field('Head')
            ->findOrEmpty();
        if(empty($user_image)){
            exitJson(403,'无数据');
        }
        exitJson(200,'接收成功',$user_image);
    }
    /**
     * 获取公告信息
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getMessage(){
//        $sign=input('sign/s');
//        echo MD5('rwxjianghu3');
//        exit;
//        if($key!=md5('rwxjianghu3')){
//            exitJson(400,"签名错误");
//        }

        $url=config('config.url');
//        p($config);
//        exit;
//        rynativewebdb
        $list=connect::conn_platform('rynativewebdb')
            ->table('ads')
            ->where(['Type'=>0])
            ->field('id,title,resourceurl,remark')
            ->select();
//        $config=connect::conn_platform('rynativewebdb')
//            ->table('configinfo')
//            ->where(['ConfigKey'=>'SiteConfig'])
//            ->field('field2 as url')
//            ->findOrEmpty();
        foreach($list as $key => $val){
//            p($val);
            if(!empty($val['resourceurl'])){
                $list[$key]['resourceurl']=$url.$val['resourceurl'];
            }
        }
        exitJson(200,'获取成功',$list);
//        p($list);
//        exit;
    }
    /**
     * 获取微信客服信息
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getWechatMessage(){
//        $sign=input('sign/s');
//        if($key!=md5('rwxjianghu3')){
//            exitJson(400,"签名错误");
//        }
        $config=connect::conn_platform('rynativewebdb')
            ->table('configinfo')
            ->where(['ConfigKey'=>'ContactConfig'])
            ->field('Field3,Field4,Field5,Field6,Field7,Field8')
            ->findOrEmpty();

        foreach($config as $key => $val){
            if(!empty($val)){
                if($key!='row_number'){
                    $list[$key]=$val;
                }
            }
        }
        exitJson(200,'获取成功',$list);
    }
    /**
     * 获取战绩
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getExploits(){
        $ClubID=input('post.ClubID/d');
        $UserID=input('post.UserID/d');
        $KindID=input('post.KindID/d');
        $Type=input('post.Type/d');//2,前天,1，昨天,0，今天
        switch ($Type){
            case 0:
                $where='DATEDIFF(DAY,ConcludeTime,GETDATE())=0';
                break;
            case 1:
                $where='DATEDIFF(DAY,ConcludeTime,GETDATE())=1';
                break;
            case 2:
                $where='DATEDIFF(DAY,ConcludeTime,GETDATE())=2';
                break;
            default:
                exitJson(400,'参数错误');
                break;
        }
        if($ClubID==0){
            $list=connect::conn_platform('rytreasuredb')
                ->table('recordusergamebigend')
                ->where(['UserID'=>$UserID,'KindID'=>$KindID,'LockClubID'=>0])
                ->where($where)
                ->field('RoomNum,ConcludeTime,UserID,KindID')
                ->order('ConcludeTime desc')
                ->limit(60)
                ->select();
//                ->toArray();
        }else{
            $list=connect::conn_platform('rytreasuredb')
                ->table('recordusergamebigend')
                ->where(['UserID'=>$UserID,'LockClubID'=>$ClubID,'KindID'=>$KindID])
                ->where($where)
                ->field('RoomNum,ConcludeTime,UserID,KindID')
                ->order('ConcludeTime desc')
                ->limit(60)
                ->select();
//                ->toArray();
        }
        if(empty($list)){
            exitJson(400,'数据为空');
        }else{
            exitJson(200,'获取成功',$list);
        }

    }
    /**
     * 获取用户积分
     * API
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function getUserMatchScore(){
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }

//        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $MatchScore=clubuser::where(['ClubID'=>$ClubID,'UserID'=>$UserID])->field('matchscore')->findOrEmpty()->toArray();
//        writeLog($MatchScore);
//        p($MatchScore);
//        exit;sss
        if($MatchScore){
            exitJson(200, '获取成功',$MatchScore);
        }else{
            exitJson(500, '获取失败');
        }

    }
    /**
     * 获取用户钻石
     * API
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function getUserDiamond(){
        $UserID = input('post.UserID/d');//执行者的user_id
        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($sign)){
            exitJson(400,'参数错误');
        }

//        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $MatchScore=connect::conn_platform('RYTreasureDB')->table('userroomcard')->where(['UserID'=>$UserID])->field('roomcard')->findOrEmpty();
//        writeLog($MatchScore);
//        p($MatchScore);
//        exit;sss
        if($MatchScore){
            exitJson(200, '获取成功',$MatchScore);
        }else{
            exitJson(500, '获取失败');
        }

    }

    /**
     * 获取滚动公告
     * API
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function getScollNotice(){
        $UserID = input('post.UserID/d');//执行者的user_id
        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($sign)){
            exitJson(400,'参数错误');
        }

//        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $NoticeList=connect::conn_platform('rynativewebdb')
            ->table('news')
            ->field('NewsID,Body')
            ->select();
//        writeLog($MatchScore);
//        p($MatchScore);
//        exit;sss
        if($NoticeList){
            exitJson(200, '获取成功',$NoticeList);
        }else{
            exitJson(500, '获取失败');
        }

    }

    /**
     * 储存头像/更新头像
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function saveImageNew(){
        $image_url = input('url/s');//头像网络地址
        $userid = input('userid/d');//user_id
        $ip = input('ip/s');
        $machine = input('machine/s');
        $md5=input('md5/s');
//        校验参数
        if(empty($image_url)||empty($userid)||empty($ip)||empty($machine)){
            exitJson(404,'参数错误');
        }

        $user_image=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])
            ->field('Head')
            ->findOrEmpty();
        if($user_image){
            if($md5==md5($user_image['Head'])){
                exitJson(202,'头像不需更新');
            }
            $update_result=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])->update(['Head'=>$image_url]);
        }else{
            $update_result=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])->insert(['Head'=>$image_url,'UserID'=>$userid]);
        }

        if($update_result){
            exitJson(200,'更新成功');
        }else{
            exitJson(204,'更新失败');
        }
    }

    /**
     * 获取公告
     * 公告
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getClubNotice(){
        $UserID = input('post.UserID/d');//自己的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        //        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $ClubNotice=clubinfo::where([
            ['ClubID','=',$ClubID]
        ])
            ->field('ClubNotice')
            ->findOrEmpty()
            ->toArray();


        if($ClubNotice){
            exitJson(200,'获取公告成功',$ClubNotice['ClubNotice']);
        }else{
            exitJson(500,'获取失败');
        }
    }


}