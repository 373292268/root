<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/18
 * Time: 14:32
 */

namespace app\Client\model;


use think\Db;
use think\Model;
use think\facade\Config;

class connect extends Model
{
    public static function conn_platform($db='ryaccountsdb'){
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
         * 获取保险箱记录
         *
         * @return $club_count 俱乐部数
         * */
    public static function getCofferRecord($UserID,$ClubID){
        $CofferRecord=self::conn_platform()->table('recordcoffer')->where(['userID'=>$UserID,'ClubID'=>$ClubID])->limit(20)->field('userID,clubID,score,conffer,addScore')->select()->toArray();
        return $CofferRecord;
    }

}