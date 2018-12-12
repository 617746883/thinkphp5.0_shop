<?php
/**
 * 拍卖
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Seckill extends Base
{
	public function __construct()
	{
		parent::__construct();
		if (!function_exists('redis')) {
			$this->error('请更新到最新版本才能使用秒杀应用');
			exit();
		}

		$redis = redis();
		dump($redis);
		if (is_error($redis)) {
			$message = '请联系管理员开启 redis 支持，才能使用秒杀应用';

			$this->error($message);
			exit();
		}
	}

	public function index()
	{
		header('location: ' . url('admin/seckill/task'));exit;
		return $this->fetch('');
	}

	public function task()
	{
		$psize = 20;
		$condition = ' 1 ';

		if ($_GET['enabled'] != '') {
			$condition .= ' and enabled=' . intval($_GET['enabled']);
		}

		if (!empty($_GET['keyword'])) {
			$_GET['keyword'] = trim($_GET['keyword']);
			$condition .= ' and title  like "%' . $_GET['keyword'] . '%"';
		}

		$list = Db::name('shop_seckill_task')->where($condition)->order('id desc')->paginate($psize);

		if(!empty($list)) {
			foreach ($list as $k => $row) {
				$row['roomcount'] = Db::name('shop_seckill_task_room')->where('taskid',$row['id'])->count();
				$row['isused'] = model('seckill')->usedDate($row['id']);
				$data = array();
	    		$data = $row;
	    		$list->offsetSet($k,$data);
			}
			unset($row);
		}
		$pager = $list->render();
		$category = Db::name('shop_seckill_category')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'category'=>$category]);
		return $this->fetch('seckill/task/index');
	}

	public function taskadd()
	{
		$data = $this->taskpost();
		return $data;
	}

	public function taskedit()
	{
		$data = $this->taskpost();
		return $data;
	}

	protected function taskpost()
	{
		$id = intval($_GET['id']);
		$redis = redis();

		if (Request::instance()->isPost()) {
			$allgoods = array();
			$alltimes = $_POST['times'];
			if (!is_array($alltimes) || empty($alltimes)) {
				show_json(0, '未设置任何秒杀点');
			}

			$taskdata = array('title' => trim($_POST['title']), 'enabled' => intval($_POST['enabled']), 'cateid' => intval($_POST['cateid']), 'tag' => trim($_POST['tag']), 'page_title' => trim($_POST['page_title']), 'share_title' => trim($_POST['share_title']), 'share_desc' => trim($_POST['share_desc']), 'share_icon' => trim($_POST['share_icon']), 'oldshow' => intval($_POST['oldshow']), 'closesec' => intval($_POST['closesec']), 'times' => implode(',', $alltimes), 'overtimes' => intval($_POST['overtimes']));

			if (!empty($id)) {
				Db::name('shop_seckill_task')->where('id',$id)->update($taskdata);
				model('shop')->plog('seckill.task.edit', '修改专题 ID: ' . $id . ' 标题:' . $taskdata['title'] . ' 自动取消时间: ' . $taskdata['closesec']);
			}
			else {
				$taskdata['createtime'] = time();
				$id = Db::name('shop_seckill_task')->insertGetId($taskdata);
				$taskdata['id'] = $id;
				model('shop')->plog('seckill.task.add', '添加专题 ID: ' . $id . ' 标题:' . $taskdata['title'] . ' 自动取消时间: ' . $taskdata['closesec']);
			}

			$notimes = array();
			$i = 0;

			while ($i <= 23) {
				if (!in_array($i, $alltimes)) {
					$notimes[] = $i;
				}

				++$i;
			}

			foreach ($alltimes as $i) {
				$time = Db::name('shop_seckill_task_time')->where('taskid',$id)->where('time',$i)->find();

				if (empty($time)) {
					$time = array('taskid' => $id, 'time' => $i);
					Db::name('shop_seckill_task_time')->insert($time);
				}
			}

			if (!empty($notimes)) {
				foreach ($notimes as $i) {
					$time = Db::name('shop_seckill_task_time')->where('taskid',$id)->where('time',$i)->find();
					Db::name('shop_seckill_task_time')->where('id',$time['id'])->delete();
					Db::name('shop_seckill_task_goods')->where('taskid',$id)->where('timeid',$time['id'])->delete();
				}
			}

			model('seckill')->setTaskCache($id);
			show_json(1, array('url' => url('admin/seckill/task')));
		}

		$item = Db::name('shop_seckill_task')->where('id',$id)->find();
		$category = Db::name('shop_seckill_category')->select();
		$alltimes = array();
		$times = array();

		if (!empty($item)) {
			$alltimes = explode(',', $item['times']);
			$times = Db::name('shop_seckill_task_time')->where('taskid',$item['id'])->select();

			foreach ($times as &$t) {
				$goods = Db::name('shop_seckill_task_goods')->alias('tg')->join('shop_goods g','tg.goodsid = g.id','left')->where('tg.taskid=' .$item['id']. ' and tg.timeid=' . $t['id'])->group('tg.goodsid')->order('tg.displayorder asc')->field('tg.id,tg.goodsid, tg.price as packageprice, tg.maxbuy, g.title,g.thumb,g.hasoption,tg.commission1,tg.commission2,tg.commission3,tg.total')->select();
				foreach ($goods as &$g) {
					$options = array();

					if ($g['hasoption']) {
						$g['optiontitle'] = Db::name('shop_seckill_task_goods')->alias('tg')->join('shop_goods g','tg.goodsid = g.id','left')->where('tg.timeid=' . $t['id'] . ' and tg.taskid= ' . $item['id'] . ' and tg.goodsid= ' . $g['goodsid'])->field('tg.id,tg.goodsid,tg.optionid,tg.price as packageprice,tg.maxbuy,g.title,g.marketprice,tg.commission1,tg.commission2,tg.commission3,tg.total')->select();

						foreach ($g['optiontitle'] as $go) {
							$options[] = $go['optionid'];
						}
					}

					$g['option'] = implode(',', $options);
				}

				unset($g);
				$t['goods'] = $goods;
			}

			unset($t);
		}
		$this->assign(['item'=>$item,'category'=>$category,'times'=>$times,'alltimes'=>$alltimes]);
		return $this->fetch('seckill/task/post');
	}

}	