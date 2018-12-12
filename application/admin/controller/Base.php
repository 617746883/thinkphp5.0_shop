<?php
/**
 * 后台基类
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;
use think\Session;
use think\Env;
class Base extends Controller
{
    /**
     * 允许访问的请求类型
     * @var string
     */
    public $restMethodList = 'get|post|put|delete|patch|head|options';

    /**
	 * 析构函数，初始化操作
	 * @param  [string]  $token [用户token]
	 * @return [type]           [description]
	 */
	public function __construct()
	{
		parent::__construct(); 
        if(in_array(request()->action(),array('login','loginout')) || in_array(request()->controller(),array('Utility','Login'))){
            return true;
        } else {
            if(session('?admin')){
                $admin_info = session('admin');
                $token_key = "admin_login_token" . $admin_info['username'];
                $token = Cache::get($token_key, '');
                if ($token != $admin_info['token']) {
                    session_destroy();
                    Db::name('member')->where('id',$admin_info['id'])->setField('token','');
                    $this->error("您的账号已在其它地方登陆", url("admin/login/index"));
                } else {
                    $expire = intval(Env::get('web.token', 172800));
                    Cache::set($token_key, $admin_info['token'], $expire);
                }
                if ((time() - session('session_start_time')) > config('session')['expire']) {
                    session_destroy();//销毁缓存！
                    Db::name('member')->where('id',$admin_info['id'])->setField('token','');
                    $this->error('登录超时',url('admin/login/index'),1);
                } else {
                    session('session_start_time',time());
                    $this->assign('admin_info',$admin_info);
                }    
                // $this->checkAuth(); 
                $this->init();
                $this->assign('admin',$admin_info);
                return true;
            } else {
                $this->redirect('admin/login/index');
            }
        }
	}

	public function init()
    {
    	$copyright = model('common')->getCopyright(1);
        $system = model('system')->init(false);
        $sysmenus = model('system')->getMenu(true,0);
        $shopset = model('common')->getSysset();
        $shop_data = $shopset['shop'];
        $this->shopset = $shopset;
        $this->assign(['system'=>$system,'sysmenus'=>$sysmenus,'shopset'=>$shopset,'shop_data'=>$shop_data,'copyright'=>$copyright]);
    }

    public function checkAuth()
    {
        $request = Request::instance();
        $mod = strtolower($request->module());
        $ctl = strtolower($request->controller());
        $act = strtolower($request->action());
        $path = $mod . '/' . $ctl . '/' . $act;
        if($path == 'admin/index/index' || $path == 'admin/user/index' || $path == 'admin/system/loginout') {
            return true;
        }
        $admin = session('admin');
        $id = $admin['id'];
        if($id === 1){
            return true;
        }
        $auth=new \Auth\Auth();//权限认证Auth类
        if(!$auth->check($mod.'-'.$ctl.'-'.$act,$id)) {// 第一个参数是规则名称,第二个参数是用户ID
            if (Request::instance()->isAjax()) {
                show_json(0,'你没有权限');
            } else {
                $this->error('你没有权限',url('admin/index/index'));
            }            
        }
    }

    // public function _empty()
    // {
    //     $this->redirect(url('admin/index/error'));
    // }

}