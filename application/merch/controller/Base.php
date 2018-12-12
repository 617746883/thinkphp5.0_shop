<?php
/**
 * 后台基类
 *
 * @author SUL1SS <617746883@QQ.com>
*/

namespace app\merch\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;
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
        
        if(!session('?account')) {
            $this->redirect(url('merch/login/index'));
        }
        $account = session('account');
        $merch = Db::name('shop_merch')->alias('merch')->join('shop_merch_group grp','merch.groupid = grp.id','left')->join('shop_merch_category cate','merch.cateid = cate.id','left')->field('merch.*,grp.groupname,grp.goodschecked,grp.commissionchecked,grp.changepricechecked,grp.finishchecked,cate.catename')->where('merch.id = ' . $account['merchid'])->find();
        $this->account = $account;
        $this->merch = $merch;
		$this->init();        
        $copyright = model('common')->getCopyright(1,$merch['id']);        
        $system = model('system')->init($merch['id']);
        $sysmenus = model('system')->getMenu(true,$merch['id']);
        $this->assign(['frame_menus'=>$frame_menus,'account'=>$account,'merch'=>$merch,'copyright'=>$copyright,'system'=>$system,'sysmenus'=>$sysmenus]);
	}

	public function init()
    {
        $shopset = model('common')->getSysset();
        $request = Request::instance();
        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        $routes = explode('/', strtolower($request->module() . '/' . $request->controller() . '/' . $request->action()));
        $this->shopset = $shopset;
        $this->assign(['system'=>$system,'routes'=>$routes,'shopset'=>$shopset,'module'=>$module,'controller'=>$controller,'action'=>$action]);
    }

    public function _empty()
    {
        $this->redirect(url('merch/index/error'));
    }

    public function frame_menus()
    {
        $request = Request::instance();
        $module = strtolower($request->module());
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if ($controller == 'goods') {
            $merch = $this->merch;
            $totals = model('goods')->getTotals($merch['id']);
            $this->assign(['totals'=>$totals]);
        }
        $this->assign(['module'=>$module,'controller'=>$controller,'action'=>$action]);
        if ($controller == 'index') {
            return $this->fetch('/tabs');
        } else if ($controller == 'system') {
            $routes = explode('/', strtolower($request->module() . '/' . $request->controller() . '/' . $request->action()));
            $tabs = $routes[0] . (isset($routes[1]) ? '/' . $routes[1] : '') . '/tabs';
            return $this->fetch($tabs);
        } else {
            return $this->fetch($controller . '/tabs');
        }
    }

}