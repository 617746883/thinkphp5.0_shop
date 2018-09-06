<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Verifygoods extends \think\Model
{
	/**
     *
     * @param type $orderid
     */
	public static function createverifygoods($orderid)
	{
		$verifygoods = Db::name('shop_verifygoods')->where('orderid',$orderid)->find();

		if (!empty($verifygoods)) {
			return false;
		}

		$ordergoods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id')->join('shop_order o','og.orderid = o.id')->where('og.orderid =' . $orderid . ' and g.type = 5')->field('o.mid,o.id as orderid,og.id as ordergoodsid,g.verifygoodsdays,g.verifygoodsnum,g.verifygoodslimittype,g.verifygoodslimitdate,og.total')->select();
		$time = time();

		foreach ($ordergoods as $ordergood) {
			$total = intval($ordergood['total']);
			$i = 0;

			while ($i < $total) {
				$data = array('mid' => $ordergood['mid'], 'orderid' => $ordergood['orderid'], 'ordergoodsid' => $ordergood['ordergoodsid'], 'starttime' => $time, 'limittype' => intval($ordergood['verifygoodslimittype']), 'limitdate' => intval($ordergood['verifygoodslimitdate']), 'limitdays' => intval($ordergood['verifygoodsdays']), 'limitnum' => intval($ordergood['verifygoodsnum']), 'used' => 0, 'invalid' => 0);
				Db::name('shop_verifygoods')->insert($data);
				++$i;
			}
		}

		return true;
	}
}
