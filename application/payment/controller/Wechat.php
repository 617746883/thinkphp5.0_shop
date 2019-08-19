<?php
/**
 * 微信支付回调
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\payment\controller;
use think\Controller;
use think\Db;
use think\Request;
use think\Log;
class Wechat extends Controller
{
	public function notify()
	{
		vendor('wechatpay.WechatPay');
		$set = model('common')->getSec();
        $sec = iunserializer($set['sec']);
		$config = array('APPID'=>$sec['app_wechat']['appid'], 'MCHID'=>$sec['app_wechat']['merchid'], 'KEY'=>$sec['app_wechat']['apikey'], 'APPSECRET'=>$sec['app_wechat']['appsecret'], 'MERCHNAME'=>$sec['app_wechat']['merchname'], 'NOTIFY_URL'=>getHttpHost() . '/payment/wechat/notify.php');

		$weixinpay=new \WechatPay();
        $weixinpay->config = $config;
        $result = $weixinpay->notify();
        if($result) {
        	$type = $result['attach'];
        	$total_fee = round($result['total_fee'] / 100, 2);
        	$tid = $result['out_trade_no'];
        	$transaction_id = $result['transaction_id'];
        	if ($type == '0') {
				$this->order($tid,$total_fee,$transaction_id);
			} else if ($type == '1') {
				$this->groups($tid,$total_fee,$transaction_id);
			} else if ($type == '2') {
				$this->creditShop($tid,$total_fee,$transaction_id);
			} else if ($type == '3') {
				$this->auction($tid,$total_fee,$transaction_id);
			} else if ($type == '4') {
				$this->seckill($tid,$total_fee,$transaction_id);
			} else if ($type == '4') {
				$this->auctionbond($tid,$total_fee,$transaction_id);
			} else if ($type == '123') {
				$this->community($tid,$total_fee,$transaction_id);
			}   
			$this->tosuccess();            
        } else {
			$this->tofail();
		}
	}

	protected function tosuccess()
	{
		$result = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
		echo array2xml($result);
		exit();
	}

	protected function tofail()
	{
		$result = array('return_code' => 'FAIL');
		echo array2xml($result);
		exit();
	}

	protected function order($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('shop_order')->where('ordersn',$tid)->find();

		$log = Db::name('shop_core_paylog')->where('module','shop')->where('tid',$tid)->find();

		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_order')->where('ordersn',$log['tid'])->update(array('paytype' => 1, 'apppay' => 1, 'transid' => $transaction_id));
			$ret = array();
			$ret['result'] = 'success';
			$ret['type'] = $log['type'];
			$ret['from'] = 'return';
			$ret['tid'] = $log['tid'];
			$ret['user'] = $log['mid'];
			$ret['fee'] = $log['fee'];
			$ret['tag'] = $log['tag'];
			$result = model('order')->payResult($ret);

			if ($result) {
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $transaction_id;
				$record = array();
				$record['status'] = '1';
				$record['type'] = 'wechat';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
			}
		} else {
			$this->tofail();
		}
	}

	protected function groups($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$log = Db::name('shop_core_paylog')->where('module','groups')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_groups_order')->where('orderno',$log['tid'])->update(array('paytype' => 1, 'transid' => $transaction_id));
			$result = model('groups')->payResult($tid,'wechat','app');
			if ($result) {
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $transaction_id;
				$record = array();
				$record['status'] = '1';
				$record['type'] = 'wechat';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
			}
		} else {
			$this->tofail();
		}
	}

	protected function creditShop($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('shop_creditshop_log')->where('logno',$tid)->find();

		$log = Db::name('shop_core_paylog')->where('module','creditshop')->where('tid',$tid)->find();

		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_creditshop_log')->where('logno',$log['tid'])->update(array('paytype' => 1, 'transid' => $transaction_id));
			$logno = trim($tid);
			if( empty($logno) ) {
				$this->tofail();
			}
			$logno = str_replace("_borrow", "", $logno);
			$result = model("creditshop")->payResult($logno, "wechat", $total_fee, $transaction_id);

			if ($result) {
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $transaction_id;
				$record = array();
				$record['status'] = '1';
				$record['type'] = 'wechat';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
			}
		} else {
			$this->tofail();
		}	
	}

	protected function auction($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('shop_auction_order')->where('ordersn',$tid)->find();
		$log = Db::name('shop_core_paylog')->where('module','auction')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_auction_order')->where('ordersn',$log['tid'])->update(array('paytype' => 2, 'paystatus' => 1, 'transid' => $transaction_id, 'status'=>1, 'paytime'=>time()));
			$log['tag'] = iunserializer($log['tag']);
			$log['tag']['transaction_id'] = $transaction_id;
			$record = array();
			$record['status'] = '1';
			$record['type'] = 'alipay';
			$record['tag'] = iserializer($log['tag']);
			$record['createtime'] = time();
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
		} else {
			$this->tofail();
		}
	}

	protected function community($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('community_apply_payment')->where('applysn',$tid)->find();
		$log = Db::name('shop_core_paylog')->where('module','community')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			$result = Db::name('community_apply_payment')->where('applysn',$log['tid'])->update(array('paytype' => 1, 'transid' => $transaction_id, 'status' => 1, 'paytime' => time()));
			$orderids = array_unique(array_filter(explode(",", $order['orderids'])));
			foreach ($orderids as $val) {
				if($order['type'] == 'water') {
					Db::name('community_house_water_order')->where('id',$val)->update(array('status'=>1,'paymenttime'=>time()));
				} elseif($order['type'] == 'electricity') {
					Db::name('community_house_electricity_order')->where('id',$val)->update(array('status'=>1,'paymenttime'=>time()));
				} else {
					if($order['type'] == 'property') {
						Db::name('community_house_property_order')->where('id',$val)->update(array('status'=>1,'paymenttime'=>time()));
					}
				}
			}
			if ($result) {
				$log['tag'] = iunserializer($log['tag']);
				$log['tag']['transaction_id'] = $transaction_id;
				$record = array();
				$record['status'] = '1';
				$record['type'] = 'wechat';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
				model('notice')->sendCommunityPayment($order['id']);
			}
		} else {
			$this->tofail();
		}
	}

	protected function auctionbond($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$log = Db::name('shop_core_paylog')->where('module','auction_bond')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_auction_bondorder')->where('ordersn',$log['tid'])->update(array('paytype' => 1, 'transid' => $transaction_id,'status'=>1,'paytime'=>time()));
			$log['tag'] = iunserializer($log['tag']);
			$log['tag']['transaction_id'] = $transaction_id;
			$record = array();
			$record['status'] = '1';
			$record['type'] = 'wechat';
			$record['tag'] = iserializer($log['tag']);
			$record['createtime'] = time();
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
		} else {
			$this->tofail();
		}
	}

}