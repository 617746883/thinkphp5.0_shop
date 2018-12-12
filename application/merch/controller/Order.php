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

	protected function orderData($status = '', $st = '') 
	{
		$merch = $this->merch;
		$psize = 20;
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		else {
			$is_openmerch = 0;
		}
		$condition = ' o.merchid = ' . $merch['id'] . ' and o.ismr=0 and o.deleted=0 and o.istrade=0 ';

		$searchtime = trim(input('searchtime'));
		if (!empty($searchtime) && is_array(input('time/a')) && in_array($searchtime, array('create', 'pay', 'send', 'finish'))) {
			$starttime = strtotime(input('time/a')['start']);
			$endtime = strtotime(input('time/a')['end']);
			$condition .= ' AND o.' . $searchtime . 'time >= ' . $starttime . ' AND o.' . $searchtime . 'time <= ' . $endtime;
		}

		if (input('paytype') != '') {
				$condition .= ' AND o.paytype =' . intval(input('paytype'));
		}

		if (!empty(input('searchfield')) && !empty(input('keyword'))) {
			$searchfield = trim(strtolower(input('searchfield')));
			$keyword = trim(input('keyword'));
			$sqlcondition = '';

			if ($searchfield == 'ordersn') {
				$condition .= " AND locate('" . $keyword . "',o.ordersn)>0";
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
			} else if ($searchfield == 'saler') {
				$condition .= " AND (locate('" . $keyword . "',sm.realname)>0 or locate('" . $keyword . "',sm.mobile)>0 or locate('" . $keyword . "',sm.nickname)>0 or locate('" . $keyword . "',s.salername)>0 )";
			} else if ($searchfield == 'verifycode') {
				$condition .= " AND (verifycode='" . $keyword . "' or locate('" . $keyword . "',o.verifycodes)>0)";
			} else if ($searchfield == 'store') {
				$condition .= " AND (locate('" . $keyword . "',store.merchname)>0)";
				$sqlcondition = ' left join ' . tablename('shop_store') . ' store on store.id = o.verifystoreid and store.uniacid=o.uniacid';
			}
		}
		$statuscondition = '';

		if ($status !== '') {
			if ($status == '-1') {
				$statuscondition = ' AND o.status=-1 and o.refundtime=0';
			} else if ($status == '4') {
				$statuscondition = ' AND o.refundstate>0 and o.refundid<>0';
			} else if ($status == '5') {
				$statuscondition = ' AND o.refundtime<>0';
			} else if ($status == '1') {
				$statuscondition = ' AND ( o.status = 1 or (o.status=0 and o.paytype=3) )';
			} else if ($status == '0') {
				$statuscondition = ' AND o.status = 0 and o.paytype<>3';
			} else if ($status == '2') {
				$statuscondition = ' AND ( o.status = 2 or (o.status=1 and o.sendtype>0) )';
			} else if ($status == '6') {
				$statuscondition = ' AND o.isverify = 1 ';
			} else {
				$statuscondition = ' AND o.status = ' . intval($status);
			}
		}
		
		$list = Db::name('shop_order')
			->alias('o')
			->join('shop_order_refund r','r.id =o.refundid','left')
			->join('member m','m.id=o.mid','left')
			->join('shop_member_address a','a.id=o.addressid','left')
			->join('shop_dispatch d','d.id = o.dispatchid','left')
			->join('shop_saler s','s.mid = o.verifyoperatorid','left')
			->join('member sm','sm.id = s.mid','left')
			->where($condition . $statuscondition)
			->field('o.* , a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea, a.street as astreet,a.address as aaddress,d.dispatchname,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,sm.id as salerid,sm.nickname as salernickname,s.salername,r.rtype,r.status as rstatus,o.sendtype')
			->order('o.createtime','desc')
			->paginate($psize);
		if ($_GET['export'] == 1) {
			
		}
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
				$pt = $value['paytype'];
				$value['statusvalue'] = $s;
				$value['statuscss'] = $orderstatus[$value['status']]['css'];
				$value['status'] = $orderstatus[$value['status']]['name'];
				if (($pt == 3) && empty($value['statusvalue'])) {
					$value['statuscss'] = $orderstatus[1]['css'];
					$value['status'] = $orderstatus[1]['name'];
				}

				if ($s == 1) {
					if ($value['isverify'] == 1) {
						$value['status'] = '待使用';

						if (0 < $value['sendtype']) {
							$value['status'] = '部分使用';
						}
					} else if (empty($value['addressid'])) {
						$value['status'] = '待取货';
					} else {
						if (0 < $value['sendtype']) {
							$value['status'] = '部分发货';
						}
					}
				}
				if ($s == -1) {
					if (!empty($value['refundtime'])) {
						$value['status'] = '已退款';
					}
				}

				$value['paytypevalue'] = $pt;
				$value['css'] = $paytype[$pt]['css'];
				$value['paytype'] = $paytype[$pt]['name'];
				$value['dispatchname'] = empty($value['addressid']) ? '自提' : $value['dispatchname'];

				if (empty($value['dispatchname'])) {
					$value['dispatchname'] = '快递';
				}

				$isonlyverifygoods = model('order')->checkisonlyverifygoods($value['id']);

				if ($isonlyverifygoods) {
					$value['dispatchname'] = '记次/时商品';
				}

				if ($pt == 3) {
					$value['dispatchname'] = '货到付款';
				} else if ($value['isverify'] == 1) {
					$value['dispatchname'] = '线下核销';
				} else if ($value['isvirtual'] == 1) {
					$value['dispatchname'] = '虚拟物品';
				} else {
					if (!empty($value['virtual'])) {
						$value['dispatchname'] = '虚拟物品(卡密)<br/>自动发货';
					}
				}

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

				$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.orderid',$value['id'])->field('g.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.changeprice,og.oldprice,op.specs,g.merchid,og.seckill,og.seckill_taskid,og.seckill_roomid,g.ispresell')->select();
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

					$goods .= '' . $og['title'] . "\r\n";

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
				$value['goods'] = set_medias($order_goods, 'thumb');
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

		if (input('export') == '1') {
            $list = Db::name('shop_order')
                ->alias('o')
                ->join('member m','o.mid = m.id','LEFT')
                ->where($condition)
                ->order('o.createtime','desc')
                ->field('o.*,m.id as memberid,m.nickname,m.realname,m.mobile')
                ->limit(15)
                ->select();
            foreach ($list as &$val) {
                $val['createtime'] = date('Y-m-d H:i:s', $val['createtime']);
                $val['paytime'] = date('Y-m-d H:i:s', $val['paytime']);
                $val['sendtime'] = date('Y-m-d H:i:s', $val['sendtime']);
                $val['finishtime'] = date('Y-m-d H:i:s', $val['finishtime']);
                $val['status'] = $orderstatus[$val['status']]['name'];
                $val['paytype'] = $paytype[$val['paytype']]['name'];
                $val['mnickname'] = $val['nickname'];
                $val['mreal_name'] = $val['realname'];
                $val['mmobile'] = $val['mobile'];
                $address = iunserializer($val['address']); 
                  
                $val['areal_name'] = $address['realname'];
                $val['amobile'] = $address['mobile'];
                $val['aprovince'] = $address['province'];
                $val['acity'] = $address['city'];
                $val['aarea'] = $address['area'];
                $val['aaddress'] = $address['address'];
                $ogoods = Db::name('shop_order_goods')
                    ->alias('o')
                    ->join('shop_goods g','o.goodsid = g.id','LEFT')
                    ->where('orderid', $val['id'])
                    ->select();
                $val['goods'] = $ogoods;
            }
            unset($val);
            $exportlist = array();
            foreach ($list as &$r ) {
                $ogoods = $r['goods'];
                unset($r['goods']);
                foreach ($ogoods as $k => $g ) {
                    if (0 < $k) {
                        $r['order_sn'] = '';
                        $r['mnickname'] = '';
                        $r['mreal_name'] = '';
                        $r['mmobile'] = '';
                        $r['areal_name'] = '';
                        $r['amobile'] = '';
                        $r['aprovince'] = '';
                        $r['acity'] = '';
                        $r['aarea'] = '';
                        $r['aaddress'] = '';
                        $r['paytype'] = '';
                        $r['goods_price'] = '';
                        $r['coupon_price'] = '';
                        $r['changeprice'] = '';
                        $r['change_dispatch_price'] = '';
                        $r['price'] = '';
                        $r['status'] = '';
                        $r['createtime'] = '';
                        $r['paytime'] = '';
                        $r['sendtime'] = '';
                        $r['finishtime'] = '';
                        $r['express_name'] = '';
                        $r['express_sn'] = '';
                        $r['remark'] = '';
                        $r['remark_saler'] = '';
                    }
                    $r['goods_title'] = $g['goods_name'];
                    $r['goods_goodssn'] = $g['goods_sn'];
                    $r['goods_optiontitle'] = $g['spec_key_name'];
                    $r['goods_total'] = $g['total'];
                    $r['goods_price1'] = $g['price'] / $g['total'];
                    $r['goods_price2'] = $g['real_price'] / $g['total'];
                    $r['goods_rprice1'] = $g['price'];
                    $r['goods_rprice2'] = $g['real_price'];
                    $exportlist[] = $r;
                }
            }
            unset($r);
            $columns = array(
                array('title' => '订单编号', 'field' => 'order_sn', 'width' => 24),
                array('title' => '会员昵称', 'field' => 'mnickname', 'width' => 12),
                array('title' => '会员姓名', 'field' => 'mreal_name', 'width' => 12),
                array('title' => '会员手机', 'field' => 'mmobile', 'width' => 12),
                array('title' => '收货姓名(或自提人)', 'field' => 'areal_name', 'width' => 12),
                array('title' => '联系电话', 'field' => 'amobile', 'width' => 12),
                array('title' => '收货地址', 'field' => 'aprovince', 'width' => 12),
                array('title' => '', 'field' => 'acity', 'width' => 12),
                array('title' => '', 'field' => 'aarea', 'width' => 12),
                array('title' => '', 'field' => 'aaddress', 'width' => 12),
                array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
                array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
                array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
                array('title' => '商品数量', 'field' => 'goods_total', 'width' => 12),
                array('title' => '商品单价(折扣前)', 'field' => 'goods_price1', 'width' => 12),
                array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
                array('title' => '商品价格(折扣前)', 'field' => 'goods_rprice1', 'width' => 12),
                array('title' => '商品价格(折扣后)', 'field' => 'goods_rprice2', 'width' => 12),
                array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
                array('title' => '商品小计', 'field' => 'goods_price', 'width' => 12),
                array('title' => '优惠券优惠', 'field' => 'coupon_price', 'width' => 12),
                array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
                array('title' => '运费改价', 'field' => 'change_dispatch_price', 'width' => 12),
                array('title' => '应收款', 'field' => 'price', 'width' => 12),
                array('title' => '状态', 'field' => 'status', 'width' => 12),
                array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
                array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
                array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
                array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
                array('title' => '快递公司', 'field' => 'express_name', 'width' => 24),
                array('title' => '快递单号', 'field' => 'express_sn', 'width' => 24),
                array('title' => '订单备注', 'field' => 'remark_send', 'width' => 36),
                array('title' => '卖家备注', 'field' => 'remark_saler', 'width' => 36),
            );
            model('excel')->export($exportlist, array( 'title' => '订单数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
            model('shop')->plog('order.op.export', '导出订单');
        }

		$total = $t['count'];
		$totalmoney = $t['sumprice'];
		$this->assign(['list'=>$list,'pager'=>$pager,'searchfield'=>$searchfield,'r_type'=>$r_type,'starttime'=>$starttime,'endtime'=>$endtime,'is_openmerch'=>$is_openmerch,'keyword'=>$keyword,'searchtime'=>$searchtime,'paytype'=>$paytype,'act'=>strtolower(Request::instance()->action())]);
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
			$user['address'] = $user['province'] . ' ' . $user['city'] . ' ' . $user['area'] . ' ' . $user['street'] . ' ' . $user['address'];
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
		return array('id' => $id, 'item' => $item, 'order_goods' => $order_goods);
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
					model('member')->setCredit($item['mid'], 'credit2', $realprice, array(0, $shopset['name'] . '退款: ' . $realprice . '元 订单号: ' . $item['ordersn']));
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