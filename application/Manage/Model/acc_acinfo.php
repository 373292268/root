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

class acc_acinfo extends Model
{
    public static function conn_accounts($db='ryaccountsdb'){
        $config=Config::get('database.');
        $config['database']=$db;
        $connect=Db::connect($config);
        return $connect;
    }
    /*
     * 获取用户注册数量
     *
     * @return $reg_count 人数
     * */
    public static function getUserRegCount(){
        $reg_count=self::conn_accounts()->table('accountsinfo')->count();
        return $reg_count;
    }

    /*
* 获取用户列表
*
* @return $userList 用户列表
* */
    public static function getAllUserList(){
        $userList=self::conn_accounts()
            ->table('accountsinfo')
            ->alias('ai')
//            ->join('RYPlatformDBLink.RYPlatformDB.dbo.clubuser cu','cu.UserID = ai.UserID')
            ->field('ai.UserID,ai.GameID,ai.NickName,ai.Compellation,ai.PassPortID,ai.LastLogonDate,ai.LastLogonIP,ai.WebLogonTimes,ai.StunDown,(select count(*) from RYPlatformDBLink.RYPlatformDB.dbo.clubuser cu where ai.UserID = cu.UserID ) as clubCount')
            ->paginate(15);
//        p($userList);
//        exit;
        return $userList;
    }

    /*
* 获取单个用户信息
*
* @return $userInfo 用户信息
* */
    public static function getUserInfo($user_id){
        $userInfo=self::conn_accounts()->table('accountsinfo ai')->field('ai.UserID,ai.GameID,ai.NickName,ai.StunDown,urc.RoomCard')->leftJoin('RYTreasureDBLink.rytreasuredb.dbo.userroomcard urc' , 'urc.UserID = ai.UserID')->where(['ai.UserID'=>$user_id])->findOrEmpty();
        return $userInfo;
    }
    /*
* 获取白名单list
*
* @return $MessageList 获取白名单
* */
    public static function getWhiteList(){
        $whiteList=self::conn_accounts()
            ->table('accountswhitelist')
            ->paginate(15);

        return $whiteList;
    }
}