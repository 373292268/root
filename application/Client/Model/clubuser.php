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
    /**new
     * 合伙人获取成员列表
     * @access static
     * @return string
     */
    static function getUserListForPartner($UserID,$ClubID){
//        echo $UserID;
//        exit;
        $userList = clubuser::where([
                ['ClubID','=',$ClubID],
                ['DistributorId','=',$UserID],
                ['Reviewed','>',0]
            ])
            ->field('UserID,GameID,NickName,WinCount,MatchScore+Coffer as MatchScore,UserRight,Reviewed')
            ->paginate(5)
            ->toArray();
        return $userList;
    }
    /**new
     * 管理获取成员列表
     * @access static
     * @return string
     */
        static function getUserListForBoss($UserID,$ClubID){
        $userList = clubuser::where([
            ['ClubID','=',$ClubID],
            ['Reviewed','>',0]
            ])
            ->field('UserID,GameID,NickName,WinCount,MatchScore+Coffer as MatchScore,Reviewed,UserRight')
            ->paginate(5)
            ->toArray();
        return $userList;
    }
    /**new  湖南项目
     * 管理获取成员列表
     * @access static
     * @return string
     */
    static function getUserListForBoss_hunan($UserID,$ClubID){
        $userList = clubuser::alias('cu')
            ->where([
                ['cu.ClubID','=',$ClubID],
                ['Reviewed','>',0]
            ])
//            ->leftJoin('clubuser cuu','cuu.UserID = cu.UserID')
            ->field('
            cu.UserID,
            cu.GameID,
            cu.NickName,
            MatchScore+Coffer as MatchScore,
            cu.Reviewed,
            cu.UserRight,
            (select sum(TeaChange) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$UserID.' and RelationID = cu.UserID and ClubID = cu.ClubID and DATEDIFF(DAY,RecordDate,GETDATE())=0) as ContributionValue,
            (select count(*) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as count,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as YesterdayScore,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=0) as Score
            ')
            ->paginate(5)
            ->toArray();
        return $userList;
    }
    /**new  湖南项目
     * 合伙人获取成员列表
     * @access static
     * @return string
     */
    static function getUserListForPartner_hunan($UserID,$ClubID){
//        echo $UserID;
//        exit;
        $userList = clubuser::alias('cu')
            ->where([
            ['cu.ClubID','=',$ClubID],
            ['DistributorId','=',$UserID],
            ['Reviewed','>',0]
        ])
//            ->leftJoin('RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend rugbe','rugbe.UserID = cu.UserID')
            ->field('
            cu.UserID,
            cu.GameID,
            cu.NickName,
            MatchScore+Coffer as MatchScore,
            cu.Reviewed,
            cu.UserRight,
            (select sum(TeaChange) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$UserID.' and RelationID = cu.UserID and ClubID = cu.ClubID and DATEDIFF(DAY,RecordDate,GETDATE())=0) as ContributionValue,
            (select count(*) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as count,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as YesterdayScore,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=0) as Score
            ')
            ->paginate(5)
            ->toArray();
        return $userList;
    }
    /** 湖南项目
     * 使用userID查询用户信息
     * */
    static function getUserClubInfoByUserID_hunan($UserID,$ClubID){
        $user_info_for_user_id = clubuser::alias('cu')
            ->where([
            ['cu.UserID','=',$UserID],
            ['cu.ClubID','=',$ClubID]
        ])
//            ->leftJoin('RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend rugbe','rugbe.UserID = cu.UserID')
            ->field('
            cu.UserID,
            cu.GameID,
            cu.NickName,
            MatchScore+Coffer as MatchScore,
            cu.Reviewed,
            cu.UserRight,
            (select sum(TeaChange) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$UserID.' and RelationID = cu.UserID and ClubID = cu.ClubID and DATEDIFF(DAY,RecordDate,GETDATE())=0) as ContributionValue,
            (select count(*) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as count,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1) as YesterdayScore,
            (select sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.recordusergamebigend where UserID = cu.UserID and LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=0) as Score
            ')
            ->findOrEmpty()
            ->toArray();
//        p($user_info);
//        exit;
        return $user_info_for_user_id;
    }
    /**new
     * 合伙人搜索成员
     * @access static
     * @return string
     */
    static function getServerUserListForPartner($UserID,$ServerGameID,$ClubID){
        $userList = clubuser::where([
                ['ClubID','=',$ClubID],
                ['DistributorId','=',$UserID],
                ['GameID','=',$ServerGameID],
                ['Reviewed','>',0]
            ])
//            ->whereOr('UserID='.$UserID.' and ClubID='.$ClubID)
            ->field('UserID,GameID,NickName,WinCount,MatchScore+Coffer as MatchScore,Reviewed')
            ->findOrEmpty()
            ->toArray();
        return $userList;
    }
    /**new
     * 管理搜索成员
     * @access static
     * @return string
     */
    static function getServerUserListForBoss($UserID,$ServerGameID,$ClubID){
        $userList = clubuser::where([
                ['ClubID','=',$ClubID],
                ['GameID','=',$ServerGameID],
                ['Reviewed','>',0]
            ])
//            ->whereOr('UserID='.$UserID.' and ClubID='.$ClubID)
            ->field('UserID,GameID,NickName,WinCount,MatchScore+Coffer as MatchScore')
            ->findOrEmpty()
            ->toArray();
        return $userList;
    }
    /**new
     * 获取普通用户的信息
     * @access static
     * @return string
     */
    static function getUserInfoByUser($ClickUserID,$ClubID){
        $userList = clubuser::where([
                ['ClubID','=',$ClubID],
                ['Reviewed','>',0],
                ['UserID','=',$ClickUserID]
            ])
            ->field('CreateDateTime,Reviewed')
            ->findOrEmpty()
            ->toArray();
        return $userList;
    }
    /**new
     * 获取其他用户的信息
     * @access static
     * @return string
     */
    static function getUserInfoByOther($ClickUserID,$ClubID,$Level){
        if($Level==3||$Level==2){
            $userList = clubuser::where([
                    ['ClubID','=',$ClubID],
                    ['Reviewed','>',0],
                    ['UserID','=',$ClickUserID]
                ])
                ->field('
                CreateDateTime,Reviewed,
                (select isnull(sum(TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,RecordDate,GETDATE())=0) as todayExpression,
                (select isnull(sum(TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,RecordDate,GETDATE())=1) as yesterdayExpression
                ')
                ->findOrEmpty()
                ->toArray();
        }elseif($Level==1){
            $userList = clubuser::where([
                    ['ClubID','=',$ClubID],
                    ['Reviewed','>',0],
                    ['UserID','=',$ClickUserID]
                ])
                ->field('
                CooperatePercent,CreateDateTime,Reviewed,
                (select isnull(sum(TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,RecordDate,GETDATE())=0) as todayExpression,
                (select isnull(sum(TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.recrodteainfo where UserID = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,RecordDate,GETDATE())=1) as yesterdayExpression,
                (select count(*) from clubuser where DistributorId = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,CreateDateTime,GETDATE())=1) as yesterdayDownUser,
                (select count(*) from clubuser where DistributorId = '.$ClickUserID.' and ClubID = '.$ClubID.' and DATEDIFF(DAY,CreateDateTime,GETDATE())=0) as todayDownUser,
                (select count(*) from clubuser where DistributorId = '.$ClickUserID.' and  ClubID = '.$ClubID.') as DownUser
                ')
                ->findOrEmpty()
                ->toArray();
        }

        return $userList;
    }
    /**
     * 使用userID查询用户信息
     * */
    static function getUserClubInfoByUserID($UserID,$ClubID,$field){
        $user_info_for_user_id = clubuser::where([
                ['UserID','=',$UserID],
                ['ClubID','=',$ClubID]
            ])
            ->field($field)
            ->findOrEmpty()
            ->toArray();
//        p($user_info);
//        exit;
        return $user_info_for_user_id;
    }
    /**
     * 使用GameID查询用户信息
     * */
    static function getUserClubInfoByGameID($GameID,$ClubID,$field){
        $user_info_for_user_id = clubuser::where([
                ['GameID','=',$GameID],
                ['ClubID','=',$ClubID]
            ])
            ->field($field)
            ->findOrEmpty()
            ->toArray();
//        p($user_info);
//        exit;
        return $user_info_for_user_id;
    }
    /**  湖南项目
     * 点击合伙人获取到的数据
     * $where         条件
     * @access static
     * @return string
     */
    static function getAgentList_hunan($UserID,$ClubID){

        $sql='
          SELECT 
          tc.GameID,
          tc.UserID,
          tc.NickName,
          Revenue=tc.TotalRevenue,
          tc.CooperatePercent,
          GrossScore=(SELECT sum(cuu.MatchScore)+sum(cuu.Coffer) from ClubUser cuu LEFT JOIN ClubUser cu on cu.DistributorId = tc.UserID and cu.ClubID = tc.ClubID where cuu.UserID = cu.UserID and cuu.ClubID = cu.ClubID),
          -- PlayCount=(SELECT count(*) from RYTreasureDBLink.RYTreasureDB.dbo.RecordUserGameBigEnd rugbe LEFT JOIN ClubUser cu on cu.DistributorId = tc.UserID where rugbe.UserID = cu.UserID and rugbe.LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=1),
          -- WinAndLose=(SELECT sum(WinScore) from RYTreasureDBLink.RYTreasureDB.dbo.RecordUserGameBigEnd rugbe LEFT JOIN ClubUser cu on cu.DistributorId = tc.UserID where rugbe.UserID = cu.UserID and rugbe.LockClubID = cu.ClubID and DATEDIFF(DAY,ConcludeTime,GETDATE())=0),
          TodayCooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=0 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
          YesterdayCooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID)
          FROM dbo.ClubUser tc 
          WHERE (tc.ClubID=? AND tc.DistributorId = ? AND UserRight=1) OR (tc.ClubID =? AND tc.UserID = ?)
          ';
        $agentList=clubuser::query($sql,[$ClubID,$UserID,$ClubID,$UserID]);
        return $agentList;
    }
    /**
     * 点击合伙人获取到的数据
     * $where         条件
     * @access static
     * @return string
     */
    static function getAgentList($UserID,$ClubID){

        $sql='
          SELECT 
          tc.GameID,
          tc.UserID,
          tc.NickName,
          Revenue=tc.TotalRevenue,
          tc.CooperatePercent,
          TodayCooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=0 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
          YesterdayCooperete=(SELECT ISNULL(SUM(rti.TeaChange),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID),
          YesterdayRevenue= (SELECT ISNULL(SUM(rti.TeaScore),0) from RYRecordDBLink.RYRecordDB.dbo.RecrodTeaInfo rti where DATEDIFF(DAY,RecordDate,GETDATE())=1 AND rti.ClubID = tc.ClubID AND rti.UserID = tc.UserID) 
          FROM dbo.ClubUser tc 
          WHERE (tc.ClubID=? AND tc.DistributorId = ? AND UserRight=1) OR (tc.ClubID =? AND tc.UserID = ?)
          ';
        $agentList=clubuser::query($sql,[$ClubID,$UserID,$ClubID,$UserID]);
        return $agentList;
    }

    /**new
     * 搜索合伙人
     * @access static
     * @return string
     */
    static function getServerAgentListForServer($UserID,$ServerGameID,$ClubID,$Type='partner'){
        //如果$Type为boss，则证明是馆主查询类型
        if($Type=='boss'){
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
              WHERE tc.ClubID=? AND tc.GameID = ?
              ';
            $agentList=clubuser::query($sql,[$ClubID,$ServerGameID]);
            //如果$Type为partner，则证明是合伙人查询类型
        }else{
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
              WHERE tc.ClubID=? AND tc.GameID = ? AND tc.DistributorId = ?
              ';
            $agentList=clubuser::query($sql,[$ClubID,$ServerGameID,$UserID]);
        }


        return $agentList;
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
     * 点击合伙人获取到的数据
     * $where         条件
     * @access static
     * @return string
     */
    static function getAgentList_bak($ClubID){
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

}