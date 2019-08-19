<?php

namespace app\common\model;

use think\Model;

class Livemall extends Model
{
    public static function getSets()
	{
		return model('common')->getPluginset('livemall');
	}

	/**
     * 计算订单商品的佣金，及下单时候主播信息登记
     * @global type 
     * @param type $order_goods
     * @return type
     */
	public static function calculate($orderid = 0, $update = true, $order_liveid = NULL)
	{
		$set = self::getSets();
		$order = Db::name('shop_order')->where('id = ' . $orderid)->field('liveid,price,goodsprice,deductcredit2,discountprice,isdiscountprice,dispatchprice,changeprice,ispackage,packageid,couponprice,buyagainprice')->find();

		if (empty($set['commissiontype'])) {
			$rate = 1;
		} else {
			$numm = $order['goodsprice'] - $order['isdiscountprice'] - $order['discountprice'] - $order['couponprice'] - $order['buyagainprice'];
			if ($numm != 0) {
				$rate = ($order['price'] - $order['changeprice'] - $order['dispatchprice'] + $order['deductcredit2']) / $numm;
			} else {
				$rate = 1;
			}
		}

		$liveid = !is_null($order_liveid) ? $order_liveid : $order['liveid'];
		$hascommission = false;
		if ($order['isparent'] && $order['parentid'] == 0) {
			$parentsetql = 'select id from ' . tablename('shop_order') . 'where parentid=' . $orderid;
			$condition = ' WHERE 1 ';
			$condition .= ' AND og.orderid in(' . $parentsetql . ')';
			$goodssetql = 'select og.id,og.realprice,og.total,g.nolive,g.hasoption,og.goodsid,og.optionid,g.hascommission,g.nocommission, g.commission1_rate,g.commission1_pay,g.commission2_rate,g.commission2_pay,g.commission3_rate,g.commission3_pay,g.commission,og.commissions,og.seckill,og.seckill_taskid,og.seckill_timeid from ' . tablename('shop_order_goods') . '  og ' . ' left join ' . tablename('shop_goods') . ' g on g.id = og.goodsid ' . $condition;
			$goods = Db::query($goodssetql);
		} else {
			$goods = Db::query('select og.id,og.realprice,og.total,g.nolive,g.hasoption,og.goodsid,og.optionid,g.hascommission,g.nocommission,g.commission1_rate,g.commission1_pay,g.commission2_rate,g.commission2_pay,g.commission3_rate,g.commission3_pay,g.commission,og.commissions,og.seckill,og.seckill_taskid,og.seckill_timeid from ' . tablename('shop_order_goods') . '  og ' . ' left join ' . tablename('shop_goods') . ' g on g.id = og.goodsid' . ' where og.orderid=' . $orderid);
		}

		if ($set['openlivemall'] == 0) {
			foreach ($goods as &$cinfo) {
				$price = $cinfo['realprice'] * $rate;
				$seckill_goods = false;

				if ($cinfo['seckill']) {
					$seckill_goods = Db::name('shopseteckill_task_goods')->where('goodsid=' . $cinfo['goodsid'] . ' and optionid =' . $cinfo['optionid'] . ' and taskid=' . $cinfo['seckill_taskid'] . ' and timeid=' . $cinfo['seckill_timeid'])->field('commission1,commission2,commission3')->find();
				}

				if (!empty($seckill_goods)) {
					$hascommission = true;
					$cinfo['livecommission'] = array('default' => 1 <= $set['level'] ? $seckill_goods['commission1'] * $cinfo['total'] : 0);
					foreach ($levels as $level) {
						$cinfo['commission1']['level' . $level['id']] = $seckill_goods['commission1'] * $cinfo['total'];
						$cinfo['commission2']['level' . $level['id']] = $seckill_goods['commission2'] * $cinfo['total'];
						$cinfo['commission3']['level' . $level['id']] = $seckill_goods['commission3'] * $cinfo['total'];
					}
				} else {
					$goods_commission = !empty($cinfo['commission']) ? json_decode($cinfo['commission'], true) : '';

					if (empty($cinfo['nocommission'])) {
						$hascommission = true;

						if ($cinfo['hascommission'] == 1) {
							if (empty($goods_commission['type'])) {
								$cinfo['commission1'] = array('default' => 1 <= $set['level'] ? (0 < $cinfo['commission1_rate'] ? round($cinfo['commission1_rate'] * $price / 100, 2) . '' : round($cinfo['commission1_pay'] * $cinfo['total'], 2)) : 0);
								$cinfo['commission2'] = array('default' => 2 <= $set['level'] ? (0 < $cinfo['commission2_rate'] ? round($cinfo['commission2_rate'] * $price / 100, 2) . '' : round($cinfo['commission2_pay'] * $cinfo['total'], 2)) : 0);
								$cinfo['commission3'] = array('default' => 3 <= $set['level'] ? (0 < $cinfo['commission3_rate'] ? round($cinfo['commission3_rate'] * $price / 100, 2) . '' : round($cinfo['commission3_pay'] * $cinfo['total'], 2)) : 0);

								foreach ($levels as $level) {
									$cinfo['commission1']['level' . $level['id']] = 0 < $cinfo['commission1_rate'] ? round($cinfo['commission1_rate'] * $price / 100, 2) . '' : round($cinfo['commission1_pay'] * $cinfo['total'], 2);
									$cinfo['commission2']['level' . $level['id']] = 0 < $cinfo['commission2_rate'] ? round($cinfo['commission2_rate'] * $price / 100, 2) . '' : round($cinfo['commission2_pay'] * $cinfo['total'], 2);
									$cinfo['commission3']['level' . $level['id']] = 0 < $cinfo['commission3_rate'] ? round($cinfo['commission3_rate'] * $price / 100, 2) . '' : round($cinfo['commission3_pay'] * $cinfo['total'], 2);
								}
							} else if (empty($cinfo['hasoption'])) {
								$temp_price = array();
								$i = 0;

								while ($i < $set['level']) {
									if (!empty($goods_commission['default']['option0'][$i])) {
										if (strexists($goods_commission['default']['option0'][$i], '%')) {
											$dd = floatval(str_replace('%', '', $goods_commission['default']['option0'][$i]));
											if (0 < $dd && $dd < 100) {
												$temp_price[$i] = round($dd / 100 * $price, 2);
											} else {
												$temp_price[$i] = 0;
											}
										} else {
											$temp_price[$i] = round($goods_commission['default']['option0'][$i] * $cinfo['total'], 2);
										}
									}

									++$i;
								}

								$cinfo['commission1'] = array('default' => 1 <= $set['level'] ? $temp_price[0] : 0);
								$cinfo['commission2'] = array('default' => 2 <= $set['level'] ? $temp_price[1] : 0);
								$cinfo['commission3'] = array('default' => 3 <= $set['level'] ? $temp_price[2] : 0);

								foreach ($levels as $level) {
									$temp_price = array();
									$i = 0;

									while ($i < $set['level']) {
										if (!empty($goods_commission['level' . $level['id']]['option0'][$i])) {
											if (strexists($goods_commission['level' . $level['id']]['option0'][$i], '%')) {
												$dd = floatval(str_replace('%', '', $goods_commission['level' . $level['id']]['option0'][$i]));
												if (0 < $dd && $dd < 100) {
													$temp_price[$i] = round($dd / 100 * $price, 2);
												}
												else {
													$temp_price[$i] = 0;
												}
											} else {
												$temp_price[$i] = round($goods_commission['level' . $level['id']]['option0'][$i] * $cinfo['total'], 2);
											}
										}

										++$i;
									}

									$cinfo['commission1']['level' . $level['id']] = $temp_price[0];
									$cinfo['commission2']['level' . $level['id']] = $temp_price[1];
									$cinfo['commission3']['level' . $level['id']] = $temp_price[2];
								}
							} else {
								$temp_price = array();
								$i = 0;

								while ($i < $set['level']) {
									if (!empty($goods_commission['default']['option' . $cinfo['optionid']][$i])) {
										if (strexists($goods_commission['default']['option' . $cinfo['optionid']][$i], '%')) {
											$dd = floatval(str_replace('%', '', $goods_commission['default']['option' . $cinfo['optionid']][$i]));
											if (0 < $dd && $dd < 100) {
												$temp_price[$i] = round($dd / 100 * $price, 2);
											}
											else {
												$temp_price[$i] = 0;
											}
										}
										else {
											$temp_price[$i] = round($goods_commission['default']['option' . $cinfo['optionid']][$i] * $cinfo['total'], 2);
										}
									}

									++$i;
								}

								$cinfo['commission1'] = array('default' => 1 <= $set['level'] ? $temp_price[0] : 0);
								$cinfo['commission2'] = array('default' => 2 <= $set['level'] ? $temp_price[1] : 0);
								$cinfo['commission3'] = array('default' => 3 <= $set['level'] ? $temp_price[2] : 0);

								foreach ($levels as $level) {
									$temp_price = array();
									$i = 0;

									while ($i < $set['level']) {
										if (!empty($goods_commission['level' . $level['id']]['option' . $cinfo['optionid']][$i])) {
											if (strexists($goods_commission['level' . $level['id']]['option' . $cinfo['optionid']][$i], '%')) {
												$dd = floatval(str_replace('%', '', $goods_commission['level' . $level['id']]['option' . $cinfo['optionid']][$i]));
												if (0 < $dd && $dd < 100) {
													$temp_price[$i] = round($dd / 100 * $price, 2);
												}
												else {
													$temp_price[$i] = 0;
												}
											}
											else {
												$temp_price[$i] = round($goods_commission['level' . $level['id']]['option' . $cinfo['optionid']][$i] * $cinfo['total'], 2);
											}
										}

										++$i;
									}

									$cinfo['commission1']['level' . $level['id']] = $temp_price[0];
									$cinfo['commission2']['level' . $level['id']] = $temp_price[1];
									$cinfo['commission3']['level' . $level['id']] = $temp_price[2];
								}
							}
						} else {
							$cinfo['commission1'] = array('default' => 1 <= $set['level'] ? round($set['commission1'] * $price / 100, 2) . '' : 0);
							$cinfo['commission2'] = array('default' => 2 <= $set['level'] ? round($set['commission2'] * $price / 100, 2) . '' : 0);
							$cinfo['commission3'] = array('default' => 3 <= $set['level'] ? round($set['commission3'] * $price / 100, 2) . '' : 0);

							foreach ($levels as $level) {
								$cinfo['commission1']['level' . $level['id']] = 1 <= $set['level'] ? round($level['commission1'] * $price / 100, 2) . '' : 0;
								$cinfo['commission2']['level' . $level['id']] = 2 <= $set['level'] ? round($level['commission2'] * $price / 100, 2) . '' : 0;
								$cinfo['commission3']['level' . $level['id']] = 3 <= $set['level'] ? round($level['commission3'] * $price / 100, 2) . '' : 0;
							}
						}

						if (0 < $order['ispackage']) {
							$packoption = array();

							if (!empty($cinfo['optionid'])) {
								$packoption = Db::name('shop_package_goods_option')->where('pid = ' . $order['packageid'] . ' and optionid = ' . $cinfo['optionid'])->field('commission1,commission2,commission3')->find();
							} else {
								$packoption = Db::name('shop_package_goods_option')->where('pid = ' . $order['packageid'] . ' and goodsid = ' . $cinfo['goodsid'])->field('commission1,commission2,commission3')->find();
							}

							$cinfo['commission1'] = array('default' => 1 <= $set['level'] ? $packoption['commission1'] : 0);
							$cinfo['commission2'] = array('default' => 2 <= $set['level'] ? $packoption['commission2'] : 0);
							$cinfo['commission3'] = array('default' => 3 <= $set['level'] ? $packoption['commission3'] : 0);

							foreach ($levels as $level) {
								$cinfo['commission1']['level' . $level['id']] = $packoption['commission1'];
								$cinfo['commission2']['level' . $level['id']] = $packoption['commission2'];
								$cinfo['commission3']['level' . $level['id']] = $packoption['commission3'];
							}
						}
					} else {
						$cinfo['commission1'] = array('default' => 0);
						$cinfo['commission2'] = array('default' => 0);
						$cinfo['commission3'] = array('default' => 0);

						foreach ($levels as $level) {
							$cinfo['commission1']['level' . $level['id']] = 0;
							$cinfo['commission2']['level' . $level['id']] = 0;
							$cinfo['commission3']['level' . $level['id']] = 0;
						}
					}
				}

				if ($update) {
					Db::name('shop_order_goods')->where('id = ' . $cinfo['id'])->update(array('livecommission' => $commissions, 'nolivecommission' => $cinfo['nolivecommission']));
				}
			}
			unset($cinfo);
		}

		if (!$hascommission) {
			Db::name('shop_order')->where('id = ' . $orderid)->update(array('liveid' => 0));
		}

		return $goods;
	}

