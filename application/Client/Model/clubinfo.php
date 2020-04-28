<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/2
 * Time: 14:25
 */
namespace app\Client\model;
use think\Model;

class clubinfo extends Model
{
    /**
     * 增加俱乐部数据中的人数
     * $where         条件
     * @access static
     * @return string
     */
    static function IncClubNumberPeople($ClubID){
        $MatchScore=clubuser::where(['ClubID'=>$ClubID])->setInc('ClubPlayerCount');
        return $MatchScore;
    }
}