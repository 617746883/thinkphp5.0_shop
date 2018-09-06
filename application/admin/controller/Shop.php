<?php
/**
 * 店铺
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Db;
use think\Request;
class Shop extends Base
{
    public function index()
    {
    	header('location: ' . url('admin/shop/banner'));exit;
    }

    public function banner()
    {
		$psize = 20;
		$condition = ' 1 ';
		$enabled = input('enabled');
		if ($enabled != '') {
			$condition .= ' and enabled=' . intval(input('enabled'));
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and bannername like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_banner')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
    	return $this->fetch('shop/banner/index');
    }

    public function banneradd()
	{
		$bannerdata = $this->bannerpost();
		return $bannerdata;
	}

	public function banneredit()
	{
		$bannerdata = $this->bannerpost();
		return $bannerdata;
	}

	protected function bannerpost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim(input('bannername')), 'link' => trim(input('link')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'thumb' => trim(input('thumb')));
			if (!empty($id)) {
				Db::name('shop_banner')->where('id',$id)->update($data);
				model('shop')->plog('shop.banner.edit', '修改幻灯片 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_banner')->insertGetId($data);
				model('shop')->plog('shop.banner.add', '添加幻灯片 ID: ' . $id);
			}
			show_json(1);
		}
		$item = Db::name('shop_banner')->where('id',$id)->find();

		$request = Request::instance();
		$controller = strtolower($request->controller());
		$this->assign(['item'=>$item,'controller'=>$controller]);
		return $this->fetch('shop/banner/post');
	}

	public function bannerdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1);
	}

	public function bannerdisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item =  Db::name('shop_banner')->where('id',$id)->field('id,bannername')->find();

		if (!empty($item)) {
			Db::name('shop_banner')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('shop.banner.edit', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function bannerenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_banner')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('shop.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function nav()
	{
		$psize = 20;
		$condition = ' 1 and iswxapp=0 ';

		if (input('status') != '') {
			$condition .= ' and status=' . intval(input('status'));
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and navname like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_nav')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('shop/nav/index');
	}

	public function navadd()
	{
		$navdata = $this->navpost();
		return $navdata;
	}

	public function navedit()
	{
		$navdata = $this->navpost();
		return $navdata;
	}

	protected function navpost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('navname' => trim(input('navname')), 'url' => trim(input('url')), 'status' => intval(input('status')), 'displayorder' => intval(input('displayorder')), 'icon' => trim(input('icon')));

			if (!empty($id)) {
				Db::name('shop_nav')->where('id',$id)->update($data);
				model('shop')->plog('shop.nav.edit', '修改首页导航 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_nav')->insertGetId($data);
				model('shop')->plog('shop.nav.add', '添加首页导航 ID: ' . $id);
			}

			show_json(1);
		}

		$item = Db::name('shop_nav')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('shop/nav/post');
	}

	public function navdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_nav')->where('id','in',$id)->field('id,navname')->select();

		foreach ($items as $item) {
			Db::name('shop_nav')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.nav.delete', '删除首页导航 ID: ' . $item['id'] . ' 标题: ' . $item['navname'] . ' ');
		}
		show_json(1);
	}

	public function navdisplayorder()
	{
		$id = input('id/d');
		$displayorder = intval(input('value'));
		$item = Db::name('shop_nav')->where('id',$id)->field('id,navname')->find();

		if (!empty($item)) {
			Db::name('shop_nav')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('shop.nav.edit', '修改首页导航排序 ID: ' . $item['id'] . ' 标题: ' . $item['navname'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function navstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_nav')->where('id','in',$id)->field('id,navname')->select();

		foreach ($items as $item) {
			Db::name('shop_nav')->where('id',$item['id'])->setField('status',input('status/d',1));
			model('shop')->plog('shop.nav.edit', ('修改首页导航状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['navname'] . '<br/>状态: ' . input('status')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function adv()
	{
		$psize = 20;
		$condition = ' 1 and iswxapp=0 ';
		if (input('enabled') != '') {
			$condition .= ' and enabled=' . intval(input('enabled'));
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and advname like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_adv')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
    	return $this->fetch('shop/adv/index');
	}

	public function advadd()
	{
		$advdata = $this->advpost();
		return $advdata;
	}

	public function advedit()
	{
		$advdata = $this->advpost();
		return $advdata;
	}

	protected function advpost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('advname' => trim(input('advname')), 'link' => trim(input('link')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'thumb' => trim(input('thumb')));

			if (!empty($id)) {
				Db::name('shop_adv')->where('id',$id)->update($data);
				model('shop')->plog('shop.adv.edit', '修改广告 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_adv')->insertGetId($data);
				model('shop')->plog('shop.adv.add', '添加广告 ID: ' . $id);
			}

			show_json(1);
		}

		$item = Db::name('shop_adv')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('shop/adv/post');
	}

	public function advdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_adv')->where('id','in',$id)->field('id,advname')->select();

		foreach ($items as $item) {
			Db::name('shop_adv')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.adv.delete', '删除广告 ID: ' . $item['id'] . ' 标题: ' . $item['advname'] . ' ');
		}

		show_json(1);
	}

	public function advdisplayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_adv')->where('id',$id)->field('id,advname')->find();

		if (!empty($item)) {
			Db::name('shop_adv')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('shop.adv.edit', '修改广告排序 ID: ' . $item['id'] . ' 标题: ' . $item['advname'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function advenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_adv')->where('id','in',$id)->field('id,advname')->select();

		foreach ($items as $item) {
			Db::name('shop_adv')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('shop.adv.edit', ('修改广告状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['advname'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}

		show_json(1);
	}

	public function composition()
	{
		$defaults = array(
			'adv'     => array('text' => '幻灯片', 'visible' => 1),
			'search'  => array('text' => '搜索栏', 'visible' => 1),
			'nav'     => array('text' => '导航栏', 'visible' => 1),
			'notice'  => array('text' => '公告栏', 'visible' => 1),
			'seckill' => array('text' => '秒杀栏', 'visible' => 1),
			'cube'    => array('text' => '魔方栏', 'visible' => 1),
			'banner'  => array('text' => '广告栏', 'visible' => 1),
			'goods'   => array('text' => '推荐栏', 'visible' => 1)
		);

		if (Request::instance()->isPost()) {
			$datas = json_decode(html_entity_decode($_POST['datas']), true);

			if (!is_array($datas)) {
				show_json(0, '数据出错');
			}

			$indexsort = array();
			$visible = input('visible/a');
			foreach ($datas as $v) {
				$indexsort[$v['id']] = array('text' => $defaults[$v['id']]['text'], 'visible' => intval($visible[$v['id']]));
			}

			$shop = model('common')->getSysset('shop');
			$shop['indexsort'] = $indexsort;
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('admin.shop.composition', '修改首页排版');
			show_json(1);
		}

		$shop = model('common')->getSysset('shop');
		$oldsorts = (!empty($shop['indexsort']) ? $shop['indexsort'] : $defaults);
		$sorts = array();

		foreach ($oldsorts as $key => $old) {
			$sorts[$key] = $old;
			if (($key == 'notice') && !isset($oldsorts['seckill'])) {
				$sorts['seckill'] = array('text' => '秒杀栏', 'visible' => 0);
			}
		}
		$this->assign(['sorts'=>$sorts]);
		return $this->fetch('shop/composition/index');
	}

	public function dispatch()
	{
		$condition = ' 1 ';
		$enabled=input('enabled/d');
		$condition .= ' and merchid = 0';
		$keyword = input('keyword/s');
		if($enabled != '')
		{
			if($enabled == 1) {
				$condition .= ' and enabled = ' . $enabled;
			} elseif ($enabled == 2) {
				$condition .= ' and enabled = 0';
			}

		}
		if (!empty($keyword)) {
			$condition .= ' and dispatchname like "%' . $keyword . '%"';
		}
		$list = Db::name('shop_dispatch')
			->where($condition)
			->field('id,displayorder,dispatchname,isdefault,secondnumprice,enabled,secondprice,firstprice,firstnumprice,calculatetype')
			->select();
		$this->assign(['list' => $list]);
		return $this->fetch('shop/dispatch/index');
	}

	public function dispatchadd()
	{
		$dispatchdata = $this->dispatchpost();
		return $dispatchdata;
	}

	public function dispatchedit()
	{
		$dispatchdata = $this->dispatchpost();
		return $dispatchdata;
	}

	public function dispatchpost()
	{

		$id = input('id/d');

		if (Request::instance()->isPost()) {
			$areas = array();
			$randoms = $_POST['random'];

			if (is_array($randoms)) {
				foreach ($randoms as $random) {
					$citys = trim($_POST['citys'][$random]);

					if (empty($citys)) {
						continue;
					}

					if ($_POST['firstnum'][$random] < 1) {
						$_POST['firstnum'][$random] = 1;
					}

					if ($_POST['secondnum'][$random] < 1) {
						$_POST['secondnum'][$random] = 1;
					}

					$areas[] = array('citys' => $_POST['citys'][$random], 'citys_code' => $_POST['citys_code'][$random], 'firstprice' => $_POST['firstprice'][$random], 'firstweight' => max(0, $_POST['firstweight'][$random]), 'secondprice' => $_POST['secondprice'][$random], 'secondweight' => $_POST['secondweight'][$random] <= 0 ? 1000 : $_POST['secondweight'][$random], 'firstnumprice' => $_POST['firstnumprice'][$random], 'firstnum' => $_POST['firstnum'][$random], 'secondnumprice' => $_POST['secondnumprice'][$random], 'secondnum' => $_POST['secondnum'][$random], 'freeprice' => $_POST['freeprice'][$random]);
				}
			}

			$_POST['default_firstnum'] = trim($_POST['default_firstnum']);

			if ($_POST['default_firstnum'] < 1) {
				$_POST['default_firstnum'] = 1;
			}

			$_POST['default_secondnum'] = trim($_POST['default_secondnum']);

			if ($_POST['default_secondnum'] < 1) {
				$_POST['default_secondnum'] = 1;
			}

			$data = array('merchid' => 0, 'displayorder' => intval($_POST['displayorder']), 'dispatchtype' => intval($_POST['dispatchtype']), 'isdefault' => intval($_POST['isdefault']), 'dispatchname' => trim($_POST['dispatchname']), 'express' => trim($_POST['express']), 'calculatetype' => trim($_POST['calculatetype']), 'firstprice' => trim($_POST['default_firstprice']), 'firstweight' => trim(max(0, $_POST['default_firstweight'])), 'secondprice' => trim($_POST['default_secondprice']), 'secondweight' => intval($_POST['default_secondweight']) <= 0 ? 1000 : trim($_POST['default_secondweight']), 'firstnumprice' => trim($_POST['default_firstnumprice']), 'firstnum' => $_POST['default_firstnum'], 'secondnumprice' => trim($_POST['default_secondnumprice']), 'secondnum' => $_POST['default_secondnum'], 'freeprice' => $_POST['default_freeprice'], 'areas' => iserializer($areas), 'nodispatchareas' => iserializer($_POST['nodispatchareas']), 'nodispatchareas_code' => iserializer($_POST['nodispatchareas_code']), 'isdispatcharea' => intval($_POST['isdispatcharea']), 'enabled' => intval($_POST['enabled']));

			if ($data['isdefault']) {
				Db::name('shop_dispatch')->where('merchid',0)->setField('isdefault',0);
			}

			if (!empty($id)) {
				Db::name('shop_dispatch')->where('id',$id)->update($data);
				model('shop')->plog('shop.dispatch.edit', '修改配送方式 ID: ' . $id);
			} else {
				$id = Db::name('shop_dispatch')->insertGetId($data);
				model('shop')->plog('shop.dispatch.add', '添加配送方式 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/shop/dispatchedit', array('id' => $id))));
		}

		$dispatch = Db::name('shop_dispatch')->where('id',$id)->find();

		if (!empty($dispatch)) {
			$dispatch_areas = unserialize($dispatch['areas']);
			$dispatch_carriers = unserialize($dispatch['carriers']);
			$dispatch_nodispatchareas = unserialize($dispatch['nodispatchareas']);
			$dispatch_nodispatchareas_code = unserialize($dispatch['nodispatchareas_code']);

		}
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$areas = model('common')->getAreas();
		$express_list = model('shop')->getExpressList();
		$this->assign(['dispatch'=>$dispatch,'area_set'=>$area_set,'new_area'=>$new_area,'areas'=>$areas,'express_list'=>$express_list,'dispatch_nodispatchareas' => $dispatch_nodispatchareas,'dispatch_areas' => $dispatch_areas,'dispatch_carriers'=>$dispatch_carriers,'dispatch_nodispatchareas_code'=>$dispatch_nodispatchareas_code]);
		return $this->fetch('shop/dispatch/post');
	}

	public function dispatchdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.dispatch.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchenabled()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('enabled',input('enabled/d'));
			model('shop')->plog('shop.house.edit', ('修改配送方式状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . input('enabled/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchsetdefault()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}
		$isdefault = input('isdefault');

		if ($isdefault == 1) {
			Db::name('shop_dispatch')->where('merchid',0)->setField('isdefault',0);
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('isdefault',input('isdefault/d'));
			model('shop')->plog('shop.dispatch.edit', ('设为默认配送方式<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . $_POST['isdefault']) == 1 ? '是' : '否');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchdisplayorder()
	{
		$id = input('id/d');
		$displayorder = intval(input('value'));
		$item = Db::name('shop_dispatch')->where('id','eq',$id)->field('id,dispatchname')->find();

		if (!empty($item)) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('displayorder',$displayorder);
			model('shop')->plog('shop.dispatch.edit', '修改配送方式排序 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function dispatchtpl()
	{
		$random = random(16);
		ob_start();		
		$this->assign(['random'=>$random]);
		echo $this->fetch('shop/dispatch/tpl');
		$contents = ob_get_contents();
		ob_clean();
		exit(json_encode(array('random' => $random, 'html' => $contents)));
	}

	public function notice()
    {
    	$psize = 20;
		$condition = ' merchid =0 ';
		$status = input('status');
		$keyword = input('keyword');
		if ($status != '') {
			$condition .= ' and status=' . $status;
		}

		if (!empty($keyword)) {
			$condition .= ' and title like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_notice')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
    	return $this->fetch('shop/notice/index');
    }

    public function noticeadd()
	{
		$noticedata = $this->noticepost();
		return $noticedata;
	}

	public function noticeedit()
	{
		$noticedata = $this->noticepost();
		return $noticedata;
	}

	protected function noticepost()
	{
		$id = input('id/d');

		if (Request::instance()->isPost()) {
			$data = array('link' => input('link/s'),'displayorder' => input('displayorder/d',0), 'title' => trim(input('title/s')), 'cate' => trim(input('cate/s')),  'thumb' => trim(input('thumb')), 'detail' => input('detail'), 'status' => input('status/d',0), 'createtime' => time(),'merchid'=>0);

			if (!empty($id)) {
				Db::name('shop_notice')->where('id',$id)->update($data);
				model('shop')->plog('shop.notice.edit', '修改公告 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_notice')->insertGetId($data);
				model('shop')->plog('shop.notice.add', '修改公告 ID: ' . $id);
				model('notice')->push();
			}
			show_json(1, array('url' => url('admin/shop/notice')));
		}

		$notice = Db::name('shop_notice')->where('id',$id)->find();
		$this->assign(['notice'=>$notice]);
		return $this->fetch('shop/notice/post');
	}

	public function noticepush()
	{
		$id = input('id/d');
		$notice = Db::name('shop_notice')->where('id',$id)->find();
		model('notice')->sendShopNotice($notice['id']);
		show_json(1, array('url' => referer()));
	}

	public function noticedisplayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_notice')->where('id',$id)->field('id,title')->find();

		if (!empty($item)) {
			Db::name('shop_notice')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('shop.notice.edit', '修改公告排序 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function noticedelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_notice')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.notice.delete', '删除公告 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function noticestatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_notice')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('shop.notice.edit', ('修改公告状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function comment()
    {
    	$psize = 20;
		$condition = ' c.deleted=0 and g.merchid=0';
		$keyword = input('keyword');
		$time = input('time/a');
		$fade = input('fade');
		$replystatus = input('replystatus');
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and ( o.ordersn like "%' . $keyword . '%" or g.title like "%' . $keyword . '%")';
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($time['start']) && !empty($time['end'])) {
			$starttime = strtotime($time['start']);
			$endtime = strtotime($time['end']);
			$condition .= ' AND c.createtime >= ' . $starttime . ' AND c.createtime <= ' . $endtime;
		}

		if ($fade != '') {
			if (empty($fade)) {
				$condition .= ' AND c.mid=\'\'';
			}
			else {
				$condition .= ' AND c.mid<>\'\'';
			}
		}

		if ($replystatus != '') {
			if (empty($replystatus)) {
				$condition .= ' AND c.reply_content=\'\'';
			}
			else {
				$condition .= ' AND c.append_content=\'\' and c.append_reply_content=\'\'';
			}
		}

		$list = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','c.goodsid = g.id','left')->join('shop_order o','c.orderid = o.id','left')->where($condition)->field('c.*, o.ordersn,g.title,g.thumb')->order('c.createtime','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime,'fade'=>$fade,'replystatus'=>$replystatus,'keyword'=>$keyword]);
    	return $this->fetch('shop/comment/index');
    }

    public function commentdelete()
	{
		$id = intval($_POST['id']);

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_order_comment')->where('id','in',$id)->field('id')->select();

		foreach ($items as $item) {
			Db::name('shop_order_comment')->where('id',$item['id'])->setField('deleted',1);
			$goods = Db::name('shop_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
			model('shop')->plog('shop.comment.delete', '删除评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function commentadd()
	{
		$data = $this->commentvirtual();
		return $data;
	}

	public function commentedit()
	{
		$data = $this->commentvirtual();
		return $data;
	}

	protected function commentvirtual()
	{
		$id = input('id');
		$item = Db::name('shop_order_comment')->where('id',$id)->find();
		$goodsid = input('goodsid');

		if (Request::instance()->isPost()) {
			if (empty($goodsid)) {
				show_json(0, array('message' => '请选择要评价的商品'));
			}

			$goods = set_medias(Db::name('shop_goods')->where('id',$goodsid)->field('id,thumb,title')->find(), 'thumb');

			if (empty($goods)) {
				show_json(0, array('message' => '请选择要评价的商品'));
			}

			$createtime = strtotime(input('createtime'));
			if (empty($createtime) || (time() < $createtime)) {
				$createtime = time();
			}

			$data = array('level' => intval($_POST['level']), 'goodsid' => intval($_POST['goodsid']), 'nickname' => trim($_POST['nickname']), 'headimgurl' => trim($_POST['headimgurl']), 'content' => $_POST['content'], 'images' => is_array($_POST['images']) ? iserializer($_POST['images']) : iserializer(array()), 'reply_content' => $_POST['reply_content'], 'reply_images' => is_array($_POST['reply_images']) ? iserializer($_POST['reply_images']) : iserializer(array()), 'append_content' => $_POST['append_content'], 'append_images' => is_array($_POST['append_images']) ? iserializer($_POST['append_images']) : iserializer(array()), 'append_reply_content' => $_POST['append_reply_content'], 'append_reply_images' => is_array($_POST['append_reply_images']) ? iserializer($_POST['append_reply_images']) : iserializer(array()), 'createtime' => $createtime);

			if (empty($data['nickname'])) {
				$data['nickname'] = Db::name('member')->where('nickname','<>','')->orderRaw(rand())->value('nickname');
			}

			if (empty($data['headimgurl'])) {
				$data['headimgurl'] = Db::name('member')->where('avatar','<>','')->orderRaw(rand())->value('avatar');
			}

			if (!empty($id)) {
				Db::name('shop_order_comment')->where('id',$id)->update($data);
				model('shop')->plog('shop.comment.edit', '编辑商品虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}
			else {
				$id = Db::name('shop_order_comment')->insertGetId($data);
				model('shop')->plog('shop.comment.add', '添加虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}

			show_json(1, array('url' => url('admin/shop/comment')));
		}

		if (empty($goodsid)) {
			$goodsid = intval($item['goodsid']);
		}

		$goods = Db::name('shop_goods')->where('id',$goodsid)->field('id,thumb,title')->find();
		$this->assign(['goods'=>$goods,'item'=>$item]);
		return $this->fetch('shop/comment/virtual');
	}

	public function commentpost()
	{
		$id = input('id');
		$type = input('type');
		$item = Db::name('shop_order_comment')->where('id',$id)->find();
		$goods = Db::name('shop_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
		$order = Db::name('shop_order')->where('id',$item['orderid'])->field('id,ordersn')->find();

		if (Request::instance()->isPost()) {
			if ($type == 0) {
				$data = array('reply_content' => $_POST['reply_content'], 'reply_images' => is_array($_POST['reply_images']) ? iserializer(model('common')->array_images($_POST['reply_images'])) : iserializer(array()), 'append_reply_content' => $_POST['append_reply_content'], 'append_reply_images' => is_array($_POST['append_reply_images']) ? iserializer($_POST['append_reply_images']) : iserializer(array()));
				Db::name('shop_order_comment')->where('id',$id)->update($data);
				model('shop')->plog('shop.comment.post', '回复商品评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}
			else {
				$checked = intval($_POST['checked']);
				$change_data = array();
				$change_data['checked'] = $checked;

				if (!empty($item['append_content'])) {
					$replychecked = intval($_POST['replychecked']);
					$change_data['replychecked'] = $replychecked;
				}

				$checked_array = array('审核通过', '审核中', '审核不通过');
				Db::name('shop_order_comment')->where('id',$id)->update($change_data);
				$log_msg = '商品首次评价' . $checked_array[$checked];

				if (!empty($item['append_content'])) {
					$log_msg .= ' 追加评价' . $checked_array[$checked];
				}

				$log_msg .= ' ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title'];
				model('shop')->plog('shop.comment.post', $log_msg);
			}

			show_json(1, array('url' => url('admin/shop/comment')));
		}
		$append_images = iunserializer($item['append_images']);
		$images = iunserializer($item['images']);
		$this->assign(['goods'=>$goods,'item'=>$item,'type'=>$type,'order'=>$order,'append_images'=>$append_images,'images'=>$images]);
		return $this->fetch('shop/comment/post');
	}

	public function refundaddress()
	{
		$psize = 20;
		$condition = ' merchid=0 ';

		if ($enabled != '') {
			$condition .= ' and enabled=' . intval($enabled);
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and title  like %' . $keyword . '%';
		}

		$list = Db::name('shop_refund_address')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('shop/refundaddress/index');
	}

	public function refundaddressadd()
	{
		$data = $this->refundaddresspost();
		return $data;
	}

	public function refundaddressedit()
	{
		$data = $this->refundaddresspost();
		return $data;
	}

	protected function refundaddresspost()
	{
		$id = input('id');

		if (Request::instance()->isPost()) {
			$data = array();
			$data['merchid'] = 0;
			$data['title'] = trim($_POST['title']);
			$data['name'] = trim($_POST['name']);
			$data['tel'] = trim($_POST['tel']);
			$data['mobile'] = trim($_POST['mobile']);
			$data['zipcode'] = trim($_POST['zipcode']);
			$data['province'] = trim($_POST['province']);
			$data['city'] = trim($_POST['city']);
			$data['area'] = trim($_POST['area']);
			$data['address'] = trim($_POST['address']);
			$data['isdefault'] = $isdefault;

			if ($data['isdefault']) {
				Db::name('shop_refund_address')->where('merchid',0)->setField('isdefault',0);
			}

			if (!empty($id)) {
				model('shop')->plog('shop.refundaddress.edit', '修改退货地址 ID: ' . $id);
				Db::name('shop_refund_address')->where('id',$id)->update($data);
			}
			else {
				$id = Db::name('shop_refund_address')->insertGetId($data);
				model('shop')->plog('shop.refundaddress.add', '添加退货地址 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/shop/refundaddress', array('op' => 'display'))));
		}

		if (!empty($id)) {
			$item = Db::name('shop_refund_address')->where('id',$id)->where('merchid',0)->find();
		}

		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);
		$this->assign(['item'=>$item,'area_set'=>$area_set,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('shop/refundaddress/post');
	}

	public function refundaddressdelete()
	{
		$id = input('id');

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_refund_address')->where('id','in',$id)->where('merchid',0)->select();

		foreach ($items as $item) {
			Db::name('shop_refund_address')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.refundaddress.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function refundaddresssetdefault()
	{
		$id = input('id');
		$isdefault = input('isdefault/d');
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		if ($isdefault == 1) {
			Db::name('shop_refund_address')->where('merchid',0)->setField('isdefault',0);
		}

		$items = Db::name('shop_refund_address')->where('id','in',$id)->where('merchid',0)->select();

		foreach ($items as $item) {
			Db::name('shop_refund_address')->where('id',$item['id'])->setField('isdefault',intval($isdefault));
			model('shop')->plog('shop.refundaddress.edit', ('修改配送方式默认状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $isdefault) == 1 ? '是' : '否');
		}

		show_json(1, array('url' => referer()));
	}

	public function ajax()
	{
		$goods_totals = Db::name('shop_goods')->where('status=1 and deleted=0 and total<=0 and total<>-1')->count();
		$finance_total = 1;
			// pdo_fetchcolumn('select count(1) from ' . tablename('ewei_shop_member_log') . ' log ' . ' left join ' . tablename('ewei_shop_member') . ' m on m.openid=log.openid and m.uniacid= log.uniacid' . ' left join ' . tablename('ewei_shop_member_group') . ' g on m.groupid=g.id' . ' left join ' . tablename('ewei_shop_member_level') . ' l on m.level =l.id' . ' where log.uniacid=:uniacid and log.type=:type and log.money<>0 and log.status=:status', array(':uniacid' => $_W['uniacid'], ':type' => 1, ':status' => 0));
		
		show_json(1, array('goods_totals' => $goods_totals, 'finance_total' => $finance_total));
	}

	public function ajaxgoods()
	{
		show_json(1, array(
		'obj' => array('goods_rank_0' => $this->selectGoodsRank(0), 'goods_rank_1' => $this->selectGoodsRank(1), 'goods_rank_7' => $this->selectGoodsRank(7))
	));
	}

	protected function selectGoodsRank($day = 0)
	{
		$day = (int) $day;

		if ($day != 0) {
			$createtime1 = strtotime(date('Y-m-d', time() - ($day * 3600 * 24)));
			$createtime2 = strtotime(date('Y-m-d', time()));
		} else {
			$createtime1 = strtotime(date('Y-m-d', time()));
			$createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
		}

		$condition = ' ';
		$condition1 = ' 1 ';

		if (!empty($createtime1)) {
			$condition .= ' AND o.createtime >= ' . $createtime1;
		}

		if (!empty($createtime2)) {
			$condition .= ' AND o.createtime <= ' . $createtime2 . ' ';
		}
		$list = Db::name('shop_goods')->alias('g')->where($condition1)->field('g.id,g.title,g.thumb,(select ifnull(sum(og.price),0) from  ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_order') . ' o on og.orderid=o.id  where o.status>=1 and og.goodsid=g.id ' . $condition . ')  as money , (select ifnull(sum(og.total),0) from ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_order') . ' o on og.orderid=o.id  where o.status>=1 and og.goodsid=g.id ' . $condition . ') as count')->order('count','desc')->limit(7)->select();
		return $list;
	}

}