	public static function getOrderCommissions($orderid = 0, $ogid = 0)
	{
		$set = self::getSet();
		$liveid = Db::name('shop_order')->where('id = ' . $orderid)->value('liveid');
		$goods = Db::name('shop_order_goods')->where('id=' . $ogid . ' and orderid=' . $orderid . ' and nocommission=0')->field('commission1,commission2,commission3')->find();
		$commissions = array('level1' => 0, 'level2' => 0, 'level3' => 0);

		if (0 < $set['level']) {
			$commission1 = iunserializer($goods['commission1']);
			$commission2 = iunserializer($goods['commission2']);
			$commission3 = iunserializer($goods['commission3']);

			if (!empty($liveid)) {
				$m1 = model('member')->getMember($liveid);
				if ($m1['isagent'] == 1 && $m1['status'] == 1) {
					$l1 = self::getLevel($m1['id']);
					$commissions['level1'] = empty($l1) ? round($commission1['default'], 2) : round($commission1['level' . $l1['id']], 2);

					if (!empty($m1['liveid'])) {
						$m2 = model('member')->getMember($m1['liveid']);
						$l2 = self::getLevel($m2['id']);
						$commissions['level2'] = empty($l2) ? round($commission2['default'], 2) : round($commission2['level' . $l2['id']], 2);

						if (!empty($m2['liveid'])) {
							$m3 = model('member')->getMember($m2['liveid']);
							$l3 = self::getLevel($m3['id']);
							$commissions['level3'] = empty($l3) ? round($commission3['default'], 2) : round($commission3['level' . $l3['id']], 2);
						}
					}
				}
			}
		}

		return $commissions;
	}

