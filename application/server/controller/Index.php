<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\server\controller;
use think\Controller;
use Workerman\Lib\Timer;
use think\Db;
class Index extends Controller
{
	public function __construct()
	{
		parent::__construct(); 
		$shopset = model('common')->getSysset();
		$groupsset = Db::name('shop_groups_set')->limit(1)->find();
		$this->shopset = $shopset;
		$this->groupsset = $groupsset;
	}

	public function index()
	{
		$res = model("groups")->payResult('PT20190118140121668574','wechat',true);
	}

	public function add_timer()
	{
        Timer::add(20, array($this, 'runTasks'), array(), true);//时间间隔过小，运行会崩溃
    }

    public function runTasks() 
	{
		set_time_limit(0);
		$this->orderclose();
		$this->orderreceive();
		$this->orderwillclose();
		$this->goodsstatus();
		$this->goodspresell();
		$this->groups();
		$this->auction();
	}

    protected function orderclose()
    {
    	$shopset = $this->shopset;
    	$trade = $shopset['trade'];
    	if( isset($trade["closeorder_virtual"]) && !empty($trade["closeorder_virtual"]) ) {
			$min = intval($trade["closeorder_virtual"]);
		} else {
			$min = 15;
		}
		if( 0 < $min ) {
			$mintimes = 60 * $min;
			$orders = Db::name('shop_order')->where(" paytype<>3  and ((createtime + " . $mintimes . " <=unix_timestamp() and status=0) or (status = 1 and `isverify` = 1 and `verifyendtime` <= unix_timestamp() and `verifyendtime` > 0))")->field('id,mid,deductcredit2,ordersn,isparent,deductcredit,deductprice,status,isparent,isverify,couponid')->select();
			if( count($orders) != 0 ) {
				foreach( $orders as $o ) {
					if( $o["status"] == 0 ) {
						if( $o["isparent"] == 0 ) {
							if(!empty($o["couponid"])) {
								model('coupon')->returnConsumeCoupon($o["id"]);
							}
							model("order")->setStocksAndCredits($o["id"], 2);
							model("order")->setDeductCredit2($o);
						}
						Db::name('shop_order')->where('id = ' . $o['id'])->update(array('status'=>-1,'canceltime'=>time()));
						model("payment")->closeOrder($o["ordersn"]);
					} else {
						if( $o["status"] == 1 && $o["isverify"] == 1 ) 
						{
							Db::name('shop_order')->where('id = ' . $o['id'])->update(array('status'=>-1,'canceltime'=>time()));
							model("payment")->closeOrder($o["ordersn"]);
						}
					}
				}
			}
		}
    }

    protected function orderreceive()
    {
    	$cityexpress_receive = 0;
		$cityexpress = Db::name('shop_city_express')->where('merchid = 0')->find();
		if( !empty($cityexpress["enabled"]) && !empty($cityexpress["receive_goods"]) ) {
			$cityexpress_receive = (0 < intval($cityexpress["receive_goods"]) ? intval($cityexpress["receive_goods"]) : 0);
		}
		$shopset = $this->shopset;
    	$trade = $shopset['trade'];
		$days = intval($trade["receive"]);
		$orders = Db::name('shop_order')->where('status = 2')->field('id,couponid,mid,isparent,sendtime,price,merchid,isverify,addressid,isvirtualsend,`virtual`,dispatchtype,city_express_state')->group('id')->select();
		if( !empty($orders) ) {
			foreach( $orders as $orderid => $order ) 
			{
				if( !empty($order["city_express_state"]) && !empty($cityexpress_receive) ) 
				{
					$days = $cityexpress_receive;
				}
				$result = $this->goodsReceive($order, $days);
				if( !$result ) 
				{
					continue;
				}
				$time = time();
				Db::name('shop_order')->where('id = ' . $orderid)->update(array('status'=>3,'finishtime'=>$time));
				if( $order["isparent"] == 1 ) 
				{
					continue;
				}
				model("member")->upgradeLevel($order["mid"], $orderid);
				model("order")->setGiveBalance($orderid, 1);
				model("notice")->sendOrderMessage($orderid);
				model("order")->fullback($orderid);
				model("order")->setStocksAndCredits($orderid, 3);
				if( !empty($order["couponid"]) ) {
					model('coupon')->backConsumeCoupon($order["id"]);
				}
				model('coupon')->sendcouponsbytask($order["id"]);
			}
		}
    }

    protected function goodsReceive($order, $sysday = 0) 
	{
		$days = array( );
		if( $this->checkFetchOrder($order) ) 
		{
			return false;
		}
		$isonlyverifygoods = model("order")->checkisonlyverifygoods($order["id"]);
		if( $isonlyverifygoods ) 
		{
			return false;
		}
		if( $order["merchid"] == 0 ) 
		{
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid = '.$order["id"])->field('og.goodsid, g.autoreceive')->select();
			foreach( $goods as $i => $g ) 
			{
				$days[] = $g["autoreceive"];
			}
			$day = max($days);
		}
		else 
		{
			$day = 0;
		}
		if( $day < 0 ) 
		{
			return false;
		}
		if( $day == 0 ) 
		{
			if( $sysday <= 0 ) 
			{
				return false;
			}
			$day = $sysday;
		}
		$daytimes = 86400 * $day;
		if( $order["sendtime"] + $daytimes <= time() ) 
		{
			return true;
		}
		return false;
	}

