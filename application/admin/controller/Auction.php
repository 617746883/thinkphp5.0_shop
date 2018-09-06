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
		$list = Db::name('shop_auction_category')->order('displayorder','desc')->select();
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
				Db::name('shop_auction_category')->where('id',$id)->update($data);
				model('shop')->plog('auction.category.edit', '修改积分商城分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_auction_category')->insertGetId($data);
				model('shop')->plog('auction.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/auction/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_auction_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('auction/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_auction_category')->where('id',$id)->field('id,name')->find();

		if (!empty($item)) {
			Db::name('shop_auction_category')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('auction.category.delete', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_auction_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		Db::name('shop_auction_category')->where('id',$id)->delete();
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

		$items = Db::name('shop_auction_category')->where('id','in',$id)->field('id,name')->select();

		foreach ($items as $item) {
			Db::name('shop_auction_category')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('auction.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function goodstotal()
	{
		$type = intval(input('type'));
		$condition = ' isauction = 1 ';

		if ($type == 1) {
			$condition .= ' and deleted = 0 and total > 0 and status = 1 ';
		} else if ($type == 2) {
			$condition .= ' and deleted = 0 and total = 0 and status = 1';
		} else if ($type == 3) {
			$condition .= ' and deleted = 0 and status = 0 ';
		} else {
			if ($type == 4) {
				$condition .= ' and deleted = 1 ';
			}
		}

		$total = Db::name('shop_goods')->where($condition)->count();
		echo json_encode($total);
	}

	public function goods()
	{
		$psize = 20;
		$type = input('type');
		$keyword = input('keyword');
		$status = input('status');
		$category = input('category');

		$condition = ' g.isauction = 1 ';

		switch ($type) {
		case 'sale':
			$condition .= ' and g.deleted = 0 and g.total > 0 and g.status = 1 ';
			break;

		case 'sold':
			$condition .= ' and g.deleted = 0 and g.total <= 0 and g.status = 1 ';
			break;

		case 'store':
			$condition .= ' and g.deleted = 0 and g.status = 0 ';
			break;

		case 'recycle':
			$condition .= ' and g.deleted = 1 ';
			break;

		default:
			$condition .= ' and g.deleted = 0 and g.total > 0 and g.status = 1 ';
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

		$list = Db::name('shop_goods')
			->alias('g')
			->join('shop_goods_category c','g.category = c.id','left')
			->where($condition)
			->order("g.displayorder",'desc')
			->field('g.*,c.name')
			->paginate($psize);
		$pager = $list->render();
		$category = array();
		$this->assign(['list'=>$list,'pager'=>$pager,'type'=>$type,'status'=>$status,'category'=>$category,'keyword'=>$keyword]);
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
		$item = Db::name('shop_goods')
			->alias('g')
			->join('shop_goods_category c','g.category = c.id','left')
			->field('g.*,c.name')
			->where('g.id',$id)
			->find();
		$category = array();

		if (!empty($item['thumb'])) {
			$piclist = array_merge(array($item['thumb']), iunserializer($item['thumb_url']));
		}

		$stores = array();

		if (!empty($item['storeids'])) {
			$stores = Db::name('shop_store')->where('id','in',$item['storeids'])->field('id,storename')->select();
		}

		$dispatch_data = Db::name('shop_dispatch')->where('enabled',1)->order('displayorder','desc')->select();

		if (Request::instance()->isPost()) {
			$data = array('isauction' => 1, 'displayorder' => input('displayorder'), 'gid' => input('gid'), 'title' => trim(input('title')), 'category' => intval(input('category')), 'thumb' => '', 'thumb_url' => '', 'marketprice' => input('marketprice/f'), 'auctionprice' => input('auctionprice/f'), 'single' => input('single/d'), 'singleprice' => input('singleprice/f'), 'goodsnum' => input('goodsnum/d') < 1 ? 1 : input('goodsnum/d'), 'purchaselimit' => input('purchaselimit/d'), 'unit' => trim(input('unit')), 'total' => input('total/d'), 'showtotal' => input('showtotal/d'), 'sales' => input('sales/d'), 'teamnum' => input('teamnum/s'), 'dispatchtype' => input('dispatchtype'), 'dispatchprice' => input('dispatchprice/f'), 'status' => input('status/d'), 'isindex' => input('isindex/d'), 'isrecommand' => input('isrecommand/d',0), 'groupnum' => input('groupnum/d'), 'endtime' => input('endtime'), 'description' => trim(input('description')), 'goodssn' => trim(input('goodssn')), 'productsn' => trim(input('productsn')), 'content' => model('common')->html_images(input('content')), 'createtime' => time(), 'goodsid' => input('gid/d',0), 'isdiscount' => input('isdiscount/d'), 'discount' => input('discount/d',0), 'headstype' => input('headstype/d'), 'headsmoney' => input('headsmoney/f'), 'headsdiscount' => input('headsdiscount/d'), 'storeids' => is_array($_POST['storeids']) ? implode(',', $_POST['storeids']) : '');

			if ($data['auctionprice'] < $data['headsmoney']) {
				$data['headsmoney'] = $data['auctionprice'];
			}

			if (!empty($data['verifytype']) && ($data['verifynum'] < 1)) {
				$data['verifynum'] = 1;
			}

			if ($data['headsmoney'] < 0) {
				$data['headsmoney'] = 0;
			}

			if ($data['headsdiscount'] < 0) {
				$data['headsdiscount'] = 0;
			}

			if (100 < $data['headsdiscount']) {
				$data['headsdiscount'] = 100;
			}

			if ($data['goodsnum'] < 0) {
				show_json(0, '数量不能小于1！');
			}

			if ($data['groupnum'] < 2) {
				show_json(0, '开团人数至少为2人！');
			}

			if ($data['endtime'] < 1) {
				show_json(0, '组团限时不能小于1小时！');
			}

			if ($data['auctionprice'] <= 0) {
				show_json(0, '拼团价格不符合要求！');
			}

			if (($data['singleprice'] <= 0) && ($data['single'] == 1)) {
				show_json(0, '单购价格不符合要求！');
			}

			$data['title'] = empty($data['goodstype']) ? trim(input('goodsid_text')) : trim(input('couponid_text'));

			if (is_array($_POST['thumbs'])) {
				$thumbs = $_POST['thumbs'];
				$thumb_url = array();

				foreach ($thumbs as $th) {
					$thumb_url[] = trim($th);
				}

				$data['thumb'] = $thumb_url[0];
				unset($thumb_url[0]);
				$data['thumb_url'] = iserializer($thumb_url);
			}

			if (!empty($id)) {
				$goods_update = Db::name('shop_goods')->where('id',$id)->update($data);

				if (!$goods_update) {
					show_json(0, '商品编辑失败！');
				}

				model('shop')->plog('auction.goods.edit', '编辑拼团商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			}
			else {
				$id = Db::name('shop_goods')->insertGetId($data);

				if (!$id) {
					show_json(0, '商品添加失败！');
				}
				$gid = intval($data['gid']);

				if ($gid) {
					Db::name('shop_goods')->where('id',$id)->setField('auctiontype',1);
				}

				model('shop')->plog('auction.goods.add', '添加拼团商品 ID: ' . $id . '  <br/>商品名称: ' . $data['title']);
			}

			show_json(1, array('url' => url('admin/auction/goodsedit', array('op' => 'post', 'id' => $id, 'tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}
		$category = model('shop')->getFullCategory(true, true);
		$this->assign(['item'=>$item,'category'=>$category,'stores'=>$stores,'dispatch_data'=>$dispatch_data,'piclist'=>$piclist]);
		return $this->fetch('auction/goods/post');
	}

}