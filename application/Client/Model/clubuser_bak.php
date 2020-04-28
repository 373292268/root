<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/2
 * Time: 14:25
 */
namespace app\Client\model;
use think\Model;

class clubuser extends Model
{
//    protected $pk = 'id';
    /**
     * 使用userID查询用户信息
     * */
    static function getUserClubInfo($UserID,$ClubID,$field){
        $user_info_for_user_id = clubuser::where(['UserID'=>$UserID,'ClubID'=>$ClubID])->field($field)->findOrEmpty()->toArray();
//        p($user_info);
//        exit;
        return $user_info_for_user_id;
    }
    /**
     * 使用GameID查询用户信息
     * */
    static function getUserClubInfoForGameID($GameID,$ClubID,$field){
        $user_info_for_game_id=clubuser::where(['GameID'=>$GameID,'ClubID'=>$ClubID])->field($field)->findOrEmpty()->toArray();
        return $user_info_for_game_id;
    }
    /**
     * 增减用户携带积分
     * */
    static function setUserScore($UserID,$ClubID,$score){
        $UpdateUserScore=clubuser::where(['UserID'=>$UserID,'ClubID'=>$ClubID])->setInc('MatchScore',$score);
        return $UpdateUserScore;
    }
    /**
 * 增减用户保险箱积分
 * */
    static function setUserCoffer($UserID,$ClubID,$score){
        $UpdateUserCoffer=clubuser::where(['UserID'=>$UserID,'ClubID'=>$ClubID])->setInc('Coffer',$score);
        return $UpdateUserCoffer;
    }
    /**
    * 获取用户身份
    * */
    static function getUserIdentity($ClubID,$GameID){
        $MatchScore=clubuser::where(['GameID'=>$GameID,'ClubID'=>$ClubID])->where('UserRight != 0 or DistributorId > 0')->count();
        return $MatchScore;
    }
    /**
    * 返还给上一级代理表情
    * */
    static function returnLastExpression($ClubID,$UserID,$number){
        $status=clubuser::where(['UserID'=>$UserID,'ClubID'=>$ClubID])->setInc('TotalRevenue',$number);
        return $status;
    }
    /**
     * 获取下级代理中合作比例最大值
     * $where         条件
     * @access static
     * @return string
     */
    static function getDownAgentPersentMax($where){
        $MatchScore=clubuser::where($where)->max('CooperatePercent');
        return $MatchScore;
    }
    /**
     * 删除一个用户数据
     * $where         条件
     * @access static
     * @return string
     */
    static function deleteUserData($UserID,$ClubID){
        $MatchScore=clubuser::where(['UserID'=>$UserID,'ClubID'=>$ClubID])->delete();
        return $MatchScore;
    }
    /**
     * 点击合伙人获取到的数据
     * $where         条件
     * @access static
     * @return string
     */
    static function getAgentList($ClubID){
        $sql='
              SELECT 
              tc.gameid,
              tc.userid,
              tc.nickname,
              revenue=tc.TotalRevenue,
              tc.cooperatepercent,
              today_cooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=0 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
              yesterday_cooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
              yesterday_revenue= (SELECT ISNULL(SUM(rti.TeaScore),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID) 
              FROM dbo.ClubUser tc 
              WHERE tc.ClubID='.$ClubID.' AND UserRight=1 AND UserLevel = 1
              ';
        $agentList=clubuser::query($sql);
        return $agentList;
    }
    /**
     * 点击下级合伙人获取到的数据
     * $where         条件
     * @access static
     * @return string
     */
    static function getNextAgentList($UserID,$ClubID){
        $sql='
            SELECT 
            tc.gameid,
            tc.userid,
            tc.nickname,
            revenue=tc.TotalRevenue,
            tc.cooperatepercent,
            today_cooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=0 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
            yesterday_cooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
            yesterday_revenue= (SELECT ISNULL(SUM(rti.TeaScore),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID) 
            FROM dbo.ClubUser tc 
            WHERE tc.ClubID='.$ClubID.' AND UserRight=1 AND (AgentDistributorId ='.$UserID.' or UserID = '.$UserID.')';
        $agentList=clubuser::query($sql);
//        p($sql);
//        exit;
        return $agentList;
    }
    /**
     * 获取下级用户的数据
     * @access static
     * @return string
     */
    static function getUserIndexInfo($UserID,$ClubID){
        $userList = clubuser
            ::alias("tc") //取一个别名
            ->join('clubuser cu ',' cu.ClubID=tc.ClubID and cu.userid='.$UserID)
            ->join('clubuserstatistics tcr',' tcr.userid = tc.UserID and tcr.clubid = tc.ClubID and DATEDIFF(DAY,tcr.setdate,GETDATE())=1','left')
            ->where(array('tc.ClubID'=>$ClubID,'tc.DistributorId'=>$UserID,'tc.Reviewed'=>1,'tc.UserRight'=>0))
            ->field('tc.gameid,tc.userid,tc.nickname,tc.revenue,cu.cooperatepercent,now_revenue=tc.revenue-isnull(tcr.revenue,0)')
            ->select()
            ->toArray();
        return $userList;
    }
    /**
     * 获取成员列表
     * @access static
     * @return string
     */
    static function getUserList($ClubID){
        $userList = clubuser::where(['ClubID'=>$ClubID])
            ->field('UserID,GameID,NickName,Reviewed,UserRight')
            ->limit(60)
            ->select()
            ->toArray();
        return $userList;
    }
}