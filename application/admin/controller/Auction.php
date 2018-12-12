<?php
/**
 * 拍卖
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Auction extends Base
{
	public function index()
	{
		return $this->fetch('');
	}

	public function banner()
	{
		$psize = 20;
		$condition = ' 1 ';
		$enabled = input('enabled');
		$enabled = input('keyword');
		if ($enabled != '') {
			$condition .= ' and enabled=' . intval($enabled);
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and bannername like "%' . $keyword . '%"';
		}
		$list = Db::name('shop_auction_banner')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('auction/banner/index');
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
		$id = input('id/d');

		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim(input('bannername')), 'link' => trim(input('link')), 'enabled' => input('enabled/d'), 'displayorder' => input('displayorder/d'), 'thumb' => trim(input('thumb')));

			if (!empty($id)) {
				Db::name('shop_auction_banner')->where('id',$id)->update($data);
				model('shop')->plog('auction.banner.edit', '修改幻灯片 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_auction_banner')->insertGetId($data);
				model('shop')->plog('auction.banner.add', '添加幻灯片 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/auction/banner')));
		}
		$item = Db::name('shop_auction_banner')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('auction/banner/post');
	}

	public function bannerdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_auction_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('auction.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function bannerdisplayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_auction_banner')->where('id',$id)->field('id,bannername')->select();

		if (!empty($item)) {
			Db::name('shop_auction_banner')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('auction.banner.delete', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function bannerenabled()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}
		$enabled = input('enabled/d');
		$items = Db::name('shop_auction_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_banner')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('auction.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function category()
	{
		$list = Db::name('shop_auction_goods_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('auction/category/index');
	}

	public function categoryadd()
	{
		$data = $this->categorypost();
		return $data;
	}

	public function categoryedit()
	{
		$data = $this->categorypost();
		return $data;
	}

	protected function categorypost()
	{
		$id = input('id/d');
		if (Request::instance()->isPost()) {
			$data = array('name' => trim(input('catename')), 'enabled' => intval(input('enabled')), 'isrecommand' => intval(input('isrecommand')), 'displayorder' => intval(input('displayorder')), 'thumb' => trim(input('thumb')));

			if (!empty($id)) {
				Db::name('shop_auction_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('auction.category.edit', '修改积分商城分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_auction_goods_category')->insertGetId($data);
				model('shop')->plog('auction.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/auction/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_auction_goods_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('auction/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_auction_goods_category')->where('id',$id)->field('id,name')->find();

		if (!empty($item)) {
			Db::name('shop_auction_goods_category')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('auction.category.delete', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_auction_goods_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		Db::name('shop_auction_goods_category')->where('id',$id)->delete();
		model('shop')->plog('auction.category.delete', '删除积分商城分类 ID: ' . $id . ' 标题: ' . $item['name'] . ' ');
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = intval(input('id'));
		$enabled = input('enabled/d',0);
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_auction_goods_category')->where('id','in',$id)->field('id,name')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_goods_category')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('auction.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function goods()
	{
		$psize = 20;
		$keyword = input('keyword');
		$status = input('status');
		$category = input('category');
		$condition = ' g.deleted = 0 ';
		$type = input('type');
		switch( $type ) 
		{
			case "sale": $condition .= " and g.status = 1 and g.q_mid = 0 and g.starttime < " . time() . " and g.endtime > " . time();
			break;
			case "wait": $condition .= " and g.status = 1 and g.q_mid = 0 and g.starttime > " . time();
			break;
			case "finish": $condition .= " and g.status = 1 and g.q_mid <> 0 ";
			break;
			case "auctions": $condition .= " and g.status = 1 and g.q_mid = 0 and g.endtime < " . time();
			break;
			case "store": $condition .= " and g.status != 1 ";
			break;
			default: $condition .= " and g.status = 1 and g.q_mid = 0 and g.starttime < " . time() . " and g.endtime > " . time();
		}
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and g.title like "%' . $keyword . '%"';
		}

		if ($status != '') {
			$condition .= ' AND g.status = ' . $status;
		}

		if ($category != '') {
			$condition .= ' AND g.category = ' . $category;
		}

		$list = Db::name('shop_auction_goods')
			->alias('g')
			->join('shop_auction_goods_category c','g.category = c.id','left')
			->where($condition)
			->order("g.displayorder",'desc')
			->field('g.*,c.name')
			->paginate($psize);
		$pager = $list->render();
		$categorys = Db::name('shop_auction_goods_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'categorys'=>$categorys,'type'=>$type,'status'=>$status,'category'=>$category,'keyword'=>$keyword]);
		return $this->fetch('auction/goods/index');
	}

	public function goodsadd()
	{
		$goodsdata = $this->goodspost();
		return $goodsdata;
	}

	public function goodsedit()
	{
		$goodsdata = $this->goodspost();
		return $goodsdata;
	}

	protected function goodspost()
	{
		$id = input('id/d');
		$item = Db::name('shop_auction_goods')
			->alias('g')
			->join('shop_auction_goods_category c','g.category = c.id','left')
			->field('g.*,c.name')
			->where('g.id',$id)
			->find();

		if (Request::instance()->isPost()) {
			$data = $_POST['goods'];
			
			if (empty($data['title'])) {
				show_json(0, '请填写商品标题');
			}
			if (empty($data['thumb'])) {
				show_json(0, '请上传商品图片');
			}
			if (empty($data['endtime'])) {
				show_json(0, '请填写商品结束时间');
			}
			if (empty($data['shprice'])) {
				show_json(0, '请填写商品起拍价格');
			}
			if (empty($data['bond'])) {
				show_json(0, '请填写商品保证金');
			}
			if (empty($data['addprice'])) {
				show_json(0, '请填写商品默认加价价格');
			}
			if (empty($data['starttime'])) {
				show_json(0, '请填写商品开始时间');
			}
			$data['content'] = htmlspecialchars_decode($data['content']);
			$data['starttime'] = strtotime($data['starttime']);
			$data['endtime'] =strtotime($data['endtime']);
			if (!empty($id)) {
				$goods_update = Db::name('shop_auction_goods')->where('id',$id)->update($data);

				if (!$goods_update) {
					show_json(0, '商品编辑失败！');
				}

				model('shop')->plog('auction.goods.edit', '编辑拍卖商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			} else {
				$data['createtime'] = time();
				$id = Db::name('shop_auction_goods')->insertGetId($data);

				if (!$id) {
					show_json(0, '商品添加失败！');
				}
				$gid = intval($data['gid']);

				if ($gid) {
					Db::name('shop_auction_goods')->where('id',$id)->setField('auctiontype',1);
				}

				model('shop')->plog('auction.goods.add', '添加拍卖商品 ID: ' . $id . '  <br/>商品名称: ' . $data['title']);
			}

			show_json(1, array('url' => url('admin/auction/goodsedit', array('id' => $id))));
		}
		$category = array();
		$category = Db::name('shop_auction_goods_category')->order('displayorder','desc')->select();
		$this->assign(['item'=>$item,'category'=>$category]);
		return $this->fetch('auction/goods/post');
	}

	public function goodsdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_auction_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_goods')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('auction.goods.delete', '删除积分商城商品 ID: ' . $item['id'] . '  <br/>商品名称: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('shop_auction_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_goods')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('auction.goods.edit', '修改积分商城商品 ' . $item['id'] . ' <br /> 状态: ' . ($status == 0 ? '下架' : '上架'));
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsproperty()
	{
		$id = intval(input('id'));
		$type = trim(input('type'));
		$value = intval(input('value'));

		if (in_array($type, array('status', 'displayorder', 'title'))) {
			Db::name('shop_auction_goods')->where('id',$id)->update(array($type => $value));
			$statusstr = '';

			if ($type == 'status') {
				$typestr = '上下架';
				$statusstr = ($value == 1 ? '上架' : '下架');
			} else {
				if ($type == 'displayorder') {
					$typestr = '排序';
					$statusstr = '序号 ' . $value;
				}
			}
			model('shop')->plog('auction.goods.edit', '修改积分商城商品' . $typestr . '状态   ID: ' . $id . ' ' . $statusstr . ' ');
		}

		show_json(1);
	}

	public function goodstotal()
	{
		$type = intval($_GET["type"]);
		$condition = " 1 ";
		if( $type == 1 ) 
		{
			$condition .= " and status = 1 and q_mid = 0 and starttime < " . time() . " and endtime > " . time();
		}
		else 
		{
			if( $type == 2 ) 
			{
				$condition .= " and status = 1 and q_mid = 0 and starttime > " . time();
			}
			else 
			{
				if( $type == 3 ) 
				{
					$condition .= " and status = 1 and q_mid <> 0 ";
				}
				else 
				{
					if( $type == 4 ) 
					{
						$condition .= " and status = 1 and q_mid = 0 and endtime < " . time();
					} 
					else 
					{
						if( $type == 5 ) 
						{
							$condition .= " and status != 1 ";
						} 
					}
				}
			}
		}
		$total = Db::name('shop_auction_goods')->where($condition)->count();
		echo json_encode($total);
	}

	public function set()
	{
		$data = model('common')->getPluginset('auction');
		if (Request::instance()->isPost()) {
			$data = (is_array($_POST['data']) ? $_POST['data'] : array());
			$data['openauction'] = intval($_POST['data']['openauction']);
			$data['explain'] = model('common')->html_images($_POST['data']['explain']);
			model('common')->updatePluginset(array('auction' => $data));
			model('shop')->plog('auction.set.edit', '修改积分商城基本设置');
			show_json(1, array('url' => url('admin/auction/set', array('tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}

		$this->assign(['data'=>$data]);
		return $this->fetch('auction/set');
	}

}