<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Captcha;
use think\Cache;
use think\Config;
use think\Session;
class Login extends Controller
{
	public $admin_id = 0;
	public $admin = array();

	public function __construct() {
        parent::__construct(); 
        if(Session::has('admin')) {
        	$admin = Session::get('admin');      
        	$this->admin = $admin;
        	$this->admin_id = $admin['id'];
        }      
    }

    public function index()
    {
    	$referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url("admin/index/index");
    	if (strstr($referurl, 'loginout') || strstr($referurl, 'cleancache') || strstr($referurl, 'login')) {
	        $referurl = url('admin/index/index');
	    }
    	if($this->admin && $this->admin_id > 0) {
    		$this->redirect($referurl);exit;
        }
    	$copyright = model('common')->getCopyright();
        $shopset = model('common')->getSysset('shop');
        $this->assign(['shopset'=>$shopset,'copyright'=>$copyright,'referurl'=>$referurl]);
        return $this->fetch();
    }

    public function checklogin()
    {
    	$post = request()->post();
    	if (empty($post)) {
			show_json(0,'请输入账号密码');
		}		
		
		if (empty($post['username'])) {
			show_json(0,'账号不能为空！');
		} elseif (empty($post['password'])) {
			show_json(0,'密码不能为空！');
		}

		$record = Db::name('admin')->where('username','eq',$post['username'])->where('status',1)->find();
		if (empty($record)) {
			show_json(0,'该账号不存在或已被封禁，请重试！');
		}
        if (!empty($record['starttime']) && $record['starttime'] > time()) {
            show_json(0,'您的账号正在审核或是已经被系统禁止，请联系网站管理员解决?');
        }
        if (!empty($record['endtime']) && $record['endtime'] < time()) {
            show_json(0,'您的账号有效期限已过，请联系网站管理员解决！');
        }
        $password = md5(config('AUTH_CODE').$post['password'].$record['salt']);
        if ($password == $record['password']) {
            if(empty($record['joindate']) || empty($record['joinip'])) {
                $admin_info['joindate'] = time();
                $admin_info['joinip'] = request()->ip();
            }
            $admin_info['lastvisit'] = time();
            $admin_info['lastip'] = request()->ip();
            $admin_info['token'] = $this->request->token('__token__', 'sha1');
            Db::name('admin')->where(array('id'=>$record['id']))->update($admin_info);
            /*** 单点登录，记录token ***/
            $expire = intval(Config::get('web.token', 172800));
            Cache::set("admin_login_token" . $record['username'], $admin_info['token'], $expire);
            /*** END ***/ 
            $record['token'] = $admin_info['token'];
            unset($record['password'],$record['salt']);
            Session::set('admin',$record);
            Session::set('session_start_time',time());
            model('shop')->plog('admin.login', '管理员 : ' . $record['username'] . '悄悄地登陆了后台');
            show_json(1,array('url'=>$post['referurl']));
        } else {
            show_json(0,'密码错误，请重试！');
        }
	}
}