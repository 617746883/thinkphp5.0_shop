<?php
/**
 * H5支付测试
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\mobile\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Pay extends Controller
{
	public function h5pay()
	{
		$shopset = model('common')->getSysset('shop');
		$order = Db::name('shop_order')->where('id = 1')->find();
		$params = array( );
		$params["tid"] = 'SH20181211110412625858';
		$params["user"] = 1;
		$params['product_id'] = 1;
		$params["fee"] = $order["price"];
		$params["title"] = '订单' . " 单号:" . $order["ordersn"];
		$wechat = model('payment')->wechat_build($params, 'web', 3, 'web');
		if (!is_array($wechat)) {
			$this->result(0,$wechat);
		}
		$this->assign('wechat',$wechat);
		$this->assign('params',$params);
		$this->assign('shopset',$shopset);
		return $this->fetch('');
	}

	public function jspaipay($params)
	{
		$shopset = model('common')->getSysset('shop');
		$wechat = model('payment')->wechat_build($params, 'wechat', 3, 'wechat');
		if (!is_array($wechat)) {
			$this->result(0,$wechat);
		}
		$jsApiParameters = json_encode($wechat);
		$this->assign('jsApiParameters',$jsApiParameters);
		$this->assign('params',$params);
		$this->assign('shopset',$shopset);
		return $this->fetch('');
	}

	public function aliwappay()
	{
		$shopset = model('common')->getSysset('shop');
		$order = Db::name('shop_order')->where('id = 1')->find();
		$params = array( );
		$params["tid"] = 'SH20181211110412625858';
		$params["user"] = 1;
		$params['product_id'] = 1;
		$params["fee"] = $order["price"];
		$params["title"] = '订单' . " 单号:" . $order["ordersn"];
		$alipay = model('payment')->alipay_build($params, 'iOS', 0, getHttpHost() . '/public/dist/order','web');
		if (empty($alipay)) {
			$this->result(0,'参数错误');
		}
		$this->assign('alipay',$alipay);
		$this->assign('params',$params);
		$this->assign('shopset',$shopset);
		return $this->fetch('');
	}
	
}