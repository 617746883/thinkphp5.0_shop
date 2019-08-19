<?php
/**
 * 积分商城
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Sign extends Base
{
	public function index()
	{
		header('location: ' . url('admin/sign/rule'));exit;
	}

	public function rule()
	{
		$set = model('sign')->getSet();
		if (!empty($set['sign_rule'])) {
			$set['sign_rule'] = iunserializer($set['sign_rule']);
		}
		if (!empty($set['reword_order'])) {
			$set['reword_order'] = iunserializer($set['reword_order']);

			foreach ($set['reword_order'] as $key => $row) {
				$volume[$key] = $row['day'];
			}

			if (1 < count($set['reword_order'])) {
				array_multisort($volume, SORT_ASC, $set['reword_order']);
			}

			unset($volume);
		}

		if (!empty($set['reword_sum'])) {
			$set['reword_sum'] = iunserializer($set['reword_sum']);

			foreach ($set['reword_sum'] as $key => $row) {
				$volume[$key] = $row['day'];
			}

			if (1 < count($set['reword_sum'])) {
				array_multisort($volume, SORT_ASC, $set['reword_sum']);
			}

			unset($volume);
		}

		if (!empty($set['reword_special'])) {
			$set['reword_special'] = iunserializer($set['reword_special']);
		}

		if (Request::instance()->isPost()) {
			$data = array('isopen' => intval($_POST['isopen']), 'signold' => intval($_POST['signold']), 'signold_price' => intval($_POST['signold_price']), 'signold_type' => intval($_POST['signold_type']), 'textsign' => trim($_POST['textsign']), 'textsignold' => trim($_POST['textsignold']), 'textsigned' => trim($_POST['textsigned']), 'textsignforget' => trim($_POST['textsignforget']), 'maincolor' => trim($_POST['maincolor']), 'cycle' => intval($_POST['cycle']), 'reward_default_first' => intval($_POST['reward_default_first']), 'reward_default_day' => intval($_POST['reward_default_day']), 'reword_order' => $_POST['reword_order'] ? $_POST['reword_order'] : '', 'reword_sum' => $_POST['reword_sum'] ? $_POST['reword_sum'] : '', 'reword_special' => $_POST['reword_special'] ? $_POST['reword_special'] : '', 'sign_rule' => iserializer($_POST['sign_rule']));

			if (!empty($data['reword_order'])) {
				$reword_order = array();

				foreach ($data['reword_order'] as $k1 => $v1) {
					foreach ($v1 as $k2 => $v2) {
						if (!empty($k1) && !empty($v2)) {
							$reword_order[$k2][$k1] = $v2;
						}
					}
				}

				$data['reword_order'] = iserializer($reword_order);
			}

			if (!empty($data['reword_sum'])) {
				$reword_sum = array();

				foreach ($data['reword_sum'] as $k1 => $v1) {
					foreach ($v1 as $k2 => $v2) {
						if (!empty($k1) && !empty($v2)) {
							$reword_sum[$k2][$k1] = $v2;
						}
					}
				}

				$data['reword_sum'] = iserializer($reword_sum);
			}

			if (!empty($data['reword_special'])) {
				$reword_special = array();

				foreach ($data['reword_special'] as $k1 => $v1) {
					foreach ($v1 as $k2 => $v2) {
						if ($k1 == 'date') {
							$v2 = strtotime($v2);
						}

						$reword_special[$k2][$k1] = $v2;
					}
				}

				$data['reword_special'] = iserializer($reword_special);
			}

			if (empty($set)) {
				Db::name('shop_sign_set')->insert($data);
			} else {
				Db::name('shop_sign_set')->where('id = ' . $set['id'])->update($data);
			}

			model('shop')->plog('sign.rule.edit', '修改签到规则');
			$textcredit = trim($_POST['textcredit']);

			if (!empty($textcredit)) {
				$tradedata = model('common')->getSysset('trade');
				$tradedata['credittext'] = $textcredit;
				model('common')->updateSysset(array('trade' => $tradedata));
			}

			show_json(1);
		}
		$this->assign(['set'=>$set]);
		return $this->fetch('sign/rule');
	}

	public function records()
	{
		$time_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$time_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
		$starttime = !empty($_GET['time']['start']) ? strtotime($_GET['time']['start']) : $time_start;
		$endtime = !empty($_GET['time']['end']) ? strtotime($_GET['time']['end']) : $time_end;
		$condition = ' 1 ';
		$time = $_GET['time'];
		$keyword = trim($_GET['keyword']);
		$type = trim($_GET['type']);
		$searchtime = intval($_GET['searchtime']);

		if (!empty($keyword)) {
			$condition .= ' and (m.nickname like \'%' . $keyword . '%\' or r.log like \'%' . $keyword . '%\') ';
		}

		if ($type != '' && -1 < $type) {
			$condition .= ' and `type`=' . $type;
		}

		if (!empty($searchtime) && is_array($_GET['time'])) {
			$_GET['time']['start'] = strtotime($_GET['time']['start']);
			$_GET['time']['end'] = strtotime($_GET['time']['end']) + 3600 * 24 - 1;
			$condition .= ' and r.time BETWEEN ' . $_GET['time']['start'] . ' AND ' . $_GET['time']['end'];
		}

		$psize = 20;
		$list = Db::name('shop_sign_records')->alias('r')->join('member m','r.mid = m.id','left')->where($condition)->order('r.time desc, r.id desc')->paginate($psize);
		$pager = $list->render();
		$count = 0;

		foreach ($list as $item) {
			if (0 < $item['credit']) {
				$count = $count + $item['credit'];
			}
		}
		$this->assign(['list'=>$list,'pager'=>$pager,'time_start'=>$time_start,'time_end'=>$time_end,'starttime'=>$starttime,'endtime'=>$endtime,'keyword'=>$keyword,'type'=>$type,'searchtime'=>$searchtime]);
		return $this->fetch('sign/records');
	}

}