<?php
/**
 * 工具
 * ============================================================================
 * * 版权所有 2018
 * @param Author: SU1NSS 2017-09-14 $
 */

namespace app\apiv1\controller;  
use think\Controller;
use think\Request;   
use think\Db; 
use think\File;
class Util extends Base
{
	/**
     * 发送手机注册验证码
     */
    public function send_sms_captcha()
    {
        $mobile = trim(input('mobile'));
        $type = trim(input('type'));
        if(empty($type))
        {
            $this->result(0,'发送失败');
        }
        if(!check_mobile($mobile)){
            $this->result(0,'手机号码格式有误');
        }
        $captcha = rand(1000,9999);
        $send = model('util')->send_sms_captcha($mobile,$captcha,$type);
        if($send['code'] != 1){
        	exit(json_encode(array('status'=>0, 'message'=>$send['msg'], 'data'=>$send['data'])));
        }
        $this->result(1,'发送成功，请注意查收',array('captcha'=>$captcha));
    }

    /**
     * 发送手机注册验证码
     */
    public function sms_captcha_verify()
    {
        $mobile = trim(input('mobile')); $captcha = trim(input('captcha')); $type = trim(input('type'));
        $res = model('common')->sms_captcha_verify($mobile, $captcha, $type);
        if($res['code'] != 1){
            exit(json_encode(array('status'=>0, 'message'=>$res['msg'])));
        }
        $this->result(1,'验证成功');
    }

    /**
     * 上传图片
     * return 路径
     */ 
    public function upload_img()
    {
        $target = input('target/s','');
        if(!empty($target)){
            $mid = $this->getMemberId();
        }
        $file = request()->file('file');
        if(empty($file))
        {
            exit(json_encode(array('code'=>0, 'msg'=>'请选择文件', 'data'=>'')));
        }      
        $config = config('UploadFile');
        $size = $config['imageFileSize'];
        $ext = $config['imageExts'];
        $info = $file->validate(['size'=>$size,'ext'=>$ext])->move(ROOT_PATH . '/public/attachment' . DS . "app/",true,false);
        if($info){
            // 成功上传后 获取上传信息    
            $getFilename = $info->getSaveName();
            $imagepath = "/public/attachment/app/" . $getFilename;
            if(!empty($target)){
                Db::name('member')->where('id',$mid)->setField('avatar',tomedia($imagepath));
            }
            $this->result(1,'success',tomedia($imagepath));
        }else{
            // 上传失败获取错误信息
            $this->result(0,'上传失败:'.$file->getError());
        }
    }

    /**
     * 支付设置
     */
    public function payset()
    {
        $set = model('common')->getSec();
        $sec = iunserializer($set['sec']);
        $data = model('common')->getSysset('pay');
        $app_wechat = array('success'=>1,'paytypename'=>'微信支付','paytype'=>1);
        $app_alipay = array('success'=>1,'paytypename'=>'支付宝支付','paytype'=>2);
        $credit = array('success'=>1,'paytypename'=>'余额支付','paytype'=>3);
        $cash = array('success'=>1,'paytypename'=>'货到付款','paytype'=>4);
        if(empty($data['app_wechat'])) {
            $app_wechat['success'] = 0;
        }
        if(empty($data['app_alipay'])) {
            $app_alipay['success'] = 0;
        }
        if(empty($data['credit'])) {
            $credit['success'] = 0;
        }
        if(empty($data['cash'])) {
            $cash['success'] = 0;
        }

        $payset = array($app_wechat,$app_alipay,$credit,$cash);
        $this->result(1,'success',$payset);
    }

    public function express()
    {
        $expresscom = trim(input('expresscom'));
        $express = trim(input('express'));
        $expresssn = trim(input('expresssn'));
        $expresssn = str_replace(' ', '', $expresssn);
        $result = model('util')->getExpressList($express, $expresssn);
        if(empty($result) || $result['status'] != 200) {
            $this->result(0,'物流单暂无结果');
        }
        $expressimg = 'https://cdn.kuaidi100.com/images/all/56/' . $express . '.png';
        $this->result(1,'success',array('expresscom'=>$expresscom,'express'=>$express,'expresssn'=>$expresssn,'expressimg'=>$expressimg,'state'=>$result['state'],'list'=>$result['list']));
    }

    public function getExpressList()
    {
        $data = Db::name('shop_express')->where('status=1')->order('displayorder','desc')->select(); //根据小区名称第一个汉字的首字母正序排序  
        $settlesRes = array();  
        $settlesRes = groupByInitials($data,'name'); 
        foreach ($settlesRes as $key => $val) {
            $settlesResarr[] = array('name'=>$key,'list'=>$val);
        }
        $this->result(1,'success',$settlesResarr);
    }

}