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
use app\Client\model\clubuserscorerecord;
use app\Client\model\connect;
use app\Client\model\personalroomscoreinfo;
use \think\Controller;
use Think\Db;

class Select extends Controller
{
    /**
     * 公共方法
     */
    public function initialize()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
//        echo $UserID;
//        echo $ClubID;
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
        }
    }
    /**
     * 获取成员列表
     * 成员列表
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getUserList(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
//        验证身份
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
//        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
        //        签名
        $key=config('key');
        $data=[
            'UserID'=>$UserID,
            'ClubID'=>$ClubID,
            'key'=>$key,
        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $List=clubuser::getUserList($ClubID);
        if($List){
            exitJson(200,'获取成功',$List);
        }else{
            exitJson(500,'无用户');
        }
    }
    /**
     * 获取保险箱记录
     * 保险箱
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getCofferRecord(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
//        验证身份
//        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
//            exitJson(401,'无权限');
//        }
//        校验参数
        if(empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
        //        签名
        $key=config('key');
        $data=[
            'UserID'=>$UserID,
            'ClubID'=>$ClubID,
            'key'=>$key,
        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $List=clubuser::getCofferRecord($UserID,$ClubID);
        if($List){
            exitJson(200,'获取成功',$List);
        }else{
            exitJson(500,'无用户');
        }
    }
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
    public function agent(){
        $AgentUserID = input('AgentUserID/d');//添加者的GameID
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
//        验证身份
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }
//        校验参数
        if(empty($AgentUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
        //        签名
        $key=config('key');
        $data=[
            'UserID'=>$UserID,
            'ClubID'=>$ClubID,
            'key'=>$key,
        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
//        如果该请求是楼主或者管理员发起的  则分请求自己或请求别人两种情况
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            请求自己、
            if($UserID==$AgentUserID){
                $agentList=clubuser::getAgentList($ClubID);
            }
//            请求下级代理
            if($UserID!=$AgentUserID){
                $agentList=clubuser::getNextAgentList($AgentUserID,$ClubID);

            }
//            如果该请求是合伙人发起的，则只有一种情况
        }elseif ($_SESSION['level']=='partner'){
            //            请求自己和请求下级执行的接口一样
                $agentList=clubuser::getNextAgentList($AgentUserID,$ClubID);
        }

        $count=clubuser::where(['ClubID'=>$ClubID,'DistributorId'=>$UserID,'Reviewed'=>0])->count();

        if($count>0){
            $status=0;
        }else{
            $status=1;
        }
//        p($agentList);
//        exit;
        if($agentList){
            exitJson(200,'成功',$agentList,$status);
        }else{
            exitJson(204,'数据为空','',$status);
        }
    }

    /**
     * 获取下级用户页面信息
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $AgentUserID 点击的代理userid
     * $status(0:有审核成员 1：无)
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function user(){
        $AgentUserID = input('AgentUserID/d');//添加者的GameID
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名

        if($_SESSION['level']!='partner'&&$_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
        }
        //        校验参数
        if(empty($AgentUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
//            echo 123;
            exitJson(400,'参数错误');
        }
        //        签名
        $key=config('key');
        $data=[
            'UserID'=>$UserID,
            'ClubID'=>$ClubID,
            'key'=>$key,
        ];
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
        $userList=clubuser::getUserIndexInfo($AgentUserID,$ClubID);
        $count=clubuser::where(['ClubID'=>$ClubID,'DistributorId'=>$UserID,'Reviewed'=>0])->count();

        if($count>0){
            $status=0;
        }else{
            $status=1;
        }
        if($userList){
            exitJson(200,'成功',$userList,$status);
        }else{
            exitJson(204,'数据为空','',$status);
        }
    }

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
        $key=config('key');
        $data=[
            'UserID'=>$UserID,
            'ClubID'=>$ClubID,
            'key'=>$key,
        ];
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
    public function integralRecord(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $where = ['cusr.ClubID'=>$ClubID,'cu.Reviewed'=>1];
        }elseif($_SESSION['level']=='partner'){
            $where=['cusr.ClubID'=>$ClubID,'cusr.operate_userid'=>$UserID,'cu.Reviewed'=>1];
            // p($where);
            // exit;
        }else{
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        //获取积分记录的数据
        $data_list=clubuserscorerecord::alias("cusr")
            ->join('clubuser cu','cu.UserID = cusr.userid and cu.ClubID = cusr.clubid')
            ->join('clubuser cuu','cuu.UserID = cusr.operate_userid and cuu.ClubID = cusr.clubid')
            ->where($where)
            ->where('datediff(d,cusr.setdate,getdate())<=1')
            ->field('cusr.userid,cusr.gameid,cusr.score,cusr.setdate,cu.nickname,cusr.operate_type,cuu.gameid as operate_gameid')
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
        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

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
        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

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
        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $data_list=$this->getTeahouseIntegralData($ClubID);
        if($data_list){
            exitJson(200,'成功',$data_list);
        }else{
            exitJson(204,'数据为空');
        }

    }

    /**
     * 获取个人数据中的游戏变动数据
     * 个人数据
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $SelectUserID      要查询的用户id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function detailGameChange(){
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $SelectUserID=input('SelectUserID/d');
        $sign = input('sign/s');
//        writeLog(input());
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $list=personalroomscoreinfo::where(['ClubID'=>$ClubID,'UserID'=>$SelectUserID])
            ->field('roomid,writetime,score')
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
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $SelectUserID=input('SelectUserID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $list=clubuserscorerecord::where(['clubid'=>$ClubID,'userid'=>$SelectUserID])
            ->field('userid,gameid,clubid,score,setdate,operate_type')
            ->order('setdate desc')
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
        $UserID = input('UserID/d');
        $SelectUserID=input('SelectUserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }

        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $list=personalroomscoreinfo::where(array('ClubID'=>$ClubID,'UserID'=>$SelectUserID))
            ->field('roomid,revenue,writetime')
            ->order('WriteTime desc')
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
        $UserID = input('UserID/d');
        $SelectUserID=input('SelectUserID/d');
        $ClubID = input('ClubID/d');
        $sign = input('sign/s');

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($SelectUserID)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        $list=$this->getDetailRoomStatisticsData($ClubID,$SelectUserID);
        if($list){
            exitJson(200,'成功',$list);
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

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

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

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

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

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

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

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }
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
    public function examinList()
    {
        $UserID = input('UserID/d');
        $ClubID = input('ClubID/d');
        $examinedUserID=input('examinedUserID/d');
        $sign = input('sign/s');


        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }

        $key=config('key');
//        exitJson(403,'签名错误');
//        if($sign!=Sign($data)){
//            exitJson(403,'签名错误');
//        }

        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            $where=[
                'DistributorId'=>$examinedUserID,
                'ClubID' => $ClubID,
                'Reviewed'=>0
            ];

        }elseif($_SESSION['level']=='partner'){
            $where = [
                'DistributorId'=>$UserID,
                'ClubID' =>$ClubID,
                'Reviewed'=>0
            ];

        }
        ///////对接客户端所需要数据
        $data_list=clubuser::where($where)->field('userid,gameid,nickname')->select()->toArray();
        if($data_list){
            exitJson(200, '审核列表',$data_list);
        }else{
            exitJson(500, '无审核成员');
        }
    }



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

        $today=clubuser::query('select today=sum(teachange) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo where DATEDIFF(dd,RecordDate,getdate())=0 and ClubID='.$ClubID.' and UserID= '.$UserID);
        $yesterday=clubuser::query('select yesterday=sum(teachange) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo where DATEDIFF(dd,RecordDate,getdate())=1 and ClubID='.$ClubID.' and UserID= '.$UserID);
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