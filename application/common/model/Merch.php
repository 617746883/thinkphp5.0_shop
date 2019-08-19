<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Merch extends \think\Model
{
	static public $allPerms = array();
	static public $getLogTypes = array();
	static public $formatPerms = array();

	protected function build($condition, $params, $data)
	{
		foreach ($data as $key => $value) {
			if ($key == 'column' || $key == 'field') {
				continue;
			}

			if (stripos($key, 'in') === 0) {
				$key = str_ireplace('in', '', $key);

				if (is_array($value)) {
					foreach ($value as &$val) {
						$val = (int) $val;
					}

					unset($val);
					$key = str_ireplace('in', '', $key);
					$condition .= ' AND `' . $key . '` in(' . implode(',', $value) . ')';
				}

				continue;
			}

			if (stripos($key, 'orlike') === 0) {
				$key = str_ireplace('orlike', '', $key);

				if (is_array($value)) {
					$condition .= ' OR (';
					$i = 0;

					foreach ($value as $k => $val) {
						if ($i == 0) {
							$condition .= '`' . $k . '`=' . $$val;
						} else {
							if (stripos($val[0], 'and') !== false || stripos($val[0], 'or') !== false) {
								$condition .= ' ' . strtoupper($val[0]) . ' `' . $k . '` like \'' . $val[1] . '\'';
							} else {
								$condition .= ' AND `' . $k . '` like %' . $val . '%';
							}
						}

						++$i;
					}

					$condition .= ')';
					continue;
				}

				$condition .= ' OR `' . $key . '` like %' . $value . '%';
				continue;
			}

			if (stripos($key, 'like') === 0) {
				$key = str_ireplace('like', '', $key);

				if (is_array($value)) {
					$condition .= ' AND (';
					$i = 0;

					foreach ($value as $k => $val) {
						if ($i == 0) {
							$condition .= '`' . $k . '` like %' . $val . '%';
						} else {
							if (stripos($val[0], 'and') !== false || stripos($val[0], 'or') !== false) {
								$condition .= ' ' . strtoupper($val[0]) . ' `' . $k . '` like ' . $val[1];
							} else {
								$condition .= ' AND `' . $k . '` like %' . $val . '%';
							}
						}

						++$i;
					}

					$condition .= ')';
					continue;
				}

				$condition .= ' AND `' . $key . '` like %' . $value . '%';
				continue;
			}

			if (stripos($key, 'limit') === 0) {
				if (is_array($value)) {
					if (isset($value[1])) {
						$condition .= ' LIMIT ' . $value[0] . ',' . $value[1];
					}
					else {
						$condition .= ' LIMIT ' . $value[0];
					}

					continue;
				}

				$condition .= ' LIMIT ' . $value;
				continue;
			}

			if (stripos($key, 'orderby') === 0) {
				if (is_array($value)) {
					$condition .= ' ORDER BY';
					$i = 0;

					foreach ($value as $k => $val) {
						if ($i == 0) {
							$condition .= ' ' . $k . ' ' . $val;
						}
						else {
							$condition .= ',' . $k . ' ' . $val;
						}

						++$i;
					}

					continue;
				}

				$condition .= ' LIMIT ' . $value;
				continue;
			}

			if (stripos($key, 'or') === 0) {
				$key = str_ireplace('or', '', $key);

				if (is_array($value)) {
					$condition .= ' OR (';
					$i = 0;

					foreach ($value as $k => $val) {
						if ($i == 0) {
							$condition .= '`' . $k . '`=' . $val;
						} else {
							if (stripos($val[0], 'and') !== false || stripos($val[0], 'or') !== false) {
								$condition .= ' ' . strtoupper($val[0]) . ' `' . $k . '`=' . $val[1];
							} else {
								$condition .= ' AND `' . $k . '`=' . $val;
							}
						}

						++$i;
					}

					$condition .= ')';
					continue;
				}

				$condition .= ' OR `' . $key . '`=' . $value;
				continue;
			}

			if (stripos($key, 'and') === 0) {
				$key = str_ireplace('and', '', $key);

				if (is_array($value)) {
					$condition .= ' AND (';
					$i = 0;

					foreach ($value as $k => $val) {
						if ($i == 0) {
							$condition .= '`' . $k . '`=' . $val;
						}
						else {
							if (stripos($val[0], 'and') !== false || stripos($val[0], 'or') !== false) {
								$condition .= ' ' . strtoupper($val[0]) . ' `' . $k . '`=' . $val[1];
							} else {
								$condition .= ' AND `' . $k . '`=' . $val;
							}
						}

						++$i;
					}

					$condition .= ')';
					continue;
				}

				$condition .= ' OR `' . $key . '`=' . $value;
				continue;
			}

			$condition .= ' AND `' . $key . '`=' . $value;
		}

		if (isset($data['field'])) {
			if (is_array($data['field'])) {
				$field = '`' . implode('`,`', $data['field']) . '``';
			} else {
				$field = explode(',', $data['field']);

				foreach ($field as &$value) {
					$temp = explode(' ', $value);

					if (strpos($value, '(') === false) {
						$value = str_replace($temp[0], '`' . $temp[0] . '`', $value);
					}
				}

				unset($value);
				$field = implode(',', $field);
			}
		}

		return array('condition' => $condition, 'column' => isset($data['column']) ? $data['column'] : '', 'field' => isset($field) ? $field : '*');
	}

	public function formatPerms() {
        if (empty($formatPerms)) {
            $perms = self::allPerms();
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
			$merch_user = fetchall('select * from ' . tablename('shop_merch') . ' where id in(' . implode(',', $merch_ids) . ')', 'id');
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

		$allset = self::refreshSet($merchid);
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

	public function getInsertData($fields, $memberdata)
	{
		return '';
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
		$data["commission"] = $list["commission"];
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

	public function getMerchPriceOld($merchid, $flag = 0) 
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

	public function getMerchPrice($merchid, $flag = 0) 
	{
		$merch_data = model("common")->getPluginset("merch");
		if( !empty($merch_data["deduct_commission"]) ) {
			$deduct_commission = 1;
		} else {
			$deduct_commission = 0;
		}
		$condition = " u.id=" . $merchid . " and o.status=3 and o.isparent=0 and o.merchapply<=0 and o.paytype<>3 ";
		$tradeset = model("common")->getSysset("trade");
		$refunddays = intval($tradeset["refunddays"]);
		$refundcondition = '';
		$ordercondition = '';
		if( 0 < $refunddays ) 
		{
			$finishtime = intval(time() - $refunddays * 3600 * 24);
			$condition .= " and o.finishtime<" . $finishtime;
			$ordercondition .= " and finishtime<" . $finishtime;
			$refundcondition .= " and finishtime<" . $finishtime;
		}
		$merch = Db::name('shop_merch')->where('id = ' . $merchid)->field('id,merchname,payrate')->find();
		$list = Db::name('shop_order')->where('status=3 and isparent=0 and merchapply<=0 and paytype<>3 and merchid = ' . $merchid . $refundcondition . $ordercondition)->field('id,agentid,price,goodsprice,credit4price,dispatchprice,discountprice,deductprice,deductcredit2,isdiscountprice,deductenough,merchdeductenough,merchisdiscountprice,changeprice,seckilldiscountprice,isluckbuy,luckbuydiscount')->select();
		$merchcouponprice = Db::name('shop_merch')->alias('u')->join('shop_order o','u.id=o.merchid','left')->where($condition . " and o.couponmerchid>0 ")->sum('o.couponprice');
		if( 0 < $flag ) 
		{
			$orderids = array( );
			$commission = 0;
			$realprice = 0;
			$price = 0;
			$goodsprice = 0;
			$credit4price = 0;
			$dispatchprice = 0;
			$deductprice = 0;
			$deductcredit2 = 0;
			$isdiscountprice = 0;
			$deductenough = 0;
			$merchdeductenough = 0;
			$merchisdiscountprice = 0;
			$changeprice = 0;
			$seckilldiscountprice = 0;
			if( !empty($list) ) 
			{
				foreach( $list as $k => $v ) 
				{
					$orderprice = 0;
					$orderids[] = $v["id"];
					$commission += model("order")->getOrderCommission($v["id"], $v["agentid"]);
					if($v['isluckbuy'] == 1) {
						switch ($v['luckbuydiscount']) {
							case '7':
								$orderprice = round($v['goodsprice'] * 0.7, 2);
								break;
							case '6':
								$orderprice = round($v['goodsprice'] * 0.6, 2);
								break;
							case '5':
								$orderprice = round($v['goodsprice'] * 0.5, 2);
								break;
							
							default:
								$orderprice = round($v['goodsprice'] * 0.7, 2);
								break;
						}
					} else {
						$orderprice = $v['price'] + ($v['credit4price'] * 0.1);
					}
					$realprice += $orderprice;
					$price += $v['price'];
					$goodsprice += $v['goodsprice'];
					$credit4price += $v['credit4price'];
					$dispatchprice += $v['dispatchprice'];
					$discountprice += $v['discountprice'];
					$deductprice += $v['deductprice'];
					$deductcredit2 += $v['deductcredit2'];
					$isdiscountprice += $v['isdiscountprice'];
					$deductenough += $v['deductenough'];
					$merchdeductenough += $v['merchdeductenough'];
					$merchisdiscountprice += $v['merchisdiscountprice'];
					$changeprice += $v['changeprice'];
					$seckilldiscountprice += $v['seckilldiscountprice'];					
				}
			}
			$data["orderids"] = $orderids;
			$data["realprice"] = round($realprice, 2);
			$data["commission"] = $commission;
			$data["price"] = $price;
			$data["goodsprice"] = $goodsprice;
			$data["credit4price"] = $credit4price;
			$data["dispatchprice"] = $dispatchprice;
			$data["discountprice"] = $discountprice;
			$data["deductprice"] = $deductprice;
			$data["deductcredit2"] = $deductcredit2;
			$data["isdiscountprice"] = $isdiscountprice;
			$data["deductenough"] = $deductenough;
			$data["merchdeductenough"] = $merchdeductenough;
			$data["merchisdiscountprice"] = $merchisdiscountprice;
			$data["changeprice"] = $changeprice;
			$data["seckilldiscountprice"] = $seckilldiscountprice;
		}
		$data["orderprice"] = $data["goodsprice"] + $data["dispatchprice"] + $data["changeprice"];
		// $data["realprice"] = $data["orderprice"] - $data["merchdeductenough"] - $data["merchisdiscountprice"] - $merchcouponprice - $data["seckilldiscountprice"];
		if( $deduct_commission ) 
		{
			$data["realprice"] -= $data["commission"];
		}
		$list = array_merge($merch,$data);
		$list["realpricerate"] = ((100 - floatval($data["payrate"])) * $data["realprice"]) / 100;
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
		if( !empty($merch_data["deduct_commission"]) ) {
			$deduct_commission = 1;
		} else {
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
		$con = "o.id,u.merchname,u.payrate,o.price,o.goodsprice,o.dispatchprice,o.credit4price,discountprice," . "o.deductprice,o.deductcredit2,o.isdiscountprice,o.deductenough,o.changeprice,o.agentid,o.seckilldiscountprice," . "o.merchdeductenough,o.merchisdiscountprice,o.couponmerchid,o.couponprice,o.couponmerchid,o.ordersn,o.finishtime,o.merchapply,o.isluckbuy,o.luckbuydiscount";
		$sql = "select " . $con . " from " . tablename("shop_merch") . " u " . " left join " . tablename("shop_order") . " o on u.id=o.merchid" . " where 1 " . $condition;
		$order = Db::query($sql);
		$orderprice = 0;
		foreach( $order as &$list ) 
		{
			$merchcouponprice = 0;
			if( 0 < $list["couponmerchid"] ) 
			{
				$merchcouponprice = $list["couponprice"];
			}
			$list["commission"] = model("order")->getOrderCommission($list["id"], $list["agentid"]);
			$list["orderprice"] = $list["goodsprice"] + $list["dispatchprice"] + $list["changeprice"];
			if($list['isluckbuy'] == 1) {
				switch ($list['luckbuydiscount']) {
					case '7':
						$orderprice = round($list['goodsprice'] * 0.7, 2);
						break;
					case '6':
						$orderprice = round($list['goodsprice'] * 0.6, 2);
						break;
					case '5':
						$orderprice = round($list['goodsprice'] * 0.5, 2);
						break;
					
					default:
						$orderprice = round($list['goodsprice'] * 0.7, 2);
						break;
				}
			} else {
				$orderprice = $list['price'] + ($list['credit4price'] * 0.1);
			}
			$list["realprice"] = $orderprice - $list["merchdeductenough"] - $list["merchisdiscountprice"] - $merchcouponprice;
			if( $deduct_commission ) 
			{
				$list["realprice"] -= $list["commission"];
			}
			if( $applyid ) 
			{
				$item = self::getOneApply($applyid);
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
				$item = self::getMerchPriceList($merchid, $orderid, 1, $applyid);
				$data["realprice"] += $item["realprice"];
				$data["realpricerate"] += $item["realpricerate"];
				$data["orderprice"] += $item["orderprice"];
			}
		}
		return $data;
	}

	public function getPassApplyCredit($merchid, $orderids, $creditrate, $isbillcredit)
	{
		$data = array();
		$data['realprice'] = 0;
		$data['realpricerate'] = 0;
		$data['orderprice'] = 0;

		if (!empty($orderids)) {
			foreach ($orderids as $key => $orderid) {
				$item = self::getMerchCreditList($merchid, $orderid, 1, $creditrate, $isbillcredit);
				$data['realprice'] += $item['realprice'];
				$data['realpricerate'] += $item['realcreaterate'];
				$data['orderprice'] += $item['orderprice'];
				$data['credit'] += $item['credit'];
			}
		}

		return $data;
	}

	public function getMerchApplyTotals($merchid = 0)
	{
		$totals = array();
		$condition = ' 1 ';

		if (0 < $merchid) {
			$condition .= ' and merchid= ' . $merchid;
		}

		$totals['status1'] = Db::name('shop_merch_bill')->where($condition . ' and status = 1')->count();
		$totals['status2'] = Db::name('shop_merch_bill')->where($condition . ' and status = 2')->count();
		$totals['status3'] = Db::name('shop_merch_bill')->where($condition . ' and status = 3')->count();
		$totals['status_1'] = Db::name('shop_merch_bill')->where($condition . ' and status = -1')->count();
		return $totals;
	}

	/***
	 * 商户评分
	 * 权重：订单评价95%；系统评价5%
	 **/
	public static function getGrade($merchid = 0)
	{
		$totalcount = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid)->count();
		$level5 = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid . ' and level = 5')->count();
		$level4 = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid . ' and level = 4')->count();
		$level3 = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid . ' and level = 3')->count();
		$level2 = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid . ' and level = 2')->count();
		$level1 = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where('g.merchid= ' . $merchid . ' and level = 1')->count();
		$refund = Db::name('shop_order_refund')->where('merchid = ' . $merchid)->count();
		$levelpercent5 = $level5 / $totalcount;
		$levelpercent4 = $level4 / $totalcount;
		$levelpercent3 = $level3 / $totalcount;
		$levelpercent2 = $level2 / $totalcount;
		$levelpercent1 = $level1 / $totalcount;
		$totalscore = $levelpercent5 * 5 + $levelpercent4 * 4 + $levelpercent3 * 3 + $levelpercent2 * 2 + $levelpercent1 * 1 - $refund * 0.01;
		return array('totalscore'=>$totalscore,'totalcount'=>$totalcount,'levelpercent5'=>$levelpercent5,'levelpercent4'=>$levelpercent4,'levelpercent3'=>$levelpercent3,'levelpercent2'=>$levelpercent2,'levelpercent1'=>$levelpercent1);
	}

	/***
	 * 商户评分
	 * 权重：订单评价95%；系统评价5%
	 **/
	public static function setGrade($merchid = 0)
	{
		if(empty($merchid)) {
			return;
		}
		$data = self::getGrade($merchid);
		if($data['totalscore'] > 0) {
			Db::name('shop_merch')->where('id = ' . $merchid)->update(array('score'=>$data['totalscore']));
		}
		return true;
	}

	public function allPerms()
	{
		if (empty(self::$allPerms)) {
			$perms = array('shop' => self::perm_shop(), 'goods' => self::perm_goods(), 'order' => self::perm_order(), 'statistics' => self::perm_statistics(), 'perm' => self::perm_perm(), 'apply' => self::perm_apply(), 'taobao' => self::perm_taobao());
			self::$allPerms = $perms;
		}

		return self::$allPerms;
	}

	protected function perm_creditshop()
	{
		return array();
		return array(
			'text'    => model('plugin')->getName('creditshop'),
			'goods'   => array(
				'text'   => '商品',
				'main'   => '查看列表',
				'view'   => '查看详细',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('property' => 'edit')
			),
			'log'     => array('text' => '订单/记录', 'exchange' => '兑换记录', 'draw' => '抽奖记录', 'order' => '待发货', 'convey' => '待收货', 'finish' => '已完成', 'verifying' => '待核销', 'verifyover' => '已核销', 'verify' => '全部核销', 'detail' => '详情', 'doexchange' => '确认兑换-log', 'export' => '导出明细-log'),
			'comment' => array('text' => '评价管理', 'edit' => '回复评价', 'check' => '审核评价')
		);
	}

	protected function perm_shop()
	{
		return array(
		'text'          => '店铺管理',
		'dispatch'      => array(
			'text'   => '配送方式',
			'main'   => '查看列表',
			'view'   => '查看内容',
			'add'    => '添加-log',
			'edit'   => '修改-log',
			'delete' => '删除-log',
			'xxx'    => array('displayorder' => 'edit', 'enabled' => 'edit', 'setdefault' => 'edit')
			),
		'comment'       => array('text' => '评价', 'main' => '查看列表', 'add' => '添加-log', 'edit' => '编辑-log', 'post' => '回复-log', 'delete' => '删除-log'),
		'refundaddress' => array(
			'text'   => '退货地址',
			'main'   => '查看列表',
			'view'   => '查看内容',
			'add'    => '添加-log',
			'edit'   => '修改-log',
			'delete' => '删除-log',
			'xxx'    => array('setdefault' => 'edit')
			),
		'verify'        => array(
			'text'  => 'O2O核销',
			'saler' => array(
				'text'   => '店员管理',
				'main'   => '查看列表',
				'view'   => '查看内容',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('status' => 'edit')
				),
			'store' => array(
				'text'   => '门店管理',
				'main'   => '查看列表',
				'view'   => '查看内容',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('displayorder' => 'edit', 'status' => 'edit')
				)
			)
		);
	}

	protected function perm_taobao()
	{
		return array();
		return array(
			'text'      => '商品助手',
			'main'      => '获取宝贝',
			'jingdong'  => array('text' => '京东助手', 'main' => '获取宝贝'),
			'one688'    => array('text' => '1688宝贝助手', 'main' => '获取宝贝'),
			'taobaocsv' => array('text' => '淘宝CSV助手', 'main' => '获取宝贝'),
			'set'       => array('text' => '淘宝助手客户端', 'main' => '获取宝贝')
			);
	}

	protected function perm_goods()
	{
		return array(
			'text'     => '商品管理',
			'main'     => '浏览列表',
			'view'     => '查看详情',
			'add'      => '添加-log',
			'edit'     => '修改-log',
			'delete'   => '删除-log',
			'delete1'  => '彻底删除-log',
			'restore'  => '恢复到仓库-log',
			'xxx'      => array('status' => 'edit', 'property' => 'edit', 'change' => 'edit')
			);
	}

	protected function perm_sale()
	{
		$array = array(
			'text'   => '营销',
			'coupon' => array(
				'text'     => '优惠券管理',
				'view'     => '浏览',
				'add'      => '添加-log',
				'edit'     => '修改-log',
				'delete'   => '删除-log',
				'send'     => '发放-log',
				'set'      => '修改设置-log',
				'xxx'      => array('displayorder' => 'edit'),
				'category' => array('text' => '优惠券分类', 'main' => '查看', 'edit' => '修改-log'),
				'log'      => array('text' => '优惠券记录', 'main' => '查看', 'export' => '导出记录')
				)
			);
		$sale = array('enough' => '修改满额立减-log', 'enoughfree' => '修改满额包邮-log');
		$array = array_merge($array, $sale);
		return $array;
	}

	protected function perm_statistics()
	{
		return array(
			'text'            => '数据统计',
			'sale'            => array('text' => '销售统计', 'main' => '查看', 'export' => '导出-log'),
			'sale_analysis'   => array('text' => '销售指标', 'main' => '查看'),
			'order'           => array('text' => '订单统计', 'main' => '查看', 'export' => '导出-log'),
			'goods'           => array('text' => '商品销售明细', 'main' => '查看', 'export' => '导出-log'),
			'goods_rank'      => array('text' => '商品销售排行', 'main' => '查看', 'export' => '导出-log'),
			'goods_trans'     => array('text' => '商品销售转化率', 'main' => '查看', 'export' => '导出-log'),
			'member_cost'     => array('text' => '会员消费排行', 'main' => '查看', 'export' => '导出-log'),
			'member_increase' => array('text' => '会员增长趋势', 'main' => '查看')
		);
	}

	protected function perm_order()
	{
		return array(
			'text'      => '订单',
			'detail'    => array('text' => '订单详情', 'edit' => '编辑'),
			'list'      => array('text' => '订单管理', 'main' => '浏览全部订单', 'status_1' => '浏览关闭订单', 'status0' => '浏览待付款订单', 'status1' => '浏览已付款订单', 'status2' => '浏览已发货订单', 'status3' => '浏览完成的订单', 'status4' => '浏览退货申请订单', 'status5' => '浏览已退货订单'),
			'op'        => array(
				'text'          => '操作',
				'delete'        => '订单删除-log',
				'pay'           => '确认付款-log',
				'send'          => '发货-log',
				'sendcancel'    => '取消发货-log',
				'finish'        => '确认收货(快递单)-log',
				'verify'        => '确认核销(核销单)-log',
				'fetch'         => '确认取货(自提单)-log',
				'close'         => '关闭订单-log',
				'changeprice'   => '订单改价-log',
				'changeaddress' => '修改收货地址-log',
				'remarksaler'   => '订单备注-log',
				'paycancel'     => '订单取消付款-log',
				'fetchcancel'   => '订单取消取货-log',
				'changeexpress' => '修改快递状态',
				'refund'        => array('text' => '维权', 'main' => '维权信息', 'submit' => '提交维权申请')
				)
			);
	}

	protected function perm_perm()
	{
		return array(
			'text' => '权限系统',
			'log'  => array('text' => '操作日志', 'main' => '查看列表'),
			'role' => array(
				'text'   => '角色管理',
				'main'   => '查看列表',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('status' => 'edit', 'query' => 'main')
				),
			'user' => array(
				'text'   => '操作员管理',
				'main'   => '查看列表',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('status' => 'edit')
				)
			);
	}

	protected function perm_apply()
	{
		return array(
			'text'   => '提现',
			'detail' => array('text' => '提现详情', 'export' => '导出提现申请订单详情'),
			'list'   => array('text' => '提现管理', 'post' => '申请提现', 'status1' => '浏览待审核申请', 'status2' => '浏览待结算申请', 'status3' => '浏览已结算申请', 'export' => '导出申请')
			);
	}

	protected function perm_exhelper()
	{
		return array(
			'text'     => '快递助手',
			'print'    => array(
				'single' => array('text' => '单个打印', 'express' => '打印快递单-log', 'invoice' => '打印发货单-log', 'dosend' => '一键发货-log'),
				'batch'  => array('text' => '批量打印', 'express' => '打印快递单-log', 'invoice' => '打印发货单-log', 'dosend' => '一键发货-log')
				),
			'temp'     => array(
				'express' => array(
					'text'   => '快递单模板管理',
					'add'    => '添加-log',
					'edit'   => '修改-log',
					'delete' => '删除-log',
					'xxx'    => array('setdefault' => 'edit')
					),
				'invoice' => array(
					'text'   => '发货单模板管理',
					'add'    => '添加-log',
					'edit'   => '修改-log',
					'delete' => '删除-log',
					'xxx'    => array('setdefault' => 'edit')
					)
				),
			'sender'   => array(
				'text'   => '发货人信息管理',
				'main'   => '查看列表',
				'view'   => '查看',
				'add'    => '添加-log',
				'edit'   => '修改-log',
				'delete' => '删除-log',
				'xxx'    => array('setdefault' => 'edit')
				),
			'short'    => array('text' => '商品简称', 'main' => '查看', 'edit' => '修改-log'),
			'printset' => array('text' => '打印端口设置', 'main' => '查看', 'edit' => '修改-log')
		);
	}

	public static function getMerchs($merch_array)
	{
		$merchs = array();

		if (!empty($merch_array)) {
			foreach ($merch_array as $key => $value) {
				$merchid = $key;

				if (0 < $merchid) {
					$merchs[$merchid]['merchid'] = $merchid;
					$merchs[$merchid]['goods'] = $value['goods'];
					$merchs[$merchid]['ggprice'] = $value['ggprice'];
				}
			}
		}

		return $merchs;
	}

	public static function getUserTotals()
	{
		$totals = array('reg0' => Db::name('shop_merch_reg')->where('status=0')->count(), 'reg_1' => Db::name('shop_merch_reg')->where('status=-1')->count(), 'user0' => Db::name('shop_merch')->where('status=0')->count(), 'user1' => Db::name('shop_merch')->where('status=1')->count(), 'user2' => Db::name('shop_merch')->where('status=2')->count(), 'user3' => Db::name('shop_merch')->where('status=1 and TIMESTAMPDIFF(DAY,now(),FROM_UNIXTIME(accounttime))<=30')->count());
		return $totals;
	}

	public static function getClearTotals()
	{
		$totals = array('status0' => Db::name('shop_merch_clearing')->where('status = 0')->count(), 'status1' => Db::name('shop_merch_clearing')->where('status = 1')->count(), 'status2' => Db::name('shop_merch_clearing')->where('status = 2')->count());
		return $totals;
	}

	public static function getMerchCreditList($merchid, $orderid = 0, $flag = 0, $creditrate = 0, $isbillcredit = 1)
	{
		$merch_data = model('common')->getPluginset('merch');

		if (empty($merch_data)) {
			$merch_data = self::getPluginsetByMerch('merch');
		}

		$condition = ' u.id=' . $merchid . ' and o.status>0 ';

		switch ($flag) {
		case 0:
			$condition .= ' and o.merchapply <= 0';
			break;

		case 1:
			$condition .= ' and o.merchapply = 1';
			break;

		case 2:
			$condition .= ' and o.merchapply = 2';
			break;

		case 3:
			$condition .= ' and o.merchapply = 3';
			break;
		}

		if (!empty($orderid)) {
			$condition .= ' and o.id=' . $orderid . ' Limit 1';
		}

		$con = 'o.id,u.merchname,u.iscreditmoney,u.creditrate,u.iscreditmoney,dispatch, money, credit,o.logno as ordersn,o.createtime,o.time_finish,o.createtime' . ',o.merchapply';
		$sql = 'select ' . $con . ' from ' . tablename('shop_merch') . ' u ' . ' left join ' . tablename('shop_creditshop_log') . ' o on u.id=o.merchid' . (' where 1 ' . $condition);
		$order = Db::query($sql);
		$_order = array();

		if (!empty($order)) {
			foreach ($order as $k => $v) {
				if ($v['money'] + $v['dispatch'] == 0 && $v['iscreditmoney'] == 1) {
					continue;
				}

				$_order[] = $v;
			}
		}

		if (!empty($_order)) {
			foreach ($_order as &$list) {
				$list['orderprice'] = $list['dispatch'] + $list['money'];

				if ($isbillcredit == 1) {
					$list['realcreaterate'] = 0;
					$list['creditrate'] = $creditrate;
				}
				else if ($creditrate == 0) {
					@$list['realcreaterate'] = floor(floatval($list['credit'] / $list['creditrate']));
				}
				else {
					@$list['realcreaterate'] = floor(floatval($list['credit'] / $creditrate));
					$usecredit = $list['realcreaterate'] * $creditrate;
					$list['lave'] = $list['credit'] - $usecredit;
					$list['creditrate'] = $creditrate;
				}

				$list['realprice'] = $list['realcreaterate'] + $list['money'] + $list['dispatch'];
			}

			unset($list);

			if (!empty($orderid)) {
				return $_order[0];
			}

			return $_order;
		}
	}

	public function tempData($type, $merchid = 0)
	{
		$merchid = empty($merchid) ? $_GET['merchid'] : $merchid;
		$pindex = max(1, intval($_GET['page']));
		$psize = 20;
		$condition = ' type=' . $type . ' and merchid=' . $merchid;

		if (!empty($_GET['keyword'])) {
			$_GET['keyword'] = trim($_GET['keyword']);
			$condition .= ' AND expressname LIKE \'%' . trim($_GET['keyword']) . '%\'';
		}

		$sql = 'SELECT id,expressname,expresscom,isdefault FROM ' . tablename('shop_exhelper_express') . (' where  1 and ' . $condition . ' ORDER BY isdefault desc, id DESC LIMIT ') . ($pindex - 1) * $psize . ',' . $psize;
		$list = Db::query($sql);
		$total = Db::name('shop_exhelper_express')->where($condition)->count();
		$pager = pagination($total, $pindex, $psize);
		return array('list' => $list, 'total' => $total, 'pager' => $pager, 'type' => $type);
	}

	public function setDefault($id, $type, $merchid = 0)
	{
		$merchid = empty($merchid) ? $_GET['merchid'] : $merchid;
		$item = pdo_fetch('SELECT id,expressname,type FROM ' . tablename('shop_exhelper_express') . ' WHERE id=:id and type=:type AND uniacid=:uniacid and merchid=:merchid', array(':id' => $id, ':type' => $type, ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));

		if (!empty($item)) {
			pdo_update('shop_exhelper_express', array('isdefault' => 0), array('type' => $type, 'uniacid' => $_W['uniacid'], 'merchid' => $merchid));
			pdo_update('shop_exhelper_express', array('isdefault' => 1), array('id' => $id, 'merchid' => $merchid));

			if ($type == 1) {
				plog('merch.exhelper.temp.express.setdefault', '设置默认快递单 ID: ' . $item['id'] . '， 模板名称: ' . $item['expressname'] . ' ');
			}
			else {
				if ($type == 2) {
					plog('merch.exhelper.temp.invoice.setdefault', '设置默认发货单 ID: ' . $item['id'] . '， 模板名称: ' . $item['expressname'] . ' ');
				}
			}
		}
	}

	public function tempDelete($id, $type, $merchid = 0)
	{
		$merchid = empty($merchid) ? $_GET['merchid'] : $merchid;
		$items = pdo_fetchall('SELECT id,expressname FROM ' . tablename('shop_exhelper_express') . (' WHERE id in( ' . $id . ' ) and type=:type and uniacid=:uniacid and merchid=:merchid'), array(':type' => $type, ':uniacid' => $_W['uniacid'], ':merchid' => $merchid));

		foreach ($items as $item) {
			pdo_delete('shop_exhelper_express', array('id' => $item['id'], 'uniacid' => $_W['uniacid'], 'merchid' => $merchid));

			if ($type == 1) {
				plog('merch.exhelper.temp.express.delete', '删除 快递助手 快递单模板 ID: ' . $item['id'] . '， 模板名称: ' . $item['expressname'] . ' ');
			}
			else {
				if ($type == 2) {
					plog('merch.exhelper.temp.invoice.delete', '删除 快递助手 发货单模板 ID: ' . $item['id'] . '， 模板名称: ' . $item['expressname'] . ' ');
				}
			}
		}
	}

	public function getTemp($merchid = 0)
	{
		$merchid = empty($merchid) ? $_GET['merchid'] : $merchid;
		$temp_sender = pdo_fetchall('SELECT id,isdefault,sendername,sendertel FROM ' . tablename('shop_exhelper_senduser') . ' WHERE uniacid=:uniacid and merchid=:merchid order by isdefault desc ', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
		$temp_express = pdo_fetchall('SELECT id,type,isdefault,expressname FROM ' . tablename('shop_exhelper_express') . ' WHERE type=1 and uniacid=:uniacid and merchid=:merchid order by isdefault desc ', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
		$temp_invoice = pdo_fetchall('SELECT id,type,isdefault,expressname FROM ' . tablename('shop_exhelper_express') . ' WHERE type=2 and uniacid=:uniacid and merchid=:merchid order by isdefault desc ', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
		return array('temp_sender' => $temp_sender, 'temp_express' => $temp_express, 'temp_invoice' => $temp_invoice);
	}

	public function updateOrderPay()
	{
		$sql = 'select id,parentid from ' . tablename('shop_order') . ' where parentid>0 and status>0 and paytype=0 ';
		$list = Db::query($sql);

		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$params[':orderid'] = $v['parentid'];
				$item = Db::name('shop_order')->where('id=' . $v['parentid'] . ' and status>0 and paytype>0')->field('paytype')->find();

				if (0 < $item['paytype']) {
					Db::name('shop_order')->where('id = ' . $v['id'])->update(array('paytype' => $item['paytype']));
				}
			}
		}
	}

	public function getPluginsetByMerch($key = '')
	{
		$set = Db::name('shop_sysset')->where('1')->find();

		if (empty($set)) {
			$set = array();
		}

		$allset = iunserializer($set['plugins']);
		$retsets = array();

		if (!empty($key)) {
			if (is_array($key)) {
				foreach ($key as $k) {
					$retsets[$k] = isset($allset[$k]) ? $allset[$k] : array();
				}
			} else {
				$retsets = isset($allset[$key]) ? $allset[$key] : array();
			}

			return $retsets;
		}

		return $allset;
	}

	public function getPluginList($merchid = 0)
	{
		$category = model('plugin')->getList(1);
		$has_plugins = array();
		$perm = m('perm');
		if (p('taobao') && $perm && $perm->is_perm_plugin('taobao')) {
			$has_plugins[] = 'taobao';
		}

		if (p('creditshop') && $perm && $perm->is_perm_plugin('creditshop')) {
			$has_plugins[] = 'creditshop';
		}

		if (!empty($merchid)) {
			$item = self::getListUserOne($merchid);

			if (!empty($item['pluginset'])) {
				$pluginset = iunserializer($item['pluginset']);
			}
		}

		$plugins_list = array();
		$plugins_all = array();

		foreach ($category as $key => $value) {
			foreach ($value['plugins'] as $k => $v) {
				$plugins_all[$v['identity']] = $v;

				if (in_array($v['identity'], $has_plugins)) {
					$is_has = 1;

					if (!empty($pluginset)) {
						if ($pluginset[$v['identity']]['close'] == 1) {
							$is_has = 0;
						}
					}

					if ($is_has) {
						$plugins_list[] = $v;
					}
				}
			}
		}

		$data = array();
		$data['plugins_list'] = $plugins_list;
		$data['plugins_all'] = $plugins_all;
		return $data;
	}

	public function CheckPlugin($plugin, $merchid = 0, $flag = 0)
	{
		$plugins_data = self::getPluginList($merchid);
		$plugins_list = $plugins_data['plugins_list'];
		$check = 0;

		foreach ($plugins_list as $k => $v) {
			if ($v['identity'] == $plugin) {
				$check = 1;
				break;
			}
		}

		if (empty($flag)) {
			if ($check == 0) {
				exit('您没有该应用的权限!');
			}
		}
		else {
			return $check;
		}
	}

}