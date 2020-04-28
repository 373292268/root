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

class record extends Model
{
    public static function conn_platform($db='ryrecorddb'){
        $config=Config::get('database.');
        $config['database']=$db;
        $connect=Db::connect($config);
        return $connect;
    }
    public static function DiamondRecordSearch($condition){
        $clubInfo=self::conn_platform('ryaccountsdb')
            ->table('accountsinfo')
            ->alias('ai')
            ->where(["ai.GameID" => (int)$condition])
            ->leftJoin('RYRecordDBLink.RYRecordDB.dbo.recordroomcard rrc','rrc.SourceUserID = ai.UserID')
            ->field('rrc.RecordID,ai.GameID,rrc.SBeforeCard,rrc.RoomCard,rrc.CollectDate,rrc.Remarks')
            ->paginate(15,false,['query'=>input()]);
//        p($userList);
//        exit;
        return $clubInfo;
    }
}