	public static function getStatistics($options)
	{
		$array = array('total');
		$level1_commission_total = 0;
		$level2_commission_total = 0;
		$level3_commission_total = 0;

		if (!empty($options['level1_liveids'])) {
			foreach ($options['level1_liveids'] as $k => $v) {
				$info = self::getInfo($v['id'], $array);
				$level1_commission_total += $info['commission_total'];
			}
		}

		if (!empty($options['level2_liveids'])) {
			foreach ($options['level2_liveids'] as $k => $v) {
				$info = self::getInfo($v['id'], $array);
				$level2_commission_total += $info['commission_total'];
			}
		}

		if (!empty($options['level3_liveids'])) {
			foreach ($options['level3_liveids'] as $k => $v) {
				$info = self::getInfo($v['id'], $array);
				$level3_commission_total += $info['commission_total'];
			}
		}

		$level_commission_total = $level1_commission_total + $level2_commission_total + $level3_commission_total;
		$data = array();
		$data['level_commission_total'] = $level_commission_total;
		$data['level1_commission_total'] = $level1_commission_total;
		$data['level2_commission_total'] = $level2_commission_total;
		$data['level3_commission_total'] = $level3_commission_total;
		return $data;
	}

	/**
     * 是否是分销商
     * @param type $mid
     * @return type
     */
	public static function isAgent($mid)
	{
		if (empty($mid)) {
			return false;
		}

		if (is_array($mid)) {
			return $mid['isagent'] == 1 && $mid['status'] == 1;
		}

		$member = model('member')->getMember($mid);
		return $member['isagent'] == 1 && $member['status'] == 1;
	}

	/**
     * 计算出此商品的佣金
     * @param type $goodsid
     * @return type 
     */
	public static function getCommission($goods, $mid = '')
	{
		$set = self::getSet();
		$commission = 0;

		if ($goods['hascommission'] == 1) {
			$price = $goods['maxprice'];
			$level = self::getLevel($mid);
			$levelid = 'default';

			if ($level) {
				$levelid = 'level' . $level['id'];
			}

			$goods_commission = !empty($goods['commission']) ? json_decode($goods['commission'], true) : array();

			if ($goods_commission['type'] == 0) {
				$commission = 1 <= $set['level'] ? (0 < $goods['commission1_rate'] ? $goods['commission1_rate'] * $goods['marketprice'] / 100 : $goods['commission1_pay']) : 0;
			} else {
				$price_all = array();

				foreach ($goods_commission[$levelid] as $key => $value) {
					foreach ($value as $k => $v) {
						if (strexists($v, '%')) {
							array_push($price_all, floatval(str_replace('%', '', $v) / 100) * $price);
							continue;
						}

						array_push($price_all, $v);
					}
				}

				$commission = max($price_all);
			}
		} else {
			$level = self::getLevel($mid);
			if (!empty($level)) {
				$commission = 1 <= $set['level'] ? round($level['commission1'] * $goods['marketprice'] / 100, 2) : 0;
			} else {
				$commission = 1 <= $set['level'] ? round($set['commission1'] * $goods['marketprice'] / 100, 2) : 0;
			}
		}

		return $commission;
	}

	/**
     * 店中店二维码
     * @global type $_W
     * @param type $openid
     * @return string
     */
	public static function createMyShopQrcode($mid = 0, $posterid = 0)
	{
		$path = ROOT_PATH . '/public/data/qrcode/';

		if (!is_dir($path)) {
			mkdirs($path);
		}

		$url = url('commission/myshop', array(), FALSE, true);
		$url .= '&liveid=' . $mid;
		if (!empty($posterid)) {
			$url .= '&posterid=' . $posterid;
		}

		$file = 'shop_qrcode_' . $posterid . '_' . $mid . '.png';
		$qrcode_file = $path . $file;

		$errorCorrectionLevel = 'L';
	    if (isset($qrLevel) && in_array($qrLevel, ['L', 'M', 'Q', 'H'])) {
	        $errorCorrectionLevel = $qrLevel;
	    }
	    $matrixPointSize = 4;
	    if (isset($qrSize)) {
	        $matrixPointSize = min(max((int)$qrSize, 1), 10);
	    }
	    \PHPQRCode\QRcode::png($url, $qrcode_file, $errorCorrectionLevel, $matrixPointSize, 2);
	    if (file_exists($qrcode_file))
	        return tomedia('/public/data/qrcode/' . $file);
	    else
	        return FALSE;
	}

	private function createImage($url)
	{
		$resp = ihttp_request($url);
		return imagecreatefromstring($resp['content']);
	}

