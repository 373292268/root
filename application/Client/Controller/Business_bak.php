<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/2
 * Time: 16:22
 */
namespace app\Client\Controller;

use app\Client\model\clubdissmissagent;
use app\Client\model\clubinfo;
use app\Client\model\clubuser;
use app\Client\model\clubclearrecord;
use app\Client\model\clubuserexitrecord;
use app\Client\model\clubuserscorerecord;
use app\Client\model\clubrevenuerecord;
use app\Client\model\connect;
use \think\Controller;
use Think\Db;

class Business extends controller
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
     * 合伙人
     * 添加合伙人
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $addUserID      添加者的GameID
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function addAgent(){
//        echo 123;
//        exit;
        $addGameID = input('addGameID/d');//添加者的GameID
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
//        校验参数
        if(empty($addGameID)||empty($UserID)||empty($ClubID)||empty($sign)){
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
//        获取用户信息
//        $user=new user();
        $userInfo=clubuser::getUserClubInfoForGameID($addGameID,$ClubID,'UserRight,DistributorId,UserID,AgentDistributorId');
//        p($userInfo);
//        exit;
//        $userInfo=Clubuser::getUserClubInfo($UserID,$ClubID,'UserRight,DistributorId,UserID');
        if(empty($userInfo)){
            exitJson(403,'成员不存在');
        }

        if($userInfo['UserRight']!=0){//非普通成员
            exitJson(403,'该用户不是普通成员');
        }
        if(!empty($userInfo['AgentDistributorId'])){//字段已经有值
            exitJson(403,'该用户已有上级');
        }

//        如果是馆主或者管理员，则设置出来直接是一级合伙人
        if($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            if($userInfo['DistributorId']>0){
                exitJson(403,'已有上级，不可成为顶级合伙人');
            }
//            如果是普通合伙人，则设置出来是低一级的合伙人
        }elseif($_SESSION['level']=='partner'){
            if($userInfo['DistributorId']!=$data['UserID']){
                exitJson(403,'该用户上级不匹配');
            }
        }

//        生成openID
        $code=CreateOpenID();
//        p($code);
//        exit;

        if($_SESSION['level']=='partner'){
//        查询自己的信息
            $selfInfo = clubuser::getUserClubInfo($UserID,$ClubID,'UserLevel');
//            var_dump($selfInfo['UserLevel']);
//            exit;
//            $where=array(
//                'ClubID'=>$data['ClubID'],
//                'GameID'=>$data['addGameID']
//            );
            $UserLevel = (int)$selfInfo['UserLevel']+1;
//            echo $UserLevel;
//            exit;
            $save=array('UserRight'=>1,'AgentDistributorId'=>$UserID,'UserLevel'=>$UserLevel,'OpenID'=>$code);

        }elseif($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
//            $where=array(array('GameID'=>$data['addGameID'],'ClubID'=>$data['ClubID']));
            $save=array('UserRight'=>1,'AgentDistributorId'=>$userInfo['UserID'],'UserLevel'=>1,'OpenID'=>$code);
        }
//        更新
        $result=clubuser::where(['GameID'=>$addGameID,'ClubID'=>$ClubID])->update($save);
        if($result){
            exitJson(200,'添加成功');
        }else{
            exitJson(500,'添加失败');
        }
    }

    /**
     * 积分管理
     * 调整积分
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $AdjustGameID      被调整用户gameid
     * $score       调整数值
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * updateTime       2020年2月28日14:28:07
     */
    public function adjustScore(){
        $adjustGameID = input('AdjustGameID/d');//被调整用户gameid
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
        $score = input('Score/d');//调整数值
//        限制最大输入值
        if($score>999999){
            exitJson(400,'数额过大');
        }
        if(empty($_SESSION['level'])){
            exitJson(400,'身份不明');
        }
        //        参数过滤
        if(empty($adjustGameID)||empty($UserID)||empty($ClubID)||empty($sign)||empty($score)){
            exitJson(400,'参数错误');
        }

        //根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
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

//        查询被调整积分个人信息
        $userInfo = clubuser::getUserClubInfoForGameID($adjustGameID,$ClubID,'UserID,UserRight,DistributorId,MatchScore,Coffer');
//        p($userInfo);
//        exit;
        if(empty($userInfo)){
            exitJson(403,'不存在的成员');
        }
//        p($userInfo->isEmpty());
//        exit;
//        $userInfo_DistributorID=$userInfo->DistributorId;//获取操作者的上级id
//        $userInfo_UserRight=$userInfo->UserRight;//获取操作者的用户状态
//        $userInfo_UserID=$userInfo->UserID;\
        if($_SESSION['level']=='partner') {
            if ($userInfo['UserID'] == $UserID) {
                exitJson(403, '合伙人不允许调整本人积分');
            }
        }

//        查询俱乐部管理积分方式
        $clubInfo=clubinfo::where(['ClubID'=>$ClubID])->field('ScoreRight')->findOrEmpty()->toArray();
//        p($clubInfo);
//        exit;
        $ScoreRight=$clubInfo['ScoreRight'];//获取俱乐部积分操作模式

//        管理员模式：只有管理员或楼主可以上下分
//        合伙人模式：只有合伙人可以上下分，管理员只能给合伙人上下分
        if($ScoreRight==0){         //管理员模式
            if($_SESSION['level']=='partner'){
                exitJson(401,'无权限');
            }
        }elseif($ScoreRight==1){    //合伙人模式
            if($_SESSION['level']!='partner'){
//                如果操作者不是合伙人，也就是操作者是馆主或管理员
//                检查被操作的对象是不是合伙人。如果不是则返回没权限
                if($userInfo['UserRight']!=1){
                    exitJson(401,'合伙人模式只可给合伙人调分数');
                }
//                如果操作者是合伙人，则要验证被操作者是否是自己下级
            }elseif($_SESSION['level']=='partner'){
                if($userInfo['DistributorId']!=$UserID){
                    exitJson(401,'该用户不是自己下级用户');
                }
            }
        }
//        计算调整的分数
        $change_score=$score;//调整的分数
        if($change_score>0){
            $user_before=$userInfo['MatchScore'];//被调整用户调整之前分数
        }elseif($change_score<0){
            $user_before=$userInfo['Coffer'];
        }
        $user_after=$user_before+$change_score;//被调整用户调整之后分数
        if($user_after>999999999){
            exitJson(505,'积分数值异常');
        }
        if($user_after<0){
            exitJson(403,'保险箱积分不足');
        }
        //        开启事务
      
        Db::startTrans();

        /*
         * 如果执行者是茶馆馆主则不扣积分
         * 如果执行者是合伙人则要扣掉自身积分
         * */
//        如果该执行者不是茶馆老板
        if($_SESSION['level']!='boss'){
//            查询执行者自己的信息
            $selfInfo=clubuser::getUserClubInfo($UserID,$ClubID,'Coffer');
//            执行者保险箱当前现有积分
            $selfInfo_present=$selfInfo['Coffer'];
//            计算执行者剩余积分,如果是下分则负负得正
            $selfScoreRest=$selfInfo_present-$change_score;
            if($selfScoreRest<0){
                exitJson(403,'积分不足');
            }
            //执行者减去所调整积分
//            取改变积分的相反数
            $self_change_score=take_opposite($change_score);

//            进行字段相加，若下分，则操作者加上个正值，若上分，则加上个负值
            //改变的是操作者的保险箱数据
            clubuser::setUserCoffer($UserID,$ClubID,$self_change_score);
        }
        //调整被调整者积分
        //如果大于0则是上分，修改身上携带积分
        //如果小于0则是下分，修改保险箱中积分
        if($change_score>0){
            $result=clubuser::setUserScore($userInfo['UserID'],$ClubID,$change_score);
        }elseif($change_score<0){
            $result=clubuser::setUserCoffer($userInfo['UserID'],$ClubID,$change_score);
        }else{
            Db::rollback();//回滚
            exitJson(500,'参数错误');
        }

//        写调整记录，准备添加数据
//        判断上下分类型
        if($_SESSION['level']=='boss'){
            $operate_type=1;//楼主调整
        }elseif($_SESSION['level']=='manager'){
            $operate_type=2;//管理员调整0
        }elseif($_SESSION['level']=='partner'){
            $operate_type=3;//合伙人调整
        }else{
            Db::rollback();//回滚
            exitJson(500,'参数错误');
        }

        $add=[
            'userid'=>$userInfo['UserID'],
            'gameid'=>$adjustGameID,
            'clubid'=>$ClubID,
            'before'=>$user_before,
            'score'=>$change_score,
            'after'=>$user_after,
            'operate_userid'=>$UserID,
            'operate_type'=>$operate_type,
        ];

        $addResult=Clubuserscorerecord::insert($add);
//        p($result);
//        p($addResult);
//        exit;
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'调整成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'调整失败');
        }
    }
    /**
     * 积分管理
     * 清除个人积分
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $ClearUserID      要清除用户的UserID
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * updateTime  2020年2月28日15:41:36
     */

    public function clearUserScore(){
        $ClearUserID = input('ClearUserID/d');//被调整用户userID
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($ClearUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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


        //获取用户个人信息
        $userInfo=clubuser::getUserClubInfo($ClearUserID,$ClubID,'Coffer');
        if(empty($userInfo)){
            exitJson(403,'成员不存在');
        }

//        开启事务
        Db::startTrans();

//        清零用户积分
        $result=clubuser::where(['UserID'=>$ClearUserID,'ClubID'=>$ClubID])->update(['Coffer'=>0]);

//        写清零记录
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$userInfo['Coffer'],//保险箱分数
            'userid'=>$ClearUserID,//被清除者userid
            'type'=>2,//1为表情2为积分3为土豪4为大赢家
        );

        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }

    /**
     * 清除所有人积分
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * * updateTime  2020年2月28日15:41:36
     */
    public function clearScore(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign = input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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
        
        Db::startTrans();
//        准备记录数据
//        必须先准备数据，否则清除后无法计算被清除积分总和
        $ClubAllUserInfo=clubuser::where(['ClubID'=>$ClubID])->field('UserID')->select()->toArray();
//        将查出来的二维数组用，连接为字符串
        $ClubAllUserID=two_dimension_array_to_string($ClubAllUserInfo,'UserID');
        $ClubAllScore=clubuser::where(['ClubID'=>$ClubID])->sum('Coffer');
//        p($ClubAllScore);
//        exit;

//        保险箱清零
//        暂时不能清零身上积分
        $result=clubuser::where(['ClubID'=>$ClubID])->update(['Coffer'=>0]);
//        准备记录数据
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$ClubAllScore,//被清除积分总和
            'userid'=>$ClubAllUserID,//被清楚积分者集合
            'type'=>2,//1为表情2为积分3为土豪4为大赢家
        );
