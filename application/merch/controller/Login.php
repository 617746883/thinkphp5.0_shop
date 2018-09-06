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
        $this->assign(['shopset'=>$shopset,'copyright'=>$copyright,'referurl'=>$referurl]);
        return $this->fetch();
    }

    public function checklogin()
    {
    	$post = request()->post();
    	if (empty($post)) {
			$this->redirect('merch/login/index');
		}

		$data = array(
			'lastvisit' => time(),
			'lastip' => request()->ip(),
		);
		if(empty($post['username'])) {
			$this->error('账号不能为空！','merch/login/index');
		} else if(empty($post['pwd'])) {
			$this->error('密码不能为空！','merch/login/index');
		}

		$account = db('shop_store_account')->where('username','eq',$post['username'])->find();
		if (empty($account)) {
			$this->error('该账号不存在，请重试！','merch/login/index');
		}
		$merch = Db::name('shop_store')->where('id',$account['merchid'])->find();
		if(empty($merch)) {
			$this->error('该商户不存在！','merch/login/index');
		}
		if($merch['accounttime'] <= time()) {
			$this->error('该商户服务已到期！','merch/login/index');
		}
		$password = md5(trim($post['pwd']) . $account['salt']);
		if ($password == $account['pwd']) {
			Db::name('shop_store_account')->where(array('id'=>$account['id']))->data($admin_info);
			unset($account['pwd'],$account['salt']);
			Session::set('merch',$account);
			$this->success('登陆成功！','merch/index/index');
		} else {
			$this->error('密码错误，请重试！','merch/login/index');
		}
			
	}

  
}