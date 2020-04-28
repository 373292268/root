<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/3/2
 * Time: 14:28
 */

namespace app\Accounts\Controller;
use app\Accounts\model\accountssend;
use \think\Controller;
use Think\Db;

class Api extends Controller
{

    /**
     * 储存头像/更新头像
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function saveImageNew(){
        $image_url = input('url/s');//头像网络地址
        $userid = input('userid/d');//user_id
        $ip = input('ip/s');
        $machine = input('machine/s');
        $md5=input('md5/s');
//        校验参数
        if(empty($image_url)||empty($userid)||empty($ip)||empty($machine)){
            exitJson(404,'参数错误');
        }

        $user_image=accountssend::where(['UserID'=>$userid])
            ->field('Head')
            ->findOrEmpty()
            ->toArray();
        if($user_image){
            if($md5==md5($user_image['Head'])){
                exitJson(202,'头像不需更新');
            }
            $update_result=accountssend::where(['UserID'=>$userid])->update(['Head'=>$image_url]);
        }else{
            $update_result=accountssend::where(['UserID'=>$userid])->insert(['Head'=>$image_url,'UserID'=>$userid]);
        }



        if($update_result){
            exitJson(200,'更新成功');
        }else{
            exitJson(204,'更新失败');
        }
    }
    /**
     * 获取用户头像
     * API
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function getImage(){
        $userid=$_POST['userid'];//用户id
        if(empty($userid)){
            exitJson(404,'参数为空');
        }
        $user_image=accountssend::where(['UserID'=>$userid])
            ->field('Head')
            ->findOrEmpty()
            ->toArray();
        if(empty($user_image)){
            exitJson(403,'无数据');
        }
        exitJson(200,'接收成功',$user_image);
    }

}