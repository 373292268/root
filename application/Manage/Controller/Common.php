<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2019/12/18
 * Time: 11:25
 */

namespace app\Manage\Controller;
use think\Controller;
use app\Manage\Model\auth_rule;

class Common extends Controller
{
    public function initialize(){

        session('menu',null);
        $menu=auth_rule::where(['pid'=>0,'status'=>1])->select()->toArray();
        foreach ($menu as $key=>$val)
        {
            $menu[$key]['action'] =  auth_rule::where(array('pid'=>$val['id'],'status'=>1))->order('sort asc')->select()->toArray();
        }
        session('menu',$menu);
        $this->assign('menu',$menu);
    }
}