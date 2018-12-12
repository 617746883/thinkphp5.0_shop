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
class Alipay extends Controller
{
	public function notify()
	{
		vendor('alipay.aop.AopClient');
        vendor('alipay.aop.request.AlipayTradeAppPayRequest');
        $data = $_POST;

        $set = model('common')->getSec();
        $sec = iunserializer($set['sec']);
        $config = array('appId'=>$sec['app_alipay']['appid'], 'alipayrsaPublicKey'=>$sec['app_alipay']['public_key'], 'rsaPrivateKey'=>$sec['app_alipay']['private_key']);

        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
        $flag = $aop->rsaCheckV1($data, NULL, "RSA2");
        if($flag){
            // file_put_contents($file, "Hello World!!!\n",FILE_APPEND);
            //验证成功
            //这里可以做一下你自己的订单逻辑处理  
            $type = $data['body'];
        	$total_fee = round($data['total_amount'], 2);
        	$tid = $data['out_trade_no'];
        	$transaction_id = $data['trade_no'];
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
			} else if ($type == '123') {
				$this->community($tid,$total_fee,$transaction_id);
			}
            echo 'success';//这个必须返回给支付宝，响应给支付宝，            
        } else {
            //验证失败
            // file_put_contents($file, "Hello World---\n",FILE_APPEND);
            echo "fail";
        }
	}

	protected function order($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('shop_order')->where('ordersn',$tid)->find();
		$log = Db::name('shop_core_paylog')->where('module','shop')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			Db::name('shop_order')->where('ordersn',$log['tid'])->update(array('paytype' => 2, 'apppay' => 1, 'transid' => $transaction_id));
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
				$record['type'] = 'alipay';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
			}
		} else {
			echo "fail";
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

	protected function community($tid = '', $total_fee = 0, $transaction_id = '')
	{
		$order = Db::name('community_apply_payment')->where('applysn',$tid)->find();
		$log = Db::name('shop_core_paylog')->where('module','community')->where('tid',$tid)->find();
		if (!empty($log) && ($log['status'] == '0') && ($log['fee'] == $total_fee)) {
			$result = Db::name('community_apply_payment')->where('applysn',$log['tid'])->update(array('paytype' => 2, 'transid' => $transaction_id, 'status' => 1, 'paytime' => time()));
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
				$record['type'] = 'alipay';
				$record['tag'] = iserializer($log['tag']);
				$record['createtime'] = time();
				Db::name('shop_core_paylog')->where('plid',$log['plid'])->update($record);
				model('notice')->sendCommunityPayment($order['id']);
			}
		} else {
			echo "fail";
		}
	}

}