<?php
namespace app\apiv1\model;
use think\Db;
class Store extends \think\Model
{
	public function getListUserOne($merchid)
	{
		$merchid = intval($merchid);

		if ($merchid) {
			$merch = Db::name('shop_store')->where('id',$merchid)->find();
			return $merch;
		}
		return false;
	}

	public function getListUser($list, $return = 'all')
	{
		if (!(is_array($list))) {
			return self::getListUserOne($list);
		}
		$shopset = model('common')->getSysset();
		$merch = array();

		foreach ($list as $value) {
			$merchid = $value['merchid'];

			if (empty($merchid)) {
				$merchid = 0;
			}

			if (empty($merch[$merchid])) {
				$merch[$merchid] = array();
			}

			array_push($merch[$merchid], $value);
		}

		if (!(empty($merch))) {
			$merch_ids = array_keys($merch);
			$merch_user = Db::name('shop_store')->where('id','in',implode(',', $merch_ids))->select();
			$all = array('merch' => $merch, 'merch_user' => $merch_user);
			return ($return == 'all' ? $all : $all[$return]);
		}

		return array();
	}

	public function updateSet($values = array(), $merchid = 0)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : $merchid));
		$sets = self::getSet('', $merchid);

		foreach ($values as $key => $value ) {
			foreach ($value as $k => $v ) {
				$sets[$key][$k] = $v;
			}
		}
		Db::name('shop_store')->where('id',$merchid)->update(array('sets' => iserializer($sets)));
	}

	public function getSet($name = '', $merchid = 0)
	{
		$merchid = ((empty($merchid) ? session('?merchid') : intval($merchid)));

		$merch_set = Db::name('shop_store')->where('id',$merchid)->find();
		$allset = iunserializer($merch_set['sets']);
		return ($name ? $allset[$name] : $allset);
	}

	public function getEnoughs($set)
	{
		$allenoughs = array();
		$enoughs = $set['enoughs'];

		if ((0 < floatval($set['enoughmoney'])) && (0 < floatval($set['enoughdeduct']))) {
			$allenoughs[] = array('enough' => floatval($set['enoughmoney']), 'money' => floatval($set['enoughdeduct']));
		}


		if (is_array($enoughs)) {
			foreach ($enoughs as $e ) {
				if ((0 < floatval($e['enough'])) && (0 < floatval($e['give']))) {
					$allenoughs[] = array('enough' => floatval($e['enough']), 'money' => floatval($e['give']));
				}

			}
		}


		usort($allenoughs, 'merch_sort_enoughs');
		return $allenoughs;
	}

	public static function getMerchs($merch_array)
	{
		$merchs = array();

		if (!(empty($merch_array))) {
			foreach ($merch_array as $key => $value ) {
				$merchid = $key;

				if (0 < $merchid) {
					$merchs[$merchid]['merchid'] = $merchid;
					$merchs[$merchid]['goods'] = $value['goods'];
					$merchs[$merchid]['ggprice'] = $value['ggprice'];
				}

			}
		}
		return $merchs;
	}

	public static function sendMessage($sendData, $message_type)
	{
		return;
		$notice = m('common')->getPluginset('merch');
		$tm = $notice['tm'];
		$templateid = $tm['templateid'];

		if (($message_type == 'merch_apply') && empty($usernotice['merch_apply'])) {
			$tm['msguser'] = 0;
			$data = array('[商户名称]' => $sendData['merchname'], '[主营项目]' => $sendData['salecate'], '[联系人]' => $sendData['realname'], '[手机号]' => $sendData['mobile'], '[申请时间]' => date('Y-m-d H:i:s', $sendData['applytime']));
			$message = array('keyword1' => (!(empty($tm['merch_applytitle'])) ? $tm['merch_applytitle'] : '商户入驻申请'), 'keyword2' => (!(empty($tm['merch_apply'])) ? $tm['merch_apply'] : '[商户名称]在[申请时间]提交了入驻申请，请到后台查看~'));
			return $this->sendNotice($tm, 'merch_apply_advanced', $data, $message);
		}


		if (($message_type == 'merch_apply_money') && empty($usernotice['merch_apply_money'])) {
			$tm['msguser'] = 1;
			$data = array('[商户名称]' => $sendData['merchname'], '[金额]' => $sendData['money'], '[联系人]' => $sendData['realname'], '[手机号]' => $sendData['mobile'], '[申请时间]' => date('Y-m-d H:i:s', $sendData['applytime']));
			$message = array('keyword1' => (!(empty($tm['merch_applymoneytitle'])) ? $tm['merch_applymoneytitle'] : '商户提现申请'), 'keyword2' => (!(empty($tm['merch_applymoney'])) ? $tm['merch_applymoney'] : '[商户名称]在[申请时间]提交了提现申请,提现金额' . $sendData['money'] . '.[联系人] [手机号].请到后台查看~'));
			return $this->sendNotice($tm, 'merch_applymoney_advanced', $data, $message);
		}

	}

}