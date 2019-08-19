<?php
namespace app\mobile\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Queue;
header("Access-Control-Allow-Origin: http://localhost:8000"); # 跨域处理
header('Access-Control-Allow-Methods:POST,GET,OPTIONS'); // 允许请求的类型
header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with,token'); // 设置允许自定义请求头的字段

class Base extends Controller
{
	/**
     * 允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post|options';

	/**
	 * 析构函数，初始化操作
	 * @param  [string]  $token [用户token]
	 * @return [type]           [description]
	 */
	public function _initialize()
	{		
	    $request = Request::instance();
    	$this->request = $request;
        if(!is_mobile()) {
            $this->error('请在手机打开');
        }
        // $this->init();    //检查资源类型
        $headerinfo = $request->header();
        $shopset = model('common')->getSysset();
        if($shopset['shop']['close'] == 1) {
            $this->error('商城已关闭',$shopset['shop']['closeurl']);
        }
        $mod = strtolower($request->module());
        $ctl = strtolower($request->controller());
        $act = strtolower($request->action());
        $this->assign(['headerinfo'=>$headerinfo,'shopset'=>$shopset,'mod'=>$mod,'ctl'=>$ctl,'act'=>$act]);
	}

    public function setToken()
    {
        $token = trim(input('token'));
        if(!empty($token)) {
            session('token',$token);
        } 
        show_json(1);
    }

    public function getMemberId()
    {
        $token = '';
        if(session('?token')){
            $token = session('token');
        }
        $res = Db::name('member')->where('token', $token)->field('id,expirestime,token,status,isblack')->find(); 
        $tokencheck = array('code'=>90002,'msg'=>'token error!');  //token错误验证失败      
        if (!empty($res))
        {
            if($res['status'] != 1 || $res['isblack'] == 1)
            {
                $tokencheck = array('code'=>-90004,'msg'=>'您的账号不存在或是已经被系统禁止，请联系管理员解决?');
            }
            if ((time() - $res['expirestime']) > 604800) 
            {
                $tokencheck = array('code'=>90003,'msg'=>'身份信息已过期请重新登录!');  //token长时间未使用而过期，需重新登陆
            }
            $new_time_out = time() + 604800;//604800是七天
            if (Db::name('member')->where('id', $res['id'])->update(['expirestime'=>$new_time_out]));
            {
                $tokencheck = array('code'=>90001,'msg'=>'success','data'=>$res['id']);  //token验证成功，time_out刷新成功，可以获取接口信息
            }
        }
        if (empty($tokencheck) || $tokencheck['code'] != 90001) {
            echo '<script language="javascript">alert("请登录");window.webkit.messageHandlers.callApp.postMessage({link:"login"});window.webkit.messageHandlers.callApp.postMessage({link:"wxinpay?sign="});</script>';die;
        }
        return $tokencheck['data'];
    }

}