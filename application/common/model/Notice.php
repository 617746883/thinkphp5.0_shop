<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Notice extends \think\Model
{
	/**
	 * 阿里推送
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  
	 */
	public static function Alipush($regId,$title,$msg)
	{
		$noticeset = model('common')->getSysset('notice');
		if(empty($noticeset['ali_push'])) {
			return;
		}
		return;
		$sec = iunserializer($set['sec']);
		$accessKeyId = $sec['ali_push']['ali_accessKeyId'];
        $accessKeySecret = $sec['ali_push']['ali_accessKeySecret'];
        $appKey = $sec['ali_push']['ali_appKey'];
	}

	/**
	 * 极光推送
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  
	 */
	public static function Jpush($regId,$title,$msg,$messagetype)
	{
		$noticeset = model('common')->getSysset('notice');
		if(empty($noticeset['j_push'])) {
			return;
		}
		$set = model('common')->getSec();
		$sec = iunserializer($set['sec']);
		$app_key = $sec['j_push']['jiguang_appKey'];
        $master_secret = $sec['j_push']['jiguang_masterSecret'];
        $client = new \JPush\Client($app_key, $master_secret, NULL);

        $push_payload = $client->push()
            ->setPlatform('all')
            // ->addAllAudience()
            ->addRegistrationId($regId)
            ->iosNotification(
                array(
                    'title' => $title,
                    // "subtitle" => "JPush Subtitle" ,
                    "body" => $msg
                ), 
                array(
	                'badge' => '+1',
	                'sound' => 'default',
	                'content-available' => true,
	                'mutable-content' => true,
	                'category' => 'jiguang',
	                'extras' => array(
	                    'msg' => $messagetype
	                ),
	            )
            )
            ->androidNotification($msg, array(
                'title' => $title,
                'builder_id' => 2,
                'priority' => 2,
                'category' => 'jiguang',
                'alert_type' => -1,
                'style' => 1,
                'big_text' => $msg,
                'extras' => array(
                    'msg' => $messagetype
                ),
            ))
            ->message($msg, array(
                'title' => $title,
                'msg_content' => $msg,
                'content_type' => 'text',
                'extras' => array(
                    'msg' => $messagetype
                ),
            ))
            ->options(array(
                // 'sendno' => 100,
                'time_to_live' => 1,
                'apns_production' => false,
                // 'big_push_duration' => 1,
                'apns_collapse_id' => ''
            ));
        try {
            $response = $push_payload->send();
            return $response;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            return;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            return;
        }
		return true;
	}

	/**
	 * 发送订单通知
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  退回地址id
	 */
	public static function sendOrderMessage($orderid = '0', $delRefund = false, $raid = NULL, $refundid = 0)
	{
		if (empty($orderid)) {
			return;
		}
		$order = Db::name('shop_order')->where('id',$orderid)->find();

		if (empty($order)) {
			return;
		}
		$is_merch = 0;
		$mid = $order['mid'];
		if ($order['isparent'] == 1) {
			$scondition = ' og.parentorderid= ' . $orderid;
		}
		 else {
			$scondition = ' og.orderid=' . $orderid;
		}

		$order_goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where($scondition)->field('g.id,g.title,og.realprice,og.total,og.price,og.optionname as optiontitle,g.noticemid,g.noticetype,og.sendtype,og.expresscom,og.expresssn,og.sendtime')->select();

		$goods = '';
		$goodsname = '';
		$goodsnum = 0;

		foreach ($order_goods as $og ) {
			$goods .= "  " . $og['title'] . '( ';

			if (!(empty($og['optiontitle']))) {
				$goods .= ' 规格: ' . $og['optiontitle'];
			}

			$goods .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . '); ';
			$goodsname .= $og['title'] . ' ' . "  ";
			$goodsnum += $og['total'];
		}

		$couponstr = '';
		if ((0 < $order['couponid']) && $order['couponprice']) {
			$couponstr = ' 优惠券抵扣 ' . price_format($order['couponprice'], 2) . '元 ' . " ";
		}

		$orderpricestr = ' 订单总价: ' . $order['price'] . '(包含运费:' . $order['dispatchprice'] . ')';
		$member = model('member')->getMember($mid);
		$carrier = false;
		$store = false;

		if (!(empty($order['storeid']))) {
			if (0 < $order['merchid']) {
				$store = Db::name('shop_store')->where('id',$order['storeid'])->find();
			} else {
				$store = Db::name('shop_store')->where('id',$order['storeid'])->find();
			}
		}
		$buyerinfo = '';
		$buyerinfo_name = '';
		$buyerinfo_mobile = '';
		$addressinfo = '';
		if (!(empty($order['address']))) {
			$address = iunserializer($order['address_send']);
			if ((is_array($address) && empty($address)) || !(is_array($address))) {
				$address = iunserializer($order['address']);
				if ((is_array($address) && empty($address)) || !(is_array($address))) {
					$address = Db::name('shop_member_address')->where('id',$order['addressid'])->field('id,realname,mobile,address,province,city,area')->find();
				}
			}

			if (!(empty($address))) {
				$addressinfo = $address['province'] . $address['city'] . $address['area'] . ' ' . $address['address'];
				$buyerinfo = '收件人: ' . $address['realname'] . " " . '联系电话: ' . $address['mobile'] . " " . '收货地址: ' . $addressinfo;
				$buyerinfo_name = $address['realname'];
				$buyerinfo_mobile = $address['mobile'];
			}
		} else {
			$carrier = iunserializer($order['carrier']);
			if (is_array($carrier) && !(empty($carrier))) {
				$buyerinfo = '联系人: ' . $carrier['carrier_realname'] . " " . '联系电话: ' . $carrier['carrier_mobile'];
				$buyerinfo_name = $carrier['carrier_realname'];
				$buyerinfo_mobile = $carrier['carrier_mobile'];
			}
		}

		$set = model('common')->getSysset();
		if (!(empty($set))) {
			$shop = $set['shop'];

			if (!(empty($shop))) {
				$shopname = $shop['name'];
			}
		}
		$datas = array(
			array('name' => '商城名称', 'value' => $shop['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '订单号', 'value' => $order['ordersn']),
			array('name' => '订单金额', 'value' => $order['price']),
			array('name' => '运费', 'value' => $order['dispatchprice']),
			array('name' => '商品详情', 'value' => $goods),
			array('name' => '快递公司', 'value' => $order['expresscom']),
			array('name' => '快递单号', 'value' => $order['expresssn']),
			array('name' => '购买者姓名', 'value' => $buyerinfo_name),
			array('name' => '购买者电话', 'value' => $buyerinfo_mobile),
			array('name' => '收货地址', 'value' => $addressinfo),
			array('name' => '下单时间', 'value' => date('Y-m-d H:i', $order['createtime'])),
			array('name' => '支付时间', 'value' => date('Y-m-d H:i', $order['paytime'])),
			array('name' => '发货时间', 'value' => date('Y-m-d H:i', $order['sendtime'])),
			array('name' => '收货时间', 'value' => date('Y-m-d H:i', $order['finishtime'])),
			array('name' => '取消时间', 'value' => date('Y-m-d H:i', $order['canceltime'])),
			array('name' => '门店', 'value' => (!(empty($store)) ? $store['storename'] : '')),
			array('name' => '门店地址', 'value' => (!(empty($store)) ? $store['address'] : '')),
			array('name' => '门店联系人', 'value' => (!(empty($store)) ? $store['realname'] . '/' . $store['mobile'] : '')),
			array('name' => '门店营业时间', 'value' => (!(empty($store)) ? ((empty($store['saletime']) ? '全天' : $store['saletime'])) : '')),
			array('name' => '虚拟物品自动发货内容', 'value' => $order['virtualsend_info']),
			array('name' => '虚拟卡密自动发货内容', 'value' => $order['virtual_str']),
			array('name' => '自提码', 'value' => $order['verifycode']),
			array('name' => '备注信息', 'value' => $order['remark']),
			array('name' => '商品数量', 'value' => $goodsnum),
			array('name' => '商品名称', 'value' => $goodsname)
			);
		if (!(empty($order['merchid']))) {
			$is_merch = 1;
			$merch_tm = model('merch')->getSet('notice', $order['merchid']);
		}
		if ($delRefund) {
			$r_type = array('退款', '退货退款', '换货');
			if (!(empty($refundid))) {
				$refund = Db::name('shop_order_refund')->where('id',$refundid)->find();

				if (empty($refund)) {
					return;
				}

				$datas[] = array('name' => '售后类型', 'value' => $r_type[$refund['rtype']]);
				$datas[] = array('name' => '申请金额', 'value' => ($refund['rtype'] == 2 ? '-' : $refund['applyprice']));
				$datas[] = array('name' => '退款金额', 'value' => $refund['price']);
				$datas[] = array('name' => '换货快递公司', 'value' => $refund['rexpresscom']);
				$datas[] = array('name' => '换货快递单号', 'value' => $refund['rexpresssn']);

				if ($refund['status'] == 5) {
					if ($refund['rtype'] == 2) {
						if (empty($address)) {
							return;
						}
						$text = '您申请换货的宝贝已经成功发货，请注意查收 ' . " " . '订单编号：' . " " . $order['ordersn'] . " " . '快递公司：[换货快递公司]' . $refund['rexpresscom'] . '  快递单号：[换货快递单号]' . $refund['rexpresssn'];
						$msg = '您申请换货的宝贝已经成功发货，请注意查收';
						self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				} else if ($refund['status'] == 3) {
					if (($refund['rtype'] == 2) || ($refund['rtype'] == 1)) {
						if (0 < $order['merchid']) {
							$salerefund = iunserializer($refund['refundaddress']);
						} else {
							if (empty($raid)) {
								$salerefund = Db::name('shop_refund_address')->where('isdefault',1)->find();
							} else {
								$salerefund = Db::name('shop_refund_address')->where('id',$raid)->find();
							}
						}
						if (!(empty($salerefund))) {
							$datas[] = array('name' => '卖家收货地址', 'value' => $salerefund['province'] . $salerefund['city'] . $salerefund['area'] . ' ' . $salerefund['address']);
							$datas[] = array('name' => '卖家联系电话', 'value' => $salerefund['mobile']);
							$datas[] = array('name' => '卖家收货人', 'value' => $salerefund['name']);
						}

						$text = '您好，您的退换货申请已经通过，请您及时发送快递。' . " " . $order['ordersn'] . " " . '[订单号]' . "\n" . '请将快递发送到以下地址，并随包裹填写您的订单编号以及联系方式，我们将尽快为您处理' . " " . '邮寄地址：' . $salerefund['province'] . $salerefund['city'] . $salerefund['area'] . ' ' . $salerefund['address'] . '联系电话：[卖家联系电话]' . $salerefund['mobile'] . '收货人：[卖家收货人]' . $salerefund['name'] . '感谢您关注，如有疑问请联系在线客服';
						$msg = '您好，您的退换货申请已经通过，请您及时发送快递。';
						self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				} else if ($refund['status'] == 1) {
					if (($refund['rtype'] == 0) || ($refund['rtype'] == 1)) {
						$refundtype = '';

						if (empty($refund['refundtype'])) {
							$refundtype = '余额账户';
						} else if ($refund['refundtype'] == 1) {
							$refundtype = '您的对应支付渠道（如银行卡，微信钱包等, 具体到账时间请您查看微信支付通知)';
						} else {
							$refundtype = ' 请联系客服进行退款事项！';
						}

						$text = '您好，您有一笔' . $r_type[$refund['rtype']] . '已经成功，[退款金额]' . $refund['price'] . '元已经退回您的申请退款账户内，请及时查看 。' . " " . '订单编号：' . $order['ordersn'] . '退款金额：' . $refund['price'] . '元' . " " . '退款原因：[售后类型]' . $r_type[$refund['rtype']] . '退款去向：' . $refundtype . " " . '感谢您关注，如有疑问请联系在线客服';
						$msg = '您好，您有一笔' . $r_type[$refund['rtype']] . '已经成功';
						self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				} else if ($refund['status'] == -1) {
					$text = '您好，你有一笔' . $r_type[$refund['rtype']] . '被驳回，您可以与我们取得联系！' . " " . '退款金额：[申请金额]' . $refund['applyprice'] . '元' . "" . '订单编号：' . $order['ordersn'] . '商品详情：' . $goodsname . '订单编号：' . $order['ordersn'] . " " . '退款金额：' . (($refund['rtype'] == 2 ? '-' : $refund['applyprice'])) . '元' . "  " . '感谢您关注，如有疑问请联系在线客服或点击查看详情';
					$msg = '您好，你有一笔' . $r_type[$refund['rtype']] . '被驳回，您可以与我们取得联系！';
					self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
				}
			}
			return;
		}
		if ($order['status'] == -1) {
			$text = '您好，您的订单由于主动取消或长时间未付款已经关闭！！！' . " " . '商品名称：' . $goodsname . " " . '订单编号：' . " " . $order['ordersn'] . " " . '订单金额：' . $order['price'] . '下单时间：' . date('Y-m-d H:i', $order['createtime']) . '关闭时间：' . date('Y-m-d H:i', $order['canceltime']) . '感谢您的关注，如有疑问请联系在线客服咨询';
			$msg = '您好，您的订单由于主动取消或长时间未付款已经关闭！！！';
			self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
		} else if (($order['status'] == 0) && ($order['paytype'] == 3)) {
			$is_send = 0;
			if (!(empty($merch_tm)) && empty($merch_tm['saler_pay_close_advanced'])) {
				$is_send = 1;
				$tm['mid'] = $merch_tm['mid'];
			}

			if (!(empty($is_send))) {
				$msg = '您有新的货到付款订单！！';
				$text = '您有新的货到付款订单！！' . " " . '请及时安排发货。' . " " . '订单号：' . $order['ordersn'] . " " . '订单金额：' . $order['price'] . '下单时间：' . date('Y-m-d H:i', $order['createtime']) . ' ' . " " . '购买商品信息：[商品详情]' . $goods . '备注信息：' . $order['remark'] . ' ' . "" .  $buyerinfo . '请及时安排发货';
				$account = model('common')->getAccount();

				if (!(empty($tm['mid']))) {
					$mids = explode(',', $tm['mid']);

					foreach ($mids as $tmmid ) {
						if (empty($tmmid)) {
							continue;
						}
						self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				}
			}

			if (!(empty($tm['mobile'])) && empty($tm['saler_pay_close_sms']) && empty($is_merch)) {
				$mobiles = explode(',', $tm['mobile']);

				foreach ($mobiles as $mobile ) {
					if (empty($mobile)) {
						continue;
					}
				}
			}

			$i = 0;

			foreach ($order_goods as $og ) {
				if (!(empty($og['noticemid'])) && !(empty($og['noticetype']))) {
					$noticetype = explode(',', $og['noticetype']);
					if (($og['noticetype'] == '1') || (is_array($noticetype) && in_array('1', $noticetype))) {
						++$i;
						$goodstr = $og['title'] . '( ';

						if (!(empty($og['optiontitle']))) {
							$goodstr .= ' 规格: ' . $og['optiontitle'];
							$optiontitle = '( 规格: ' . $og['optiontitle'] . ')';
						}

						$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';
						$text = '您有新的货到付款订单！！' . " " . '请及时安排发货。' . " " . '订单号：' .  $order['ordersn']  . '订单金额：' . $order['price'] . '下单时间：' . date('Y-m-d H:i', $order['createtime']) . '--' . '备注信息：[备注信息]' . "" . '--' . "" . '收货人：[购买者姓名]' . "" . '收货人电话:[购买者电话]' . "" . '收货地址:[收货地址]' . "" . '请及时安排发货'. '商品详情：' . $goodstr . $couponstr;
						$msg = '您有新的货到付款订单！！';
						$datas['gooddetail'] = array('name' => '单品详情', 'value' => $goodstr);
						foreach ($noticemids as $noticemid ) {
							self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
						}
					}
				}
			}
		} else if (($order['status'] == 1) && empty($order['sendtype'])) {
			$is_send = 0;
			if (!(empty($merch_tm)) && empty($merch_tm['saler_pay_close_advanced'])) {
				$is_send = 1;
				$tm['mid'] = $merch_tm['mid'];
			}
			if (!(empty($is_send))) {
				$msg = '您有新的已付款订单！！';
				$text = '您有新的订单于' . date('Y-m-d H:i', $order['paytime']) . '已付款！！' . " " . '请登录后台查看详情并及时安排发货。';

				if (!(empty($tm['mid']))) {
					$mids = explode(',', $tm['mid']);
					foreach ($mids as $tmmid ) {
						if (empty($tmmid)) {
							continue;
						}
						self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				}
			}

			if (!(empty($tm['mobile'])) && empty($tm['saler_pay_close_sms']) && empty($is_merch)) {
				$mobiles = explode(',', $tm['mobile']);

				foreach ($mobiles as $mobile ) {
					if (empty($mobile)) {
						continue;
					}
				}
			}
			
			$i = 0;
			foreach ($order_goods as $og ) {
				if (!(empty($og['noticemid'])) && !(empty($og['noticetype']))) {
					$noticetype = explode(',', $og['noticetype']);
					if (($og['noticetype'] == '1') || (is_array($noticetype) && in_array('1', $noticetype))) {
						++$i;
						$goodstr = $og['title'] . '( ';

						if (!(empty($og['optiontitle']))) {
							$goodstr .= ' 规格: ' . $og['optiontitle'];
							$optiontitle = '( 规格: ' . $og['optiontitle'] . ')';
						}

						$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';

						$msg = '您有新的已付款订单！！';
						$text = '您有新的已付款订单！！' . " " . '请及时安排发货。' . "  " . $goodstr;
						$noticemids = explode(',', $og['noticemid']);
						$datas['gooddetail'] = array('name' => '单品详情', 'value' => $goodstr);
						foreach ($noticemids as $noticemid ) {
							self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
						}
					}
				}
			}

			$remark = " ";

			if ($order['isverify']) {
				$remark = " " . '点击订单详情查看可消费门店, 【' . $shopname . '】欢迎您的再次购物！' . " ";
			}
			 else if ($order['dispatchtype']) {
				$remark = " " . '您可以到选择的自提点进行取货了,【' . $shopname . '】欢迎您的再次购物！' . " ";
			}

			$text = '您的订单已经成功支付，我们将尽快为您安排发货！！ ' . " " . '订单号：' . " " . $order['ordersn'] . " " . '商品名称：' . $goodsname . " " . '商品数量：' . $goodsnum . '下单时间：' . date('Y-m-d H:i', $order['paytime']) . '订单金额： ' . $order['price'] . $couponstr . $remark;
			$msg = '您的订单已经成功支付，我们将尽快为您安排发货！！';
			self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
			
			if (($order['dispatchtype'] == 1) && empty($order['isverify'])) {
				if (!($carrier) || !($store)) {
					return;
				}
				$msg = '您的订单已经成功支付！！ ';
				$remark = '请您到选择的自提点进行取货, 自提联系人: ' . $store['realname'] . ' 联系电话: ' . $store['mobile'];
				$text = '自提订单提交成功!！' . " " . '自提码：' . $order['verifycode'] . $remark;
				self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
			}
		} else {
			if (($order['status'] == 2) || (($order['status'] == 1) && !(empty($order['sendtype'])))) {
				$isonlyverify = model('order')->checkisonlyverifygoods($orderid);

				if (empty($order['dispatchtype']) && !($isonlyverify)) {
					$datas[] = array('name' => '发货类型', 'value' => (empty($order['sendtype']) ? '按订单发货' : '按包裹发货'));

					if (empty($order['sendtype'])) {
						if (empty($address)) {
							return;
						}

						$text = '您的宝贝已经成功发货！ ' . " " . '商品名称：' . $goodsname . '  快递公司：' . $order['expresscom'] . '快递单号：' . $order['expresssn'];
						$msg = '您的宝贝已经成功发货！';

						if (0 < $order['merchid']) {
							$merch_user = model('merch')->getListUserOne($order['merchid']);

							if (!(empty($merch_user['mobile']))) {
								$text .= " " . '商户电话：[商户电话]';
								$datas[] = array('name' => '商户电话', 'value' => $merch_user['mobile']);
							}

							if (!(empty($merch_user['address']))) {
								$text .= " " . '商户地址：[商户地址]';
								$datas[] = array('name' => '商户地址', 'value' => $merch_user['address']);
							}
						}
						$remark_value .= " " . '我们正加速送到您的手上，请您耐心等候';					

						$text .= $remark_value;

						self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					} else {
						$package_goods = array();
						$package_expresscom = '';
						$package_expresssn = '';
						$package_sendtime = '';
						$package_goodsdetail = '';
						$package_goodsname = '';

						foreach ($order_goods as $og ) {
							if ($og['sendtype'] == $order['sendtype']) {
								$package_goods[] = $og;

								if (empty($package_expresscom)) {
									$package_expresscom = $og['expresscom'];
								}

								if (empty($package_expresssn)) {
									$package_expresssn = $og['expresssn'];
								}

								if (empty($package_sendtime)) {
									$package_sendtime = $og['sendtime'];
								}

								$package_goodsdetail .= "  " . $og['title'] . '( ';

								if (!(empty($og['optiontitle']))) {
									$package_goodsdetail .= ' 规格: ' . $og['optiontitle'];
								}

								$package_goodsdetail .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . '); ';
								$package_goodsname .= $og['title'] . ' ' . "  ";
							}
						}

						if (empty($package_goods)) {
							return;
						}

						$datas[] = array('name' => '包裹快递公司', 'value' => $package_expresscom);
						$datas[] = array('name' => '包裹快递单号', 'value' => $package_expresssn);
						$datas[] = array('name' => '包裹发送时间', 'value' => date('Y-m-d H:i', $package_sendtime));
						$datas[] = array('name' => '包裹商品详情', 'value' => $package_goodsdetail);
						$datas[] = array('name' => '包裹商品名称', 'value' => $package_goodsname);
						$remark = '<a href=\'' . $url . '\'>点击快速查询物流信息</a>';
						$text = '您的包裹已经成功发货！ ' . "  " . '商品名称：[包裹商品名称] ' . $package_goodsname . ' 快递公司：[包裹快递公司]' . $package_expresscom . ' 快递单号：[包裹快递单号]' . $package_expresssn;
						$msg = '您的包裹已经发货！'; 
						self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
					}
				}
				if ($isonlyverify) {
					$is_send = 0;

					if (empty($is_merch)) {
						$is_send = 1;
					}
					 else if (!(empty($merch_tm)) && empty($merch_tm['saler_pay_close_advanced'])) {
						$is_send = 1;
						$tm['mid'] = $merch_tm['mid'];
					}

					if (!(empty($is_send))) {
						$msg = '您有新的记次时商品订单于' . date('Y-m-d H:i', $order['paytime']) . '已付款！';
						$text = '您有新的已付款记次时商品订单！' . " " . '请登录后台查看详情。' . "  " . '订单号：' . " " . $order['ordersn'] . " " . '订单金额：' . $order['price'] . '支付时间：' . date('Y-m-d H:i', $order['paytime']) . '--' . "  " . '购买商品信息：' . $goods . '备注信息：' . $order['remarksaler '];
						$account = model('common')->getAccount();

						if (!(empty($tm['mid']))) {
							$mids = explode(',', $tm['mid']);
							foreach ($mids as $tmmid ) {
								if (empty($tmmid)) {
									continue;
								}
								self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}
					}


					if (!(empty($tm['mobile'])) && empty($tm['saler_pay_close_sms']) && empty($is_merch)) {
						$mobiles = explode(',', $tm['mobile']);

						foreach ($mobiles as $mobile ) {
							if (empty($mobile)) {
								continue;
							}
						}
					}

					$i = 0;

					foreach ($order_goods as $og ) {
						if (!(empty($og['noticemid'])) && !(empty($og['noticetype']))) {
							$noticetype = explode(',', $og['noticetype']);
							if (($og['noticetype'] == '1') || (is_array($noticetype) && in_array('1', $noticetype))) {
								++$i;
								$goodstr = $og['title'] . '( ';

								if (!(empty($og['optiontitle']))) {
									$goodstr .= ' 规格: ' . $og['optiontitle'];
									$optiontitle = '( 规格: ' . $og['optiontitle'] . ')';
								}

								$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';
								$text = '您有新的已付款记次时商品订单！！' . " " . '请及时安排发货。' . "  " . '订单号：' . " " . $order['ordersn'] . " " . '订单金额：' . " " . '支付时间：' . date('Y-m-d H:i', $order['paytime']) . '--' . " " . '购买商品信息：' . $goodstr . " ";
								$remark = '订单号：' . "\n" . $order['ordersn'] . "\n" . '商品详情：' . $goodstr;
								$msg = '您有新的记次时商品订单于' . date('Y-m-d H:i', $order['paytime']) . '已付款！！';
								$datas['gooddetail'] = array('name' => '单品详情', 'value' => $goodstr);
								$noticemids = explode(',', $og['noticemid']);
								$datas['gooddetail'] = array('name' => '单品详情', 'value' => $goodstr);
								foreach ($noticemids as $noticemid ) {
									self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
								}
							}

						}

					}
				}

			} else if ($order['status'] == 3) {
				if (!(empty($order['virtual']))) {
					$msg = '买家购买的商品已经自动发货!' . "";
					$remark = '订单号：' . $order['ordersn'] . "\n" . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . "\n" . '商品详情：' . $goods . "\n\n" . '购买者信息:' . "\n" . $buyerinfo;
					$text = $first . " " . '订单号：' . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goods . "  " . '购买者信息:' . " " . $buyerinfo;
					$is_send = 0;

					if (!(empty($merch_tm)) && empty($merch_tm['saler_finish_close_advanced'])) {
						$is_send = 1;
						$tm['mid2'] = $merch_tm['mid2'];
					}


					if (!(empty($is_send))) {
						$msg = '买家购买的商品已经自动发货!' . "";
						$account = model('common')->getAccount();

						if (!(empty($tm['mid2']))) {
							$mids = explode(',', $tm['mid2']);

							foreach ($mids as $tmmid ) {
								if (empty($tmmid)) {
									continue;
								}
								self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}
					}

					if (!(empty($tm['mobile2'])) && empty($tm['saler_finish_close_sms'])) {
						$mobiles = explode(',', $tm['mobile2']);

						foreach ($mobiles as $mobile ) {
							if (empty($mobile)) {
								continue;
							}
						}
					}

					foreach ($order_goods as $og ) {
						$noticetype = explode(',', $og['noticetype']);
						if (($og['noticetype'] == '2') || (is_array($noticetype) && in_array('2', $noticetype))) {
							$goodstr = $og['title'] . '( ';

							if (!(empty($og['optiontitle']))) {
								$goodstr .= ' 规格: ' . $og['optiontitle'];
							}

							$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';
							$remark = '订单号：' . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goodstr . "  " . '购买者信息:' . " " . $buyerinfo;
							$text = $msg . " " . '订单号：' . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goodstr . "  " . '购买者信息:' . " " . $buyerinfo;
							$datas[] = array('name' => '单品详情', 'value' => $goodstr);
							$noticemids = explode(',', $og['noticemid']);

							foreach ($noticemids as $noticemid ) {
								self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}

					}
				} else if ($order['isvirtualsend']) {
					$msg = '买家购买的商品已经自动发货!' . " ";
					$text = $first . " " . '订单号：' . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goods . "  " . '购买者信息:' . " " . $buyerinfo;
					$is_send = 0;

					if (!(empty($merch_tm)) && empty($merch_tm['saler_finish_close_advanced'])) {
						$is_send = 1;
						$tm['mid2'] = $merch_tm['mid2'];
					}

					if (!(empty($is_send))) {
						$msg = '买家购买的商品已经自动发货!' . " ";
						$account = model('common')->getAccount();

						if (!(empty($tm['mid2']))) {
							$mids = explode(',', $tm['mid2']);

							foreach ($mids as $tmmid ) {
								if (empty($tmmid)) {
									continue;
								}
								self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}

					}


					if (!(empty($tm['mobile2'])) && empty($tm['saler_finish_close_sms'])) {
						$mobiles = explode(',', $tm['mobile2']);

						foreach ($mobiles as $mobile ) {
							if (empty($mobile)) {
								continue;
							}
						}
					}

					foreach ($order_goods as $og ) {
						$noticetype = explode(',', $og['noticetype']);
						if (($og['noticetype'] == '2') || (is_array($noticetype) && in_array('2', $noticetype))) {
							$goodstr = $og['title'] . '( ';

							if (!(empty($og['optiontitle']))) {
								$goodstr .= ' 规格: ' . $og['optiontitle'];
							}

							$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';
							$text = $msg . " " . '订单号：' . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goodstr . "  " . '购买者信息:' . " " . $buyerinfo;

							$noticemids = explode(',', $og['noticemid']);
							$datas[] = array('name' => '单品详情', 'value' => $goodstr);
							foreach ($noticemids as $noticemid ) {
								self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}
					}
				} else {
					$msg = '买家购买的商品已经确认收货!' . " ";

					if ($order['isverify'] == 1) {
						$msg = '买家购买的商品已经确认核销!' . " ";
					}

					$text = $msg . " " . '订单号：' . " " . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goods;

					if (!(empty($buyerinfo))) {
						$text = $text . "  " . '购买者信息:' . " " . $buyerinfo;
					}

					$is_send = 0;

					if (!(empty($merch_tm)) && empty($merch_tm['saler_finish_close_advanced'])) {
						$is_send = 1;
						$tm['mid2'] = $merch_tm['mid2'];
					}

					if (!(empty($is_send))) {
						$msg = '买家购买的商品已经确认收货!' . " ";
						$account = model('common')->getAccount();

						if (!(empty($tm['mid2']))) {
							$mids = explode(',', $tm['mid2']);

							foreach ($mids as $tmmid ) {
								if (empty($tmmid)) {
									continue;
								}
								self::sendNotice(array('mid' => $tmmid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}						
					}

					if (!(empty($tm['mobile2'])) && empty($tm['saler_finish_close_sms']) && empty($is_merch)) {
						$mobiles = explode(',', $tm['mobile2']);

						foreach ($mobiles as $mobile ) {
							if (empty($mobile)) {
								continue;
							}
						}
					}

					foreach ($order_goods as $og ) {
						$noticetype = explode(',', $og['noticetype']);
						if (($og['noticetype'] == '2') || (is_array($noticetype) && in_array('2', $noticetype))) {
							$goodstr = $og['title'] . '( ';

							if (!(empty($og['optiontitle']))) {
								$goodstr .= ' 规格: ' . $og['optiontitle'];
							}

							$goodstr .= ' 单价: ' . ($og['price'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . '); ';
							$text = '买家购买的商品已经确认收货!' . " " . '订单号：' . " " . $order['ordersn'] . " " . '收货时间：' . date('Y-m-d H:i', $order['finishtime']) . " " . '商品详情：' . $goods;

							if (!(empty($buyerinfo))) {
								$text = $text . "  " . '购买者信息:' . " " . $buyerinfo;
							}

							$msg = '买家购买的商品已经确认收货!';
							$noticemids = explode(',', $og['noticemid']);
							$datas[] = array('name' => '单品详情', 'value' => $goodstr);
							foreach ($noticemids as $noticemid ) {
								self::sendNotice(array('mid' => $noticemid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $order['ordersn'], 'id' => $order['id']));
							}
						}
					}
				}
			}
		}

		return;
	}

	public static function sendOrderChangeMessage($mid, $params, $type)
	{
		if (empty($mid)) {
			return false;
		}

		$member = model('member')->getMember($mid);

		if ($type == 'orderstatus') {
			$datas = array(
				array('name' => '粉丝昵称', 'value' => $member['nickname']),
				array('name' => '修改时间', 'value' => time()),
				array('name' => '订单号', 'value' => $params['orderid']),
				array('name' => '订单编号', 'value' => $params['ordersn']),
				array('name' => '原收货地址', 'value' => $params['olddata']),
				array('name' => '新收货地址', 'value' => $params['data']),
				array('name' => '订单原价格', 'value' => $params['olddata']),
				array('name' => '订单新价格', 'value' => $params['data']),
				array('name' => '订单更新内容', 'value' => $params['title'])
				);

			$msg = '亲爱的' . $member['nickname'] . '，您的' . $params['title'] . '已更新';

			if ($params['type'] == '1') {
				$datas[] = array('name' => '订单更新类型', 'value' => '订单金额变更');
				$msg['OrderStatus'] = array('title' => '订单状态', 'value' => '订单金额变更', 'color' => '#ff0000');
				$msg['remark'] = array('value' => '订单原价格 : ' . $params['olddata'] . '元' . "\n" . '订单新价格 : ' . $params['data'] . '元' . "\n\n" . '如有疑问请联系在线客服。', 'color' => '#000000');
				$text2 = '订单原价 : ' . $params['olddata'] . '元' . " " . '订单现价 : ' . $params['data'] . '元';
			}
			 else {
				$datas[] = array('name' => '订单更新类型', 'value' => '收货地址变更');
				$msg['OrderStatus'] = array('title' => '订单状态', 'value' => '收货地址变更', 'color' => '#ff0000');
				$text2 = " " . '原收货地址 : ' . $params['olddata'] . " " . '新收货地址 : ' . $params['data'];
			}

			$text = '亲爱的' . $member['nickname'] . '，您的' . $params['title'] . '已更新，详情如下：' . " " . '订单编号：' . " " . $params['ordersn'] . " " . '订单状态：[订单更新类型]' . $text2 . "  " ;
			self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'order', 'tid' => $params['ordersn'], 'id' => $params['orderid']));
		}
		return;
	}

	public static function sendSecondgoodschecked($id) 
	{
		if (empty($id)) {
			return false;
		}

		$item = Db::name('citywide_secondgoods')->where('id',$id)->find();
		if (empty($item)) {
			return false;
		}
		if (empty($item['mid'])) {
			return false;
		}
		$mid = $item['mid'];
		$member = model('member')->getMember($mid);
		$datas = array(
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '审核时间', 'value' => time()),
			array('name' => '商品名称：', 'value' => $item['title']),
			);

		$msg = '亲爱的' . $member['nickname'] . '，您发布的二手商品[' . $item['title'] . ']状态已更新';

		if ($item['checked' == -1]) {
			$datas[] = array('name' => '商品审核状态', 'value' => '审核未通过');
			$text2 = '商品审核状态 : 审核未通过 ' . $item['failedreason'];
		} else {
			if($item['checked'] == 0) {
				$datas[] = array('name' => '商品审核状态', 'value' => '已通过审核');
				$text2 = " 商品审核状态 : 已通过审核 ";
			}			
		}

		$text = '亲爱的' . $member['nickname'] . '，您发布的二手商品' . $item['title'] . '已状态更新，详情如下：' . ' 商品状态：[商品更新类型]' . $text2;
		self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'secondgoods', 'tid' => $item['id'], 'id' => $item['id']));
	}

	/**
	 * 积分变动提示
	 * @param type $mid
	 * @param type $oldlevel
	 * @param type $level
	 * @return type
	 */
	public function sendMemberPointChange($mid, $pointchange = 0, $changetype = 0, $from = 0)
	{
		$shopset = model('common')->getSysset();
		$member = model('member')->getMember($mid);
		$credit1 = model('member')->getCredit($mid);

		if (!(empty($usernotice['backpoint_ok']))) {
			return;
		}

		$credittext = ((empty($shopset['trade']['credittext']) ? '积分' : $shopset['trade']['credittext']));
		$pointtext = '';

		if ($changetype == 0) {
			$pointtext = '增加' . (double) $pointchange . $credittext;
		} else if ($changetype == 1) {
			$pointtext = '减少' . (double) $pointchange . $credittext;
		}


		if (empty($from)) {
			$fromstr = '管理员后台手动处理';
		} else if ($from == 1) {
			$fromstr = '收银台积分变动提醒';
		}

		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '积分变动', 'value' => $pointtext),
			array('name' => '赠送时间', 'value' => date('Y-m-d H:i', time())),
			array('name' => '积分余额', 'value' => (double) $member['credit1'] . $credittext)
			);
		$msg = '亲爱的' . $member['nickname'] . '，您的' . $credittext . '发生变动';
		$remark = " " . '[' . $shopset['shop']['name'] . ']感谢您的支持，如有疑问请联系在线客服。';
		$text = '亲爱的[' . $member['nickname'] . ']， 您的' . $credittext . '发生变动，具体内容如下：' . " " . '积分变动：[' . $pointtext . ']' . "" . '变动时间：[' . date('Y-m-d H:i', time()) . ']' . "" . '充值方式：' . $fromstr . "" . '当前积分余额：[积分余额] ' . "" . $remark;
		
		self::sendNotice(array('mid' => $mid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'credit', 'tid' => 0, 'id' => 0));
	}

	public function sendStockWarnMessage($goodsid, $optionid)
	{
		return;
		if (!(empty($order['merchid']))) {
			$is_merch = 1;
			$merch_tm = model('merch')->getSet('notice', $order['merchid']);
		}

		$goodsid = intval($goodsid);
		$optionid = intval($optionid);
		$shopset = model('common')->getSysset();
		$tm = $set['notice'];

		if (empty($goodsid)) {
			return;
		}

		$goods = Db::name('shop_goods')->where('id',$goodsid)->find();

		if (empty($goods)) {
			return;
		}

		$goodtitle = $goods['title'];

		if (!(empty($optionid))) {
			$option = Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goodsid)->find();

			if (!(empty($option))) {
				$goodtitle = $goodtitle . '(' . $option['title'] . '}';
			}
		}


		$data = model('common')->getSysset('trade');

		if (!(empty($data['stockwarn']))) {
			$stockwarn = intval($data['stockwarn']) . '件';
		} else {
			$stockwarn = '5件';
		}

		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '商品名称', 'value' => $goodtitle),
			array('name' => '预警数量', 'value' => $data['stockwarn'])
			);

		if (!(empty($is_send))) {
			$text = '您的' . $shopset['shop']['name'] . '内的商品：' . $goodtitle . '  ' . " " . '库存已经不足' . $data['stockwarn'] . '件，请及时补货！';
			$msg = '您的商品库存已经不足';

			if (!(empty($tm['openid3']))) {
				$mids = explode(',', $tm['openid3']);

				foreach ($mids as $tmomid ) {
					if (empty($tmomid)) {
						continue;
					}
					self::sendNotice(array('mid' => $tmomid, 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => 0));
				}
			}
		}
	}

	/**
	 * 发送小区通知公告
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  退回地址id
	 */
	public static function sendCommunityNotice($noticeid = '0')
	{
		if(empty($noticeid)) {
			return;
		}
		$notice = Db::name('community_notice')->where('id',$noticeid)->find();
		if(empty($notice)) {
			return;
		}
		$datas = array(
			array('name' => '公告标题', 'value' => $notice['title']),
			array('name' => '副标题', 'value' => $notice['subtitle']),
			array('name' => '创建时间', 'value' => $notice['createtime'])
			);
		$text = '小区公告[' . $notice['subtitle'] . ']    立即点击产看';
		$msg = '[天润智慧社区]' . $notice['title'];
		$members = Db::name('member')->where('isblack',0)->select();
		foreach ($members as $member) {
			if(!empty($member['regId'])) {
				self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'communitynotice', 'tid' => 0, 'id' => $notice['id']));
				continue;
			}
		}
		return;
	}

	/**
	 * 发送商城通知公告
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  退回地址id
	 */
	public static function sendShopNotice($noticeid = '0')
	{
		if(empty($noticeid)) {
			return;
		}
		$notice = Db::name('shop_notice')->where('id',$noticeid)->find();
		if(empty($notice)) {
			return;
		}
		$datas = array(
			array('name' => '公告标题', 'value' => $notice['title']),
			array('name' => '消息属性', 'value' => $notice['cate']),
			array('name' => '链接', 'value' => $notice['link']),
			array('name' => '创建时间', 'value' => $notice['createtime'])
			);
		$text = '小区公告[' . $notice['title'] . ']    立即点击产看';
		$msg = '[天润易购]' . $notice['title'];
		$members = Db::name('member')->where('isblack',0)->select();
		foreach ($members as $member) {
			if(!empty($member['regId'])) {
				self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'shopnotice', 'tid' => 0, 'id' => $notice['id']));
				continue;
			}
		}
		return;
	}

	/**
	 * 发送小区报修通知
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  退回地址id
	 */
	public static function sendCommunityRepair($repairid = '0')
	{
		if(empty($repairid)) {
			return;
		}
		$repair = Db::name('community_apply_repair')->where('id',$repairid)->find();
		if(empty($repair)) {
			return;
		}
		$datas = array(
			array('name' => '保修内容', 'value' => $repair['description']),
			array('name' => '预约时间', 'value' => $repair['bookingtime']),
			array('name' => '联系电话', 'value' => $repair['mobile']),
			array('name' => '报修时间', 'value' => $repair['createtime'])
			);
		if($repair['status'] == 1) {
			$text = '申请维修[' . $repair['description'] . ']  已成功提交预约，请耐心等待。 立即点击产看';
			$msg = '[天润智慧社区]，您已成功提交预约保修申请！！！';
		} elseif ($repair['status'] == 2) {
			$text = '申请维修[' . $repair['description'] . ']  正在处理中，请耐心等待。 立即点击产看';
			$msg = '[天润智慧社区]，您的预约报修正在处理！！！';
		} elseif ($repair['status'] == 2) {
			$text = '申请维修[' . $repair['description'] . ']  已处理完成。 立即点击产看';
			$msg = '[天润智慧社区]，您的预约报修已处理！！！';
		} else {
			if($repair['status'] == -1) {
				$text = '申请维修[' . $repair['description'] . ']  已关闭。 立即点击产看';
			$msg = '[天润智慧社区]，您的预约报修已关闭。！！！';
			}
		}
		
		$member = Db::name('member')->where('id',$repair['mid'])->find();
		if(!empty($member['regId'])) {
			self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'repair', 'tid' => 0, 'id' => $repair['id']));
		}
		return;
	}

	/**
	 * 发送小区缴费通知
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  退回地址id
	 */
	public static function sendCommunityPayment($paymentid = '0')
	{
		if(empty($paymentid)) {
			return;
		}
		$payment = Db::name('community_apply_payment')->where('id',$paymentid)->find();
		if(empty($payment)) {
			return;
		}
		$datas = array(
			array('name' => '缴费金额', 'value' => $payment['price']),
			array('name' => '手续费', 'value' => $payment['poundage']),
			array('name' => '业务编号', 'value' => $payment['applysn']),
			array('name' => '申请时间', 'value' => $payment['createtime'])
			);
		switch ($payment['type']) {
			case 'water':
				$type = '水费';
				break;
			
			case 'electricity':
				$type = '电费';
				break;
			
			case 'property':
				$type = '物业费';
				break;
			
			default:
				$type = '水费';
				break;
		}
		$datas[] = array('name'=>'缴费类型','value'=>$type);
		if($payment['status'] == 1) {
			$text = '申请缴费[' . $type . ']  已成功付款。 立即点击产看';
			$msg = '[天润智慧社区]，您已成功提交缴费申请！！！';
		} elseif ($payment['status'] == 2) {
			$text = '申请缴费[' . $type . ']  已处理完成。 立即点击产看';
			$msg = '[天润智慧社区]，您的缴费申请已处理！！！';
		} else {
			if($payment['status'] == -1) {
				$text = '申请缴费[' . $type . ']  已关闭。 立即点击产看';
				$msg = '[天润智慧社区]，您的缴费申请已关闭！！！';
			}
		}
		
		$member = Db::name('member')->where('id',$payment['mid'])->find();
		if(!empty($member['regId'])) {
			self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => $payment['type'], 'tid' => $payment['applysn'], 'id' => $payment['id']));
		}
		return;
	}

	/**
	 * 发送商户入住申请通知
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  
	 */
	public static function sendMerchReg($regid = '0')
	{
		if(empty($regid)) {
			return;
		}
		$item = Db::name('shop_store_reg')->where('id',$regid)->find();
		if(empty($item)) {
			return;
		}
		$datas = array(
			array('name' => '店铺名称', 'value' => $item['merchname']),
			array('name' => '主营项目', 'value' => $item['salecate']),
			array('name' => '真实姓名', 'value' => $item['mobile']),
			array('name' => '联系手机', 'value' => $item['realname']),
			array('name' => '申请时间', 'value' => $item['applytime'])
			);
		if ($item['status'] == 1) {
			$text = '您申请入驻的店铺[' . $item['merchname'] . '] 状态已更新，点击查看详情';
			$msg = '[天润易购]' . $item['merchname'] . '已通过审核';
		} else if ($item['status'] == -1) {
			$text = '您申请入驻的店铺[' . $item['merchname'] . '] 审核未通过,驳回理由: ' . $item['reason'];
			$msg = '[天润易购]' . $item['merchname'] . '审核未通过';
		}
		
		$member = Db::name('member')->where('id',$item['mid'])->find();
		if(!empty($member['regId'])) {
			self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => $item['id']));
		}
		return;
	}

	/**
	 * 发送商户店铺通知
	 * @param type $message_type
	 * @param type $order
	 * @param type $raid  
	 */
	public static function sendMerchMessage($storeid = '0')
	{
		return;
		if(empty($storeid)) {
			return;
		}
		$item = Db::name('shop_store')->where('id',$storeid)->find();
		if(empty($item)) {
			return;
		}
		$datas = array(
			array('name' => '店铺名称', 'value' => $item['merchname']),
			array('name' => '主营项目', 'value' => $item['salecate']),
			array('name' => '真实姓名', 'value' => $item['mobile']),
			array('name' => '联系手机', 'value' => $item['realname']),
			array('name' => '申请时间', 'value' => $item['applytime'])
			);
		if ($item['status'] == 1) {
			$text = '您的店铺[' . $item['merchname'] . '] 状态已更新，点击查看详情,到期时间: ' . date('Y-m-d', $item['accounttime']) . ' -> ' . date('Y-m-d', $item['accounttime']);
			$msg = '[天润易购]' . $item['merchname'] . '允许入驻';
		} else if ($item['status'] == 2) {
			$text = '您申请入驻的店铺[' . $item['merchname'] . '] 暂停中,到期时间: ' . date('Y-m-d', $item['accounttime']) . ' -> ' . date('Y-m-d', $item['accounttime']);
			$msg = '[天润易购]' . $item['merchname'] . '暂停中';
		}
		
		$member = Db::name('member')->where('id',$item['mid'])->find();
		if(!empty($member['regId'])) {
			self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => $item['id']));
		}
		return;
	}

	/**
	 * 会员升级提醒
	 * @param type $mid
	 * @param type $oldlevel
	 * @param type $level
	 * @return type
	 */
	public static function sendMemberUpgradeMessage($mid = 0, $oldlevel = NULL, $level = NULL)
	{
		$member = model('member')->getMember($mid);

		if (!($level)) {
			$level = model('member')->getLevel($mid);
		}
		$shopset = model('common')->getSysset();
		$oldlevelname = ((empty($oldlevel['levelname']) ? '普通会员' : $oldlevel['levelname']));
		$msg = '亲爱的' . $member['nickname'] . '，恭喜您成功升级！';
		$text = '您会员等级从 ' . $oldlevelname . ' 升级为 ' . $level['levelname'] . ', 特此通知!' . '您即可享有' . $level['levelname'] . '的专属优惠及服务！';

		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '旧等级', 'value' => $oldlevelname),
			array('name' => '新等级', 'value' => $level['levelname'])
			);
		self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => 0));
	}

	/**
	 * 积分商城提醒
	 * @param type $mid
	 * @param type $oldlevel
	 * @param type $level
	 * @return type
	 */
	public static function sendCreditshopMessage($log_id = "") 
	{
		if( empty($id) ) {
			return NULL;
		}
		$log = Db::name('shop_creditshop_log')->where('id',$id)->find();
		if( empty($log) ) 
		{
			return NULL;
		}
		$member = model("member")->getMember($log["mid"]);
		if( empty($member) ) 
		{
			return NULL;
		}
		$credit = intval($member["credit1"]);
		$goods = model('creditshop')->getGoods($log["goodsid"], $member);
		if( empty($goods["id"]) ) 
		{
			return NULL;
		}
		if( 0 < $log["optionid"] ) 
		{
			$goods_option = Db::name('shop_creditshop_goods_option')->where('id',$log['optionid'])->field('credit,money')->find();
			$goods["credit"] = $goods_option["credit"];
			$goods["money"] = $goods_option["money"];
		}
		$goods["credit"] *= $log["goods_num"];
		$goods["money"] *= $log["goods_num"];
		$type = $goods["type"];
		$credits = "";
		if( 0 < $goods["credit"] & 0 < $goods["money"] ) 
		{
			$credits = $goods["credit"] . "积分+" . $goods["money"] . "元";
		}
		else 
		{
			if( 0 < $goods["credit"] ) 
			{
				$credits = $goods["credit"] . "积分";
			}
			else 
			{
				if( 0 < $goods["money"] ) 
				{
					$credits = $goods["money"] . "元";
				}
				else 
				{
					$credits = "0";
				}
			}
		}
		$shopset = model('common')->getSysset();
		$shop = $shopset['shop'];
		$set = model('Common')->getPluginset('creditshop');
		if( $log["status"] == 2 ) 
		{
			if( !empty($type) ) 
			{
				if( $log["status"] == 2 ) 
				{
					$remark = " 【" . $shop["name"] . "】期待您再次光顾！";
					if( $goods["goodstype"] == 0 && $goods["isverify"] == 0 ) 
					{
						if( 0 < $goods["dispatch"] ) 
						{
							$remark = " 请您支付邮费后, 我们会尽快发货，【" . $shop["name"] . "】期待您再次光顾！";
						}
						else 
						{
							$remark = " 请您选择邮寄地址后, 我们会尽快发货，【" . $shop["name"] . "】期待您再次光顾！";
						}
					}
					$msg = "恭喜您，您中奖啦~";
				}
			}
			else 
			{
				if( $log["dispatchstatus"] != 1 ) 
				{
					$remark = " 【" . $shop["name"] . "】期待您再次光顾！";
					if( $log["dispatchstatus"] != -1 ) 
					{
						if( 0 < $goods["dispatch"] ) 
						{
							$remark = " 请您支付邮费后, 我们会尽快发货，【" . $shop["name"] . "】期待您再次光顾！";
						}
						else 
						{
							$remark = " 请您选择邮寄地址后, 我们会尽快发货，【" . $shop["name"] . "】期待您再次光顾！";
						}
					}
					$msg = "恭喜您，商品兑换成功~";
				}
			}
			if( $log["dispatchstatus"] == 1 || $log["dispatchstatus"] == -1 ) 
			{
				$remark = "收货信息:  无需物流";
				if( !empty($log["addressid"]) ) 
				{
					$address = Db::name('shop_member_address')->where('id',$log['addressid'])->find();
					if( !empty($address) ) 
					{
						$remark = "收件人: " . $address["realname"] . " 联系电话: " . $address["mobile"] . " 收货地址: " . $address["province"] . $address["city"] . $address["area"] . " " . $address["address"];
					}
					$remark = " 【" . $shop["name"] . "】期待您再次光顾！";
				}
				$msg = "积分商城商品兑换成功~";
			}
		}
		else 
		{
			if( $log["status"] == 3 ) 
			{
				$remark = "无需物流";
				if( !empty($log["addressid"]) ) 
				{
					$address = Db::name('shop_member_address')->where('id',$log['addressid'])->find();
					if( !empty($address) ) 
					{
						$remark = " 收件人: " . $address["realname"] . " 联系电话: " . $address["mobile"] . " 收货地址: " . $address["province"] . $address["city"] . $address["area"] . " " . $address["address"];
					}
				}
				$msg = "您的积分兑换奖品已发货~";
			}
		}
		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '商品名称', 'value' => $goods['title']),
			);
		self::sendNotice(array('mid' => $member['id'], 'first' => $msg, 'default' => $remark, 'datas' => $datas, 'type' => 'creditshop', 'tid' => $log['logno'], 'id' => $log['id']));
	}

	/**
	 * 会员积分余额变动提醒
	 * @param type $mid
	 * @param type $oldlevel
	 * @param type $level
	 * @return type
	 */
	public static function sendMemberLogMessage($log_id = "", $channel = 0, $isback = false) 
	{
		$log_info = Db::name('member_credits_record')->where('id',$log_id)->find();
		$member = model('member')->getMember($log_info['mid']);
		$shopset = model('common')->getSysset();
		$credittext = ((empty($shopset['trade']['credittext']) ? '积分' : $shopset['trade']['credittext']));
		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '余额变动', 'value' => $log_info['num']),
			array('name' => '赠送时间', 'value' => date('Y-m-d H:i', time())),
			array('name' => '余额', 'value' => (double) $member['credit2'])
			);
		if (empty($from)) {
			$fromstr = '管理员后台手动处理';
		} else if ($from == 1) {
			$fromstr = '收银台积分变动提醒';
		}
		$msg = '亲爱的' . $member['nickname'] . '，您的' . $credittext . '发生变动';
		$remark = " " . '[' . $shopset['shop']['name'] . ']感谢您的支持，如有疑问请联系在线客服。';
		$text = '亲爱的[' . $member['nickname'] . ']， 您的' . $credittext . '发生变动，具体内容如下：' . " " . '变动：[' . $log_info['num'] . ']' . "" . '变动时间：[' . date('Y-m-d H:i', time()) . ']' . "" . '充值方式：' . $fromstr . "" . '当前余额：[余额] ' . "" . $remark;
		
		self::sendNotice(array('mid' => $log_info['mid'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => 0));
	}

	public static function sendNotice(array $params)
	{
		$touser = ((isset($params['mid']) ? $params['mid'] : ''));

		if (empty($touser)) {
			return;
		}
		$user = model('member')->getMember($touser);
		if(empty($user['regId'])) {
			return;
		}

		$title = ((isset($params['first']) ? $params['first'] : ''));

		if (empty($title)) {
			return;
		}

		$msg = ((isset($params['default']) ? $params['default'] : ''));

		if (empty($msg)) {
			return;
		}

		switch ($params['type']) {
			case 'order':
				$messagethumb = '/public/static/images/message/order.png';
				$messagetype = array('messageType'=>'order','link'=>"order?id=".$params['id']);
				break;

			case 'groups':
				$messagethumb = '/public/static/images/message/teams.png';
				$messagetype = array('messageType'=>'groups','link'=>"groups?id=".$params['id']);
				break;

			case 'auction':
				$messagethumb = '/public/static/images/message/auction.png';
				$messagetype = array('messageType'=>'auction','link'=>"auction?id=".$params['id']);
				break;

			case 'notice':
				$messagethumb = '/public/static/images/message/notice.png';
				$messagetype = array('messageType'=>'notice','link'=>"notice?id=".$params['id']);
				break;

			case 'electricity':
				$messagethumb = '/public/static/images/message/electricity.png';
				$messagetype = array('messageType'=>'electricity','link'=>"electricity?id=".$params['id']);
				break;

			case 'property':
				$messagethumb = '/public/static/images/message/property.png';
				$messagetype = array('messageType'=>'property','link'=>"property?id=".$params['id']);
				break;

			case 'repair':
				$messagethumb = '/public/static/images/message/repair.png';
				$messagetype = array('messageType'=>'repair','link'=>"repair?id=".$params['id']);
				break;

			case 'treasure':
				$messagethumb = '/public/static/images/message/treasure.png';
				$messagetype = array('messageType'=>'treasure','link'=>"treasure?id=".$params['id']);
				break;

			case 'water':
				$messagethumb = '/public/static/images/message/water.png';
				$messagetype = array('messageType'=>'water','link'=>"water?id=".$params['id']);
				break;

			case 'communitynotice':
				$messagethumb = '/public/static/images/message/notice.png';
				$messagetype = array('messageType'=>'communitynotice','link'=>"communitynotice?id=".$params['id']);
				break;

			case 'shopnotice':
				$messagethumb = '/public/static/images/message/notice.png';
				$messagetype = array('messageType'=>'shopnotice','link'=>"shopnotice?id=".$params['id']);
				break;

			case 'secondgoods':
				$messagethumb = '/public/static/images/message/secondgoods.png';
				$messagetype = array('messageType'=>'secondgoods','link'=>"secondgoods?id=".$params['id']);
				break;

			case 'system':
				$messagethumb = '/public/static/images/message/system.png';
				$messagetype = array('messageType'=>'system','link'=>"system?id=".$params['id']);
				break;
			
			default:
				$messagethumb = '/public/static/images/message/order.png';
				$messagetype = array('messageType'=>'order','link'=>"order?id=".$params['id']);
				break;
		}

		$message = array('mid'=>$touser, 'title'=>$title, 'remark'=>$msg, 'datas'=>iserializer($params['datas']), 'messagethumb'=>$messagethumb, 'messagetype'=>$params['type'], 'messagetid'=>$params['tid'], 'businessid' => $params['id'], 'createtime'=>time(), 'sendtime'=>time(),'sendcount'=>1);

		Db::name('member_message')->insert($message);
		self::JPush($user['regId'],$title,$msg,$messagetype);        
		self::Alipush($user['mobile'],$title,$msg);        
	}

}