<?php
/**
 * 财务
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Finance extends Base
{
	public function index()
	{
		header('location: ' . url('admin/finance/withdraw'));exit;
	}

	public function withdraw()
	{
		$psize = 20;
		$condition = ' log.type=' . $type . ' and log.money<>0';
		$condition1 = '';
		$keyword = input('keyword');
		$searchfield = input('searchfield');
		if (!(empty($keyword))) {
			$keyword = trim($keyword);
			if ($searchfield == 'logno') {
				$condition .= ' and log.logno like :keyword';
			} else if ($searchfield == 'member') {
				$condition1 .= ' and (m.realname like :keyword or m.nickname like :keyword or m.mobile like :keyword)';
			}
			$params[':keyword'] = '%' . $keyword . '%';
		}
		return $this->fetch('finance/log/withdraw');
	}

	public function recharge()
	{
		$psize = 20;
		$condition = ' log.type=' . $type . ' and log.money<>0';
		$condition1 = '';
		$keyword = input('keyword');
		$searchfield = input('searchfield');
		if (!(empty($keyword))) {
			$keyword = trim($keyword);
			if ($searchfield == 'logno') {
				$condition .= ' and log.logno like :keyword';
			} else if ($searchfield == 'member') {
				$condition1 .= ' and (m.realname like :keyword or m.nickname like :keyword or m.mobile like :keyword)';
			}
			$params[':keyword'] = '%' . $keyword . '%';
		}
		return $this->fetch('finance/log/recharge');
	}

	public function transaction()
	{
		$type = input('type');
		$condition = ' log.status = 1 ';
		if(!empty($type)) {
			$condition .= ' and log.type = \'' . $type . '\'';
		}
		$keyword = input('keyword');
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and (m.realname like "%' . $keyword . '%" or m.nickname like "%' . $keyword . '%" or m.mobile like "%' . $keyword . '%" )';
		}
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['time']['start']) && !empty($_GET['time']['end'])) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND log.createtime >= ' . $starttime . ' AND log.createtime <= ' . $endtime;
		}
		$level = input('level');
		if (!empty($level)) {
			$condition .= ' and m.level=' . intval($level);
		}
		$groupid = input('groupid');
		if (!empty($groupid)) {
			$condition .= ' and m.groupid=' . intval($groupid);
		}

		$condition .= ' and log.mid<>0';
		$list = Db::name('shop_core_paylog')->alias('log')
			->join('member m','m.id=log.mid','left')
			->join('member_group g','m.groupid=g.id','left')
			->join('member_level l','m.level =l.id','left')
			->where($condition)
			->field('log.*,m.id as mid,m.realname,m.avatar,m.nickname,m.avatar, m.mobile')
			->order('log.createtime','desc')
			->paginate($psize);
		$pager = $list->render();
		$groups = model('member')->getGroups();
		$levels = model('member')->getLevels();
		$this->assign(['list'=>$list,'pager'=>$pager,'groups'=>$groups,'levels'=>$levels]);
		return $this->fetch('finance/detailed/transaction');
	}

	public function credit1()
	{
		$data = $this->creditdata('credit1');
		return $data;
	}

	public function credit2()
	{
		$data = $this->creditdata('credit2');
		return $data;
	}

	protected function creditdata($type = 'credit1')
	{
		$psize = 20;
		$condition = ' 1 and log.module = \'shop\'  and log.credittype=\'' . $type . '\'';
		$keyword = input('keyword');
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and (m.realname like "%' . $keyword . '%" or m.nickname like "%' . $keyword . '%" or m.mobile like "%' . $keyword . '%" )';
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['time']['start']) && !empty($_GET['time']['end'])) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND log.createtime >= ' . $starttime . ' AND log.createtime <= ' . $endtime;
		}
		$level = input('level');
		if (!empty($level)) {
			$condition .= ' and m.level=' . intval($level);
		}
		$groupid = input('groupid');
		if (!empty($groupid)) {
			$condition .= ' and m.groupid=' . intval($groupid);
		}

		$condition .= ' and log.mid<>0';

		$list = Db::name('member_credits_record')->alias('log')
			->join('member m','m.id=log.mid','left')
			->join('member_group g','m.groupid=g.id','left')
			->join('member_level l','m.level =l.id','left')
			->where($condition)
			->field('log.*,m.id as mid, m.realname,m.avatar,m.nickname,m.avatar, m.mobile')
			->order('log.createtime','desc')
			->paginate($psize);
		$pager = $list->render();
		$groups = model('member')->getGroups();
		$levels = model('member')->getLevels();
		$this->assign(['list'=>$list,'pager'=>$pager,'groups'=>$groups,'levels'=>$levels]);
		return $this->fetch('finance/detailed/credit');
	}

	public function downloadbill()
	{
		if (Request::instance()->isPost()) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$type = trim($_GET['type']);
			$datatype = intval($_GET['datatype']);
			$result = model('finance')->downloadbill($starttime, $endtime, $type, $datatype);

			if (is_error($result)) {
				show_json(0,$result['message']);
			}

			model('shop')->plog('finance.downloadbill.main', '下载对账单');
		}
		if (empty($starttime) || empty($endtime)) {
			$starttime = $endtime = time();
		}
		$this->assign(['starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('');
	}

}