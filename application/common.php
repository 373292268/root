<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------


/**
 * 输出调用
 * @param  int $code 状态码
 * @param  string $message 返回信息
 * @param  array $data 数据信息
 */
function exitJson($code, $message = '', $data = array())
{

    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Method:POST,GET");

    header('Content-Type:application/json');
    exit(json_encode(array('code' => $code, 'message' => $message, 'data' => $data), JSON_UNESCAPED_UNICODE));


}
/**
 * 断点输出
 * @param  array $arr 输出数组
 */
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}
/**
 * 客户端签名
 * @param  array $data 签名数组
 */
function Sign($data){
    $key = MD5($data['UserID'] . $data['ClubID'] . $data['key']);
    return $key;
}

/**
 * 取相反数
 * @param  array $number 所取数值
 */
function take_opposite($number = 0)
{
    return (int)$number > 0 ? -1 * (int)$number : abs((int)$number);
}

// 对象变数组
function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

function time_bec()
{
    $time = date("Y-m-d H:i:s", time());
    return $time;
}

function writeLog($data, $name = null, $sign = null)//数据，文件名字，标志
{
    if ($name == null) {
        $name = date('Y-m-d', time()) . '.log';
    }
    // file_put_contents('test.log', $name);
    file_put_contents('../runtime/custom/' . $name, '---------------------------------------------------' . $sign . '---------------------------------------------------------' . PHP_EOL, FILE_APPEND);
    file_put_contents('../runtime/custom/' . $name, PHP_EOL . date('Y-m-d H:i:s', time()) . PHP_EOL, FILE_APPEND);
    file_put_contents('../runtime/custom/' . $name, PHP_EOL . var_export($data, true) . PHP_EOL, FILE_APPEND);
}
function merge($arr){
    foreach ($arr as $key => $val){
        $list[$val['controller']][]=$val;
        $listkey[]=$val['controller'];
    }
    p($list);
    p(array_unique($listkey));
    exit;
}

//随机生成六位数组
function code()
{
    $code = rand(100000, 999999);
    return $code;
}
//查询数据库中是否有值，如果有就继续生成
function CreateOpenID()
{

    $num = code();
    $codenumber = \app\Client\model\clubuser::where(array('OpenID'=>$num))->field('OpenID')->find();
    if($codenumber){
        CreateOpenID();
    }else{
        return $num;
    }

}
//function arr_where($where,$arr){
//    $value=array_values($where);
//    $key=array_keys($where);
////    echo count($key);
////    exit;
//    foreach ($arr as $k => $v){
//
//    }
//}

//二维数组变成逗号连接的字符串
function two_dimension_array_to_string($array,$field){
    if (is_array($array)) {
        $string='';
        foreach ($array as $key => $val) {
            $string=$string.','.$val[$field];
        }
    }
    $string=substr($string,1);
    return $string;
}

//获取ip
function getIp()
{

    if(!empty($_SERVER["HTTP_CLIENT_IP"]))
    {
        $cip = $_SERVER["HTTP_CLIENT_IP"];
    }
    else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else if(!empty($_SERVER["REMOTE_ADDR"]))
    {
        $cip = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
        $cip = '';
    }
    preg_match("/[\d\.]{7,15}/", $cip, $cips);
    $cip = isset($cips[0]) ? $cips[0] : 'unknown';
    unset($cips);

    return $cip;
}

//过滤回调函数
function  filterFunction($arr){
    if($arr === '' || $arr === null){
        return false;
    }
    return true;
}
/**
 * 客户端签名
 * @param  array $data 签名数组 并验证
 */
function getSignForApi(Array $data){
    $data=array_filter($data,'filterFunction');
    ksort($data);
    $str='';
    foreach($data as $key => $val){
        if($key!='sign'){
            $str.=$val;
        }
    }
    //获取签名key
    $str.=config('config.key');
//    writeLog($str,'sign.log','string');
    $sign=md5($str);
//    writeLog($sign,'sign.log','$sign');
    if($sign!=$data['sign']){
        return false;
    }else{
        return true;
    }
//    p(config('config.key'));

}



/**
 * 随机字符
 * @param number $length 长度
 * @param string $type 类型
 * @param number $convert 转换大小写
 * @return string
 */
    function random($length=6, $type='string', $convert=0){
    $config = array(
        'number'=>'1234567890',
        'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
        'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
    );

    if(!isset($config[$type])) $type = 'string';
    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) -1;
    for($i = 0; $i < $length; $i++){
        $code .= $string{mt_rand(0, $strlen)};
    }
    if(!empty($convert)){
        $code = ($convert > 0)? strtoupper($code) : strtolower($code);
    }
    return $code;
}



// 无限极分类
function subtree($arr, $id = 0,&$number=0)
{

//    $subs = array(); // 子孙数组
    foreach ($arr as $v) {
        if ($v['DistributorId'] == $id) {

            $number += $v['Coffer']+$v['MatchScore'];
//            $subs[] = $v;
//            $subs = array_merge($subs, subtree($arr, $v['UserID']));


            subtree($arr, $v['UserID'],$number);

        }
    }
    return $number;
}
// 查看无限级是否存在某个值 （所有用户，父级id，被确认id）

function treeHave($arr, $id ,$haveUserID)
{
//    p($arr);
//    $subs = array(); // 子孙数组
    foreach ($arr as $v) {

        if ($v['DistributorId'] == $id) {
//            p($v['UserID']);
//            p($haveUserID);
            if($v['UserID']==$haveUserID){
                return true;

            }else{
                $status=treeHave($arr, $v['UserID'],$haveUserID);
                if($status==true){
                    break;
                }
            }

        }
    }
    if(!isset($status)){
        $status=false;
    }
//    var_dump($status);
//    exit;
    return $status;
}