	/**
     * 创建商品海报
     * @param type $goodsid
     */
	public static function createGoodsImage($goods, $mid = 0)
	{
		$goods = set_medias($goods, 'thumb');
		$shopsetet = model('common')->getSysset('shop');
		$me = model('member')->getMember($mid);
		if ($me['isagent'] == 1 && $me['status'] == 1) {
			$userinfo = $me;
		} else {
			$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;

			if (!empty($mid)) {
				$userinfo = model('member')->getMember($mid);
			}
		}

		$path = ROOT_PATH . '/public/data/poster/';

		if (!is_dir($path)) {
			mkdirs($path);
		}

		$img = empty($goods['commission_thumb']) ? $goods['thumb'] : tomedia($goods['commission_thumb']);
		$md5 = md5(json_encode(array('id' => $goods['id'], 'marketprice' => $goods['marketprice'], 'productprice' => $goods['productprice'], 'img' => $img, 'shopset' => $shopsetet, 'openid' => $openid, 'version' => 4)));
		$file = $md5 . '.jpg';

		if (!is_file($path . $file)) {
			set_time_limit(0);
			$font = ROOT_PATH . '/public/static/fonts/msyh.ttf';
			$target = imagecreatetruecolor(640, 1225);
			$bg = imagecreatefromjpeg(ROOT_PATH . '/public/static/images/poster.jpg');
			imagecopy($target, $bg, 0, 0, 0, 0, 640, 1225);
			imagedestroy($bg);

			if (!empty($userinfo['avatar'])) {
				$avatar = preg_replace('/\\/0$/i', '/96', $userinfo['avatar']);
				$head = self::createImage($avatar);
				$w = imagesx($head);
				$h = imagesy($head);
				imagecopyresized($target, $head, 24, 32, 0, 0, 88, 88, $w, $h);
				imagedestroy($head);
			}

			if (!empty($img)) {
				$thumb = self::createImage($img);
				$w = imagesx($thumb);
				$h = imagesy($thumb);
				imagecopyresized($target, $thumb, 0, 160, 0, 0, 640, 640, $w, $h);
				imagedestroy($thumb);
			}

			$black = imagecreatetruecolor(640, 127);
			imagealphablending($black, false);
			imagesavealpha($black, true);
			$blackcolor = imagecolorallocatealpha($black, 0, 0, 0, 25);
			imagefill($black, 0, 0, $blackcolor);
			imagecopy($target, $black, 0, 678, 0, 0, 640, 127);
			imagedestroy($black);
			$goods_qrcode_file = tomedia(m('qrcode')->createGoodsQrcode($userinfo['id'], $goods['id'])) . '?' . time();
			$qrcode = self::createImage($goods_qrcode_file);
			$w = imagesx($qrcode);
			$h = imagesy($qrcode);
			imagecopyresized($target, $qrcode, 50, 835, 0, 0, 250, 250, $w, $h);
			imagedestroy($qrcode);
			$bc = imagecolorallocate($target, 0, 3, 51);
			$cc = imagecolorallocate($target, 240, 102, 0);
			$wc = imagecolorallocate($target, 255, 255, 255);
			$yc = imagecolorallocate($target, 255, 255, 0);
			$str1 = '我是';
			imagettftext($target, 20, 0, 150, 70, $bc, $font, $str1);
			imagettftext($target, 20, 0, 210, 70, $cc, $font, $userinfo['nickname']);
			$str2 = '我要为';
			imagettftext($target, 20, 0, 150, 105, $bc, $font, $str2);
			$str3 = $shopsetet['name'];
			imagettftext($target, 20, 0, 240, 105, $cc, $font, $str3);
			$box = imagettfbbox(20, 0, $font, $str3);
			$width = $box[4] - $box[6];
			$str4 = '代言';
			imagettftext($target, 20, 0, 240 + $width + 10, 105, $bc, $font, $str4);
			$str5 = mbsetubstr($goods['title'], 0, 50, 'utf-8');
			imagettftext($target, 20, 0, 30, 730, $wc, $font, $str5);
			$str6 = '￥' . number_format($goods['marketprice'], 2);
			imagettftext($target, 25, 0, 25, 780, $yc, $font, $str6);
			$box = imagettfbbox(26, 0, $font, $str6);
			$width = $box[4] - $box[6];

			if (0 < $goods['productprice']) {
				$str7 = '￥' . number_format($goods['productprice'], 2);
				imagettftext($target, 22, 0, 25 + $width + 10, 780, $wc, $font, $str7);
				$end = 25 + $width + 10;
				$box = imagettfbbox(22, 0, $font, $str7);
				$width = $box[4] - $box[6];
				imageline($target, $end, 770, $end + $width + 20, 770, $wc);
				imageline($target, $end, 771.5, $end + $width + 20, 771, $wc);
			}

			imagejpeg($target, $path . $file);
			imagedestroy($target);
		}

		return tomedia('public/data/poster/' . $file);
	}

	/**
     * 常见店铺海报
     * @return string
     */
	public static function createShopImage($mid = '')
	{
		$shop = model('common')->getSysset('shop');
		$shopsetet = set_medias($shop, 'signimg');
		$path = ROOT_PATH . '/public/data/poster/';

		if (!is_dir($path)) {
			mkdirs($path);
		}

		$me = model('member')->getMember($mid);
		if ($me['isagent'] == 1 && $me['status'] == 1) {
			$userinfo = $me;
		} else {
			$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;
			if (!empty($mid)) {
				$userinfo = model('member')->getMember($mid);
			}
		}

		$md5 = md5(json_encode(array('mid' => $mid, 'signimg' => $shopsetet['signimg'], 'shopset' => $shopsetet, 'version' => 4)));
		$file = $md5 . '.jpg';

		if (!is_file($path . $file)) {
			set_time_limit(0);
			@ini_set('memory_limit', '256M');
			$font = ROOT_PATH . '/public/static/fonts/msyh.ttf';
			$target = imagecreatetruecolor(640, 1225);
			$bc = imagecolorallocate($target, 0, 3, 51);
			$cc = imagecolorallocate($target, 240, 102, 0);
			$wc = imagecolorallocate($target, 255, 255, 255);
			$yc = imagecolorallocate($target, 255, 255, 0);
			$bg = imagecreatefromjpeg(ROOT_PATH . '/public/static/images/poster.jpg');
			imagecopy($target, $bg, 0, 0, 0, 0, 640, 1225);
			imagedestroy($bg);

			if (!empty($userinfo['avatar'])) {
				$avatar = preg_replace('/\\/0$/i', '/96', $userinfo['avatar']);
				$head = self::createImage($avatar);
				$w = imagesx($head);
				$h = imagesy($head);
				imagecopyresized($target, $head, 24, 32, 0, 0, 88, 88, $w, $h);
				imagedestroy($head);
			}

			if (!empty($shopsetet['signimg'])) {
				$thumb = self::createImage($shopsetet['signimg']);
				$w = imagesx($thumb);
				$h = imagesy($thumb);
				imagecopyresized($target, $thumb, 0, 160, 0, 0, 640, 640, $w, $h);
				imagedestroy($thumb);
			}

			$qrcode_file = tomedia(self::createMyShopQrcode($userinfo['id']));
			$qrcode = self::createImage($qrcode_file);
			$w = imagesx($qrcode);
			$h = imagesy($qrcode);
			imagecopyresized($target, $qrcode, 50, 835, 0, 0, 250, 250, $w, $h);
			imagedestroy($qrcode);
			$str1 = '我是';
			imagettftext($target, 20, 0, 150, 70, $bc, $font, $str1);
			imagettftext($target, 20, 0, 210, 70, $cc, $font, $userinfo['nickname']);
			$str2 = '我要为';
			imagettftext($target, 20, 0, 150, 105, $bc, $font, $str2);
			$str3 = $shopsetet['name'];
			imagettftext($target, 20, 0, 240, 105, $cc, $font, $str3);
			$box = imagettfbbox(20, 0, $font, $str3);
			$width = $box[4] - $box[6];
			$str4 = '代言';
			imagettftext($target, 20, 0, 240 + $width + 10, 105, $bc, $font, $str4);
			imagejpeg($target, $path . $file);
			imagedestroy($target);
		}

		return tomedia('/public/data/poster/' . $file);
	}

