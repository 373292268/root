<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/6
 * Time: 18:13
 */

namespace app\Manage\Controller;

use think\Controller;

class Login extends Controller
{
    public function login_index(){
        return $this->fetch();
    }
    public function login_do(){
        $userName=input('post.username');
        $password=input('post.password');

//        $menu=merge($menu);
//        $limit=array_search('')

        if(empty($userName)||empty($password)){
            return json([400,'参数为空']);
        }
        if($userName=='administrator'){
            if(md5($password)==md5('123456')){
                session('login_status',true,'think');

                return json([200,'登录成功']);
            }else{
                return json([500,'账号或密码错误']);
            }
        }else{
            return json([500,'账号或密码错误']);
        }
    }
}