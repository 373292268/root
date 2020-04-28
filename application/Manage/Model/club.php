<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/18
 * Time: 14:32
 */

namespace app\Manage\model;


use think\Db;
use think\Model;
use think\facade\Config;

class club extends Model
{
    public static function conn_platform($db='ryplatformdb'){
        $config=Config::get('database.');
        $config['database']=$db;
        $connect=Db::connect($config);
        return $connect;
    }
    /*
     * 获取俱乐部数量
     *
     * @return $club_count 俱乐部数
     * */
    public static function getClubCount(){
        $club_count=self::conn_platform()->table('clubinfo')->count();
        return $club_count;
    }
    /*
     * 获取总积分数值
     *
     * @return $club_count 总积分数
     * */
    public static function getIntegralCount(){
        $score_count=self::conn_platform()->table('clubuser')->sum('MatchScore');
        return $score_count;
    }

    /*
 * 获取上分数值
 *
 * @return $club_count 上分数
 * */
    public static function getUpIntegralCount(){
        $score_count=self::conn_platform()->table('clubuserscorerecord')->whereTime('setdate', 'today')->where('score','>','0')->sum('score');
        return $score_count;
    }
    /*
    * 获取下分数值
    *
    * @return $club_count 下分数
    * */
    public static function getDownIntegralCount(){
        $score_count=self::conn_platform()->table('clubuserscorerecord')->whereTime('setdate', 'today')->where('score','<',0)->sum('score');
        return $score_count;
    }
    /*
     * 获取馆主积分总数
     *
     * @return $reg_count 人数
     * */
    public static function getBossScoreSum(){
        $reg_count=self::conn_platform()->table('clubuser')->where('UserRight',3)->field('sum(MatchScore)+sum(Coffer) as sum')->find();
        return $reg_count['sum'];
    }
    /*
 * 获取合伙人积分总数
 *
 * @return $reg_count 人数
 * */
    public static function getAgentScoreSum(){
        $reg_count=self::conn_platform()->table('clubuser')->where('UserRight',1)->field('sum(MatchScore)+sum(Coffer) as sum')->find();
        $reg_count['sum']=empty($reg_count['sum'])?0:$reg_count['sum'];
//        p($reg_count);
//        exit;
        return $reg_count['sum'];
    }
    /*
* 获取用户积分总数
*
* @return $reg_count 人数
* */
    public static function getUserScoreSum(){
        $reg_count=self::conn_platform()->table('clubuser')->where('UserRight',0)->field('sum(MatchScore)+sum(Coffer) as sum')->find();
        return $reg_count['sum'];
    }
    /*
* 获取用户保险箱总数
*
* @return $reg_count 人数
* */
    public static function getUserCofferSum(){
        $reg_count=self::conn_platform()->table('clubuser')->sum('Coffer');
        return $reg_count;
    }
    /**
* 馆主,一，二，三，四，五级合伙人个数
*
* @return $reg_count 人数
* */
    public static function getLevelCount(){
        $reg_count['boss_count']=self::conn_platform()->table('clubuser')->where(['UserRight'=>3])->count();
        $reg_count['first_count']=self::conn_platform()->table('clubuser')->where(['UserLevel'=>1])->count();
        $reg_count['second_count']=self::conn_platform()->table('clubuser')->where(['UserLevel'=>2])->count();
        $reg_count['third_count']=self::conn_platform()->table('clubuser')->where(['UserLevel'=>3])->count();
        $reg_count['fourth_count']=self::conn_platform()->table('clubuser')->where(['UserLevel'=>4])->count();
        $reg_count['fifth_count']=self::conn_platform()->table('clubuser')->where('UserLevel','>',5)->count();
        return $reg_count;
    }
    /*
    * 获取茶馆列表
    *
    * @return $clubList 茶馆列表
    * */
    public static function getAllTeaHouseList(){
        $clubList=self::conn_platform()
            ->table('clubinfo')
//            ->join('RYPlatformDBLink.RYPlatformDB.dbo.clubuser cu','cu.UserID = ai.UserID')
            ->field('ClubID,NickName,ClubName,ClubPlayerCount,NeedReview,IsClosed,ClubStatus')
            ->paginate(15);
//        p($userList);
//        exit;
        return $clubList;
    }
    /*
    * 获取茶馆详细信息
    *
    * @return $clubInfo 获取茶馆详细信息
    * */
    public static function getClubInfo($ClubID){
        $clubInfo=self::conn_platform()
            ->table('clubinfo')
            ->where(['ClubID'=>$ClubID])
            ->field('ClubID,ClubStatus')
            ->findOrEmpty();
//        p($userList);
//        exit;
        return $clubInfo;
    }
    /*
* 根据id或名字获取茶馆信息
*
* @return $clubInfo 获取茶馆详细信息
* */
    public static function teaHouseSearch($condition){
        $clubInfo=self::conn_platform()
            ->table('clubinfo')
            ->where("ClubID = ".(int)$condition." or ClubName like '%$condition%'")
            ->field('ClubID,NickName,ClubName,ClubPlayerCount,NeedReview,IsClosed,ClubStatus')
            ->paginate(15,false,['query'=>input()]);
//        p($userList);
//        exit;
        return $clubInfo;
    }
    /*
* 根据ClubID获取某个俱乐部的所有人
*
* @return $clubInfo 获取茶馆详细信息
* */
    public static function getClubUserListByClubID($ClubID){
        $userList=self::conn_platform()
            ->table('clubuser')
            ->alias('cu')
            ->where(['ClubID'=>$ClubID])
            ->join('RYAccountsDBLink.RYAccountsDB.dbo.accountsinfo ai','ai.UserID = cu.UserID')
//            ->join('clubuser cuu','cuu.UserID = cu.UserID')
            ->field('ai.UserID,ai.GameID,ai.NickName,ai.LastLogonDate,ai.LastLogonIP,ai.WebLogonTimes,ai.StunDown,cu.UserRight,(select count(*) from clubuser cu where cu.UserID = ai.UserID ) as clubCount,(select count(*) from clubuser cu where cu.DistributorId = cu.UserID ) as downUser,(select count(*) from clubuser cu where cu.AgentDistributorId = cu.UserID ) as downAgent')
            ->paginate(15,false,['query'=>input()]);

        return $userList;
    }
}