//        写记录
        $addResult=clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }

    /**
     * 清除个人输牌次数
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $ClearUserID      清除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * * updateTime  2020年2月28日15:41:36
     */
    public function clearUserLost(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $ClearUserID = input('ClearUserID/d');//清除者userid
        $sign=input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($ClearUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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
        //获取用户个人信息
        $userInfo=clubuser::getUserClubInfo($ClearUserID,$ClubID,'LostCount');
        if(empty($userInfo)){
            exitJson(403,'成员不存在');
        }
        
        Db::startTrans();
//        清零输牌次数
        $result=clubuser::where(['ClubID'=>$ClubID,'UserID'=>$ClearUserID])->update(['LostCount'=>0]);
//        准备记录数据
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$userInfo['LostCount'],//被清除次数
            'userid'=>$ClearUserID,//被清除者userid
            'type'=>3,//1为表情2为积分3为土豪4为大赢家
        );
//        写记录
        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }
    /**
     * 清除个人赢牌次数
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $ClearUserID      清除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * * updateTime  2020年2月28日15:41:36
     */
    public function clearUserWin(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $ClearUserID = input('ClearUserID/d');//清除者userid
        $sign=input('sign/s');//签名
        // p($_SESSION);exit;
        //根据茶馆设置查询权限
        if(empty($ClearUserID)||empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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
        //获取用户个人信息

        $userInfo=clubuser::getUserClubInfo($ClearUserID,$ClubID,'WinCount');
        if(empty($userInfo)){
            exitJson(403,'成员不存在');
        }
        
        Db::startTrans();
        //清零赢牌次数
        $result=clubuser::where(['ClubID'=>$ClubID,'UserID'=>$ClearUserID])->update(['WinCount'=>0]);
//        准备记录数据
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$userInfo['WinCount'],//清除的赢牌次数
            'userid'=>$ClearUserID,//被清除者的userid
            'type'=>4,//1为表情2为积分3为土豪4为大赢家
        );
//        写记录
        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }
    /**
     * 清除所有人输牌
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * * updateTime  2020年2月28日15:41:36
     */
    public function clearLost(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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

        
        Db::startTrans();

//        准备记录数据
//        必须先准备数据，否则清除后无法计算被清除积分总和
        $ClubAllUserInfo=clubuser::where(['ClubID'=>$ClubID])->field('UserID')->select()->toArray();
//        将查出来的二维数组用，连接为字符串
        $ClubAllUserID=two_dimension_array_to_string($ClubAllUserInfo,'UserID');
        $ClubAllLostCount=clubuser::where(['ClubID'=>$ClubID])->sum('LostCount');
//        清零所有人输牌记录
        $result=clubuser::where(['ClubID'=>$ClubID])->update(['LostCount'=>0]);

//        准备记录数据
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$ClubAllLostCount,//被清除次数总和
            'userid'=>$ClubAllUserID,//被清除积分者集合
            'type'=>3,//1为表情2为积分3为土豪4为大赢家
        );

        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }
    /**
     * 清除所有人赢牌
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * @update_time  2020年2月28日15:45:39
     */
    public function clearWin(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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

        
        Db::startTrans();
//        准备记录数据
//        必须先准备数据，否则清除后无法计算被清除积分总和
        $ClubAllUserInfo=clubuser::where(['ClubID'=>$ClubID])->field('UserID')->select()->toArray();
//        将查出来的二维数组用，连接为字符串
        $ClubAllUserID=two_dimension_array_to_string($ClubAllUserInfo,'UserID');
        $ClubAllWinCount=clubuser::where(['ClubID'=>$ClubID])->sum('WinCount');
//        清零所有人赢牌记录
        $result=clubuser::where(['ClubID'=>$ClubID])->update(['WinCount'=>0]);
//        准备记录数据
        $add=array(
            'operate'=>$UserID,//操作者
            'score'=>$ClubAllWinCount,//被清除次数总和
            'userid'=>$ClubAllUserID,//被清除积分者集合
            'type'=>3,//1为表情2为积分3为土豪4为大赢家
        );
//        写记录
        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }

    /**
     * 清除所有表情
     * 积分管理
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * update_time  2020年2月28日16:01:28
     */
    public function clearExpression(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名
        //根据茶馆设置查询权限
        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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

        
        Db::startTrans();
//        准备记录数据

        $add=array(
            'operate'=>$UserID,
            'score'=>0,
            'userid'=>'clubAll',
            'type'=>1,
        );
//        清零所有用户产生表情和代理获取抽水
        $result=clubuser::where(['ClubID'=>$ClubID])->update(['Revenue'=>0,'TotalRevenue'=>0]);
//        写记录
        $addResult=Clubclearrecord::insert($add);
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }

    ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    /// ///////////////////////////////////////////////////////////////////////////////////
    ///


    /**
     * 清除合伙人表情
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $ClearUserID      清除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * update_time  2020年2月28日16:01:44
     */
    public function clearUserExpression(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $ClearUserID = input('ClearUserID/d');//被清除者id
        $sign=input('sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($ClearUserID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        //根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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

        
        Db::startTrans();


        //查找被清除的合伙人
        $info=clubuser::getUserClubInfo($ClearUserID,$ClubID,'totalRevenue,UserLevel');
//        p($info);
//        exit;
//        更新俱乐部中该用户表情
        $result=clubuser::where(['ClubID'=>$ClubID,'UserID'=>$ClearUserID])->update(['TotalRevenue'=>0]);

//        将记录表的数据更改////暂不需要
//        $res=Clubrevenuerecord::where(['ClubID'=>$ClubID,'UserID'=>$ClearUserID])->where('DATEDIFF(day,setdate,GETDATE())=1')->update(['revenue'=>0,'immediately_revenue'=>0]);
        //添加一条清除记录
        $add=array(
            'operate'=>$UserID,
            'score'=> $info['totalRevenue'],
            'userid'=>$ClearUserID,
            'type'=>1,//1为表情2为积分3为土豪4为大赢家
        );
        $addResult=Clubclearrecord::insert($add);

        if($info['UserLevel']!=1){
            // 把他的表情返还到上级代理身上
            clubuser::returnLastExpression($UserID,$ClubID,$add['score']);
        }
        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }
    /**
     * 清除所有合伙人表情
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     * update_time  2020年2月28日16:47:30
     */
    public function clearAllExpression(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
        //根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'){
            exitJson(401,'无权限');
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

        
        Db::startTrans();

        // 查找同一俱乐部所有合伙人
        $info = clubuser::where(['ClubID'=>$ClubID,'UserRight'=>1])->field('TotalRevenue,UserID')->select()->toArray();
        foreach($info as $key=>$val){
            $add[]=[
                'operate'=>$data['UserID'],
                'score'=> $val['TotalRevenue'],
                'userid'=>$val['UserID'],
                'type'=>1,
            ];
        }
//        将俱乐部所有合伙人表情清除
        $result=clubuser::where(['ClubID'=>$ClubID,'UserRight'=>1])->update(['TotalRevenue'=>0]);

        //清除所有合伙人的表情在表情记录表
//        $res=Clubrevenuerecord::where(['ClubID'=>$ClubID])->where('DATEDIFF(day,setdate,GETDATE())=1')->update(['revenue'=>0,'immediately_revenue'=>0]);
        //添加所有被删除合伙人记录  注：此处必须用动态方式
        $ClubClearRecord=new clubclearrecord();
        $addResult=$ClubClearRecord->saveAll($add);

        if($result&&$addResult){
            Db::commit();//执行
            exitJson(200,'清除成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'清除失败');
        }
    }
    /**
     * 调整合伙人比例
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $AdjustUserID      被调整者userid
     * $AdjustPercent    调整比例
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function adjustUserPercent(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $AdjustUserID=input('AdjustUserID/d');//被调整者userid
        $AdjustPercent=input('AdjustPercent/d');//调整比例
        $sign=input('sign/s');//签名
        // writeLog($data['AdjustPercent'],'adjustUserPercent.log','adjustUserPercent');
        // if(empty($data['AdjustPercent'])){
        //     writeLog(1,'adjustUserPercent.log','adjustUserPercent');
        // }

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($AdjustUserID)||$AdjustPercent==''){
            exitJson(400,'参数错误');
        }
//根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
        }
//        判断是不是给自己设置
        if($UserID==$AdjustUserID){
            exitJson(401,'无权限');
        }
//        判断比例是否合法
        if($AdjustPercent>100) {
            exitJson(403,'比例不能大于100');
        }
        elseif ($AdjustPercent<0){
            exitJson(403,'比例不能小于0');
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


//        开启事务
        Db::startTrans();
//        获取调整用户的上级id
        $userInfo = clubuser::getUserClubInfo($AdjustUserID,$ClubID,'UserRight,DistributorId');
//        调整的比例必须小于上级大于下级
        if($_SESSION['level']=='partner'){
            if($userInfo['DistributorId']!=$UserID){
                exitJson(403,'非直属上级无法调整');
            }
//            查询上级（自己）数据
            $upUserInfo=clubuser::getUserClubInfo($UserID,$ClubID,'CooperatePercent');
//            调整的比例不可大于上级的比例
            if($AdjustPercent>$upUserInfo['CooperatePercent']){
                exitJson(403,'比例不能大于上级');
            }
//            查询该用户下级代理
//            查询下级代理比例最大的人
            $downUserInfo=clubuser::getDownAgentPersentMax(['DistributorId'=>$AdjustUserID,'ClubID'=>$ClubID,'UserRight'=>1]);
//            p($downUserInfo);
//            exit;
            if($AdjustPercent<$downUserInfo){
                exitJson(403,'比例不能小于下级');
            }
        }elseif($_SESSION['level']=='boss'||$_SESSION['level']=='manager'){
            if($userInfo['UserRight']!=1){
                exitJson(403,'非直属上级无法调整');
            }
            $down_agent=clubuser::getDownAgentPersentMax(['DistributorId'=>$AdjustUserID,'ClubID'=>$ClubID,'UserRight'=>1]);
            if($AdjustPercent<$down_agent){
                exitJson(403,'比例不能小于下级');
            }
        }
        $where=array(
            'UserRight'=>1,
            'ClubID'=>$ClubID,
            'UserID'=>$AdjustUserID
        );

        $update=array(
            'CooperatePercent'=>$AdjustPercent
        );

        $arr=clubuser::where($where)->update($update);
        // p($arr);
        // exit;

        if($arr){
            Db::commit();//执行
            exitJson(200,'调整成功 ');
        }else{
            Db::rollback();//回滚
            exitJson(500,'调整失败');
        }

    }
    /**
     * 审核成员
     * 合伙人
     *
     * $UserID        用户id
     * $ClubID        俱乐部id
     * $examUserID    被查看的普通用户id
     * $examNormalID  被查看的合伙人id
     * $status      点击状态
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function examineNormal()
    {
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $examUserID=input('examUserID/d');//被查看的普通用户id
        $examNormalID=input('examNormalID/d');//被查看的合伙人id
        $status=input('status/s');//点击状态
        $sign=input('sign/s');//签名
        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($examUserID)||empty($examNormalID)||empty($status)){
            exitJson(400,'参数错误');
        }
//根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
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
//        验证该用户被审核合法性
//        查询该用户数据
        $examUserInfo=clubuser::getUserClubInfo($examUserID,$ClubID,'Reviewed,DistributorId');
//        验证被操作者状态
        if($examUserInfo['Reviewed']!=0){
            exitJson(400,'该用户不可被审核');
        }
//        当操作者是合伙人的时候，验证是否是直属下级
        if($_SESSION['level']=='partner'&&$examUserInfo['DistributorId']!=$examNormalID){
            exitJson(400,'无权限');
        }
//        处理请求类型
        if($status=='pass'){
//            修改用户状态
            $user_status=clubuser::where(['UserID'=>$examUserID,'ClubID'=>$ClubID])->update(['Reviewed'=>1]);
//            修改clubinfo表中的俱乐部人数
            clubinfo::IncClubNumberPeople($ClubID);

        }elseif($status=='refuse'){
//            不通过就直接删掉这条数据
            $user_status=clubuser::deleteUserData($examUserID,$ClubID);
        }
        if($user_status){
            Db::commit();//执行
            exitJson(200,'执行成功');
        }else{
            Db::rollback();//回滚
            exitJson(500,'执行失败');
        }
    }

    /**
     * 清除所有最大负分
     * 排行榜
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function clearLostScoreList(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
//根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
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
        $clear_result=clubuser::where(['ClubID'=>$ClubID])->update(['MaxLostScore'=>0]);

        if($clear_result!=0){
            exitJson(200, '清除成功');
        }else{
            exitJson(500, '清除失败');
        }

    }

    /**
     * 清除所有最大赢分
     * 排行榜
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function clearWinScoreList(){
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $sign=input('sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)){
            exitJson(400,'参数错误');
        }
//根据茶馆设置查询权限
        if($_SESSION['level']!='boss'&&$_SESSION['level']!='manager'&&$_SESSION['level']!='partner'){
            exitJson(401,'无权限');
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
        $clear_result=clubuser::where(['ClubID'=>$ClubID])->update(['MaxWinScore'=>0]);

        if($clear_result!=0){
            exitJson(200, '清除成功');
        }else{
            exitJson(500, '清除失败');
        }

    }


    /**
     * 撤职合伙人 已关闭
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $DeletedUserID    被删除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */


    public function deleteUser(){
//        exitJson(300,'功能关闭');
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $DeletedUserID=input('DeletedUserID/d');//被调整者userid
        $sign=input('sign/s');//签名

        if(empty($UserID)||empty($ClubID)||empty($sign)||empty($DeletedUserID)){
            exitJson(400,'参数错误');
        }
        //根据茶馆设置查询权限
        if($_SESSION['level']!='boss'){
            exitJson(401,'无权限');
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

        Db::startTrans();
        //找到被删除合伙人  代理等级,上级id,上级合伙人id
        $DeleteInfo=clubuser::getUserClubInfo($DeletedUserID,$ClubID,'GameID,NickName,UserLevel,DistributorId,AgentDistributorId');
        if(empty($DeleteInfo)){
            exitJson(404,'合伙人不存在');
        }
        $status=0;
//        p($DeleteInfo);
//        exit;
        if($DeleteInfo['UserLevel']!=1){

//        修改下级玩家包括合伙人的DistributorId.合伙人和普通玩家的DistributorId都是上级UserID
            $downUpdate=clubuser::where(['DistributorId'=>$DeletedUserID,'ClubID'=>$ClubID])->update(['DistributorId'=>$DeleteInfo['DistributorId']]);
//        修改下级合伙人的UserLevel
//        clubuser::where(['AgentDistributorId'=>$DeletedUserID,'ClubID'=>$ClubID])->setInc('UserLevel');
//        继而修改下级合伙人的AgentDistributorId以及UserRight,DistributorId在上边已经修改过
            $downAgentUpdate=clubuser::where(['AgentDistributorId'=>$DeletedUserID,'ClubID'=>$ClubID])->update(['AgentDistributorId'=>$DeleteInfo['DistributorId'],'UserRight'=>0]);
            if($downUpdate&&$downAgentUpdate){
                $status=1;
            }
            $result=clubuser::where(['UserID'=>$DeletedUserID,'ClubID'=>$ClubID])->update(['DistributorId'=>$DeleteInfo['DistributorId'],'AgentDistributorId'=>0,'UserRight'=>0,'UserLevel'=>0]);
        }else{
            //        修改一级合伙人下级玩家包括合伙人的DistributorId.升级为一级合伙人或楼主下级玩家后，合伙人和普通玩家的DistributorId都是NULL
            $downUpdate=clubuser::where(['DistributorId'=>$DeletedUserID,'ClubID'=>$ClubID])->update(['DistributorId'=>NULL]);
            //        继而修改下级合伙人的AgentDistributorId以及UserRight,DistributorId在上边已经修改过
            $downAgentUpdate=clubuser::execute('update dbo.clubuser set AgentDistributorId = UserID,UserLevel = 1 where AgentDistributorId = '.$DeletedUserID.' and ClubID = '.$ClubID);
            if($downUpdate&&$downAgentUpdate){
                $status=1;
            }
            $result=clubuser::where(['UserID'=>$DeletedUserID,'ClubID'=>$ClubID])->update(['DistributorId'=>NULL,'AgentDistributorId'=>0,'UserRight'=>0,'UserLevel'=>0]);
        }

        if($result!=1||$status!=1){
            Db::rollback();//回滚
            exitJson(404,'撤职失败');
        }

        $add=array(
            'UserID' => $DeletedUserID,
            'GameID' => $DeleteInfo['GameID'],
            'NickName' => $DeleteInfo['NickName'],
            'ClubID' => $ClubID,
            'AgentLevel' => $DeleteInfo['UserLevel'],
            'DistributorID'=>$DeleteInfo['DistributorId'],
        );
        clubdissmissagent::insert($add);
        if($result==1&&$status==1){
            Db::commit();//执行
            exitJson(404,'撤职成功');
        }
//        如果下级不止有自己
//        if($down>1) {
//            //合伙人有下级，删除合伙人下级
//            clubuser::where(['DistributorId' => $DeletedUserID,'ClubID' => $ClubID])->delete();
//            //减去俱乐部人数
////            $delete_count = BusinessModel::decCount('clubinfo', $counWhere, 'ClubPlayerCount', $count);
//
////            if ($delete_count) {
////                Db::commit();//执行
////                exitJson(200, '删除成功');
////            } else {
////                Db::rollback();//回滚
////                exitJson(500, '删除失败');
////            }
////        }else{
//////            $delete_count = BusinessModel::decCount('clubinfo', $counWhere, 'ClubPlayerCount', $res);
////            if($res&&$delete_count){
////                Db::commit();//执行
////                exitJson(200, '删除成功');
////            }else{
////                Db::rollback();//回滚
////                exitJson(500, '删除失败');
////            }
////
//        }

    }
    /**
     * 删除普通用户   已关闭
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $DeletedUserID    被删除者userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */

    public function deleteNormal()
    {
        exitJson(300,'功能关闭');
    }
    /**
     * 邀请成员 已关闭
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $invitedGameID    被邀请者gameid
     * $AgentID    被操作代理id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function invitedNormal()
    {
        $UserID = input('UserID/d');//执行者的user_id
        $ClubID = input('ClubID/d');//俱乐部id
        $invitedGameID=input('invitedGameID/d');//被调整者gameid
        $AdjustPercent=input('AdjustPercent/d');//调整比例
        $sign=input('sign/s');//签名
    }

    /**
     * 调配成员 已关闭
     * 合伙人
     *
     * $UserID      用户id
     * $ClubID      俱乐部id
     * $deployGameID   被调配者gameid
     * deployUserID    被调配者的代理userid
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function deployNormal()
    {

    }

    public function test(){
        $downUpdate=clubuser::execute('update dbo.clubuser set AgentDistributorId = UserID where UserID = 10022 and ClubID = 1000000');
        p(Db::getLastSql());
    }
}