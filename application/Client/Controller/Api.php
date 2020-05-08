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
use think\facade\Cache;
use think\Controller;
use think\Config;

class Api extends Controller
{


    /**
     * 进入大厅请求接口
     * API
     * 合并getMessage，getWechatMessage，getScollNotice，saveImageNew，getUserDiamond
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getEssentialInformation(){
        $UserID = input('post.UserID/d');//执行者的user_id
        $image_url = input('post.url/s');//头像网络地址
        $md5=input('post.md5/s');
        $sign=input('post.sign/s');//签名
        if(empty($UserID)||empty($image_url)||empty($md5)||empty($sign)){
            exitJson(400,'参数错误');
        }
        //        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        $data['getMessage']=Cache::get('getMessage');
        if(empty($data['getMessage'])){
            $data['getMessage']=self::getMessage();
        }

        $data['getWechatMessage']=Cache::get('getWechatMessage');
        if(empty($data['getWechatMessage'])){
            $data['getWechatMessage']=self::getWechatMessage();
        }
//        $data['getWechatMessage']=self::getWechatMessage();
        $data['getScollNotice']=Cache::get('getScollNotice');
        if(empty($data['getScollNotice'])){
            $data['getScollNotice']=self::getScollNotice();
        }
//        $data['getScollNotice']=self::getScollNotice();
        $data['saveImageNew']=self::saveImageNew($image_url,$UserID,$md5);
//        $data['getUserDiamond']=self::getUserDiamondPrivate($UserID);

        exitJson(200,'获取成功',$data);
    }


    /**
     * 进入俱乐部请求接口
     * API
     * 合并getUserMatchScore，getUserDiamond，getClubNotice，getBusinessCard，getRecordStatus
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getEnterClubApi(){
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/s');//俱乐部id
        $sign=input('post.sign/s');//签名
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        //        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }


//        获取记录状态
//        $data['getRecordStatus']=Cache::get('getRecordStatus');
//        if(empty($data['getRecordStatus'])){
            $data['getRecordStatus']=NewSelect::getRecordStatus($UserID,$ClubID);
//        }

//        获取俱乐部公告
//        $data['getClubNotice']=Cache::get('getClubNotice');
//        if(empty($data['getClubNotice'])){
            $data['getClubNotice']=self::getClubNotice($UserID,$ClubID);
//        }


//        获取名片信息
//        $data['getBusinessCard']=Cache::get('getBusinessCard');
//        if(empty($data['getBusinessCard'])){
            $data['getBusinessCard']=NewSelect::getBusinessCard($UserID,$ClubID);
//        }
//        $data['getWechatMessage']=self::getWechatMessage();


//        $data['getScollNotice']=self::getScollNotice();

//        获取用户钻石
        $data['getUserDiamond']=self::getUserDiamondPrivate($UserID);


//        获取用户积分
        $data['getUserMatchScore']=self::getUserMatchScorePrivate($UserID,$ClubID);

//        $data['getUserDiamond']=self::getUserDiamondPrivate($UserID);

        exitJson(200,'获取成功',$data);
    }

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
     * 获取战绩总计
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getStatistics(){
        $ClubID=input('post.ClubID/d');
        $UserID=input('post.UserID/d');
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

            $data=connect::conn_platform('rytreasuredb')
                ->table('recordusergamebigend')
                ->where(['UserID'=>$UserID,'LockClubID'=>$ClubID])
                ->where($where)
                ->field('count(1) as count,sum(WinScore) as sum,sum(BigWiner) as winner')
                ->findOrEmpty();
            $data['sum']=$data['sum']==null?0:$data['sum'];
        $data['winner']=$data['winner']==null?0:$data['winner'];
        if(empty($data)){
            exitJson(400,'获取失败');
        }else{
            exitJson(200,'获取成功',$data);
        }

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
//          p($data);
//          exit;
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
        $MatchScore=connect::conn_platform('rytreasuredb')->table('userroomcard')->where(['UserID'=>$UserID])->field('roomcard')->findOrEmpty();
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
     * 获取公告
     * 公告
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    private function getClubNotice($UserID,$ClubID){
//        $UserID = input('post.UserID/d');//自己的user_id
//        $ClubID = input('post.ClubID/d');//俱乐部id
//        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($ClubID)){
            return(['code'=>400,'msg'=>'参数错误']);
        }
        //        签名
//        $status=getSignForApi(input('post.'));
//        if($status==false){
//            exitJson(403,'签名错误');
//        }

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
            return(['code'=>200,'msg'=>'获取公告成功','data'=>$ClubNotice['ClubNotice']]);
        }else{
            return(['code'=>500,'msg'=>'获取失败']);
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ////////////////////////////////////////内部API接口///////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 获取公告信息
     * 内部接口API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    private function getMessage(){
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
        Cache::set('getMassage',$list,3000);
        return(['code'=>200,'msg'=>'获取成功','data'=>$list]);
//        p($list);
//        exit;
    }
    /**
     * 获取微信客服信息
     * 内部API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    private function getWechatMessage(){
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
        Cache::set('getWechatMessage',$list,3000);
//        exitJson(200,'获取成功',$list);
        return(['code'=>200,'msg'=>'获取成功','data'=>$list]);
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

    private function getScollNotice(){
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $NoticeList=connect::conn_platform('rynativewebdb')
            ->table('news')
            ->field('NewsID,Body')
            ->cache('getScollNotice',0)
            ->select();
//        writeLog($MatchScore);
//        p($MatchScore);
//        exit;sss
        Cache::set('getScollNotice',$NoticeList,3000);
        if($NoticeList){
            return(['code'=>200,'msg'=>'获取成功','data'=>$NoticeList]);
        }else{
            return(['code'=>500,'msg'=>'获取失败']);
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
    private function saveImageNew($image_url,$userid,$md5){
//        校验参数
        if(empty($image_url)||empty($userid)||empty($md5)){
            return(['code'=>400,'msg'=>'参数错误']);
        }

        $user_image=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])
            ->field('Head')
            ->findOrEmpty();
        if($user_image){
            if($md5==md5($user_image['Head'])){
                return(['code'=>202,'msg'=>'头像不需要更新']);
            }
            $update_result=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])->update(['Head'=>$image_url]);
        }else{
            $update_result=connect::conn_platform()->table('accountssend')->where(['UserID'=>$userid])->insert(['Head'=>$image_url,'UserID'=>$userid]);
        }

        if($update_result){
            return(['code'=>200,'msg'=>'更新成功']);
        }else{
            return(['code'=>204,'msg'=>'更新失败']);
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

    private function getUserDiamondPrivate($UserID){

        if(empty($UserID)){
            return(['code'=>400,'msg'=>'参数错误']);
        }

        $MatchScore=connect::conn_platform('rytreasuredb')->table('userroomcard')->where(['UserID'=>$UserID])->field('roomcard')->findOrEmpty();

        if($MatchScore){
            return(['code'=>200,'msg'=>'获取成功','data'=>$MatchScore]);
        }else{
            return(['code'=>500,'msg'=>'获取失败']);
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

    private function getUserMatchScorePrivate($UserID,$ClubID){
//        $UserID = input('post.UserID/d');//执行者的user_id
//        $ClubID = input('post.ClubID/d');//俱乐部id
//        $sign=input('post.sign/s');//签名

        if(empty($UserID)||empty($ClubID)){
            return(['code'=>400,'msg'=>'参数错误']);
        }


        $MatchScore=clubuser::where(['ClubID'=>$ClubID,'UserID'=>$UserID])->field('matchscore')->findOrEmpty()->toArray();

        if($MatchScore){
            return(['code'=>200,'msg'=>'获取成功','data'=>$MatchScore]);
        }else{
            return(['code'=>500,'msg'=>'获取失败']);
        }

    }
}