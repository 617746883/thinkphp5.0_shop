<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Creditshop extends \think\Model
{
	public static function getGoods($id, $member, $optionid = 0, $num = 1) 
	{		
		if( empty($id) ) 
		{
			return NULL;
		}
		$credit = $member["credit1"];
		$money = $member["credit2"];
		$optionid = intval($optionid);
		$merchid = 0;
		$condition = " id =  " . $id;
		if( 0 < $merchid ) 
		{
			$condition .= " and merchid = " . $merchid . " ";
		}
		$shopset = model('common')->getSysset();
		$goods = Db::name('shop_creditshop_goods')->where($condition)->find();
		if( empty($goods) ) 
		{
			return false;
		}
		if( !empty($goods["status"]) && empty($goods["status"]) ) 
		{
			return array( "canbuy" => false, "buymsg" => "已下架" );
		}
		$goods = set_medias($goods, "thumb");
		if( 0 < $goods["credit"] && 0 < $goods["money"] ) 
		{
			$goods["acttype"] = 0;
		}
		else 
		{
			if( 0 < $goods["credit"] ) 
			{
				$goods["acttype"] = 1;
			}
			else 
			{
				if( 0 < $goods["money"] ) 
				{
					$goods["acttype"] = 2;
				}
				else 
				{
					$goods["acttype"] = 3;
				}
			}
		}
		if( intval($goods["isendtime"]) == 1 ) 
		{
			$goods["endtime_str"] = date("Y-m-d H:i", $goods["endtime"]);
		}
		$goods["timestart_str"] = date("Y-m-d H:i", $goods["timestart"]);
		$goods["timeend_str"] = date("Y-m-d H:i", $goods["timeend"]);
		$goods["timestate"] = "";
		$goods["canbuy"] = !empty($goods["status"]) && empty($goods["deleted"]);
		if( empty($goods["canbuy"]) ) 
		{
			$goods["buymsg"] = "已下架";
		}
		else 
		{
			if( $goods["goodstype"] == 3 ) 
			{
				if( $goods["packetsurplus"] <= 0 || $goods["surplusmoney"] <= $goods["packetlimit"] || $goods["surplusmoney"] < $goods["minpacketmoney"] ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "已兑完" : "已抽完");
				}
			}
			else 
			{
				if( $num - 1 < $goods["total"] ) 
				{
					$logcount = Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and status>=2')->sum('goods_num');
					$goods["logcount"] = $logcount;
					if( $goods["joins"] < $logcount ) 
					{
						Db::name('shop_creditshop_goods')->where('id',$id)->update(array('joins' => $logcount));
					}
				}
				else 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "已兑完" : "已抽完");
				}
			}
			if( $goods["hasoption"] && $optionid ) 
			{
				$option = Db::name('shop_creditshop_goods_option')->where('id = ' . $optionid . ' and goodsid = ' . $id . ' ')->field('total,credit,money,title as optiontitle,weight')->find();
				if(empty($option)) {
					$goods["canbuy"] = false;
					$goods["buymsg"] = "商品规格错误";
					return $goods;
				}
				$goods["credit"] = $option["credit"];
				$goods["money"] = $option["money"];
				$goods["weight"] = $option["weight"];
				$goods["total"] = $option["total"];
				$goods["optiontitle"] = $option["optiontitle"];
				if( $option["total"] <= $num - 1 ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "已兑完" : "已抽完");
				}
			}
			if( $goods["isverify"] == 0 ) 
			{
				if( $goods["dispatchtype"] == 1 ) 
				{
					if( empty($goods["dispatchid"]) ) 
					{
						$dispatch = model("dispatch")->getDefaultDispatch($goods["merchid"]);
					}
					else 
					{
						$dispatch = model("dispatch")->getOneDispatch($goods["dispatchid"]);
					}
					if( empty($dispatch) ) 
					{
						$dispatch = model("dispatch")->getNewDispatch($goods["merchid"]);
					}
					$areas = iunserializer($dispatch["areas"]);
					if( !empty($areas) && is_array($areas) ) 
					{
						$firstprice = array( );
						foreach( $areas as $val ) 
						{
							if( empty($dispatch["calculatetype"]) ) 
							{
								$firstprice[] = $val["firstprice"];
							}
							else 
							{
								$firstprice[] = $val["firstnumprice"];
							}
						}
						array_push($firstprice, model("dispatch")->getDispatchPrice($num, $dispatch));
						$ret = array( "min" => round(min($firstprice), 2), "max" => round(max($firstprice), 2) );
						$goods["areas"] = $ret;
					}
					else 
					{
						$ret = $goods["dispatch"];
					}
					$goods["dispatch"] = (is_array($ret) ? $ret["min"] : $ret);
				}
			}
			else 
			{
				$goods["dispatch"] = 0;
			}
			if( $goods["canbuy"] && 0 < $goods["totalday"] ) 
			{
				$logcount = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and status>=2 and  date_format(from_UNIXTIME(`createtime`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\')')->sum('goods_num');
				if( $goods["totalday"] <= $logcount ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "今日已兑完" : "今日已抽完");
				}
			}
			if( $goods["canbuy"] &&  $goods["chanceday"] && $num - 1 < $goods["chanceday"] ) 
			{
				$logcount = Db::name('shop_creditshop_log')->where("goodsid=" . $id .  " and mid=" . $member['id'] .  " and status>0 and  date_format(from_UNIXTIME(`createtime`),'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')")->sum('goods_num');
				if( $goods["chanceday"] <= $logcount ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "今日已兑换" : "今日已抽奖");
				}
			}
			if( $goods["canbuy"] && $num - 1 < $goods["chance"] ) 
			{
				$logcount = Db::name('shop_creditshop_log')->where("goodsid=" . $id . " and mid=" . $member['id'] . " and status>0 ")->sum('goods_num');
				if( $goods["chance"] <= $logcount ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = (empty($goods["type"]) ? "已兑换" : "已抽奖");
				}
			}
			if( $goods["canbuy"] ) 
			{
				$credit_text = (empty($shopset["trade"]["credittext"]) ? "积分" : $shopset["trade"]["credittext"]);
				if( $credit < $goods["credit"] * $num && 0 < $goods["credit"] ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = $credit_text . "不足";
				}
			}
			if( $goods["canbuy"] && $goods["istime"] == 1 ) 
			{
				if( time() < $goods["timestart"] ) 
				{
					$goods["canbuy"] = false;
					$goods["timestate"] = "before";
					$goods["buymsg"] = "活动未开始";
				}
				else 
				{
					if( $goods["timeend"] < time() ) 
					{
						$goods["canbuy"] = false;
						$goods["buymsg"] = "活动已结束";
					}
					else 
					{
						$goods["timestate"] = "after";
					}
				}
			}
			if( $goods["canbuy"] && $goods["isendtime"] == 1 && $goods["isverify"] && $goods["endtime"] < time() ) 
			{
				$goods["canbuy"] = false;
				$goods["buymsg"] = "活动已结束(超出兑换期)";
			}
			$levelid = $member["level"];
			$groupid = $member["groupid"];
			if( $goods["canbuy"] && $goods["buylevels"] != "" ) 
			{
				$buylevels = explode(",", $goods["buylevels"]);
				if( !in_array($levelid, $buylevels) ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = "无会员特权";
				}
			}
			if( $goods["canbuy"] && $goods["buygroups"] != "" ) 
			{
				$buygroups = explode(",", $goods["buygroups"]);
				if( !in_array($groupid, $buygroups) ) 
				{
					$goods["canbuy"] = false;
					$goods["buymsg"] = "无会员特权";
				}
			}
		}

		$goods["money"] = price_format($goods["money"], 2);
		$goods["minmoney"] = price_format($goods["minmoney"], 2);
		$goods["minmoney"] = price_format($goods["minmoney"], 2);
		return $goods;
	}

	public static function dispatchPrice($goodsid, $addressid, $optionid = 0, $num = 1, $mid = 0) 
	{
		$member = model("member")->getMember($mid);
		$goods = self::getGoods($goodsid, $member, $optionid);
		$dispatch = 0;
		$dispatch_array = array( );
		$address = Db::name('shop_member_address')->where('id = ' . $addressid)->field('id,realname,mobile,address,province,city,area')->find();
		if( $goods["dispatchtype"] == 0 ) {
			$dispatch = $goods["dispatch"];
		} else {
			$merchid = $goods["merchid"];
			if( empty($goods["dispatchid"]) ) {
				$dispatch_data = model("dispatch")->getDefaultDispatch($merchid);
			} else {
				$dispatch_data = model("dispatch")->getOneDispatch($goods["dispatchid"]);
			}
			if( empty($dispatch_data) ) {
				$dispatch_data = model("dispatch")->getNewDispatch($merchid);
			}
			if( !empty($dispatch_data) ) {
				$dkey = $dispatch_data["id"];
				if( !empty($user_city) ) {
					$citys = model("dispatch")->getAllNoDispatchAreas($dispatch_data["nodispatchareas"]);
					if( !empty($citys) && in_array($user_city, $citys) && !empty($citys) ) {
						$isnodispatch = 1;
						$has_goodsid = 0;
						if( !empty($nodispatch_array["goodid"]) && in_array($goods["goodsid"], $nodispatch_array["goodid"]) ) {
							$has_goodsid = 1;
						}
						if( $has_goodsid == 0 ) {
							$nodispatch_array["goodid"][] = $goods["id"];
							$nodispatch_array["title"][] = $goods["title"];
							$nodispatch_array["city"] = $user_city;
						}
					}
				}
				if( $goods["isverify"] == 0 && $goods["goodstype"] == 0 ) {
					$areas = unserialize($dispatch_data["areas"]);
					if( $dispatch_data["calculatetype"] == 1 ) 
					{
						$param = $num;
					}
					else 
					{
						$param = $goods["weight"] * $num;
					}
					if( array_key_exists($dkey, $dispatch_array) ) 
					{
						$dispatch_array[$dkey]["param"] += $param;
					}
					else 
					{
						$dispatch_array[$dkey]["data"] = $dispatch_data;
						$dispatch_array[$dkey]["param"] = $param;
					}
				}
			}
			$dispatch_merch = array( );
			if( !empty($dispatch_array) ) 
			{
				foreach( $dispatch_array as $k => $v ) 
				{
					$dispatch_data = $dispatch_array[$k]["data"];
					$param = $dispatch_array[$k]["param"];
					$areas = unserialize($dispatch_data["areas"]);
					if( !empty($address) ) 
					{
						$dprice = model("dispatch")->getCityDispatchPrice($areas, $address, $param, $dispatch_data);
					}
					else 
					{
						if( !empty($member["city"]) ) 
						{
							$dprice = model("dispatch")->getCityDispatchPrice($areas, $member, $param, $dispatch_data);
						}
						else 
						{
							$dprice = model("dispatch")->getDispatchPrice($param, $dispatch_data);
						}
					}
					$dispatch = $dprice;
				}
			}
		}
		return $dispatch;
	}

	public static function payResult($logno, $type, $total_fee, $transaction_id) 
	{
		$log = Db::name('shop_creditshop_log')->where('logno',$logno)->find();
		$member = model("member")->getMember($log["mid"]);
		$goods = self::getGoods($log["goodsid"], $member, $log["optionid"], $log["goods_num"]);
		$goods["money"] *= $log["goods_num"];
		$goods["credit"] *= $log["goods_num"];
		$credit = $member["credit1"];
		$money = $member["credit2"];
		if( 0 < $log["status"] ) {
			return true;
		}
		$record = array( );
		$record["paystatus"] = 1;
		if( $type == "wechat" ) {
			$record["paytype"] = 1;
		} else {
			if( $type == "alipay" ) {
				$record["paytype"] = 2;
			}
		}
		if( !empty($log) && $log["paystatus"] < 1 && $log["creditpay"] == 0 ) {
			if( empty($log["paystatus"]) ) {
				if( 0 < $goods["credit"] && $credit < $goods["credit"] ) {
					return true;
				}
				if( 0 < $goods["money"] && $money < $goods["money"] && $log["paytype"] == 0 ) {
					return true;
				}
			}
			if( !empty($goods) && $total_fee == $goods["money"] + $goods["dispatch"] ) {
				Db::name('shop_creditshop_log')->where('id',$log['id'])->update($record);
			}
			if( 0 < $goods["credit"] && empty($log["creditpay"]) ) {
				$update["credit"] = $goods["credit"];
				model("member")->setCredit($log["mid"], "credit1", 0 - $goods["credit"], "积分商城扣除积分 " . $goods["credit"]);
				$update["creditpay"] = 1;
				Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setInc('joins',1);
			}
			$update["money"] = $total_fee;
			$status = 1;
			if( $goods["type"] == 1 ) 
			{
				if( 0 < $goods["rate1"] && 0 < $goods["rate2"] ) 
				{
					if( $goods["rate1"] == $goods["rate2"] ) 
					{
						$status = 2;
					}
					else 
					{
						$rand = rand(0, intval($goods["rate2"]));
						if( $rand <= intval($goods["rate1"]) ) 
						{
							$status = 2;
						}
					}
				}
			}
			else 
			{
				$status = 2;
			}
			if( $status == 2 && $goods["isverify"] == 1 ) 
			{
				$update["eno"] = $this->createENO();
			}
			if( $goods["isverify"] == 1 ) 
			{
				$update["verifynum"] = (0 < $goods["verifynum"] ? $goods["verifynum"] : 1);
				if( $goods["isendtime"] == 0 ) 
				{
					if( 0 < $goods["usetime"] ) 
					{
						$update["verifytime"] = time() + 3600 * 24 * intval($goods["usetime"]);
					}
					else 
					{
						$update["verifytime"] = 0;
					}
				}
				else 
				{
					$update["verifytime"] = intval($goods["endtime"]);
				}
			}
			$update["status"] = $status;
			$update["money"] = $money;
			if( 0 < $goods["dispatch"] && $goods["goodstype"] == 0 && $goods["type"] == 0 ) 
			{
				$update["dispatchstatus"] = "1";
				$update["dispatch"] = $goods["dispatch"];
			}
			Db::name('shop_creditshop_log')->where('id',$log['id'])->update($update);
			if( $status == 2 ) 
			{
				if( $goods["goodstype"] == 1 ) 
				{
					if( model("coupon") ) 
					{
						model("coupon")->creditshop($log["id"]);
						$status = 3;
					}
					$update["time_finish"] = time();
				}
				else 
				{
					if( $goods["goodstype"] == 2 ) 
					{
						$credittype = "credit2";
						$creditstr = "积分商城兑换余额";
						$num = abs($goods["grant1"]);
						$credit2 = floatval($member["credit2"]) + $num;
						model("member")->setCredit($log["mid"], $credittype, $num, array(0, $creditstr ));
						$set = model("common")->getSysset("shop");
						$data = array( "mid" => $log["mid"], "credittype" => "credit2", "createtime" => time(), "remark" => $set["name"] . "积分商城兑换余额", "num" => $num, "module" => "creditshop" );
						$mlogid = Db::name('member_credits_record')->insertGetId($data);
						model("notice")->sendMemberLogMessage($mlogid);
						$status = 3;
						$update["time_finish"] = time();
					}
					else 
					{
						if( $goods["goodstype"] == 3 ) 
						{
						}
					}
				}
				$update["status"] = $status;
				Db::name('shop_creditshop_log')->where('id',$log['id'])->update($update);
				model('notice')->sendCreditshopMessage($log["id"]);
				if( $status == 3 ) 
				{
					Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('total',1);
				}
				if( $goods["goodstype"] == 0 && $status == 2 ) 
				{
					Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('total',1);
				}
				if( $goods["goodstype"] == 3 && $status == 2 ) 
				{
					Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('packetsurplus',1);
				}
				if( $goods["hasoption"] && $log["optionid"] ) 
				{
					Db::name('shop_creditshop_goods_option')->where('id',$log['optionid'])->setDec('total',1);
				}
			}
		}
		return true;
	}

	public static function createENO() 
	{
		$ecount = Db::name('shop_creditshop_log')->count();
		if( $ecount < 99999999 ) 
		{
			$ecount = 8;
		}
		else 
		{
			$ecount = strlen($ecount . "");
		}
		$eno = rand(pow(10, $ecount), pow(10, $ecount + 1) - 1);
		while( 1 ) 
		{
			$c = Db::name('shop_creditshop_log')->where('eno',$eno)->count();
			if( $c <= 0 ) 
			{
				break;
			}
			$eno = rand(pow(10, $ecount), pow(10, $ecount + 1) - 1);
		}
		return $eno;
	}

	public static function packetmoney($goodsid) 
	{
		$money = 0;
		$goods = Db::name('shop_creditshop_goods')->where('id',$goodsid)->find();
		$size = Db::name('shop_creditshop_log')->where(" goodsid = " . $goodsid . " and status = 2 ")->count();
		if( !$goods ) 
		{
			return array( "status" => 0, "message" => "活动已下架！" );
		}
		if( $goods["packettype"] == 1 ) 
		{
			$MoneyPackage = array( "remainSize" => $goods["packetsurplus"] + $size, "remainMoney" => $goods["surplusmoney"] );
			$min = $goods["minpacketmoney"];
			if( empty($goods["maxpacketmoney"]) ) 
			{
				$goods["maxpacketmoney"] = $goods["minpacketmoney"];
			}
			if( $MoneyPackage["remainMoney"] < $goods["minpacketmoney"] ) 
			{
				return array( "status" => 0, "message" => "奖金不足了" );
			}
			if( $MoneyPackage["remainMoney"] <= $goods["maxpacketmoney"] ) 
			{
				$max = $MoneyPackage["remainMoney"];
			}
			else 
			{
				if( $goods["maxpacketmoney"] < $MoneyPackage["remainMoney"] ) 
				{
					$max = $goods["maxpacketmoney"];
				}
			}
			$money = mt_rand($max * 100, $min * 100);
			$money = round($money * 100, 0) / 100;
		}
		else 
		{
			$money = $goods["grant2"];
		}
		return array( "status" => 1, "money" => $money );
	}

}