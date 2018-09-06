<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Coupon extends \think\Model
{
	public function sendcouponsbytask($orderid)
	{
		if (!is_array($orderid)) {
			$order = Db::name('shop_order')->where('id',$orderid)->where('status','>=',0)->field('id,mid,ordersn,createtime,status,finishtime,`virtual`')->find();
		}

		if (empty($order)) {
			return NULL;
		}

		$gosendtask = false;

		if ($order['status'] == 1) {
			$gosendtask = true;
			$sendpoint = 2;
		}
		else {
			if ($order['status'] == 3) {
				$gosendtask = true;
				$sendpoint = 1;
			}
		}

		if ($gosendtask) {
			$list = self::getOrderSendCoupons($orderid, $sendpoint, 1, $order['mid']);
			if (!empty($list) && (0 < count($list))) {
				$this->posterbylist($list, $order['mid'], 6);
			}
		}

		$list2 = self::getOrderSendCoupons($orderid, $sendpoint, 2, $order['mid']);
		if (!empty($list2) && (0 < count($list2))) {
			self::posterbylist($list2, $order['mid'], 6);
		}
	}

	public function getOrderSendCoupons($orderid, $sendpoint, $tasktype, $mid)
	{
		if ($sendpoint == 2) {
			$taskdata = Db::name('shop_coupon_taskdata')->where('status=0  and mid=' . $mid . ' and sendpoint=' . $sendpoint . ' and tasktype=' . $tasktype . ' and orderid=' . $orderid)->field('id,couponid,sendnum')->select();
		}
		else {
			Db::name('shop_coupon_taskdata')->where('status=0  and mid=' . $mid . ' and sendpoint=' . $sendpoint . ' and tasktype=' . $tasktype . ' and orderid=' . $orderid)->field('id,couponid,sendnum')->select();
		}

		return $taskdata;
	}

	public function posterbylist($list, $mid, $gettype)
	{
		$num = 0;
		$showkey = random(20);
		$data = model('common')->getPluginset('coupon');
		if (empty($data['showtemplate']) || ($data['showtemplate'] == 2)) {
			$url = url('sale/coupon/my/showcoupons3', array('key' => $showkey), true);
		}
		else {
			$url = url('sale/coupon/my/showcoupons', array('key' => $showkey), true);
		}

		foreach ($list as $taskdata) {
			$couponnum = 0;
			$couponnum = intval($taskdata['sendnum']);
			$num += $couponnum;
			$i = 1;

			while ($i <= $couponnum) {
				$couponlog = array('mid' => $mid, 'logno' => model('common')->createNO('shop_coupon_log', 'logno', 'CC'), 'couponid' => $taskdata['couponid'], 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => intval($gettype));
				Db::name('shop_coupon_log')->insert($couponlog);
				$data = array('mid' => $mid, 'couponid' => $taskdata['couponid'], 'gettype' => intval($gettype), 'gettime' => time());
				$coupondataid = Db::name('shop_coupon_data')->insertGetId($data);
				$data = array('showkey' => $showkey, 'mid' => $mid, 'coupondataid' => $coupondataid);
				Db::name('shop_coupon_sendshow')->insert($data);
				++$i;
			}
			Db::name('shop_coupon_sendshow')->where('id',$taskdata['id'])->setField('status',1);
		}

		$msg = '恭喜您获得' . $num . '张优惠券!';
		$ret = model('message')->sendCustomNotice($mid, $msg, $url);
	}

	public function backConsumeCoupon($orderid)
	{
		if (!is_array($orderid)) {
			$order = Db::name('shop_order')->where('id',$orderid)->where('status','>=',0)->where('couponid','>',0)->field('id,mid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,coupongoodprice')->find();
		}

		if (empty($order)) {
			return NULL;
		}

		$couponid = $order['couponid'];
		$couponmerchid = $order['couponmerchid'];
		$isparent = $order['isparent'];
		$parentid = $order['parentid'];
		$finishtime = $order['finishtime'];

		if (empty($couponid)) {
			return NULL;
		}

		$coupon = self::getCouponByDataID($order['couponid']);

		if (empty($coupon)) {
			return NULL;
		}

		if (!empty($coupon['back'])) {
			return NULL;
		}

		$coupongoodprice = 0;

		if ($parentid == 0) {
			$coupongoodprice = $order['coupongoodprice'];
		}

		if (($isparent == 1) || ($parentid != 0)) {
			$all_done = 1;

			if ($isparent == 1) {
				if (0 < $couponmerchid) {
					$order = Db::name('shop_order')->where('parentid='. $parentid . ' and couponmerchid=' . $couponmerchid . ' and status>=0')->field('id,mid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,isparent,parentid')->find();

					if (empty($order)) {
						return NULL;
					}

					if ($order['status'] != 3) {
						$all_done = 0;
					}
					else {
						$finishtime = $order['finishtime'];
					}
				}
				else {
					$list = model('order')->getChildOrder($orderid);
				}
			}
			else if (0 < $couponmerchid) {
				if ($order['status'] != 3) {
					$all_done = 0;
				}
				else {
					$finishtime = $order['finishtime'];
				}
			}
			else {
				$list = model('order')->getChildOrder($parentid);
			}

			if (!empty($list)) {
				foreach ($list as $k => $v) {
					if (($v['status'] != 3) && (0 < $v['couponid'])) {
						$all_done = 0;
					}
					else {
						if ($finishtime < $v['finishtime']) {
							$finishtime = $v['finishtime'];
						}
					}
				}
			}
		}

		if (($parentid != 0) && ($couponmerchid == 0)) {
			if ($all_done == 1) {
				$order = Db::name('shop_order')->where('id',$parentid)->where('status','>=',0)->field('id,mid,ordersn,createtime,couponid,couponmerchid,status,finishtime,`virtual`,isparent,parentid')->find();

				if (empty($order)) {
					return NULL;
				}
			}
		}

		$backcredit = $coupon['backcredit'];
		$backmoney = $coupon['backmoney'];
		$backredpack = $coupon['backredpack'];
		$gives = array();
		$canback = false;
		if (($order['status'] == 1) && ($coupon['backwhen'] == 2)) {
			$canback = true;
		}
		else {
			$is_done = 0;
			if (($isparent == 1) || ($parentid != 0)) {
				if ($all_done == 1) {
					$is_done = 1;
				}
			}
			else {
				if ($order['status'] == 3) {
					$is_done = 1;
				}
			}

			if ($is_done == 1) {
				if (!empty($order['virtual'])) {
					$canback = true;
				}
				else if ($coupon['backwhen'] == 1) {
					$canback = true;
				}
				else {
					if ($coupon['backwhen'] == 0) {
						$canback = true;
						$tradeset = model('common')->getSysset('trade');
						$refunddays = intval($tradeset['refunddays']);

						if (0 < $refunddays) {
							$days = intval((time() - $finishtime) / 3600 / 24);

							if ($days <= $refunddays) {
								$canback = false;
							}
						}
					}
				}
			}
		}

		if ($canback) {
			if (0 < $parentid) {
				$ordermoney = Db::name('shop_order')->where('id=' . $parentid . ' and status>=0 and couponid >0')->value('coupongoodprice');
			}
			else {
				$ordermoney = $coupongoodprice;
			}

			if ($ordermoney == 0) {
				$sql = 'select ifnull( sum(og.realprice),0) from ' . tablename('ewei_shop_order_goods') . ' og ';
				$sql .= ' left join ' . tablename('ewei_shop_order') . ' o on';
				if (($couponmerchid == 0) && ($isparent == 1)) {
					$sql .= ' o.id=og.parentorderid ';
				}
				else {
					$sql .= ' o.id=og.orderid ';
				}

				$sql .= ' where o.id=:orderid and o.mid=:mid and o.uniacid=:uniacid ';
				$ordermoney = pdo_fetchcolumn($sql, array(':uniacid' => $_W['uniacid'], ':mid' => $order['mid'], ':orderid' => $order['id']));
			}

			if (!empty($backcredit)) {
				if (strexists($backcredit, '%')) {
					$backcredit = intval((floatval(str_replace('%', '', $backcredit)) / 100) * $ordermoney);
				}
				else {
					$backcredit = intval($backcredit);
				}

				if (0 < $backcredit) {
					$gives['credit'] = $backcredit;
					m('member')->setCredit($order['mid'], 'credit1', $backcredit, array(0, '充值优惠券返积分'));
				}
			}

			if (!empty($backmoney)) {
				if (strexists($backmoney, '%')) {
					$backmoney = round(floatval((floatval(str_replace('%', '', $backmoney)) / 100) * $ordermoney), 2);
				}
				else {
					$backmoney = round(floatval($backmoney), 2);
				}

				if (0 < $backmoney) {
					$gives['money'] = $backmoney;
					m('member')->setCredit($order['mid'], 'credit2', $backmoney, array(0, '购物优惠券返利'));
				}
			}

			if (!empty($backredpack)) {
				if (strexists($backredpack, '%')) {
					$backredpack = round(floatval((floatval(str_replace('%', '', $backredpack)) / 100) * $ordermoney), 2);
				}
				else {
					$backredpack = round(floatval($backredpack), 2);
				}

				if (0 < $backredpack) {
					$gives['redpack'] = $backredpack;
					$backredpack = intval($backredpack * 100);
					m('finance')->pay($order['mid'], 1, $backredpack, '', '购物优惠券-返现金', false);
				}
			}
			Db::name('shop_coupon_data')->where('id',$order['couponid'])->update(array('back' => 1, 'backtime' => time()));
			$this->sendBackMessage($order['mid'], $coupon, $gives);
		}
	}

	public function getCouponByDataID($dataid = 0)
	{
		$data = Db::name('shop_coupon_data')->where('id',$dataid)->field('id,mid,couponid,used,back,backtime')->find();

		if (empty($data)) {
			return false;
		}

		$coupon = Db::name('shop_coupon')->where('id',$data['couponid'])->find();

		if (empty($coupon)) {
			return false;
		}

		$coupon['back'] = $data['back'];
		$coupon['backtime'] = $data['backtime'];
		$coupon['used'] = $data['used'];
		$coupon['usetime'] = $data['usetime'];
		return $coupon;
	}

	public static function addtaskdata($orderid)
	{
		$pdata = model('common')->getPluginset('coupon');
		$order = Db::name('shop_order')->where('id',$orderid)->field('id,mid,price')->find();

		if (empty($order)) {
			return NULL;
		}

		if ($pdata['isopensendtask'] == 1) {
			$price = $order['price'];
			$sendtasks = Db::name('shop_coupon_sendtasks')->where('status = 1 and starttime< ' . time() . ' and endtime>' . time() . ' and enough<=' . $price . '   and num>=sendnum')->field('id,couponid,sendnum,num,sendpoint')->find();

			if (!empty($sendtasks)) {
				$data = array('mid' => $order['mid'], 'taskid' => intval($sendtasks['id']), 'couponid' => intval($sendtasks['couponid']), 'parentorderid' => 0, 'sendnum' => intval($sendtasks['sendnum']), 'tasktype' => 1, 'orderid' => $orderid, 'createtime' => time(), 'status' => 0, 'sendpoint' => intval($sendtasks['sendpoint']));
				Db::name('shop_coupon_taskdata')->insert($data);
				$num = intval($sendtasks['num']) - intval($sendtasks['sendnum']);
				Db::name('shop_coupon_sendtasks')->where('id',$sendtasks['id'])->setField('num',$num);
			}
		}

		if ($pdata['isopengoodssendtask'] == 1) {
			$goodssendtasks = Db::name('shop_coupon_goodsendtask')->alias('gst')->join('shop_order_goods og','og.goodsid =gst.goodsid and (orderid=' . $orderid . ' or parentorderid=' . $orderid . ')')->where('og.mid=' . $order['mid'] .' and gst.num>=gst.sendnum and gst.status = 1')->select();

			foreach ($goodssendtasks as $task) {
				$data = array('mid' => $order['mid'], 'taskid' => intval($task['taskid']), 'couponid' => intval($task['couponid']), 'sendnum' => intval($task['total']) * intval($task['sendnum']), 'tasktype' => 2, 'orderid' => intval($task['orderid']), 'parentorderid' => intval($task['parentorderid']), 'createtime' => time(), 'status' => 0, 'sendpoint' => intval($task['sendpoint']));
				Db::name('shop_coupon_taskdata')->insert($data);
				$num = intval($task['num']) - (intval($task['total']) * intval($task['sendnum']));
				Db::name('shop_coupon_goodsendtask')->where('id',$task['taskid'])->setField('num',$num);
			}
		}
	}

	public static function useConsumeCoupon($orderid = 0)
	{
		if (empty($orderid)) {
			return NULL;
		}

		$order = Db::name('shop_order')->where('id',$orderid)->where('status','>=',0)->field('ordersn,createtime,couponid')->find();

		if (empty($order)) {
			return NULL;
		}

		$coupon = false;

		if (!empty($order['couponid'])) {
			$coupon = self::getCouponByDataID($order['couponid']);
		}

		if (empty($coupon)) {
			return NULL;
		}
		Db::name('shop_coupon_data')->where('id',$order['couponid'])->update(array('used' => 1, 'usetime' => $order['createtime'], 'ordersn' => $order['ordersn']));
		$mid = $order['mid'];
		self::posterbyusesendtask($coupon['id'], $mid);
	}

	public static function posterbyusesendtask($couponid, $mid)
	{
		$pdata = model('common')->getPluginset('coupon');

		if ($pdata['isopenusesendtask'] == 0) {
			return NULL;
		}

		$list = Db::name('shop_coupon_usesendtasks')->where('status',1)->where('usecouponid',$couponid)->where('starttime','<',time())->where('endtime','>',time())->where('num>=sendnum')->order('id')->select();

		if (empty($list)) {
			return NULL;
		}

		$gettype = 6;
		$num = 0;
		$showkey = random(20);
		$data = model('common')->getPluginset('coupon');
		if (empty($data['showtemplate']) || ($data['showtemplate'] == 2)) {
			$url = url('sale/coupon/my/showcoupons3', array('key' => $showkey), true);
		}
		else {
			$url = url('sale/coupon/my/showcoupons', array('key' => $showkey), true);
		}

		foreach ($list as $taskdata) {
			$couponnum = 0;
			$couponnum = intval($taskdata['sendnum']);
			$num += $couponnum;
			$i = 1;

			while ($i <= $couponnum) {
				$couponlog = array('mid' => $mid, 'logno' => model('common')->createNO('shop_coupon_log', 'logno', 'CC'), 'couponid' => $taskdata['couponid'], 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => intval($gettype));
				Db::name('shop_coupon_log')->insert($couponlog);
				$data = array('mid' => $mid, 'couponid' => $taskdata['couponid'], 'gettype' => intval($gettype), 'gettime' => time());
				$coupondataid = Db::name('shop_coupon_data')->insertGetId($data);
				$data = array('showkey' => $showkey, 'mid' => $mid, 'coupondataid' => $coupondataid);
				Db::name('shop_coupon_sendshow')->insert($data);
				++$i;
			}

			$num2 = intval($taskdata['num']) - intval($taskdata['sendnum']);
			Db::name('shop_coupon_usesendtasks')->where('id',$taskdata['id'])->setField('num',$num2);
		}

		$msg = '恭喜您获得' . $num . '张优惠券!';
		$ret = model('message')->sendCustomNotice($mid, $msg, $url);
	}

	public static function returnConsumeCoupon($order)
	{
		if (!is_array($order)) {
			$order = Db::name('shop_order')->where('id',intval($order))->where('status','>=',0)->field('id,mid,ordersn,createtime,couponid,status,finishtime')->find();
		}

		if (empty($order)) {
			return NULL;
		}

		$coupon = self::getCouponByDataID($order['couponid']);

		if (empty($coupon)) {
			return NULL;
		}

		if (!empty($coupon['returntype'])) {
			if (!empty($coupon['used'])) {
				Db::name('shop_coupon_data')->where('id',$order['couponid'])->update(array('used' => 0, 'usetime' => 0, 'ordersn' => ''));
				self::sendReturnMessage($order['mid'], $coupon);
			}
		}
	}

	public static function sendReturnMessage($mid, $coupon)
	{
		$msg = '优惠券退回';
		$text = '您的优惠券【' . $coupon['couponname'] . '】已退回您的账户，您可以再次使用, 谢谢!';
		model('notice')->sendCustomNotice($mid, $msg, $text);
	}


}