<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Order extends \think\Model
{
	public static function getGoodsDiscountPrice($g, $level, $type = 0)
	{
		if (!empty($level['id'])) {
			$level = Db::name('member_level')->where('id',$level['id'])->select();
			$level = (empty($level) ? array() : $level);
		}

		if ($type == 0) {
			$total = $g['total'];
		}
		else {
			$total = 1;
		}

		$gprice = $g['marketprice'] * $total;

		if (empty($g['buyagain_islong'])) {
			$gprice = $g['marketprice'] * $total;
		}

		$buyagain_sale = true;
		$buyagainprice = 0;
		$canbuyagain = false;

		if (empty($g['is_task_goods'])) {
			if (0 < floatval($g['buyagain'])) {
				if (model('goods')->canBuyAgain($g)) {
					$canbuyagain = true;

					if (empty($g['buyagain_sale'])) {
						$buyagain_sale = false;
					}
				}
			}
		}

		$price = $gprice;
		$price1 = $gprice;
		$price2 = $gprice;
		$taskdiscountprice = 0;
		$lotterydiscountprice = 0;

		if (!empty($g['is_task_goods'])) {
			$buyagain_sale = false;
			$price = $g['task_goods']['marketprice'] * $total;

			if ($price < $gprice) {
				$d_price = abs($gprice - $price);

				if ($g['is_task_goods'] == 1) {
					$taskdiscountprice = $d_price;
				}
				else {
					if ($g['is_task_goods'] == 2) {
						$lotterydiscountprice = $d_price;
					}
				}
			}
		}

		$discountprice = 0;
		$isdiscountprice = 0;
		$isd = false;
		@$isdiscount_discounts = json_decode($g['isdiscount_discounts'], true);
		$discounttype = 0;
		$isCdiscount = 0;
		$isHdiscount = 0;
		if ($g['isdiscount'] && (time() <= $g['isdiscount_time']) && $buyagain_sale) {
			if (is_array($isdiscount_discounts)) {
				$key = (!empty($level['id']) ? 'level' . $level['id'] : 'default');
				if (!isset($isdiscount_discounts['type']) || empty($isdiscount_discounts['type'])) {
					if (empty($g['merchsale'])) {
						$isd = trim($isdiscount_discounts[$key]['option0']);
						if (!empty($isd)) {
							$price1 = self::getFormartDiscountPrice($isd, $gprice, $total);
						}
					}
					else {
						$isd = trim($isdiscount_discounts['merch']['option0']);

						if (!empty($isd)) {
							$price1 = self::getFormartDiscountPrice($isd, $gprice, $total);
						}
					}
				}
				else if (empty($g['merchsale'])) {
					$isd = trim($isdiscount_discounts[$key]['option' . $g['optionid']]);

					if (!empty($isd)) {
						$price1 = self::getFormartDiscountPrice($isd, $gprice, $total);
					}
				}
				else {
					$isd = trim($isdiscount_discounts['merch']['option' . $g['optionid']]);

					if (!empty($isd)) {
						$price1 = self::getFormartDiscountPrice($isd, $gprice, $total);
					}
				}
			}

			if ($gprice <= $price1) {
				$isdiscountprice = 0;
				$isCdiscount = 0;
			}
			else {
				$isdiscountprice = abs($price1 - $gprice);
				$isCdiscount = 1;
			}
		}

		if (empty($g['isnodiscount']) && $buyagain_sale) {
			$discounts = json_decode($g['discounts'], true);

			if (is_array($discounts)) {
				$key = (!empty($level['id']) ? 'level' . $level['id'] : 'default');
				if (!isset($discounts['type']) || empty($discounts['type'])) {
					if (!empty($discounts[$key])) {
						$dd = floatval($discounts[$key]);
						if ((0 < $dd) && ($dd < 10)) {
							$price2 = round(($dd / 10) * $gprice, 2);
						}
					}
					else {
						$dd = floatval($discounts[$key . '_pay'] * $total);
						$md = floatval($level['discount']);

						if (!empty($dd)) {
							$price2 = round($dd, 2);
						}
						else {
							if ((0 < $md) && ($md < 10)) {
								$price2 = round(($md / 10) * $gprice, 2);
							}
						}
					}
				}
				else {
					$isd = trim($discounts[$key]['option' . $g['optionid']]);

					if (!empty($isd)) {
						$price2 = self::getFormartDiscountPrice($isd, $gprice, $total);
					}
				}
			}

			if ($gprice <= $price2) {
				$discountprice = 0;
				$isHdiscount = 0;
			}
			else {
				$discountprice = abs($price2 - $gprice);
				$isHdiscount = 1;
			}
		}

		if ($isCdiscount == 1) {
			$price = $price1;
			$discounttype = 1;
		}
		else {
			if ($isHdiscount == 1) {
				$price = $price2;
				$discounttype = 2;
			}
		}

		$unitprice = round($price / $total, 2);
		$isdiscountunitprice = round($isdiscountprice / $total, 2);
		$discountunitprice = round($discountprice / $total, 2);

		if ($canbuyagain) {
			if (empty($g['buyagain_islong'])) {
				$buyagainprice = ($unitprice * (10 - $g['buyagain'])) / 10;
			}
			else {
				$buyagainprice = ($price * (10 - $g['buyagain'])) / 10;
			}
		}

		$price = $price - $buyagainprice;
		return array('unitprice' => $unitprice, 'price' => $price, 'taskdiscountprice' => $taskdiscountprice, 'lotterydiscountprice' => $lotterydiscountprice, 'discounttype' => $discounttype, 'isdiscountprice' => $isdiscountprice, 'discountprice' => $discountprice, 'isdiscountunitprice' => $isdiscountunitprice, 'discountunitprice' => $discountunitprice, 'price0' => $gprice, 'price1' => $price1, 'price2' => $price2, 'buyagainprice' => $buyagainprice);
	}

	public function getGoodsDiscounts($goods, $isdiscount_discounts, $levelid, $options = array())
	{
		$key = (empty($levelid) ? 'default' : 'level' . $levelid);
		$prices = array();

		if (empty($goods['merchsale'])) {
			if (!empty($isdiscount_discounts[$key])) {
				foreach ($isdiscount_discounts[$key] as $k => $v) {
					$k = substr($k, 6);
					$op_marketprice = model('goods')->getOptionPirce($goods['id'], $k);
					$gprice = self::getFormartDiscountPrice($v, $op_marketprice);
					$prices[] = $gprice;

					if (!empty($options)) {
						foreach ($options as $key => $value) {
							if ($value['id'] == $k) {
								$options[$key]['marketprice'] = $gprice;
							}
						}
					}
				}
			}
		}
		else {
			if (!empty($isdiscount_discounts['merch'])) {
				foreach ($isdiscount_discounts['merch'] as $k => $v) {
					$k = substr($k, 6);
					$op_marketprice = model('goods')->getOptionPirce($goods['id'], $k);
					$gprice = self::getFormartDiscountPrice($v, $op_marketprice);
					$prices[] = $gprice;

					if (!empty($options)) {
						foreach ($options as $key => $value) {
							if ($value['id'] == $k) {
								$options[$key]['marketprice'] = $gprice;
							}
						}
					}
				}
			}
		}

		$data = array();
		$data['prices'] = $prices;
		$data['options'] = $options;
		return $data;
	}

	public static function getFormartDiscountPrice($isd, $gprice, $gtotal = 1)
	{
		$price = $gprice;

		if (!empty($isd)) {
			if (strexists($isd, '%')) {
				$dd = floatval(str_replace('%', '', $isd));
				if ((0 < $dd) && ($dd < 100)) {
					$price = round(($dd / 100) * $gprice, 2);
				}
			}
			else {
				if (0 < floatval($isd)) {
					$price = round(floatval($isd * $gtotal), 2);
				}
			}
		}

		return $price;
	}

	public function checkOrderGoods($orderid)
	{
		$flag = 0;
		$msg = '订单中的商品' . '<br/>';
		$list = Db::name('shop_goods')->alias('g')
			->join('shop_order_goods og','g.id=og.goodsid','left')
			->where('og.orderid = ' . $orderid)
			->select();

		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if (empty($v['status']) || !empty($v['deleted'])) {
					$flag = 1;
					$msg .= $v['title'] . '<br/>';
				}
			}

			if ($flag == 1) {
				$msg .= '已下架,不能付款!';
			}
		}

		$data = array();
		$data['flag'] = $flag;
		$data['msg'] = $msg;
		return $data;
	}

	public static function complete($orderid, $type, $ordersn)
	{
		
	}

	/**
     * //处理订单库存及用户积分情况(赠送积分)
     * @param type $orderid
     * @param type $type 0 下单 1 支付 2 取消 3 确认收货
     */
	public function setStocksAndCredits($orderid = '', $type = 0)
	{
		$order = Db::name('shop_order')->where('id',$orderid)->field('id,ordersn,price,mid,dispatchtype,addressid,carrier,status,isparent,paytype,isnewstore,storeid,istrade,status')->find();

		if (!empty($order['istrade'])) {
			return NULL;
		}

		if ($order['isparent'] == 1) {
			$condition = ' og.parentorderid=' . $orderid;
		} else {
			$condition = ' og.orderid= ' . $orderid;
		}

		$goods = Db::name('shop_order_goods')->alias('og')
			->join('shop_goods g','g.id=og.goodsid','left')
			->where($condition)
			->field('og.goodsid,og.total,g.totalcnf,og.realprice,g.credit,og.optionid,g.total as goodstotal,og.optionid,g.sales,g.salesreal,g.type')
			->select();

		$credits = 0;

		foreach ($goods as $g) {
			$goods_item = Db::name('shop_goods')->where('id',$g['goodsid'])->field('total as goodstotal')->find();
			$g['goodstotal'] = $goods_item['goodstotal'];

			$stocktype = 0;

			if ($type == 0) {
				if ($g['totalcnf'] == 0) {
					$stocktype = -1;
				}
			} else if ($type == 1) {
				if ($g['totalcnf'] == 1) {
					$stocktype = -1;
				}
			} else {
				if ($type == 2) {
					if (1 <= $order['status']) {
						if ($g['totalcnf'] == 1) {
							$stocktype = 1;
						}
					} else {
						if ($g['totalcnf'] == 0) {
							$stocktype = 1;
						}
					}
				}
			}

			if (!empty($stocktype)) {
				$data = model('common')->getSysset('trade');

				if (!empty($data['stockwarn'])) {
					$stockwarn = intval($data['stockwarn']);
				} else {
					$stockwarn = 5;
				}

				if (!empty($g['optionid'])) {
					$option = model('goods')->getOption($g['goodsid'], $g['optionid']);

					if (!empty($option) && ($option['stock'] != -1)) {
						$stock = -1;

						if ($stocktype == 1) {
							$stock = $option['stock'] + $g['total'];
						} else {
							if ($stocktype == -1) {
								$stock = $option['stock'] - $g['total'];
								($stock <= 0) && ($stock = 0);
								if ($stock <= $stockwarn) {
									model('notice')->sendStockWarnMessage($g['goodsid'], $g['optionid']);
								}
							}
						}

						if ($stock != -1) {
							Db::name('shop_goods_option')->where('goodsid',$g['goodsid'])->where('id',$g['optionid'])->setField('stock',$stock);
						}
					}
				}

				if (!empty($g['goodstotal']) && ($g['goodstotal'] != -1)) {
					$totalstock = -1;

					if ($stocktype == 1) {
						$totalstock = $g['goodstotal'] + $g['total'];
					} else {
						if ($stocktype == -1) {
							$totalstock = $g['goodstotal'] - $g['total'];
							($totalstock <= 0) && ($totalstock = 0);
							if ($totalstock <= $stockwarn) {
								model('notice')->sendStockWarnMessage($g['goodsid'], 0);
							}
						}
					}

					if ($totalstock != -1) {
						Db::name('shop_goods')->where('id',$g['goodsid'])->setField('total',$totalstock);
					}
				}
			}

			$isgoodsdata = model('common')->getPluginset('sale');
			if(!empty($isgoodsdata)) {
				$isgoodspoint = iunserializer($isgoodsdata['credit1']);
				if (!empty($isgoodspoint['isgoodspoint']) && ($isgoodspoint['isgoodspoint'] == 1)) {
					$gcredit = trim($g['credit']);

					if (!empty($gcredit)) {
						if (strexists($gcredit, '%')) {
							$credits += intval((floatval(str_replace('%', '', $gcredit)) / 100) * $g['realprice']);
						} else {
							$credits += intval($g['credit']) * $g['total'];
						}
					}
				}

				if ($type == 0) {
				} else {
					if ($type == 1) {
						if (1 <= $order['status']) {
							$salesreal = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id = og.orderid','left')->where('og.goodsid',$g['goodsid'])->where('o.status','>=',1)->field('ifnull(sum(total),0) as salesreal')->find();
							Db::name('shop_goods')->where('id',$g['goodsid'])->setField('salesreal',$salesreal);
						}
					}
				}
			}			
		}

		if (0 < $credits) {
			$shopset = model('common')->getSysset('shop');

			if ($type == 3) {
				if ($order['status'] == 3) {
					model('member')->setCredit($order['mid'], 'credit1', $credits, array(0, $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']));
					model('notice')->sendMemberPointChange($order['mid'], $credits, 0);
				}
			} else {
				if ($type == 2) {
					if ($order['status'] == 3) {
						model('member')->setCredit($order['mid'], 'credit1', 0 - $credits, array(0, $shopset['name'] . '购物取消订单扣除积分 订单号: ' . $order['ordersn']));
						model('notice')->sendMemberPointChange($order['mid'], $credits, 1);
					}
				}
			}
		} else if ($type == 3) {
			if ($order['status'] == 3) {
				$money = 0;
				// $money = com_run('sale::getCredit1', $order['mid'], (double) $order['price'], $order['paytype'], 1);

				if (0 < $money) {
					model('notice')->sendMemberPointChange($order['mid'], $money, 0);
				}
			}
		} else {
			if ($type == 2) {
				if ($order['status'] == 3) {
					$money = 0;
					// $money = com_run('sale::getCredit1', $order['mid'], (double) $order['price'], $order['paytype'], 1, 1);

					if (0 < $money) {
						model('notice')->sendMemberPointChange($order['mid'], $money, 1);
					}
				}
			}
		}
	}

	public function fullback($orderid)
	{
		$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_order o','og.orderid = o.id','left')->where('og.orderid = ' . $orderid)->field('o.mid,og.optionid,og.goodsid,og.price,og.total')->select();

		foreach ($order_goods as $key => $value) {
			if (0 < $value['optionid']) {
				$goods = Db::name('shop_goods')->alias('g')->join('shop_goods_option go','go.goodsid = ' . $value['goodsid'] . ' and go.id = ' . $value['optionid'],'left')->where('g.id',$value['goodsid'])->find();
			}
			else {
				$goods = Db::name('shop_goods')->where('id',$value['goodsid'])->find();
			}

			if (0 < $goods['isfullback']) {
				$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$value['goodsid'])->field('id,minallfullbackallprice,maxallfullbackallprice,minallfullbackallratio,maxallfullbackallratio,`day`,\r\n                          fullbackprice,fullbackratio,status,hasoption,marketprice,`type`,startday')->find();
				if (!empty($fullbackgoods) && $goods['hasoption'] && (0 < $value['optionid'])) {
					$option = Db::name('shop_goods_option')->where('goodsid',$value['goodsid'])->where('id',$value['optionid'])->field('id,title,allfullbackprice,allfullbackratio,fullbackprice,fullbackratio,`day`')->find();

					if (!empty($option)) {
						$fullbackgoods['minallfullbackallprice'] = $option['allfullbackprice'];
						$fullbackgoods['minallfullbackallratio'] = $option['allfullbackratio'];
						$fullbackgoods['fullbackprice'] = $option['fullbackprice'];
						$fullbackgoods['fullbackratio'] = $option['fullbackratio'];
						$fullbackgoods['day'] = $option['day'];
					}
				}

				$fullbackgoods['startday'] = $fullbackgoods['startday'] - 1;

				if (!empty($fullbackgoods)) {
					$data = array('uniacid' => $uniacid, 'orderid' => $orderid, 'mid' => $value['mid'], 'day' => $fullbackgoods['day'], 'fullbacktime' => strtotime('+' . $fullbackgoods['startday'] . ' days'), 'goodsid' => $value['goodsid'], 'createtime' => time());

					if (0 < $fullbackgoods['type']) {
						$data['price'] = ($value['price'] * $fullbackgoods['minallfullbackallratio']) / 100;
						$data['priceevery'] = ($value['price'] * $fullbackgoods['fullbackratio']) / 100;
					}
					else {
						$data['price'] = $value['total'] * $fullbackgoods['minallfullbackallprice'];
						$data['priceevery'] = $value['total'] * $fullbackgoods['fullbackprice'];
					}
					Db::name('shop_fullback_log')->insert($data);
				}
			}
		}
	}

	/**
     * 处理赠送余额情况
     * @param type $orderid
     * @param type $type 1 订单完成 2 售后
     */
	public function setGiveBalance($orderid = '', $type = 0)
	{
		$order = Db::name('shop_order')->where('id',$orderid)->field('id,ordersn,price,mid,dispatchtype,addressid,carrier,status')->find();
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$orderid)->field('og.goodsid,og.total,g.totalcnf,og.realprice,g.money,og.optionid,g.total as goodstotal,og.optionid,g.sales,g.salesreal')->select();
		$balance = 0;

		foreach ($goods as $g) {
			$gbalance = trim($g['money']);

			if (!empty($gbalance)) {
				if (strexists($gbalance, '%')) {
					$balance += round((floatval(str_replace('%', '', $gbalance)) / 100) * $g['realprice'], 2);
				} else {
					$balance += round($g['money'], 2) * $g['total'];
				}
			}
		}

		if (0 < $balance) {
			$shopset = model('common')->getSysset('shop');

			if ($type == 1) {
				if ($order['status'] == 3) {
					model('member')->setCredit($order['mid'], 'credit2', $balance, array(0, $shopset['name'] . '购物赠送余额 订单号: ' . $order['ordersn']));
				}
			}
			else {
				if ($type == 2) {
					if (1 <= $order['status']) {
						model('member')->setCredit($order['mid'], 'credit2', 0 - $balance, array(0, $shopset['name'] . '购物取消订单扣除赠送余额 订单号: ' . $order['ordersn']));
					}
				}
			}
		}
	}

	/**
     * 获取子订单
     * @global type $_W
     * @param type $orderid
     */
	public function getChildOrder($orderid)
	{
		$list = Db::name('shop_order')->where('parentid',$orderid)->field('id,ordersn,status,finishtime,couponid,merchid')->select();
		return $list;
	}

	public function getMerchEnough($merch_array)
	{
		$merch_enough_total = 0;
		$merch_saleset = array();

		foreach ($merch_array as $key => $value) {
			$merchid = $key;

			if (0 < $merchid) {
				$enoughs = $value['enoughs'];

				if (!empty($enoughs)) {
					$ggprice = $value['ggprice'];

					foreach ($enoughs as $e) {
						if ((floatval($e['enough']) <= $ggprice) && (0 < floatval($e['money']))) {
							$merch_array[$merchid]['showenough'] = 1;
							$merch_array[$merchid]['enoughmoney'] = $e['enough'];
							$merch_array[$merchid]['enoughdeduct'] = $e['money'];
							$merch_saleset['merch_showenough'] = 1;
							$merch_saleset['merch_enoughmoney'] += $e['enough'];
							$merch_saleset['merch_enoughdeduct'] += $e['money'];
							$merch_enough_total += floatval($e['money']);
							break;
						}
					}
				}
			}
		}

		$data = array();
		$data['merch_array'] = $merch_array;
		$data['merch_enough_total'] = $merch_enough_total;
		$data['merch_saleset'] = $merch_saleset;
		return $data;
	}

	public function getOrderDispatchPrice($goods, $member, $address, $saleset = false, $merch_array, $t, $loop = 0)
	{
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$realprice = 0;
		$dispatch_price = 0;
		$dispatch_array = array();
		$dispatch_merch = array();
		$total_array = array();
		$totalprice_array = array();
		$nodispatch_array = array();
		$goods_num = count($goods);
		$seckill_payprice = 0;
		$seckill_dispatchprice = 0;
		$user_city = '';
		$user_city_code = '';

		if (empty($new_area)) {
			if (!empty($address)) {
				$user_city = $user_city_code = $address['city'];
			}
			else {
				if (!empty($member['city'])) {
					if (!strexists($member['city'], '市')) {
						$member['city'] = $member['city'] . '市';
					}

					$user_city = $user_city_code = $member['city'];
				}
			}
		}
		else {
			if (!empty($address)) {
				$user_city = $address['city'] . $address['area'];
				$user_city_code = $address['datavalue'];
			}
		}

		foreach ($goods as $g) {
			$realprice += $g['ggprice'];
			$dispatch_merch[$g['merchid']] = 0;
			$total_array[$g['goodsid']] += $g['total'];
			$totalprice_array[$g['goodsid']] += $g['ggprice'];
		}
		foreach ($goods as $g) {
			$seckillinfo = false;
			if ($seckillinfo && ($seckillinfo['status'] == 0)) {
				$seckill_payprice += $g['ggprice'];
			}

			$isnodispatch = 0;
			$sendfree = false;
			$merchid = $g['merchid'];

			if (($g['type'] != 1) && ($g['type'] != 4)) {
				$sendfree = true;
			}
			
			if (!empty($g['issendfree'])) {
				$sendfree = true;
			}
			else {
				if ($seckillinfo && ($seckillinfo['status'] == 0)) {
				}
				else {
					if (($g['ednum'] <= $total_array[$g['goodsid']]) && (0 < $g['ednum'])) {
						if (empty($new_area)) {
							$gareas = explode(';', $g['edareas']);
						}
						else {
							$gareas = explode(';', $g['edareas_code']);
						}

						if (empty($gareas)) {
							$sendfree = true;
						}
						else if (!empty($address)) {
							if (!in_array($user_city_code, $gareas)) {
								$sendfree = true;
							}
						}
						else if (!empty($member['city'])) {
							if (!in_array($member['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else {
							$sendfree = true;
						}
					}
				}

				if ($seckillinfo && ($seckillinfo['status'] == 0)) {
				}
				else {
					if ((floatval($g['edmoney']) <= $totalprice_array[$g['goodsid']]) && (0 < floatval($g['edmoney']))) {
						if (empty($new_area)) {
							$gareas = explode(';', $g['edareas']);
						}
						else {
							$gareas = explode(';', $g['edareas_code']);
						}

						if (empty($gareas)) {
							$sendfree = true;
						}
						else if (!empty($address)) {
							if (!in_array($user_city_code, $gareas)) {
								$sendfree = true;
							}
						}
						else if (!empty($member['city'])) {
							if (!in_array($member['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else {
							$sendfree = true;
						}
					}
				}
			}

			if ($g['dispatchtype'] == 1) {
				if (!empty($user_city)) {
					if (empty($new_area)) {
						$citys = model('dispatch')->getAllNoDispatchAreas();
					}
					else {
						$citys = model('dispatch')->getAllNoDispatchAreas('', 1);
					}

					if (!empty($citys)) {
						if (in_array($user_city_code, $citys) && !empty($citys)) {
							$isnodispatch = 1;
							$has_goodsid = 0;

							if (!empty($nodispatch_array['goodid'])) {
								if (in_array($g['goodsid'], $nodispatch_array['goodid'])) {
									$has_goodsid = 1;
								}
							}

							if ($has_goodsid == 0) {
								$nodispatch_array['goodid'][] = $g['goodsid'];
								$nodispatch_array['title'][] = $g['title'];
								$nodispatch_array['city'] = $user_city;
							}
						}
					}
				}

				if ((0 < $g['dispatchprice']) && !$sendfree && ($isnodispatch == 0)) {
					$dispatch_merch[$merchid] += $g['dispatchprice'];
					if ($seckillinfo && ($seckillinfo['status'] == 0)) {
						$seckill_dispatchprice += $g['dispatchprice'];
					}
					else {
						$dispatch_price += $g['dispatchprice'];
					}
				}
			}
			else {
				if ($g['dispatchtype'] == 0) {
					if (empty($g['dispatchid'])) {
						$dispatch_data = model('dispatch')->getDefaultDispatch($merchid);
					} else {
						$dispatch_data = model('dispatch')->getOneDispatch($g['dispatchid']);
					}
					if (empty($dispatch_data)) {
						$dispatch_data = model('dispatch')->getNewDispatch($merchid);
					}
					if (!empty($dispatch_data)) {
						$isnoarea = 0;
						$dkey = $dispatch_data['id'];
						$isdispatcharea = intval($dispatch_data['isdispatcharea']);

						if (!empty($user_city)) {
							if (empty($isdispatcharea)) {
								if (empty($new_area)) {
									$citys = model('dispatch')->getAllNoDispatchAreas($dispatch_data['nodispatchareas']);
								}
								else {
									$citys = model('dispatch')->getAllNoDispatchAreas($dispatch_data['nodispatchareas_code'], 1);
								}

								if (!empty($citys)) {
									if (in_array($user_city_code, $citys)) {
										$isnoarea = 1;
									}
								}
							} else {
								if (empty($new_area)) {
									$citys = model('dispatch')->getAllNoDispatchAreas();
								}
								else {
									$citys = model('dispatch')->getAllNoDispatchAreas('', 1);
								}

								if (!empty($citys)) {
									if (in_array($user_city_code, $citys)) {
										$isnoarea = 1;
									}
								}

								if (empty($isnoarea)) {
									$isnoarea = model('dispatch')->checkOnlyDispatchAreas($user_city_code, $dispatch_data);
								}
							}

							if (!empty($isnoarea)) {
								$isnodispatch = 1;
								$has_goodsid = 0;

								if (!empty($nodispatch_array['goodid'])) {
									if (in_array($g['goodsid'], $nodispatch_array['goodid'])) {
										$has_goodsid = 1;
									}
								}

								if ($has_goodsid == 0) {
									$nodispatch_array['goodid'][] = $g['goodsid'];
									$nodispatch_array['title'][] = $g['title'];
									$nodispatch_array['city'] = $user_city;
								}
							}
						}

						if (!$sendfree && ($isnodispatch == 0)) {
							$areas = unserialize($dispatch_data['areas']);

							if ($dispatch_data['calculatetype'] == 1) {
								$param = $g['total'];
							}
							else {
								$param = $g['weight'] * $g['total'];
							}

							if (array_key_exists($dkey, $dispatch_array)) {
								$dispatch_array[$dkey]['param'] += $param;
							}
							else {
								$dispatch_array[$dkey]['data'] = $dispatch_data;
								$dispatch_array[$dkey]['param'] = $param;
							}

							if ($seckillinfo && ($seckillinfo['status'] == 0)) {
								if (array_key_exists($dkey, $dispatch_array)) {
									$dispatch_array[$dkey]['seckillnums'] += $param;
								}
								else {
									$dispatch_array[$dkey]['seckillnums'] = $param;
								}
							}
						}
					}
				}
			}
		}
		if (!empty($dispatch_array)) {
			$dispatch_info = array();

			foreach ($dispatch_array as $k => $v) {
				$dispatch_data = $dispatch_array[$k]['data'];
				$param = $dispatch_array[$k]['param'];
				$areas = unserialize($dispatch_data['areas']);

				if (!empty($address)) {
					$dprice = model('dispatch')->getCityDispatchPrice($areas, $address, $param, $dispatch_data);
				}
				else if (!empty($member['city'])) {
					$dprice = model('dispatch')->getCityDispatchPrice($areas, $member, $param, $dispatch_data);
				}
				else {
					$dprice = model('dispatch')->getDispatchPrice($param, $dispatch_data);
				}

				$merchid = $dispatch_data['merchid'];
				$dispatch_merch[$merchid] += $dprice;

				if (0 < $v['seckillnums']) {
					$seckill_dispatchprice += $dprice;
				}
				else {
					$dispatch_price += $dprice;
				}

				$dispatch_info[$dispatch_data['id']]['price'] += $dprice;
				$dispatch_info[$dispatch_data['id']]['freeprice'] = intval($dispatch_data['freeprice']);
			}

			if (!empty($dispatch_info)) {
				foreach ($dispatch_info as $k => $v) {
					if ((0 < $v['freeprice']) && ($v['freeprice'] <= $v['price'])) {
						$dispatch_price -= $v['price'];
					}
				}

				if ($dispatch_price < 0) {
					$dispatch_price = 0;
				}
			}
		}

		if (!empty($merch_array)) {
			foreach ($merch_array as $key => $value) {
				$merchid = $key;

				if (0 < $merchid) {
					$merchset = $value['set'];

					if (!empty($merchset['enoughfree'])) {
						if (floatval($merchset['enoughorder']) <= 0) {
							$dispatch_price = $dispatch_price - $dispatch_merch[$merchid];
							$dispatch_merch[$merchid] = 0;
						}
						else {
							if (floatval($merchset['enoughorder']) <= $merch_array[$merchid]['ggprice']) {
								if (empty($merchset['enoughareas'])) {
									$dispatch_price = $dispatch_price - $dispatch_merch[$merchid];
									$dispatch_merch[$merchid] = 0;
								}
								else {
									$areas = explode(';', $merchset['enoughareas']);

									if (!empty($address)) {
										if (!in_array($address['city'], $areas)) {
											$dispatch_price = $dispatch_price - $dispatch_merch[$merchid];
											$dispatch_merch[$merchid] = 0;
										}
									}
									else if (!empty($member['city'])) {
										if (!in_array($member['city'], $areas)) {
											$dispatch_price = $dispatch_price - $dispatch_merch[$merchid];
											$dispatch_merch[$merchid] = 0;
										}
									}
									else {
										if (empty($member['city'])) {
											$dispatch_price = $dispatch_price - $dispatch_merch[$merchid];
											$dispatch_merch[$merchid] = 0;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if ($saleset) {
			if (!empty($saleset['enoughfree'])) {
				$saleset_free = 0;

				if ($loop == 0) {
					if (floatval($saleset['enoughorder']) <= 0) {
						$saleset_free = 1;
					}
					else {
						if (floatval($saleset['enoughorder']) <= $realprice - $seckill_payprice) {
							if (empty($saleset['enoughareas'])) {
								$saleset_free = 1;
							}
							else {
								if (empty($new_area)) {
									$areas = explode(';', trim($saleset['enoughareas'], ';'));
								}
								else {
									$areas = explode(';', trim($saleset['enoughareas_code'], ';'));
								}

								if (!empty($user_city_code)) {
									if (!in_array($user_city_code, $areas)) {
										$saleset_free = 1;
									}
								}
							}
						}
					}
				}

				if ($saleset_free == 1) {
					$is_nofree = 0;
					$new_goods = array();

					if (!empty($saleset['goodsids'])) {
						foreach ($goods as $k => $v) {
							if (!in_array($v['goodsid'], $saleset['goodsids'])) {
								$new_goods[$k] = $goods[$k];
								unset($goods[$k]);
							}
							else {
								$is_nofree = 1;
							}
						}
					}

					if (($is_nofree == 1) && ($loop == 0)) {
						if ($goods_num == 1) {
							$new_data1 = self::getOrderDispatchPrice($goods, $member, $address, $saleset, $merch_array, $t, 1);
							$dispatch_price = $new_data1['dispatch_price'];
						}
						else {
							$new_data2 = self::getOrderDispatchPrice($new_goods, $member, $address, $saleset, $merch_array, $t, 1);
							$dispatch_price = $dispatch_price - $new_data2['dispatch_price'];
						}
					}
					else {
						if ($saleset_free == 1) {
							$dispatch_price = 0;
						}
					}
				}
			}
		}

		if ($dispatch_price == 0) {
			foreach ($dispatch_merch as &$dm) {
				$dm = 0;
			}
			unset($dm);
		}

		if (!empty($nodispatch_array) && !empty($address)) {
			$nodispatch = '商品';

			foreach ($nodispatch_array['title'] as $k => $v) {
				$nodispatch .= $v . ',';
			}

			$nodispatch = trim($nodispatch, ',');
			$nodispatch .= '不支持配送到' . $nodispatch_array['city'];
			$nodispatch_array['nodispatch'] = $nodispatch;
			$nodispatch_array['isnodispatch'] = 1;
		}

		$data = array();
		$data['dispatch_price'] = $dispatch_price + $seckill_dispatchprice;
		$data['dispatch_merch'] = $dispatch_merch;
		$data['nodispatch_array'] = $nodispatch_array;
		$data['seckill_dispatch_price'] = $seckill_dispatchprice;
		return $data;
	}

	public function getChildOrderPrice($order, $goods, $dispatch_array, $merch_array, $sale_plugin, $discountprice_array)
	{
		$totalprice = $order['price'];
		$goodsprice = $order['goodsprice'];
		$grprice = $order['grprice'];
		$deductprice = $order['deductprice'];
		$deductcredit = $order['deductcredit'];
		$deductcredit2 = $order['deductcredit2'];
		$deductenough = $order['deductenough'];
		$is_deduct = 0;
		$is_deduct2 = 0;
		$deduct_total = 0;
		$deduct2_total = 0;
		$ch_order = array();

		if ($sale_plugin) {
			if (!empty($deduct)) {
				$is_deduct = 1;
			}
			if (!empty($deduct2)) {
				$is_deduct2 = 1;
			}
		}

		foreach ($goods as &$g) {
			$merchid = $g['merchid'];
			$ch_order[$merchid]['goods'][] = $g['goodsid'];
			$ch_order[$merchid]['grprice'] += $g['ggprice'];
			$ch_order[$merchid]['goodsprice'] += $g['marketprice'] * $g['total'];
			$ch_order[$merchid]['couponprice'] = $discountprice_array[$merchid]['deduct'];

			if ($is_deduct == 1) {
				if ($g['manydeduct']) {
					$deduct = $g['deduct'] * $g['total'];
				}
				else {
					$deduct = $g['deduct'];
				}

				if ($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0)) {
				}
				else {
					$deduct_total += $deduct;
					$ch_order[$merchid]['deducttotal'] += $deduct;
				}
			}

			if ($is_deduct2 == 1) {
				if ($g['deduct2'] == 0) {
					$deduct2 = $g['ggprice'];
				}
				else {
					if (0 < $g['deduct2']) {
						if ($g['ggprice'] < $g['deduct2']) {
							$deduct2 = $g['ggprice'];
						}
						else {
							$deduct2 = $g['deduct2'];
						}
					}
				}

				if ($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0)) {
				}
				else {
					$ch_order[$merchid]['deduct2total'] += $deduct2;
					$deduct2_total += $deduct2;
				}
			}
		}

		unset($g);

		foreach ($ch_order as $k => $v) {
			if ($is_deduct == 1) {
				if (0 < $deduct_total) {
					$n = $v['deducttotal'] / $deduct_total;
					$deduct_credit = ceil(round($deductcredit * $n, 2));
					$deduct_money = round($deductprice * $n, 2);
					$ch_order[$k]['deductcredit'] = $deduct_credit;
					$ch_order[$k]['deductprice'] = $deduct_money;
				}
			}

			if ($is_deduct2 == 1) {
				if (0 < $deduct2_total) {
					$n = $v['deduct2total'] / $deduct2_total;
					$deduct_credit2 = round($deductcredit2 * $n, 2);
					$ch_order[$k]['deductcredit2'] = $deduct_credit2;
				}
			}

			$op = round($v['grprice'] / $grprice, 2);
			$ch_order[$k]['op'] = $op;

			if (0 < $deductenough) {
				$deduct_enough = round($deductenough * $op, 2);
				$ch_order[$k]['deductenough'] = $deduct_enough;
			}
		}

		foreach ($ch_order as $k => $v) {
			$merchid = $k;
			$price = ($v['grprice'] - $v['deductprice'] - $v['deductcredit2'] - $v['deductenough'] - $v['couponprice']) + $dispatch_array['dispatch_merch'][$merchid];

			if (0 < $merchid) {
				$merchdeductenough = $merch_array[$merchid]['enoughdeduct'];

				if (0 < $merchdeductenough) {
					$price -= $merchdeductenough;
					$ch_order[$merchid]['merchdeductenough'] = $merchdeductenough;
				}
			}

			$ch_order[$merchid]['price'] = $price;
		}

		return $ch_order;
	}

	public static function checkhaveverifygoods($orderid)
	{
		$num = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id')->where('og.orderid =' . $orderid . ' and g.type=5')->count();
		$num = intval($num);

		if (0 < $num) {
			return true;
		}

		return false;
	}

	/**
     * 支付成功
     * @global type $_W
     * @param type $params
     */
	public function payResult($params)
	{
		$fee = intval($params['fee']);
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$ordersn_tid = $params['tid'];
		$ordersn = rtrim($ordersn_tid, 'TR');
		$order = Db::name('shop_order')->where('ordersn',$ordersn)->field('id,ordersn,price,mid,dispatchtype,addressid,carrier,status,isverify,deductcredit2,`virtual`,isvirtual,couponid,isvirtualsend,isparent,paytype,merchid,createtime,buyagainprice,istrade,tradestatus')->find();

		if (1 <= $order['status']) {
			return true;
		}

		$orderid = $order['id'];

		if ($params['from'] == 'return') {
			$address = false;

			if (empty($order['dispatchtype'])) {
				$address = Db::name('shop_member_address')->where('id',$order['addressid'])->field('realname,mobile,address')->find();
			}

			$carrier = false;
			if (($order['dispatchtype'] == 1) || ($order['isvirtual'] == 1)) {
				$carrier = unserialize($order['carrier']);
			}

			model('verifygoods')->createverifygoods($order['id']);

			if ($params['type'] == 'cash') {
				if ($order['isparent'] == 1) {
					// $change_data = array();
					// $change_data['merchshow'] = 1;
					// Db::name('shop_order')->where('id',$orderid)->update($change_data);
					self::setChildOrderPayResult($order, 0, 0);
				}
				return true;
			}

			if ($order['istrade'] == 0) {
				if ($order['status'] == 0) {
					if (!empty($order['virtual'])) {
						return model('virtual')->pay($order);
					}

					if ($order['isvirtualsend']) {
						return self::payVirtualSend($order['id']);
					}

					$isonlyverifygoods = self::checkisonlyverifygoods($orderid);
					$time = time();
					$change_data = array();

					if ($isonlyverifygoods) {
						$change_data['status'] = 2;
					}
					else {
						$change_data['status'] = 1;
					}

					$change_data['paytime'] = $time;

					Db::name('shop_order')->where('id',$orderid)->update($change_data);

					if ($order['isparent'] == 1) {
						self::setChildOrderPayResult($order, $time, 1);
					}

					self::setStocksAndCredits($orderid, 1);

					model('coupon')->sendcouponsbytask($order['id']);

					if (!empty($order['couponid'])) {
						model('coupon')->backConsumeCoupon($order['id']);
					}

					if ($order['isparent'] == 1) {
						$child_list = self::getChildOrder($order['id']);

						foreach ($child_list as $k => $v) {
							model('notice')->sendOrderMessage($v['id']);
						}
					} else {
						model('notice')->sendOrderMessage($orderid);
					}

					if ($order['isparent'] == 1) {
						$merchData = Db::name('shop_order')->where('parentid',intval($order['id']))->field('id,merchid')->select();

						foreach ($merchData as $mk => $mv) {
							
						}
					}
					else {
						
					}
				}
			} else {
				$time = time();
				$change_data = array();
				$count_ordersn = self::countOrdersn($ordersn_tid);
				if (($order['status'] == 0) && ($count_ordersn == 1)) {
					$change_data['status'] = 1;
					$change_data['tradestatus'] = 1;
					$change_data['paytime'] = $time;
				} else {
					if (($order['status'] == 1) && ($order['tradestatus'] == 1) && ($count_ordersn == 2)) {
						$change_data['tradestatus'] = 2;
						$change_data['tradepaytime'] = $time;
					}
				}

				Db::name('shop_order')->where('id',$orderid)->update($change_data);
				if (($order['status'] == 0) && ($count_ordersn == 1)) {
					model('notice')->sendOrderMessage($orderid);
				}
			}

			Db::name('shop_order_goods')->where('orderid',$order['id'])->update(array('rstate' => 10, 'prohibitrefund' => 0));

			return true;
		}

		return false;
	}

	public static function countOrdersn($ordersn, $str = 'TR')
	{
		$count = intval(substr_count($ordersn, $str));
		return $count;
	}

	/**
     * 子订单支付成功
     * @global type $_W
     * @param type $order
     * @param type $time
     */
	public static function setChildOrderPayResult($order, $time, $type)
	{
		$orderid = $order['id'];
		$list = self::getChildOrder($orderid);

		if (!empty($list)) {
			$change_data = array();

			if ($type == 1) {
				$change_data['status'] = 1;
				$change_data['paytime'] = $time;
			}

			foreach ($list as $k => $v) {
				if ($v['status'] == 0) {
					Db::name('shop_order')->where('id',$v['id'])->update($change_data);
				}
			}
		}
	}

	/**
     * 虚拟商品自动发货
     * @param int $orderid
     * @return bool?
     */
	public function payVirtualSend($orderid = 0)
	{
		$order = Db::name('shop_order')->where('id',$orderid)->field('id,ordersn, price,mid,dispatchtype,addressid,carrier,status,isverify,deductcredit2,`virtual`,isvirtual,couponid')->find();
		$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$orderid)->field('g.virtualsend,g.virtualsendcontent')->find();
		$time = time();

		Db::name('shop_order')->where('id',$orderid)->update(array('virtualsend_info' => $order_goods['virtualsendcontent'], 'status' => '3', 'paytime' => $time, 'sendtime' => $time, 'finishtime' => $time));
		self::fullback($order['id']);
		self::setStocksAndCredits($orderid, 1);
		self::setStocksAndCredits($orderid, 3);
		model('member')->upgradeLevel($order['mid']);
		self::setGiveBalance($orderid, 1);

		model('coupon')->sendcouponsbytask($order['id']);

		if (!empty($order['couponid'])) {
			model('coupon')->backConsumeCoupon($order['id']);
		}

		model('notice')->sendOrderMessage($orderid);
		return true;
	}

	public static function checkisonlyverifygoods($orderid)
	{
		$num = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id')->where('og.orderid =' . $orderid . ' and g.type<>5')->count();
		$num = intval($num);

		if (0 < $num) {
			return false;
		}

		$num2 = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id')->where('og.orderid =' . $orderid . ' and g.type=5')->count();
		$num2 = intval($num2);

		if (0 < $num2) {
			return true;
		}

		return false;
	}

	public static function changeParentOrderPrice($parent_order)
	{
		$id = $parent_order['id'];
		$item = Db::name('shop_order')->where('id',$id)->field('price,ordersn2,dispatchprice,changedispatchprice')->find();

		if (!empty($item)) {
			$orderupdate = array();
			$orderupdate['price'] = $item['price'] + $parent_order['price_change'];
			$orderupdate['ordersn2'] = $item['ordersn2'] + 1;
			$orderupdate['dispatchprice'] = $item['dispatchprice'] + $parent_order['dispatch_change'];
			$orderupdate['changedispatchprice'] = $item['changedispatchprice'] + $parent_order['dispatch_change'];

			if (!empty($orderupdate)) {
				Db::name('shop_order')->where('id',$id)->update($orderupdate);
			}
		}
	}

	/**
     * 返还抵扣的余额
     * @param type $order
     */
	public static function setDeductCredit2($order)
	{
		$shopset = model('common')->getSysset();
		if (0 < $order['deductcredit2']) {
			model('member')->setCredit($order['mid'], 'credit2', $order['deductcredit2'], array('0', $shopset['shop']['name'] . '购物返还抵扣余额 余额: ' . $order['deductcredit2'] . ' 订单号: ' . $order['ordersn']));
		}
	}

	public static function fullbackstop($orderid)
	{
		$shopset = model('common')->getSysset();
		$fullback_log = Db::name('shop_fullback_log')->where('orderid',$orderid)->find();
		Db::name('shop_fullback_log')->where('id',$fullback_log['id'])->setField('isfullback',1);
	}

	public static function getTotals($merch = 0) 
	{
		$merch = intval($merch);
		$condition = ' 1 and isparent=0';
		if ($merch < 0) {
			$condition .= ' and merchid=0';
		} else {
			$condition .= ' and merchid= ' . $merch;
		}
		$totals['all'] = Db::name('shop_order')->where($condition)->where('ismr=0 and deleted=0')->count();
		$totals['status_1'] = Db::name('shop_order')->where($condition)->where('ismr=0 and status=-1 and refundtime=0 and deleted=0')->count();
		$totals['status0'] = Db::name('shop_order')->where($condition)->where('ismr=0  and status=0 and paytype<>3 and deleted=0')->count();
		$totals['status1'] = Db::name('shop_order')->where($condition)->where('ismr=0  and ( status=1 or ( status=0 and paytype=3) ) and deleted=0')->count();
		$totals['status2'] = Db::name('shop_order')->where($condition)->where('ismr=0  and ( status=2 or (status = 1 and sendtype > 0) ) and deleted=0')->count();
		$totals['status3'] = Db::name('shop_order')->where($condition)->where('ismr=0  and status=3 and deleted=0')->count();
		$totals['status4'] = Db::name('shop_order')->where($condition)->where('ismr=0  and refundstate>0 and refundid<>0 and deleted=0')->count();
		$totals['status5'] = Db::name('shop_order')->where($condition)->where('ismr=0 and refundtime<>0 and deleted=0')->count();
		return $totals;
	}

	/**
     * 计算订单中商品累计赠送的积分
     * @param type $order
     */
	public static function getGoodsCredit($goods)
	{
		$credits = 0;

		foreach ($goods as $g) {
			$gcredit = trim($g['credit']);

			if (!empty($gcredit)) {
				if (strexists($gcredit, '%')) {
					$credits += intval((floatval(str_replace('%', '', $gcredit)) / 100) * $g['realprice']);
				}
				else {
					$credits += intval($g['credit']) * $g['total'];
				}
			}
		}

		return $credits;
	}

}