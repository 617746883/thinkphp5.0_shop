<?php
/**
 * 订单管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Order extends Base
{
	public function index()
	{
		return $this->fetch('');
	}

	public function olist1()
	{
		$listdata = $this->listdata(1);
		return $listdata;
	}

	public function olist2()
	{
		$listdata = $this->listdata(2);
		return $listdata;
	}

	public function olist3()
	{
		$listdata = $this->listdata(3);
		return $listdata;
	}

	public function olist0()
	{
		$listdata = $this->listdata(0);
		return $listdata;
	}

	public function olist6()
	{
		$listdata = $this->listdata(6);
		return $listdata;
	}

	public function olist_1()
	{
		$listdata = $this->listdata(-1);
		return $listdata;
	}

	public function olist_all()
	{
		$listdata = $this->listdata();
		return $listdata;
	}

	public function listdata($status = '')
	{
		$psize = 20;
		$merch = $this->merch;
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		else {
			$is_openmerch = 0;
		}
		$condition = ' o.merchshow = 1 and o.ismr=0 and o.deleted=0 and o.istrade=0 and o.merchid = ' . $merch['id'];

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
				$sqlcondition = ' left join ' . tablename('ewei_shop_store') . ' store on store.id = o.verifystoreid and store.uniacid=o.uniacid';
			}
			// else if ($searchfield == 'goodstitle') {
			// 	$sqlcondition = ' inner join ( select DISTINCT(og.orderid) from ' . tablename('ewei_shop_order_goods') . ' og left join ' . tablename('ewei_shop_goods') . ' g on g.id=og.goodsid where og.uniacid = \'' . $uniacid . '\' and (locate(:keyword,g.title)>0)) gs on gs.orderid=o.id';
			// }
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
			->join('shop_saler s','s.mid = o.verifyopenid','left')
			->join('member sm','sm.id = s.mid','left')
			->where($condition . $statuscondition)
			->field('o.* , a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea, a.street as astreet,a.address as aaddress,d.dispatchname,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,sm.id as salerid,sm.nickname as salernickname,s.salername,r.rtype,r.status as rstatus,o.sendtype')
			->order('o.createtime','desc')
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
		$is_merch = array();
		$is_merchname = 0;

		if ($merch_plugin) {
			$merch_user = model('store')->getListUser($list, 'merch_user');

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
					if (!empty($level) && empty($agentid)) {
						$commissions = iunserializer($og['commissions']);

						if (!empty($m1)) {
							if (is_array($commissions)) {
								$commission1 += (isset($commissions['level1']) ? floatval($commissions['level1']) : 0);
							}
							else {
								$c1 = iunserializer($og['commission1']);
								$l1 = $p->getLevel($m1['openid']);

								if (!empty($c1)) {
									$commission1 += (isset($c1['level' . $l1['id']]) ? $c1['level' . $l1['id']] : $c1['default']);
								}
							}
						}

						if (!empty($m2)) {
							if (is_array($commissions)) {
								$commission2 += (isset($commissions['level2']) ? floatval($commissions['level2']) : 0);
							}
							else {
								$c2 = iunserializer($og['commission2']);
								$l2 = $p->getLevel($m2['openid']);

								if (!empty($c2)) {
									$commission2 += (isset($c2['level' . $l2['id']]) ? $c2['level' . $l2['id']] : $c2['default']);
								}
							}
						}

						if (!empty($m3)) {
							if (is_array($commissions)) {
								$commission3 += (isset($commissions['level3']) ? floatval($commissions['level3']) : 0);
							}
							else {
								$c3 = iunserializer($og['commission3']);
								$l3 = $p->getLevel($m3['openid']);

								if (!empty($c3)) {
									$commission3 += (isset($c3['level' . $l3['id']]) ? $c3['level' . $l3['id']] : $c3['default']);
								}
							}
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
		$total = Db::name('shop_order')->alias('o')->where($condition . $statuscondition)->count();
		$totalmoney = Db::name('shop_order')->alias('o')->where($condition . $statuscondition)->sum('price');
		$this->assign(['list'=>$list,'pager'=>$pager,'searchfield'=>$searchfield,'r_type'=>$r_type,'starttime'=>$starttime,'endtime'=>$endtime,'is_openmerch'=>$is_openmerch,'keyword'=>$keyword,'searchtime'=>$searchtime,'paytype'=>$paytype,'total'=>$total,'totalmoney'=>$totalmoney,'act'=>strtolower(Request::instance()->action())]);
		return $this->fetch('order/list');
	}

	public function detail()
	{
		$id = input('id/d');
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
		}
		else {
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

		if (!empty($item['verifyopenid'])) {
			$saler = model('member')->getMember($item['verifyopenid']);

			if (empty($item['merchid'])) {
				$saler['salername'] = Db::name('shop_saler')->where('mid',$item['verifyopenid'])->value('salername');
			}
			else {
				$saler['salername'] = Db::name('shop_saler')->where('mid',$item['verifyopenid'])->value('salername');
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

							$v['nickname'] = Db::name('member')->where('id',$v['verifyopenid'])->value('nickname');

							if (empty($item['merchid'])) {
								$v['salername'] = Db::name('shop_saler')->where('mid',$v['verifyopenid'])->value('salername');
							}
							else {
								$v['salername'] = Db::name('shop_saler')->where('mid',$v['verifyopenid'])->value('salername');
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
		$item = Db::name('shop_order')->where('id',$id)->find();
		if (empty($item)) {
			show_json(0, '未找到订单!');
		}

		return array('id' => $id, 'item' => $item);
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
			model('shop')->plog('order.op.pay', '订单确认付款 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
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
				if ((0 < $item['refundstate']) && !empty($item['refundid'])) {
					$change_refund = array();
					$change_refund['status'] = -1;
					$change_refund['refundtime'] = $time;
					Db::name('shop_order_refund')->where('id',$item['refundid'])->update($change_refund);
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
				show_json(1);
			}
		} elseif($ops == 'finish') {
			$opdata = $this->opData();
			extract($opdata);
			Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 3, 'finishtime' => time()));
			model('order')->fullback($item['id']);

			model('member')->upgradeLevel($item['openid']);
			model('order')->setGiveBalance($item['id'], 1);
			model('notice')->sendOrderMessage($item['id']);

			model('coupon')->sendcouponsbytask($item['id']);
			if (!empty($item['couponid'])) {
				model('coupon')->backConsumeCoupon($item['id']);
			}

			model('shop')->plog('order.op.finish', '订单完成 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
			show_json(1);
		} elseif($ops == 'fetchcancel') {
			$opdata = $this->opData();
			extract($opdata);

			if ($item['status'] != 3) {
				show_json(0, '订单未取货，不需取消！');
			}
			Db::name('shop_order')->where('id',$item['id'])->update(array('status' => 1, 'finishtime' => 0));
			model('shop')->plog('order.op.fetchcancel', '订单取消取货 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn']);
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
				$d['verifyopenid'] = '';
			}

			Db::name('shop_order')->where('id',$item['id'])->update($d);
			model('order')->fullback($item['id']);

			model('coupon')->sendcouponsbytask($item['id']);
			if (!empty($item['couponid'])) {
				model('coupon')->backConsumeCoupon($item['id']);
			}

			if (!empty($item['refundid'])) {
				$refund = Db::name('shop_order_refund')->where('id',$item['refundid'])->find();

				if (!empty($refund)) {
					Db::name('shop_order_refund')->where('id',$item['refundid'])->setField('status',-1);
					Db::name('shop_order')->where('id',$item['id'])->setField('refundstate',0);
				}
			}

			$log = '订单确认取货';

			$log .= ' ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'];
			model('order')->setGiveBalance($item['id'], 1);
			model('member')->upgradeLevel($item['openid']);
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
					if (empty(input('ordergoodsid'))) {
						show_json(0, '请选择发货商品！');
					}

					$ogoods = array();
					$ogoods = Db::name('shop_order_goods')->where('orderid',$item['id'])->field('sendtype')->order('sendtype','desc')->select();
					$senddata = array('sendtype' => $ogoods[0]['sendtype'] + 1, 'sendtime' => $data['sendtime']);
					$data['sendtype'] = $ogoods[0]['sendtype'] + 1;
					$goodsid = input('ordergoodsid');

					foreach ($goodsid as $key => $value) {
						Db::name('shop_order_goods')->where('id',$value)->update($data);
					}

					$send_goods = Db::name('shop_order_goods')->where('orderid',$item['id'])->where('sendtype',0)->find();

					if (empty($send_goods)) {
						$senddata['status'] = 2;
					}
					Db::name('shop_order')->where('id',$item['id'])->update($senddata);
				}
				else {
					$data['status'] = 2;
					Db::name('shop_order')->where('id',$item['id'])->update($data);
				}

				if (!empty($item['refundid'])) {
					$refund = Db::name('shop_order_refund')->where('id',$item['refundid'])->find();

					if (!empty($refund)) {
						Db::name('shop_order_refund')->where('id',$item['refundid'])->update(array('status' => -1, 'endtime' => $time));
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
					}
					else {
						Db::name('shop_order')->where('id',$id)->update($change_data);
					}

					model('shop')->plog('order.op.changeexpress', '修改快递状态 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 快递公司: ' . $expresscom . ' 快递单号: ' . $expresssn);
					show_json(1);
				}
				else {
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
			}
			else {
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
					model('notice')->sendOrderChangeMessage($item['openid'], array('title' => '订单收货地址', 'orderid' => $item['id'], 'ordersn' => $item['ordersn'], 'olddata' => $oldaddress['address'], 'data' => $province . $city . $area . $address, 'type' => 0), 'orderstatus');
					show_json(1);
				}
			}
			$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street,'user'=>$user,'id'=>$id]);
			echo $this->fetch('order/op/changeaddress');
		}
	}

	/**
     * ajax return 七日交易记录.近7日交易时间,交易金额,交易数量
     */
	public function ajaxtransaction()
	{
		$orderPrice = $this->selectOrderPrice(7);
		$transaction = $this->selectTransaction($orderPrice['fetchall'], 7);

		if (empty($transaction)) {
			$i = 7;

			while (1 <= $i) {
				$transaction['price'][date('Y-m-d', time() - ($i * 3600 * 24))] = 0;
				$transaction['count'][date('Y-m-d', time() - ($i * 3600 * 24))] = 0;
				--$i;
			}
		}

		echo json_encode(array('price_key' => array_keys($transaction['price']), 'price_value' => array_values($transaction['price']), 'count_value' => array_values($transaction['count'])));
	}

	/**
     * 查询订单金额
     * @param int $day 查询天数
     * @return bool
     */
	protected function selectOrderPrice($day = 0)
	{
		$day = (int) $day;
		$merch = $this->merch;
		if ($day != 0) {
			$createtime1 = strtotime(date('Y-m-d', time() - ($day * 3600 * 24)));
			$createtime2 = strtotime(date('Y-m-d', time()));
		}
		else {
			$createtime1 = strtotime(date('Y-m-d', time()));
			$createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
		}
		
		$pdo_res = Db::name('shop_order')->where('ismr=0 and isparent=0 and (status > 0 or ( status=0 and paytype=3)) and deleted=0 and merchid = ' . $merch['id'])->where('createtime','between',[$createtime1,$createtime2])->field('id,price,createtime')->select();
		$price = 0;

		foreach ($pdo_res as $arr) {
			$price += $arr['price'];
		}

		$result = array('price' => round($price, 1), 'count' => count($pdo_res), 'fetchall' => $pdo_res);
		return $result;
	}

	/**
     * 查询近七天交易记录
     * @param array $pdo_fetchall 查询订单的记录
     * @param int $days 查询天数默认7
     * @return $transaction["price"] 七日 每日交易金额
     * @return $transaction["count"] 七日 每日交易订单数
     */
	protected function selectTransaction(array $pdo_fetchall, $days = 7)
	{
		$transaction = array();
		$days = (int) $days;

		if (!empty($pdo_fetchall)) {
			$i = $days;

			while (1 <= $i) {
				$transaction['price'][date('Y-m-d', time() - ($i * 3600 * 24))] = 0;
				$transaction['count'][date('Y-m-d', time() - ($i * 3600 * 24))] = 0;
				--$i;
			}

			foreach ($pdo_fetchall as $key => $value) {
				if (array_key_exists(date('Y-m-d', $value['createtime']), $transaction['price'])) {
					$transaction['price'][date('Y-m-d', $value['createtime'])] += $value['price'];
					$transaction['count'][date('Y-m-d', $value['createtime'])] += 1;
				}
			}

			return $transaction;
		}

		return array();
	}

	/**
     * ajax return 交易订单
     */
	protected function order($day)
	{
		$day = (int) $day;
		$orderPrice = $this->selectOrderPrice($day);
		$orderPrice['avg'] = empty($orderPrice['count']) ? 0 : round($orderPrice['price'] / $orderPrice['count'], 1);
		unset($orderPrice['fetchall']);
		return $orderPrice;
	}

	public function ajaxorder()
	{
		$order0 = $this->order(0);
		$order1 = $this->order(1);
		$order7 = $this->order(7);
		$order30 = $this->order(30);
		$order7['price'] = $order7['price'] + $order0['price'];
		$order7['count'] = $order7['count'] + $order0['count'];
		$order7['avg'] = empty($order7['count']) ? 0 : round($order7['price'] / $order7['count'], 1);
		$order30['price'] = $order30['price'] + $order0['price'];
		$order30['count'] = $order30['count'] + $order0['count'];
		$order30['avg'] = empty($order30['count']) ? 0 : round($order30['price'] / $order30['count'], 1);
		show_json(1, array('order0' => $order0, 'order1' => $order1, 'order7' => $order7, 'order30' => $order30));
	}

	public function ajaxgettotals() 
	{
		$merch = $this->merch;
		$totals = model('order')->getTotals($merch['id']);
		$result = ((empty($totals) ? array() : $totals));
		show_json(1, $result);
	}

}