<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/7/6
 * Time: 17:33
 */

namespace app\Client\Controller;


use app\Client\model\clubuser;
use app\Client\model\clubuserscorerecord;
use app\Client\model\connect;
use Think\Db;

class User
{
    public $Status=false;
    public $UserID;
    public $ClubID;
    public $NickName;
    public $GameID;
    public $HeadImg;
    public $Score;
    public $Coffer;
    public $Level;

    public function __construct($UserID = null,  $GameID = null)
    {
        $this->UserID = $UserID;
        $this->GameID = $GameID;
        if ($this->GameID && $this->UserID==null) {
            return $this->Status=$this->getUserBasicInfoByGameID();
        } elseif ($this->UserID &&$this->GameID==null) {
            return $this->Status=$this->getUserBasicInfoByUserID();
        } elseif ($this->UserID==null && $this->GameID==null) {
            return $this->Status=false;
        }
    }


    /**
     * 获取用户头像
     *
     *
     */
    public function getUserHeadImg()
    {

        $UserClubInfo  = connect::conn_platform()
            ->table('accountssend')
            ->where([
                ['UserID', '=', $this->UserID]
            ])
            ->field('Head')
            ->findOrEmpty();
        $this->HeadImg = $UserClubInfo['Head'];
    }
    /**
     * 获取用户积分身份
     *
     * pram @ClubID 俱乐部ID
     * return @void
     */
     public function getUserClubScoreLevel($ClubID){
        $this->ClubID=$ClubID;
        $UserClubScore=clubuser::where([
            ['ClubID','=',$this->ClubID],
            ['UserID','=',$this->UserID]
        ])
            ->field('MatchScore,Coffer,UserRight')
            ->findOrEmpty()
            ->toArray();
//        p($UserClubScore);
//        exit;
        $this->Coffer=$UserClubScore['Coffer'];
        $this->Score=$UserClubScore['MatchScore'];
        $this->Level=$UserClubScore['UserRight'];
     }


    /**
     * 增减用户保险箱积分
     *
     * pram @OriginalScore 原始积分，以正负判断
     *
     */
    public function changUserClubCoffer($OriginalScore){
        $ChangScore=abs($OriginalScore);
        if($OriginalScore>0){
            $Result=clubuser::where([
                ['ClubID','=',$this->ClubID],
                ['UserID','=',$this->UserID]
            ])
                ->setInc('Coffer',$ChangScore);
        }elseif ($OriginalScore<0){
            $Result=clubuser::where([
                ['ClubID','=',$this->ClubID],
                ['UserID','=',$this->UserID]
            ])
                ->setDec('Coffer',$ChangScore);
        }
        return $Result;
    }
    /**
     * 用户的积分变动记录
     *
     * pram @OriginalScore  原始积分，以正负判断
     * pram @UserID         执行者id
     * pram @type           1是老板2是管理员3是合伙人4退出返还5删除返还6转账
     *
     */
    public function insertUserScoreRecord($OriginalScore,$UserID,$type){
        $add=[
            'userid'=>$this->UserID,
            'gameid'=>$this->GameID,
            'clubid'=>$this->ClubID,
            'before'=>$this->Coffer,
            'score'=>$OriginalScore,
            'after'=>$this->Coffer+$OriginalScore,
            'operate_userid'=>$UserID,
            'operate_type'=>$type
        ];
        $Result=clubuserscorerecord::insert($add);

        return $Result;
    }
















    /**
     * 通过UserID获取用户基本信息
     *
     * GameID和NickName
     */
    private function getUserBasicInfoByUserID()
    {

        $UserClubInfo   = connect::conn_platform()
            ->table('accountsinfo')
            ->where([
            ['UserID', '=', $this->UserID]
        ])
            ->field('GameID,NickName')
            ->findOrEmpty();
        if(!empty($UserClubInfo)){
            $this->NickName = $UserClubInfo['NickName'];
            $this->GameID   = $UserClubInfo['GameID'];
            return true;
        }else{
            return false;
        }

    }

    /**
     * 通过GameID获取用户基本信息
     *
     * UserID和NickName
     */
    private function getUserBasicInfoByGameID()
    {

        $UserClubInfo   = connect::conn_platform()
            ->table('accountsinfo')
            ->where([
            ['GameID', '=', $this->GameID]
        ])
            ->field('UserID,NickName')
            ->findOrEmpty();
        if(!empty($UserClubInfo)){
            $this->NickName = $UserClubInfo['NickName'];
            $this->UserID   = $UserClubInfo['UserID'];
            return true;
        }else{
            return false;
        }

    }

}