	protected function checkFetchOrder($order) 
	{
		if( $order["isverify"] != 1 && empty($order["addressid"]) && empty($order["isvirtualsend"]) && empty($order["virtual"]) && $order["dispatchtype"] ) 
		{
			return true;
		}
		return false;
	}

	protected function orderwillclose()
    {
    	$shopset = $this->shopset;
    	$trade = $shopset['trade'];
    	if( isset($trade["closeorder_virtual"]) && !empty($trade["closeorder_virtual"]) ) {
			$days = intval($trade["closeorder_virtual"]);
		} else {
			$days = 15;
		}
		$minute = intval($trade['willcloseorder']);
		if ($minute == 0) {
			$minute = 15;
		}
		$minute *= 60;
		$daytimes = 60 * $days;
		$orders = Db::name('shop_order')->where('status=0 and paytype<>3 and willcancelmessage <> 1 and createtime + ' . $daytimes . '- ' . $minute . '<=unix_timestamp() ')->field('id,mid,deductcredit2,ordersn,isparent,deductcredit,deductprice')->select();

		foreach ($orders as $o) {
			$onew = Db::name('shop_order')->where('id=' . $o['id'] . ' and status=0 and paytype<>3  and createtime + ' . $daytimes . '- ' . $minute . ' <=unix_timestamp()')->field('id,status')->find();
			if (!empty($onew) && $onew['status'] == 0) {
				model('notice')->sendOrderWillCancelMessage($onew['id'], $daytimes);
			}
		}
    }

    protected function goodsstatus()
    {
    	$goods = Db::name('shop_goods')->where('isstatustime > 0 and deleted = 0 ')->field('id,statustimestart,statustimeend')->select();
		foreach ($goods as $key => $value) {
			if ($value['statustimestart'] < time() && time() < $value['statustimeend']) {
				$value['status'] = 1;
			} else {
				$value['status'] = 0;
			}
			Db::name('shop_goods')->where('id = ' . $value['id'])->update(array('status' => $value['status']));
		}
    }

    protected function goodspresell()
    {
    	$goods = Db::name('shop_goods')->where(' ispresell > 0 and deleted = 0 ')->field('id,title,ispresell,presellover,presellovertime,presellstart,preselltimestart,presellend,preselltimeend')->select();
		if( !empty($goods) ) {
			foreach( $goods as $key => $value ) {
				if( $value["ispresell"] == 1 && $value["presellover"] == 0 && $value["presellend"] == 1 ) {
					if( $value["preselltimeend"] < time() ) {
						$value["status"] = 0;
						Db::name('shop_goods')->where('id = ' . $value['id'])->update(array('status' => $value['status']));
					}
				} else {
					if( $value["ispresell"] == 1 && $value["presellover"] == 1 && $value["presellend"] == 1 ) {
						$time = $value["presellover"] * 86400000;
						if( $value["preselltimeend"] + $time < time() ) {
							$value["status"] = 0;
							Db::name('shop_goods')->where('id = ' . $value['id'])->update(array( "ispresell" => $value["status"] ));
						}
					}
				}
			}
		}
    }

