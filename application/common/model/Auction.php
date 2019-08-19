<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Auction extends \think\Model
{
	/**
	 * 支付成功
	 * @param $orderid [int]
	 * @return  [array]    $data  []
	 **/
	public function payResult($ordersn)
	{
		$order = Db::name('shop_auction_order')->where('ordersn',$ordersn)->find();
		if (0 < $order['status']) {
			return true;
		}

		$record['status'] = 1;
		$record['paytime'] = time();
		
		Db::name('shop_auction_order')->where('id',$order['id'])->update($record);
		self::sendAuctionMessage($order['id']);
		return true;
	}
}