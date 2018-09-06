<?php
namespace app\apiv1\controller;
use think\Controller;
use think\Db;
use think\Request;
// 指定允许其他域名访问  
header('Access-Control-Allow-Origin:*');  
// 响应类型  
header('Access-Control-Allow-Methods:*');  
// 响应头设置  
header('Access-Control-Allow-Headers:*');

class Base extends Controller
{
	/**
     * 允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post';

	/**
	 * 析构函数，初始化操作
	 * @param  [string]  $token [用户token]
	 * @return [type]           [description]
	 */
	public function _initialize()
	{		
	    $request = Request::instance();
    	$this->request = $request;
        // $this->init();    //检查资源类型
        $headerinfo = $request->header();
		$token = $this->request->header('token');
        $shopset = model('common')->getSysset();
        if($shopset['shop']['close'] == 1) {
            $this->result(0,'商城已关闭',array('closedetail'=>$shopset['shop']['closedetail'],'closeurl'=>$shopset['shop']['closeurl']));
        }
        $this->shopset = $shopset;
	}

	/**
     * 初始化方法
     * 检测请求类型，数据格式等操作
     */
    public function init()
    {
    	// 检测时间+_300秒内请求会异常
    	$time = $this->request->header('x-timestamp');
		if(($time > time()+300)  || ($time < time()-300)) {
			$this->result(0,'The requested time is incorrect');
		}
    	// 资源类型检测
        $ext = $this->request->ext();
        if ('' == $ext) {
            $this->type = $this->request->type(); // 自动检测资源类型
        } elseif (!preg_match('/\(' . $this->restTypeList . '\)$/i', $ext)) {
            $this->type = 'json';// 资源类型非法 则用默认资源类型访问
        } else {
            $this->type = $ext;
        }
        // 请求方式检测
        $method = strtolower($this->request->method());
        $this->method = $method;
        if (false === stripos($this->restMethodList, $method)) {
        	$this->result(0,'Method Not Allowed');
        }
    }

    public function getMemberId()
    {
        $token = $this->request->header('token');
        $tokencheck = model('Login')->checktoken($token);
        if ($tokencheck['code'] != 90001) {
            $this->result(3,$tokencheck['msg']);
        }
        return $tokencheck['data'];
    }

	public function _empty()
    {
        $this->result(0,'empty method!');
    }

}