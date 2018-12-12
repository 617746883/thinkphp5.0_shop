<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Merch extends \think\Model
{
	public function formatPerms() {
        if (empty($formatPerms)) {
            $perms = $this->allPerms();
            $array = array();
            foreach ($perms as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $ke => $val) {
                        if (!is_array($val)) {
                            $array["parent"][$key][$ke] = $val;
                        }
                        if (is_array($val) && $ke != "xxx") {
                            foreach ($val as $k => $v) {
                                if (!is_array($v)) {
                                    $array["son"][$key][$ke][$k] = $v;
                                }
                                if (is_array($v) && $k != "xxx") {
                                    foreach ($v as $kk => $vv) {
                                        if (!is_array($vv)) {
                                            $array["grandson"][$key][$ke][$k][$kk] = $vv;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            self::$formatPerms = $array;
        }
        return self::$formatPerms;
    }

	public static function getListUser($list, $return = "all") 
	{
		if( !is_array($list) ) 
		{
			return self::getListUserOne($list);
		}
		$merch = array( );
		foreach( $list as $value ) 
		{
			$merchid = $value["merchid"];
			if( empty($merchid) ) 
			{
				$merchid = 0;
			}
			if( empty($merch[$merchid]) ) 
			{
				$merch[$merchid] = array( );
			}
			array_push($merch[$merchid], $value);
		}
		if( !empty($merch) ) 
		{
			$merch_ids = array_keys($merch);
			$merch_user = Db::name('shop_merch')->where("id in(" . implode(",", $merch_ids) . ")")->select();
			$all = array( "merch" => $merch, "merch_user" => $merch_user );
			return ($return == "all" ? $all : $all[$return]);
		}
		return array( );
	}

	public static function getListUserOne($merchid) 
	{
		$merchid = intval($merchid);
		if( $merchid ) 
		{
			$merch_user = Db::name('shop_merch')->where("id=" . $merchid)->find();
			return $merch_user;
		}
		return false;
	}

	public static function getGroups() 
	{
		return Db::name('shop_merch_group')->where('status=1')->order('isdefault desc , id asc')->field('id,groupname')->select();
	}

	public static function getCategory($data = array( )) 
	{
		$condition = " 1 ";
		return Db::name('shop_merch_category')->where($condition)->select();
	}

	public static function getCategorySwipe($data = array( )) 
	{
		$condition = " 1 ";
		return Db::name('shop_merch_category_swipe')->where($condition)->select();
	}

	public static function getMerch($data = array( )) 
	{
		$condition = " 1 ";
		return Db::name('shop_merch')->where($condition)->select();
	}

	public static function updateSet($values = array( ), $merchid = 0) 
	{
		$merchid = (empty($merchid) ? 0 : $merchid);
		$sets = self::getSet("", $merchid, true);
		foreach( $values as $key => $value ) 
		{
			foreach( $value as $k => $v ) 
			{
				$sets[$key][$k] = $v;
			}
		}
		Db::name('shop_merch')->where('id = ' . $merchid)->update(array( "sets" => iserializer($sets) ));
	}

	public function refreshSet($merchid = 0)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : $merchid));
		
		$merch_set = Db::name('shop_merch')->where('id',$merchid)->field('sets')->find();
		$allset = iunserializer($merch_set['sets']);

		if (!(is_array($allset))) {
			$allset = array();
		}

		return $allset;
	}

	public function getSet($name = '', $merchid = 0, $refresh = false)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : intval($merchid)));

		$allset = $this->refreshSet($merchid);
		return ($name ? $allset[$name] : $allset);
	}

	public function getEnoughs($set)
	{
		$allenoughs = array();
		$enoughs = $set['enoughs'];

		if ((0 < floatval($set['enoughmoney'])) && (0 < floatval($set['enoughdeduct']))) {
			$allenoughs[] = array('enough' => floatval($set['enoughmoney']), 'money' => floatval($set['enoughdeduct']));
		}


		if (is_array($enoughs)) {
			foreach ($enoughs as $e ) {
				if ((0 < floatval($e['enough'])) && (0 < floatval($e['give']))) {
					$allenoughs[] = array('enough' => floatval($e['enough']), 'money' => floatval($e['give']));
				}

			}
		}


		usort($allenoughs, 'merch_sort_enoughs');
		return $allenoughs;
	}

	public function getGoodsTotals($merchid) 
	{
		return array( "sale" => Db::name('shop_goods')->where('checked=0 and status=1 and deleted=0 and total>0 and merchid=' . $merchid)->count(), "out" => Db::name('shop_goods')->where('checked=0 and  status=1 and deleted=0 and total=0 and merchid=' . $merchid)->count(), "check" => Db::name('shop_goods')->where('checked=1 and deleted=0 and merchid=' . $merchid)->count(), "stock" => Db::name('shop_goods')->where('checked=0 and status=0 and deleted=0 and merchid=' . $merchid)->count(), "cycle" => Db::name('shop_goods')->where('deleted=1 and merchid=' . $merchid)->count() );
	}

	public function getOrderTotals($merchid) 
	{
		$condition = " isparent=0 and ismr=0 and merchid = " . $merchid . ' and deleted=0 ';
		$totals["all"] = Db::name('shop_order')->where($condition . '')->count();
		$totals["status_1"] = Db::name('shop_order')->where($condition . ' and status=-1 and refundtime=0')->count();
		$totals["status0"] = Db::name('shop_order')->where($condition . ' and status=0 and paytype<>3')->count();
		$totals["status1"] = Db::name('shop_order')->where($condition . ' and ( status=1 or ( status=0 and paytype=3) )')->count();
		$totals["status2"] = Db::name('shop_order')->where($condition . ' and status=2')->count();
		$totals["status3"] = Db::name('shop_order')->where($condition . ' and status=3')->count();
		$totals["status4"] = Db::name('shop_order')->where($condition . ' and refundstate>0 and refundid<>0')->count();
		$totals["status5"] = Db::name('shop_order')->where($condition . ' and refundtime<>0')->count();
		return $totals;
	}

	public function checkMaxMerchUser($type = 0) 
	{
		$totals = self::getAllUserTotals();
		$max_merch = self::getMaxMerchUser();
		$flag = 0;
		if( 0 < $max_merch && $max_merch <= $totals ) 
		{
			if( $type == 1 ) 
			{
				$flag = 1;
			}
			else 
			{
				show_json(0, "已经达到最大商户数量,不能再添加商户.");
			}
		}
		return $flag;
	}

	public function getAllUserTotals() 
	{
		$totals = Db::name('shop_merch')->count();
		return $totals;
	}

	public function getMaxMerchUser() 
	{
		$data = Db::name('shop_perm_plugin')->field('datas')->find();
		$max_merch = 0;
		if( !empty($data["datas"]) ) 
		{
			$datas = json_decode($data["datas"]);
			$max_merch = $datas->max_merch;
			if( empty($max_merch) ) 
			{
				$max_merch = 0;
			}
		}
		return $max_merch;
	}

	public function getMerchTotals() 
	{
		$totals = array( "reg0" => Db::name('shop_merch_reg')->where('status=0')->count(), "reg_1" => Db::name('shop_merch_reg')->where('status=-1')->count(), "user0" => Db::name('shop_merch')->where('status=0')->count(), "user1" => Db::name('shop_merch')->where('status=1')->count(), "user2" => Db::name('shop_merch')->where('status=2')->count(), "user3" => Db::name('shop_merch')->where('status=1 and TIMESTAMPDIFF(DAY,now(),FROM_UNIXTIME(accounttime))<=30')->count());
		return $totals;
	}

	public function getMerchOrderTotals($type = 0) 
	{
		$condition = " o.merchid>0 and o.isparent=0";
		if( $type == 0 ) 
		{
			$condition .= " and o.status >= 0 ";
		}
		else 
		{
			if( $type == 1 ) 
			{
				$condition .= " and o.status >= 1 ";
			}
			else 
			{
				if( $type == 3 ) 
				{
					$condition .= " and o.status = 3 ";
				}
			}
		}
		$condition .= " and o.deleted = 0 ";
		$sql = "select sum(o.price) as totalmoney from " . tablename("shop_order") . " o " . " left join " . tablename("shop_merch") . " u on u.id = o.merchid " . " where " . $condition . " ";
		$price = Db::query($sql);
		$totalmoney = round($price["totalmoney"], 2);
		$totalcount = Db::name('shop_order')->alias('o')->join('shop_merch u','u.id = o.merchid','left')->where($condition)->count();
		$data = array( );
		$data["totalmoney"] = $totalmoney;
		$data["totalcount"] = $totalcount;
		return $data;
	}

	public static function getFullCategory($merchid = 0, $fullname = false, $enabled = false)
	{
		$allcategory = array();
		$sql = ' 1 ';

		if ($enabled) {
			$sql .= ' AND enabled=1';
		}
		if ($merchid) {
			$sql .= ' AND merchid=' . $merchid;
		}
		$category = Db::name('shop_merch_goods_category')->where($sql)->order('parentid','asc')->order('displayorder','desc')->select();

		if (empty($category)) {
			return array();
		}

		foreach ($category as &$c) {
			if (empty($c['parentid'])) {
				$allcategory[] = $c;

				foreach ($category as &$c1) {
					if ($c1['parentid'] != $c['id']) {
						continue;
					}

					if ($fullname) {
						$c1['name'] = $c['name'] . '-' . $c1['name'];
					}

					$allcategory[] = $c1;

					foreach ($category as &$c2) {
						if ($c2['parentid'] != $c1['id']) {
							continue;
						}

						if ($fullname) {
							$c2['name'] = $c1['name'] . '-' . $c2['name'];
						}

						$allcategory[] = $c2;

						foreach ($category as &$c3) {
							if ($c3['parentid'] != $c2['id']) {
								continue;
							}

							if ($fullname) {
								$c3['name'] = $c2['name'] . '-' . $c3['name'];
							}

							$allcategory[] = $c3;
						}

						unset($c3);
					}

					unset($c2);
				}

				unset($c1);
			}

			unset($c);
		}

		return $allcategory;
	}

	public static function select_operator($merchid = 0) 
	{
		$merchid = intval($merchid);
		$total = Db::name('shop_merch_account')->where('merchid = ' . $merchid . ' and isfounder<>1')->count();
		return $total;
	}

	public static function getMerchOrderTotalPrice($merchid) 
	{
		$data = array( );
		$list = self::getMerchPrice($merchid, 1);
		$data["status0"] = $list["realprice"];
		$orderids = $list["orderids"];
		$condition = " and merchid= " . $merchid;
		$sql = "select * from " . tablename("shop_merch_bill") . " where 1 " . $condition . " and status=1 and creditstatus =2";
		$status1 = Db::query($sql);
		$status1price = 0;
		$status1orderids = array( );
		foreach( $status1 as $k => $v ) 
		{
			$status1price += $v["realprice"];
			if( !empty($status1orderids) ) 
			{
				$status1orderids = array_merge($status1orderids, iunserializer($v["orderids"]));
			}
		}
		$data["status1"] = round($status1price, 2);
		$data["commission1"] = 0;
		if( !empty($status1orderids) ) 
		{
			$status1orderids = array_diff($status1orderids, $orderids);
		}
		if( 0 < count($status1orderids) ) 
		{
			$status1order = Db::query("SELECT id FROM " . tablename("shop_order") . " WHERE id in(" . join(",", $status1orderids) . ") ");
			$commission1 = 0;
			if( !empty($status1order) ) 
			{
				foreach( $status1order as $k => $v ) 
				{
					$commission1 += model("order")->getOrderCommission($v["id"], $v["agentid"]);
				}
			}
			$data["commission1"] = round($commission1, 2);
		}
		$status2 = Db::query("select sum(realprice) as totalmoney from " . tablename("shop_merch_bill") . " where 1 " . $condition . " and status=2 and creditstatus =2");
		$status2price = 0;
		$status2orderids = array( );
		foreach( $status2 as $k => $v ) 
		{
			$status2price += $v["realprice"];
			if( !empty($status2orderids) ) 
			{
				$status2orderids = array_merge($status2orderids, iunserializer($v["orderids"]));
			}
		}
		$data["status2"] = round($status2price, 2);
		$data["commission2"] = 0;
		if( !empty($status2orderids) ) 
		{
			$status2orderids = array_diff($status2orderids, $orderids);
		}
		if( 0 < count($status2orderids) ) 
		{
			$status2order = Db::query("SELECT id FROM " . tablename("shop_order") . " WHERE id in(" . join(",", $status2orderids) . ") ");
			$commission2 = 0;
			if( !empty($status2order) ) 
			{
				foreach( $status2order as $k => $v ) 
				{
					$commission2 += model("order")->getOrderCommission($v["id"], $v["agentid"]);
				}
			}
			$data["commission2"] = round($commission2, 2);
		}
		$status3 = Db::query("select *  from " . tablename("shop_merch_bill") . " where 1 " . $condition . " and status=3 and creditstatus =2");
		$status3price = 0;
		$status3orderids = array( );
		foreach( $status3 as $k => $v ) 
		{
			$status3price += $v["finalprice"];
			if( !empty($status3orderids) ) 
			{
				$status3orderids = array_merge($status3orderids, iunserializer($v["orderids"]));
			}
		}
		$data["status3"] = round($status3price, 2);
		$data["commission3"] = 0;
		if( !empty($status3orderids) ) 
		{
			$status3orderids = array_diff($status3orderids, $orderids);
		}
		if( 0 < count($status3orderids) ) 
		{
			$status3order = Db::query("SELECT id FROM " . tablename("shop_order") . " WHERE id in(" . join(",", $status3orderids) . ") ");
			$commission3 = 0;
			if( !empty($status3order) ) 
			{
				foreach( $status3order as $k => $v ) 
				{
					$commission3 += model("order")->getOrderCommission($v["id"], $v["agentid"]);
				}
			}
			$data["commission3"] = round($commission3, 2);
		}
		return $data;
	}

	public function getMerchPrice($merchid, $flag = 0) 
	{
		$merch_data = model("common")->getPluginset("merch");
		if( !empty($merch_data["deduct_commission"]) ) {
			$deduct_commission = 1;
		} else {
			$deduct_commission = 0;
		}
		$condition = " u.id=" . $merchid . " and o.status=3 and o.isparent=0 and o.merchapply<=0 and o.paytype<>3 ";
		$con = "u.id,u.merchname,u.payrate,sum(o.price) price,sum(o.goodsprice) goodsprice,sum(o.dispatchprice) dispatchprice,sum(o.discountprice) discountprice,sum(o.deductprice) deductprice,sum(o.deductcredit2) deductcredit2,sum(o.isdiscountprice) isdiscountprice,sum(o.deductenough) deductenough,sum(o.merchdeductenough) merchdeductenough,sum(o.merchisdiscountprice) merchisdiscountprice,sum(o.changeprice) changeprice,sum(o.seckilldiscountprice) seckilldiscountprice";
		$tradeset = model("common")->getSysset("trade");
		$refunddays = intval($tradeset["refunddays"]);
		$refundcondition = '';
		if( 0 < $refunddays ) 
		{
			$finishtime = intval(time() - $refunddays * 3600 * 24);
			$condition .= " and o.finishtime<" . $finishtime;
			$refundcondition .= " and finishtime<" . $finishtime;
		}
		$sql = "select " . $con . " from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_order") . " o on u.id=o.merchid" . " where " . $condition . " limit 1";
		$list = Db::name('shop_merch')->where('id = ' . $merchid)->field('id,merchname,payrate')->find();
		$order = Db::name('shop_order')->where('status=3 and isparent=0 and merchapply<=0 and paytype<>3 and merchid = ' . $merchid . $refundcondition)->field('sum(price) price,sum(goodsprice) goodsprice,sum(dispatchprice) dispatchprice,sum(discountprice) discountprice,sum(deductprice) deductprice,sum(deductcredit2) deductcredit2,sum(isdiscountprice) isdiscountprice,sum(deductenough) deductenough,sum(merchdeductenough) merchdeductenough,sum(merchisdiscountprice) merchisdiscountprice,sum(changeprice) changeprice,sum(seckilldiscountprice) seckilldiscountprice')->find();
		$list = array_merge($list,$order);
		$merchcouponprice = Db::name('shop_merch')->alias('u')->join('shop_order o','u.id=o.merchid','left')->where($condition . " and o.couponmerchid>0 ")->sum('o.couponprice');
		if( 0 < $flag ) 
		{
			$sql = "select o.id,o.agentid from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_order") . " o on u.id=o.merchid" . " where " . $condition;
			$order = Db::query($sql);
			$orderids = array( );
			$commission = 0;
			if( !empty($order) ) 
			{
				foreach( $order as $k => $v ) 
				{
					$orderids[] = $v["id"];
					$commission += model("order")->getOrderCommission($v["id"], $v["agentid"]);
				}
			}
			$list["orderids"] = $orderids;
			$list["commission"] = $commission;
		}
		$list["orderprice"] = $list["goodsprice"] + $list["dispatchprice"] + $list["changeprice"];

		$list["realprice"] = $list["orderprice"] - $list["merchdeductenough"] - $list["merchisdiscountprice"] - $merchcouponprice - $list["seckilldiscountprice"];
		if( $deduct_commission ) 
		{
			$list["realprice"] -= $list["commission"];
		}
		$list["realpricerate"] = ((100 - floatval($list["payrate"])) * $list["realprice"]) / 100;
		$list["merchcouponprice"] = $merchcouponprice;
		return $list;
	}

	public static function getMerchCreditTotalPrice($merchid) 
	{
		$data = array( );
		$list = self::getMerchCredit($merchid, 1);
		$data["credit0"] = $list["realprice"];
		$data["iscredit"] = $list["iscredit"];
		$orderids = $list["orderids"];
		$condition = " merchid=" . $merchid;
		$sql = "select * from " . tablename("shop_merch_bill") . " where " . $condition . " and status=1 and creditstatus = 1";
		$status1 = Db::query($sql);
		$status1price = 0;
		foreach( $status1 as $k => $v ) 
		{
			$status1price += $v["realprice"];
			if( !empty($status1orderids) ) 
			{
				$status1orderids = array_merge($status1orderids, iunserializer($v["orderids"]));
			}
		}
		$data["credit1"] = round($status1price, 2);
		$data["commission1"] = 0;
		if( !empty($status1orderids) ) 
		{
			$status1orderids = array_diff($status1orderids, $orderids);
		}
		$sql = "select * from " . tablename("shop_merch_bill") . " where " . $condition . " and status=2 and creditstatus = 1";
		$status2 = Db::query($sql);
		$status2price = 0;
		foreach( $status2 as $k => $v ) 
		{
			$status2price += $v["passrealprice"];
			if( !empty($status2orderids) ) 
			{
				$status2orderids = array_merge($status2orderids, iunserializer($v["orderids"]));
			}
		}
		$data["credit2"] = round($status2price, 2);
		$data["commission2"] = 0;
		if( !empty($status2orderids) ) 
		{
			$status2orderids = array_diff($status2orderids, $orderids);
		}
		$sql = "select *  from " . tablename("shop_merch_bill") . " where " . $condition . " and status=3 and creditstatus = 1";
		$status3 = Db::query($sql);
		$status3price = 0;
		foreach( $status3 as $k => $v ) 
		{
			$status3price += $v["passrealprice"];
			if( !empty($status3orderids) ) 
			{
				$status3orderids = array_merge($status3orderids, iunserializer($v["orderids"]));
			}
		}
		$data["credit3"] = round($status3price, 2);
		$data["commission3"] = 0;
		return $data;
	}

	public function getMerchCredit($merchid, $flag = 0) 
	{
		$merch_data = model("common")->getPluginset("merch");
		$condition = " and u.id=" . $merchid . " and o.status >0  and o.merchapply<=0 ";
		$con = "o.id,u.merchname,u.creditrate,sum(o.dispatch) as dispatch,sum(o.money) as money,sum(o.credit) as credit,u.iscredit,u.iscreditmoney";
		$sql = "select " . $con . " from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_creditshop_log") . " o on u.id=o.merchid" . " where 1 " . $condition . " limit 1";
		$list = Db::query($sql);
		$_condition = "and id=" . $merchid;
		$_sql = "select iscreditmoney,creditrate,iscredit from" . tablename("shop_merch") . "where 1 " . $_condition . ' LIMIT 1';

		$iscredit = Db::name('shop_merch')->where(" id=" . $merchid)->field('iscreditmoney,creditrate,iscredit')->find();
		if( 0 < $flag ) 
		{
			$sql = "select o.id,u.iscreditmoney,o.dispatch,o.money,o.credit from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_creditshop_log") . " o on u.id=o.merchid" . " where 1 " . $condition;
			$order = Db::query($sql);
			$orderids = array( );
			if( !empty($order) ) 
			{
				$credit = 0;
				foreach( $order as $k => $v ) 
				{
					if( $v["iscreditmoney"] == 1 && $v["dispatch"] + $v["money"] == 0 ) 
					{
						continue;
					}
					$orderids[] = $v["id"];
					if( $iscredit["iscreditmoney"] != 1 && $list["creditrate"] != 0 ) 
					{
						$credit += @floor(@floatval($v["credit"] / $list["creditrate"]));
					}
				}
			}
			$list["orderids"] = $orderids;
		}
		if( $iscredit["iscreditmoney"] == 1 ) 
		{
			$list["credit"] = 0;
			$list["realcreaterate"] = 0;
			$list["realprice"] = $list["realcreaterate"] + $list["money"] + $list["dispatch"];
		}
		else 
		{
			$list["realcreaterate"] = $credit;
			$list["realprice"] = $list["realcreaterate"] + $list["money"] + $list["dispatch"];
		}
		$list["orderprice"] = $list["dispatch"] + $list["money"];
		$list["iscredit"] = $iscredit["iscredit"];
		$list["iscreditmoney"] = $iscredit["iscreditmoney"];
		return $iscredit;
		return $list;
	}

	public function getMerchPriceList($merchid, $orderid = 0, $flag = 0, $applyid = 0) 
	{
		$merch_data = model("common")->getPluginset("merch");
		if( !empty($merch_data["deduct_commission"]) ) 
		{
			$deduct_commission = 1;
		}
		else 
		{
			$deduct_commission = 0;
		}
		$condition = " and u.id=" . $merchid . " and o.status=3 and o.isparent=0 and o.paytype<>3 ";
		switch( $flag ) 
		{
			case 0: $condition .= " and o.merchapply <= 0";
			break;
			case 1: $condition .= " and o.merchapply = 1";
			break;
			case 2: $condition .= " and o.merchapply = 2";
			break;
			case 3: $condition .= " and o.merchapply = 3";
			break;
		}
		$tradeset = model("common")->getSysset("trade");
		$refunddays = intval($tradeset["refunddays"]);
		if( 0 < $refunddays ) 
		{
			$finishtime = intval(time() - $refunddays * 3600 * 24);
			$condition .= " and o.finishtime<" . $finishtime;
		}
		if( !empty($orderid) ) 
		{
			$condition .= " and o.id=" . $orderid . " Limit 1";
		}
		$con = "o.id,u.merchname,u.payrate,o.price,o.goodsprice,o.dispatchprice,discountprice," . "o.deductprice,o.deductcredit2,o.isdiscountprice,o.deductenough,o.changeprice,o.agentid,o.seckilldiscountprice," . "o.merchdeductenough,o.merchisdiscountprice,o.couponmerchid,o.couponprice,o.couponmerchid,o.ordersn,o.finishtime,o.merchapply";
		$sql = "select " . $con . " from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_order") . " o on u.id=o.merchid" . " where 1 " . $condition;
		$order = Db::query($sql);
		foreach( $order as &$list ) 
		{
			$merchcouponprice = 0;
			if( 0 < $list["couponmerchid"] ) 
			{
				$merchcouponprice = $list["couponprice"];
			}
			$list["commission"] = model("order")->getOrderCommission($list["id"], $list["agentid"]);
			$list["orderprice"] = $list["goodsprice"] + $list["dispatchprice"] + $list["changeprice"];
			$list["realprice"] = $list["orderprice"] - $list["merchdeductenough"] - $list["merchisdiscountprice"] - $merchcouponprice;
			if( $deduct_commission ) 
			{
				$list["realprice"] -= $list["commission"];
			}
			if( $applyid ) 
			{
				$item = $this->getOneApply($applyid);
				if( $item ) 
				{
					$list["payrate"] = $item["payrate"];
				}
			}
			$list["realpricerate"] = ((100 - floatval($list["payrate"])) * $list["realprice"]) / 100;
			$list["merchcouponprice"] = $merchcouponprice;
		}
		unset($list);
		if( !empty($orderid) ) 
		{
			return $order[0];
		}
		return $order;
	}

	public function getOneApply($id) 
	{
		$condition = " and b.id= " . $id;
		$sql = "select b.*,u.merchname,u.realname,u.mobile,u.iscreditmoney from " . tablename("shop_merch_bill") . " b " . " left join " . tablename("shop_merch") . " u on b.merchid = u.id" . " where 1 " . $condition . " Limit 1";
		$data = Db::name('shop_merch_bill')->alias('b')->join('shop_merch u','b.merchid = u.id','left')->field('b.*,u.merchname,u.realname,u.mobile,u.iscreditmoney')->where(' 1 ' . $condition)->find();
		return $data;
	}

	public function getPassApplyPrice($merchid, $orderids, $applyid = 0) 
	{
		$data = array( );
		$data["realprice"] = 0;
		$data["realpricerate"] = 0;
		$data["orderprice"] = 0;
		if( !empty($orderids) ) 
		{
			foreach( $orderids as $key => $orderid ) 
			{
				$item = $this->getMerchPriceList($merchid, $orderid, 1, $applyid);
				$data["realprice"] += $item["realprice"];
				$data["realpricerate"] += $item["realpricerate"];
				$data["orderprice"] += $item["orderprice"];
			}
		}
		return $data;
	}

}