	/**
     * 常见店铺海报
     * @return string
     */
	public static function createWxShopImage($mid = '')
	{
		$shop = model('common')->getSysset('shop');
		$shopsetet = set_medias($shop, 'signimg');
		$path = ROOT_PATH . '/public/data/poster/';

		if (!is_dir($path)) {
			mkdirs($path);
		}

		$me = model('member')->getMember($mid);
		if ($me['isagent'] == 1 && $me['status'] == 1) {
			$userinfo = $me;
		} else {
			$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;
			if (!empty($mid)) {
				$userinfo = model('member')->getMember($mid);
			}
		}

		$md5 = md5(json_encode(array('mid' => $mid, 'signimg' => $shopsetet['signimg'], 'shopset' => $shopsetet, 'version' => 4)));
		$file = $md5 . '13.jpg';

		if (!is_file($path . $file)) {
			set_time_limit(0);
			@ini_set('memory_limit', '256M');
			$font = ROOT_PATH . '/public/static/fonts/msyh.ttf';
			$target = imagecreatetruecolor(640, 1225);
			$bc = imagecolorallocate($target, 0, 3, 51);
			$cc = imagecolorallocate($target, 240, 102, 0);
			$wc = imagecolorallocate($target, 255, 255, 255);
			$yc = imagecolorallocate($target, 255, 255, 0);
			$bg = imagecreatefromjpeg(ROOT_PATH . '/public/static/images/poster.jpg');
			imagecopy($target, $bg, 0, 0, 0, 0, 640, 1225);
			imagedestroy($bg);

			if (!empty($userinfo['avatar'])) {
				$avatar = preg_replace('/\\/0$/i', '/96', $userinfo['avatar']);
				$head = self::createImage($avatar);
				$w = imagesx($head);
				$h = imagesy($head);
				imagecopyresized($target, $head, 24, 32, 0, 0, 88, 88, $w, $h);
				imagedestroy($head);
			}

			if (!empty($shopsetet['signimg'])) {
				$thumb = self::createImage($shopsetet['signimg']);
				$w = imagesx($thumb);
				$h = imagesy($thumb);
				imagecopyresized($target, $thumb, 0, 160, 0, 0, 640, 640, $w, $h);
				imagedestroy($thumb);
			}
			$qrcode = model('wxapp')->getCodeUnlimit(array('scene' => 'mid=' . $userinfo['id'], 'page' => 'pages/index/index'));
			return $qrcode;
			if (!(is_error($qrcode))) 
			{
				$qrcode = imagecreatefromstring($qrcode);
				imagecopyresized($target, $qrcode, 50, 835, 0, 0, 250, 250, imagesx($qrcode), imagesy($qrcode));
			}
			// $qrcode = self::createImage($qrcode_file);
			// $w = imagesx($qrcode);
			// $h = imagesy($qrcode);
			// imagecopyresized($target, $qrcode, 50, 835, 0, 0, 250, 250, $w, $h);
			imagedestroy($qrcode);
			$str1 = '我是';
			imagettftext($target, 20, 0, 150, 70, $bc, $font, $str1);
			imagettftext($target, 20, 0, 210, 70, $cc, $font, $userinfo['nickname']);
			$str2 = '我要为';
			imagettftext($target, 20, 0, 150, 105, $bc, $font, $str2);
			$str3 = $shopsetet['name'];
			imagettftext($target, 20, 0, 240, 105, $cc, $font, $str3);
			$box = imagettfbbox(20, 0, $font, $str3);
			$width = $box[4] - $box[6];
			$str4 = '代言';
			imagettftext($target, 20, 0, 240 + $width + 10, 105, $bc, $font, $str4);
			imagejpeg($target, $path . $file);
			imagedestroy($target);
		}

		return tomedia('/public/data/poster/' . $file);
	}

	public static function checkOrderConfirm($orderid = '0')
	{
		if (empty($orderid)) {
			return NULL;
		}

		$order = Db::name('shop_order')->where('id = ' . $orderid . ' and status>=0')->field('id,mid,ordersn,goodsprice,liveid,paytime,officcode')->find();

		if (empty($order)) {
			return NULL;
		}

		$mid = $order['mid'];
		$member = model('member')->getMember($mid);

		if (empty($member)) {
			return NULL;
		}

		$become_child = intval($set['become_child']);
		$parent = false;

		if (empty($become_child)) {
			$parent = model('member')->getMember($member['liveid']);
		} else {
			if (!empty($order['officcode']) && m('offic')) {
				$parent = Db::name('member')->where('mobile = ' . trim($order['officcode']))->find();
			} else {
				$parent = model('member')->getMember($member['inviter']);
			}
		}

		$parent_is_agent = !empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1;
		$time = time();
		$become_child = intval($set['become_child']);

		if ($parent_is_agent) {
			if ($become_child == 1) {
				if (empty($member['liveid']) && $member['id'] != $parent['id']) {
					if (empty($member['fixliveid'])) {
						$member['liveid'] = $parent['id'];
						Db::name('member')->where('id = ' . $member['id'])->update(array('liveid' => $parent['id'], 'childtime' => $time));

						if (m('dividend')) {
							self::saveRelation($member['id'], $parent['id'], 1);
							model('dividend')->update_headsid($member['id'], $parent['id']);
						}

						self::sendMessage($parent['openid'], array('nickname' => $member['nickname'], 'openid' => $member['openid'], 'childtime' => $time), 'TM_COMMISSION_AGENT_NEW');
						self::upgradeLevelByAgent($parent['id']);

						if (m('globonus')) {
							model('globonus')->upgradeLevelByAgent($parent['id']);
						}

						if (m('abonus')) {
							model('abonus')->upgradeLevelByAgent($parent['id']);
						}
					}
				}
			}
		}

		$liveid = $member['liveid'];
		if ($member['isagent'] == 1 && $member['status'] == 1) {
			if (!empty($set['selfbuy'])) {
				$liveid = $member['id'];
			}
		}

		if (!empty($liveid)) {
			$res = Db::name('shop_order')->where('id = ' . $orderid)->update(array('liveid' => $liveid));
			self::calculate($orderid, true, $res ? $liveid : NULL);
			return NULL;
		}

		self::calculate($orderid);
	}

