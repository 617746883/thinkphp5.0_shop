<?php
/**
 * 商户订单
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Order extends Base
{
	public function index()
	{
		return $this->fetch('order/index');
	}

	public function ajaxgettotals() 
	{
		$merch = $this->merch;
		$totals = model('merch')->getOrderTotals($merch['id']);
		$result = (empty($totals) ? array( ) : $totals);
		show_json(1, $result);
	}

	public function ajaxorder() 
	{
		$day = (int) $_GET["day"];
		$order = $this->selectOrderPrice($day);
		unset($order["fetchall"]);
		$allorder = $this->selectOrderPrice($day, true);
		unset($allorder["fetchall"]);
		$avg = $this->selectOrderPrice($day, true, true);
		unset($allorder["fetchall"]);
		$order = array( "order_count" => $order["count"], "order_price" => $order["price"], "allorder_count" => $allorder["count"], "allorder_price" => $allorder["price"], "avg" => $avg["avg"] );
		show_json(1, array( "order" => $order ));
	}

	public function ajaxtransaction() 
	{
		$orderPrice = $this->selectOrderPrice(7);
		$transaction = $this->selectTransaction($orderPrice["fetchall"], 7);
		if( empty($transaction) ) 
		{
			for( $i = 7; 1 <= $i; $i-- ) 
			{
				$transaction["price"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
				$transaction["count"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
			}
		}
		$allorderPrice = $this->selectOrderPrice(7, true);
		$alltransaction = $this->selectTransaction($allorderPrice["fetchall"], 7, true);
		if( empty($transaction) ) 
		{
			for( $i = 7; 1 <= $i; $i-- ) 
			{
				$alltransaction["price"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
				$alltransaction["count"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
			}
		}
		echo json_encode(array( "price_key" => array_keys($transaction["price"]), "price_value" => array_values($transaction["price"]), "count_value" => array_values($transaction["count"]), "allprice_value" => array_values($alltransaction["price"]), "allcount_value" => array_values($alltransaction["count"]) ));
	}

	protected function selectOrderPrice($day = 0, $is_all = false, $is_avg = false) 
	{
		$merch = $this->merch;
		$day = (int) $day;
		if( $day != 0 ) 
		{
			if( $day == 30 ) 
			{
				$yest = date("Y-m-d");
				$createtime1 = strtotime(date("Y-m-d", strtotime("-30 day")));
				$createtime2 = strtotime((string) $yest . " 23:59:59");
			}
			else 
			{
				if( $day == 7 ) 
				{
					$yest = date("Y-m-d");
					$createtime1 = strtotime(date("Y-m-d", strtotime("-7 day")));
					$createtime2 = strtotime((string) $yest . " 23:59:59");
				}
				else 
				{
					$yesterday = strtotime("-1 day");
					$yy = date("Y", $yesterday);
					$ym = date("m", $yesterday);
					$yd = date("d", $yesterday);
					$createtime1 = strtotime((string) $yy . "-" . $ym . "-" . $yd . " 00:00:00");
					$createtime2 = strtotime((string) $yy . "-" . $ym . "-" . $yd . " 23:59:59");
				}
			}
		}
		else 
		{
			$createtime1 = strtotime(date("Y-m-d", time()));
			$createtime2 = (strtotime(date("Y-m-d", time())) + 3600 * 24) - 1;
		}
		$time = "paytime";
		$where = " and (( status > 0 and (paytime between " . $createtime1 . " and " . $createtime2 . ")) or ((createtime between " . $createtime1 . " and " . $createtime2 . " ) and status>=0 and paytype=3))";
		if( !empty($is_all) ) 
		{
			$time = "createtime";
			$where = " and createtime between " . $createtime1 . " and " . $createtime2;
		}
		if( !empty($is_avg) ) 
		{
			$time = "paytime";
			$where = " and (status >0 and (paytime between " . $createtime1 . " and " . $createtime2 . "))";
		}
		$sql = "select id,price,mid," . $time . "  from " . tablename("shop_order") . " where merchid = " . $merch['id'] . " and ismr=0 and isparent=0  and deleted=0 " . $where;
		$pdo_res = Db::query($sql);
		$price = 0;
		$avg = 0;
		$member = array( );
		foreach( $pdo_res as $arr ) 
		{
			$price += $arr["price"];
			$member[] = $arr["mid"];
		}
		if( !empty($is_avg) ) 
		{
			$member_num = count(array_unique($member));
			$avg = (empty($member_num) ? 0 : round($price / $member_num, 2));
		}
		$result = array( "price" => round($price, 2), "count" => count($pdo_res), "avg" => $avg, "fetchall" => $pdo_res );
		return $result;
	}

	protected function selectTransaction(array $pdo_fetchall, $days = 7, $is_all = false) 
	{
		$transaction = array( );
		$days = (int) $days;
		if( !empty($pdo_fetchall) ) 
		{
			for( $i = $days; 1 <= $i; $i-- ) 
			{
				$transaction["price"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
				$transaction["count"][date("Y-m-d", time() - $i * 3600 * 24)] = 0;
			}
			if( empty($is_all) ) 
			{
				foreach( $pdo_fetchall as $key => $value ) 
				{
					if( array_key_exists(date("Y-m-d", $value["paytime"]), $transaction["price"]) ) 
					{
						$transaction["price"][date("Y-m-d", $value["paytime"])] += $value["price"];
						$transaction["count"][date("Y-m-d", $value["paytime"])] += 1;
					}
				}
			}
			else 
			{
				foreach( $pdo_fetchall as $key => $value ) 
				{
					if( array_key_exists(date("Y-m-d", $value["createtime"]), $transaction["price"]) ) 
					{
						$transaction["price"][date("Y-m-d", $value["createtime"])] += $value["price"];
						$transaction["count"][date("Y-m-d", $value["createtime"])] += 1;
					}
				}
			}
			return $transaction;
		}
		else 
		{
			return array( );
		}
	}

	protected function orderData($status = '') 
	{
		$merch_user = $this->merch;
		$pindex = max(1, intval($_GET['page']));
		$psize = 20;

		if ($st == 'main') {
			$st = '';
		} else {
			$st = '.' . $st;
		}

		$sendtype = !isset($_GET['sendtype']) ? 0 : $_GET['sendtype'];
		$merchid = $merch_user['id'];
		$condition = ' o.merchid = ' . $merchid . ' and o.deleted=0 and o.isparent=0';
		$paras = $paras1 = array(':uniacid' => $uniacid, ':merchid' => $merchid);
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		$searchtime = trim($_GET['searchtime']);
		if (!empty($searchtime) && is_array($_GET['time']) && in_array($searchtime, array('create', 'pay', 'send', 'finish'))) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND o.' . $searchtime . 'time >= ' . $starttime . ' AND o.' . $searchtime . 'time <= ' . $endtime;
		}

		if ($_GET['paytype'] != '') {
			if ($_GET['paytype'] == '2') {
				$condition .= ' AND ( o.paytype =21 or o.paytype=22 or o.paytype=23 )';
			}
			else if ($_GET['paytype'] == '4') {
				$condition .= ' AND o.paytype = 3 AND is_cashier = 1 ';
			}
			else {
				$condition .= ' AND o.paytype =' . intval($_GET['paytype']);
			}
		}

		if (!empty($_GET['searchfield']) && !empty($_GET['keyword'])) {
			$searchfield = trim(strtolower($_GET['searchfield']));
			$keyword = trim($_GET['keyword']);
			$sqlcondition = '';

			if ($searchfield == 'ordersn') {
				$condition .= ' AND locate(\'' . $keyword . '\',o.ordersn)>0';
			}
			else if ($searchfield == 'member') {
				$condition .= ' AND (locate(\'' . $keyword . '\',m.realname)>0 or locate(\'' . $keyword . '\',m.mobile)>0 or locate(\'' . $keyword . '\',m.nickname)>0)';
				$priceCondition = ' AND (nickname LIKE \'' . $_GET['keyword'] . '%\' OR realname LIKE \'' . $_GET['keyword'] . '%\' OR mobile LIKE \'' . $_GET['keyword'] . '%\') ';
			}
			else if ($searchfield == 'address') {
				$condition .= ' AND ( locate(\'' . $keyword . '\',a.realname)>0 or locate(\'' . $keyword . '\',a.mobile)>0 or locate(\'' . $keyword . '\',o.carrier)>0)';
				$priceCondition = ' AND (a.realname LIKE \'' . $_GET['keyword'] . '%\' OR a.mobile LIKE \'' . $_GET['keyword'] . '%\')';
			}
			else if ($searchfield == 'location') {
				$condition .= ' AND ( locate(\'' . $keyword . '\',a.province)>0 or locate(\'' . $keyword . '\',a.city)>0 or locate(\'' . $keyword . '\',a.area)>0 or locate(\'' . $keyword . '\',a.address)>0)';
				$priceCondition = ' AND (a.province LIKE \'' . $_GET['keyword'] . '%\' OR a.city LIKE \'' . $_GET['keyword'] . '%\' OR a.area LIKE \'' . $_GET['keyword'] . '%\') ';
			}
			else if ($searchfield == 'expresssn') {
				$condition .= ' AND locate(\'' . $keyword . '\',o.expresssn)>0';
			}
			else if ($searchfield == 'saler') {
				$condition .= ' AND (locate(\'' . $keyword . '\',sm.realname)>0 or locate(\'' . $keyword . '\',sm.mobile)>0 or locate(\'' . $keyword . '\',sm.nickname)>0 or locate(\'' . $keyword . '\',s.salername)>0 )';
			}
			else if ($searchfield == 'store') {
				$condition .= ' AND (locate(\'' . $keyword . '\',store.storename)>0)';
				$sqlcondition = ' left join ' . tablename('shop_store') . ' store on store.id = o.verifystoreid and store.uniacid=o.uniacid';
			}
			else if ($searchfield == 'goodstitle') {
				$sqlcondition = ' inner join ( select DISTINCT(og.orderid) from ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_goods') . (' g on g.id=og.goodsid where og.uniacid = \'' . $uniacid . '\' and (locate(\'' . $keyword . '\',g.title)>0)) gs on gs.orderid=o.id');
			} else {
				if ($searchfield == 'goodssn') {
					$sqlcondition = ' inner join ( select DISTINCT(og.orderid) from ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_goods') . (' g on g.id=og.goodsid where og.uniacid = \'' . $uniacid . '\' and (((locate(\'' . $keyword . '\',g.goodssn)>0)) or (locate(\'' . $keyword . '\',og.goodssn)>0))) gs on gs.orderid=o.id');
				}
			}
		}

		$statuscondition = '';

		if ($status !== '') {
			if ($status == '-1') {
				$statuscondition = ' AND o.status=-1 and (o.refundtime=0 or o.refundstate=3)';
				$priceStatus = ' AND status=-1 and (refundtime=0 or refundstate=3)';
			}
			else if ($status == '4') {
				$statuscondition = ' AND (o.refundstate>0 and o.refundid<>0 or (o.refundtime=0 and o.refundstate=3))';
				$priceStatus = ' AND (refundstate>0 and refundid<>0 or (o.refundtime<>0 and o.refundstate=3))';
			}
			else if ($status == '5') {
				$statuscondition = ' AND o.refundtime<>0';
				$priceStatus = ' AND refundtime<>0';
			}
			else if ($status == '1') {
				$statuscondition = ' AND ( o.status = 1 or (o.status=0 and o.paytype=3) )';
				$priceStatus = ' AND ( status = 1 or (status=0 and paytype=3) )';
			}
			else if ($status == '0') {
				$statuscondition = ' AND o.status = 0 and o.paytype<>3';
				$priceStatus = ' AND status = 0 and paytype<>3';
			}
			else {
				$statuscondition = ' AND o.status = ' . intval($status);
				$priceStatus = ' AND o.status = ' . intval($status);
			}
		}

		$agentid = intval($_GET['agentid']);
		$p = m('commission');
		$level = 0;

		if ($p) {
			$cset = model('commission')->getSet();
			$level = intval($cset['level']);
		}

		$olevel = intval($_GET['olevel']);
		if (!empty($agentid) && 0 < $level) {
			$agent = model('commission')->getInfo($agentid, array());

			if (!empty($agent)) {
				$agentLevel = model('commission')->getLevel($agentid);
			}

			if (empty($olevel)) {
				if (1 <= $level) {
					$condition .= ' and  ( o.agentid=' . intval($_GET['agentid']);
				}

				if (2 <= $level && 0 < $agent['level2']) {
					$condition .= ' or o.agentid in( ' . implode(',', array_keys($agent['level1_agentids'])) . ')';
				}

				if (3 <= $level && 0 < $agent['level3']) {
					$condition .= ' or o.agentid in( ' . implode(',', array_keys($agent['level2_agentids'])) . ')';
				}

				if (1 <= $level) {
					$condition .= ')';
				}
			}
			else if ($olevel == 1) {
				$condition .= ' and  o.agentid=' . intval($_GET['agentid']);
			}
			else if ($olevel == 2) {
				if (0 < $agent['level2']) {
					$condition .= ' and o.agentid in( ' . implode(',', array_keys($agent['level1_agentids'])) . ')';
				}
				else {
					$condition .= ' and o.agentid in( 0 )';
				}
			}
			else {
				if ($olevel == 3) {
					if (0 < $agent['level3']) {
						$condition .= ' and o.agentid in( ' . implode(',', array_keys($agent['level2_agentids'])) . ')';
					} else {
						$condition .= ' and o.agentid in( 0 )';
					}
				}
			}
		}

		$sql = 'select o.* , a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea,a.address as aaddress,a.street as astreet,d.dispatchname,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,sm.id as salerid,sm.nickname as salernickname,s.salername,r.rtype,r.status as rstatus from ' . tablename('shop_order') . ' o' . ' left join ' . tablename('shop_order_refund') . ' r on r.id =o.refundid ' . ' left join ' . tablename('member') . ' m on m.id=o.mid ' . ' left join ' . tablename('shop_member_address') . ' a on a.id=o.addressid ' . ' left join ' . tablename('shop_dispatch') . ' d on d.id = o.dispatchid ' . ' left join ' . tablename('shop_saler') . ' s on s.mid = o.verifyoperatorid and s.merchid=o.merchid' . ' left join ' . tablename('member') . ' sm on sm.id = s.mid ' . (' ' . $sqlcondition . ' where ' . $condition . ' ' . $statuscondition . ' GROUP BY o.id ORDER BY o.createtime DESC,o.status DESC  ');

		if (empty($_GET['export'])) {
			$sql .= 'LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		}

		$list = fetchall($sql);
		$paytype = array(
			0  => array('css' => 'default', 'name' => '未支付'),
			1  => array('css' => 'danger', 'name' => '余额支付'),
			11 => array('css' => 'default', 'name' => '后台付款'),
			2  => array('css' => 'danger', 'name' => '在线支付'),
			21 => array('css' => 'success', 'name' => '微信支付'),
			22 => array('css' => 'warning', 'name' => '支付宝支付'),
			23 => array('css' => 'warning', 'name' => '银联支付'),
			3  => array('css' => 'primary', 'name' => '货到付款'),
			4  => array('css' => 'primary', 'name' => '收银台现金收款')
			);
		$orderstatus = array(
			-1 => array('css' => 'default', 'name' => '已关闭'),
			0  => array('css' => 'danger', 'name' => '待付款'),
			1  => array('css' => 'info', 'name' => '待发货'),
			2  => array('css' => 'warning', 'name' => '待收货'),
			3  => array('css' => 'success', 'name' => '已完成')
			);

		foreach ($list as &$value) {
			$s = $value['status'];
			$pt = $value['paytype'];
			$value['statusvalue'] = $s;
			$value['statuscss'] = $orderstatus[$value['status']]['css'];
			$value['status'] = $orderstatus[$value['status']]['name'];
			if ($pt == 3 && empty($value['statusvalue'])) {
				$value['statuscss'] = $orderstatus[1]['css'];
				$value['status'] = $orderstatus[1]['name'];
			}

			if ($s == 1) {
				if ($value['isverify'] == 1) {
					$value['status'] = '待使用';
				}
				else {
					if (empty($value['addressid'])) {
						$value['status'] = '待取货';
					}
				}
			}

			if ($s == 3) {
				if (!empty($value['refundtime'])) {
					$value['status'] = '已维权';
				}
			}

			$value['paytypevalue'] = $pt;
			$value['css'] = $paytype[$pt]['css'];
			$value['paytype'] = $paytype[$pt]['name'];
			$value['dispatchname'] = empty($value['addressid']) ? '自提' : $value['dispatchname'];

			if (empty($value['dispatchname'])) {
				$value['dispatchname'] = '快递';
			}

			if ($pt == 3) {
				$value['dispatchname'] = '货到付款';
			}
			else if ($value['isverify'] == 1) {
				$value['dispatchname'] = '线下核销';
			}
			else if ($value['isvirtual'] == 1) {
				$value['dispatchname'] = '虚拟物品';
			}
			else {
				if (!empty($value['virtual'])) {
					$value['dispatchname'] = '虚拟物品(卡密)<br/>自动发货';
				}
			}

			if ($value['dispatchtype'] == 1 || !empty($value['isverify']) || !empty($value['virtual']) || !empty($value['isvirtual'])) {
				$value['address'] = '';
				$carrier = iunserializer($value['carrier']);

				if (is_array($carrier)) {
					$value['addressdata']['realname'] = $value['realname'] = $carrier['carrier_realname'];
					$value['addressdata']['mobile'] = $value['mobile'] = $carrier['carrier_mobile'];
				}
			}
			else {
				$address = iunserializer($value['address']);
				$isarray = is_array($address);
				$value['realname'] = $isarray ? $address['realname'] : $value['arealname'];
				$value['mobile'] = $isarray ? $address['mobile'] : $value['amobile'];
				$value['province'] = $isarray ? $address['province'] : $value['aprovince'];
				$value['city'] = $isarray ? $address['city'] : $value['acity'];
				$value['area'] = $isarray ? $address['area'] : $value['aarea'];
				$value['address'] = $isarray ? $address['address'] : $value['aaddress'];
				$value['street'] = $isarray ? $address['street'] : $value['astreet'];
				$value['address_province'] = $value['province'];
				$value['address_city'] = $value['city'];
				$value['address_area'] = $value['area'];
				$value['address_street'] = $value['street'];
				$value['address_address'] = $value['address'];
				$value['address'] = $value['province'] . ' ' . $value['city'] . ' ' . $value['area'] . ' ' . $value['address'];
				$value['addressdata'] = array('realname' => $value['realname'], 'mobile' => $value['mobile'], 'address' => $value['address']);
			}

			$commission1 = -1;
			$commission2 = -1;
			$commission3 = -1;
			$m1 = false;
			$m2 = false;
			$m3 = false;
			if (!empty($level) && empty($agentid)) {
				if (!empty($value['agentid'])) {
					$m1 = model('member')->getMember($value['agentid']);
					$commission1 = 0;

					if (!empty($m1['agentid'])) {
						$m2 = model('member')->getMember($m1['agentid']);
						$commission2 = 0;

						if (!empty($m2['agentid'])) {
							$m3 = model('member')->getMember($m2['agentid']);
							$commission3 = 0;
						}
					}
				}
			}

			if (!empty($agentid)) {
				$magent = model('member')->getMember($agentid);
			}

			$order_goods = fetchall('select g.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.changeprice,og.oldprice,og.commission1,og.commission2,og.commission3,og.commissions,op.specs,og.single_refundid,og.single_refundstate,og.id as ogid,og.nocommission from ' . tablename('shop_order_goods') . ' og ' . ' left join ' . tablename('shop_goods') . ' g on g.id=og.goodsid ' . ' left join ' . tablename('shop_goods_option') . ' op on og.optionid = op.id ' . ' where og.orderid=' . $value['id'] . ' order by og.single_refundstate desc ');
			$goods = '';
			$is_singlerefund = false;

			foreach ($order_goods as &$og) {
				if (!$is_singlerefund && ($og['single_refundstate'] == 1 || $og['single_refundstate'] == 2)) {
					$is_singlerefund = true;
				}

				if (!empty($og['specs'])) {
					$thumb = model('goods')->getSpecThumb($og['specs']);

					if (!empty($thumb)) {
						$og['thumb'] = $thumb;
					}
				}

				if (!empty($level) && empty($agentid) && empty($og['nocommission'])) {
					$commissions = iunserializer($og['commissions']);

					if (!empty($m1)) {
						if (is_array($commissions)) {
							$commission1 += isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
						}
						else {
							$c1 = iunserializer($og['commission1']);
							$l1 = $p->getLevel($m1['openid']);
							$commission1 += isset($c1['level' . $l1['id']]) ? $c1['level' . $l1['id']] : $c1['default'];
						}
					}

					if (!empty($m2)) {
						if (is_array($commissions)) {
							$commission2 += isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
						}
						else {
							$c2 = iunserializer($og['commission2']);
							$l2 = $p->getLevel($m2['openid']);
							$commission2 += isset($c2['level' . $l2['id']]) ? $c2['level' . $l2['id']] : $c2['default'];
						}
					}

					if (!empty($m3)) {
						if (is_array($commissions)) {
							$commission3 += isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
						}
						else {
							$c3 = iunserializer($og['commission3']);
							$l3 = $p->getLevel($m3['openid']);
							$commission3 += isset($c3['level' . $l3['id']]) ? $c3['level' . $l3['id']] : $c3['default'];
						}
					}
				}

				$goods .= '' . $og['title'] . '';

				if (!empty($og['optiontitle'])) {
					$goods .= ' 规格: ' . $og['optiontitle'];
				}

				if (!empty($og['option_goodssn'])) {
					$og['goodssn'] = $og['option_goodssn'];
				}

				if (!empty($og['option_productsn'])) {
					$og['productsn'] = $og['option_productsn'];
				}

				if (!empty($og['goodssn'])) {
					$goods .= ' 商品编号: ' . $og['goodssn'];
				}

				if (!empty($og['productsn'])) {
					$goods .= ' 商品条码: ' . $og['productsn'];
				}

				$goods .= ' 单价: ' . $og['price'] / $og['total'] . ' 折扣后: ' . $og['realprice'] / $og['total'] . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . ' 折扣后: ' . $og['realprice'] . '';
			}

			unset($og);
			if (!empty($level) && empty($agentid)) {
				$value['commission1'] = $commission1;
				$value['commission2'] = $commission2;
				$value['commission3'] = $commission3;
			}

			$value['goods'] = set_medias($order_goods, 'thumb');
			$value['goods_str'] = $goods;
			if (!empty($agentid) && 0 < $level) {
				$commission_level = 0;

				if ($value['agentid'] == $agentid) {
					$value['level'] = 1;
					$level1_commissions = fetchall('select commission1,commissions  from ' . tablename('shop_order_goods') . ' og ' . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid ' . ' where og.orderid=' . $value['id'] . ' and o.agentid= ' . $agentid . ' ');

					foreach ($level1_commissions as $c) {
						$commission = iunserializer($c['commission1']);
						$commissions = iunserializer($c['commissions']);

						if (empty($commissions)) {
							$commission_level += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
						} else {
							$commission_level += isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
						}
					}
				} else if (in_array($value['agentid'], array_keys($agent['level1_agentids']))) {
					$value['level'] = 2;

					if (0 < $agent['level2']) {
						$level2_commissions = fetchall('select commission2,commissions  from ' . tablename('shop_order_goods') . ' og ' . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid ' . ' where og.orderid=' . $value['id'] . ' and  o.agentid in ( ' . implode(',', array_keys($agent['level1_agentids'])) . ')  ');

						foreach ($level2_commissions as $c) {
							$commission = iunserializer($c['commission2']);
							$commissions = iunserializer($c['commissions']);

							if (empty($commissions)) {
								$commission_level += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
							}
							else {
								$commission_level += isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
							}
						}
					}
				}
				else {
					if (in_array($value['agentid'], array_keys($agent['level2_agentids']))) {
						$value['level'] = 3;

						if (0 < $agent['level3']) {
							$level3_commissions = fetchall('select commission3,commissions from ' . tablename('shop_order_goods') . ' og ' . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid ' . ' where og.orderid=' . $value['id'] . ' and  o.agentid in ( ' . implode(',', array_keys($agent['level2_agentids'])) . ')  ');

							foreach ($level3_commissions as $c) {
								$commission = iunserializer($c['commission3']);
								$commissions = iunserializer($c['commissions']);

								if (empty($commissions)) {
									$commission_level += isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
								}
								else {
									$commission_level += isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
								}
							}
						}
					}
				}

				$value['commission'] = $commission_level;
			}

			$value['is_singlerefund'] = $is_singlerefund;
			if($value['isluckbuy'] == 1) {
                $lotterylog = Db::name('shop_lottery_log')->where(' orderid = ' . $value['id'])->find();
                $value['lotterylog'] = $lotterylog;
            }
		}

		unset($value);

		if ($_GET['export'] == 1) {
			model('shop')->plog('order.op.export', '导出订单');
			$columns = array(
				array('title' => '订单编号', 'field' => 'ordersn', 'width' => 24),
				array('title' => '粉丝昵称', 'field' => 'nickname', 'width' => 12),
				array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => '会员等级', 'field' => 'levelname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 24),
				array('title' => '会员手机手机号', 'field' => 'mmobile', 'width' => 12),
				array('title' => '收货姓名(或自提人)', 'field' => 'realname', 'width' => 12),
				array('title' => '联系电话', 'field' => 'mobile', 'width' => 12),
				array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
				array('title' => '', 'field' => 'address_city', 'width' => 12),
				array('title' => '', 'field' => 'address_area', 'width' => 12),
				array('title' => '', 'field' => 'address_street', 'width' => 12),
				array('title' => '', 'field' => 'address_address', 'width' => 12),
				array('title' => '卖家备注', 'field' => 'remarksaler', 'width' => 24),
				array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
				array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
				array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
				array('title' => '商品数量', 'field' => 'goods_total', 'width' => 12),
				array('title' => '商品单价(折扣前)', 'field' => 'goods_price1', 'width' => 12),
				array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
				array('title' => '商品价格(折扣后)', 'field' => 'goods_rprice1', 'width' => 12),
				array('title' => '商品价格(折扣后)', 'field' => 'goods_rprice2', 'width' => 12),
				array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
				array('title' => '配送方式', 'field' => 'dispatchname', 'width' => 12),
				array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'deductprice', 'width' => 12),
				array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
				array('title' => '满额立减', 'field' => 'deductenough', 'width' => 12),
				array('title' => '优惠券优惠', 'field' => 'couponprice', 'width' => 12),
				array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
				array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
				array('title' => '应收款', 'field' => 'price', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
				array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),
				array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
				array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36)
				);
			if (!empty($agentid) && 0 < $level) {
				$columns[] = array('title' => '分销级别', 'field' => 'level', 'width' => 24);
				$columns[] = array('title' => '分销佣金', 'field' => 'commission', 'width' => 24);
			}

			foreach ($list as &$row) {
				$row['realname'] = str_replace('=', '', $row['realname']);
				$row['nickname'] = str_replace('=', '', $row['nickname']);
				$row['ordersn'] = $row['ordersn'] . ' ';

				if (0 < $row['deductprice']) {
					$row['deductprice'] = '-' . $row['deductprice'];
				}

				if (0 < $row['deductcredit2']) {
					$row['deductcredit2'] = '-' . $row['deductcredit2'];
				}

				if (0 < $row['deductenough']) {
					$row['deductenough'] = '-' . $row['deductenough'];
				}

				if ($row['changeprice'] < 0) {
					$row['changeprice'] = '-' . $row['changeprice'];
				}
				else {
					if (0 < $row['changeprice']) {
						$row['changeprice'] = '+' . $row['changeprice'];
					}
				}

				if ($row['changedispatchprice'] < 0) {
					$row['changedispatchprice'] = '-' . $row['changedispatchprice'];
				}
				else {
					if (0 < $row['changedispatchprice']) {
						$row['changedispatchprice'] = '+' . $row['changedispatchprice'];
					}
				}

				if (0 < $row['couponprice']) {
					$row['couponprice'] = '-' . $row['couponprice'];
				}

				$row['expresssn'] = $row['expresssn'] . ' ';
				$row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
				$row['paytime'] = !empty($row['paytime']) ? date('Y-m-d H:i:s', $row['paytime']) : '';
				$row['sendtime'] = !empty($row['sendtime']) ? date('Y-m-d H:i:s', $row['sendtime']) : '';
				$row['finishtime'] = !empty($row['finishtime']) ? date('Y-m-d H:i:s', $row['finishtime']) : '';
				$row['salerinfo'] = '';
				$row['storeinfo'] = '';
				$levelname = '普通会员';
				$mlevel = m('member')->getLevel($row['openid']);

				if ($mlevel) {
					$levelname = $mlevel['levelname'];
				}

				$row['levelname'] = $levelname;

				if (com('verify')) {
					$verifyinfo = iunserializer($row['verifyinfo']);

					if (!empty($row['verifyoperatorid'])) {
						$saler = model('member')->getMember($row['verifyoperatorid']);
						$merch_saler = fetch('select id,salername from ' . tablename('shop_saler') . ' where mid=' . $row['verifyoperatorid'] . ' and merchid = ' . $merchid . ' limit 1 ');
						$saler['salername'] = isset($merch_saler['salername']) ? $merch_saler['salername'] : '';
						$row['salerinfo'] = '[' . isset($merch_saler['id']) ? $merch_saler['id'] : '' . ']' . $saler['salername'] . '(' . $row['nickname'] . ')';
					}

					if (!empty($row['verifystoreid'])) {
						$row['storeinfo'] = Db::name('shop_store')->where('id = ' . $row['verifystoreid'])->value('storename');
					}

					if ($row['isverify']) {
						if (is_array($verifyinfo)) {
							if (empty($row['dispatchtype'])) {
								$v = $verifyinfo[0];
								if ($v['verified'] || $row['verifytype'] == 1) {
									$v['storename'] = Db::name('shop_store')->where('id = ' . $row['verifystoreid'])->value('storename');

									if (empty($v['storename'])) {
										$v['storename'] = '总店';
									}

									$row['storeinfo'] = $v['storename'];
									$v['nickname'] = Db::name('member')->where('id = ' . $row['verifyoperatorid'])->value('nickname');
									$v['salername'] = Db::name('shop_saler')->where('mid = ' . $row['verifyoperatorid'] . ' and merchid = ' . $merchid)->value('salername');
									$row['salerinfo'] = $v['salername'] . '(' . $v['nickname'] . ')';
								}

								unset($v);
							}
						}
					}
				}
			}

			unset($row);
			$exportlist = array();

			foreach ($list as &$r) {
				$ogoods = $r['goods'];
				unset($r['goods']);

				foreach ($ogoods as $k => $g) {
					if (0 < $k) {
						$r['ordersn'] = '';
						$r['realname'] = '';
						$r['mobile'] = '';
						$r['openid'] = '';
						$r['nickname'] = '';
						$r['mrealname'] = '';
						$r['mmobile'] = '';
						$r['address'] = '';
						$r['address_province'] = '';
						$r['address_city'] = '';
						$r['address_area'] = '';
						$r['address_street'] = '';
						$r['address_address'] = '';
						$r['paytype'] = '';
						$r['dispatchname'] = '';
						$r['dispatchprice'] = '';
						$r['goodsprice'] = '';
						$r['status'] = '';
						$r['createtime'] = '';
						$r['sendtime'] = '';
						$r['finishtime'] = '';
						$r['expresscom'] = '';
						$r['expresssn'] = '';
						$r['deductprice'] = '';
						$r['deductcredit2'] = '';
						$r['deductenough'] = '';
						$r['changeprice'] = '';
						$r['changedispatchprice'] = '';
						$r['price'] = '';
					}

					$r['goods_title'] = $g['title'];
					$r['goods_goodssn'] = $g['goodssn'];
					$r['goods_optiontitle'] = $g['optiontitle'];
					$r['goods_total'] = $g['total'];
					$r['goods_price1'] = $g['price'] / $g['total'];
					$r['goods_price2'] = $g['realprice'] / $g['total'];
					$r['goods_rprice1'] = $g['price'];
					$r['goods_rprice2'] = $g['realprice'];
					$exportlist[] = $r;
				}
			}

			unset($r);
			model('excel')->export($exportlist, array('title' => '订单数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
		}

		if ($searchfield == 'member') {
			$openidArr = fetchall('SELECT id FROM ' . tablename('member') . ' WHERE 1 ' . $priceCondition);

			if (!empty($openidArr)) {
				foreach ($openidArr as $openid) {
					$openids[] = $openid['id'];
				}

				$inOpenid = '\'' . implode('\',\'', $openids) . '\'';
				$orderPrice = fetch('SELECT count(1) as count,sum(price) as sumprice FROM ' . tablename('shop_order') . ' WHERE merchid = ' . $merchid . ' AND deleted=0 AND isparent=0 AND mid IN (' . $inOpenid . ')' . $priceStatus);
			}
			else {
				$orderPrice['sumprice'] = 0;
			}

			$totalmoney = $orderPrice['sumprice'];
			$total = $orderPrice['count'];
		}
		else if ($searchfield == 'address') {
			$orderPrice = fetch('SELECT count(1) as count,sum(o.price) as sumprice FROM ' . tablename('shop_order') . ' o left join ' . tablename('shop_member_address') . ' a on o.addressid = a.id WHERE o.deleted=0 AND o.isparent=0 AND o.merchid = ' . $merchid . $priceCondition . $statuscondition);
			$totalmoney = $orderPrice['sumprice'];
			$total = $orderPrice['count'];

			if ($orderPrice['count'] == 0) {
				$totalmoney = 0;
			}
		}
		else if ($searchfield == 'location') {
			$orderPrice = fetch('SELECT count(1) as count,sum(o.price) as sumprice FROM ' . tablename('shop_order') . ' o left join ' . tablename('shop_member_address') . ' a on o.addressid = a.id WHERE o.deleted=0 AND o.isparent=0 AND o.merchid = ' . $merchid . $priceCondition . $statuscondition);
			$totalmoney = $orderPrice['sumprice'];
			$total = $orderPrice['count'];

			if ($orderPrice['count'] == 0) {
				$totalmoney = 0;
			}
		} else {
			$t = fetch('SELECT count(DISTINCT(o.id)) as count,sum(o.price) as sumprice FROM ' . tablename('shop_order') . ' o ' . ' left join ' . tablename('shop_order_refund') . ' r on r.id =o.refundid ' . ' left join ' . tablename('shop_saler') . ' s on s.mid = o.verifyoperatorid and s.merchid=o.merchid' . ' left join ' . tablename('member') . ' sm on sm.id = s.mid ' . (' ' . $sqlcondition . ' WHERE ' . $condition . ' ' . $statuscondition), $paras);
			$total = $t['count'];
			$totalmoney = $t['sumprice'];
		}

		$pager = pagination2($total, $pindex, $psize);
		$stores = fetchall('select id,storename from ' . tablename('shop_store') . ' where merchid = ' . $merchid);
		$r_type = array('退款', '退货退款', '换货');
		$this->assign(['list'=>$list,'pager'=>$pager,'level'=>$level,'agentid'=>$agentid,'searchfield'=>$searchfield,'r_type'=>$r_type,'starttime'=>$starttime,'endtime'=>$endtime,'is_openmerch'=>$is_openmerch,'keyword'=>$keyword,'searchtime'=>$searchtime,'paytype'=>$paytype,'act'=>strtolower(Request::instance()->action())]);
		return $this->fetch('order/list');
	}

	public function olist1()
	{
		$orderData = $this->orderData(1);
		return $orderData;
	}

	public function olist2()
	{
		$orderData = $this->orderData(2);
		return $orderData;
	}

	public function olist3()
	{
		$orderData = $this->orderData(3);
		return $orderData;
	}

	public function olist0()
	{
		$orderData = $this->orderData(0);
		return $orderData;
	}

	public function olist6()
	{
		$orderData = $this->orderData(6);
		return $orderData;
	}

	public function olist_1()
	{
		$orderData = $this->orderData(-1);
		return $orderData;
	}

	public function olist_all()
	{
		$orderData = $this->orderData();
		return $orderData;
	}

	public function detail()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$item = Db::name('shop_order')->where('id',$id)->find();
		$item['statusvalue'] = $item['status'];
		$item['paytypevalue'] = $item['paytype'];
		$isonlyverifygoods = model('order')->checkisonlyverifygoods($item['id']);
		$order_goods = array();

		if (0 < $item['sendtype']) {
			$order_goods = Db::name('shop_order_goods')->where('orderid',$id)->where('sendtime','>',0)->where('sendtype','>',0)->group('sendtype')->order('sendtime','desc')->select();

			foreach ($order_goods as $key => $value) {
				$order_goods[$key]['goods'] = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid=' . $id . ' and og.sendtype=' . $value['sendtype'])->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell,og.realprice')->select();
			}

			$item['sendtime'] = $order_goods[0]['sendtime'];
		}

		$shopset = model('common')->getSysset('shop');

		if (empty($item)) {
			$this->error('抱歉，订单不存在!', referer(), 'error');
		}
		$member = model('member')->getMember($item['mid']);
		$dispatch = Db::name('shop_dispatch')->where('id',$item['dispatchid'])->where('merchid',0)->find();
		if (empty($item['addressid'])) {
			$user = unserialize($item['carrier']);
		} else {
			$user = iunserializer($item['address']);

			if (!is_array($user)) {
				$user = Db::name('shop_member_address')->where('id',$item['addressid'])->find();
			}

			$address_info = $user['address'];
			// $user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
			$user['address'] = $user['street'] . ' ' . $user['address'];
			$item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
		}
		$refund = Db::name('shop_order_refund')->where('orderid',$item['id'])->order('id','asc')->find();
		$goods = Db::name('shop_order_goods')->alias('o')->join('shop_goods g','o.goodsid=g.id','left')->where('o.orderid',$id)->field('g.*, o.goodssn as option_goodssn, o.productsn as option_productsn,o.total,g.type,o.optionname,o.optionid,o.price as orderprice,o.realprice,o.changeprice,o.oldprice,o.seckill,o.seckill_taskid,o.seckill_roomid')->select();
		$is_merch = false;
		foreach ($goods as &$r) {
			$r['seckill_task'] = false;

			if ($r['seckill']) {

			}

			if (!empty($r['option_goodssn'])) {
				$r['goodssn'] = $r['option_goodssn'];
			}

			if (!empty($r['option_productsn'])) {
				$r['productsn'] = $r['option_productsn'];
			}

			$r['marketprice'] = $r['orderprice'] / $r['total'];

			if (!empty($r['merchid'])) {
				$is_merch = true;
			}
		}

		unset($r);
		$item['goods'] = $goods;
		$coupon = model('coupon')->getCouponByDataID($item['couponid']);
		$verifyinfo = iunserializer($item['verifyinfo']);

		if (!empty($item['verifyoperatorid'])) {
			$saler = model('member')->getMember($item['verifyoperatorid']);

			if (empty($item['merchid'])) {
				$saler['salername'] = Db::name('shop_saler')->where('mid',$item['verifyoperatorid'])->value('salername');
			}
			else {
				$saler['salername'] = Db::name('shop_saler')->where('mid',$item['verifyoperatorid'])->value('salername');
			}
		}

		if (!empty($item['verifystoreid'])) {
			if (empty($item['merchid'])) {
				$store = array();
			}
			else {
				$store = array();
			}
		}

		if ($item['isverify']) {
			if (is_array($verifyinfo)) {
				if (empty($item['dispatchtype'])) {
					foreach ($verifyinfo as &$v) {
						if ($v['verified'] || ($item['verifytype'] == 1)) {
							if (empty($item['merchid'])) {
								$v['storename'] = $shopset['shop']['name'];
							}
							else {
								$v['storename'] = Db::name('shop_store')->where('id',$item['merchid'])->value('merchname');
							}

							if (empty($v['storename'])) {
								$v['storename'] = '总店';
							}

							$v['nickname'] = Db::name('member')->where('id',$v['verifyoperatorid'])->value('nickname');

							if (empty($item['merchid'])) {
								$v['salername'] = Db::name('shop_saler')->where('mid',$v['verifyoperatorid'])->value('salername');
							}
							else {
								$v['salername'] = Db::name('shop_saler')->where('mid',$v['verifyoperatorid'])->value('salername');
							}
						}
					}
					unset($v);
				}
			}
		}
		if($item['isluckbuy'] == 1) {
            $lotterylog = Db::name('shop_lottery_log')->where(' orderid = ' . $item['id'])->find();
            $item['lotterylog'] = $lotterylog;
        }
		$this->assign(['item'=>$item,'member'=>$member,'dispatch'=>$dispatch,'user'=>$user,'coupon'=>$coupon,'isonlyverifygoods'=>$isonlyverifygoods]);
		return $this->fetch('order/detail');
	}

	protected function opData()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$item = Db::name('shop_order')->where('id',$id)->where('merchid',$merch['id'])->find();
		if (empty($item)) {
			show_json(0, '未找到订单!');
		}
		$order_goods = Db::name('shop_order_goods')->alias('og')
			->join('shop_goods g','g.id=og.goodsid','left')
			->where('og.orderid',$item['id'])
			->field('og.id,og.goodsid,og.total,g.totalcnf,og.refundid,og.rstate,og.refundtime,og.prohibitrefund,g.cannotrefund,g.total as goodstotal,g.sales,g.salesreal,g.type')
			->select();
		$goods = fetchall('select single_refundstate from ' . tablename('shop_order_goods') . ' where orderid=' . $item['id']);
		$is_singlerefund = false;

		foreach ($goods as $og) {
			if (!$is_singlerefund && ($og['single_refundstate'] == 1 || $og['single_refundstate'] == 2)) {
				$is_singlerefund = true;
				break;
			}
		}

		return array('id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund);
	}

	public function delete()
	{
		$status = intval(input('status'));
		$orderid = intval(input('id'));
		$merch = $this->merch;
		Db::name('shop_order')->where('id = ' . $orderid . ' and merchid = ' . $merch['id'])->update(array('deleted' => 1));
		model('shop')->mplog('order.op.delete', '订单删除 ID: ' . $orderid);
		show_json(1, url('merch/order/olist_all', array('status' => $status)));
	}

	public function changeprice()
	{
		$opdata = $this->opData();
		extract($opdata);
		$merch_user = $merch;

		if (100 <= $item['ordersn2']) {
			$item['ordersn2'] = 0;
		}

		if (Request::instance()->isPost()) {
			if (empty($merch_user['changepricechecked'])) {
				show_json(0, '您没有改价的权限!');
			}

			if (0 < $item['parentid']) {
				$parent_order = array();
				$parent_order['id'] = $item['parentid'];
			}

			$changegoodsprice = $_POST['changegoodsprice'];

			if (!is_array($changegoodsprice)) {
				show_json(0, '未找到改价内容!');
			}

			$changeprice = 0;

			foreach ($changegoodsprice as $ogid => $change) {
				$changeprice += floatval($change);
			}

			$dispatchprice = floatval($_POST['changedispatchprice']);

			if ($dispatchprice < 0) {
				$dispatchprice = 0;
			}

			$orderprice = $item['price'] + $changeprice;
			$changedispatchprice = 0;

			if ($dispatchprice != $item['dispatchprice']) {
				$changedispatchprice = $dispatchprice - $item['dispatchprice'];
				$orderprice += $changedispatchprice;
			}

			if ($orderprice < 0) {
				show_json(0, '订单实际支付价格不能小于0元!');
			}

			foreach ($changegoodsprice as $ogid => $change) {
				$og = fetch('select price,realprice from ' . tablename('shop_order_goods') . ' where id=' . $ogid . ' and merchid = ' . $merch['id'] . ' limit 1');

				if (!empty($og)) {
					$realprice = $og['realprice'] + $change;

					if ($realprice < 0) {
						show_json(0, '单个商品不能优惠到负数');
					}
				}
			}

			$ordersn2 = $item['ordersn2'] + 1;

			if (99 < $ordersn2) {
				show_json(0, '超过改价次数限额');
			}

			$orderupdate = array();

			if ($orderprice != $item['price']) {
				$orderupdate['price'] = $orderprice;
				$orderupdate['ordersn2'] = $item['ordersn2'] + 1;

				if (0 < $item['parentid']) {
					$parent_order['price_change'] = $orderprice - $item['price'];
				}
			}

			$orderupdate['changeprice'] = $item['changeprice'] + $changeprice;

			if ($dispatchprice != $item['dispatchprice']) {
				$orderupdate['dispatchprice'] = $dispatchprice;
				$orderupdate['changedispatchprice'] = $item['changedispatchprice'] + $changedispatchprice;

				if (0 < $item['parentid']) {
					$parent_order['dispatch_change'] = $changedispatchprice;
				}
			}

			if (!empty($orderupdate)) {
				Db::name('shop_order')->where('id = ' . $item['id'])->update($orderupdate);
			}

			if (0 < $item['parentid']) {
				if (!empty($parent_order)) {
					m('order')->changeParentOrderPrice($parent_order);
				}
			}

			foreach ($changegoodsprice as $ogid => $change) {
				$og = fetch('select price,realprice,changeprice from ' . tablename('shop_order_goods') . ' where id=' . $ogid . ' and merchid = ' . $merch['id'] . ' limit 1');

				if (!empty($og)) {
					$realprice = $og['realprice'] + $change;
					$changeprice = $og['changeprice'] + $change;
					Db::name('shop_order_goods')->where('id = ' . $ogid)->update(array('realprice' => $realprice, 'changeprice' => $changeprice));
				}
			}

			if (0 < abs($changeprice)) {
				$pluginc = m('commission');

				if ($pluginc) {
					model('commission')->calculate($item['id'], true);
				}
			}

			model('shop')->mplog('order.op.changeprice', '订单号： ' . $item['ordersn'] . ' <br/> 价格： ' . $item['price'] . ' -> ' . $orderprice);
			model('notice')->sendOrderChangeMessage($item['mid'], array('title' => '订单金额', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $item['price'], 'data' => round($orderprice, 2), 'type' => 1), 'orderstatus');
			show_json(1);
		}

		$order_goods = fetchall('select og.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.oldprice from ' . tablename('shop_order_goods') . ' og ' . ' left join ' . tablename('shop_goods') . ' g on g.id=og.goodsid ' . ' where og.merchid = ' . $merch['id'] . ' and og.orderid=' . $item['id']);

		if (empty($item['addressid'])) {
			$user = unserialize($item['carrier']);
			$item['addressdata'] = array('realname' => $user['carrier_realname'], 'mobile' => $user['carrier_mobile']);
		} else {
			$user = iunserializer($item['address']);

			if (!is_array($user)) {
				$user = fetch('SELECT * FROM ' . tablename('shop_member_address') . ' WHERE id = ' . $item['addressid']);
			}

			$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['address'];
			$item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
		}
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund,'user'=>$user]);
		echo $this->fetch('order/op/changeprice');
	}

	public function pay($a = array(), $b = array())
	{
		$opdata = $this->opData();
		extract($opdata);
		show_json(1);

		if (1 < $item['status']) {
			show_json(0, '订单已付款，不需重复付款！');
		}

		if (!empty($item['virtual']) && m('virtual')) {
			model('virtual')->pay($item);
		}
		else {
			Db::name('shop_order')->where('id = ' . $item['id'] . ' and merchid = ' . $merch['id'])->update(array('status' => 1, 'paytype' => 11, 'paytime' => time()));
			model('order')->setStocksAndCredits($item['id'], 1);
			model('notice')->sendOrderMessage($item['id']);

			if (m('coupon')) {
				model('coupon')->sendcouponsbytask($item['id']);
			}

			if (m('coupon') && !empty($item['couponid'])) {
				model('coupon')->backConsumeCoupon($item['id']);
			}

			if (m('commission')) {
				model('commission')->checkOrderPay($item['id']);
			}
		}

		model('shop')->mplog('order.op.pay', '订单确认付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
		show_json(1);
	}

	public function close()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法关闭订单！');
		}

		if ($item['status'] == -1) {
			show_json(0, '订单已关闭，无需重复关闭！');
		} else {
			if (1 <= $item['status']) {
				show_json(0, '订单已付款，不能关闭！');
			}
		}

		if (Request::instance()->isPost()) {
			if (0 < $item['parentid']) {
				show_json(1);
			}

			if (!empty($item['transid'])) {
			}

			$time = time();
			if (0 < $item['refundstate'] && !empty($item['refundid'])) {
				$change_refund = array();
				$change_refund['status'] = -1;
				$change_refund['refundtime'] = $time;
				Db::name('shop_order_refund')->where(array('id' => $item['refundid'], 'merchid' => $merch['id']))->update($change_refund);
			}

			if (0 < $item['deductcredit']) {
				model('member')->setCredit($item['mid'], 'credit1', $item['deductcredit'], array('0', $shopset['shop']['name'] . ('购物返还抵扣积分 积分: ' . $item['deductcredit'] . ' 抵扣金额: ' . $item['deductprice'] . ' 订单号: ' . $item['ordersn'])));
			}

			model('order')->setDeductCredit2($item);
			if (m('coupon') && !empty($item['couponid'])) {
				model('coupon')->returnConsumeCoupon($item['id']);
			}

			model('order')->setStocksAndCredits($item['id'], 2);
			Db::name('shop_order')->where(array('id' => $item['id']))->update(array('status' => -1, 'refundstate' => 0, 'canceltime' => $time, 'remarkclose' => $_POST['remark']));
			model('shop')->mplog('order.op.close', '订单关闭 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
		}
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund]);
		echo $this->fetch('order/op/close');
	}

	public function paycancel()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法取消付款！');
		}

		if ($item['status'] != 1) {
			show_json(0, '订单未付款，不需取消！');
		}

		if (Request::instance()->isPost()) {
			model('order')->setStocksAndCredits($item['id'], 2);
			Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('status' => 0, 'cancelpaytime' => time()));
			model('shop')->mplog('order.op.paycancel', '订单取消付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
		}
	}

	public function finish()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法确认收货！');
		}

		$merch_user = $_W['merch_user'];

		if (empty($merch_user['finishchecked'])) {
			show_json(0, '您没有确认收货的权限!');
		}

		Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('status' => 3, 'finishtime' => time()));
		model('member')->upgradeLevel($item['mid'], $item['id']);
		model('order')->setStocksAndCredits($item['id'], 3);
		model('order')->setGiveBalance($item['id'], 1);
		model('notice')->sendOrderMessage($item['id']);

		if (m('coupon')) {
			model('coupon')->sendcouponsbytask($item['id']);
		}

		if (!empty($item['couponid'])) {
			model('coupon')->backConsumeCoupon($item['id']);
		}

		if (m('commission')) {
			model('commission')->checkOrderFinish($item['id']);
		}

		model('shop')->mplog('order.op.finish', '订单完成 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
		show_json(1);
	}

	public function fetchcancel()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法取消取货！');
		}

		if ($item['status'] != 3) {
			show_json(0, '订单未取货，不需取消！');
		}

		Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('status' => 1, 'finishtime' => 0));
		model('shop')->mplog('order.op.fetchcancel', '订单取消取货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
		show_json(1);
	}

	public function sendcancel()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法取消发货！');
		}

		if ($item['status'] != 2) {
			show_json(0, '订单未发货，不需取消发货！');
		}

		if (Request::instance()->isPost()) {
			if (!empty($item['transid'])) {
			}

			$remark = trim($_POST['remark']);

			if (!empty($item['remarksend'])) {
				$remark = $item['remarksend'] . '' . $remark;
			}

			Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('status' => 1, 'sendtime' => 0, 'remarksend' => $remark));

			if ($item['paytype'] == 3) {
				model('order')->setStocksAndCredits($item['id'], 2);
			}

			model('shop')->mplog('order.op.sendcancel', '订单取消发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 原因: ' . $remark);
			show_json(1);
		}
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund]);
		echo $this->fetch('order/op/sendcancel');
	}

	public function ofetch()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法确认取货！');
		}

		if ($item['status'] != 1) {
			show_json('订单未付款，无法确认取货！');
		}

		$time = time();
		$d = array('status' => 3, 'sendtime' => $time, 'finishtime' => $time);

		if ($item['isverify'] == 1) {
			$d['verified'] = 1;
			$d['verifytime'] = $time;
			$d['verifyopenid'] = '';
			$verifyinfo = iunserializer($item['verifyinfo']);

			foreach ($verifyinfo as &$v) {
				$v['verified'] = 1;
				$v['verifytime'] = $time;
			}

			unset($v);
			$d['verifyinfo'] = iserializer($verifyinfo);
		}

		Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update($d);
		if (!empty($item['refundid'])) {
			$refund = fetch('select * from ' . tablename('shop_order_refund') . ' where id=' . $item['refundid'] . ' limit 1');

			if (!empty($refund)) {
				Db::name('shop_order_refund')->where(array('id' => $item['refundid']))->update(array('status' => -1));
				Db::name('shop_order')->where(array('id' => $item['id']))->update(array('refundstate' => 0));
			}
		}

		model('order')->setGiveBalance($item['id'], 1);
		model('member')->upgradeLevel($item['mid'], $item['id']);
		model('notice')->sendOrderMessage($item['id']);

		if (m('commission')) {
			model('commission')->checkOrderFinish($item['id']);
		}

		model('shop')->mplog('order.op.fetch', '订单确认取货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
		show_json(1);
	}

	public function send()
	{
		$opdata = $this->opData();
		extract($opdata);

		if ($is_singlerefund) {
			show_json(0, '订单商品存在维权，无法发货！');
		}

		if (empty($item['addressid'])) {
			show_json(0, '无收货地址，无法发货！');
		}

		if ($item['paytype'] != 3) {
			if ($item['status'] != 1) {
				show_json(0, '订单未付款，无法发货！');
			}
		}

		if (Request::instance()->isPost()) {
			if (!empty($_POST['isexpress']) && empty($_POST['expresssn'])) {
				show_json(0, '请输入快递单号！');
			}

			if (!empty($item['transid'])) {
			}

			$time = time();
			Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('status' => 2, 'express' => trim($_POST['express']), 'expresscom' => trim($_POST['expresscom']), 'expresssn' => trim($_POST['expresssn']), 'sendtime' => $time));

			if (!empty($item['refundid'])) {
				$refund = pdo_fetch('select * from ' . tablename('shop_order_refund') . ' where id=:id limit 1', array(':id' => $item['refundid']));

				if (!empty($refund)) {
					Db::name('shop_order_refund')->where(array('id' => $item['refundid']))->update(array('status' => -1, 'endtime' => $time));
					Db::name('shop_order')->where(array('id' => $item['id']))->update(array('refundstate' => 0));
				}
			}

			if ($item['paytype'] == 3) {
				model('order')->setStocksAndCredits($item['id'], 1);
			}

			model('notice')->sendOrderMessage($item['id']);
			model('shop')->mplog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . $_POST['expresscom'] . ' 快递单号: ' . $_POST['expresssn']);
			show_json(1);
		}

		$address = iunserializer($item['address']);

		if (!is_array($address)) {
			$address = pdo_fetch('SELECT * FROM ' . tablename('shop_member_address') . ' WHERE id = :id and uniacid=:uniacid', array(':id' => $item['addressid'], ':uniacid' => $_W['uniacid']));
		}

		$express_list = model('express')->getExpressList();
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund, 'address' => $address, 'express_list' => $express_list]);
		echo $this->fetch('order/op/send');
	}

	public function remarksaler()
	{
		$opdata = $this->opData();
		extract($opdata);

		if (Request::instance()->isPost()) {
			Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update(array('remarksaler' => $_POST['remark']));
			model('shop')->mplog('order.op.remarksaler', '订单备注 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 备注内容: ' . $_POST['remark']);
			show_json(1);
		}
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund]);
		echo $this->fetch('order/op/remarksaler');
	}

	public function changeexpress()
	{
		$opdata = $this->opData();
		extract($opdata);
		$edit_flag = 1;

		if (Request::instance()->isPost()) {
			$express = $_POST['express'];
			$expresscom = $_POST['expresscom'];
			$expresssn = trim($_POST['expresssn']);

			if (empty($id)) {
				$ret = '参数错误！';
				show_json(0, $ret);
			}

			if (!empty($expresssn)) {
				$change_data = array();
				$change_data['express'] = $express;
				$change_data['expresscom'] = $expresscom;
				$change_data['expresssn'] = $expresssn;
				Db::name('shop_order')->where(array('id' => $item['id'], 'merchid' => $merch['id']))->update($change_data);
				model('shop')->mplog('order.op.changeexpress', '修改快递状态 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 快递公司: ' . $expresscom . ' 快递单号: ' . $expresssn);
				show_json(1);
			}
			else {
				show_json(0, '请填写快递单号！');
			}
		}

		$address = iunserializer($item['address']);

		if (!is_array($address)) {
			$address = fetch('SELECT * FROM ' . tablename('shop_member_address') . ' WHERE id = ' . $item['addressid']);
		}

		$express_list = model('express')->getExpressList();
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund, 'address' => $address, 'express_list' => $express_list,'edit_flag'=>$edit_flag]);
		echo $this->fetch('order/op/send');
	}

	public function changeaddress()
	{
		$opdata = $this->opData();
		extract($opdata);
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);

		if (empty($item['addressid'])) {
			$user = unserialize($item['carrier']);
		} else {
			$user = iunserializer($item['address']);

			if (!is_array($user)) {
				$user = fetch('SELECT * FROM ' . tablename('shop_member_address') . ' WHERE id = ' . $item['addressid']);
			}

			$address_info = $user['address'];
			$user_address = $user['address'];
			$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
			$item['addressdata'] = $oldaddress = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
		}

		if (Request::instance()->isPost()) {
			$realname = $_POST['realname'];
			$mobile = $_POST['mobile'];
			$province = $_POST['province'];
			$city = $_POST['city'];
			$area = $_POST['area'];
			$street = $_POST['street'];
			$changead = intval($_POST['changead']);
			$address = trim($_POST['address']);

			if (!empty($id)) {
				if (empty($realname)) {
					$ret = '请填写收件人姓名！';
					show_json(0, $ret);
				}

				if (empty($mobile)) {
					$ret = '请填写收件人手机！';
					show_json(0, $ret);
				}

				if ($changead) {
					if ($province == '请选择省份') {
						$ret = '请选择省份！';
						show_json(0, $ret);
					}

					if (empty($address)) {
						$ret = '请填写详细地址！';
						show_json(0, $ret);
					}
				}

				$item = fetch('SELECT id, ordersn, address FROM ' . tablename('shop_order') . ' WHERE id = ' . $id . ' and merchid = ' . $merch['id']);
				$address_array = iunserializer($item['address']);
				$address_array['realname'] = $realname;
				$address_array['mobile'] = $mobile;

				if ($changead) {
					$address_array['province'] = $province;
					$address_array['city'] = $city;
					$address_array['area'] = $area;
					$address_array['street'] = $street;
					$address_array['address'] = $address;
				}
				else {
					$address_array['province'] = $user['province'];
					$address_array['city'] = $user['city'];
					$address_array['area'] = $user['area'];
					$address_array['street'] = $user['street'];
					$address_array['address'] = $user_address;
				}

				$address_array = iserializer($address_array);
				Db::name('shop_order')->where(array('id' => $id, 'merchid' => $merch['id']))->update( array('address' => $address_array));
				model('shop')->mplog('order.op.changeaddress', '修改收货地址 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br>原地址: 收件人: ' . $oldaddress['realname'] . ' 手机号: ' . $oldaddress['mobile'] . ' 收件地址: ' . $oldaddress['address'] . '<br>新地址: 收件人: ' . $realname . ' 手机号: ' . $mobile . ' 收件地址: ' . $province . ' ' . $city . ' ' . $area . ' ' . $address);
				show_json(1);
			}
		}
		$this->assign(['id' => $id, 'merch' => $merch, 'item' => $item, 'order_goods' => $order_goods, 'is_singlerefund' => $is_singlerefund, 'area_set' => $area_set, 'new_area' => $new_area, 'address_street' => $address_street, 'user' => $user, 'address_info' => $address_info, 'user_address' => $user_address]);
		echo $this->fetch('order/op/changeaddress');
	}

	public function op()
	{
		$ops = input('ops/s');
		if(empty($ops) || !in_array($ops, array('pay','changeprice','close','paycancel','finish','fetchcancel','sendcancel','fetch','send','remarksaler','changeexpress','changeaddress')))
		{
			show_json(0);
		}
		if($ops == 'pay') {
			$opdata = $this->opData();
			extract($opdata);
			if (1 < $item['status']) {
				show_json(0, '订单已付款，不需重复付款！');
			}

			if (!empty($item['virtual'])) {
				model('virtual')->pay($item);
			} else {
				Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 1, 'paytype' => 11, 'paytime' => time()));
				model('order')->setStocksAndCredits($item['id'], 1);
				model('notice')->sendOrderMessage($item['id']);
				model('coupon')->sendcouponsbytask($item['id']);

				if (!empty($item['couponid'])) {
					model('coupon')->backConsumeCoupon($item['id']);
				}
			}

			model('verifygoods')->createverifygoods($item['id']);
			Db::name('shop_order_goods')->where('orderid',$item['id'])->update(array('rstate' => 10, 'prohibitrefund' => 0));
			model('shop')->plog('order.op.pay', '订单确认付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
		} elseif($ops == 'paycancel') {
			$opdata = $this->opData();
			extract($opdata);

			if ($item['status'] != 1) {
				show_json(0, '订单未付款，不需取消！');
			}

			if (Request::instance()->isPost()) {
				model('order')->setStocksAndCredits($item['id'], 2);
				Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 0, 'cancelpaytime' => time()));
				model('shop')->plog('order.op.paycancel', '订单取消付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);

				foreach ($order_goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('rstate' => 0, 'prohibitrefund' => 0));
				}
				show_json(1);
			}
		} elseif ($ops == 'changeprice') {
			$opdata = $this->opData();
			extract($opdata);
			if (Request::instance()->isPost()) {
				if(empty($merch['changepricechecked'])) {
					show_json(0, '您没有权限!');
				}
				$changegoodsprice = input('changegoodsprice/a');

				if (!is_array($changegoodsprice)) {
					show_json(0, '未找到改价内容!');
				}

				if (0 < $item['parentid']) {
					$parent_order = array();
					$parent_order['id'] = $item['parentid'];
				}

				$changeprice = 0;

				foreach ($changegoodsprice as $ogid => $change) {
					$changeprice += floatval($change);
				}

				$dispatchprice = floatval(input('changedispatchprice'));

				if ($dispatchprice < 0) {
					$dispatchprice = 0;
				}

				$orderprice = $item['price'] + $changeprice;
				$changedispatchprice = 0;

				if ($dispatchprice != $item['dispatchprice']) {
					$changedispatchprice = $dispatchprice - $item['dispatchprice'];
					$orderprice += $changedispatchprice;
				}

				if ($orderprice < 0) {
					show_json(0, '订单实际支付价格不能小于0元!');
				}

				foreach ($changegoodsprice as $ogid => $change) {
					$og = Db::name('shop_order_goods')->where('id',$ogid)->field('price,realprice')->find();

					if (!empty($og)) {
						$realprice = $og['realprice'] + $change;

						if ($realprice < 0) {
							show_json(0, '单个商品不能优惠到负数');
						}
					}
				}

				$ordersn2 = $item['ordersn2'] + 1;

				if (99 < $ordersn2) {
					show_json(0, '超过改价次数限额');
				}

				$orderupdate = array();

				if ($orderprice != $item['price']) {
					$orderupdate['price'] = $orderprice;
					$orderupdate['ordersn2'] = $item['ordersn2'] + 1;

					if (0 < $item['parentid']) {
						$parent_order['price_change'] = $orderprice - $item['price'];
					}
				}

				$orderupdate['changeprice'] = $item['changeprice'] + $changeprice;

				if ($dispatchprice != $item['dispatchprice']) {
					$orderupdate['dispatchprice'] = $dispatchprice;
					$orderupdate['changedispatchprice'] += $changedispatchprice;

					if (0 < $item['parentid']) {
						$parent_order['dispatch_change'] = $changedispatchprice;
					}
				}

				if (!empty($orderupdate)) {
					Db::name('shop_order')->where('id',$item['id'])->update($orderupdate);
				}

				if (0 < $item['parentid']) {
					if (!empty($parent_order)) {
						model('order')->changeParentOrderPrice($parent_order);
					}
				}

				foreach ($changegoodsprice as $ogid => $change) {
					$og = Db::name('shop_order_goods')->where('id',$ogid)->field('price,realprice,changeprice')->find();

					if (!empty($og)) {
						$realprice = $og['realprice'] + $change;
						$changeprice = $og['changeprice'] + $change;
						Db::name('shop_order_goods')->where('id',$ogid)->update(array('realprice' => $realprice, 'changeprice' => $changeprice));
					}
				}

				model('shop')->plog('order.op.changeprice', '订单号： ' . $item['ordersn'] . ' <br/> 价格： ' . $item['price'] . ' -> ' . $orderprice);
				model('notice')->sendOrderChangeMessage($item['mid'], array('title' => '订单金额', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $item['price'], 'data' => round($orderprice, 2), 'type' => 1), 'orderstatus');
				show_json(1);
			}
			$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$item['id'])->field('og.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.oldprice')->select();

			if (empty($item['addressid'])) {
				$user = unserialize($item['carrier']);
				$item['addressdata'] = array('realname' => $user['carrier_realname'], 'mobile' => $user['carrier_mobile']);
			}
			else {
				$user = iunserializer($item['address']);

				if (!is_array($user)) {
					$user = Db::name('shop_member_address')->where('id',$item['addressid'])->find();
				}

				$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['address'];
				$item['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
			}
			$this->assign(['item'=>$item,'user'=>$user,'order_goods'=>$order_goods]);
			echo $this->fetch('order/op/changeprice');
		} elseif($ops == 'close') {
			$opdata = $this->opData();
			extract($opdata);

			if ($item['status'] == -1) {
				show_json(0, '订单已关闭，无需重复关闭！');
			}
			else {
				if (1 <= $item['status']) {
					show_json(0, '订单已付款，不能关闭！');
				}
			}

			if (Request::instance()->isPost()) {
				if (!empty($item['transid'])) {
				}

				$time = time();
				if ((0 < $item['refundstate'])) {
					$change_refund = array();
					$change_refund['status'] = -1;
					$change_refund['refundtime'] = $time;
					$change_refund['lastupdate'] = $time;
					Db::name('shop_order_refund')->where('orderid',$item['id'])->update($change_refund);
				}

				if (0 < $item['deductcredit']) {
					model('member')->setCredit($item['mid'], 'credit1', $item['deductcredit'], array('0', $shopset['shop']['name'] . '购物返还抵扣积分 积分: ' . $item['deductcredit'] . ' 抵扣金额: ' . $item['deductprice'] . ' 订单号: ' . $item['ordersn']));
				}

				model('order')->setDeductCredit2($item);
				if (!empty($item['couponid'])) {
					model('coupon')->returnConsumeCoupon($item['id']);
				}

				model('order')->setStocksAndCredits($item['id'], 2);
				Db::name('shop_order')->where('id',$item['id'])->update(array('status' => -1, 'refundstate' => 0, 'canceltime' => $time, 'remarkclose' => input('remark')));
				model('shop')->plog('order.op.close', '订单关闭 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
				show_json(1);
			}
			$this->assign(['item'=>$item]);
			echo $this->fetch('order/op/close');
		} elseif ($ops == 'finish') {
			$opdata = $this->opData();
			extract($opdata);
			if(empty($merch['finishchecked'])) {
				show_json(0, '您没有权限!');
			}
			Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 3, 'finishtime' => time()));
			model('order')->fullback($item['id']);

			model('member')->upgradeLevel($item['mid']);
			model('order')->setGiveBalance($item['id'], 1);
			model('notice')->sendOrderMessage($item['id']);

			model('coupon')->sendcouponsbytask($item['id']);
			if (!empty($item['couponid'])) {
				model('coupon')->backConsumeCoupon($item['id']);
			}

			model('shop')->plog('order.op.finish', '订单完成 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);

			foreach ($order_goods as $key => $value) {
				if(!empty($value['cannotrefund'])) {
					$rstate = 11;
				} else {
					$rstate = 12;
				}
				Db::name('shop_order_goods')->where('id',$value['id'])->update(array('rstate' => $rstate, 'prohibitrefund' => $value['cannotrefund']));
			}
			show_json(1);
		} elseif($ops == 'fetchcancel') {
			$opdata = $this->opData();
			extract($opdata);

			if ($item['status'] != 3) {
				show_json(0, '订单未发货，不需取消！');
			}
			Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 1, 'finishtime' => 0));
			model('shop')->plog('order.op.fetchcancel', '订单取消发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
		} elseif($ops == 'sendcancel') {
			$opdata = $this->opData();
			extract($opdata);
			$sendtype = input('sendtype/d');
			if (($item['status'] != 2) && ($item['sendtype'] == 0)) {
				show_json(0, '订单未发货，不需取消发货！');
			}

			if (Request::instance()->isPost()) {
				if (!empty($item['transid'])) {
				}

				$remark = trim(input('remark'));

				if (!empty($item['remarksend'])) {
					$remark = $item['remarksend'] . "\r\n" . $remark;
				}

				$data = array('sendtime' => 0, 'remarksend' => $remark);

				if (0 < $item['sendtype']) {
					if (empty($sendtype)) {
						if (empty(input('bundle'))) {
							show_json(0, '请选择您要修改的包裹！');
						}

						$sendtype = intval(input('bundle'));
					}

					$data['sendtype'] = 0;
					Db::name('shop_order_goods')->where('orderid',$item['id'])->where('sendtype',$sendtype)->update($data);
					$order = Db::name('shop_order')->where('id',$item['id'])->field('sendtype')->find();
					Db::name('shop_order')->where('id',$item['id'])->update(array('sendtype' => $order['sendtype'] - 1, 'status' => 1));
				} else {
					$data['status'] = 1;
					Db::name('shop_order')->where('id',$item['id'])->update($data);
				}

				if ($item['paytype'] == 3) {
					model('order')->setStocksAndCredits($item['id'], 2);
				}

				model('shop')->plog('order.op.sendcancel', '订单取消发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 原因: ' . $remark);
				show_json(1);
			}

			$sendgoods = array();
			$bundles = array();

			if (0 < $sendtype) {
				$sendgoods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid=' . $item['id'] .' and og.sendtype=' . $sendtype)->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
			} else {
				if (0 < $item['sendtype']) {
					$i = 1;

					while ($i <= intval($item['sendtype'])) {
						$bundles[$i]['goods'] = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid='.$i.' and og.sendtype=' . $i)->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
						$bundles[$i]['sendtype'] = $i;

						if (empty($bundles[$i]['goods'])) {
							unset($bundles[$i]);
						}
						++$i;
					}
				}
			}
			$this->assign(['item'=>$item,'sendgoods'=>$sendgoods,'bundles'=>$bundles]);
			echo $this->fetch('order/op/sendcancel');
		} elseif($ops == 'fetch') {
			$opdata = $this->opData();
			extract($opdata);

			if ($item['status'] != 1) {
				show_json(0, '订单未付款，无法确认取货！');
			}

			$time = time();
			$d = array('status' => 3, 'sendtime' => $time, 'finishtime' => $time);

			if ($item['isverify'] == 1) {
				$d['verified'] = 1;
				$d['verifytime'] = $time;
				$d['verifyoperatorid'] = '';
			}

			Db::name('shop_order')->where('id',$item['id'])->update($d);
			model('order')->fullback($item['id']);

			model('coupon')->sendcouponsbytask($item['id']);
			if (!empty($item['couponid'])) {
				model('coupon')->backConsumeCoupon($item['id']);
			}

			if (!empty($item['refundstate'])) {
				$refund = Db::name('shop_order_refund')->where('orderid',$item['id'])->find();

				if (!empty($refund)) {
					Db::name('shop_order_refund')->where('orderid',$item['id'])->setField('status',-1);
					Db::name('shop_order')->where('id',$item['id'])->setField('refundstate',0);
				}
			}

			$log = '订单确认取货';

			$log .= ' ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'];
			model('order')->setGiveBalance($item['id'], 1);
			model('member')->upgradeLevel($item['mid']);
			model('notice')->sendOrderMessage($item['id']);

			model('shop')->plog('order.op.fetch', $log);
			show_json(1);
		} elseif($ops == 'send') {
			$opdata = $this->opData();
			extract($opdata);

			if (empty($item['addressid'])) {
				show_json(0, '无收货地址，无法发货！');
			}

			if ($item['paytype'] != 3) {
				if ($item['status'] != 1) {
					show_json(0, '订单未付款，无法发货！');
				}
			}

			if (Request::instance()->isPost()) {
				if (!empty(input('isexpress')) && empty(input('expresssn'))) {
					show_json(0, '请输入快递单号！');
				}

				if (!empty($item['transid'])) {
				}

				$time = time();
				$data = array('sendtype' => 0 < $item['sendtype'] ? $item['sendtype'] : intval(input('sendtype')), 'express' => trim(input('express')), 'expresscom' => trim(input('expresscom')), 'expresssn' => trim(input('expresssn')), 'sendtime' => $time);
				$data['express']=Db::name('shop_express')->where('name',$data['expresscom'])->value('express');
				if ((intval(input('sendtype')) == 1) || (0 < $item['sendtype'])) {
					if (empty(input('ordergoodsid/a'))) {
						show_json(0, '请选择发货商品！');
					}

					$ogoods = array();
					$ogoods = Db::name('shop_order_goods')->where('orderid',$item['id'])->field('sendtype')->order('sendtype','desc')->select();
					$senddata = array('sendtype' => $ogoods[0]['sendtype'] + 1, 'sendtime' => $data['sendtime']);
					$data['sendtype'] = $ogoods[0]['sendtype'] + 1;
					$goodsid = input('ordergoodsid/a');

					foreach ($goodsid as $key => $value) {
						Db::name('shop_order_goods')->where('id',$value)->update($data);
					}

					$send_goods = Db::name('shop_order_goods')->where('orderid',$item['id'])->where('sendtype',0)->find();

					if (empty($send_goods)) {
						$senddata['status'] = 2;
					}
					Db::name('shop_order')->where('id',$item['id'])->update($senddata);
				} else {
					$data['status'] = 2;
					Db::name('shop_order')->where('id',$item['id'])->update($data);
				}

				if (!empty($item['refundstate'])) {
					$refund = Db::name('shop_order_refund')->where('orderid',$item['id'])->find();

					if (!empty($refund)) {
						Db::name('shop_order_refund')->where('orderid',$item['id'])->update(array('status' => -1, 'endtime' => $time, 'lastupdate' => time(), 'lastupdate' => time()));
						Db::name('shop_order')->where('id',$item['id'])->setField('refundstate',0);
					}
				}

				if ($item['paytype'] == 3) {
					model('order')->setStocksAndCredits($item['id'], 1);
				}

				model('notice')->sendOrderMessage($item['id']);
				model('shop')->plog('order.op.send', '订单发货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br/>快递公司: ' . input('expresscom') . ' 快递单号: ' . input('expresssn'));
				show_json(1);
			}

			$noshipped = array();
			$shipped = array();

			if (0 < $item['sendtype']) {
				$noshipped = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.sendtype = 0 and og.orderid='.$item['id'])->field('og.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
				$i = 1;

				while ($i <= $item['sendtype']) {
					$shipped[$i]['sendtype'] = $i;
					$shipped[$i]['goods'] = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.sendtype = ' . $i . ' and og.orderid='.$item['id'])->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
					++$i;
				}
			}

			$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$item['id'])->field('og.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
			$address = iunserializer($item['address']);

			if (!is_array($address)) {
				$address = Db::name('shop_member_address')->where('id',$item['addressid'])->find();
			}

			$express_list = model('shop')->getExpressList();
			$this->assign(['item'=>$item,'noshipped'=>$noshipped,'shipped'=>$shipped,'express_list'=>$express_list,'order_goods'=>$order_goods,'address'=>$address]);
			echo $this->fetch('order/op/send');
		} elseif($ops == 'remarksaler') {
			$opdata = $this->opData();
			extract($opdata);

			if (Request::instance()->isPost()) {
				Db::name('shop_order')->where('id',$item['id'])->setField('remarksaler',input('remark'));
				model('shop')->plog('order.op.remarksaler', '订单备注 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 备注内容: ' . input('remark'));
				show_json(1);
			}
			$this->assign(['item'=>$item]);
			echo $this->fetch('order/op/remarksaler');
		} elseif($ops == 'changeexpress') {
			$opdata = $this->opData();
			extract($opdata);
			$changeexpress = 1;
			$sendtype = intval(input('sendtype'));
			$edit_flag = 1;

			if (Request::instance()->isPost()) {
				$express = input('express');
				$expresscom = input('expresscom');
				$expresssn = trim(input('expresssn'));

				if (empty($id)) {
					$ret = '参数错误！';
					show_json(0, $ret);
				}

				if (!empty($expresssn)) {
					$change_data = array();
					$change_data['express'] = $express;
					$change_data['expresscom'] = $expresscom;
					$change_data['expresssn'] = $expresssn;

					if (0 < $item['sendtype']) {
						if (empty($sendtype)) {
							if (empty(input('bundle'))) {
								show_json(0, '请选择您要修改的包裹！');
							}

							$sendtype = intval(input('bundle'));
						}
						Db::name('shop_order_goods')->where('orderid',$id)->where('sendtype',$sendtype)->update($change_data);
					} else {
						Db::name('shop_order')->where('id',$id)->update($change_data);
					}

					model('shop')->plog('order.op.changeexpress', '修改快递状态 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 快递公司: ' . $expresscom . ' 快递单号: ' . $expresssn);
					show_json(1);
				} else {
					show_json(0, '请填写快递单号！');
				}
			}

			$sendgoods = array();
			$bundles = array();

			if (0 < $sendtype) {
				$sendgoods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid=' . $item['id'] . ' and og.sendtype=' . $sendtype)->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
			} else {
				if (0 < $item['sendtype']) {
					$i = 1;

					while ($i <= intval($item['sendtype'])) {
						$bundles[$i]['goods'] = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid='. $item['id'] . ' and og.sendtype=' . $i)->field('g.id,g.title,g.thumb,og.sendtype,g.ispresell')->select();
						$bundles[$i]['sendtype'] = $i;

						if (empty($bundles[$i]['goods'])) {
							unset($bundles[$i]);
						}

						++$i;
					}
				}
			}

			$address = iunserializer($item['address']);

			if (!is_array($address)) {
				$address = Db::name('shop_member_address')->where('id',$item['addressid'])->find();
			}
			$express_list = model('shop')->getExpressList();
			$this->assign(['item'=>$item,'noshipped'=>$noshipped,'shipped'=>$shipped,'express_list'=>$express_list,'order_goods'=>$order_goods,'address'=>$address]);
			echo $this->fetch('order/op/send');
		} elseif ($ops == 'changeaddress') {
			$opdata = $this->opData();
			extract($opdata);
			$area_set = model('util')->get_area_config_set();
			$new_area = intval($area_set['new_area']);

			$address_street = intval($area_set['address_street']);

			if (empty($item['addressid'])) {
				$user = unserialize($item['carrier']);
			} else {
				$user = iunserializer($item['address']);

				if (!is_array($user)) {
					$user = Db::name('shop_member_address')->where('id',$item['addressid'])->find();
				}

				$address_info = $user['address'];
				$user_address = $user['address'];
				$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
				$item['addressdata'] = $oldaddress = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'address' => $user['address']);
			}

			if (Request::instance()->isPost()) {
				$realname = input('realname');
				$mobile = input('mobile');
				$province = input('province');
				$city = input('city');
				$area = input('area');
				$street = input('street');
				$changead = intval(input('changead'));
				$address = trim(input('address'));
				if (!empty($id)) {
					if (empty($realname)) {
						$ret = '请填写收件人姓名！';
						show_json(0, $ret);
					}

					if (empty($mobile)) {
						$ret = '请填写收件人手机！';
						show_json(0, $ret);
					}

					if ($changead) {
						if ($province == '请选择省份') {
							$ret = '请选择省份！';
							show_json(0, $ret);
						}
						if (empty($address)) {
							$ret = '请填写详细地址！';
							show_json(0, $ret);

						}
					}	
					$item = Db::name('shop_order')->where('id',$id)->field('id, ordersn, address,mid')->find();
					$address_array = iunserializer($item['address']);
					$address_array['realname'] = $realname;
					$address_array['mobile'] = $mobile;

					if ($changead) {
						$address_array['province'] = $province;
						$address_array['city'] = $city;
						$address_array['area'] = $area;
						$address_array['street'] = $street;
						$address_array['address'] = $address;
					}
					else {
						$address_array['province'] = $user['province'];
						$address_array['city'] = $user['city'];
						$address_array['area'] = $user['area'];
						$address_array['street'] = $user['street'];
						$address_array['address'] = $user_address;
					}

					$address_array = iserializer($address_array);
					Db::name('shop_order')->where('id',$id)->update(array('address' => $address_array));
					model('shop')->plog('order.op.changeaddress', '修改收货地址 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' <br>原地址: 收件人: ' . $oldaddress['realname'] . ' 手机号: ' . $oldaddress['mobile'] . ' 收件地址: ' . $oldaddress['address'] . '<br>新地址: 收件人: ' . $realname . ' 手机号: ' . $mobile . ' 收件地址: ' . $province . ' ' . $city . ' ' . $area . ' ' . $address);
					model('notice')->sendOrderChangeMessage($item['mid'], array('title' => '订单收货地址', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $oldaddress['address'], 'data' => $province . $city . $area . $address, 'type' => 0), 'orderstatus');
					show_json(1);
				}
			}
			$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street,'user'=>$user,'id'=>$id,'edit_flag'=>$edit_flag]);
			echo $this->fetch('order/op/changeaddress');
		}
	}

	public function refund4()
	{
		$refundData = $this->refundData(4);
		return $refundData;
	}

	public function refund5()
	{
		$refundData = $this->refundData(5);
		return $refundData;
	}

	protected function refundData($state)
	{
		$psize = 20;
		$merch = $this->merch;
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		$condition = ' o.merchid = ' . $merch['id'] . ' and o.ismr=0 and o.deleted=0 and o.istrade=0 ';

		if ($state == '4') {
			$statuscondition = ' AND f.status=0 or f.status>1';
		} else if ($state == '5') {
			$statuscondition = ' AND f.refundtime<>0 and (f.status=-1 or f.status=-2 or f.status=1)';
		}

		if (is_array($_GET['time'])) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND f.createtime >= ' . $starttime . ' AND f.createtime <= ' . $endtime;
		}

		if (!empty(input('searchfield')) && !empty(input('keyword'))) {
			$searchfield = trim(strtolower(input('searchfield')));
			$keyword = trim(input('keyword'));
			$sqlcondition = '';

			if ($searchfield == 'refundno') {
				$condition .= " AND locate('" . $keyword . "',f.refundno)>0";
			} else if ($searchfield == 'member') {
				$condition .= " AND (locate('" . $keyword . "',m.realname)>0 or locate('" . $keyword . "',m.mobile)>0 or locate('" . $keyword . "',m.nickname)>0)";
			} else if ($searchfield == 'mid') {
				$condition .= ' AND m.id = ' . $keyword;
			} else if ($searchfield == 'address') {
				$condition .= " AND ( locate('". $keyword . "',a.realname)>0 or locate('" . $keyword . "',a.mobile)>0 or locate('" . $keyword . "',o.carrier)>0)";
			} else if ($searchfield == 'location') {
				$condition .= " AND ( locate('" . $keyword . "',a.province)>0 or locate('" . $keyword . "',a.city)>0 or locate('" . $keyword . "',a.area)>0 or locate('" . $keyword . "',a.address)>0)";
			} else if ($searchfield == 'expresssn') {
				$condition .= " AND locate('" . $keyword . "',o.expresssn)>0";
			}
		}
		
		$list = Db::name('shop_order_refund')
			->alias('f')
			->join('shop_order o','f.orderid =o.id','left')
			->join('member m','m.id=f.mid','left')
			->join('shop_member_address a','a.id=o.addressid','left')
			->join('shop_dispatch d','d.id = o.dispatchid','left')
			->where($condition . $statuscondition)
			->field('f.*,o.ordersn,o.status as orderstatus,o.goodsprice,o.olddispatchprice,o.taskdiscountprice,o.lotterydiscountprice,o.discountprice,o.deductprice,o.deductcredit2,o.deductenough,o.merchdeductenough,o.couponprice,o.isdiscountprice,o.buyagainprice,o.seckilldiscountprice,o.changeprice,o.changedispatchprice,o.price as orderprice,o.dispatchprice,o.addressid,o.sendtype,o.express,o.expresssn,o.remark, o.dispatchtype,o.isverify,o.virtual,o.isvirtual,o.address,o.carrier,a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea, a.street as astreet,a.address as aaddress,d.dispatchname,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile')
			->order('f.createtime','desc')
			->paginate($psize);
		$pager = $list->render();
		$r_type = array('退款', '退货退款', '换货');
		$paytype = array(
			0  => array('css' => 'default', 'name' => '未支付'),
			6  => array('css' => 'danger', 'name' => '余额支付'),
			11 => array('css' => 'default', 'name' => '后台付款'),
			4  => array('css' => 'danger', 'name' => '在线支付'),
			1 => array('css' => 'success', 'name' => '微信支付'),
			2 => array('css' => 'warning', 'name' => '支付宝支付'),
			5 => array('css' => 'warning', 'name' => '银联支付'),
			3  => array('css' => 'primary', 'name' => '货到付款')
		);
		$orderstatus = array(
			-1 => array('css' => 'default', 'name' => '已关闭'),
			0  => array('css' => 'danger', 'name' => '待付款'),
			1  => array('css' => 'info', 'name' => '待发货'),
			2  => array('css' => 'warning', 'name' => '待收货'),
			3  => array('css' => 'success', 'name' => '已完成')
		);
		$refundstatus = array(
			-2 => array('css' => 'default', 'name' => '客户取消'),
			-1 => array('css' => 'default', 'name' => '已拒绝'),
			0  => array('css' => 'danger', 'name' => '等待商家处理申请'),
			1  => array('css' => 'success', 'name' => '售后完成'),
			3  => array('css' => 'warning', 'name' => '等待客户退回物品'),
			4  => array('css' => 'info', 'name' => '客户退回物品，等待商家重新发货'),
			5  => array('css' => 'info', 'name' => '等待客户收货'),
		);
		$is_merch = array();
		$is_merchname = 0;

		if ($merch_plugin) {
			$merch_user = model('merch')->getListUser($list, 'merch_user');

			if (!empty($merch_user)) {
				$is_merchname = 1;
			}
		}
		if (!empty($list)) {
			foreach ($list as $k => $value) { 
				if(!empty($value['merchid'])) {
					$value['merchname'] = '商户';
				} else {
					$value['merchname'] = '商城';
				}			

				$s = $value['status'];
				$pt = $value['refundtype'];
				$value['statusvalue'] = $s;
				$value['statuscss'] = $refundstatus[$value['status']]['css'];
				$value['status'] = $refundstatus[$value['status']]['name'];
				$value['orderstatuscss'] = $orderstatus[$value['orderstatus']]['css'];
				$value['orderstatus'] = $orderstatus[$value['orderstatus']]['name'];

				$value['refundtypevalue'] = $pt;
				$value['css'] = $refundtype[$pt]['css'];
				$value['refundtype'] = $refundtype[$pt]['name'];

				if (($value['dispatchtype'] == 1) || !empty($value['isverify']) || !empty($value['virtual']) || !empty($value['isvirtual'])) {
					$value['address'] = '';
					$carrier = iunserializer($value['carrier']);

					if (is_array($carrier)) {
						$value['addressdata']['realname'] = $value['realname'] = $carrier['carrier_realname'];
						$value['addressdata']['mobile'] = $value['mobile'] = $carrier['carrier_mobile'];
					}
				} else {
					$address = iunserializer($value['address']);
					$isarray = is_array($address);
					$value['realname'] = $isarray ? $address['realname'] : $value['arealname'];
					$value['mobile'] = $isarray ? $address['mobile'] : $value['amobile'];
					$value['province'] = $isarray ? $address['province'] : $value['aprovince'];
					$value['city'] = $isarray ? $address['city'] : $value['acity'];
					$value['area'] = $isarray ? $address['area'] : $value['aarea'];
					$value['street'] = $isarray ? $address['street'] : $value['astreet'];
					$value['address'] = $isarray ? $address['address'] : $value['aaddress'];
					$value['address_province'] = $value['province'];
					$value['address_city'] = $value['city'];
					$value['address_area'] = $value['area'];
					$value['address_street'] = $value['street'];
					$value['address_address'] = $value['address'];
					$value['address'] = $value['province'] . ' ' . $value['city'] . ' ' . $value['area'] . ' ' . $value['address'];
					$value['addressdata'] = array('realname' => $value['realname'], 'mobile' => $value['mobile'], 'address' => $value['address']);
				}

				$goodsids = array_unique(array_filter(explode(",", $value['goodsids'])));
				$refund_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$value['id'])->where('og.id','in',$goodsids)->field('g.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.changeprice,og.oldprice,op.specs,g.merchid,og.seckill,og.seckill_taskid,og.seckill_roomid,g.ispresell')->select();
				$goods = '';

				foreach ($order_goods as &$og) {
					$og['seckill_task'] = false;
					$og['seckill_room'] = false;

					if ($og['seckill']) {
						$og['seckill_task'] = plugin_run('seckill::getTaskInfo', $og['seckill_taskid']);
						$og['seckill_room'] = plugin_run('seckill::getRoomInfo', $og['seckill_taskid'], $og['seckill_roomid']);
					}

					if (!empty($og['specs'])) {
						$thumb = model('goods')->getSpecThumb($og['specs']);

						if (!empty($thumb)) {
							$og['thumb'] = $thumb;
						}
					}

					$goods .= '' . $og['title'] . " ";

					if (!empty($og['optiontitle'])) {
						$goods .= ' 规格: ' . $og['optiontitle'];
					}

					if (!empty($og['option_goodssn'])) {
						$og['goodssn'] = $og['option_goodssn'];
					}

					if (!empty($og['option_productsn'])) {
						$og['productsn'] = $og['option_productsn'];
					}

					if (!empty($og['goodssn'])) {
						$goods .= ' 商品编号: ' . $og['goodssn'];
					}

					if (!empty($og['productsn'])) {
						$goods .= ' 商品条码: ' . $og['productsn'];
					}

					$goods .= ' 单价: ' . ($og['price'] / $og['total']) . ' 折扣后: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . ' 折扣后: ' . $og['realprice'] . "\r\n ";
				}

				unset($og);
				$value['goods'] = set_medias($refund_goods, 'thumb');
				$value['goods_str'] = $goods;				
				$data = array();
	    		$data = $value;
	    		$list->offsetSet($k,$data);
			}
			unset($value);
		}

		if (($condition != ' o.ismr=0 and o.deleted=0 and o.isparent=0') || !empty($sqlcondition)) {
			$t = array();
		} else {
			$t = Db::name('shop_order')->where('ismr=0 and deleted=0 and isparent=0 ' . $status_condition)->field('COUNT(*) as count, ifnull(sum(price),0) as sumprice')->find();
		}

		$total = $t['count'];
		$totalmoney = $t['sumprice'];
		$this->assign(['list'=>$list,'pager'=>$pager,'searchfield'=>$searchfield,'r_type'=>$r_type,'starttime'=>$starttime,'endtime'=>$endtime,'is_openmerch'=>$is_openmerch,'keyword'=>$keyword,'searchtime'=>$searchtime,'refundtype'=>$refundtype]);
		return $this->fetch('order/op/refund/list');
	}

	public function refund()
	{
		$orderid = intval(input('orderid'));
		$refundid = intval(input('refundid'));
		$merch = $this->merch;
		$item = Db::name('shop_order')->where('id',$orderid)->where('merchid',$merch['id'])->find();

		if (empty($item)) {
			$this->error('未找到订单!');
		}

		if (!empty($refundid)) {
			$refund = Db::name('shop_order_refund')->where('id',$refundid)->find();
			$refund['imgs'] = iunserializer($refund['imgs']);
		}
		if (empty($refundid)) {
			$this->error('未找到维权申请!');
		}

		$r_type = array('退款', '退货退款', '换货');
		$step_array = array();
		$step_array[1]['step'] = 1;
		$step_array[1]['title'] = '客户申请维权';
		$step_array[1]['time'] = $refund['createtime'];
		$step_array[1]['done'] = 1;
		$step_array[2]['step'] = 2;
		$step_array[2]['title'] = '商家处理维权申请';
		$step_array[2]['done'] = 1;
		$step_array[3]['step'] = 3;
		$step_array[3]['done'] = 0;

		if (0 <= $refund['status']) {
			if ($refund['rtype'] == 0) {
				$step_array[3]['title'] = '退款完成';
			} else if ($refund['rtype'] == 1) {
				$step_array[3]['title'] = '客户退回物品';
				$step_array[4]['step'] = 4;
				$step_array[4]['title'] = '退款退货完成';
			} else {
				if ($refund['rtype'] == 2) {
					$step_array[3]['title'] = '客户退回物品';
					$step_array[4]['step'] = 4;
					$step_array[4]['title'] = '商家重新发货';
					$step_array[5]['step'] = 5;
					$step_array[5]['title'] = '换货完成';
				}
			}

			if ($refund['status'] == 0) {
				$step_array[2]['done'] = 0;
				$step_array[3]['done'] = 0;
			}

			if ($refund['rtype'] == 0) {
				if (0 < $refund['status']) {
					$step_array[2]['time'] = $refund['refundtime'];
					$step_array[3]['done'] = 1;
					$step_array[3]['time'] = $refund['refundtime'];
				}
			} else {
				$step_array[2]['time'] = $refund['operatetime'];
				if (($refund['status'] == 1) || (4 <= $refund['status'])) {
					$step_array[3]['done'] = 1;
					$step_array[3]['time'] = $refund['sendtime'];
				}

				if (($refund['status'] == 1) || ($refund['status'] == 5)) {
					$step_array[4]['done'] = 1;

					if ($refund['rtype'] == 1) {
						$step_array[4]['time'] = $refund['refundtime'];
					} else {
						if ($refund['rtype'] == 2) {
							$step_array[4]['time'] = $refund['returntime'];

							if ($refund['status'] == 1) {
								$step_array[5]['done'] = 1;
								$step_array[5]['time'] = $refund['refundtime'];
							}
						}
					}
				}
			}
		} else if ($refund['status'] == -1) {
			$step_array[2]['done'] = 1;
			$step_array[2]['time'] = $refund['endtime'];
			$step_array[3]['done'] = 1;
			$step_array[3]['title'] = '拒绝' . $r_type[$refund['rtype']];
			$step_array[3]['time'] = $refund['endtime'];
		} else {
			if ($refund['status'] == -2) {
				if (!empty($refund['operatetime'])) {
					$step_array[2]['done'] = 1;
					$step_array[2]['time'] = $refund['operatetime'];
				}

				$step_array[3]['done'] = 1;
				$step_array[3]['title'] = '客户取消' . $r_type[$refund['rtype']];
				$step_array[3]['time'] = $refund['refundtime'];
			}
		}

		$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$refund['id'])->where('og.id','in',$goodsids)->field('g.*, og.goodssn as option_goodssn, og.productsn as option_productsn,og.total,g.type,og.optionname,og.optionid,og.price as orderprice,og.realprice,og.changeprice,og.oldprice,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs')->select();
		foreach ($goods as &$r) {
			if (!empty($r['option_goodssn'])) {
				$r['goodssn'] = $r['option_goodssn'];
			}

			if (!empty($r['option_productsn'])) {
				$r['productsn'] = $r['option_productsn'];
			}
		}

		unset($r);
		$item['goods'] = $goods;
		$member = model('member')->getMember($item['mid']);
		$express_list = model('shop')->getExpressList();
		$this->assign(['item'=>$item,'member'=>$member,'express_list'=>$express_list,'step_array'=>$step_array,'refund'=>$refund,'r_type'=>$r_type]);
		return $this->fetch('order/op/refund/detail');
	}

	public function refundsubmit()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$refundid = intval(input('refundid'));
		$item = Db::name('shop_order')->where('id',$id)->where('merchid',$merch['id'])->find();

		if (empty($item)) {
			show_json(0,'未找到订单!');
		}

		if (empty($refundid)) {
			show_json(0,'未找到维权信息!');
		}

		if (!empty($refundid)) {
			$refund = Db::name('shop_order_refund')->where('id',$refundid)->find();
			$refund['imgs'] = iunserializer($refund['imgs']);
		}
		$refund_address = Db::name('shop_refund_address')->where('merchid',0)->select();
		$express_list = model('shop')->getExpressList();
		
		$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$refund['id'])->where('og.id','in',$goodsids)->field('og.id,og.goodsid,og.total,g.title,g.credit,g.isfullback,g.thumb,og.realprice,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs')->select();
		$r_type = array('退款', '退货退款', '换货');
		$rtypestr = $r_type[$refund['rtype']];
		if (Request::instance()->isPost()) {
			$shopset = model('common')->getSysset();
			if (empty($item['refundstate'])) {
				show_json(0, '订单未申请维权，不需处理！');
			}
			$refundcount = Db::name('shop_order_refund')->where('orderid',$item['id'])->where('status','in',[0,3,4,5])->count();
			if (($refund['status'] < 0) || ($refund['status'] == 1) && $refundcount <= 0) {
				Db::name('shop_order')->where('id',$item['id'])->setField('refundstate',0);
				show_json(0, '未找到需要处理的维权申请，不需处理！');
			}

			if (empty($refund['refundno'])) {
				$refund['refundno'] = model('common')->createNO('shop_order_refund', 'refundno', 'SR');
				Db::name('shop_order_refund')->where('id',$refund['id'])->setField('refundno',$refund['refundno']);
			}

			$refundstatus = intval(input('refundstatus'));
			$refundcontent = trim(input('refundcontent'));
			$time = time();
			$change_refund = array();

			if ($refundstatus == 0) {
				show_json(1);
			} else if ($refundstatus == 3) {
				$raid = input('raid');
				$message = trim(input('message'));

				if ($raid == 0) {
					$raddress = Db::name('shop_refund_address')->where('isdefault=1 and merchid=0')->find();
				} else {
					$raddress = Db::name('shop_refund_address')->where('id=' . $raid . ' and merchid=0')->find();
				}

				if (empty($raddress)) {
					$raddress = Db::name('shop_refund_address')->where('merchid=0')->order('id','desc')->find();
				}

				unset($raddress['mid']);
				unset($raddress['isdefault']);
				unset($raddress['deleted']);
				$iraddress = iserializer($raddress);
				$change_refund['reply'] = '';
				$change_refund['refundaddress'] = $iraddress;
				$change_refund['refundaddressid'] = $raid;
				$change_refund['message'] = $message;
				$change_refund['lastupdate'] = time();
				if (empty($refund['operatetime'])) {
					$change_refund['operatetime'] = $time;
				}

				if ($refund['status'] != 4) {
					$change_refund['status'] = 3;
				}

				Db::name('shop_order_refund')->where('id',$refundid)->update($change_refund);
				Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '店家', 'title' => '卖家已同意' . $rtypestr . '申请', 'content' => '卖家退货地址：' . $raddress['province'] . $raddress['city'] . $raddress['area'] . $raddress['address'] . '联系方式：' . $raddress['mobile'] . '  ' . $raddress['tel'] . '  ' . $raddress['name'], 'createtime' => time()));
				if ($refund['rtype'] == 2) {
					$refundstate = 43;
				} elseif ($refund['rtype'] == 0) {
					$refundstate = 33;
				} else {
					if ($refund['rtype'] == 1) {
						$refundstate = 23;
					}
				}
				foreach ($goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
				}
				model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
			} else if ($refundstatus == 5) {
				$change_refund['rexpress'] = input('rexpress');
				$change_refund['rexpresscom'] = input('rexpresscom');
				$change_refund['rexpresssn'] = trim(input('rexpresssn'));
				$change_refund['status'] = 5;
				if (($refund['status'] != 5) && empty($refund['returntime'])) {
					$change_refund['returntime'] = $time;
					if (empty($refund['operatetime'])) {
						$change_refund['operatetime'] = $time;
					}
				}
				$change_refund['lastupdate'] = time();
				Db::name('shop_order_refund')->where('id',$refundid)->update($change_refund);
				Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '店家', 'title' => '卖家重新发货', 'content' => '快递公司' . $change_refund['rexpresscom'] . '快递单号' . $change_refund['rexpresssn'], 'link' => 'expresslist?expresscom=' . $change_refund['rexpresscom'] . '&express=' . $change_refund['rexpress'] . '&expresssn=' . $change_refund['rexpresssn'], 'createtime' => time()));
				if ($refund['rtype'] == 2) {
					$refundstate = 46;
				} elseif ($refund['rtype'] == 0) {
					$refundstate = 36;
				} else {
					if ($refund['rtype'] == 1) {
						$refundstate = 26;
					}
				}
				foreach ($goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
				}
				model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
			} else if ($refundstatus == 10) {
				$refund_data['status'] = 1;
				$refund_data['refundtime'] = $time;
				$refund_data['lastupdate'] = time();
				Db::name('shop_order_refund')->where('id',$refundid)->update($refund_data);
				$order_data = array();
				$order_data['refundstate'] = 0;
				$order_data['status'] = 3;
				$order_data['refundtime'] = $time;
				$refundcount = Db::name('shop_order_refund')->where('orderid',$item['id'])->where('status','in',[0,3,4,5])->count();
				if($refundcount <= 0) {					
					Db::name('shop_order')->where('id',$item['id'])->update($order_data);	
				}
				Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '系统', 'title' => '售后已完成（已关闭）', 'content' => '售后维权已完成', 'createtime' => time()));
				if ($refund['rtype'] == 2) {
					$refundstate = 47;
				} elseif ($refund['rtype'] == 0) {
					$refundstate = 37;
				} else {
					if ($refund['rtype'] == 1) {
						$refundstate = 27;
					}
				}
				foreach ($goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
				}
				model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
			} else if ($refundstatus == 1) {
				if (0 < $item['parentid']) {
					$parent_item = Db::name("shop_order")->where('id',$item['parentid'])->field('id,ordersn,ordersn2,price,transid,paytype,apppay')->find();

					if (empty($parent_item)) {
						show_json(0, '未找到退款订单!');
					}

					$order_price = $parent_item['price'];
					$ordersn = $parent_item['ordersn'];
					$item['transid'] = $parent_item['transid'];
					$item['paytype'] = $parent_item['paytype'];
					$item['apppay'] = $parent_item['apppay'];

					if (!empty($parent_item['ordersn2'])) {
						$var = sprintf('%02d', $parent_item['ordersn2']);
						$ordersn .= 'GJ' . $var;
					}
				} else {
					$order_price = $item['price'];
					$ordersn = $item['ordersn'];

					if (!empty($item['ordersn2'])) {
						$var = sprintf('%02d', $item['ordersn2']);
						$ordersn .= 'GJ' . $var;
					}
				}

				$realprice = $refund['applyprice'];
				$refundtype = 0;
				if (empty($item['transid']) && ($item['paytype'] == 2) && empty($item['apppay'])) {
					$item['paytype'] = 23;
				}

				if ($item['paytype'] == 6) {
					model('member')->setCredit($item['mid'], 'credit2', $realprice, array(0, $shopset['name'] . '退款: ' . $realprice . '颗金贝 订单号: ' . $item['ordersn']));
					$result = true;
					$refundtype = 6;
				} else if ($item['paytype'] == 1) {
					$result = model('payment')->wxapp_refund($item['mid'], $ordersn, $refund['refundno'], $order_price * 100, $realprice * 100, !empty($item['apppay']) ? true : false);
					$refundtype = 1;
				} else if ($item['paytype'] == 2) {
					$set = model('common')->getSysset('pay');
					$sec = model('common')->getSec();
					$sec = iunserializer($sec['sec']);
					if (empty($item['transid'])) {
						show_json(0, '仅支持 升级此功能后退款的订单!');
					}
					if (empty($sec['app_alipay']['private_key']) || empty($sec['app_alipay']['appid'])) {
						show_json(0, '支付参数错误，私钥为空或者APPID为空!');
					}
					if (!empty($item['apppay'])) {				
						$params = array('out_request_no' => time(), 'out_trade_no' => $ordersn, 'refund_amount' => $realprice, 'refund_reason' => $shopset['name'] . '退款: ' . $realprice . '元 订单号: ' . $item['ordersn']);
						$result = model('payment')->ali_refund($params);
					} else {					
						if (!is_array($set['pay'])) {
							return show_json(0, '没有设定支付参数');
						}
					}
					$refundtype = 2;
				} else {
					if (($item['paytype'] == 23) && !empty($item['isborrow'])) {
						$result = model('payment')->refundBorrow($item['borrowopenid'], $ordersn, $refund['refundno'], $order_price * 100, $realprice * 100, !empty($item['ordersn2']) ? 1 : 0);
					} else {
						if ($realprice < 1) {
							show_json(0, '退款金额必须大于1元，才能使用企业付款退款!');
						}

						$realprice = round($realprice - $item['deductcredit2'], 2);

						if (0 < $realprice) {
							$result = model('payment')->pay($item['mid'], 1, $realprice * 100, $refund['refundno'], $shopset['name'] . '退款: ' . $realprice . '元 订单号: ' . $item['ordersn']);
						}

						$refundtype = 2;
					}
				}
				header("Content-type: text/html;charset=utf-8");
				if (is_error($result)) {
					show_json(0, $result['message']);
				}

				$credits = model('order')->getGoodsCredit($goods);

				if (0 < $credits) {
				}

				if (0 < $item['deductcredit']) {
					model('member')->setCredit($item['mid'], 'credit1', $item['deductcredit'], array('0', $shopset['name'] . '购物返还抵扣积分 积分: ' . $item['deductcredit'] . ' 抵扣金额: ' . $item['deductprice'] . ' 订单号: ' . $item['ordersn']));
				}

				if (!empty($refundtype)) {
					if ($realprice < 0) {
						$item['deductcredit2'] = $refund['applyprice'];
					}
					model('order')->setDeductCredit2($item);
				}

				$change_refund['reply'] = '';
				$change_refund['status'] = 1;
				$change_refund['refundtype'] = $refundtype;
				$change_refund['price'] = $realprice;
				$change_refund['refundtime'] = $time;

				if (empty($refund['operatetime'])) {
					$change_refund['operatetime'] = $time;
				}
				$change_refund['lastupdate'] = $time;
				Db::name('shop_order_refund')->where('id',$refundid)->update($change_refund);
				model('order')->setGiveBalance($item['id'], 2);
				model('order')->setStocksAndCredits($item['id'], 2);

				if ($refund['orderprice'] == $refund['applyprice']) {
					if (!empty($item['couponid'])) {
						model('coupon')->returnConsumeCoupon($item['id']);
					}
				}
				$refundcount = Db::name('shop_order_refund')->where('orderid',$item['id'])->where('status','in',[0,3,4,5])->count();
				if($refundcount <= 0) {
					Db::name('shop_order')->where('id',$item['id'])->update(array('refundstate' => 0, 'status' => -1, 'refundtime' => $time));
				}

				foreach ($goods as $g) {			
					if (0 < $g['isfullback']) {
						model('order')->fullbackstop($item['id']);
					}
					$salesreal = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id = og.orderid','left')->where('og.goodsid=' . $g['id'] . ' and o.status>=1')->sum('total');
					Db::name('shop_goods')->where('id',$g['id'])->setField('salesreal',$salesreal);
				}

				$log = '订单退款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'];

				if (0 < $item['parentid']) {
					$log .= ' 父订单号:' . $ordersn;
				}

				$plid = Db::name('shop_core_paylog')->insertGetId(array('mid' => $item['mid'], 'module' => 'shop', 'tid' => $item['ordersn'], 'fee' => -$item['price'], 'status' => 1, 'createtime' => time()));
				model('shop')->plog('order.op.refund.submit', $log);
				Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '店家', 'title' => '卖家同意打款', 'content' => '请注意您的支付宝、微信信息，关注打款进度', 'createtime' => time()));
				if ($refund['rtype'] == 2) {
					$refundstate = 47;
				} elseif ($refund['rtype'] == 0) {
					$refundstate = 37;
				} else {
					if ($refund['rtype'] == 1) {
						$refundstate = 27;
					}
				}

				foreach ($goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
				}
				model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
			} else if ($refundstatus == -1) {
				Db::name('shop_order_refund')->where('id',$refundid)->update(array('reply' => $refundcontent, 'status' => -1, 'endtime' => $time, 'lastupdate' => time()));
				model('shop')->plog('order.op.refund.submit', '订单退款拒绝 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 原因: ' . $refundcontent);
				$refundcount = Db::name('shop_order_refund')->where('orderid',$item['id'])->where('status','in',[0,3,4,5])->count();
				if($refundcount <= 0) {
					Db::name('shop_order')->where('id',$item['id'])->setField('refundstate',0);
				}
				Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '店家', 'title' => '卖家拒绝您的' . $rtypestr . '申请', 'content' => '买家已拒绝，拒绝原因：' . $refundcontent, 'createtime' => time()));
				if ($refund['rtype'] == 2) {
					$refundstate = 41;
				} elseif ($refund['rtype'] == 0) {
					$refundstate = 31;
				} else {
					if ($refund['rtype'] == 1) {
						$refundstate = 21;
					}
				}
				foreach ($goods as $key => $value) {
					Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
				}
				model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
			} else {
				if ($refundstatus == 2) {
					$refundtype = 2;
					$change_refund['reply'] = '';
					$change_refund['status'] = 1;
					$change_refund['refundtype'] = $refundtype;
					$change_refund['price'] = $refund['applyprice'];
					$change_refund['refundtime'] = $time;

					if (empty($refund['operatetime'])) {
						$change_refund['operatetime'] = $time;
					}
					$change_refund['lastupdate'] = $time;
					Db::name('shop_order_refund')->where('id',$refundid)->update($change_refund);
					model('order')->setGiveBalance($item['id'], 2);
					model('order')->setStocksAndCredits($item['id'], 2);

					if ($refund['orderprice'] == $refund['applyprice']) {
						if (!empty($item['couponid'])) {
							model('coupon')->returnConsumeCoupon($item['id']);
						}
					}
					$refundcount = Db::name('shop_order_refund')->where('orderid',$item['id'])->where('status','in',[0,3,4,5])->count();
					if($refundcount <= 0) {
						Db::name('shop_order')->where('id',$item['id'])->update(array('refundstate' => 0, 'status' => -1, 'refundtime' => $time));
					}

					$credits = model('order')->getGoodsCredit($goods);
					model('shop')->plog('order.op.refund.submit', '订单退款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 手动退款!');

					if (0 < $credits) {
					}

					foreach ($goods as $g) {
						if (0 < $g['isfullback']) {
							model('order')->fullbackstop($item['id']);
						}
						$salesreal = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id = og.orderid','left')->where('og.goodsid=' . $g['id'] . ' and o.status>=1')->sum('total');
						Db::name('shop_goods')->where('id',$g['id'])->setField('salesreal',$salesreal);
					}
					$plid = Db::name('shop_core_paylog')->insertGetId(array('mid' => $item['mid'], 'module' => 'shop', 'tid' => $item['ordersn'], 'fee' => -$item['price'], 'status' => 1, 'createtime' => time()));
					Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 2, 'operator' => '店家', 'title' => '卖家同意打款', 'content' => '请注意您的支付宝、微信信息，关注打款进度。或及时与商家联系协商', 'createtime' => time()));
					if ($refund['rtype'] == 2) {
						$refundstate = 47;
					} elseif ($refund['rtype'] == 0) {
						$refundstate = 37;
					} else {
						if ($refund['rtype'] == 1) {
							$refundstate = 27;
						}
					}
					foreach ($goods as $key => $value) {
						Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
					}
					model('notice')->sendOrderMessage($item['id'], true, null, $refundid);
				}
			}
			show_json(1);
		}
		$this->assign(['item'=>$item,'refund'=>$refund,'refund_address'=>$refund_address,'express_list'=>$express_list,'r_type'=>$r_type,'id'=>$id,'refundid'=>$refundid]);
		echo $this->fetch('order/op/refund/submit');
	}

}