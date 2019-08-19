<?php
/**
 * 商户
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Shop extends Base
{	
	public function index()
	{
		$this->redirect(url('merch/shop/notice'));
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
		$merch = $this->merch;
		if (Request::instance()->isPost()) {
			$data = array('link' => input('link/s'),'displayorder' => input('displayorder/d',0), 'title' => trim(input('title/s')), 'cate' => trim(input('cate/s')),  'thumb' => trim(input('thumb')), 'detail' => input('detail'), 'status' => input('status/d',0), 'createtime' => time(),'merchid'=>$merch['id']);

			if (!empty($id)) {
				Db::name('shop_notice')->where('id',$id)->update($data);
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], $merch['id'], 'shop.notice.edit', '修改公告 ID: ' . $id);
			} else {
				$id = Db::name('shop_notice')->insertGetId($data);
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], $merch['id'], 'shop.notice.add', '修改公告 ID: ' . $id);
				model('notice')->push();
			}
			show_json(1, array('url' => url('merch/shop/notice')));
		}

		$notice = Db::name('shop_notice')->where('id',$id)->find();
		$this->assign(['notice'=>$notice]);
		return $this->fetch('shop/notice/post');
	}

	public function noticepush()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$notice = Db::name('shop_notice')->where('id',$id)->find();
		model('notice')->sendShopNotice($notice['id']);
		show_json(1, array('url' => referer()));
	}

	public function noticedisplayorder()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$displayorder = input('value/d');
		$item = Db::name('shop_notice')->where('id',$id)->field('id,title')->find();

		if (!empty($item)) {
			Db::name('shop_notice')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], $merch['id'], 'shop.notice.edit', '修改公告排序 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function noticedelete()
	{
		$id = input('id/d');
		$merch = $this->merch;

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_notice')->where('id',$item['id'])->delete();
			model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], $merch['id'], 'shop.notice.delete', '删除公告 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function noticestatus()
	{
		$id = input('id/d');
		$merch = $this->merch;

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_notice')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], $merch['id'], 'shop.notice.edit', ('修改公告状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function comment()
    {
    	$merch = $this->merch;
    	$psize = 20;
		$condition = ' c.deleted=0 and g.merchid= ' . $merch['id'];
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
		$merch = $this->merch;
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_order_comment')->where('id','in',$id)->field('id')->select();

		foreach ($items as $item) {
			Db::name('shop_order_comment')->where('id',$item['id'])->setField('deleted',1);
			$goods = Db::name('shop_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
			model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], 'shop.comment.delete', '删除评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
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
		$merch = $this->merch;
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
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], 'shop.comment.edit', '编辑商品虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}
			else {
				$id = Db::name('shop_order_comment')->insertGetId($data);
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], 'shop.comment.add', '添加虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}

			show_json(1, array('url' => url('merch/shop/comment')));
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
		$merch = $this->merch;
		$type = input('type');
		$item = Db::name('shop_order_comment')->where('id',$id)->find();
		$goods = Db::name('shop_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
		$order = Db::name('shop_order')->where('id',$item['orderid'])->field('id,ordersn')->find();

		if (Request::instance()->isPost()) {
			if ($type == 0) {
				$data = array('reply_content' => $_POST['reply_content'], 'reply_images' => is_array($_POST['reply_images']) ? iserializer(model('common')->array_images($_POST['reply_images'])) : iserializer(array()), 'append_reply_content' => $_POST['append_reply_content'], 'append_reply_images' => is_array($_POST['append_reply_images']) ? iserializer($_POST['append_reply_images']) : iserializer(array()));
				Db::name('shop_order_comment')->where('id',$id)->update($data);
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], 'shop.comment.post', '回复商品评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
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
				model('shop')->mplog($merch['id'], $merch['id'], $merch['id'], 'shop.comment.post', $log_msg);
			}

			show_json(1, array('url' => url('merch/shop/comment')));
		}
		$append_images = iunserializer($item['append_images']);
		$images = iunserializer($item['images']);
		$this->assign(['goods'=>$goods,'item'=>$item,'type'=>$type,'order'=>$order,'append_images'=>$append_images,'images'=>$images]);
		return $this->fetch('shop/comment/post');
	}

	public function refundaddress()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' merchid= ' . $merch['id'];

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
				model('shop')->mplog($merch['id'], $merch['id'], 'shop.refundaddress.edit', '修改退货地址 ID: ' . $id);
				Db::name('shop_refund_address')->where('id',$id)->update($data);
			}
			else {
				$id = Db::name('shop_refund_address')->insertGetId($data);
				model('shop')->mplog($merch['id'], $merch['id'], 'shop.refundaddress.add', '添加退货地址 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/shop/refundaddress', array('op' => 'display'))));
		}

		if (!empty($id)) {
			$item = Db::name('shop_refund_address')->where('id',$id)->where('merchid',$merch['id'])->find();
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
		$merch = $this->merch;
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_refund_address')->where('id','in',$id)->where('merchid',$merch['id'])->select();

		foreach ($items as $item) {
			Db::name('shop_refund_address')->where('id',$item['id'])->delete();
			model('shop')->mplog($merch['id'], $merch['id'], 'shop.refundaddress.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
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
			model('shop')->mplog($merch['id'], $merch['id'], 'shop.refundaddress.edit', ('修改配送方式默认状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . $isdefault) == 1 ? '是' : '否');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatch()
	{
		$condition = ' 1 ';
		$merch = $this->merch;
		$enabled=input('enabled/d');
		$condition .= ' and merchid = ' . $merch['id'];
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
		$merch = $this->merch;
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
				model('shop')->mplog($merch['id'], 'shop.dispatch.edit', '修改配送方式 ID: ' . $id);
			} else {
				$id = Db::name('shop_dispatch')->insertGetId($data);
				model('shop')->mplog($merch['id'], 'shop.dispatch.add', '添加配送方式 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/shop/dispatchedit', array('id' => $id))));
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
		$merch = $this->merch;
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->delete();
			model('shop')->mplog($merch['id'], 'shop.dispatch.delete', '删除配送方式 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchenabled()
	{
		$id = input('id/d');
		$merch = $this->merch;
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_dispatch')->where('id','in',$id)->field('id,dispatchname')->select();

		foreach ($items as $item) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('enabled',input('enabled/d'));
			model('shop')->mplog($merch['id'], 'shop.house.edit', ('修改配送方式状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . input('enabled/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchsetdefault()
	{
		$id = input('id/d');
		$merch = $this->merch;
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
			model('shop')->mplog($merch['id'], 'shop.dispatch.edit', ('设为默认配送方式<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['dispatchname'] . '<br/>状态: ' . $_POST['isdefault']) == 1 ? '是' : '否');
		}

		show_json(1, array('url' => referer()));
	}

	public function dispatchdisplayorder()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$displayorder = intval(input('value'));
		$item = Db::name('shop_dispatch')->where('id','eq',$id)->field('id,dispatchname')->find();

		if (!empty($item)) {
			Db::name('shop_dispatch')->where('id',$item['id'])->setField('displayorder',$displayorder);
			model('shop')->mplog($merch['id'], 'shop.dispatch.edit', '修改配送方式排序 ID: ' . $item['id'] . ' 标题: ' . $item['dispatchname'] . ' 排序: ' . $displayorder . ' ');
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

	public function cityexpress()
    {
    	$merch = $this->merch;
		$condition = ' merchid= ' . $merch['id'];
		$cityexpress = Db::name('shop_city_express')->where($condition)->find();
		if (!empty($cityexpress)) {
			$config = unserialize($cityexpress['config']);

			if ($cityexpress['express_type'] == 1) {
				$cityexpress['app_key'] = $config['app_key'];
				$cityexpress['app_secret'] = $config['app_secret'];
				$cityexpress['source_id'] = $config['source_id'];
				$cityexpress['shop_no'] = $config['shop_no'];
				$cityexpress['city_code'] = $config['city_code'];
			}
		}
		if (Request::instance()->isPost()) {
			$data = array('merchid' => $merch['id'], 'start_fee' => floatval(round($_POST['start_fee'], 2)), 'start_km' => intval($_POST['start_km']), 'pre_km' => intval($_POST['pre_km']), 'pre_km_fee' => floatval(round($_POST['pre_km_fee'], 2)), 'fixed_km' => intval($_POST['fixed_km']), 'fixed_fee' => floatval(round($_POST['fixed_fee'], 2)), 'receive_goods' => intval($_POST['receive_goods']), 'geo_key' => trim($_POST['geo_key']), 'lat' => trim($_POST['lat']), 'lng' => trim($_POST['lng']), 'range' => intval($_POST['range']), 'zoom' => intval($_POST['zoom']), 'express_type' => intval($_POST['express_type']), 'config' => iserializer($this->TrimArray($_POST['config'])), 'tel1' => trim($_POST['tel1']), 'tel2' => trim($_POST['tel2']), 'is_sum' => trim($_POST['is_sum']), 'is_dispatch' => trim($_POST['is_dispatch']), 'enabled' => intval($_POST['enabled']));

			if (!empty($cityexpress)) {
				model('shop')->mplog($merch['id'], 'shop.cityexpress.edit', '修改同城配送 ID: ' . $cityexpress['id']);
				Db::name('shop_city_express')->where('id',$cityexpress['id'])->update($data);
			}
			else {
				$id = Db::name('shop_city_express')->insertGetId($data);
				model('shop')->mplog($merch['id'], 'shop.cityexpress.add', '添加同城配送 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/shop/cityexpress')));
		}
		$this->assign(['cityexpress'=>$cityexpress]);
    	return $this->fetch('shop/cityexpress/index');
    }

    public function TrimArray($arr)
	{
		foreach ($arr as $key => $val) {
			$arr[$key] = trim($val);
		}

		return $arr;
	}

	public function store()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' merchid = ' . $merch['id'];
		$keyword = trim($_GET['keyword']);
		$type = intval($_GET['type']);
		if (!empty($keyword)) {
			$condition .= ' AND (storename LIKE \'%' . $keyword . '%\' OR address LIKE \'%' . $keyword . '%\' OR tel LIKE \'%' . $keyword . '%\')';
		}

		if (!empty($type)) {
			$condition .= ' AND type = ' . $type;
		}

		$list = Db::name('shop_store')->where($condition)->order('displayorder desc,id desc')->paginate($psize);

		if(!empty($list)) {
			foreach ($list as $k => $value) { 
				$row['salercount'] = Db::name('shop_saler')->where('storeid = ' . $value['id'])->count();
				$data = array();
	    		$data = $value;
	    		$list->offsetSet($k,$data);
			}
		}
		unset($value);
		$this->assign(['list'=>$list,'pager'=>$pager,'type'=>$type,'keyword'=>$keyword]);
		return $this->fetch('shop/store/index');
	}

	public function storeadd()
	{
		$storedata = $this->storepost();
		return $storedata;
	}

	public function storeedit()
	{
		$storedata = $this->storepost();
		return $storedata;
	}

	protected function storepost()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);

		if (Request::instance()->isPost()) {
			if (!empty($_POST['perms'])) {
				$perms = implode(',', $_POST['perms']);
			} else {
				$perms = '';
			}

			if (empty($_POST['logo'])) {
				show_json(0, '门店LOGO不能为空');
			}

			if (empty($_POST['map']['lng']) || empty($_POST['map']['lat'])) {
				show_json(0, '门店位置不能为空');
			}

			if (empty($_POST['address'])) {
				show_json(0, '门店地址不能为空');
			} else {
				if (30 < mb_strlen($_POST['address'], 'UTF-8')) {
					show_json(0, '门店地址不能超过30个字符');
				}
			}

			$label = '';

			if (!empty($_POST['lab'])) {
				if (8 < count($_POST['lab'])) {
					show_json(0, '标签不能超过8个');
				}

				foreach ($_POST['lab'] as $lab) {
					if (20 < mb_strlen($lab, 'UTF-8')) {
						show_json(0, '标签长度不能超过20个字符');
					}

					if (strlen(trim($lab)) <= 0) {
						show_json(0, '标签不能为空');
					}
				}
				$label = implode(',', $_POST['lab']);
			}

			$tag = '';

			if (!empty($_POST['tag'])) {
				if (3 < count($_POST['tag'])) {
					show_json(0, '角标不能超过3个');
				}

				foreach ($_POST['tag'] as $tg) {
					if (3 < mb_strlen($tg, 'UTF-8')) {
						show_json(0, '角标长度不能超过3个字符');
					}

					if (strlen(trim($tg)) <= 0) {
						show_json(0, '角标不能为空');
					}
				}

				$tag = implode(',', $_POST['tag']);
			}

			$cates = '';

			if (!empty($_POST['cates'])) {
				if (3 < count($_POST['cates'])) {
					show_json(0, '门店分类不能超过3个');
				}
				$cates = implode(',', $_POST['cates']);
			}

			if (empty($_POST['tel']) || strlen(trim($_POST['tel'])) <= 0) {
				show_json(0, '门店电话不能为空');
			} else {
				if (20 < strlen($_POST['tel'])) {
					show_json(0, '门店电话不能大于20个字符');
				}
			}

			if (!empty($_POST['saletime'])) {
				if (20 < strlen($_POST['saletime'])) {
					show_json(0, '营业时间不能大于20个字符');
				}
			}

			$data = array('merchid' => $merch['id'], 'storename' => trim($_POST['storename']), 'address' => trim($_POST['address']), 'province' => trim($_POST['province']), 'city' => trim($_POST['city']), 'area' => trim($_POST['area']), 'provincecode' => trim($_POST['chose_province_code']), 'citycode' => trim($_POST['chose_city_code']), 'areacode' => trim($_POST['chose_area_code']), 'tel' => trim($_POST['tel']), 'lng' => $_POST['map']['lng'], 'lat' => $_POST['map']['lat'], 'type' => intval($_POST['type']), 'realname' => trim($_POST['realname']), 'mobile' => trim($_POST['mobile']), 'label' => $label, 'tag' => $tag, 'fetchtime' => trim($_POST['fetchtime']), 'saletime' => trim($_POST['saletime']), 'logo' => trim($_POST['logo']), 'desc' => trim($_POST['desc']), 'opensend' => intval($_POST['opensend']), 'status' => intval($_POST['status']), 'cates' => $cates, 'perms' => $perms);

			$data['order_printer'] = is_array($_POST['order_printer']) ? implode(',', $_POST['order_printer']) : '';
			$data['order_template'] = intval($_POST['order_template']);
			$data['ordertype'] = is_array($_POST['ordertype']) ? implode(',', $_POST['ordertype']) : '';

			if (!empty($id)) {
				Db::name('shop_store')->where('id',$id)->update($data);
				model('shop')->mplog($merch['id'],'shop.verify.store.edit', '编辑门店 ID: ' . $id);
			} else {
				$id = Db::name('shop_store')->insertGetId($data);
				model('shop')->mplog($merch['id'],'shop.verify.store.add', '添加门店 ID: ' . $id);
			}
			show_json(1, array('url' => url('merch/shop/store')));
		}

		$item = Db::name('shop_store')->where('id',$id)->find();
		$perms = explode(',', $item['perms']);

		$label = explode(',', $item['label']);
		$tag = explode(',', $item['tag']);
		$cates = explode(',', $item['cates']);
		$this->assign(['item'=>$item,'perms'=>$perms,'label'=>$label,'tag'=>$tag,'cates'=>$cates,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('shop/store/post');
	}

	public function storedelete()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_store')->where('id in( ' . $id . ' )')->field('id,storename')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id = ' . $item['id'])->delete();
			model('shop')->mplog($merch['id'],'shop.verify.store.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function storedisplayorder()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$displayorder = intval(input('value'));
		$item = Db::name('shop_store')->where('id = ' . $id)->field('id,storename')->find();
		if (!empty($item)) {
			Db::name('shop_store')->where('id = ' . $id)->update(array('displayorder' => $displayorder));
			model('shop')->mplog($merch['id'],'shop.verify.store.edit', '修改门店排序 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function storestatus()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$status = intval(input('status'));
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_store')->where('id in( ' . $id . ' )')->field('id,storename')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id = ' . $item['id'])->update(array('status' => intval($status)));
			model('shop')->mplog($merch['id'],'shop.verify.store.edit', '修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['storename'] . '<br/>状态: ' . $status == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function storequery()
	{
		$kwd = trim($_POST['keyword']);
		$merch = $this->merch;
		$limittype = empty($_POST['limittype']) ? 0 : intval($_POST['limittype']);
		$condition = ' status=1 and merchid = ' . $merch['id'];

		if ($limittype == 0) {
			$condition .= '  and type in (1,2,3) ';
		}

		if (!empty($kwd)) {
			$condition .= ' AND `storename` LIKE "%' . $kwd . '%"';
		}

		$ds = Db::name('shop_store')->where($condition)->field('id,storename')->order('id asc')->select();
		if ($_POST['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('shop/store/query');
		exit();
	}

	public function saler()
	{
		$merch = $this->merch;		
		$condition = ' s.merchid = ' . $merch['id'];
		$status = input('status');
		$keyword = input('keyword');
		if ($status != '') {
			$condition .= ' and s.status = ' . $status;
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and ( s.salername like "%' . $keyword . '%" or m.realname like "%' . $keyword . '%" or m.mobile like "%' . $keyword . '%" or m.nickname like "%' . $keyword . '%")';
		}

		$list = Db::name('shop_saler')->alias('s')->join('member m','s.mid=m.id','left')->join('shop_store store','store.id=s.storeid','left')->where($condition)->field('s.*,m.nickname,m.avatar,m.realname,store.storename')->order('s.id asc')->paginate(20);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('shop/saler/index');
	}

	public function saleradd()
	{
		$data = $this->salerpost();
		return $data;
	}

	public function saleredit()
	{
		$data = $this->salerpost();
		return $data;
	}

	protected function salerpost()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$item = Db::name('shop_saler')->where('id = ' . $id)->find();
		$saler = array();
		if (!empty($item)) {
			$saler = model('member')->getMember($item['mid']);
			$store = Db::name('shop_store')->where('id = ' . $item['storeid'])->find();
		}

		if (Request::instance()->isPost()) {
			$data = array('merchid' => $merch['id'],'storeid' => intval($_POST['storeid']), 'mid' => trim($_POST['mid']), 'status' => intval($_POST['status']), 'salername' => trim($_POST['salername']), 'mobile' => trim($_POST['mobile']), 'roleid' => intval($_POST['roleid']));

			if (empty($data['storeid'])) {
				show_json(0, '请选择所属门店');
			}

			if (empty($item['username'])) {
				if (empty($_POST['username'])) {
					show_json(0, '用户名不能为空!');
				}

				$usernames = Db::name('shop_saler')->where('username',$_POST['username'])->count();

				if (0 < $usernames) {
					show_json(0, '该用户名已被使用，请修改后重新提交!');
				}

				$data['username'] = $_POST['username'];
			}

			if (!empty($_POST['pwd'])) {
				$salt = random(8);
				while (1) {
					$saltcount = Db::name('shop_saler')->where('salt',$salt)->count();
					if ($saltcount <= 0) {
						break;
					}

					$salt = random(8);
				}

				$pwd = md5(trim($_POST['pwd']) . $salt);
				$data['pwd'] = $pwd;
				$data['salt'] = $salt;
			} else {
				if (empty($item)) {
					show_json(0, '用户密码不能为空!');
				}
			}

			$m = model('member')->getMember($data['mid']);

			if (!empty($id)) {
				Db::name('shop_saler')->where('id',$id)->update($data);
				model('shop')->mplog($merch['id'],'shop.verify.saler.edit', '编辑店员 ID: ' . $id . ' <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['mid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
			} else {
				$scount = Db::name('shop_saler')->where('mid',$data['mid'])->count();

				if (0 < $scount) {
					show_json(0, '此会员已经成为店员，没法重复添加');
				}

				$id = Db::name('shop_saler')->insertGetId($data);
				model('shop')->mplog($merch['id'],'shop.verify.saler.add', '添加店员 ID: ' . $id . '  <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['mid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
			}

			show_json(1, array('url' => url('merch/shop/saler')));
		}
		$stores = Db::name('shop_store')->where('status = 1 and merchid = ' . $merch['id'])->field('id,storename')->order('id asc')->select();
		$this->assign(['item'=>$item,'saler'=>$saler,'stores'=>$stores]);
		return $this->fetch('shop/saler/post');
	}

	public function salerdelete()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_saler')->where('id in( ' . $id . ' )')->field('id,salername')->select();
		foreach ($items as $item) {
			Db::name('shop_saler')->where('id',$item['id'])->delete();
			model('shop')->mplog($merch['id'],'shop.verify.saler.delete', '删除店员 ID: ' . $item['id'] . ' 店员名称: ' . $item['salername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function salerstatus()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		$merch = $this->merch;
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_saler')->where('id in( ' . $id . ' )')->field('id,salername')->select();
		foreach ($items as $item) {
			Db::name('shop_saler')->where('id',$item['id'])->update(array('status' => intval($status)));
			model('shop')->mplog($merch['id'],'shop.verify.saler.edit', '修改店员状态<br/>ID: ' . $item['id'] . '<br/>店员名称: ' . $item['salername'] . '<br/>状态: ' . $status == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function salerquery()
	{
		$kwd = trim($_POST['keyword']);
		$merch = $this->merch;
		$condition = ' 1 ';
		if (!empty($kwd)) {
			$condition .= ' AND ( m.nickname LIKE "%' . $kwd . '%" or m.realname LIKE "%' . $kwd . '%" or m.mobile LIKE "%' . $kwd . '%" or store.storename like "%' . $kwd . '%" )';
		}

		$ds = Db::name('shop_saler')->alias('s')->join('member m','s.mid=m.mid','left')->join('shop_store store','store.id=s.storeid','left')->where($condition)->field('s.*,m.nickname,m.avatar,m.mobile,m.realname,store.storename')->order('id asc')->select();
		$this->assign(['ds'=>$ds]);
		return $this->fetch('shop/saler/query');
		exit();
	}
	
	public function ajax()
	{
		$merch = $this->merch;
		$goods_totals = Db::name('shop_goods')->where('merchid = ' . $merch['id'] . ' and status=1 and deleted=0 and total<=0 and total<>-1 ')->count();

		show_json(1, array('goods_totals' => $goods_totals));

	}
}