    protected function groups()
    {
    	$groupsset = $this->groupsset;
    	$shopset = $this->shopset;
    	$times = 30 * 60;
		$orders = Db::name('shop_groups_order')->where("status = 0 and createtime + " . $times . " <= " . time() . " ")->field('id,status')->select();
		foreach( $orders as $k => $val ) {
			if( !empty($val) && $val["status"] == 0 ) {
				Db::name('shop_groups_order')->where('id = ' . $val['id'])->update(array('status'=>-1,'canceltime'=>time()));
			}
		}

		$allteam = Db::name('shop_groups_order')->where(' heads = 1 and status = 1 and success = 0 ')->select();
		foreach( $allteam as $k => $val ) {
			$total = Db::name('shop_groups_order')->where('teamid = ' . $val["teamid"] . ' and heads = 1 and status = 1 and success = 0 and is_team = 1 ')->count();
			$groups_num = $val["groupnum"];
			if( $val["is_ladder"] == 1 ) {
				$ladder = Db::name('shop_groups_ladder')->where('id = ' . $val["ladder_id"])->find();
				$groups_num = $ladder["ladder_num"];
			}
			if( $groups_num == $total ) {
				Db::name('shop_groups_order')->where('teamid = ' . $val["teamid"])->update(array( "success" => 1 ));
				model("notice")->sendTeamMessage($val["id"]);
			} else {
				$hours = $val["endtime"];
				$time = time();
				$date = date("Y-m-d H:i:s", $val["starttime"]);
				$endtime = date("Y-m-d H:i:s", strtotime(" " . $date . " + " . $hours . " hour"));
				$date1 = date("Y-m-d H:i:s", $time);
				$lasttime2 = strtotime($endtime) - strtotime($date1);
				if( $lasttime2 <= 0 ) {
					Db::name('shop_groups_order')->where('teamid = ' . $val["teamid"])->update(array( "success" => -1, "canceltime" => $time ));
					model("notice")->sendTeamMessage($val["id"]);
				}
			}
		}

		$days = intval($groupsset['receive']);
		if ($days > 0) {
			$daytimes = 86400 * $days;
			$orders = Db::name('shop_groups_order')->where(' status=2 and sendtime + ' . $daytimes . ' <=unix_timestamp() ')->field('id')->select();
			if (!empty($orders)) {
				$orderkeys = array_keys($orders);
				$orderids = implode(',', $orderkeys);
				if (!empty($orderids)) {
					Db::name('shop_groups_order')->where('id in (' . $orderids . ')')->update(array('status'=>3,'finishtime'=>time()));
				}
			}
		}

		$hours = intval($groupsset["refund"]);
		if( $hours > 0 ) 
		{
			$times = $hours * 60 * 60;
			$orders = Db::name('shop_groups_order')->where("status = 1 and pay_type !='other' and success = -1 and refundtime = 0 and canceltime + " . $times . " <= " . time())->order('id asc')->field('id,orderno,refundid,goodsid,mid,credit,creditmoney,price,freight,status,pay_type,teamid,apppay,isborrow,borrowopenid,more_spec')->select();
			foreach( $orders as $k => $val ) 
			{
				$realprice = $val["price"] - $val["creditmoney"] + $val["freight"];
				$credits = $val["credit"];
				if( $val["pay_type"] == "credit" ) 
				{
					$result = model("member")->setCredit($val["mid"], "credit2", $realprice, array( 0, $shopset["name"] . "退款: " . $realprice . "元 订单号: " . $val["orderno"] ));
				} else {
					if( $val["pay_type"] == "wechat" ) {
						$realprice = round($realprice, 2);
						$result = model("payment")->wxapp_refund($val["mid"], $val["orderno"], $val["orderno"], $realprice * 100, $realprice * 100, (!empty($val["apppay"]) ? true : false));
						$refundtype = 2;
					} else {
						if( $realprice < 1 ) 
						{
							continue;
						}
						$result = model("payment")->wechat_pay($val["mid"], 1, $realprice * 100, $val["orderno"], $shopset["name"] . "退款: " . $realprice . "元 订单号: " . $val["orderno"]);
						$refundtype = 1;
					}
				}
				if( is_error($result) && $result["message"] != "OK | 订单已全额退款" && $result["message"] != "Refund exists|退款已存在" ) 
				{
					continue;
				}
				if( 0 < $credits ) 
				{
					model("member")->setCredit($val["mid"], "credit1", $credits, array( "0", $shopset["name"] . "购物返还抵扣积分 积分: " . $val["credit"] . " 抵扣金额: " . $val["creditmoney"] . " 订单号: " . $val["orderno"] ));
				}
				$refund = Db::name('shop_groups_order_refund')->where('id = ' . $val["refundid"])->find();
				if( empty($refund) != true && $refund["refundstatus"] == 0 ) 
				{
					$change_refund["refundstatus"] = 1;
					$change_refund["refundtype"] = $refundtype;
					$change_refund["refundtime"] = time();
					if( empty($refund["operatetime"]) ) 
					{
						$change_refund["operatetime"] = time();
					}
					Db::name('shop_groups_order_refund')->where('id = ' . $val["refundid"])->update($change_refund);
				}
				Db::name('shop_groups_order')->where('id = ' . $val["id"])->update(array( "refundstate" => 0, "status" => -1, "refundtime" => time() ));
				$sales = Db::name('shop_groups_goods')->where('id = ' . $val['goodsid'])->field('id,sales,stock')->find();
				Db::name('shop_groups_goods')->where('id = ' . $sales["id"])->update(array( "sales" => $sales["sales"] - 1, "stock" => $sales["stock"] + 1 ));
				if( $val["more_spec"] == 1 ) 
				{
					$option = Db::name('shop_groups_order_goods')->where('groups_order_id = ' . $val['id'])->find();
					Db::name('shop_groups_goods_option')->where('id = ' . $option["groups_goods_option_id"])->setInc('stock');
				}
			}
		}	
    }

    protected function auction()
    {
    	$nowtime = time();
		$goods = Db::name('shop_auction_goods')->where('endtime < ' . $nowtime)->select();
		if (!empty($goods)) {
			foreach ($goods as $key => $value) {
				if (empty($value['dealmid'])) {
					$redata = Db::name('shop_auction_record')->where('goodsid = ' . $value['id'])->order('createtime desc,price desc')->find();
					if(!empty($redata)) {
						$data['dealmid']=$redata['mid'];
						Db::name('shop_auction_goods')->where('id = ' . $value['id'])->update($data);
						model('notice')->sendAuctionSuccess($value['id']);	
					}					
				}
			}
		}
		file_put_contents("server.log",time(),FILE_APPEND);//记录日志
    }

}