<?php
/**
 * Created by PhpStorm.
 * User: 95179
 * Date: 2019/12/3
 * Time: 17:26
 */

namespace app\Client\model;
use think\Model;

class personalroomscoreinfo extends Model
{
    /**
     * 详细数据
     * 获取详细数据中的表情记录数据
     */
    static function getDetailExpressionRecordData($ClubID,$UserID,$level){
        if($level=='partner'){
            $list=personalroomscoreinfo::alias('prsi')
                ->where(array('prsi.ClubID'=>$ClubID,'cu.Reviewed'=>1,'cu.DistributorId'=>$UserID))
                ->join('ClubUser cu ',' cu.UserID = prsi.UserID and cu.ClubID = prsi.ClubID')
                ->field('cu.userid,cu.nickname,cu.gameid,prsi.revenue,prsi.roomid,prsi.writetime')
                ->order('WriteTime desc')
                ->select()
                ->toArray();
        }elseif($level=='boss'||$level=='manager'){
            $list=personalroomscoreinfo::alias('prsi')
                ->where(array('prsi.ClubID'=>$ClubID,'cu.Reviewed'=>1))
                ->join('ClubUser cu ',' cu.UserID = prsi.UserID and cu.ClubID = prsi.ClubID')
                ->field('cu.userid,cu.nickname,cu.gameid,prsi.revenue,prsi.roomid,prsi.writetime')
                ->order('WriteTime desc')
                ->select()
                ->toArray();
        }

        return $list;
    }
}