	public static function checkOrderPay($orderid = '0')
	{
		if (empty($orderid)) {
			return NULL;
		}

		$set = self::getSet();

		if (empty($set['level'])) {
			return NULL;
		}

		$order = Db::name('shop_order')->where('id = ' . $orderid . ' and status>=1 ')->field('id,isparent,parentid,mid,ordersn,goodsprice,liveid,paytime')->find();

		if (empty($order)) {
			return NULL;
		}

		$mid = $order['mid'];
		$member = model('member')->getMember($mid);

		if (empty($member)) {
			return NULL;
		}

		$become_check = intval($set['become_check']);
		$become_child = intval($set['become_child']);
		$parent = false;

		if (empty($become_child)) {
			$parent = model('member')->getMember($member['liveid']);
		} else {
			$parent = model('member')->getMember($member['inviter']);
		}

		$parent_is_agent = !empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1;
		$time = time();
		$become_child = intval($set['become_child']);

		if ($parent_is_agent) {
			if ($become_child == 2) {
				if (empty($member['liveid']) && $member['id'] != $parent['id']) {
					if (empty($member['fixliveid'])) {
						$member['liveid'] = $parent['id'];
						Db::name('member')->where('id = ' . $member['id'])->update(array('liveid' => $parent['id'], 'childtime' => $time));

						if (m('dividend')) {
							self::saveRelation($member['id'], $parent['id'], 1);
							model('dividend')->update_headsid($member['id'], $parent['id']);
						}						

						self::sendMessage($parent['id'], array('nickname' => $member['nickname'], 'mid' => $member['id'], 'childtime' => $time), 'TM_COMMISSION_AGENT_NEW');
						self::upgradeLevelByAgent($parent['id']);

						if (m('globonus')) {
							model('globonus')->upgradeLevelByAgent($parent['id']);
						}

						if (m('abonus')) {
							model('abonus')->upgradeLevelByAgent($parent['id']);
						}

						if (empty($order['liveid'])) {
							$order['liveid'] = $parent['id'];
							if ($order['isparent'] && $order['parentid'] == 0) {
								$merchSql = 'SELECT id,merchid FROM ' . tablename('shop_order') . ' WHERE parentid = ' . intval($orderid);
								$merchData = Db::query($merchSql);

								foreach ($merchData as $mk => $mv) {
									Db::name('shop_order')->where('id = ' . $mv['id'])->update(array('liveid' => $parent['id']));
								}
							}
							Db::name('shop_order')->where('id = ' . $orderid)->update(array('liveid' => $parent['id']));
							$order_agent_id = !empty($parent['id']) ? $parent['id'] : NULL;
							self::calculate($orderid, true, $order_agent_id);
						}
					}
				}
			}
		}

		$isagent = $member['isagent'] == 1 && $member['status'] == 1;

		if (!$isagent) {
			if (intval($set['become']) == 4 && !empty($set['become_goodsid'])) {
				if (empty($set['become_order'])) {
					$order_goods = fetchall('select goodsid from ' . tablename('shop_order_goods') . ' where orderid= ' . $order['id'],'goodsid');

					if (in_array($set['become_goodsid'], array_keys($order_goods))) {
						if (empty($member['agentblack'])) {
							Db::name('member')->where('id = ' . $member['id'])->update(array('status' => $become_check, 'isagent' => 1, 'agenttime' => $become_check == 1 ? $time : 0, 'applyagenttime' => 0));

							if ($become_check == 1) {
								self::sendMessage($openid, array('nickname' => $member['nickname'], 'agenttime' => $time), 'TM_COMMISSION_BECOME');

								if (!empty($parent)) {
									self::upgradeLevelByAgent($parent['id']);

									if (m('globonus')) {
										model('globonus')->upgradeLevelByAgent($parent['id']);
									}

									if (m('abonus')) {
										model('abonus')->upgradeLevelByAgent($parent['id']);
									}
								}
							}
						}
					}
				}
			} else {
				if ($set['become'] == 2 || $set['become'] == 3) {
					if (empty($set['become_order'])) {
						$time = time();
						$parentisagent = true;

						if (!empty($member['liveid'])) {
							$parent = model('member')->getMember($member['liveid']);
							if (empty($parent) || $parent['isagent'] != 1 || $parent['status'] != 1) {
								$parentisagent = false;
							}
						}

						$can = false;

						if ($set['become'] == '2') {
							$ordercount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=1')->count();
							$can = intval($set['become_ordercount']) <= $ordercount;
						} else {
							if ($set['become'] == '3') {
								$moneycount = Db::name('shop_order_goods')->alias('og')->join('shop_order o','og.orderid=o.id','left')->where('o.mid=' . $mid . ' and o.status>=1')->sum('og.realprice');
								$can = floatval($set['become_moneycount']) <= $moneycount;
							}
						}

						if ($can) {
							if (empty($member['agentblack'])) {
								Db::name('member')->where('id = ' . $member['id'])->update(array('status' => $become_check, 'isagent' => 1, 'agenttime' => $time));

								if ($become_check == 1) {
									self::sendMessage($openid, array('nickname' => $member['nickname'], 'agenttime' => $time), 'TM_COMMISSION_BECOME');

									if ($parentisagent) {
										self::upgradeLevelByAgent($parent['id']);

										if (m('globonus')) {
											model('globonus')->upgradeLevelByAgent($parent['id']);
										}

										if (m('abonus')) {
											model('abonus')->upgradeLevelByAgent($parent['id']);
										}
									}
								}
							}
						}
					}
				}
			}
		}

		if (!empty($member['liveid'])) {
			$parent = model('member')->getMember($member['liveid']);
			if (!empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1) {
				$order_goods = Db::query('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1,og.commissions  from ' . tablename('shop_order_goods') . ' og ' . ' left join ' . tablename('shop_goods') . ' g on g.id=og.goodsid ' . ' where og.orderid=' . $order['id']);
				$goods = '';
				$commission_total1 = 0;
				$commission_total2 = 0;
				$commission_total3 = 0;
				$pricetotal = 0;

				foreach ($order_goods as $og) {
					$goods .= '' . $og['title'] . '( ';

					if (!empty($og['optiontitle'])) {
						$goods .= ' 规格: ' . $og['optiontitle'];
					}

					$goods .= ' 单价: ' . $og['realprice'] / $og['total'] . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . '); ';
					$commissions = iunserializer($og['commissions']);
					$commission_total1 += isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
					$commission_total2 += isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
					$commission_total3 += isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
					$pricetotal += $og['realprice'];
				}

				if ($order['liveid'] == $member['id']) {
					self::sendMessage($member['id'], array('nickname' => $member['nickname'], 'ordersn' => $order['ordersn'], 'orderopenid' => $order['mid'], 'price' => $pricetotal, 'goods' => $goods, 'commission1' => $commission_total1, 'commission2' => $commission_total2, 'commission3' => $commission_total3, 'paytime' => $order['paytime']), 'TM_COMMISSION_ORDER_PAY');
				} else {
					if ($order['liveid'] == $parent['id']) {
						self::sendMessage($parent['id'], array('nickname' => $member['nickname'], 'ordersn' => $order['ordersn'], 'orderopenid' => $order['mid'], 'price' => $pricetotal, 'goods' => $goods, 'commission1' => $commission_total1, 'commission2' => $commission_total2, 'commission3' => $commission_total3, 'paytime' => $order['paytime']), 'TM_COMMISSION_ORDER_PAY');
					}
				}
			}
		}

		if ($isagent) {
			$plugin_globonus = m('globonus');

			if (!$plugin_globonus) {
				return NULL;
			}

			$set = model('globonus')->getSet();

			if (empty($set['open'])) {
				return NULL;
			}

			if ($member['ispartner']) {
				return NULL;
			}

			if (strpos($member['openid'], 'sns_wa_') === true) {
				return NULL;
			}

			$become_check = intval($set['become_check']);
			if (intval($set['become']) == 4 && !empty($set['become_goodsid'])) {
				if (empty($set['become_order'])) {
					$order_goods = fetchall('select goodsid from ' . tablename('shop_order_goods') . ' where orderid=' . $order['id'],'goodsid');
					if (in_array($set['become_goodsid'], array_keys($order_goods))) {
						if (empty($member['partnerblack'])) {
							Db::name('member')->where('id = ' . $member['id'])->update(array('partnerstatus' => $become_check, 'ispartner' => 1, 'partnertime' => $become_check == 1 ? $time : 0));
							if ($become_check == 1) {
								model('globonus')->sendMessage($openid, array('nickname' => $member['nickname'], 'partnertime' => $time), 'TM_GLOBONUS_BECOME');
							}
						}
					}
				}
			} else {
				if ($set['become'] == 2 || $set['become'] == 3) {
					if (empty($set['become_order'])) {
						$time = time();
						$can = false;

						if ($set['become'] == '2') {
							$ordercount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=1')->count();
							$can = intval($set['become_ordercount']) <= $ordercount;
						} else {
							if ($set['become'] == '3') {
								$moneycount = Db::name('shop_order_goods')->alias('og')->join('shop_order o','og.orderid=o.id','left')->where('o.mid=' . $mid . ' and o.status>=1')->sum('og.realprice');
								$can = floatval($set['become_moneycount']) <= $moneycount;
							}
						}

						if ($can) {
							if (empty($member['partnerblack'])) {
								Db::name('member')->where('id = ' . $member['id'])->update(array('partnerstatus' => $become_check, 'ispartner' => 1, 'partnertime' => $time));

								if ($become_check == 1) {
									model('globonus')->sendMessage($openid, array('nickname' => $member['nickname'], 'partnertime' => $time), 'TM_GLOBONUS_BECOME');
								}
							}
						}
					}
				}
			}
		}
	}

