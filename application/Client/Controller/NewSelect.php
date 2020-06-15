<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/3/2
 * Time: 14:28
 */

namespace app\Client\Controller;
use app\Client\model\clubinfo;
use app\Client\model\clubuser;
use app\Client\model\clubuseroutinrecord;
use app\Client\model\clubuserscorerecord;
use app\Client\model\connect;
use app\Client\model\personalroomscoreinfo;
use app\Manage\model\club;
use \think\Controller;
use Think\Db;

class NewSelect extends Controller
{
    /**
     * 公共方法
     */
    protected function initialize()
    {
        $UserID = input('post.UserID/d');
        $ClubID = input('post.ClubID/d');
//        echo $UserID;


//        echo $ClubID;
//        writeLog(input(),'input.log','initialize'.$UserID.'ClubID='.$ClubID);
        if($UserID&&$ClubID){
//            $user=new user();
            $info=clubuser::getUserClubInfo($UserID,$ClubID,'UserRight');

//            var_dump(empty($info->userid));
//            exit;
            if(empty($info)){
                exitJson(404,'用户不存在');
            }
            $level=$info['UserRight'];
            if($level==1){
                $_SESSION['level']='partner';
            }elseif($level==2){
                $_SESSION['level']='manager';
            }elseif($level==3){
                $_SESSION['level']='boss';
            }else{
                $_SESSION['level']='normal';
            }
        }else{
            exitJson(404,'参数缺失');
        }
    }
    public static function identity()
    {
        $UserID = input('post.UserID/d');
        $ClubID = input('post.ClubID/d');
//        echo $UserID;


//        echo $ClubID;
//        writeLog(input(),'input.log','initialize'.$UserID.'ClubID='.$ClubID);
        if($UserID&&$ClubID){
//            $user=new user();
            $info=clubuser::getUserClubInfo($UserID,$ClubID,'UserRight');
//            p($info);
//            exit;
//            var_dump(empty($info->userid));
//            exit;
            if(empty($info)){
                exitJson(404,'用户不存在');
            }
            $level=$info['UserRight'];
            if($level==1){
                $_SESSION['level']='partner';
            }elseif($level==2){
                $_SESSION['level']='manager';
            }elseif($level==3){
                $_SESSION['level']='boss';
            }else{
                $_SESSION['level']='normal';
            }
        }else{
            exitJson(404,'参数缺失');
        }
    }
//-------------------------------------------成员------------------------------------------------------
    /**
     * 获取成员列表页面信息
     * 成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function user(){
//        $AgentUserID = input('post.AgentUserID/d');//添加者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
//        writeLog(input('post.'),'sign.log','input');


        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
//        //        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
//        $key=config('key');
//        $data=[
//            'UserID'=>$UserID,
//            'ClubID'=>$ClubID,
//            'key'=>$key,
//        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
//        如果点击的人是馆主或者管理员，就查出所有人
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $userList=clubuser::getUserListForBoss($UserID,$ClubID);
        }elseif($_SESSION['level']=='partner'){
            $userList=clubuser::getUserListForPartner($UserID,$ClubID);
        }
        $selfInfo=clubuser::getUserClubInfoByUserID($UserID,$ClubID,'UserID,GameID,NickName,WinCount,MatchScore+Coffer as MatchScore,UserRight,Reviewed');
//        writeLog($userList,'test.log');
        $userList['data'][]=$selfInfo;
        if($userList){
            exitJson(200,'成功',$userList);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取成员列表页面信息
     * 成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function user_hunan(){
//        $AgentUserID = input('post.AgentUserID/d');//添加者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
//        writeLog(input('post.'),'sign.log','input');


        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
//        //        签名
        $status=getSignForApi(input('post.'));
//        if($status==false){
//            exitJson(403,'签名错误');
//        }
//        $key=config('key');
//        $data=[
//            'UserID'=>$UserID,
//            'ClubID'=>$ClubID,
//            'key'=>$key,
//        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
//        如果点击的人是馆主或者管理员，就查出所有人
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){

            $userList=clubuser::getUserListForBoss_hunan($UserID,$ClubID);
        }elseif($_SESSION['level']=='partner'){

            $userList=clubuser::getUserListForPartner_hunan($UserID,$ClubID);
        }
        $selfInfo=clubuser::getUserClubInfoByUserID_hunan($UserID,$ClubID);
//        writeLog($userList,'test.log');
        $userList['data'][]=$selfInfo;
        if($userList){
            exitJson(200,'成功',$userList);
        }else{
            exitJson(204,'数据为空');
        }
    }

    /**
     * 获取成员列表查询单个人信息
     * 成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function serverUser(){
        $ServerGameID = input('post.ServerGameID/d');//添加者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($ServerGameID)){
//            echo 123;
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
//        如果点击的人是馆主或者管理员，就查出所有人
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $userList=clubuser::getServerUserListForBoss($UserID,$ServerGameID,$ClubID);
        }elseif($_SESSION['level']=='partner'){
            $userList=clubuser::getServerUserListForPartner($UserID,$ServerGameID,$ClubID);
        }
        if($userList){
            exitJson(200,'成功',$userList);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     *
     * 获取成员个人数据
     * 成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $ClickUserID 点击的用户userid
     * $status(0:有审核成员 1：无)
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function userInfo(){
        $ClickUserID = input('post.ClickUserID/d');//添加者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名

        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($ClickUserID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
        //        签名
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

//        查询用户身份

        $ClickUserInfoLevel=clubuser::where(['UserID'=>$ClickUserID,'ClubID'=>$ClubID])
            ->field('UserRight')
            ->findOrEmpty()
            ->toArray();
        if(empty($ClickUserInfoLevel)){
            exitJson(404,'用户不存在');
        }
//        p($ClickUserInfoLevel);
//        exit;

        //如果是普通用户,则只查询出加入时间
        if($ClickUserInfoLevel['UserRight']==0){
            $userInfo=clubuser::getUserInfoByUser($ClickUserID,$ClubID);
        //如果是其他身份,则查询出加入时间，分成比例，昨日，今日利润，昨日，当日新增会员，直属会员人数
        }else{
            $userInfo=clubuser::getUserInfoByOther($ClickUserID,$ClubID,$ClickUserInfoLevel['UserRight']);
        }
        writeLog($userInfo,'userList.log');
        if($userInfo){
            exitJson(200,'成功',$userInfo);
        }else{
            exitJson(204,'数据为空');//test
        } 
    }
    /**
     * 获取个人数据中的游戏变动数据
     * 个人数据
     *
     * $UserID      用户id
     *
     * $ClubID      俱乐部id
     * $SelectUserID      要查询的用户id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailGameChange(){
        $UserID = input('post.UserID/d');
        $ClubID = input('post.ClubID/d');
        $SelectUserID=input('post.SelectUserID/d');
        $Type=input('post.Type/d');//0今天，1昨天，2前天
        $sign = input('post.sign/s');
//        writeLog(input());
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
        if(empty($Type)){
            $Type=0;
        }
        $list=personalroomscoreinfo::where(['ClubID'=>$ClubID,'UserID'=>$SelectUserID])
            ->where('DATEDIFF(DAY,WriteTime,GETDATE())='.$Type)
            ->field('RoomID,WriteTime,Score')
            ->order('WriteTime desc')
            ->limit(30)
            ->select()
            ->toArray();

        if($list){
            exitJson(200,'成功',$list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 个人数据
     * 获取个人数据中的积分变动数据
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $SelectUserID      要查询的用户id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailIntegralChange(){
        $UserID = input('post.UserID/d');
        $ClubID = input('post.ClubID/d');
        $SelectUserID=input('post.SelectUserID/d');
        $sign = input('post.sign/s');
        $Type=input('post.Type/d');//0今天，1昨天，2前天

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
        if(empty($Type)){
            $Type=0;
        }
        $list=clubuserscorerecord::where(['clubid'=>$ClubID,'userid'=>$SelectUserID])
            ->where('DATEDIFF(DAY,setdate,GETDATE())='.$Type)
            ->field('userid,gameid,clubid,score,setdate,operate_type')
            ->order('setdate desc')
            ->limit(30)
            ->select()
            ->toArray();

        if($list){
            exitJson(200,'成功',$list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取个人数据中的表情扣除数据
     * 个人数据
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $SelectUserID      要查询的用户id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailExpressionDelete(){
        $UserID = input('post.UserID/d');
        $SelectUserID=input('post.SelectUserID/d');
        $ClubID = input('post.ClubID/d');
        $sign = input('post.sign/s');
        $Type=input('post.Type/d');//0今天，1昨天，2前天

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }

        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
        if(empty($Type)){
            $Type=0;
        }
        $list=personalroomscoreinfo::where([
            ['ClubID','=',$ClubID],
            ['UserID','=',$SelectUserID],
            ['Revenue','>',0]
            ])
            ->where('DATEDIFF(DAY,WriteTime,GETDATE())='.$Type)
            ->field('RoomID,Revenue,WriteTime')
            ->order('WriteTime desc')
            ->limit(30)
            ->select()
            ->toArray();

        if($list){
            exitJson(200,'成功',$list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取个人数据中的开房统计数据
     * 个人数据
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $SelectUserID      要查询的用户id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailRoomStatistics(){
        $UserID = input('post.UserID/d');
        $SelectUserID=input('post.SelectUserID/d');
        $ClubID = input('post.ClubID/d');
        $sign = input('post.sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        $list=$this->getDetailRoomStatisticsData($ClubID,$SelectUserID);
        if($list){
            exitJson(200,'成功',$list);
        }else{
            exitJson(204,'数据为空');
        }

    }

//-------------------------------------------成员------------------------------------------------------




//-------------------------------------------合伙人------------------------------------------------------
    /**
     * 合伙人
     * 合伙人主页面
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function agent(){
        $AgentUserID = input('post.AgentUserID/d');//点击者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($AgentUserID)){
//            echo 123;
            exitJson(400,'参数错误');
        }
//        p($_SESSION);
//        exit;
//        校验权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }
        if(empty($_SESSION['level'])){
            exitJson(400,'身份不明');
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
////        如果该请求是楼主或者管理员发起的  则分请求自己或请求别人两种情况
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            请求自己、
            if($UserID==$AgentUserID){
                $agentList=clubuser::getAgentList($UserID,$ClubID);
            }
//            请求下级代理
            if($UserID!=$AgentUserID){
                $agentList=clubuser::getAgentList($AgentUserID,$ClubID);

            }
//            如果该请求是合伙人发起的，则只有一种情况
        }elseif ($_SESSION['level']=='partner'){
            //            请求自己和请求下级执行的接口一样
            $agentList=clubuser::getAgentList($UserID,$ClubID);
        }

        if($agentList){
            exitJson(200,'获取成功',$agentList);
        }else{
            exitJson(201,'无数据');
        }
    }

    /**
     * 合伙人
     * 合伙人主页面
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function agent_hunan(){
        $AgentUserID = input('post.AgentUserID/d');//点击者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
//        p(input('post.'));
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($AgentUserID)){
//            echo 123;
            exitJson(400,'参数错误');
        }
//        p($_SESSION);
//        exit;
//        校验权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }
        if(empty($_SESSION['level'])){
            exitJson(400,'身份不明');
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
////        如果该请求是楼主或者管理员发起的  则分请求自己或请求别人两种情况
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            请求自己、
            if($UserID==$AgentUserID){
                $agentList=clubuser::getAgentList_hunan($UserID,$ClubID);
            }
//            请求下级代理
            if($UserID!=$AgentUserID){
                $agentList=clubuser::getAgentList_hunan($AgentUserID,$ClubID);

            }
//            如果该请求是合伙人发起的，则只有一种情况
        }elseif ($_SESSION['level']=='partner'){
            //            请求自己和请求下级执行的接口一样
            $agentList=clubuser::getAgentList_hunan($UserID,$ClubID);
        }

        if($agentList){
            exitJson(200,'获取成功',$agentList);
        }else{
            exitJson(201,'无数据');
        }
    }
    /**
     * 获取合伙人查询单个人信息
     * 成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function serverAgent(){
        $ServerGameID = input('post.ServerGameID/d');//添加者的GameID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($ServerGameID)){
//            echo 123;
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
        ////        如果该请求是楼主或者管理员发起的  则从整个茶楼查询
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $agentList=clubuser::getServerAgentListForServer($UserID,$ServerGameID,$ClubID,'boss');
//            如果该请求是合伙人发起的，则只查询自己下级
        }elseif ($_SESSION['level']=='partner'){
            $agentList=clubuser::getServerAgentListForServer($UserID,$ServerGameID,$ClubID);
        }
        if($agentList){
            exitJson(200,'成功',$agentList);
        }else{
            exitJson(204,'数据为空');
        }
    }

    /**
     * 合伙人页面获取下级成员列表页面信息
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function getUserInAgent(){
        $AgentUserID = input('post.AgentUserID/d');//点击的UserID
        $UserID = input('post.UserID/d');//执行者的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign = input('post.sign/s');//签名
//        只允许馆主和管理员查看
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
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
        $userList=clubuser::getUserListForPartner($AgentUserID,$ClubID);
        if($userList){
            exitJson(200,'成功',$userList);
        }else{
            exitJson(204,'数据为空');
        }
    }



//-------------------------------------------合伙人------------------------------------------------------



//-------------------------------------------积分记录------------------------------------------------------

    /***************************
     * 获取自己积分记录（所有人）
     * 积分记录
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function integralRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        //获取积分记录的数据
        $data_list=clubuserscorerecord::alias("cusr")
            ->join('clubuser cu','cu.UserID = cusr.operate_userid and cu.ClubID = cusr.clubid')
            ->where([
                ['cusr.userid','=',$UserID],
                ['cusr.clubid','=',$ClubID]
            ])
            ->field('cusr.userid,cusr.gameid,cusr.score,cusr.setdate,cu.nickname')
            ->limit(60)
            ->order('setdate desc')
            ->select()
            ->toArray();

//        var_dump($data_list);
//        exit;
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /***************************
     * 获取自己给别人上分的记录
     * 积分记录
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function upIntegralRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        //获取积分记录的数据
        $data_list=clubuserscorerecord::alias("cusr")
            ->join('clubuser cu','cu.UserID = cusr.userid and cu.ClubID = cusr.clubid')
            ->where([
                ['cusr.operate_userid','=',$UserID],
                ['cusr.clubid','=',$ClubID]
            ])
            ->where([
                ['score','>',0]
            ])
            ->field('cusr.userid,cusr.gameid,cusr.score,cusr.setdate,cu.nickname')
            ->limit(60)
            ->order('setdate desc')
            ->select()
            ->toArray();

//        var_dump($data_list);
//        exit;
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }

    /***************************
     * 获取自己给别人下分的记录
     * 积分记录
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function downIntegralRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        //获取积分记录的数据
        $data_list=clubuserscorerecord::alias("cusr")
            ->join('clubuser cu','cu.UserID = cusr.userid and cu.ClubID = cusr.clubid')
            ->where([
                ['cusr.operate_userid','=',$UserID],
                ['cusr.clubid','=',$ClubID]
            ])
            ->where([
                ['score','<',0]
            ])
            ->field('cusr.userid,cusr.gameid,cusr.score,cusr.setdate,cu.nickname')
            ->limit(60)
            ->order('setdate desc')
            ->select()
            ->toArray();

//        var_dump($data_list);
//        exit;
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }


//-------------------------------------------积分记录------------------------------------------------------




//-------------------------------------------申请------------------------------------------------------
    /**
     * 需要审核成员列表
     * 审核成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $examinedUserID  被查看的合伙人id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function examineRecord()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');


        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
        if($_SESSION['level']=='partner'){
            ///////对接客户端所需要数据
            $data_list=clubuseroutinrecord::where([
                ['ClubID','=',$ClubID],
                ['DistributorID','=',$UserID],
                ['Status','=',1],
            ])
                ->field('UserID,GameID,NickName,SetDate,Type')
                ->select()
                ->toArray();
        }elseif($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            ///////对接客户端所需要数据
            $data_list=clubuseroutinrecord::where([
                ['ClubID','=',$ClubID],
                ['Status','=',1],
            ])
                ->field('UserID,GameID,NickName,SetDate,Type')
                ->select()
                ->toArray();
        }


        if($data_list){
            exitJson(200, '审核记录列表',$data_list);
        }else{
            exitJson(500, '无审核成员');
        }
    }
    /**
     * 需要审核加入成员列表
     * 审核成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $examinedUserID  被查看的合伙人id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function examineIntoList()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');


        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }


        ///////对接客户端所需要数据
        $data_list=clubuseroutinrecord::where([
            ['ClubID','=',$ClubID],
            ['DistributorID','=',$UserID],
            ['Status','=',0],
            ['Type','=',1]
        ])
            ->field('UserID,GameID,NickName,SetDate,Type')
            ->select()
            ->toArray();
        if($data_list){
            exitJson(200, '审核列表',$data_list);
        }else{
            exitJson(500, '无审核成员');
        }
    }

    /**
     * 需要审核退出成员列表
     * 审核成员
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $examinedUserID  被查看的合伙人id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function examineOutList()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }


        ///////对接客户端所需要数据
        $data_list=clubuseroutinrecord::where([
            ['ClubID','=',$ClubID],
            ['DistributorID','=',$UserID],
            ['Status','=',0],
            ['Type','=',2]
        ])
            ->field('UserID,GameID,NickName,SetDate,Type')
            ->select()
            ->toArray();
        if($data_list){
            exitJson(200, '审核列表',$data_list);
        }else{
            exitJson(500, '无审核成员');
        }
    }

    /**
     * 获取进出状态
     * 管理员功能
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getInOutStatus(){
        $UserID = input('post.UserID/d');//自己的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign=input('post.sign/s');//签名
//        p($NickName);
//        p(mb_strlen('测试','utf8'));
//        exit;
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'){
            exitJson(401,'无权限');
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

//        检验茶楼是否存在

        $ClubInfoInfo=clubinfo::where([
            ['ClubID','=',$ClubID],
            ['CreateUserID','=',$UserID]
        ])
            ->field('NeedReview,NeedExitReview')
            ->findOrEmpty()
            ->toArray();

        if(empty($ClubInfoInfo)){
            exitJson(404,'茶馆不存在');
        }
//        Db::startTrans();
        if($ClubInfoInfo){
            exitJson(200,'获取成功',$ClubInfoInfo);
        }else{
            exitJson(500,'获取失败');
        }

    }
    /**
     * 获取积分管理权限状态
     * 管理员功能
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getIntegralManageStatus(){
        $UserID = input('post.UserID/d');//自己的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign=input('post.sign/s');//签名
//        p($NickName);
//        p(mb_strlen('测试','utf8'));
//        exit;
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'){
            exitJson(401,'无权限');
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

//        检验茶楼是否存在

        $ClubInfoInfo=clubinfo::where([
            ['ClubID','=',$ClubID],
            ['CreateUserID','=',$UserID]
        ])
            ->field('ScoreRight')
            ->findOrEmpty()
            ->toArray();

        if(empty($ClubInfoInfo)){
            exitJson(404,'茶馆不存在');
        }
//        Db::startTrans();
        if($ClubInfoInfo){
            exitJson(200,'获取成功',$ClubInfoInfo);
        }else{
            exitJson(500,'获取失败');
        }

    }

//-------------------------------------------申请------------------------------------------------------


//-------------------------------------------记录------------------------------------------------------

    /**
     * 获取保险箱记录
     * 记录
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getCofferRecord(){
        $UserID = input('post.UserID/d');//自己的user_id
        $ClubID = input('post.ClubID/d');//俱乐部id
        $sign=input('post.sign/s');//签名
//        writeLog(input());
//        p($NickName);
//        p(mb_strlen('测试','utf8'));
//        exit;
        //根据茶馆设置查询权限
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

//        检验用户是否存在
        $ClubUserInfo=clubuser::where([
            ['ClubID','=',$ClubID],
            ['UserID','=',$UserID]
        ])
            ->field('Coffer')
            ->findOrEmpty()
            ->toArray();

        if(empty($ClubUserInfo)){
            exitJson(404,'用户不存在');
        }

        $CofferRecord=connect::conn_platform('ryrecorddb')
            ->table('recordcoffer')
            ->where([
                ['userID','=',$UserID],
                ['clubID','=',$ClubID]
            ])
            ->field('conffer as coffer,addScore,logTime')
            ->limit(60)
            ->order('logTime desc')
            ->select();
//        writeLog($CofferRecord);
        if($CofferRecord){
            exitJson(200,'获取成功',$CofferRecord);
        }else{
            exitJson(500,'数据为空');
        }
    }

    /**
     * 获取个人名片
     * 记录
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public static function getBusinessCard($UserID,$ClubID){
//        $UserID = input('post.UserID/d');//自己的user_id
//        $ClubID = input('post.ClubID/d');//俱乐部id
//        $sign=input('post.sign/s');//签名
//        p($NickName);
//        p(mb_strlen('测试','utf8'));
//        exit;
        //根据茶馆设置查询权限
//        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            exitJson(400,'参数错误');
//        }
//
//        //        签名
//        $status=getSignForApi(input('post.'));
//        if($status==false){
//            exitJson(403,'签名错误');
//        }

//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        self::identity();
//        如果是楼主，则只显示自己的名片设置
        if($_SESSION['level']=='boss'){
            $self=clubuser::where([
                ['ClubID','=',$ClubID],
                ['UserID','=',$UserID]
            ])
                ->field('weChat,QQ,OpenID')
                ->findOrEmpty()
                ->toArray();
            $data=[
                'self'=>$self
            ];
//        如果是合伙人，则名片设置和上级名片都显示
        }elseif($_SESSION['level']=='partner'){
            $self=clubuser::where([
                ['ClubID','=',$ClubID],
                ['UserID','=',$UserID]
            ])
                ->field('weChat,QQ,DistributorId,OpenID')
                ->findOrEmpty()
                ->toArray();
            $upCard=clubuser::where([
                ['ClubID','=',$ClubID],
                ['UserID','=',$self['DistributorId']]
            ])
                ->field('UserID,weChat,QQ,OpenID')
                ->findOrEmpty()
                ->toArray();
            $data=[
                'self'=>$self,
                'upCard'=>$upCard
            ];

//        如果是普通用户和管理员，则只显示上级
        }elseif($_SESSION['level']=='normal'||$_SESSION['level']=='manager'){
            $self=clubuser::where([
                ['ClubID','=',$ClubID],
                ['UserID','=',$UserID]
            ])
                ->field('DistributorId')
                ->findOrEmpty()
                ->toArray();
            $upCard=clubuser::where([
                ['ClubID','=',$ClubID],
                ['UserID','=',$self['DistributorId']]
            ])
                ->field('UserID,weChat,QQ,OpenID')
                ->findOrEmpty()
                ->toArray();
            $data=[
                'upCard'=>$upCard
            ];
        }else{
            return(['code'=>400, 'msg'=>'身份有误']);
        }

        if($data){

//            exitJson(200,'获取成功',$data);
            return(['code'=>200, 'msg'=>'获取成功','data'=>$data]);
        }else{
            return(['code'=>500, 'msg'=>'获取失败']);
//            exitJson(500,'获取失败');
        }

    }

    /**
     * 获取记录状态
     * 记录状态
     *
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public static function getRecordStatus($UserID,$ClubID){
//        $UserID = input('UserID/d');
//        $ClubID = input('ClubID/d');
//        $sign = input('sign/s');


//        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            exitJson(400,'参数错误');
//        }
//        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
//            exitJson(401,'无权限');
//        }

//        $status=getSignForApi(input('post.'));
//        if($status==false){
//            exitJson(403,'签名错误');
//        }

        self::identity();
        ///////对接客户端所需要数据
        $joinStatus=clubuseroutinrecord::where([
            ['ClubID','=',$ClubID],
            ['DistributorID','=',$UserID],
            ['Status','=',0],
            ['Type','=',1]
        ])
            ->count();
        $exitStatus=clubuseroutinrecord::where([
            ['ClubID','=',$ClubID],
            ['DistributorID','=',$UserID],
            ['Status','=',0],
            ['Type','=',2]
        ])
            ->count();
        $Status=['joinStatus'=>$joinStatus,'exitStatus'=>$exitStatus];
        return(['code'=>200, 'msg'=>'审核列表','data'=>$Status]);
    }

//-------------------------------------------记录------------------------------------------------------

//——————————————————————————————————————————————————————————————————————————————————————————————————————



    /**
     * 获取合伙人页面信息
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $AgentUserID 被操作的合伙人id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
//    public function agent(){
//        $AgentUserID = input('AgentUserID/d');//添加者的GameID
//        $UserID = input('UserID/d');//执行者的user_id
//        $ClubID = input('ClubID/d');//俱乐部id
//        $sign = input('sign/s');//签名
////        验证身份
//        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
//            exitJson(401,'无权限');
//        }
////        校验参数
//        if(empty($AgentUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
////            echo 123;
//            exitJson(400,'参数错误');
//        }
//        //        签名
//        $key=config('key');
//        $data=[
//            'UserID'=>$UserID,
//            'ClubID'=>$ClubID,
//            'key'=>$key,
//        ];
////        exitJson(403,'签名错误');
////        if($sign!=Sign($data)){
////            exitJson(403,'签名错误');
////        }
////        如果该请求是楼主或者管理员发起的  则分请求自己或请求别人两种情况
//        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
////            请求自己、
//            if($UserID==$AgentUserID){
//                $agentList=clubuser::getAgentList($ClubID);
//            }
////            请求下级代理
//            if($UserID!=$AgentUserID){
//                $agentList=clubuser::getNextAgentList($AgentUserID,$ClubID);
//
//            }
////            如果该请求是合伙人发起的，则只有一种情况
//        }elseif ($_SESSION['level']=='partner'){
//            //            请求自己和请求下级执行的接口一样
//            $agentList=clubuser::getNextAgentList($AgentUserID,$ClubID);
//        }
//
//        $count=clubuser::where(['ClubID'=>$ClubID,'DistributorId'=>$UserID,'Reviewed'=>0])->count();
//
//        if($count>0){
//            $status=0;
//        }else{
//            $status=1;
//        }
////        p($agentList);
////        exit;
//        if($agentList){
//            exitJson(200,'成功',$agentList,$status);
//        }else{
//            exitJson(204,'数据为空','',$status);
//        }
//    }

    /**
     * 获取积分管理中的比赛积分数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function playIntegral(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');
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
//        writeLog('123','test.log','test');
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $where = ['ClubID'=>$ClubID,'Reviewed'=>1];
        }elseif($_SESSION['level']=='partner'){
            $where=['ClubID'=>$ClubID,'DistributorId'=>$UserID,'Reviewed'=>1];
        }else{
            exitJson(401,'无权限');
        }
        $userList=clubuser::where($where)
                ->field('userid,gameid,nickname,MatchScore+Coffer as matchscore')
                ->select()
                ->toArray();
        if($userList){
            exitJson(200,'成功',$userList);
        }else{
            exitJson(204,'数据为空');
        }
    }

    /***************************
     * 获取积分管理中的积分记录数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
//    public function integralRecord(){
//        $UserID = input('UserID/d');
//        $ClubID = input('ClubID/d');
//        $sign = input('sign/s');
//
//        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            exitJson(400,'参数错误');
//        }
//        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            $where = ['cusr.ClubID'=>$ClubID,'cu.Reviewed'=>1];
//        }elseif($_SESSION['level']=='partner'){
//            $where=['cusr.ClubID'=>$ClubID,'cusr.operate_userid'=>$UserID,'cu.Reviewed'=>1];
//            // p($where);
//            // exit;
//        }else{
//            exitJson(401,'无权限');
//        }
//
//        $key=config('key');
////        exitJson(403,'签名错误');
////        if($sign!=Sign($data)){
////            exitJson(403,'签名错误');
////        }
//
//        //获取积分记录的数据
//        $data_list=clubuserscorerecord::alias("cusr")
//            ->join('clubuser cu','cu.UserID = cusr.userid and cu.ClubID = cusr.clubid')
//            ->join('clubuser cuu','cuu.UserID = cusr.operate_userid and cuu.ClubID = cusr.clubid')
//            ->where($where)
//            ->where('datediff(d,cusr.setdate,getdate())<=1')
//            ->field('cusr.userid,cusr.gameid,cusr.score,cusr.setdate,cu.nickname,cusr.operate_type,cuu.gameid as operate_gameid')
//            ->order('setdate desc')
//            ->select()
//            ->toArray();
//
////        var_dump($data_list);
////        exit;
//        if($data_list){
//            exitJson(200,'成功',$data_list);
//        }else{
//            exitJson(204,'数据为空');
//        }
//    }

    /**
     * 获取积分管理中的土豪榜数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function tyrant(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        //同一俱乐部按照输分倒序排列
        $data_list=clubuser::where(['ClubID'=>$ClubID,'Reviewed'=>1])
            ->field('nickname,userid,gameid,lostcount')
            ->order('LostCount desc')
            ->select()
            ->toArray();
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取积分管理中的大赢家数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function winner(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        //同一俱乐部按照赢分倒序排列
        $data_list=clubuser::where(['ClubID'=>$ClubID,'Reviewed'=>1])
            ->field('nickname,userid,gameid,wincount')
            ->order('WinCount desc')
            ->select()
            ->toArray();

        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取积分管理中的比赛统计数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function playStatistics()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign   = input('sign/s');
        if (empty($UserID) || empty($ClubID) || empty($sign)) {
            exitJson(400, '参数错误');
        }
        $key = config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        if ($_SESSION['level'] == 'partner') {
            $user_where   = 'ClubID=' . $ClubID . ' and (DistributorId=' . $UserID . ' or UserID = ' . $UserID . ')';
            $record_where = 'and ClubID=' . $ClubID . ' and operate_userid=' . $UserID;
        } elseif ($_SESSION['level'] == 'boss' || $_SESSION['level'] == 'manager') {
            $user_where   = 'ClubID=' . $ClubID;
            $record_where = 'and ClubID=' . $ClubID;
        } else {
            exitJson('403', '无权限');
        }
        $expression_where = 'ClubID=' . $ClubID . ' and UserID=' . $UserID;
        if ($_SESSION['level'] == 'manager') {
            $managerBossInfo = clubuser::where(['ClubID' => $ClubID, 'UserRight' => 3])
                ->field('UserID')
                ->findOrEmpty()
                ->toArray();
            $UserID = $managerBossInfo['UserID'];
        }
        $data_list = $this->getPlayStatisticsData($ClubID, $UserID, $user_where, $expression_where, $record_where);

        if ($data_list) {
            exitJson(200, '成功', $data_list);
        } else {
            exitJson(204, '数据为空');
        }
    }

    /**
     * 获取积分管理中的比赛统计数据
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function teahouseIntegral(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        $data_list=$this->getTeahouseIntegralData($ClubID);
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }

    }




    /**
     * 获取详细数据中的退出记录数据
     * 详细数据
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailExitRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }

        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $where=array('cuer.ClubID'=>$ClubID);
        }elseif($_SESSION['level']!='boss'){
            $where=array('cuer.ClubID'=>$ClubID,'cu.DistributorId'=>$UserID);
        }

        $data_list=clubuser::alias('cu')
            ->join('clubuserexitrecord cuer','cu.UserID = cuer.UserID and cu.ClubID = cuer.ClubID')
            ->where($where)
            ->field('cu.userid,cu.clubid,cu.gameid,cu.matchscore,cuer.typeid,cuer.nickname,cuer.operateuserid,cuer.createdatetime')
            ->select()
            ->toArray();

        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }
    }
    /**
     * 获取详细数据中的表情记录数据
     * 详细数据
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailExpressionRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }

        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        $data_list=personalroomscoreinfo::getDetailExpressionRecordData($ClubID,$UserID,$_SESSION['level']);
//        p($data_list);
//        exit;
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }

    }
    /**
     * 积分榜排行
     * 排行榜
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function rankingList()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }

        $where = array(
            'ClubID' =>$ClubID
        );
        $num_list=clubuser::where($where)
            ->field('userid,gameid,nickname,maxwinscore')
            ->order('MaxWinScore Desc')
            ->select()
            ->toArray();
        if($num_list){
            exitJson(200, '排序成功',$num_list);
        }else{
            exitJson(500, '排序失败');
        }
    }

    /**
     * 负分榜排行
     * 排行榜
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function LostList()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }

        $status=getSignForApi(input('post.'));
        if($status==false){
            exitJson(403,'签名错误');
        }
        $where = array(
            'ClubID' =>$ClubID
        );
        $num_list=clubuser::where($where)
            ->field('userid,gameid,nickname,maxlostscore')
            ->order('MaxLostScore Desc')
            ->select()
            ->toArray();
        if($num_list){
            exitJson(200, '排序成功',$num_list);
        }else{
            exitJson(500, '排序失败');
        }
    }
//    /**
//     * 需要审核成员列表
//     * 审核成员
//     *
//     * $UserID      用户id
//     * $ClubID      俱乐部id
//     * $examinedUserID  被查看的合伙人id
//     * @return $status int  状态码
//     * @return $msg string  错误信息
//     * @return $data array  返回数据
//     */
//    public function examinList()
//    {
//        $UserID = input('UserID/d');
//        $ClubID = input('ClubID/d');
//        $examinedUserID=input('examinedUserID/d');
//        $sign = input('sign/s');
//
//
//        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            exitJson(400,'参数错误');
//        }
//        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
//            exitJson(401,'无权限');
//        }
//
//        $key=config('key');
////        exitJson(403,'签名错误');
////        if($sign!=Sign($data)){
////            exitJson(403,'签名错误');
////        }
//
//        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            $where=[
//                'DistributorId'=>$examinedUserID,
//                'ClubID' => $ClubID,
//                'Reviewed'=>0
//            ];
//
//        }elseif($_SESSION['level']=='partner'){
//            $where = [
//                'DistributorId'=>$UserID,
//                'ClubID' =>$ClubID,
//                'Reviewed'=>0
//            ];
//
//        }
//        ///////对接客户端所需要数据
//        $data_list=clubuser::where($where)->field('userid,gameid,nickname')->select()->toArray();
//        if($data_list){
//            exitJson(200, '审核列表',$data_list);
//        }else{
//            exitJson(500, '无审核成员');
//        }
//    }



