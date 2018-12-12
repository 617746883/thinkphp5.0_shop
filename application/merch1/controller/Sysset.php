<?php
/**
 * 后台系统设置
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Sysset extends Base
{
	public function index()
    {
    	header('location: ' . url('merch/sysset/shop'));exit;
    }

	public function shop()
	{
		$merch=$this->merch;
		$item = Db::name('shop_store')->where('id',$merch['id'])->find();
		if (Request::instance()->isPost()) {
			if (empty(input('cateid'))) {
				show_json(0, '请选择店鋪分类!');
			}
			$lng = $lat = '';
			if(!empty(input('map/a')) && is_array(input('map/a')))
			{
				$lng = input('map/a')['lng'];
				$lat = input('map/a')['lat'];
			}
			$banner = input('banner/s');
			$data = array('merchname' => trim(input('merchname')), 'salecate' => trim(input('salecate')), 'realname' => trim(input('realname')), 'mobile' => trim(input('mobile')), 'address' => trim(input('address')), 'tel' => trim(input('tel')), 'lng' => $lng, 'lat' => $lat, 'accounttime' => strtotime(input('accounttime')), 'accounttotal' => input('accounttotal/d'), 'groupid' => input('groupid/d'), 'cateid' => input('cateid/d'), 'isrecommand' => input('isrecommand/d'), 'remark' => trim(input('remark')), 'desc' => trim(input('desc1')), 'logo' => trim(input('logo')), 'banner' => $banner, 'paymid' => input('paymid/d',0), 'payrate' => trim(input('payrate'), '%'));
			
			if (empty($item)) {
				show_json(0);
			} else {
				Db::name('shop_store')->where('id',$item['id'])->update($data);
				model('shop')->plog('merch.user.edit', '编辑店鋪 ID: ' . $data['id'] . ' 店鋪名: ' . $item['merchname']);
			}
			show_json(1, array('url' => url('merch/sysset/shop')));
		}
		$groups = model('store')->getGroups();
		$category = model('store')->getCategory();
		$this->assign(['item' => $item,'category'=>$category]);
		return $this->fetch('sysset/index');
	}

	public function dispatch()
	{
		$merch=$this->merch;
		$condition = ' 1 ';
		$enabled=input('enabled/d');
		$condition .= ' and merchid = ' . $merch['id'];
		$keyword = input('keyword/s');
		if($enabled != '') {
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
		return $this->fetch('sysset/dispatch/index');
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
		$merch=$this->merch;
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

			$data = array('merchid' => $merch['id'], 'displayorder' => intval($_POST['displayorder']), 'dispatchtype' => intval($_POST['dispatchtype']), 'isdefault' => intval($_POST['isdefault']), 'dispatchname' => trim($_POST['dispatchname']), 'express' => trim($_POST['express']), 'calculatetype' => trim($_POST['calculatetype']), 'firstprice' => trim($_POST['default_firstprice']), 'firstweight' => trim(max(0, $_POST['default_firstweight'])), 'secondprice' => trim($_POST['default_secondprice']), 'secondweight' => intval($_POST['default_secondweight']) <= 0 ? 1000 : trim($_POST['default_secondweight']), 'firstnumprice' => trim($_POST['default_firstnumprice']), 'firstnum' => $_POST['default_firstnum'], 'secondnumprice' => trim($_POST['default_secondnumprice']), 'secondnum' => $_POST['default_secondnum'], 'freeprice' => $_POST['default_freeprice'], 'areas' => iserializer($areas), 'nodispatchareas' => iserializer($_POST['nodispatchareas']), 'nodispatchareas_code' => iserializer($_POST['nodispatchareas_code']), 'isdispatcharea' => intval($_POST['isdispatcharea']), 'enabled' => intval($_POST['enabled']));

			if ($data['isdefault']) {
				Db::name('shop_dispatch')->where('merchid',$merch['id'])->setField('isdefault',0);
			}

			if (!empty($id)) {
				Db::name('shop_dispatch')->where('id',$id)->update($data);
				model('shop')->plog('shop.brand.dispatch.edit', '修改配送方式 ID: ' . $id);
			} else {
				$id = Db::name('shop_dispatch')->insertGetId($data);
				model('shop')->plog('shop.brand.dispatch.add', '添加配送方式 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/sysset/dispatchedit', array('id' => $id))));
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
		return $this->fetch('sysset/dispatch/post');
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
			model('shop')->plog('shop.brand.dispatch.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' ');
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
			model('shop')->plog('shop.brand.house.edit', ('修改配送方式状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . input('enabled/d')) == 1 ? '显示' : '隐藏');
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
			Db::name('shop_dispatch')->where('merchid',$merch['id'])->setField('isdefault',0);
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('isdefault',input('isdefault/d'));
			model('shop')->plog('shop.brand.dispatch.edit', ('设为默认配送方式<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . $_POST['isdefault']) == 1 ? '是' : '否');
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
			model('shop')->plog('shop.brand.dispatch.edit', '修改配送方式排序 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function dispatchtpl()
	{
		$random = random(16);
		ob_start();		
		$this->assign(['random'=>$random]);
		echo $this->fetch('sysset/dispatch/tpl');
		$contents = ob_get_contents();
		ob_clean();
		exit(json_encode(array('random' => $random, 'html' => $contents)));
	}

	public function notice()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' merchid = ' . $merch['id'];
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
		return $this->fetch('sysset/notice/index');
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
		$merch = $this->merch;
		if (Request::instance()->isPost()) {
			$data = array('link' => input('link/s'),'displayorder' => input('displayorder/d',0), 'title' => trim(input('title/s')), 'cate' => trim(input('cate/s')),  'thumb' => trim(input('thumb')), 'detail' => input('detail'), 'status' => input('status/d',0), 'createtime' => time(),'merchid'=>$merch['id']);

			if (!empty($id)) {
				Db::name('shop_notice')->where('id',$id)->update($data);
				model('shop')->plog('shop.brand.notice.edit', '修改公告 ID: ' . $id);
			} else {
				$id = Db::name('shop_notice')->insertGetId($data);
				model('shop')->plog('shop.brand.notice.add', '修改公告 ID: ' . $id);
			}
			show_json(1, array('url' => url('merch/sysset/notice')));
		}

		$notice = Db::name('shop_notice')->where('id',$id)->find();
		$this->assign(['notice'=>$notice]);
		return $this->fetch('sysset/notice/post');
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
			model('shop')->plog('shop.brand.notice.edit', '修改公告排序 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' 排序: ' . $displayorder . ' ');
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
			model('shop')->plog('shop.brand.notice.delete', '删除公告 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
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
			model('shop')->plog('shop.brand.notice.edit', ('修改公告状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function comment()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' c.deleted=0 and g.merchid=' . $merch['id'];
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
		return $this->fetch('sysset/comment/index');
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
			model('shop')->plog('shop.brand.comment.delete', '删除评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
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
				model('shop')->plog('shop.brand.comment.edit', '编辑商品虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}
			else {
				$id = Db::name('shop_order_comment')->insertGetId($data);
				model('shop')->plog('shop.brand.comment.add', '添加虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}

			show_json(1, array('url' => url('merch/sysset/comment')));
		}

		if (empty($goodsid)) {
			$goodsid = intval($item['goodsid']);
		}

		$goods = Db::name('shop_goods')->where('id',$goodsid)->field('id,thumb,title')->find();
		$this->assign(['goods'=>$goods,'item'=>$item]);
		return $this->fetch('sysset/comment/virtual');
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
				model('shop')->plog('shop.brand.comment.post', '回复商品评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			} else {
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
				model('shop')->plog('shop.brand.comment.post', $log_msg);
			}

			show_json(1, array('url' => url('merch/sysset/comment')));
		}
		$append_images = iunserializer($item['append_images']);
		$images = iunserializer($item['images']);
		$this->assign(['goods'=>$goods,'item'=>$item,'type'=>$type,'order'=>$order,'append_images'=>$append_images,'images'=>$images]);
		return $this->fetch('sysset/comment/post');
	}

	public function refundaddress()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' merchid = ' . $merch['id'];

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
		return $this->fetch('sysset/refundaddress/index');
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
		$merch = $this->merch;
		if (Request::instance()->isPost()) {
			$data = array();
			$data['merchid'] = $merch['id'];
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
				Db::name('shop_refund_address')->where('merchid',$merch['id'])->setField('isdefault',0);
			}

			if (!empty($id)) {
				model('shop')->plog('shop.brand.refundaddress.edit', '修改退货地址 ID: ' . $id);
				Db::name('shop_refund_address')->where('id',$id)->update($data);
			}
			else {
				$id = Db::name('shop_refund_address')->insertGetId($data);
				model('shop')->plog('shop.brand.refundaddress.add', '添加退货地址 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/sysset/refundaddress', array('op' => 'display'))));
		}

		if (!empty($id)) {
			$item = Db::name('shop_refund_address')->where('id',$id)->where('merchid',$merch['id'])->find();
		}

		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);
		$this->assign(['item'=>$item,'area_set'=>$area_set,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('sysset/refundaddress/post');
	}

	public function refundaddressdelete()
	{
		$id = input('id');
		$merch = $this->merch;
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_refund_address')->where('id','in',$id)->where('merchid',$merch['id'])->select();

		foreach ($items as $item) {
			Db::name('shop_refund_address')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.brand.refundaddress.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function refundaddresssetdefault()
	{
		$id = input('id');
		$merch = $this->merch;
		$isdefault = input('isdefault/d');
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		if ($isdefault == 1) {
			Db::name('shop_refund_address')->where('merchid',$merch['id'])->setField('isdefault',0);
		}

		$items = Db::name('shop_refund_address')->where('id','in',$id)->where('merchid',$merch['id'])->select();

		foreach ($items as $item) {
			Db::name('shop_refund_address')->where('id',$item['id'])->setField('isdefault',intval($isdefault));
			model('shop')->plog('shop.brand.refundaddress.edit', ('修改配送方式默认状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $isdefault) == 1 ? '是' : '否');
		}

		show_json(1, array('url' => referer()));
	}


}