	public static function checkOrderFinish($orderid = '')
	{
		if (empty($orderid)) {
			return NULL;
		}

		$order = Db::name('shop_order')->where('id = ' . $orderid . ' and status>=3')->field('id,mid,ordersn,goodsprice,liveid,finishtime')->find();

		if (empty($order)) {
			return NULL;
		}

		$set = self::getSet();

		if (empty($set['level'])) {
			return NULL;
		}

		$mid = $order['mid'];
		$member = model('member')->getMember($mid);

		if (empty($member)) {
			return NULL;
		}

		self::orderFinishTask($order, $set['selfbuy'] ? true : false, $member);
		$time = time();
		$become_check = intval($set['become_check']);
		$isagent = $member['isagent'] == 1 && $member['status'] == 1;
		$parentisagent = true;

		if (!empty($member['liveid'])) {
			$parent = model('member')->getMember($member['liveid']);
			if (empty($parent) || $parent['isagent'] != 1 || $parent['status'] != 1) {
				$parentisagent = false;
			}
		}

		if (!$isagent && $set['become_order'] == '1') {
			if ($set['become'] == '4' && !empty($set['become_goodsid'])) {
				$order_goods = fetchall('select goodsid from ' . tablename('shop_order_goods') . ' where orderid=' . $order['id'],'goodsid');

				if (in_array($set['become_goodsid'], array_keys($order_goods))) {
					if (empty($member['agentblack'])) {
						Db::name('member')->where('id = ' . $member['id'])->update(array('status' => $become_check, 'isagent' => 1, 'agenttime' => $become_check == 1 ? $time : 0));
						if ($become_check == 1) {
							self::sendMessage($openid, array('nickname' => $member['nickname'], 'agenttime' => $time), 'TM_COMMISSION_BECOME');

							if ($parentisagent) {
								self::upgradeLevelByAgent($parent['id']);

								if (m('globonus')) {
									model('globonus')->upgradeLevelByAgent($parent['id']);
								}

								if (m('abonus')) {
									model('abonus')->upgradeLevelByAgent($parent['id']);
								}
							}
						}
					}
				}
			} else {
				if ($set['become'] == 2 || $set['become'] == 3) {
					$can = false;

					if ($set['become'] == '2') {
						$ordercount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=3')->count();
						$can = intval($set['become_ordercount']) <= $ordercount;
					} else {
						if ($set['become'] == '3') {
							$moneycount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=3')->sum('goodsprice');
							$can = floatval($set['become_moneycount']) <= $moneycount;
						}
					}

					if ($can) {
						if (empty($member['agentblack'])) {
							Db::name('member')->where('id = ' . $member['id'])->update(array('status' => $become_check, 'isagent' => 1, 'agenttime' => $time));
							if ($become_check == 1) {
								self::sendMessage($member['openid'], array('nickname' => $member['nickname'], 'agenttime' => $time), 'TM_COMMISSION_BECOME');
							}
						}
					}
				}
			}
		}

		if (!empty($member['liveid'])) {
			$parent = model('member')->getMember($member['liveid']);
			if (!empty($parent) && $parent['isagent'] == 1 && $parent['status'] == 1) {
				$order_goods = Db::query('select g.id,g.title,og.total,og.realprice,og.price,og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1,og.commissions from ' . tablename('shop_order_goods') . ' og ' . ' left join ' . tablename('shop_goods') . ' g on g.id=og.goodsid ' . ' where og.orderid=' . $order['id']);
				$goods = '';
				$commission_total1 = 0;
				$commission_total2 = 0;
				$commission_total3 = 0;
				$pricetotal = 0;

				foreach ($order_goods as $og) {
					$goods .= '' . $og['title'] . '( ';

					if (!empty($og['optiontitle'])) {
						$goods .= ' 规格: ' . $og['optiontitle'];
					}

					$goods .= ' 单价: ' . $og['realprice'] / $og['total'] . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . '); ';
					$commissions = iunserializer($og['commissions']);
					$commission_total1 += isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
					$commission_total2 += isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
					$commission_total3 += isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
					$pricetotal += $og['realprice'];
				}

				if ($order['liveid'] == $member['id']) {
					self::sendMessage($member['openid'], array('nickname' => $member['nickname'], 'ordersn' => $order['ordersn'], 'orderopenid' => $order['openid'], 'price' => $pricetotal, 'goods' => $goods, 'commission1' => $commission_total1, 'commission2' => $commission_total2, 'commission3' => $commission_total3, 'finishtime' => $order['finishtime']), 'TM_COMMISSION_ORDER_FINISH');
				} else {
					if ($order['liveid'] == $parent['id']) {
						self::sendMessage($parent['id'], array('nickname' => $member['nickname'], 'ordersn' => $order['ordersn'], 'orderopenid' => $order['mid'], 'price' => $pricetotal, 'goods' => $goods, 'commission1' => $commission_total1, 'commission2' => $commission_total2, 'commission3' => $commission_total3, 'finishtime' => $order['finishtime']), 'TM_COMMISSION_ORDER_FINISH');
					}
				}

				$abonus_plugin = m('abonus');

				if ($abonus_plugin) {
					model('abonus')->upgradeLevelByOrder($member['id']);
				}
			}
		}

		self::upgradeLevelByOrder($mid);
		self::upgradeLevelByGoods($mid, $orderid);

		if ($isagent) {
			$plugin_globonus = m('globonus');

			if (!$plugin_globonus) {
				return NULL;
			}

			$set = model('globonus')->getSet();

			if (empty($set['open'])) {
				return NULL;
			}

			$ispartner = $member['ispartner'] && $member['partnerstatus'];

			if ($ispartner) {
				 model('globonus')->upgradeLevelByOrder($openid);
				return NULL;
			}

			$become_check = intval($set['become_check']);

			if ($set['become_order'] == '1') {
				if ($set['become'] == '4' && !empty($set['become_goodsid'])) {
					$order_goods = fetchall('select goodsid from ' . tablename('shop_order_goods') . ' where orderid=' . $order['id'],'goodsid');

					if (in_array($set['become_goodsid'], array_keys($order_goods))) {
						if (empty($member['partnerblack'])) {
							Db::name('member')->where('id = ' . $member['id'])->update(array('partnerstatus' => $become_check, 'ispartner' => 1, 'partnertime' => $become_check == 1 ? $time : 0));
							if ($become_check == 1) {
								 model('globonus')->sendMessage($openid, array('nickname' => $member['nickname'], 'partnertime' => $time), 'TM_GLOBONUS_BECOME');
							}
						}
					}
				}
				else {
					if ($set['become'] == 2 || $set['become'] == 3) {
						$can = false;

						if ($set['become'] == '2') {
							$ordercount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=3')->count();
							$can = intval($set['become_ordercount']) <= $ordercount;
						} else {
							if ($set['become'] == '3') {
								$moneycount = Db::name('shop_order')->where('mid = ' . $mid . ' and status>=3')->sum('goodsprice');
								$can = floatval($set['become_moneycount']) <= $moneycount;
							}
						}

						if ($can) {
							if (empty($member['partnerblack'])) {
								Db::name('member')->where('id = ' . $member['id'])->update(array('partnerstatus' => $become_check, 'ispartner' => 1, 'partnertime' => $time));
								if ($become_check == 1) {
									 model('globonus')->sendMessage($member['openid'], array('nickname' => $member['nickname'], 'partnertime' => $time), 'TM_GLOBONUS_BECOME');
								}
							}
						}
					}
				}
			}
		}
	}

