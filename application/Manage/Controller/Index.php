<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/2
 * Time: 16:22
 */
namespace app\Manage\Controller;

use app\Manage\model\acc_acinfo;
use app\Manage\model\club;

class Index extends Common
{
    protected $login_status=false;
    /**
     * 公共方法
     */
    public function initialize()
    {
        parent::initialize();
        $this->login_status=session('login_status');

        if($this->login_status==false){
            exit('身份丢失');
        }
    }
    public function index(){
//        p($_SESSION);
//        exit;
//        $todayDate=''

        return $this->fetch();
    }
    public function body(){
        $data['reg_count']=acc_acinfo::getUserRegCount();//总注册人数
        $data['score_count']=club::getIntegralCount();//总积分数
        $data['score_count']=$data['score_count']/100;
        $data['boss_score_sum']=club::getBossScoreSum();//馆主积分总数
        $data['boss_score_sum']=$data['boss_score_sum']/100;
        $data['agent_score_sum']=club::getAgentScoreSum();//合伙人积分总数
        $data['agent_score_sum']=$data['agent_score_sum']/100;
        $data['user_score_sum']=club::getUserScoreSum();//用户积分总数
        $data['user_score_sum']=$data['user_score_sum']/100;
        $data['coffer_score_sum']=club::getUserCofferSum();//用户保险箱总数
        $data['coffer_score_sum']=$data['coffer_score_sum']/100;
//        $data['log_count']=acc_acinfo::getUserRegTodayCount();//今日登陆人数
//        $data['club_count']=club::getClubCount();//俱乐部个数
        $LevelCount=club::getLevelCount();//馆主,一，二，三，四，五级合伙人个数
        $data=$data+$LevelCount;
//        p($data);
//        exit;

//        $data['up_score_count']=club::getUpIntegralCount();//总上分数
//        $data['down_score_count']=club::getDownIntegralCount();//总下分数
        $time=strtotime(date('Y-m-d'));
        $time=$time-24*3600*6;
        $avg=array();
        $atime=array();
        for($i=0;$i<7;$i++){
            $t=date('Y-m-d',$time);
            $atime[]=$t;
            $avg[]=$i;
            $time=$time+24*3600;
        }
        foreach ($atime as $key => $val){
//            echo $val;
//            exit;
            $date_data['reg'][]=acc_acinfo::conn_accounts()->table('accountsinfo')->where("convert(nvarchar(10),RegisterDate,120)='".$val."'")->count();
            $date_data['login'][]=acc_acinfo::conn_accounts()->table('accountsinfo')->where("convert(nvarchar(10),LastLogonDate,120)='".$val."'")->count();
        }
//        p($atime);
//        exit;
        $atime=json_encode($atime,true);
        $atime=str_replace('"', '\'', $atime);
        $atime=str_replace('[', '', $atime);
        $atime=str_replace(']', '', $atime);
        $reg=json_encode($date_data['reg'],true);

        $login=json_encode($date_data['login'],true);
//        $login=str_replace('"', '\'', $login);
//        $login=str_replace('[', '', $login);
//        $login=str_replace(']', '', $login);
//        p($login);
//        exit;
//        $this->assign('avg',$avg);
        $this->assign('atime',$atime);
        $this->assign('reg',$reg);
        $this->assign('login',$login);
//        p($data);
//        exit;
        $this->assign('data',$data);
        return $this->fetch();
    }

}