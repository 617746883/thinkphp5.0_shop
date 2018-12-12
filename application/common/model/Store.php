<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Store extends \think\Model
{
	public static function getStoreTotals()
	{
		$totals = array('reg0' => Db::name('shop_store_reg')->where('status',0)->count(), 'reg_1' => Db::name('shop_store_reg')->where('status',-1)->count(), 'user0' => Db::name('shop_store')->where('status',0)->count(), 'user1' => Db::name('shop_store')->where('status',1)->count(), 'user2' => Db::name('shop_store')->where('status',2)->count(), 'user3' => Db::name('shop_store')->where('status',3)->count());
		return $totals;
	}

	public static function getStoreOrderTotals($type = 0)
	{
		$condition = ' 1 and o.merchid>0 ';

		if ($type == 0) {
			$condition .= ' and o.status >= 0 ';
		} else if ($type == 1) {
			$condition .= ' and o.status >= 1 ';
		} else if ($type == 3) {
			$condition .= ' and o.status = 3 ';
		}

		$condition .= ' and o.deleted = 0 ';
		$price = Db::name('shop_order')
			->alias('o')
			->join('shop_store u','u.id = o.merchid','left')
			->where($condition)
			->sum('o.price');
		$totalmoney = round($price, 2);
		$totalcount = Db::name('shop_order')
			->alias('o')
			->join('shop_store u','u.id = o.merchid','left')
			->where($condition)
			->count();
		$data = array();
		$data['totalmoney'] = $totalmoney;
		$data['totalcount'] = $totalcount;
		return $data;
	}

	public static function getGroups()
	{
		return Db::name('shop_store_group')->where('status',1)->order('isdefault','desc')->order('id','asc')->select();
	}

	public static function getStoreOrderTotalPrice($merchid)
	{
		$data = array();
		$list = self::getStorePrice($merchid, 1);
		$data['status0'] = $list['realprice'];
		$orderids = $list['orderids'];
		$condition = ' and uniacid=:uniacid and merchid=:merchid';
		$data['commission'] = round($list['commission'], 2);
		$sql = 'select *  from ' . tablename('ewei_shop_merch_bill') . ' where 1 ' . $condition . ' and status=1';
		$status1 = pdo_fetchall($sql, $params);
		$status1price = 0;
		$status1orderids = array();

		foreach ($status1 as $k => $v ) {
			$status1price += $v['realprice'];

			if (!(empty($status1orderids))) {
				$status1orderids = array_merge($status1orderids, iunserializer($v['orderids']));
			}

		}

		$data['status1'] = round($status1price, 2);
		$data['commission1'] = 0;

		if (!(empty($status1orderids))) {
			$status1orderids = array_diff($status1orderids, $orderids);
		}


		if (0 < count($status1orderids)) {
			$status1order = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_order') . ' WHERE id in(' . join(',', $status1orderids) . ') AND uniacid=' . $_W['uniacid']);
			$commission1 = 0;

			if (!(empty($status1order))) {
				foreach ($status1order as $k => $v ) {
					$commission1 += m('order')->getOrderCommission($v['id'], $v['agentid']);
				}
			}


			$data['commission1'] = round($commission1, 2);
		}


		$sql = 'select sum(realprice) as totalmoney from ' . tablename('ewei_shop_merch_bill') . ' where 1 ' . $condition . ' and status=2';
		$status2 = pdo_fetchall($sql, $params);
		$status2price = 0;
		$status2orderids = array();

		foreach ($status2 as $k => $v ) {
			$status2price += $v['realprice'];

			if (!(empty($status2orderids))) {
				$status2orderids = array_merge($status2orderids, iunserializer($v['orderids']));
			}

		}

		$data['status2'] = round($status2price, 2);
		$data['commission2'] = 0;

		if (!(empty($status2orderids))) {
			$status2orderids = array_diff($status2orderids, $orderids);
		}


		if (0 < count($status2orderids)) {
			$status2order = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_order') . ' WHERE id in(' . join(',', $status2orderids) . ') AND uniacid=' . $_W['uniacid']);
			$commission2 = 0;

			if (!(empty($status2order))) {
				foreach ($status2order as $k => $v ) {
					$commission2 += m('order')->getOrderCommission($v['id'], $v['agentid']);
				}
			}


			$data['commission2'] = round($commission2, 2);
		}


		$sql = 'select *  from ' . tablename('ewei_shop_merch_bill') . ' where 1 ' . $condition . ' and status=3';
		$status3 = pdo_fetchall($sql, $params);
		$status3price = 0;
		$status3orderids = array();

		foreach ($status3 as $k => $v ) {
			$status3price += $v['finalprice'];

			if (!(empty($status3orderids))) {
				$status3orderids = array_merge($status3orderids, iunserializer($v['orderids']));
			}

		}

		$data['status3'] = round($status3price, 2);
		$data['commission3'] = 0;

		if (!(empty($status3orderids))) {
			$status3orderids = array_diff($status3orderids, $orderids);
		}


		if (0 < count($status3orderids)) {
			$status3order = pdo_fetchall('SELECT id FROM ' . tablename('ewei_shop_order') . ' WHERE id in(' . join(',', $status3orderids) . ') AND uniacid=' . $_W['uniacid']);
			$commission3 = 0;

			if (!(empty($status3order))) {
				foreach ($status3order as $k => $v ) {
					$commission3 += m('order')->getOrderCommission($v['id'], $v['agentid']);
				}
			}


			$data['commission3'] = round($commission3, 2);
		}


		return $data;
	}

	public static function getCategory()
	{
		return Db::name('shop_store_category')->where('status',1)->select();
	}

	public function getSet($name = '', $merchid = 0, $refresh = false)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : intval($merchid)));

		$allset = $this->refreshSet($merchid);
		return ($name ? $allset[$name] : $allset);
	}

	public function refreshSet($merchid = 0)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : $merchid));
		
		$merch_set = Db::name('shop_store')->where('id',$merchid)->field('sets')->find();
		$allset = iunserializer($merch_set['sets']);

		if (!(is_array($allset))) {
			$allset = array();
		}

		return $allset;
	}

	public static function getMerchs($merch_array)
	{
		$merchs = array();

		if (!(empty($merch_array))) {
			foreach ($merch_array as $key => $value ) {
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

	public static function checkMaxMerchUser($type = 0)
	{
		$totals = $this->getAllUserTotals();
		$max_merch = $this->getMaxMerchUser();
		$flag = 0;
		if (0 < $max_merch) {
			if ($max_merch <= $totals) {
				if ($type == 1) {
					$flag = 1;
				} else {
					show_json(0, '已经达到最大商户数量,不能再添加商户.');
				}
			}
		}
		return $flag;
	}

	public static function getAllUserTotals()
	{
		$totals = Db::name('shop_store')->count();
		return $totals;
	}

	public function getListUserOne($merchid)
	{
		$merchid = intval($merchid);

		if ($merchid) {
			$merch = Db::name('shop_store')->where('id',$merchid)->find();
			return $merch;
		}
		return false;
	}

	public function getListUser($list, $return = 'all')
	{
		if (!(is_array($list))) {
			return self::getListUserOne($list);
		}
		$shopset = model('common')->getSysset();
		$merch = array();

		foreach ($list as $value) {
			$merchid = $value['merchid'];

			if (empty($merchid)) {
				$merchid = 0;
			}

			if (empty($merch[$merchid])) {
				$merch[$merchid] = array();
			}

			array_push($merch[$merchid], $value);
		}

		if (!(empty($merch))) {
			$merch_ids = array_keys($merch);
			$merch_user = Db::name('shop_store')->where('id','in',implode(',', $merch_ids))->select();
			$all = array('merch' => $merch, 'merch_user' => $merch_user);
			return ($return == 'all' ? $all : $all[$return]);
		}

		return array();
	}

}