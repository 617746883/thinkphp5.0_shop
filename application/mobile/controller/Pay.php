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
		$order = Db::name('shop_auction_record')->where('id = 14')->find();
		$params = array( );
		$params["tid"] = $order["ordersn"];
		$params["user"] = 1;
		$params['product_id'] = 14;
		$params["fee"] = $order["price"];
		$params["title"] = '参与竞拍' . " 单号:" . $order["ordersn"];
		$wechat = model('payment')->wechat_build($params, 'web', 3);
		dump($wechat);
		return $this->fetch('');
	}

	public function jspaipay($params)
	{
		$shopset = model('common')->getSysset('shop');
		$wechat = model('payment')->wechat_build($params, 'wechat', 3);
		$jsApiParameters = json_encode($wechat);
		$this->assign('jsApiParameters',$jsApiParameters);
		$this->assign('params',$params);
		$this->assign('shopset',$shopset);
		return $this->fetch('');
	}
	
}