<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Virtual extends \think\Model
{
	public static function pay_befo($order) 
	{
		$goods = Db::name('shop_order_goods')->where('orderid',$order['id'])->field('id,goodsid,total,realprice')->find();
		$g = Db::name('shop_goods')->where('id',$goods['goodsid'])->field('id,credit,sales,salesreal')->find();
		$virtual_data = Db::name('shop_virtual_data')->where('typeid',$order['virtual'])->where('mid',0)->where('merchid',$order['merchid'])->field('id,typeid,fields')->order('id','asc')->limit($goods['total'])->select();
		$type = Db::name('shop_virtual_type')->where('id',$order['virtual'])->where('merchid',$order['merchid'])->field('fields')->find();
		$fields = iunserializer($type['fields'], true);
		$virtual_info = array();
		$virtual_str = array();
		foreach ($virtual_data as $vd ) {
			$virtual_info[] = $vd['fields'];
			$strs = array();
			$vddatas = iunserializer($vd['fields']);
			foreach ($vddatas as $vk => $vv ) 
			{
				$strs[] = $fields[$vk] . ': ' . $vv;
			}
			$virtual_str[] = implode(' ', $strs);
			Db::name('shop_virtual_data')->where('id',$vd['id'])->update(array('mid' => $order['mid'], 'orderid' => $order['id'], 'ordersn' => $order['ordersn'], 'price' => round($goods['realprice'] / $goods['total'], 2), 'usetime' => time()));
			Db::name('shop_virtual_type')->where('id',$vd['typeid'])->setInc('usedata');
			self::updateStock($vd['typeid']);
		}
		$virtual_str = implode("\n", $virtual_str);
		$virtual_info = '[' . implode(',', $virtual_info) . ']';
		$time = time();
		Db::name('shop_order')->where('id',$order['id'])->update(array('virtual_info' => $virtual_info, 'virtual_str' => $virtual_str, 'sendtime' => $time));
		return true;
	}

	public static function updateStock($typeid = 0) 
	{
		$goodsids = array();
		$goods = Db::name('shop_goods')->where('type',3)->where('virtual',$typeid)->field('id')->find();
		foreach ($goods as $g ) 
		{
			$goodsids[] = $g['id'];
		}
		$alloptions = Db::name('shop_goods_option')->where('virtual',$typeid)->field('id, goodsid')->select();
		foreach ($alloptions as $opt ) 
		{
			if (!(in_array($opt['goodsid'], $goodsids))) 
			{
				$goodsids[] = $opt['goodsid'];
			}
		}
		foreach ($goodsids as $gid ) 
		{
			self::updateGoodsStock($gid);
		}
	}

	public static function updateGoodsStock($id = 0) 
	{
		$goods = Db::name('shop_goods')->where('id',$id)->where('type',3)->field('`virtual`,merchid')->find();
		if (empty($goods)) 
		{
			return;
		}
		$merchid = $goods['merchid'];
		$stock = 0;
		if (!(empty($goods['virtual']))) 
		{
			$stock = Db::name('shop_virtual_data')->where('typeid',$goods['virtual'])->where('merchid',$merchid)->where('mid',0)->count();
		}
		else 
		{
			$virtuals = array();
			$alloptions = Db::name('shop_goods_option')->where('goodsid',$id)->field('id, `virtual`')->select();
			foreach ($alloptions as $opt ) 
			{
				if (empty($opt['virtual'])) 
				{
					continue;
				}
				$c = Db::name('shop_virtual_data')->where('typeid',$opt['virtual'])->where('merchid',$merchid)->where('mid',0)->count();
				Db::name('shop_goods_option')->where('id',$opt['id'])->setField('stock',$c);
				if (!(in_array($opt['virtual'], $virtuals))) 
				{
					$virtuals[] = $opt['virtual'];
					$stock += $c;
				}
			}
		}
		Db::name('shop_goods')->where('id',$id)->setField('total',$stock);
	}

	public static function pay($order) 
	{
		$goods = Db::name('shop_order_goods')->where('orderid',$order['id'])->field('id,goodsid,total,realprice')->find();
		$g = Db::name('shop_goods')->where('id',$goods['goodsid'])->field('id,credit,sales,salesreal')->find();
		$virtual_data = Db::name('shop_virtual_data')->where('typeid',$order['virtual'])->where('mid',$order['mid'])->where('merchid',$order['merchid'])->order('id','asc')->field('id,typeid,fields')->select();
		$type = Db::name('shop_virtual_type')->where('id',$order['virtual'])->where('merchid',$order['merchid'])->field('fields')->find();
		$time = time();
		Db::name('shop_order')->where('id',$order['id'])->update(array('status' => '3', 'paytime' => $time, 'sendtime' => $time, 'finishtime' => $time));

		$credits = 0;
		$gcredit = trim($g['credit']);
		if (!(empty($gcredit))) 
		{
			if (strexists($gcredit, '%')) 
			{
				$credits += intval((floatval(str_replace('%', '', $gcredit)) / 100) * $goods['realprice']);
			}
			else 
			{
				$credits += intval($g['credit']) * $goods['total'];
			}
		}
		if (0 < $credits) 
		{
			$shopset = model('common')->getSysset('shop');
			model('member')->setCredit($order['mid'], 'credit1', $credits, array(0, $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']));
		}
		$salesreal = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id = og.orderid','left')->where('og.goodsid=' . $g['id'] . ' and o.status>=1')->sum('og.total');
		Db::name('shop_goods')->where('id',$g['id'])->setField('salesreal',$salesreal);
		model('order')->fullback($order['id']);
		model('member')->upgradeLevel($order['mid'], $order['id']);
		model('notice')->sendOrderMessage($order['id']);
		model('order')->setGiveBalance($order['id'], 1);
		model('coupon')->sendcouponsbytask($order['id']);
		if (!(empty($order['couponid']))) 
		{
			model('coupon')->backConsumeCoupon($order['id']);
		}
		return true;
	}

}