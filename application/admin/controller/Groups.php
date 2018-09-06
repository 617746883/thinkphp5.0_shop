<?php
/**
 * 团购
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Groups extends Base
{
	public function index()
	{
		$condition = ' o.isgroups = 1 and o.deleted = 0 and o.status = 1 and (o.success = 1 or o.is_team = 0) ';
		$order_ok = Db::name('shop_order')
			->alias('o')
			->join('shop_goods g','g.id = o.goodsid','left')
			->join('member m','m.id=o.mid','left')
			->where($condition)
			->order('o.createtime','desc')
			->field('o.*,g.title,g.ccate,g.marketprice,g.thumb,m.nickname,m.realname,m.mobile')
			->limit(0,10)
			->select();
		$this->assign(['order_ok'=>$order_ok]);
		return $this->fetch('');
	}

	/**
	 * ajax return 交易订单
	 */
	public function ajaxorder()
	{
		$day = input('day/d');
		$orderPrice = $this->selectOrderPrice($day);
		$orderPrice['avg'] = empty($orderPrice['count_avg']) ? 0 : number_format($orderPrice['price'] / $orderPrice['count_avg'], 2);
		$orderPrice['price'] = number_format($orderPrice['price'], 2);
		$orderPrice['count'] = number_format($orderPrice['count']);
		unset($orderPrice['fetchall']);
		echo json_encode($orderPrice);
	}

	/**
	 * 查询订单金额
	 * @param int $day 查询天数
	 * @return bool
	 */
	protected function selectOrderPrice($day = 0)
	{
		$day = (int) $day;

		if ($day == 1) {
			$createtime1 = strtotime(date('Y-m-d', time() - (3600 * 24)));
			$createtime2 = strtotime(date('Y-m-d', time()));
		}
		else if (1 < $day) {
			$createtime1 = strtotime(date('Y-m-d', time() - ($day * 3600 * 24)));
			$createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
		}
		else {
			$createtime1 = strtotime(date('Y-m-d', time()));
			$createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
		}

		$pdo_res = Db::name('shop_order')->where('isgroups',1)->where('status','>',0)->where('paytime','between',[$createtime1,$createtime2])->field('id,price,freight,starttime,mid')->select();
		$price = 0;

		foreach ($pdo_res as $key => $value) {
			$price += floatval($value['price'] + $value['freight']);
		}

		$new1 = '';

		if (!empty($pdo_res)) {
			foreach ($pdo_res as $k => $na) {
				$new[$k] = serialize($na['mid']);
			}

			$uniq = array_unique($new);

			foreach ($uniq as $k => $ser) {
				$new1[$k] = unserialize($ser);
			}
		}

		$result = array('price' => $price, 'count' => count($pdo_res), 'count_avg' => count($new1), 'fetchall' => $pdo_res);
		return $result;
	}

	/**
	 * ajax return 拼团订单
	 */
	public function ajaxteam()
	{
		$success = input('success/d');
		$orderPrice = $this->selectTeamPrice($success);
		$orderPrice['price'] = number_format($orderPrice['price'], 2);
		$orderPrice['count'] = number_format($orderPrice['count']);
		unset($orderPrice['fetchall']);
		echo json_encode($orderPrice);
	}

	/**
	 * 查询订单金额
	 * @param int $day 查询天数
	 * @return bool
	 */
	protected function selectteamPrice($success = 0)
	{
		$success = intval($success);
		$pdo_res = Db::name('shop_order')->where('isgroups',1)->where('paytime','>',0)->where('is_team',1)->where('success',$success)->field('id,price,freight,starttime')->select();
		$price = 0;

		foreach ($pdo_res as $key => $value) {
			$price += floatval($value['price'] + $value['freight']);
		}

		$result = array('price' => $price, 'count' => count($pdo_res), 'fetchall' => $pdo_res);
		return $result;
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
		$list = Db::name('shop_groups_banner')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('groups/banner/index');
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
				Db::name('shop_groups_banner')->where('id',$id)->update($data);
				model('shop')->plog('groups.banner.edit', '修改幻灯片 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_groups_banner')->insertGetId($data);
				model('shop')->plog('groups.banner.add', '添加幻灯片 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/groups/banner')));
		}
		$item = Db::name('shop_groups_banner')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('groups/banner/post');
	}

	public function bannerdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_groups_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_groups_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('groups.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function bannerdisplayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_groups_banner')->where('id',$id)->field('id,bannername')->select();

		if (!empty($item)) {
			Db::name('shop_groups_banner')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('groups.banner.delete', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
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
		$items = Db::name('shop_groups_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_groups_banner')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('groups.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function category()
	{
		$list = Db::name('shop_groups_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('groups/category/index');
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
				Db::name('shop_groups_category')->where('id',$id)->update($data);
				model('shop')->plog('groups.category.edit', '修改积分商城分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_groups_category')->insertGetId($data);
				model('shop')->plog('groups.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/groups/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_groups_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('groups/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_groups_category')->where('id',$id)->field('id,name')->find();

		if (!empty($item)) {
			Db::name('shop_groups_category')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('groups.category.delete', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_groups_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		Db::name('shop_groups_category')->where('id',$id)->delete();
		model('shop')->plog('groups.category.delete', '删除积分商城分类 ID: ' . $id . ' 标题: ' . $item['name'] . ' ');
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = intval(input('id'));
		$enabled = input('enabled/d',0);
		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_groups_category')->where('id','in',$id)->field('id,name')->select();

		foreach ($items as $item) {
			Db::name('shop_groups_category')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('groups.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function goods()
	{
		$psize = 20;
		$type = input('type');
		$keyword = input('keyword');
		$status = input('status');
		$category = input('category');

		$condition = ' g.isgroups = 1 ';

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
		return $this->fetch('groups/goods/index');
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
			$data = array('isgroups' => 1, 'displayorder' => input('displayorder'), 'gid' => input('gid'), 'title' => trim(input('title')), 'category' => intval(input('category')), 'thumb' => '', 'thumb_url' => '', 'marketprice' => input('marketprice/f'), 'groupsprice' => input('groupsprice/f'), 'single' => input('single/d'), 'singleprice' => input('singleprice/f'), 'goodsnum' => input('goodsnum/d') < 1 ? 1 : input('goodsnum/d'), 'purchaselimit' => input('purchaselimit/d'), 'unit' => trim(input('unit')), 'total' => input('total/d'), 'showtotal' => input('showtotal/d'), 'sales' => input('sales/d'), 'teamnum' => input('teamnum/s'), 'dispatchtype' => input('dispatchtype'), 'dispatchprice' => input('dispatchprice/f'), 'status' => input('status/d'), 'isindex' => input('isindex/d'), 'isrecommand' => input('isrecommand/d',0), 'groupnum' => input('groupnum/d'), 'endtime' => input('endtime'), 'description' => trim(input('description')), 'goodssn' => trim(input('goodssn')), 'productsn' => trim(input('productsn')), 'content' => model('common')->html_images(input('content')), 'createtime' => time(), 'goodsid' => input('gid/d',0), 'isdiscount' => input('isdiscount/d'), 'discount' => input('discount/d',0), 'headstype' => input('headstype/d'), 'headsmoney' => input('headsmoney/f'), 'headsdiscount' => input('headsdiscount/d'), 'storeids' => is_array($_POST['storeids']) ? implode(',', $_POST['storeids']) : '');

			if ($data['groupsprice'] < $data['headsmoney']) {
				$data['headsmoney'] = $data['groupsprice'];
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

			if ($data['groupsprice'] <= 0) {
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

				model('shop')->plog('groups.goods.edit', '编辑拼团商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			}
			else {
				$id = Db::name('shop_goods')->insertGetId($data);

				if (!$id) {
					show_json(0, '商品添加失败！');
				}
				$gid = intval($data['gid']);

				if ($gid) {
					Db::name('shop_goods')->where('id',$id)->setField('groupstype',1);
				}

				model('shop')->plog('groups.goods.add', '添加拼团商品 ID: ' . $id . '  <br/>商品名称: ' . $data['title']);
			}

			show_json(1, array('url' => url('admin/groups/goodsedit', array('op' => 'post', 'id' => $id, 'tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}
		$category = model('shop')->getFullCategory(true, true);
		$this->assign(['item'=>$item,'category'=>$category,'stores'=>$stores,'dispatch_data'=>$dispatch_data,'piclist'=>$piclist]);
		return $this->fetch('groups/goods/post');
	}

	public function goodsquery()
	{
		$kwd = trim(input('keyword'));
		$condition = ' merchid = 0 and type = 1 and status = 1 and deleted = 0 ';

		if (!empty($kwd)) {
			$condition .= ' and title like "%' . $kwd . '%"';
		}

		$ds = Db::name('shop_goods')->where($condition)->field('id as gid,title,subtitle,thumb,thumb_url,marketprice,productprice,subtitle,content,goodssn,productsn')->select();

		foreach ($ds as &$d) {
			if (!empty($d['thumb_url'])) {
				$d['thumb_url'] = iunserializer($d['thumb_url']);
			}
		}

		unset($d);

		if (input('suggest')) {
			exit(json_encode(array('value' => $ds)));
		}

		$this->assign(['ds'=>$ds]);
		return $this->fetch('groups/goods/query');
	}

	public function goodsdelete1()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title,gid')->select();

		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->delete();
			model('shop')->plog('groups.goods.edit', '从回收站彻底删除商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsrestore()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title,gid')->select();
		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->update(array('deleted' => 0, 'status' => 0));

			if (intval($item['gid'])) {
				Db::name('shop_goods')->where('id',$item['gid'])->setField('groupstype',1);
			}

			model('shop')->plog('groups.goods.edit', '从回收站恢复商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title,gid')->select();

		foreach ($items as $item) {
			$delete_update = Db::name('shop_goods')->where('id',$item['id'])->update(array('deleted' => 1, 'status' => 0));
			if (!$delete_update) {
				show_json(0, '删除商品失败！');
			}

			if (intval($item['gid'])) {
				Db::name('shop_goods')->where('id',$item['gid'])->setField('groupstype',0);
			}

			model('shop')->plog('groups.goods.delete', '删除拼团商品 ID: ' . $item['id'] . '  <br/>商品名称: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsstatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title,gid')->select();

		$status = input('status/d');
		foreach ($items as $item) {
			$status_update = Db::name('shop_goods')->where('id',$item['id'])->setField('status',$status);
			if (!$status_update) {
				show_json(0, '商品状态修改失败！');
			}

			model('shop')->plog('groups.goods.edit', '修改拼团商品 ' . $item['id'] . ' <br /> 状态: ' . ($status == 0 ? '下架' : '上架'));
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsproperty()
	{
		$id = input('id/d');
		$type = trim(input('type'));
		$value = intval(input('value'));

		if (in_array($type, array('status', 'displayorder'))) {
			$statusstr = '';

			if ($type == 'status') {
				$typestr = '上下架';
				$statusstr = ($value == 1 ? '上架' : '下架');
			} else if ($type == 'displayorder') {
				$typestr = '排序';
				$statusstr = '序号 ' . $value;
			} else {
				if ($type == 'isindex') {
					$typestr = '是否首页显示';
					$statusstr = ($value == 1 ? '是' : '否');
				}
			}

			$property_update = Db::name('shop_goods')->where('id',$id)->setField($type,$value);

			if (!$property_update) {
				show_json(0, $typestr . '修改失败');
			}

			model('shop')->plog('groups.goods.edit', '修改拼团商品' . $typestr . '状态   ID: ' . $id . ' ' . $statusstr . ' ');
		}

		show_json(1);
	}

	public function goodstotal()
	{
		$type = intval(input('type'));
		$condition = ' isgroups = 1 ';

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

	public function teamsuccess()
	{
		$teamdata = $this->teamdata('success');
		return $teamdata;
	}

	public function teaming()
	{
		$teamdata = $this->teamdata('ing');
		return $teamdata;
	}

	public function teamerror()
	{
		$teamdata = $this->teamdata('error');
		return $teamdata;
	}

	public function teamall()
	{
		$teamdata = $this->teamdata('all');
		return $teamdata;
	}

	protected function teamdata($type = 'all')
	{
		$psize = 20;
		$sort = input('sort');
		$team = input('team');
		$condition = ' paytime > 0 and heads = 1 and is_team = 1 ';

		if ($type == 'ing') {
			$condition .= ' and success = 0 ';
		}
		else if ($type == 'success') {
			$condition .= ' and success = 1 ';
		}
		else if ($type == 'error') {
			$condition .= ' and success = -1 ';
		}
		else {
			if ($type == 'all') {
				$condition .= ' ';
			}
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		$searchtime = input('searchtime');

		if ($searchtime == 'starttime') {
			$starttime = strtotime(input('time/a')['start']);
			$endtime = strtotime(input('time/a')['end']);
			$condition .= ' AND starttime >= ' . $starttime . ' AND starttime <= ' . $endtime;
		}

		$keyword = input('keyword');
		$searchfield = input('searchfield');
		if (!empty($keyword)) {
			if ($searchfield == 'orderno') {
				$condition .= ' and orderno like ' . '"%' . $keyword . '%"';
			}

			if ($searchfield == 'teamid') {
				$condition .= ' AND id = ' . $keyword;
			}
		}

		$teams = Db::name('shop_groups_order')->where($condition)->order('createtime','desc')->paginate($psize);

		foreach ($teams as $key => $value) {
			$good = Db::name('shop_goods')->where('id',$value['goodid'])->field('title')->find();
			$teams[$key]['title'] = $good['title'];
			$teams[$key]['num'] = Db::name('shop_groups_order')->where('status > 0 and deleted = 0 and o.teamid = ' . $value['teamid'])->count();
			$teams[$key]['groups_team'] = $teams[$key]['groupnum'] - $teams[$key]['num'];
			$teams[$key]['starttime'] = date('Y-m-d H:i', $value['starttime']);
			$hours = $value['endtime'];
			$date = date('Y-m-d H:i:s', $value['starttime']);
			$teams[$key]['endtime'] = date('Y-m-d H:i', strtotime(' ' . $date . ' + ' . $hours . ' hour'));
		}

		if ($sort == 'desc') {
			$teams = $this->multi_array_sort($teams, 'num');
		} else {
			if ($sort == 'asc') {
				$teams = $this->multi_array_sort($teams, 'num', SORT_ASC);
			}
		}

		if ($team == 'groups') {
			$teams = $this->multi_array_sort($teams, 'groups_team', SORT_ASC);
		}

		$pager = $teams->render();
		$this->assign(['teams'=>$teams,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime,'type'=>$type]);
		return $this->fetch('groups/team/index');
	}

	public function order1()
	{
		$orderdata = $this->orderdata(1);
		return $orderdata;
	}

	public function order2()
	{
		$orderdata = $this->orderdata(2);
		return $orderdata;
	}

	public function order3()
	{
		$orderdata = $this->orderdata(3);
		return $orderdata;
	}

	public function order4()
	{
		$orderdata = $this->orderdata(4);
		return $orderdata;
	}

	public function order5()
	{
		$orderdata = $this->orderdata(5);
		return $orderdata;
	}

	public function orderall()
	{
		$orderdata = $this->orderdata('all');
		return $orderdata;
	}

	protected function orderdata($status = '')
	{
		$psize = 10;
		$condition = ' o.isverify = 0 ';

		if (intval($status) == 1) {
			$condition .= ' and o.status = 1 and (o.success = 1 or o.is_team = 0)  ';
		} else if (intval($status) == 2) {
			$condition .= ' and o.status = 2 ';
		} else if (intval($status) == 3) {
			$condition .= ' and o.status = 0 ';
		} else if (intval($status) == 4) {
			$condition .= ' and o.status = 3 ';
		} else {
			if (intval($status) == 5) {
				$condition .= ' and o.status = -1 ';
			}
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		$searchtime = trim(input('searchtime'));

		if (!empty($searchtime)) {
			$condition .= ' and o.' . $searchtime . 'time > ' . strtotime($_POST['time']['start']) . ' and o.' . $searchtime . 'time < ' . strtotime($_POST['time']['end']) . ' ';
			$starttime = strtotime($_POST['time']['start']);
			$endtime = strtotime($_POST['time']['end']);
		}

		$paytype = input('paytype');
		if (!empty($paytype)) {
			$paytype = trim($paytype);
			$condition .= ' and o.pay_type = ' . $paytype;
		}

		$searchfield = input('searchfield');
		$keyword = input('keyword');
		if (!empty($searchfield) && !empty($keyword)) {
			$searchfield = trim(strtolower($searchfield));
			$keyword = trim($keyword);

			if ($searchfield == 'orderno') {
				$condition .= ' AND locate(' . $keyword . ',o.orderno)>0 ';
			}
			else if ($searchfield == 'member') {
				$condition .= ' AND (locate(' . $keyword . ',m.realname)>0 or locate(' . $keyword . ',m.mobile)>0 or locate(' . $keyword . ',m.nickname)>0)';
			}
			else if ($searchfield == 'address') {
				$condition .= ' AND ( locate(' . $keyword . ',a.realname)>0 or locate(' . $keyword . ',a.mobile)>0) ';
			}
			else if ($searchfield == 'expresssn') {
				$condition .= ' AND locate(' . $keyword . ',o.expresssn)>0';
			}
			else if ($searchfield == 'goodstitle') {
				$condition .= ' and locate(' . $keyword . ',g.title)>0 ';
			}
			else {
				if ($searchfield == 'goodssn') {
					$condition .= ' and locate(' . $keyword . ',g.goodssn)>0 ';
				}
			}
		}

		$list = Db::name('shop_groups_order')->alias('o')->join('shop_goods g','g.id = o.goodid','left')->join('member m','m.id=o.mid','left')->join('shop_member_address a','a.id=o.addressid','left')->where($condition)->field('o.id,o.orderno,o.status,o.expresssn,o.addressid,o.express,o.remark,o.is_team,o.pay_type,o.isverify,o.refundtime,o.price,o.freight,o.discount,o.createtime,o.success,o.deleted,o.address,o.message,g.title,g.category,g.thumb,g.groupsprice,g.singleprice,g.marketprice as gprice,g.goodssn,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea,a.address as aaddress')->group('o.id')->order('o.createtime','desc')->paginate($psize);
		$pager = $list->render();

		foreach ($list as $key => $value) {
			if (!empty($value['address'])) {
				$user = unserialize($value['address']);
				$list[$key]['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'province' => $user['province'], 'city' => $user['city'], 'area' => $user['area'], 'street' => $user['street'], 'address' => $user['address']);
			}
			else {
				$user = iunserializer($value['addressid']);

				if (!is_array($user)) {
					$user = Db::name('shop_member_address')->where('id',$value['addressid'])->find();
				}

				$list[$key]['addressdata'] = array('realname' => $user['realname'], 'mobile' => $user['mobile'], 'province' => $user['province'], 'city' => $user['city'], 'area' => $user['area'], 'street' => $user['street'], 'address' => $user['address']);
			}
		}

		$paytype = array('credit' => '余额支付', 'wechat' => '微信支付', 'other' => '其他支付');
		$paystatus = array(0 => '未付款', 1 => '已付款', 2 => '待收货', 3 => '已完成', -1 => '已取消', 4 => '待发货');
		$this->assign(['list'=>$list,'pager'=>$pager,'paytype'=>$paytype,'paystatus'=>$paystatus,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('groups/order/index');
	}

	public function refundapply()
	{
		$refunddata = $this->refunddata();
		return $refunddata;
	}

	public function refundover()
	{
		$refunddata = $this->refunddata();
		return $refunddata;
	}

	protected function refunddata()
	{
		$paytype = array('credit' => '余额支付', 'wechat' => '微信支付', 'alipay' => '支付宝支付', 'other' => '其他支付');
		$paystatus = array(0 => '未付款', 1 => '已付款', 2 => '待收货', 3 => '已完成', -1 => '已取消', 4 => '待发货');
		$this->assign(['paytype'=>$paytype,'paystatus'=>$paystatus]);
		return $this->fetch('groups/refund/index');
	}

	public function set()
	{
		$set = Db::name('shop_groups_set')->limit(1)->find();

		if (Request::instance()->isPost()) {
			$data = array('opengroups' => intval($_POST['data']['opengroups']), 'groups_description' => model('common')->html_images($_POST['groups_description']), 'rules' => model('common')->html_images($_POST['rules']), 'description' => intval($_POST['data']['description']), 'creditdeduct' => intval($_POST['data']['creditdeduct']), 'credit' => intval($_POST['data']['credit']), 'groupsdeduct' => intval($_POST['data']['groupsdeduct']), 'groupsmoney' => $_POST['data']['groupsmoney'], 'refund' => intval($_POST['data']['refund']), 'refundday' => intval($_POST['data']['refundday']), 'receive' => intval($_POST['data']['receive']), 'discount' => intval($_POST['data']['discount']), 'headstype' => intval($_POST['headstype']), 'headsmoney' => floatval($_POST['headsmoney']), 'headsdiscount' => intval($_POST['headsdiscount']), 'goodsid' => !empty($_POST['goodsid']) ? implode(',', $_POST['goodsid']) : 0);

			if (!empty($set)) {
				$set_update = Db::name('shop_groups_set')->where('id',$set['id'])->update($data);
			}
			else {
				$set_insert = Db::name('shop_groups_set')->insertGetId($data);
			}

			show_json(1, array('url' => url('admin/groups/set', array('tab' => str_replace('#tab_', '', $_POST['tab'])))));
		}

		$sys_data = model('common')->getPluginset('sale');
		$data = Db::name('shop_groups_set')->limit(1)->find();

		if ($data['goodsid']) {
			$goods = Db::name('shop_goods')->where('id','in',$data['goodsid'])->field('id,title,thumb')->select();
		}
		$this->assign(['data'=>$data,'goods'=>$goods,'sys_data'=>$sys_data]);
		return $this->fetch('groups/set/index');
	}

}