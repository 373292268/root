<?php
/**
 * Created by PhpStorm.
 * User: cxj
 * Date: 2020/3/14
 * Time: 17:33
 */

namespace app\Manage\Controller;


use app\Manage\model\acc_acinfo;
use app\Manage\model\club;
use app\Manage\model\web;
use Think\Db;
use think\Request;

class Consume extends Common
{
    /**
     * 公告列表
     * 公告
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function ScrollNotice(){
        $user_list=web::getScrollNotice();
        $page = $user_list->render();
        $data=$user_list->toArray();
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch('ScrollNotice');
    }

    /**
     * 白名单
     * 平台
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function WhiteList(){
        $user_list=acc_acinfo::getWhiteList();
        $page = $user_list->render();
        $data=$user_list->toArray();
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch('WhiteList');
    }
    /**
     * 公告详情
     * 公告
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function NoticeInfo(){

        $NewsID = input('NewsID/d');
        if(empty($NewsID)){
            $this->error('失败');
        }
        $noticeInfo=web::table('news')->where(['NewsID'=>$NewsID])->field('NewsID,Subject,OnTop,Body')->findOrEmpty()->toArray();
//        p($noticeInfo);
//        exit;
//        $user_list=web::getNoticeInfo();
//        $page = $user_list->render();
//        $data=$user_list->toArray();
        $this->assign('data',$noticeInfo);
//        $this->assign('page',$page);
        return $this->fetch('NoticeInfo');
    }
    /**
     * 修改公告
     * 公告
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function editNotice(){
        $title=input('title/s');
        $body=input('body/s');
        $status=input('status/s');
        $NewsID=input('NewsID/d');
        if($status=='open'){
            $status=1;
        }else{
            $status=0;
        }
        $noticeInfo=web::table('news')->where(['NewsID'=>$NewsID])->field('NewsID,Subject,OnTop,Body')->update(['Subject'=>$title,'OnTop'=>$status,'Body'=>$body]);
        if($noticeInfo==1){
            $this->success('成功');
        }else{
            $this->error('失败');
        }

    }
    public function indexNotice(){
        return $this->fetch('indexNotice');
    }
    public function RechargeDiamond(){
        return $this->fetch('RechargeDiamond');
    }
    public function indexWhite(){
        return $this->fetch('indexWhite');
    }
    /**
     * 新增公告
     * 公告
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function addNotice(){
        $title=input('title/s');
        $body=input('body/s');
        $status=input('status/s');
//        echo $status;
//        exit;
        if(empty($title)||empty($body)||empty($status)){
            $this->error('参数为空');
        }
        if($status=='open'){
            $status=1;
        }else{
            $status=0;
        }
        $noticeInfo=web::table('news')->insert(['Subject'=>$title,'OnTop'=>$status,'Body'=>$body]);
//        p($noticeInfo);
//        exit;
        if($noticeInfo==1){
            $this->success('成功');
        }else{
            $this->error('失败');
        }

    }

    /**
     * 消息列表
     * 消息
     *
     * $ID     信息id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function PublishMessage(){
        $user_list=web::getMessage();
        $page = $user_list->render();
        $data=$user_list->toArray();
        $this->assign('list',$data['data']);
        $this->assign('page',$page);
        return $this->fetch('PublishMessage');
    }
    /**
     * 信息详情
     * 信息
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function MessageInfo(){

        $ID = input('ID/d');
        if(empty($ID)){
            $this->error('失败');
        }
        $messageInfo=web::table('ads')->where(['ID'=>$ID])->field('ID,Title,ResourceURL,Remark')->findOrEmpty()->toArray();
//        p($noticeInfo);
//        exit;
//        $user_list=web::getNoticeInfo();
//        $page = $user_list->render();
//        $data=$user_list->toArray();
        $this->assign('data',$messageInfo);
//        $this->assign('page',$page);
        return $this->fetch('MessageInfo');
    }
    public function indexMessage(){
        return $this->fetch('indexMessage');
    }

    /**
     * 修改消息
     * 消息
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function editMessage(){



        $title=input('title/s');
        $body=input('Remark/s');
        $ID=input('ID/d');
        if(empty($title)||empty($body)||empty($ID)){
            $this->error('参数为空');
        }

//        如果上传图片不为空,认为有上传图片
        if(!empty(request()->file())){
//              获取上传图片数据
            $files=request()->file('img');
//              获取原本的图片路径
            $oldImg=web::table('ads')->where(['ID'=>$ID])->field('ResourceURL')->findOrEmpty()->toArray();
//              然后对新图片进行处理
            $deal_img=self::deal_img($files);
//              如果图片处理成功，就删除老图片
            if($deal_img['code']==200){
//                更新数据库，如果数据库更新成功，且存在旧图片，则删除旧图片
                $noticeInfo=web::table('ads')->where(['ID'=>$ID])->update(['Title'=>$title,'ResourceURL'=>$deal_img['msg'],'Remark'=>$body]);
                if($noticeInfo==1){
//              如果数据库中存有老图片，则删除老图片
                    if(!empty($oldImg['ResourceURL'])){
                        unset($info);
//                        p($info);
//                        exit;
                        self::delete_img($oldImg['ResourceURL']);
                    }
                }else{
                    unset($info);
                    self::delete_img($deal_img['msg']);
                    $this->error('更新失败');
                }

            }else{//图片处理失败，就返回失败原因
                $this->error($deal_img['msg']);
            }
        }else{//没有图片上传
            $noticeInfo=web::table('ads')->where(['ID'=>$ID])->update(['Title'=>$title,'Remark'=>$body]);
        }



        if($noticeInfo==1){
            $this->success('更新成功');
        }else{
            $this->error('更新失败');
        }

    }
    /**
     * 新增消息
     * 消息
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function addMessage(){
        $title=input('title/s');
        $body=input('Remark/s');
        if(empty($title)||empty($body)){
            $this->error('参数为空');
        }
//        如果上传图片不为空,认为有上传图片
        if(!empty(request()->file())){
//              获取上传图片数据
            $files=request()->file('img');
//              然后对新图片进行处理
            $deal_img=self::deal_img($files);
//            p($deal_img);
//            exit;
//              如果图片处理成功，就新增数据库
            if($deal_img['code']==200){
//                更新数据库，如果数据库更新成功，且存在旧图片，则删除旧图片
                $noticeInfo=web::table('ads')->insert(['Title'=>$title,'ResourceURL'=>$deal_img['msg'],'Remark'=>$body]);
                if($noticeInfo!=1){
                    unset($info);
                    self::delete_img($deal_img['msg']);
                    $this->error('更新失败');
                }
            }else{//图片处理失败，就返回失败原因
                $this->error($deal_img['msg']);
            }
        }else{//没有图片上传
            $noticeInfo=web::table('ads')->insert(['Title'=>$title,'Remark'=>$body]);
        }



        if($noticeInfo==1){
            $this->success('更新成功');
        }else{
            $this->error('更新失败');
        }

    }
    /**
     * 删除公告
     * 公告
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function deleteNotice(){
        $NewsID=input('NewsID/d');
        if(empty($NewsID)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
        Db::startTrans();
        $deleteResult=web::table('news')->where(['NewsID'=>$NewsID])->delete();

        if($deleteResult==1){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>'删除成功']);
        }else{
            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'删除失败']);
        }

    }
    /**
     * 删除消息
     * 消息
     *
     * $ID      消息id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function deleteMessage(){
        $ID=input('ID/d');
        if(empty($ID)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
        Db::startTrans();
        $deleteResult=web::table('ads')->where(['ID'=>$ID])->delete();

        if($deleteResult==1){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>'删除成功']);
        }else{
            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'删除失败']);
        }
    }

    /**
     * 删除白名单
     * 平台
     *
     * $NewsID      公告id
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function deleteWhiteList(){
        $ID=input('post.ID/d');
        if(empty($ID)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
        Db::startTrans();
        $deleteResult=acc_acinfo::conn_accounts()->table('accountswhitelist')->where([['ID','=',$ID]])->delete();

        if($deleteResult==1){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>'删除成功']);
        }else{
            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'删除失败']);
        }

    }
    /**
     * 获取用户钻石数量
     * ajax
     *
     * $GameID      用户游戏id
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function search_info(){
        $GameID=input('GameID/d');
        if(empty($GameID)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
//        Db::startTrans();
        $userInfo=acc_acinfo::conn_accounts()
            ->table('accountsinfo')
            ->alias('ai')
            ->leftJoin('RYTreasureDBLink.RYTreasureDB.dbo.UserRoomCard urc','urc.UserID=ai.UserID')
            ->where(['ai.GameID'=>$GameID])
            ->field('ai.UserID,ai.NickName,urc.RoomCard,ai.AgentID')
            ->findOrEmpty();

//        writeLog($userInfo,'search_info.log');
//        exit;
        if($userInfo){
//            Db::commit();//执行
            return json(['code'=>200,'msg'=>'获取成功','data'=>$userInfo]);
        }else{
//            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'用户不存在']);
        }
    }
    /**
     * 充值钻石
     * ajax
     *
     * $UserID      用户id
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function RechargeDiamondPay(){
        $UserID=input('UserID/d');
        $Number=input('Number/d');
//        writeLog(input());
        if($Number>999999){
            return json(['code'=>404,'msg'=>'数量过大']);
        }
        if(empty($UserID)||empty($Number)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
//        查询充值前数据
        $UserBeforeRoomCard=acc_acinfo::conn_accounts('rytreasuredb')
            ->table('userroomcard')
            ->where(['UserID'=>$UserID])
            ->field('RoomCard')
            ->findOrEmpty();
        Db::startTrans();
        if(empty($UserBeforeRoomCard)){
            return json(['code'=>404,'msg'=>'用户不存在']);
        }
        $result=acc_acinfo::conn_accounts('rytreasuredb')
            ->table('userroomcard')
            ->where(['UserID'=>$UserID])
            ->setInc('RoomCard',$Number);
        $UserRoomCard=acc_acinfo::conn_accounts('rytreasuredb')
            ->table('userroomcard')
            ->where(['UserID'=>$UserID])
            ->field('RoomCard')
            ->findOrEmpty();
//        writeLog($userInfo,'search_info.log');
//        exit;
        $add_data=[
            'SourceUserID'=>$UserID,
            'SBeforeCard'=>$UserBeforeRoomCard['RoomCard'],
            'RoomCard'=>$Number,
            'TypeID'=>1,
            'ClientIP'=>getIp(),
            'Remarks'=>'管理员充值钻石',
        ];
        $add_result=acc_acinfo::conn_accounts('ryrecorddb')
            ->table('recordroomcard')
            ->insert($add_data);
        if($result&&$add_result){
            Db::commit();//执行
            return json(['code'=>200,'msg'=>'充值成功','data'=>$UserRoomCard['RoomCard']]);
        }else{
            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'充值失败']);
        }
    }

    //    上传图片
    private function deal_img($files){
//        p($files);
//        exit;
        $info = $files->validate(['size'=>2000000,'ext'=>'jpg,png,gif'])
            ->move( '../public/static/Upload/Ads',date('YmdHis',time()));
//        p($info);
//        exit;
        if($info){
            $imgUrl='/static/Upload/Ads/'.$info->getSaveName();
            return ['code'=>200,'msg'=>$imgUrl];
//            p($_SERVER['HTTP_HOST']);
//            exit;
//            return Request::domain().
            // 输出 jpg
//             p($info->getExtension());
//             // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
//             p($info->getSaveName());
//             // 输出 42a79759f284b767dfcb2a0197904287.jpg
//             p($info->getFilename());
//             exit;
        }else{
            return ['code'=>500,'msg'=>$files->getError()];
        }
    }
    private function delete_img($fileUrl){
//        $fileUrl='http://www.league.com/static/Upload/Ads/20200317155946.jpg';
        $haveHttp=strstr($fileUrl,'http://');
        if($haveHttp!=false){
            $arr=parse_url($fileUrl);
            $imgUrl=$arr['path'];

            unlink('../public'.$imgUrl);
        }else{
            unlink('../public'.$fileUrl);
        }
    }
    public function SetJurisdiction(){
        return $this->fetch('SetJurisdiction');
    }
    public function SetTeaHouseCount(){
        $ConfigList=acc_acinfo::conn_accounts()
            ->table('systemstatusinfo')
            ->where(['Status'=>1])
            ->field('StatusValue,StatusTip,StatusString,StatusDescription,StatusName')
            ->select();
        $this->assign('data',$ConfigList);
        return $this->fetch('SetTeaHouseCount');
    }
    /**
     * 设置楼主
     * ajax
     *
     * $UserID      用户id
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function SetTeaHouse(){
        $UserID=input('UserID/d');
        $status=input('status/s');

        if(empty($UserID)||empty($status)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }

//        判断用户是否存在
        $UserInfo=acc_acinfo::conn_accounts()
            ->table('accountsinfo')
            ->where(['UserID'=>$UserID])
            ->field('AgentID,NickName')
            ->findOrEmpty();
        Db::startTrans();
        if(empty($UserInfo)){
            return json(['code'=>404,'msg'=>'用户不存在']);
        }
        if($status=='set'){
            if($UserInfo['AgentID']>0){
                return json(['code'=>404,'msg'=>'该用户已是楼主']);
            }
            $add_data=[
                'UserID'=>$UserID,
                'Compellation'=>$UserInfo['NickName'],
            ];
            $add_result=acc_acinfo::conn_accounts()
                ->table('accountsagent')
                ->insertGetId($add_data);
            $update_result=acc_acinfo::conn_accounts()
                ->table('accountsinfo')
                ->where(['UserID'=>$UserID])
                ->update(['AgentID'=>$add_result]);
            $msg='已将该用户设置为楼主';
        }elseif($status=='cancel'){
            if($UserInfo['AgentID']==0){
                return json(['code'=>404,'msg'=>'该用户不是楼主']);
            }
            $add_result=acc_acinfo::conn_accounts()
                ->table('accountsagent')
                ->where(['UserID'=>$UserID])
                ->delete();
            $update_result=acc_acinfo::conn_accounts()
                ->table('accountsinfo')
                ->where(['UserID'=>$UserID])
                ->update(['AgentID'=>0]);
            $msg='已将该用户取消楼主权限';
        }


        if($add_result&&$update_result){
            Db::commit();//执行

            return json(['code'=>200,'msg'=>$msg]);
        }else{
            Db::rollback();//回滚
            return json(['code'=>400,'msg'=>'设置失败']);
        }
    }
    /**
     * 修改配置
     * ajax
     *
     * $name        字段名字
     * $value       字段值
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function editConfig(){
        $name=input('name/s');
        $value=input('value/s');
        if(empty($name)||!isset($value)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
        $update_result=acc_acinfo::conn_accounts()
            ->table('systemstatusinfo')
            ->where(['StatusName'=>$name])
            ->update(['StatusValue'=>$value]);
        if($update_result==1){
            return json(['code'=>200,'msg'=>'修改成功']);
        }else{
            return json(['code'=>400,'msg'=>'修改失败']);
        }
    }

    /**
     * 添加白名单列表
     * ajax
     *
     * $name        字段名字
     * $value       字段值
     *
     * @return $status int  状态码
     * @return $msg string  错误信息
     * @return $data array  返回数据
     */
    public function addWhiteList(){
        $UserID=input('post.UserID/d');
        $GameID=input('post.GameID/d');
        if(empty($UserID)||empty($GameID)){
            return json(['code'=>404,'msg'=>'参数为空']);
        }
        $UserInfo=acc_acinfo::conn_accounts()
            ->table('accountsinfo')
            ->where([
                ['UserID','=',$UserID],
                ['GameID','=',$GameID],
            ])
            ->field('NickName')
            ->findOrEmpty();

        if(empty($UserInfo)){
            return json(['code'=>404,'msg'=>'用户不存在']);
        }
        $listInfo=acc_acinfo::conn_accounts()
            ->table('accountswhitelist')
            ->where([
                ['UserID','=',$UserID],
            ])
            ->field('NickName')
            ->findOrEmpty();
        if($listInfo){
            return json(['code'=>400,'msg'=>'该用户已在白名单']);
        }
        $addResult=acc_acinfo::conn_accounts()
            ->table('accountswhitelist')
            ->insert(['UserID'=>$UserID,'GameID'=>$GameID,'NickName'=>$UserInfo['NickName']]);

        if($addResult){
            return json(['code'=>200,'msg'=>'添加成功']);
        }else{
            return json(['code'=>400,'msg'=>'修改失败']);
        }
    }



}