	// stop
	public static function orderFinishTask($order, $self_buy = false, $member)
	{
		if (!m('task')) {
			return NULL;
		}

		if (empty($order['liveid'])) {
			return NULL;
		}

		$order_id = $order['id'];
		$level_price_1 = $level_price_2 = $level_price_3 = 0;
		$order_goods_list = Db::query('SELECT commissions FROM ' . tablename('shop_order_goods') . ' WHERE orderid = ' . $order_id . ' AND nocommission = 0');

		if (empty($order_goods_list)) {
			return NULL;
		}

		foreach ((array) $order_goods_list as $one_order_goods) {
			$commissions = unserialize((string) $one_order_goods['commissions']);

			if (!empty($commissions)) {
				$level_price_1 += round((double) $commissions['level1'], 2);
				$level_price_2 += round((double) $commissions['level2'], 2);
				$level_price_3 += round((double) $commissions['level3'], 2);
			}
		}

		$openid1 = $openid2 = $openid3 = '';

		if (0 < $level_price_1) {
			if ($self_buy && $member['status'] == 1) {
				$openid1 = $member['openid'];
			} else {
				$member = model('member')->getMember($member['liveid']);
				$openid1 = $member['openid'];
			}

			model('task')->checkTaskReward('commission_money', $level_price_1, $openid1);
			model('task')->checkTaskProgress((int) $level_price_1, 'pyramid_money', 0, $openid1);

			if (0 < $level_price_2) {
				$member = model('member')->getMember($member['liveid']);

				if (empty($member)) {
					return NULL;
				}

				$openid2 = $member['openid'];
				model('task')->checkTaskReward('commission_money', $level_price_2, $openid2);
				model('task')->checkTaskProgress((int) $level_price_2, 'pyramid_money', 0, $openid2);

				if (0 < $level_price_3) {
					$member = model('member')->getMember($member['liveid']);

					if (empty($member)) {
						return NULL;
					}

					$openid3 = $member['openid'];
					model('task')->checkTaskReward('commission_money', $level_price_3, $openid3);
					model('task')->checkTaskProgress((int) $level_price_3, 'pyramid_money', 0, $openid3);
				}
			}
		}
	}

	public static function getLastApply($mid, $type = -1)
	{
		$sql = 'mid=' . $mid;

		if (-1 < $type) {
			$sql .= ' and type=' . $type;
		}

		$data = Db::name('shop_commission_apply')->where($sql)->order('id desc')->find();
		return $data;
	}

	public static function getRepurchase($mid, array $time)
	{
		if (empty($mid) || empty($time)) {
			return NULL;
		}

		$set = self::getSet();
		$agentLevel = self::getLevel($mid);

		if ($agentLevel) {
			$repurchase_price = (double) $agentLevel['repurchase'];
		} else {
			$repurchase_price = (double) $set['repurchase_default'];
		}

		$residue = 0;
		$month_array = array();

		foreach ($time as $value) {
			$time1 = strtotime(date($value . '-1'));
			$time2 = strtotime('+1 months', $time1);

			if (!empty($repurchase_price)) {
				$order_price = (double) Db::name('shop_order')->where('mid = ' . $mid . ' AND `status`>2 AND `createtime` BETWEEN ' . $time1 . ' AND ' . $time2)->sum('price');
				$year_month = explode('-', $value);
				$year_month[0] = (int) $year_month[0];
				$year_month[1] = (int) $year_month[1];
				$residue_price = (double) Db::name('shop_commission_repurchase')->where('`mid`=' . $mid . ' AND `year`=' . $year_month[0] . ' AND `month`=' . $year_month[1])->sum('repurchase');
				$month_array[$value] = max($repurchase_price - ($order_price + $residue_price), 0);
			}
		}

		return $month_array;
	}

	public static function inArray($item, $array)
	{
		$flipArray = array_flip($array);
		return isset($flipArray[$item]);
	}


}
