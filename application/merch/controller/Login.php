<?php
namespace app\merch\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Captcha;
use think\Cache;
use think\Config;
use think\Session;
class Login extends Controller
{
	public $merchid = 0;
	public $merch = array();

	public function __construct() {
        parent::__construct();
        if(Session::has('merch')) {
        	$merch = Session::get('merch');      
        	$this->merch = $merch;
        	$this->merchid = $merch['id'];
        }      
    }

    public function index()
    {
    	$referurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : url("merch/index/index");
    	if (strstr($referurl, 'loginout') || strstr($referurl, 'cleancache') || strstr($referurl, 'login')) 
    	{
	        $referurl = url('merch/index/index');
	    }

    	if($this->merch && $this->merch_id > 0)
    	{
    		$this->redirect($referurl);
        }
    	$copyright = model('common')->getCopyright();
        $shopset = model('common')->getSysset('shop');
        $set = model('common')->getPluginset('merch');
        $this->assign(['shopset'=>$shopset,'copyright'=>$copyright,'referurl'=>$referurl,'set'=>$set]);
        return $this->fetch();
    }

    public function checklogin()
    {
    	$merch_data = model('common')->getPluginset('merch');
        if ($merch_data['is_openmerch']) {
            $is_openmerch = 1;
        } else {
            $is_openmerch = 0;
        }
        if(empty($is_openmerch)) {
            show_json(0,'未开启多商户');
        }
    	$post = request()->post();
    	if (empty($post)) {
			$this->redirect('merch/login/index');
		}

		$data = array(
			'lastvisit' => time(),
			'lastip' => request()->ip(),
		);
		if(empty($post['username'])) {
			show_json(0,'账号不能为空！');
		} else if(empty($post['pwd'])) {
			show_json(0,'密码不能为空！');
		}

		$account = Db::name('shop_merch_account')->where('username','eq',$post['username'])->find();
		if (empty($account)) {
			show_json(0,'该账号不存在，请重试！');
		}
		$merch = Db::name('shop_merch')->where('id',$account['merchid'])->find();
		if(empty($merch)) {
			show_json(0,'该商户不存在！');
		}
		if($merch['accounttime'] <= time()) {
			show_json(0,'该商户服务已到期！');
		}
		if($merch['status'] != 1 || $merch['deleted'] != 0) {
			show_json(0,'该商户服务未开通！');
		}
		$password = md5(trim($post['pwd']) . $account['salt']);
		if ($password == $account['pwd']) {
			Db::name('shop_merch_account')->where(array('id'=>$account['id']))->data($admin_info);
			unset($account['pwd'],$account['salt']);
			Session::set('account',$account);
			show_json(1,array('url'=>url('merch/index/index')));
		} else {
			show_json(0,'密码错误，请重试！');
		}			
	}

	public function loginout()
	{
        $admin = session('account');
		session_unset();
        session_destroy();
		$this->success('退出成功!',url('merch/login/index'));
	}
  
}