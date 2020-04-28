<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/3/23
 * Time: 16:46
 */

namespace app\Manage\Controller;


use app\Manage\model\acc_acinfo;
use app\Manage\model\record;
use Think\Db;

class Analyze extends Common
{

    /**
     * 钻石消耗列表
     * 分析
     *
     * $type        查询类型
     * $condition   查询条件
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function DiamondRecord(){
        $type=input('type/s');
        if($type=='search'){
            $condition=input('condition/s');
            $SearchList=record::DiamondRecordSearch($condition);
            $page = $SearchList->render();
            $data=$SearchList->toArray();
            $this->assign('list',$data['data']);
            $this->assign('page',$page);
            return $this->fetch('DiamondRecord');
        }
        $recordCardList=record::conn_platform()
            ->table('recordroomcard')
            ->alias('rrc')
            ->leftJoin('RYAccountsDBLink.RYAccountsDB.dbo.accountsinfo ai','ai.UserID = rrc.SourceUserID')
            ->field('rrc.RecordID,ai.GameID,rrc.SBeforeCard,rrc.RoomCard,rrc.CollectDate,rrc.Remarks')
            ->order('CollectDate desc')
            ->paginate(15);
        $page = $recordCardList->render();
        $data=$recordCardList->toArray();
//        p($data);
//        exit;
        $this->assign('page',$page);
        $this->assign('list',$data['data']);

        return $this->fetch('DiamondRecord');
    }
    /**
     * 统计本月钻石消耗
     * ajax
     *
     * $type        查询类型
     * $condition   查询条件
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function statisticsMonth(){
        $GameID=input('GameID/d');
        if(!empty($GameID)||$GameID!=''){
            $UserID=acc_acinfo::conn_accounts()
                ->table('accountsinfo')
                ->where(['GameID'=>$GameID])
                ->field('UserID')
                ->findOrEmpty();
            if(empty($UserID)){
                return json(['code'=>404,'msg'=>'用户不存在']);
            }
            $where=['SourceUserID'=>$UserID['UserID']];
        }else{
            $where=NULL;
        }
//        统计本月
//        消耗的
        $data['monthSpendStatistics']=record::conn_platform()
            ->table('recordroomcard')
            ->where($where)
            ->where(['TypeID'=>3])
            ->sum('RoomCard');

//        充值的
        $data['monthAddStatistics']=record::conn_platform()
            ->table('recordroomcard')
            ->where($where)
            ->where(['TypeID'=>1])
            ->sum('RoomCard');

//        返还的
        $data['monthBackStatistics']=record::conn_platform()
            ->table('recordroomcard')
            ->where($where)
            ->where(['TypeID'=>5])
            ->sum('RoomCard');
//        总计的
        $data['monthAllStatistics']=$data['monthAddStatistics']+$data['monthBackStatistics']-$data['monthSpendStatistics'];
        return json(['code'=>200,'msg'=>'获取成功','data'=>$data]);
    }
}