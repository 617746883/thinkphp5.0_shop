<?php
namespace app\apiv1\model;
use think\Db;
class Groups extends \think\Model
{
	/**
	 * 商品购买权限检查
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function goodsCheck($goodsid, $type = '')
	{
		try{
			$goods = Db::name('shop_goods')->where('id',$id)->where('status',1)->where('isgroups',1)->where('deleted',0)->find();
		}catch(\Exception $e){
		    $this->result(0,'执行错误');
		}

		if (empty($goods)) {
			$this->result(0,'商品不存在!');
		}

		if ($type == 'single') {
			if (empty($goods['single'])) {
				$this->result(0,'商品不允许单购，请重新选择！');
			}
		}

		if (empty($goods['total']) || $goods['total'] <= 0) {
			$this->result(0,'商品库存为0，暂时无法购买，请浏览其他商品！');
		}
		$this->result(1,'success');
	}

	/**
	 * 支付成功
	 * @param $orderid [int]
	 * @return  [array]    $data  []
	 **/
	public function payResult($orderno, $type)
	{
		$log = Db::name('shop_groups_paylog')->where('module','groups')->where('tid',$orderno)->find();
		$order = Db::name('shop_groups_order')->where('orderno',$orderno)->find();

		if (0 < $order['status']) {
			return true;
		}

		$mid = $order['mid'];
		$order_goods = Db::name('shop_goods')->where('id',$order['goodid'])->find();
		$shopset = $this->shopset;
		$result = model('member')->setCredit($mid, 'credit1', 0 - $order['credit'], array($mid, $shopset['shop']['name'] . '消费' . $order['credit'] . '积分'));

		if (is_error($result)) {
			return $result['message'];
		}

		$record = array();
		$record['status'] = '1';
		$record['type'] = $type;
		Db::name('shop_groups_order')->where('orderno',$orderno)->update(array('pay_type' => $type, 'status' => 1, 'paytime' => time(), 'starttime' => time(), 'apppay' => 1));
		self::sendTeamMessage($order['id']);

		if (!empty($order['is_team'])) {
			$total = Db::name('shop_groups_order')->where('status',1)->where('teamid',$order['teamid'])->where('success',0)->count();

			if ($order['groupnum'] == $total) {
				Db::name('shop_groups_order')->where('teamid',$order['teamid'])->where('status',1)->setField('success',1);
				Db::name('shop_groups_order')->where('teamid',$order['teamid'])->where('status',0)->update(array('success' => -1, 'status' => -1, 'canceltime' => time()));
				self::sendTeamMessage($order['id']);
			}
		}

		$total = intval($order_goods['total'] - 1);
		$sales = intval($order_goods['sales']) + 1;
		$teamnum = intval($order_goods['teamnum']) + 1;
		Db::name('shop_goods')->where('id',$order_goods['id'])->update(array('total' => $total, 'sales' => $sales, 'teamnum' => $teamnum));
		return true;
	}

