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

class web extends Model
{
    public static function conn_platform($db='rynativewebdb'){
        $config=Config::get('database.');
        $config['database']=$db;
        $connect=Db::connect($config);
        return $connect;
    }

    /*
* 获取滚动公告list
*
* @return $NoticeList 获取滚动公告列表
* */
    public static function getScrollNotice(){
        $NoticeList=self::conn_platform()
            ->table('news')
            ->field('NewsID,Subject,OnTop,Body,LastModifyDate')
            ->paginate(15);

        return $NoticeList;
    }
    /*
* 获取消息list
*
* @return $MessageList 获取消息列表
* */
    public static function getMessage(){
        $MessageList=self::conn_platform()
            ->table('ads')
            ->where(['Type'=>0,'SortID'=>0])
            ->field('ID,Title,Remark,ResourceURL')
            ->paginate(15);

        return $MessageList;
    }

}