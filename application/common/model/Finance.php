<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Finance extends \think\Model
{
	/**
     * 下载对账单
     * @param type $type ALL，返回当日所有订单信息，默认值 SUCCESS，返回当日成功支付的订单 REFUND，返回当日退款订单 REVOKED，已撤销的订单
     * @param type $money
     */
	public function downloadbill($starttime, $endtime, $type = 'ALL', $datatype = 0)
	{
		$dates = array();
		$startdate = date('Ymd', $starttime);
		$enddate = date('Ymd', $endtime);

		if ($startdate == $enddate) {
			$dates = array($startdate);
		} else {
			$days = (double) ($endtime - $starttime) / 86400;
			$d = 0;

			while ($d < $days) {
				$dates[] = date('Ymd', strtotime($startdate . '+' . $d . ' day'));
				++$d;
			}
		}

		if (empty($dates)) {
			return errormsg(-1, '对账单日期选择错误');
		}

		list($pay, $payment) = m('common')->public_build();

		if ($payment['is_new'] == 0) {
			$setting = uni_setting($_W['uniacid'], array('payment'));

			if (!is_array($setting['payment'])) {
				return error(1, '没有设定支付参数');
			}

			if (!empty($pay['weixin_sub'])) {
				$wechat = array('appid' => $payment['appid_sub'], 'mchid' => $payment['mchid_sub'], 'sub_appid' => !empty($payment['sub_appid_sub']) ? $payment['sub_appid_sub'] : '', 'sub_mch_id' => $payment['sub_mchid_sub'], 'apikey' => $payment['apikey_sub']);
			}
			else {
				$wechat = $setting['payment']['wechat'];
			}

			$sql = 'SELECT `key`,`secret` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
			$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
			$wechat['appid'] = $row['key'];
		} else {
			$wechat = array('appid' => $payment['sub_appid'], 'mchid' => $payment['sub_mch_id'], 'apikey' => $payment['apikey']);
			$sub_wechat = array('appid' => $payment['appid'], 'mchid' => $payment['mch_id'], 'sub_appid' => !empty($payment['sub_appid']) ? $payment['sub_appid'] : '', 'sub_mch_id' => $payment['sub_mch_id'], 'apikey' => $payment['apikey']);

			switch ($payment['type']) {
			case '1':
				$wechat = $sub_wechat;
				break;

			case '3':
				$wechat = $sub_wechat;
				break;

			case '4':
				return errormsg(1, '暂不支持全付通的账单下载');
			}
		}

		$content = '';

		foreach ($dates as $date) {
			$dc = $this->downloadday($date, $wechat, $type);
			if (is_error($dc) || strexists($dc, 'CDATA[FAIL]')) {
				continue;
			}

			if ($datatype && !strexists($dc, 'suliss')) {
				continue;
			}

			$content .= $date . " 账单\r\n\r\n";
			$content .= $dc . "\r\n\r\n";
		}

		if (empty($content)) {
			return errormsg(-1, '账单为空');
		}

		$content = "\xef\xbb\xbf" . $content;
		$file = time() . '.csv';
		header('Content-type: application/octet-stream ');
		header('Accept-Ranges: bytes ');
		header('Content-Disposition: attachment; filename=' . $file);
		header('Expires: 0 ');
		header('Content-Encoding: UTF8');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0 ');
		header('Pragma: public ');
		exit($content);
	}
}