	/**
	 * 拼团发送订单通知
	 * @param type $message_type
	 * @param type $order
	 */
	public function sendTeamMessage($orderid = '0', $delRefund = false)
	{
		$orderid = intval($orderid);

		if (empty($orderid)) {
			return NULL;
		}

		$order = Db::name('shop_groups_order')->where('id',$orderid)->find();

		if (empty($order)) {
			return NULL;
		}

		$mid = $order['mid'];

		if (intval($order['teamid'])) {
			$url = url('groups/team/detail', array('orderid' => $orderid, 'teamid' => intval($order['teamid'])));
		}
		else {
			$url = url('groups/orders/detail', array('orderid' => $orderid));
		}

		$order_goods = Db::name('shop_goods')->where('id',$order['goodid'])->find();
		$goodsprice = (!empty($order['is_team']) ? number_format($order_goods['groupsprice'], 2) : number_format($order_goods['singleprice'], 2));
		$price = number_format(($order['price'] - $order['creditmoney']) + $order['freight'], 2);
		$goods = '待发货商品--' . $order_goods['title'];
		$goods2 = $order_goods['title'];
		$orderpricestr = ' ¥' . $price . '元 (包含运费: ¥' . $order['freight'] . '元，积分抵扣: ¥' . $order['creditmoney'] . '元)';
		$member = m('member')->getMember($mid);
		$datas = array(
			array('name' => '商城名称', 'value' => $shopset['shop']['name']),
			array('name' => '粉丝昵称', 'value' => $member['nickname']),
			array('name' => '订单号', 'value' => $order['orderno']),
			array('name' => '订单金额', 'value' => ($order['price'] - $order['creditmoney']) + $order['freight']),
			array('name' => '运费', 'value' => $order['freight']),
			array('name' => '商品详情', 'value' => $goods),
			array('name' => '快递公司', 'value' => $order['expresscom']),
			array('name' => '快递单号', 'value' => $order['expresssn']),
			array('name' => '下单时间', 'value' => date('Y-m-d H:i', $order['createtime'])),
			array('name' => '支付时间', 'value' => date('Y-m-d H:i', $order['paytime'])),
			array('name' => '发货时间', 'value' => date('Y-m-d H:i', $order['sendtime'])),
			array('name' => '收货时间', 'value' => date('Y-m-d H:i', $order['finishtime']))
			);
		$usernotice = unserialize($member['noticeset']);

		if (!is_array($usernotice)) {
			$usernotice = array();
		}

		$set = $set = model('common')->getSysset();
		$shop = $set['shop'];

		if ($delRefund == true) {
			$order_refund = Db::name('shop_groups_order_refund')->where('id',$order['refundid'])->find();
			$refundtype = '';

			if ($order['pay_type'] == 'credit') {
				$refundtype = ', 已经退回您的余额账户，请留意查收！';
			}
			else {
				if ($order['pay_type'] == 'wechat') {
					$refundtype = ', 已经退回您的对应支付渠道（如银行卡，微信钱包等, 具体到账时间请您查看微信支付通知)，请留意查收！';
				}
			}

			if ($order_refund['refundtype'] == 2) {
				$refundtype = ', 请联系客服进行退款事项！';
			}

			$applyprice = (!empty($order_refund['applyprice']) ? $order_refund['applyprice'] : ($order['price'] - $order['creditmoney']) + $order['freight']);

			if ($order_refund['refundstatus'] == 0) {
				$msgteam = array(
					'first'    => array('value' => '您有一条申请退款的订单！', 'color' => '#4a5077'),
					'keyword1' => array('title' => '企业名称', 'value' => $shop['name'], 'color' => '#4a5077'),
					'keyword2' => array('title' => '订单编号', 'value' => '订单编号：' . $order['orderno'] . ',维权编号：' . $order_refund['refundno'], 'color' => '#4a5077')
					);
			}
			else if ($order_refund['refundstatus'] == -1) {
				$msg = array(
					'first'    => array('value' => '您的退款订单已经被驳回', 'color' => '#4a5077'),
					'keyword1' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
					'keyword2' => array('title' => '维权编号', 'value' => $order_refund['refundno'], 'color' => '#4a5077'),
					'keyword3' => array('title' => '驳回原因', 'value' => $order_refund['reply'], 'color' => '#4a5077')
					);
				self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_refund', 'default' => $msg, 'datas' => $datas));
			}
			else {
				if ($order_refund['refundstatus'] == 1) {
					$msg = array(
						'first'    => array('value' => '您的订单已经完成退款！', 'color' => '#4a5077'),
						'keyword1' => array('title' => '退款金额', 'value' => '¥' . $applyprice . '元', 'color' => '#4a5077'),
						'keyword2' => array('title' => '商品详情', 'value' => $goods2, 'color' => '#4a5077'),
						'keyword3' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
						'remark'   => array('value' => '退款金额 ¥' . $applyprice . $refundtype . "\r\n 期待您再次购物！", 'color' => '#4a5077')
						);
					self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_refund', 'default' => $msg, 'datas' => $datas));
				}
			}
		}
		else if ($order['status'] == 1) {
			if ($order['success'] == 1) {
				$order = pdo_fetchall('select * from ' . tablename('ewei_shop_groups_order') . ' where teamid = :teamid and success = 1 and status = 1 ', array(':teamid' => $order['teamid']));
				$remark = '您参加的拼团已经成功，我们将尽快为您配送~~';

				foreach ($order as $key => $value) {
					$msg = array(
						'first'    => array('value' => '您参加的拼团已经成功组团！', 'color' => '#4a5077'),
						'keyword1' => array('title' => '订单编号', 'value' => $value['orderno'], 'color' => '#4a5077'),
						'keyword2' => array('title' => '通知时间', 'value' => date('Y-m-d H:i', time()), 'color' => '#4a5077'),
						'remark'   => array('value' => $remark, 'color' => '#4a5077')
						);
					self::sendGroupsNotice(array('mid' => $value['mid'], 'tag' => 'groups_success', 'default' => $msg, 'datas' => $datas));
				}

				$remarkteam = '拼团成功了，准备发货';
				$msgteam = array(
					'first'    => array('value' => '拼团已经成功组团！', 'color' => '#4a5077'),
					'keyword1' => array('title' => '企业名称', 'value' => $shop['name'], 'color' => '#4a5077'),
					'keyword2' => array('title' => '摘要', 'value' => $goods, 'color' => '#4a5077'),
					'remark'   => array('value' => $remarkteam, 'color' => '#4a5077')
					);
			}
			else if ($order['success'] == -1) {
				$order = pdo_fetchall('select * from ' . tablename('ewei_shop_groups_order') . ' where teamid = :teamid and success = -1 and status = 1 ', array(':teamid' => $order['teamid']));
				$remark = '很抱歉，您所在的拼团未能成功组团，系统会在24小时之内自动退款。如有疑问请联系卖家，谢谢您的参与！';

				foreach ($order as $key => $value) {
					$msg = array(
						'first'    => array('value' => '您参加的拼团组团失败！', 'color' => '#4a5077'),
						'keyword1' => array('title' => '订单编号', 'value' => $value['orderno'], 'color' => '#4a5077'),
						'keyword2' => array('title' => '通知时间', 'value' => date('Y-m-d H:i:s', time()), 'color' => '#4a5077'),
						'remark'   => array('value' => $remark, 'color' => '#4a5077')
						);
					self::sendGroupsNotice(array('mid' => $value['mid'], 'tag' => 'groups_error', 'default' => $msg, 'datas' => $datas));
				}
			}
			else {
				if ($order['success'] == 0) {
					if (!empty($order['addressid'])) {
						if ($order['is_team']) {
							$remark = "\r\n您的订单我们已经收到，请耐心等待其他团员付款~~";
						}
						else {
							$remark = "\r\n您的订单我们已经收到，我们将尽快配送~~";
						}
					}

					$msg = array(
						'first'    => array('value' => '您的订单已提交成功！', 'color' => '#4a5077'),
						'keyword1' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
						'keyword2' => array('title' => '消费金额', 'value' => $orderpricestr, 'color' => '#4a5077'),
						'keyword3' => array('title' => '消费门店', 'value' => $shop['name'], 'color' => '#4a5077'),
						'keyword4' => array('title' => '消费时间', 'value' => date('Y-m-d H:i:s', $order['createtime']), 'color' => '#4a5077'),
						'remark'   => array('value' => $remark, 'color' => '#4a5077')
						);
					self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_pay', 'default' => $msg, 'url' => $url, 'datas' => $datas));

					if (!$order['is_team']) {
						$remarkteam = '单购订单成功了，准备发货';
						$msgteam = array(
							'first'    => array('value' => '单购订单成功了！', 'color' => '#4a5077'),
							'keyword1' => array('title' => '企业名称', 'value' => $shop['name'], 'color' => '#4a5077'),
							'keyword2' => array('title' => '摘要', 'value' => $goods, 'color' => '#4a5077'),
							'remark'   => array('value' => $remarkteam, 'color' => '#4a5077')
							);
					}
				}
			}
		}
		else if ($order['status'] == 2) {
			if (!empty($order['addressid'])) {
				$remark = '您的订单已发货，请注意查收！';
			}

			$msg = array(
				'first'    => array('value' => '您的订单已发货！', 'color' => '#4a5077'),
				'keyword1' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
				'keyword2' => array('title' => '物流公司', 'value' => $order['expresscom'], 'color' => '#4a5077'),
				'keyword3' => array('title' => '物流单号', 'value' => $order['expresssn'], 'color' => '#4a5077'),
				'remark'   => array('value' => $remark, 'color' => '#4a5077')
				);
			self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_send', 'default' => $msg, 'datas' => $datas));
		}
		else if ($order['status'] == 3) {
			if (!empty($order['addressid'])) {
				$remark = '您的订单已收货成功！';
			}

			$msg = array(
				'first'    => array('value' => '订单已收货！', 'color' => '#4a5077'),
				'keyword1' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
				'keyword2' => array('title' => '物流公司', 'value' => $order['expresscom'], 'color' => '#4a5077'),
				'keyword3' => array('title' => '物流单号', 'value' => $order['expresssn'], 'color' => '#4a5077'),
				'remark'   => array('value' => $remark, 'color' => '#4a5077')
				);
			self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_send', 'default' => $msg, 'datas' => $datas));
		}
		else {
			if ($order['status'] == -1) {
				if (!empty($order['addressid'])) {
					$remark = '您的订单已取消！';
				}

				$msg = array(
					'first'    => array('value' => '订单已取消！', 'color' => '#4a5077'),
					'keyword1' => array('title' => '订单编号', 'value' => $order['orderno'], 'color' => '#4a5077'),
					'keyword2' => array('title' => '通知时间', 'value' => date('Y-m-d H:i:s', time()), 'color' => '#4a5077'),
					'remark'   => array('value' => $remark, 'color' => '#4a5077')
					);
				self::sendGroupsNotice(array('mid' => $mid, 'tag' => 'groups_error', 'default' => $msg, 'datas' => $datas));
			}
		}
	}

	public function sendGroupsNotice(array $params)
	{
		$tag = (isset($params['tag']) ? $params['tag'] : '');
		$touser = (isset($params['mid']) ? $params['mid'] : '');

		if (empty($touser)) {
			return NULL;
		}

		$default_message = (isset($params['default']) ? $params['default'] : array());
		$url = (isset($params['url']) ? $params['url'] : '');
		$account = (isset($params['account']) ? $params['account'] : m('common')->getAccount());
		$datas = (isset($params['datas']) ? $params['datas'] : array());
		$advanced_message = false;

		$ret = model('message')->sendTplNotice($touser, $templateid, $default_message, $url, $account);

		if (is_error($ret)) {
			model('message')->sendCustomNotice($touser, $default_message, $url, $account);
		}
	}

}