    //-----------------------------------------------------------------------
    /**
     * 比赛统计获取比赛统计数据
     * 方法
     *
     */
    private function getPlayStatisticsData($ClubID,$UserID,$user_where,$expression_where,$record_where){
        // if($level=='partner'){
        //     $list['today']=M('personalroomscoreinfo prsi')->where(array('prsi.ClubID'=>$data['ClubID'],'cu.DistributorId'=>$data['UserID']))->join('clubuser cu on cu.ClubID = prsi.ClubID and cu.UserID = prsi.UserID')->where('datediff(d,WriteTime,getdate())=0')->sum('prsi.revenue');
        //     $list['yesterday']=M('personalroomscoreinfo prsi')->where(array('prsi.ClubID'=>$data['ClubID'],'cu.DistributorId'=>$data['UserID']))->join('clubuser cu on cu.ClubID = prsi.ClubID and cu.UserID = prsi.UserID')->where('datediff(d,WriteTime,getdate())=1')->sum('prsi.revenue');

        $today=clubuser::query('select today=sum(teachange) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo where DATEDIFF(dd,RecordDate,getdate())=0 and ClubID=? and UserID= ? ',[$ClubID,$UserID]);
        $yesterday=clubuser::query('select yesterday=sum(teachange) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo where DATEDIFF(dd,RecordDate,getdate())=1 and ClubID=? and UserID= ? ',[$ClubID,$UserID]);
//            p($today);
//            exit;

        $list['today']=$today[0]['today'];
        $list['yesterday']=$yesterday[0]['yesterday'];
        // }elseif($level=='boss'||$level=='manager'){
        //     $list['today']=M('personalroomscoreinfo prsi')->where(array('prsi.ClubID'=>$data['ClubID']))->join('clubuser cu on cu.ClubID = prsi.ClubID and cu.UserID = prsi.UserID')->where('datediff(d,WriteTime,getdate())=0 and cu.DistributorId > 0')->sum('prsi.Revenue');
        //     $list['yesterday']=M('personalroomscoreinfo prsi')->where(array('prsi.ClubID'=>$data['ClubID']))->join('clubuser cu on cu.ClubID = prsi.ClubID and cu.UserID = prsi.UserID')->where('datediff(d,WriteTime,getdate())=1 and cu.DistributorId > 0')->sum('prsi.Revenue');
        // }
        $MatchScore=clubuser::where($user_where)->sum('matchscore');
        $Coffer=clubuser::where($user_where)->sum('coffer');
        $list['match_score']=$MatchScore+$Coffer;
        $list['up_score']=clubuserscorerecord::where('datediff(d,setdate,getdate())=0 and score > 0 '.$record_where)->sum('score');
        // writeLog($record_where,'getPlayStatisticsData.log','$list[up_score]');
        // writeLog(M()->_sql(),'getPlayStatisticsData.log','$list[up_score]');
        $list['down_score']=clubuserscorerecord::where('datediff(d,setdate,getdate())=0 and score < 0 '.$record_where)->sum('score');
        $list['expression']=clubuser::where($expression_where)->sum('TotalRevenue');
        foreach ($list as $key => $val){
            $list[$key]= $val==''? 0 : $val ;
        }
        return $list;
    }
    /**
     * 茶楼统计获取数据
     * 方法
     *
     */
    private function getTeahouseIntegralData($ClubID){
        // $count=M('clubinfo ci')->where(array('ClubID'=>$ClubID))->join('clubTableCount ctc on ctc.IsMatch = ci.IsMatch')->field('ctc.TableCount')->find();

        $list=clubuser::query("select 
                yesterday_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and TypeID = 3 and Remarks = '私人房创建消耗房卡')-(select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and TypeID = 5),
                yesterday_club_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and Remarks = '茶馆创建房间消耗房卡'),
                today_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and TypeID = 3 and Remarks = '私人房创建消耗房卡')-(select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and TypeID = 5),
                today_club_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and Remarks = '茶馆创建房间消耗房卡'),
                yesterday_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and TypeID = 3 and ClubID=".$ClubID."),
                yesterday_dismiss_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and TypeID = 5 and ClubID=".$ClubID."),
                today_dismiss_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and TypeID = 5 and ClubID=".$ClubID."),
                today_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and TypeID = 3 and ClubID=".$ClubID.")");
        // p($list);
        // exit;
        foreach ($list as $key =>$val){
            // $data[$key]['yesterday_count']=$list[$key]['yesterday_count']+$list[$key]['yesterday_club_count']*$count['tablecount']-$list[$key]['yesterday_club_count'];
            // $data[$key]['today_count']=$list[$key]['today_count']+$list[$key]['today_club_count']*$count['tablecount']-$list[$key]['today_club_count'];
            $data[$key]['yesterday_count']=$list[$key]['yesterday_count'];
            $data[$key]['today_count']=$list[$key]['today_count'];
            $data[$key]['yesterday_spend']=$list[$key]['yesterday_spend']-$list[$key]['yesterday_dismiss_spend'];
            $data[$key]['today_spend']=$list[$key]['today_spend']-$list[$key]['today_dismiss_spend'];
        }
        return $data;
    }

    /**
     * 个人数据
     * 获取个人数据中的开房统计数据
     */
    private function getDetailRoomStatisticsData($ClubID,$UserID){
        $count=clubinfo::alias(' ci')
            ->where(array('ClubID'=>$ClubID))
            ->join('clubTableCount ctc ',' ctc.IsMatch = ci.IsMatch')
            ->field('ctc.TableCount')
            ->findOrEmpty()
            ->toArray();

        $list=Db::query("select 
                yesterday_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and SourceUserID=".$UserID." and TypeID = 3 and Remarks != '私人房人均消耗房卡')-(select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and TypeID = 5 and SourceUserID=".$UserID."),
                yesterday_club_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and ClubID=".$ClubID." and Remarks = '茶馆创建房间消耗房卡' and SourceUserID=".$UserID."),
                today_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and SourceUserID=".$UserID." and TypeID = 3 and Remarks != '私人房人均消耗房卡')-(select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and TypeID = 5 and SourceUserID=".$UserID."),
                today_club_count=
                (select count(*) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and ClubID=".$ClubID." and Remarks = '茶馆创建房间消耗房卡' and SourceUserID=".$UserID."),
                yesterday_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and TypeID = 3 and ClubID=".$ClubID." and SourceUserID=".$UserID."),
                yesterday_dismiss_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=1 and TypeID = 5 and ClubID=".$ClubID." and SourceUserID=".$UserID."),
                today_dismiss_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and TypeID = 5 and ClubID=".$ClubID." and SourceUserID=".$UserID."),
                today_spend=
                (select isnull(sum(RoomCard),0) from RYRecordDBLink.RYRecordDB.dbo.RecordRoomCard where DATEDIFF(dd,CollectDate,getdate())=0 and TypeID = 3 and ClubID=".$ClubID." and SourceUserID=".$UserID.") ");
        /*p($list);
        exit;*/
        $num=$count['TableCount'];
        foreach ($list as $key =>$val){
            $data[$key]['yesterday_count']=$list[$key]['yesterday_count']+$list[$key]['yesterday_club_count']*$num-$list[$key]['yesterday_club_count'];
            $data[$key]['today_count']=$list[$key]['today_count']+$list[$key]['today_club_count']*$num-$list[$key]['today_club_count'];
            $data[$key]['yesterday_spend']=$list[$key]['yesterday_spend']-$list[$key]['yesterday_dismiss_spend'];
            $data[$key]['today_spend']=$list[$key]['today_spend']-$list[$key]['today_dismiss_spend'];
        }
        return $data;
    }
}