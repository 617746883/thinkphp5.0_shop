<?php
/**
 * 商品管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Goods extends Base
{
	public function category()
	{
		$children = array();
		$category = Db::name('shop_goods_category')->order('parentid','asc')->order('displayorder','desc')->select();
		foreach ($category as $index => $row) {
			if (!empty($row['parentid'])) {
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}
		$this->assign(['children'=>$children,'category'=>$category]);
		return $this->fetch('goods/category/index');
	}

	public function categoryadd()
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	public function categoryedit()
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	protected function categorypost()
	{
		$parentid = input('parentid/d');
		$id = input('id/d');
		$parent = array();
		$parent1 = array();
		if (!empty($id)) {
			$item = Db::name('shop_goods_category')->where('id',$id)->find();
			$parentid = $item['parentid'];
		}
		else {
			$item = array('displayorder' => 0);
		}

		if (!empty($parentid)) {
			$parent = Db::name('shop_goods_category')->where('id',$parentid)->find();

			if (empty($parent)) {
				$this->error('抱歉，上级分类不存在或是已经被删除！', url('admin/goods/categoryadd'));
			}

			if (!empty($parent['parentid'])) {
				$parent1 = Db::name('shop_goods_category')->where('id',$parent['parentid'])->find();
			}
		}

		if (empty($parent)) {
			$level = 1;
		}
		else if (empty($parent['parentid'])) {
			$level = 2;
		}
		else {
			$level = 3;
		}

		if (!empty($item)) {
			$item['url'] = url('admin/goods/list', array('cate' => $item['id']));
		}

		if (Request::instance()->isPost()) {
			$data = array('name' => trim(input('catename')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'isrecommand' => intval(input('isrecommand')), 'ishome' => intval(input('ishome')), 'description' => input('description'), 'parentid' => intval($parentid), 'thumb' => trim(input('thumb')), 'advimg' => trim(input('advimg')), 'advurl' => trim(input('advurl')), 'level' => $level);

			if (!empty($id)) {
				unset($data['parentid']);
				Db::name('shop_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('shop.category.edit', '修改分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_goods_category')->insertGetId($data);
				model('shop')->plog('shop.category.add', '添加分类 ID: ' . $id);
			}

			model('shop')->getCategory();
			model('shop')->getAllCategory();
			show_json(1, array('url' => url('admin/goods/category')));
		}
		$this->assign(['item'=>$item,'parentid'=>$parentid,'parent'=>$parent,'parent1'=>$parent1]);
		return $this->fetch('goods/category/post');
	}

	public function categorydelete()
	{
		$id = input('id/d');
		$item = Db::name('shop_goods_category')->where('id',$id)->field('id,name,parentid')->find();
		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		$child = Db::name('shop_goods_category')->where('parentid',$id)->count();
		if($child > 0)
		{
			show_json(0,'请先删除下级分类');
		}
		Db::name('shop_goods_category')->where('id',$id)->whereOr('parentid',$id)->delete();
		model('shop')->plog('shop.category.delete', '删除分类 ID: ' . $id . ' 分类名称: ' . $item['name']);
		model('shop')->getCategory();
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_category')->where('id','in',$id)->select();

		foreach ($items as $item) {
			Db::name('shop_goods_category')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('shop.dispatch.edit', ('修改分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['name'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}

		model('shop')->getCategory();
		show_json(1);
	}

	public function group()
	{
		$psize = 20;
		$condition = ' merchid = 0 ';
		$keyword = input('keyword');
		$condition = ' 1 ';
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and name like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_goods_group')->where($condition)->order('id','desc')->paginate($psize); 
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'keyword'=>$keyword]);
		return $this->fetch('goods/group/index');
	}

	public function groupadd()
	{
		$groupdata = $this->grouppost();
		return $groupdata;
	}

	public function groupedit()
	{
		$groupdata = $this->grouppost();
		return $groupdata;
	}

	protected function grouppost()
	{
		$id = input('id/d');

		if (!empty($id)) {
			$item = Db::name('shop_goods_group')->where('id',$id)->where('merchid',0)->find();

			if (!empty($item['goodsids'])) {
				$item['goodsids'] = trim($item['goodsids'], ',');
				$goods = Db::name('shop_goods')->where('id','in',$item['goodsids'])->where('status',1)->where('deleted',0)->field('id,title,thumb')->select();
			}
		}

		if (Request::instance()->isPost()) {
			$displayorder = input('displayorder/d');
			$groupname = trim(input('name'));
			$goodsids = input('goodsids/a');
			$enabled = input('enabled/d');

			if (empty($groupname)) {
				show_json(0, '商品组名称不能为空');
			}

			if (empty($goodsids)) {
				show_json(0, '商品组中商品不能为空');
			}

			$data = array('displayorder' => $displayorder, 'name' => $groupname, 'merchid' => 0, 'goodsids' => implode(',', $goodsids), 'enabled' => $enabled);

			if (!empty($item)) {
				Db::name('shop_goods_group')->where('id',$item['id'])->update($data);
				model('shop')->plog('goods.group.edit', '修改商品组 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_goods_group')->insertGetId($data);
				model('shop')->plog('goods.group.add', '添加商品组 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/goods/groupedit', array('id' => $id))));
		}
		$this->assign(['item'=>$item,'goods'=>$goods]);
		return $this->fetch('goods/group/post');
	}

	public function groupdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_group')->where('id','in',$id)->field('id,name')->select();

		foreach ($items as $item) {
			Db::name('shop_goods_group')->where('id',$item['id'])->delete();
			model('shop')->plog('goods.group.delete', '删除商品组 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function groupenabled()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_group')->where('id','in',$id)->field('id,name')->select();
		foreach ($items as $item) {
			Db::name('shop_goods_group')->where('id',$item['id'])->setField('enabled',input('enabled/d'));
			model('shop')->plog('goods.group.edit', ('修改商品组状态<br/>ID: ' . $item['id'] . '<br/>商品组名称: ' . $item['name'] . '<br/>状态: ' . input('enabled/d')) == 1 ? '启用' : '禁用');
		}

		show_json(1);
	}

	public function label()
	{
		$condition = ' merchid = 0 ';
		$psize = 20;

		if (input('enabled') != '') {
			$enabled = intval(input('enabled'));
			$condition .= ' and status = ' . $enabled;
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and label like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_goods_label')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('goods/label/index');
	}

	public function labeladd()
	{
		$labeldata = $this->labelpost();
		return $labeldata;
	}

	public function labeledit()
	{
		$labeldata = $this->labelpost();
		return $labeldata;
	}

	protected function labelpost()
	{
		$id = input('id/d');

		if (!empty($id)) {
			$item = Db::name('shop_goods_label')->where('id',$id)->find();

			if (json_decode($item['labelname'], true)) {
				$labelname = json_decode($item['labelname'], true);
			}
			else {
				$labelname = unserialize($item['labelname']);
			}
		}

		if (Request::instance()->isPost()) {
			if (empty(input('labelname/a'))) {
				$labelname = array();
			}
			$labelname = input('labelname/a');
			$data = array('displayorder' => input('displayorder/d'), 'label' => trim(input('label')), 'labelname' => serialize(array_filter($labelname)), 'status' => intval(input('status')));

			if (!empty($item)) {
				Db::name('shop_goods_label')->where('id',$item['id'])->update($data);
				model('shop')->plog('goods.label.edit', '修改标签组 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_goods_label')->insertGetId($data);
				model('shop')->plog('goods.label.add', '添加标签组 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/goods/labeledit', array('id' => $id))));
		}
		$this->assign(['item'=>$item,'labelname'=>$labelname]);
		return $this->fetch('goods/label/post');
	}

	public function labeldelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_label')->where('id','in',$id)->select();

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $item) {
			Db::name('shop_goods_label')->where('id',$item['id'])->delete();
			model('shop')->plog('goods.edit', '从回收站彻底删除标签组<br/>ID: ' . $item['id'] . '<br/>标签组名称: ' . $item['label']);
		}

		show_json(1, array('url' => referer()));
	}

	public function labelstatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_label')->where('id','in',$id)->select();

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $item) {
			Db::name('shop_goods_label')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('goods.label.edit', ('修改标签组状态<br/>ID: ' . $item['id'] . '<br/>标签组名称: ' . $item['label'] . '<br/>状态: ' . input('status/d')) == 1 ? '上架' : '下架');
		}

		show_json(1, array('url' => referer()));
	}

	public function labelquery()
	{
		$kwd = trim(input('keyword'));
		$params = array();
		$condition = ' status = 1 and merchid = 0';

		if (!empty($kwd)) {
			$condition .= ' and label like "%' . $kwd . '%"';
		}

		$labels = Db::name('shop_goods_label')->where($condition)->select();

		if (empty($labels)) {
			$labels = array();
		}

		foreach ($labels as $key => $value) {
			if (json_decode($value['labelname'], true)) {
				$labels[$key]['labelname'] = json_decode($value['labelname'], true);
			}
			else {
				$labels[$key]['labelname'] = unserialize($value['labelname']);
			}
		}
		$this->assign(['labels'=>$labels]);
		return $this->fetch('goods/label/query');
	}

	public function index()
	{
		header('location: ' . url('admin/goods/sale'));exit;
	}

	public function sale()
	{
		$listdata = $this->goodslist('sale');
		return $listdata;
	}

	public function out()
	{
		$listdata = $this->goodslist('out');
		return $listdata;
	}

	public function stock()
	{
		$listdata = $this->goodslist('stock');
		return $listdata;
	}

	public function cycle()
	{
		$listdata = $this->goodslist('cycle');
		return $listdata;
	}

	public function verify()
	{
		$listdata = $this->goodslist('verify');
		return $listdata;
	}

	protected function goodslist($goodsfrom = 'sale')
	{		
		$shopset = $this->shopset;
		$merch_data = model("common")->getPluginset("merch");
		if($merch_data["is_openmerch"] ) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		$psize = 20;
		$querysql = Db::name('shop_goods')->alias('g');
		$condition = " g.type<>9";
		if( !empty($_GET["keyword"]) ) 
		{
			$keyword = trim($_GET["keyword"]);
			$querysql = $querysql->join('shop_goods_option op','g.id = op.goodsid','left');
			if( $is_openmerch ) {
				$querysql = $querysql->join('shop_merch merch','merch.id = g.merchid','left');
			}
			$querysql = $querysql->group('g.`id`');
			$condition .= " AND (g.`id` = '%" . $keyword . "%' or g.`title` LIKE '%" . $keyword . "%' or g.`keywords` LIKE '%" . $keyword . "%' or g.`goodssn` LIKE '%" . $keyword . "%' or g.`productsn` LIKE '%" . $keyword . "%' or op.`title` LIKE '%" . $keyword . "%' or op.`goodssn` LIKE '%" . $keyword . "%' or op.`productsn` LIKE '%" . $keyword . "%'";
			if( $is_openmerch ) 
			{
				$condition .= " or merch.`merchname` LIKE '%" . $keyword . "%'";
			}
			$condition .= " )";
		}
		if( !empty($_GET["cate"]) ) 
		{
			$cate = intval($_GET["cate"]);
			$condition .= " AND FIND_IN_SET(" . $cate . ",cates)<>0 ";
		}
		if( !empty($_GET["attribute"]) ) 
		{
			if( $_GET["attribute"] == "new" ) 
			{
				$condition .= " AND `isnew`=1 ";
			}
			else 
			{
				if( $_GET["attribute"] == "hot" ) 
				{
					$condition .= " AND `ishot`=1 ";
				}
				else 
				{
					if( $_GET["attribute"] == "recommand" ) 
					{
						$condition .= " AND `isrecommand`=1 ";
					}
					else 
					{
						if( $_GET["attribute"] == "discount" ) 
						{
							$condition .= " AND `isdiscount`=1 ";
						}
						else 
						{
							if( $_GET["attribute"] == "time" ) 
							{
								$condition .= " AND `istime`=1 ";
							}
							else 
							{
								if( $_GET["attribute"] == "sendfree" ) 
								{
									$condition .= " AND `issendfree`=1 ";
								}
								else 
								{
									if( $_GET["attribute"] == "nodiscount" ) 
									{
										$condition .= " AND `isdiscount`=1 ";
									}
								}
							}
						}
					}
				}
			}
		}
		empty($goodsfrom) && ($_GET['goodsfrom'] = $goodsfrom = 'sale');
		$_GET['goodsfrom'] = $goodsfrom;
		if( $goodsfrom == "sale" ) {
			$condition .= " AND g.`status` > 0 and g.`checked`=0 and g.`total`>0 and g.`deleted`=0";
			$status = 1;
		} else {
			if( $goodsfrom == "out" ) 
			{
				$condition .= " AND g.`status` > 0 and g.`total` <= 0 and g.`deleted`=0 and g.type!=30";
				$status = 1;
			}
			else 
			{
				if( $goodsfrom == "stock" ) 
				{
					$status = 0;
					$condition .= " AND (g.`status` = 0 or g.`checked`=1) and g.`deleted`=0";
				}
				else 
				{
					if( $goodsfrom == "cycle" ) 
					{
						$status = 0;
						$condition .= " AND g.`deleted`=1";
					}
					else 
					{
						if( $goodsfrom == "verify" ) 
						{
							$status = 0;
							$condition .= " AND g.`deleted`=0 and merchid>0 and checked=1";
						}
					}
				}
			}
		}
		
		$list = $querysql->where($condition)->field('g.*')->order('g.status DESC, g.displayorder DESC,g.id DESC')->paginate($psize);;
		foreach( $list as $key => $value ) 
		{
			$value["allcates"] = explode(",", $value["cates"]);
			$value["allcates"] = array_unique($value["allcates"]);
			$sale_cpcount = Db::query("SELECT sum(og.total)  as sale_count FROM " . tablename("shop_order_goods") . " og LEFT JOIN " . tablename("shop_order") . " o on og.orderid=o.id  WHERE og.goodsid=" . $value["id"] . " and o.`status`>=1 and o.refundid = 0 ");
			$value["sale_cpcount"] = $sale_cpcount["sale_count"];
			if( $is_openmerch ) {
				if($value['merchid']) {
					$value["merchname"] = Db::name('shop_merch')->where('id = ' . $value['merchid'])->value('merchname');
				} else {
					$value["merchname"] = $shopset["shop"]["name"];
				}
			}
			$data = array();
    		$data = $value;
    		$list->offsetSet($key,$data);
		}
		unset($value);
		$pager = $list->render();
		$categorys = model("shop")->getFullCategory(true, true);
		$category = array( );
		foreach( $categorys as $cate ) 
		{
			$category[$cate["id"]] = $cate;
		}		
		$goodstotal = intval($shopset["goodstotal"]);
		
		$this->assign(['list'=>$list,'pager'=>$pager,'category'=>$category,'cate'=>$cate,'status'=>$status,'keyword'=>$keyword,'goodsfrom'=>$goodsfrom]);
		return $this->fetch('goods/index');
	}

	public function add()
	{
		$data = $this->post();
		return $data;
	}

	public function edit()
	{
		$data = $this->post();
		return $data;
	}

	protected function post()
	{
		$id = input('id/d');
		$shopset = $this->shopset;
		$shopset_level = intval($shopset["commission"]["level"]);
		$tab = input('tab/s','basic');
		if (!(empty($id))) {
			Db::name('shop_goods')->where('id',$id)->setField('newgoods',0);
		}
		$item = Db::name('shop_goods')->where('id',$id)->find();

		if (!(empty($item)) && ($item['type'] == 5) && !(empty($item['opencard'])) && !(empty($item['cardid']))) {
			$card = Db::name('shop_goods_cards')->where('id',$item['cardid'])->find();
		}
		$noticetype = explode(",", $item["noticetype"]);
		$status = $item['status'];

		if (json_decode($item['labelname'], true)) {
			$labelname = json_decode($item['labelname'], true);
		} else {
			$labelname = unserialize($item['labelname']);
		}
		$endtime = ((empty($item['endtime']) ? date('Y-m-d H:i', time()) : date('Y-m-d H:i', $item['endtime'])));
		$item['statustimestart'] = ((0 < $item['statustimestart'] ? $item['statustimestart'] : time()));
		$item['statustimeend'] = ((0 < $item['statustimeend'] ? $item['statustimeend'] : strtotime('+1 month')));
		$intervalprices = iunserializer($item['intervalprice']);

		if (empty($labelname)) {
			$labelname = array();
		}
		foreach ($labelname as $key => $value ) {
			$label[$key]['id'] = $value;
			$label[$key]['labelname'] = $value;
		}

		$merchid = 0;
		if (!(empty($item))) {
			if (0 < $item['merchid']) {
				$merchid = intval($item['merchid']);
			}
		}

		$category = model('shop')->getFullCategory(true, true);
		$dispatch_data = Db::name('shop_dispatch')->where('enabled',1)->where('merchid',$merchid)->order('displayorder','desc')->select();
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);
		$levels = model('member')->getLevels();

		foreach ($levels as &$l) {
			$l['key'] = 'level' . $l['id'];
		}
		unset($l);
		$levels = array_merge(array(
			array('id' => 0, 'key' => 'default', 'levelname' => (empty($shopset['shop']['levelname']) ? '默认会员' : $shopset['shop']['levelname']))
			), $levels);
		$groups = model('member')->getGroups();
		$virtual_types = Db::name('shop_virtual_type')->where('merchid',$merchid)->where('recycled',0)->order(
			'id','asc')->select();

		$areas = model('common')->getAreas();
		$commission_level = array();
		if( m("commission") ) 
		{
			$com_set = model("commission")->getSet();
			$commission_level = fetchall("SELECT * FROM " . tablename("shop_commission_level") . " WHERE 1 ORDER BY commission1 asc");
			foreach( $commission_level as &$l ) 
			{
				$l["key"] = "level" . $l["id"];
			}
			unset($l);
			$commission_level = array_merge(array( array( "key" => "default", "levelname" => (empty($shopset["commission"]["levelname"]) ? "默认等级" : $shopset["commission"]["levelname"]) ) ), $commission_level);
		}
		
		if (Request::instance()->isPost()) {
			if (empty($id)) {
				$goodstype = input('type/d');
			} else {
				$goodstype = input('goodstype/d');
			}

			if ($goodstype != 1 && intval($_POST['status']) == 2) {
				show_json(0, '赠品只能是实体商品');
			}

			if ($_POST['isverify'] == 2 && intval($_POST['status']) == 2) {
				show_json(0, '赠品不支持核销');
			}

			if (intval($_POST['hasoption']) == 1 && intval($_POST['status']) == 2) {
				show_json(0, '赠品不支持多规格');
			}

			if ($_POST['isverify'] == 2 && $id) {
				$nowtime = time();
				$gift = Db::name('shop_gift')->where('goodsid = ' . $id . ' and endtime>=' . $nowtime)->field('id,title')->find();

				if ($gift) {
					show_json(0, '已为此商品指定了赠品不支持线下核销');
				}
			}
			$data = array('displayorder' => intval($_POST['displayorder']), 'title' => trim($_POST['goodsname']), 'subtitle' => trim($_POST['subtitle']), 'shorttitle' => trim($_POST['shorttitle']), 'keywords' => trim($_POST['keywords']), 'thumb_first' => intval($_POST['thumb_first']), 'showsales' => intval($_POST['showsales']), 'type' => $goodstype, 'ispresell' => intval($_POST['ispresell']), 'presellover' => intval($_POST['presellover']), 'presellovertime' => 0 < intval($_POST['presellovertime']) ? intval($_POST['presellovertime']) : 0, 'presellprice' => floatval($_POST['presellprice']), 'presellstart' => intval($_POST['presellstart']), 'presellend' => intval($_POST['presellend']), 'preselltimestart' => 0 < intval($_POST['presellstart']) ? strtotime($_POST['preselltimestart']) : 0, 'preselltimeend' => 0 < intval($_POST['presellend']) ? strtotime($_POST['preselltimeend']) : 0, 'presellsendtype' => intval($_POST['presellsendtype']), 'presellsendstatrttime' => strtotime($_POST['presellsendstatrttime']), 'presellsendtime' => intval($_POST['presellsendtime']), 'labelname' => serialize($_POST['labelname']), 'isrecommand' => intval($_POST['isrecommand']), 'ishot' => intval($_POST['ishot']), 'isnew' => intval($_POST['isnew']), 'isdiscount' => intval($_POST['isdiscount']), 'isdiscount_title' => trim(mb_substr($_POST['isdiscount_title'], 0, 5, 'UTF-8')), 'isdiscount_time' => strtotime($_POST['isdiscount_time']), 'issendfree' => intval($_POST['issendfree']), 'isnodiscount' => intval($_POST['isnodiscount']), 'istime' => intval($_POST['istime']), 'timestart' => strtotime($_POST['saletime']['start']), 'timeend' => strtotime($_POST['saletime']['end']), 'description' => trim($_POST['description']), 'goodssn' => trim($_POST['goodssn']), 'unit' => trim($_POST['unit']), 'createtime' => time(), 'total' => intval($_POST['total']), 'showtotal' => intval($_POST['showtotal']), 'totalcnf' => intval($_POST['totalcnf']), 'unite_total' => intval($_POST['unite_total']), 'marketprice' => $_POST['marketprice'], 'weight' => $_POST['weight'], 'costprice' => $_POST['costprice'], 'productprice' => trim($_POST['productprice']), 'productsn' => trim($_POST['productsn']), 'credit' => trim($_POST['credit']), 'maxbuy' => intval($_POST['maxbuy']), 'minbuy' => intval($_POST['minbuy']), 'usermaxbuy' => intval($_POST['usermaxbuy']), 'hasoption' => intval($_POST['hasoption']), 'sales' => intval($_POST['sales']), 'share_icon' => trim($_POST['share_icon']), 'share_title' => trim($_POST['share_title']), 'status' => $status != 2 ? intval($_POST['status']) : $status, 'groupstype' => intval($_POST['groupstype']), 'virtualsend' => intval($_POST['virtualsend']), 'virtualsendcontent' => trim($_POST['virtualsendcontent']), 'buyshow' => intval($_POST['buyshow']), 'showlevels' => is_array($_POST['showlevels']) ? implode(',', $_POST['showlevels']) : '', 'buylevels' => is_array($_POST['buylevels']) ? implode(',', $_POST['buylevels']) : '', 'showgroups' => is_array($_POST['showgroups']) ? implode(',', $_POST['showgroups']) : '', 'buygroups' => is_array($_POST['buygroups']) ? implode(',', $_POST['buygroups']) : '', 'noticeopenid' => is_array($_POST['noticeopenid']) ? implode(',', $_POST['noticeopenid']) : '', 'noticetype' => is_array($_POST['noticetype']) ? implode(',', $_POST['noticetype']) : '', 'needfollow' => intval($_POST['needfollow']), 'followurl' => trim($_POST['followurl']), 'followtip' => trim($_POST['followtip']), 'deduct' => $_POST['deduct'], 'manydeduct' => $_POST['manydeduct'], 'deduct2' => $_POST['deduct2'], 'virtual' => $goodstype == 3 ? intval($_POST['virtual']) : 0, 'ednum' => intval($_POST['ednum']), 'edareas' => trim($_POST['edareas']), 'edareas_code' => trim($_POST['edareas_code']), 'edmoney' => trim($_POST['edmoney']), 'invoice' => intval($_POST['invoice']), 'repair' => intval($_POST['repair']), 'seven' => intval($_POST['seven']), 'money' => trim($_POST['money']), 'province' => trim($_POST['province']), 'city' => trim($_POST['city']), 'quality' => intval($_POST['quality']), 'sharebtn' => intval($_POST['sharebtn']), 'autoreceive' => intval($_POST['autoreceive']), 'cannotrefund' => intval($_POST['cannotrefund']), 'buyagain' => floatval($_POST['buyagain']), 'buyagain_islong' => intval($_POST['buyagain_islong']), 'buyagain_condition' => intval($_POST['buyagain_condition']), 'buyagain_sale' => intval($_POST['buyagain_sale']), 'cashier' => intval($_POST['cashier']), 'video' => trim($_POST['video']),'nolive' => intval($_POST['nolive']));
			if($goodstype!=4) {
				if($data['marketprice']==''||$data['productprice']==''||$data['costprice']=='') {
					show_json(0, '商品价格必填！');
				}

			}
			$data['nosearch'] = intval($_POST['nosearch']);
			$data['isstatustime'] = intval($_POST['isstatustime']);
			$statustimestart = strtotime($_POST['statustime']['start']);
			$statustimeend = strtotime($_POST['statustime']['end']);
			$data['statustimestart'] = $statustimestart;
			$data['statustimeend'] = $statustimeend;
			if ($data['status'] == 1 && 0 < $data['isstatustime']) {
				if ($statustimeend <= time()) {
					show_json(0, '上架时间不符合要求！');
				}
			}
			$intervalfloor = 0;
			$intervalprice = '';
			if ($goodstype == 4) {
				$intervalfloor = input('intervalfloor/d',0);
				if ((3 < $intervalfloor) || ($intervalfloor < 1)) {
					show_json(0, '请至少添加一个区间价格！');
				}
				$intervalprices = array();

				if (0 < $intervalfloor) {
					if (intval(input('intervalnum1')) <= 0) {
						show_json(0, '请设置起批发量！');
					}

					if (input('intervalprice1/f') <= 0) {
						show_json(0, '批发价必须大于0！');
					}
					$intervalprices[] = array('intervalnum' => input('intervalnum1/d',0), 'intervalprice' => input('intervalprice1/f'));
				}

				if (1 < $intervalfloor) {
					if (input('intervalnum2/d',0) <= 0) {
						show_json(0, '请设置起批发量！');
					}
					if (input('intervalnum2/d',0) <= input('intervalnum1/d',0)) {
						show_json(0, '批发量需大于上级批发量！');
					}

					if (input('intervalprice1/f') <= input('intervalprice2/f')) {
						show_json(0, '批发价需小于上级批发价！');
					}

					$intervalprices[] = array('intervalnum' => input('intervalnum2/d',0), 'intervalprice' => input('intervalprice2/f'));
				}

				if (2 < $intervalfloor) {
					if (input('intervalnum3/d',0) <= 0) {
						show_json(0, '请设置起批发量！');
					}

					if (input('intervalnum3/d',0) <= input('intervalnum2/d',0)) {
						show_json(0, '批发量需大于上级批发量！');
					}

					if (input('intervalprice2/f') <= input('intervalprice3/f')) {
						show_json(0, '批发价需小于上级批发价！');
					}
					$intervalprices[] = array('intervalnum' => input('intervalnum3/d',0), 'intervalprice' => input('intervalprice3/f'));
				}

				$intervalprice = iserializer($intervalprices);
				$data['intervalfloor'] = $intervalfloor;
				$data['intervalprice'] = $intervalprice;
				$data['minbuy'] = input('intervalnum1');
				$data['marketprice'] = input('intervalprice1');
				$data['productprice'] = 0;
				$data['costprice'] = 0;
			}
			if (input('ispresell/d',0) == 1) {
				if (floatval(input('presellprice') <= 0)) {
					show_json(0, '请填写预售价格！');
				}

				$data['isdiscount'] = 0;
				$data['istime'] = 0;
			}
			if ($merchid == 0) {
				$data['isverify'] = input('isverify');
				$data['verifytype'] = input('verifytype');
				$data['storeids'] = ((is_array(input('storeids/a')) ? implode(',', input('storeids/a')) : ''));
				if ((intval(input('isverify')) == 2) || ($goodstype == 2) || ($goodstype == 3)) {
					$data['cash'] = 0;
				} else {
					$data['cash'] = input('cash');
				}

				$data['detail_logo'] = trim(input('detail_logo'));
				$data['detail_shopname'] = trim(input('detail_shopname'));
				$data['detail_totaltitle'] = trim(input('detail_totaltitle'));
				$data['detail_btntext1'] = trim(input('detail_btntext1'));
				$data['detail_btnurl1'] = trim(input('detail_btnurl1'));
				$data['detail_btntext2'] = trim(input('detail_btntext2'));
				$data['detail_btnurl2'] = trim(input('detail_btnurl2'));
			} else {
				if ((intval($item['isverify']) == 2) || ($goodstype == 2) || ($goodstype == 3)) {
					$data['cash'] = 0;
				} else {
					$data['cash'] = input('cash');
				}
				$data['merchsale'] = input('merchsale');
			}
			$data['isforceverifystore'] = intval($_POST['isforceverifystore']);

			$data['isendtime'] = input('isendtime/d',0);
			$data['usetime'] = input('usetime/d',0);
			$data['endtime'] = strtotime('endtime/d',0);
			$cateset = model('common')->getSysset('shop');
			$pcates = array();
			$ccates = array();
			$tcates = array();
			$fcates = array();
			$cates = array();
			$pcateid = 0;
			$ccateid = 0;
			$tcateid = 0;
			if (is_array($_POST['cates'])) {
				$cates = $_POST['cates'];
				foreach ($cates as $key => $cid ) {
					$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();

					if ($c['level'] == 1) {
						$pcates[] = $cid;
					} else if ($c['level'] == 2) {
						$ccates[] = $cid;
					} else if ($c['level'] == 3) {
						$tcates[] = $cid;
					}

					if ($key == 0) {
						if ($c['level'] == 1) {
							$pcateid = $cid;
						} else if ($c['level'] == 2) {
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow['parentid'];
							$ccateid = $cid;
						} else if ($c['level'] == 3) {
							$tcateid = $cid;
							$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
							$ccateid = $tcate['parentid'];
							$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
							$pcateid = $ccate['parentid'];
						}
					}
				}
			}
			$data['pcate'] = $pcateid;
			$data['ccate'] = $ccateid;
			$data['tcate'] = $tcateid;
			$data['cates'] = implode(',', $cates);
			$data['pcates'] = implode(',', $pcates);
			$data['ccates'] = implode(',', $ccates);
			$data['tcates'] = implode(',', $tcates);
			$data['content'] = model('common')->html_images($_POST['content']);
			$data['buycontent'] = model('common')->html_images($_POST['buycontent']);
			$data['dispatchtype'] = input('dispatchtype');
			$data['dispatchprice'] = input('dispatchprice');
			$data['dispatchid'] = input('dispatchid/d',0);
			if( m("commission") ) 
			{
				$cset = model("commission")->getSet();
				if( !empty($cset["level"]) ) 
				{
					$data["nocommission"] = intval($_POST["nocommission"]);
					$data["hascommission"] = intval($_POST["hascommission"]);
					$data["hidecommission"] = intval($_POST["hidecommission"]);
					$data["commission1_rate"] = $_POST["commission1_rate"];
					$data["commission2_rate"] = $_POST["commission2_rate"];
					$data["commission3_rate"] = $_POST["commission3_rate"];
					$data["commission1_pay"] = $_POST["commission1_pay"];
					$data["commission2_pay"] = $_POST["commission2_pay"];
					$data["commission3_pay"] = $_POST["commission3_pay"];
					$data["commission_thumb"] = trim($_POST["commission_thumb"]);
				}
			}
			if ($data['total'] === -1) {
				$data['total'] = 0;
				$data['totalcnf'] = 2;
			}
			if (is_array($_POST['thumbs'])) {
				$thumbs = input('thumbs/a');
				$thumb_url = array();

				foreach ($thumbs as $th ) {
					$thumb_url[] = trim($th);
				}

				$data['thumb'] = trim($thumb_url[0]);
				unset($thumb_url[0]);
				$data['thumb_url'] = serialize($thumb_url);
			}
			if ($goodstype == 5) {
				$verifygoodsnum = input('verifygoodsnum');
				$verifygoodslimittype = input('verifygoodslimittype');

				if (!(empty(input('verifygoodslimitdate')))) {
					$verifygoodslimitdate = strtotime(input('verifygoodslimitdate'));
				} else {
					$verifygoodslimitdate = 0;
				}

				$verifygoodsdays = input('verifygoodsdays/d',0);

				if (empty($verifygoodslimittype)) {
					if (empty($verifygoodsdays)) {
						$verifygoodsdays = 365;
					}
				}
				$data['verifygoodsnum'] = intval($verifygoodsnum);
				$data['verifygoodslimittype'] = intval($verifygoodslimittype);
				$data['verifygoodsdays'] = intval($verifygoodsdays);
				$data['verifygoodslimitdate'] = intval($verifygoodslimitdate);
			}
			if ($goodstype == 2) {
				$data['isspecial'] = intval(input('isspecial'));
				$data['specialtype'] = intval(input('specialtype'));
				if($data['isspecial'] == 2 && empty($data['specialtype'])) {
					show_json(0, '请选择卡密类型！');
				}
			}
			// 启动事务
			Db::startTrans();
			try {
			    if (empty($id)) {
					$data['merchid'] = 0;
					$id = Db::name('shop_goods')->insertGetId($data);
					model('shop')->plog('goods.add', '添加商品 ID: ' . $id);
				} else {
					unset($data['createtime']);
					Db::name('shop_goods')->where('id',$id)->update($data);
					model('shop')->plog('goods.edit', '编辑商品 ID: ' . $id);
				}
				$param_ids = $_POST['param_id'];
				$param_titles = $_POST['param_title'];
				$param_values = $_POST['param_value'];
				$param_displayorders = $_POST['param_displayorder'];
				$len = count($param_ids);
				$paramids = array();
				$k = 0;

				while ($k < $len) {
					$param_id = '';
					$get_param_id = $param_ids[$k];
					$a = array('title' => $param_titles[$k], 'value' => $param_values[$k], 'displayorder' => $k, 'goodsid' => $id);

					if (!(is_numeric($get_param_id))) {
						$param_id = Db::name('shop_goods_param')->insertGetId($a);
					} else {
					 	Db::name('shop_goods_param')->where('id',$get_param_id)->update($a);
						$param_id = $get_param_id;
					}

					$paramids[] = $param_id;
					++$k;
				}

				if (0 < count($paramids)) {
					Db::name('shop_goods_param')->where('id','not in',implode(',', $paramids))->where('goodsid',$id)->delete();
				} else {
				 	Db::name('shop_goods_param')->where('goodsid',$id)->delete();
				}

				$totalstocks = 0;
				$files = $_FILES;
				$spec_ids = $_POST['spec_id'];
				$spec_titles = $_POST['spec_title'];
				$specids = array();
				$len = count($spec_ids);
				$specids = array();
				$spec_items = array();
				$k = 0;
				while ($k < $len) {
					$spec_id = '';
					$get_spec_id = $spec_ids[$k];
					$a = array('goodsid' => $id, 'displayorder' => $k, 'title' => $spec_titles[$get_spec_id]);

					if (is_numeric($get_spec_id)) {
						Db::name('shop_goods_spec')->where('id',$get_spec_id)->update($a);
						$spec_id = $get_spec_id;
					} else {
						$spec_id = Db::name('shop_goods_spec')->insertGetId($a);;
					}

					$spec_item_ids = $_POST['spec_item_id_' . $get_spec_id];
					$spec_item_titles = $_POST['spec_item_title_' . $get_spec_id];
					$spec_item_shows = $_POST['spec_item_show_' . $get_spec_id];
					$spec_item_thumbs = $_POST['spec_item_thumb_' . $get_spec_id];
					$spec_item_oldthumbs = $_POST['spec_item_oldthumb_' . $get_spec_id];
					$spec_item_virtuals = $_POST['spec_item_virtual_' . $get_spec_id];
					$itemlen = count($spec_item_ids);
					$itemids = array();
					$n = 0;

					while ($n < $itemlen) {
						$item_id = '';
						$get_item_id = $spec_item_ids[$n];
						$d = array('specid' => $spec_id, 'displayorder' => $n, 'title' => $spec_item_titles[$n], 'show' => $spec_item_shows[$n], 'thumb' => trim($spec_item_thumbs[$n]), 'virtual' => ($data['type'] == 3 ? $spec_item_virtuals[$n] : 0));
						$f = 'spec_item_thumb_' . $get_item_id;

						if (is_numeric($get_item_id)) {
							Db::name('shop_goods_spec_item')->where('id',$get_item_id)->update($d);
							$item_id = $get_item_id;
						} else {
						 	$item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
						}

						$itemids[] = $item_id;
						$d['get_id'] = $get_item_id;
						$d['id'] = $item_id;
						$spec_items[] = $d;
						++$n;
					}

					if (0 < count($itemids)) {
						Db::name('shop_goods_spec_item')->where('specid',$spec_id)->where('id','not in',implode(',', $itemids))->delete();
					} else {
					 	Db::name('shop_goods_spec_item')->where('specid',$spec_id)->delete();
					}
					Db::name('shop_goods_spec')->where('id',$spec_id)->update(array('content' => serialize($itemids)));
					$specids[] = $spec_id;
					++$k;
				}

				if (0 < count($specids)) {
					Db::name('shop_goods_spec')->where('goodsid',$id)->where('id','not in',implode(',', $specids))->delete();
				} else {
				 	Db::name('shop_goods_spec')->where('goodsid',$id)->delete();
				}
				$optionArray = json_decode($_POST['optionArray'], true);
				$isdiscountDiscountsArray = json_decode($_POST['isdiscountDiscountsArray'], true);
				$discountArray = json_decode($_POST['discountArray'], true);
				$commissionArrayPost = json_decode($_POST["commissionArray"], true);
				$option_idss = $optionArray['option_ids'];
				$len = count($option_idss);
				$optionids = array();
				$levelArray = array();
				$isDiscountsArray = array();
				$commissionArray = array( );
				$commissionDefaultArray = array( );
				$k = 0;
				while ($k < $len) {
					$option_id = '';
					$ids = $option_idss[$k];
					$get_option_id = $optionArray['option_id'][$k];
					$idsarr = explode('_', $ids);
					$newids = array();

					foreach ($idsarr as $key => $ida ) {
						foreach ($spec_items as $it ) {
							while ($it['get_id'] == $ida) {
								$newids[] = $it['id'];
								break;
							}
						}
					}

					$newids = implode('_', $newids);
					$a = array('title' => $optionArray['option_title'][$k], 'productprice' => $optionArray['option_productprice'][$k], 'costprice' => $optionArray['option_costprice'][$k], 'marketprice' => $optionArray['option_marketprice'][$k], 'presellprice' => $optionArray['option_presellprice'][$k], 'stock' => $optionArray['option_stock'][$k], 'weight' => $optionArray['option_weight'][$k], 'goodssn' => $optionArray['option_goodssn'][$k], 'productsn' => $optionArray['option_productsn'][$k], 'goodsid' => $id, 'specs' => $newids, 'virtual' => ($data['type'] == 3 ? $optionArray['option_virtual'][$k] : 0));

					if ($goodstype == 4) {
						$a['presellprice'] = 0;
						$a['productprice'] = 0;
						$a['costprice'] = 0;
						$a['marketprice'] = input('intervalprice1/f');
					}

					$totalstocks += $a['stock'];

					if (empty($get_option_id)) {
						$option_id = Db::name('shop_goods_option')->insertGetId($a);
					} else {
					 	Db::name('shop_goods_option')->where('id',$get_option_id)->update($a);
						$option_id = $get_option_id;
					}

					$optionids[] = $option_id;

					foreach ($levels as $level ) {
						$levelArray[$level['key']]['option' . $option_id] = $discountArray['discount_' . $level['key']][$k];
						$isDiscountsArray[$level['key']]['option' . $option_id] = $isdiscountDiscountsArray['isdiscount_discounts_' . $level['key']][$k];
					}
					foreach( $commission_level as $level ) {
						if( $level["key"] == "default" ) {
							$commissionArray[$level["key"]]["option" . $option_id] = $commissionArrayPost["commission"]["commission_level_" . $level["key"] . "_" . $ids];
						} else {
							$commissionArray[$level["key"]]["option" . $option_id] = $commissionArrayPost["commission"]["commission_level_" . $level["id"] . "_" . $ids];
						}
					}
					++$k;
				}
				if (((int) input('discounts/a')['type'] == 1) && $data['hasoption']) {
					$discounts_arr = array('type' => (int) input('discounts/a')['type']);
					$discounts_arr = array_merge($discounts_arr, $levelArray);
					$discounts_json = json_encode($discounts_arr);
				} else {
					$discounts_json = ((is_array(input('discounts/a')) ? json_encode(input('discounts/a')) : json_encode(array())));
				}
				Db::name('shop_goods')->where('id',$id)->update(array('discounts' => $discounts_json));
				$has_merch = 0;
				$old_isdiscount_discounts = json_decode($item['isdiscount_discounts'], true);

				if (!(empty($old_isdiscount_discounts['merch']))) {
					$has_merch = 1;
				}
				if (!(empty($isDiscountsArray)) && $data['hasoption']) {
					$is_discounts_arr = array_merge(array('type' => 1), $isDiscountsArray);

					if ($has_merch == 1) {
						$is_discounts_arr['merch'] = $old_isdiscount_discounts['merch'];
					}
					$is_discounts_json = json_encode($is_discounts_arr);
				} else {
					foreach ($levels as $level ) {
						if ($level['key'] == 'default') {
							$isDiscountsDefaultArray[$level['key']]['option0'] = $_POST['isdiscount_discounts_level_' . $level['key'] . '_default'];
						}
						 else {
							$isDiscountsDefaultArray[$level['key']]['option0'] = $_POST['isdiscount_discounts_level_' . $level['id'] . '_default'];
						}
					}

					$is_discounts_arr = array_merge(array('type' => 0), $isDiscountsDefaultArray);

					if ($has_merch == 1) {
						$is_discounts_arr['merch'] = $old_isdiscount_discounts['merch'];
					}
					$is_discounts_json = ((is_array($is_discounts_arr) ? json_encode($is_discounts_arr) : json_encode(array())));
				}
				Db::name('shop_goods')->where('id',$id)->update(array('isdiscount_discounts' => $is_discounts_json));
				if( !empty($commissionArray) && $data["hasoption"] ) 
				{
					$commissionArray = array_merge(array( "type" => (int) $_POST["commission_type"] ), $commissionArray);
					$commission_arr = array( "commission" => (is_array($commissionArray) ? json_encode($commissionArray) : json_encode(array( ))) );
				}
				else 
				{
					foreach( $commission_level as $level ) 
					{
						if( $level["key"] == "default" ) 
						{
							if( !empty($_POST["commission_level_" . $level["key"] . "_default"]) ) 
							{
								foreach( $_POST["commission_level_" . $level["key"] . "_default"] as $key => $value ) 
								{
									$commissionDefaultArray[$level["key"]]["option0"][] = $value;
								}
							}
						}
						else 
						{
							if( !empty($_POST["commission_level_" . $level["id"] . "_default"]) ) 
							{
								foreach( $_POST["commission_level_" . $level["id"] . "_default"] as $key => $value ) 
								{
									$commissionDefaultArray[$level["key"]]["option0"][] = $value;
								}
							}
						}
					}
					$commissionDefaultArray = array_merge(array( "type" => (int) $_POST["commission_type"] ), $commissionDefaultArray);
					$commission_arr = array( "commission" => (is_array($commissionDefaultArray) ? json_encode($commissionDefaultArray) : json_encode(array( ))) );
				}
				Db::name('shop_goods')->where('id = ' . $id)->update($commission_arr);
				if ((0 < count($optionids)) && ($data['hasoption'] !== 0)) {
					Db::name('shop_goods_option')->where('goodsid',$id)->where('id','not in',implode(',', $optionids))->delete();
					$sql = 'update ' . tablename('shop_goods') . ' g set' . "\r\n" . ' g.minprice = (select min(marketprice) from ' . tablename('shop_goods_option') . ' where goodsid = ' . $id . '),' . "\r\n" . '            g.maxprice = (select max(marketprice) from ' . tablename('shop_goods_option') . ' where goodsid = ' . $id . ')' . "\r\n" . '            where g.id = ' . $id . ' and g.hasoption=1';
					Db::query($sql);
				} else {
					Db::name('shop_goods_option')->where('goodsid',$id)->delete();
					$sql = 'update ' . tablename('shop_goods') . ' set minprice = marketprice,maxprice = marketprice where id = ' . $id . ' and hasoption=0;';
					Db::query($sql);
				}

				$goodsinfo = Db::name('shop_goods')->where('id',$id)->field('id,title,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,isdiscount_discounts,sales,total,description,merchsale')->find();
				$goodsinfo = model('goods')->getOneMinPrice($goodsinfo);
				Db::name('shop_goods')->where('id',$id)->update(array('minprice' => $goodsinfo['minprice'], 'maxprice' => $goodsinfo['maxprice']));

				if (($data['hasoption'] !== 0) && ($data['totalcnf'] != 2) && empty($data['unite_total'])) {
					Db::name('shop_goods')->where('id',$id)->update(array('total' => $totalstocks));
				}
			    // 提交事务
			    Db::commit();
			} catch (\Exception $e) {
			    // 回滚事务
			    show_json(0,'操作失败');
			    Db::rollback();
			}
			show_json(1, array('url' => url('admin/goods/edit', array('id' => $id))));
		}

		if (!(empty($id))) {
			if (empty($item)) {
				$this->error('抱歉，商品不存在或是已经删除！');
			}
			$cates = explode(',', $item['cates']);
			$commission = json_decode($item["commission"], true);
			if( isset($commission["type"]) ) 
			{
				$commission_type = $commission["type"];
				unset($commission["type"]);
			}
			$buyagain_commission = array( );
			if( !empty($item["buyagain_commission"]) ) 
			{
				$buyagain_commission = json_decode($item["buyagain_commission"], true);
			}
			$discounts = json_decode($item['discounts'], true);
			$isdiscount_discounts = json_decode($item['isdiscount_discounts'], true);
			$allspecs = Db::name('shop_goods_spec')->where('goodsid',$id)->order('displayorder','asc')->select();
			foreach ($allspecs as &$s ) {
				$s['items'] = Db::name('shop_goods_spec_item')
					->alias('a')
					->join('shop_virtual_type b','b.id = a.virtual','left')
					->where('a.specid',$s['id'])
					->order('a.displayorder','asc')
					->field('a.id,a.specid,a.title,a.thumb,a.show,a.displayorder,a.valueId,a.virtual,b.title as title2')
					->select();
			}
			unset($s);
			$params = Db::name('shop_goods_param')->where('goodsid',$id)->order('displayorder','asc')->select();
			if (!(empty($item['thumb']))) {
				$piclist = array_merge(array($item['thumb']), iunserializer($item['thumb_url']));
			}
			$item['content'] = model('common')->html_to_images($item['content']);
			$html = '';
			$discounts_html = '';
			$commission_html = "";
			$isdiscount_discounts_html = '';
			$options = Db::name('shop_goods_option')->where('goodsid',$id)->order('id','asc')->select();
			$specs = array();
			if (0 < count($options)) {
				$specitemids = explode('_', $options[0]['specs']);

				foreach ($specitemids as $itemid ) {
					foreach ($allspecs as $ss ) {
						$items = $ss['items'];

						foreach ($items as $it ) {
							while ($it['id'] == $itemid) {
								$specs[] = $ss;
								break;
							}
						}
					}
				}

				$html = '';
				$html .= '<table class="table table-bordered table-condensed">';
				$html .= '<thead>';
				$html .= '<tr class="active">';
				$discounts_html .= '<table class="table table-bordered table-condensed">';
				$discounts_html .= '<thead>';
				$discounts_html .= '<tr class="active">';
				$commission_html .= "<table class=\"table table-bordered table-condensed\">";
				$commission_html .= "<thead>";
				$commission_html .= "<tr class=\"active\">";
				$isdiscount_discounts_html .= '<table class="table table-bordered table-condensed">';
				$isdiscount_discounts_html .= '<thead>';
				$isdiscount_discounts_html .= '<tr class="active">';
				$len = count($specs);
				$newlen = 1;
				$h = array();
				$rowspans = array();
				$i = 0;

				while ($i < $len) {
					$html .= '<th>' . $specs[$i]['title'] . '</th>';
					$discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
					$commission_html .= "<th>" . $specs[$i]["title"] . "</th>";
					$isdiscount_discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
					$itemlen = count($specs[$i]['items']);

					if ($itemlen <= 0) {
						$itemlen = 1;
					}


					$newlen *= $itemlen;
					$h = array();
					$j = 0;

					while ($j < $newlen) {
						$h[$i][$j] = array();
						++$j;
					}

					$l = count($specs[$i]['items']);
					$rowspans[$i] = 1;
					$j = $i + 1;

					while ($j < $len) {
						$rowspans[$i] *= count($specs[$j]['items']);
						++$j;
					}

					++$i;
				}

				$canedit = true;

				if ($canedit) {
					foreach ($levels as $level ) {
						$discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div><div class="input-group"><input type="text" class="form-control  input-sm discount_' . $level['key'] . '_all" VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'discount_' . $level['key'] . '\');"></a></span></div></div></th>';
						$isdiscount_discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div><div class="input-group"><input type="text" class="form-control  input-sm isdiscount_discounts_' . $level['key'] . '_all" VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'isdiscount_discounts_' . $level['key'] . '\');"></a></span></div></div></th>';
					}
					foreach( $commission_level as $level ) 
					{
						$commission_html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">" . $level["levelname"] . "</div></div></th>";
					}

					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">库存</div><div class="input-group"><input type="text" class="form-control input-sm option_stock_all"  VALUE=""/><span class="input-group-addon" ><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_stock\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">预售价</div><div class="input-group"><input type="text" class="form-control  input-sm option_presell_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_presell\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">现价</div><div class="input-group"><input type="text" class="form-control  input-sm option_marketprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">原价</div><div class="input-group"><input type="text" class="form-control input-sm option_productprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">成本价</div><div class="input-group"><input type="text" class="form-control input-sm option_costprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_costprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">编码</div><div class="input-group"><input type="text" class="form-control input-sm option_goodssn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_goodssn\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">条码</div><div class="input-group"><input type="text" class="form-control input-sm option_productsn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productsn\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">重量（克）</div><div class="input-group"><input type="text" class="form-control input-sm option_weight_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></div></th>';
				} else {
					foreach ($levels as $level ) {
						$discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div></div></th>';
						$isdiscount_discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div></div></th>';
					}
					foreach( $commission_level as $level ) 
					{
						$commission_html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">" . $level["levelname"] . "</div></div></th>";
					}

					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">库存</div></div></th>';
					$html .= '<th"><div class=""><div style="padding-bottom:10px;text-align:center;">预售价格</div></div></th>';
					$html .= '<th"><div class=""><div style="padding-bottom:10px;text-align:center;">销售价格</div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">市场价格</div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">成本价格</div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">商品编码</div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">商品条码</div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">重量（克）</div></th>';
				}

				$html .= '</tr></thead>';
				$discounts_html .= '</tr></thead>';
				$isdiscount_discounts_html .= '</tr></thead>';
				$commission_html .= "</tr></thead>";
				$m = 0;

				while ($m < $len) {
					$k = 0;
					$kid = 0;
					$n = 0;
					$j = 0;

					while ($j < $newlen) {
						$rowspan = $rowspans[$m];

						if (($j % $rowspan) == 0) {
							$h[$m][$j] = array('html' => '<td class=\'full\' rowspan=\'' . $rowspan . '\'>' . $specs[$m]['items'][$kid]['title'] . '</td>', 'id' => $specs[$m]['items'][$kid]['id']);
						}
						 else {
							$h[$m][$j] = array('html' => '', 'id' => $specs[$m]['items'][$kid]['id']);
						}

						++$n;

						if ($n == $rowspan) {
							++$kid;

							if ((count($specs[$m]['items']) - 1) < $kid) {
								$kid = 0;
							}


							$n = 0;
						}


						++$j;
					}

					++$m;
				}

				$hh = '';
				$dd = '';
				$isdd = '';
				$cc = '';
				$i = 0;

				while ($i < $newlen) {
					$hh .= '<tr>';
					$dd .= '<tr>';
					$isdd .= '<tr>';
					$cc .= '<tr>';
					$ids = array();
					$j = 0;

					while ($j < $len) {
						$hh .= $h[$j][$i]['html'];
						$dd .= $h[$j][$i]['html'];
						$isdd .= $h[$j][$i]['html'];
						$cc .= $h[$j][$i]['html'];
						$ids[] = $h[$j][$i]['id'];
						++$j;
					}

					$ids = implode('_', $ids);
					$val = array('id' => '', 'title' => '', 'stock' => '', 'presell' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');
					$discounts_val = array('id' => '', 'title' => '', 'level' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');
					$isdiscounts_val = array('id' => '', 'title' => '', 'level' => '', 'costprice' => '', 'productprice' => '', 'marketprice' => '', 'weight' => '', 'virtual' => '');

					foreach ($levels as $level ) {
						$discounts_val[$level['key']] = '';
						$isdiscounts_val[$level['key']] = '';
					}

					foreach ($options as $o ) {
						while ($ids === $o['specs']) {
							$val = array('id' => $o['id'], 'title' => $o['title'], 'stock' => $o['stock'], 'costprice' => $o['costprice'], 'productprice' => $o['productprice'], 'presell' => $o['presellprice'], 'marketprice' => $o['marketprice'], 'goodssn' => $o['goodssn'], 'productsn' => $o['productsn'], 'weight' => $o['weight'], 'virtual' => $o['virtual']);
							$discount_val = array('id' => $o['id']);

							foreach ($levels as $level ) {
								$discounts_val[$level['key']] = ((is_string($discounts[$level['key']]) ? '' : $discounts[$level['key']]['option' . $o['id']]));
								$isdiscounts_val[$level['key']] = ((is_string($isdiscount_discounts[$level['key']]) ? '' : $isdiscount_discounts[$level['key']]['option' . $o['id']]));
							}
							break;
						}
					}

					if ($canedit) {
						foreach ($levels as $level ) {
							$dd .= '<td>';
							$isdd .= '<td>';

							if ($level['key'] == 'default') {
								$dd .= '<input data-name="discount_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control discount_' . $level['key'] . ' discount_' . $level['key'] . '_' . $ids . '" value="' . $discounts_val[$level['key']] . '"/> ';
								$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_' . $level['key'] . ' isdiscount_discounts_' . $level['key'] . '_' . $ids . '" value="' . $isdiscounts_val[$level['key']] . '"/> ';
							}
							 else {
								$dd .= '<input data-name="discount_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control discount_level' . $level['id'] . ' discount_level' . $level['id'] . '_' . $ids . '" value="' . $discounts_val['level' . $level['id']] . '"/> ';
								$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_level' . $level['id'] . ' isdiscount_discounts_level' . $level['id'] . '_' . $ids . '" value="' . $isdiscounts_val['level' . $level['id']] . '"/> ';
							}

							$dd .= '</td>';
							$isdd .= '</td>';
						}

						$dd .= '<input data-name="discount_id_' . $ids . '"  type="hidden" class="form-control discount_id discount_id_' . $ids . '" value="' . $discounts_val['id'] . '"/>';
						$dd .= '<input data-name="discount_ids"  type="hidden" class="form-control discount_ids discount_ids_' . $ids . '" value="' . $ids . '"/>';
						$dd .= '<input data-name="discount_title_' . $ids . '"  type="hidden" class="form-control discount_title discount_title_' . $ids . '" value="' . $discounts_val['title'] . '"/>';
						$dd .= '<input data-name="discount_virtual_' . $ids . '"  type="hidden" class="form-control discount_title discount_virtual_' . $ids . '" value="' . $discounts_val['virtual'] . '"/>';
						$dd .= '</tr>';
						$isdd .= '<input data-name="isdiscount_discounts_id_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_id isdiscount_discounts_id_' . $ids . '" value="' . $isdiscounts_val['id'] . '"/>';
						$isdd .= '<input data-name="isdiscount_discounts_ids"  type="hidden" class="form-control isdiscount_discounts_ids isdiscount_discounts_ids_' . $ids . '" value="' . $ids . '"/>';
						$isdd .= '<input data-name="isdiscount_discounts_title_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_title_' . $ids . '" value="' . $isdiscounts_val['title'] . '"/>';
						$isdd .= '<input data-name="isdiscount_discounts_virtual_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_virtual_' . $ids . '" value="' . $isdiscounts_val['virtual'] . '"/>';
						$isdd .= '</tr>';

						foreach( $commission_level as $level ) 
						{
							$cc .= "<td>";
							if( !empty($commission_val) && isset($commission_val[$level["key"]]) ) 
							{
								foreach( $commission_val as $c_key => $c_val ) 
								{
									if( $c_key == $level["key"] ) 
									{
										if( $level["key"] == "default" ) 
										{
											for( $c_i = 0; $c_i < $shopset_level; $c_i++ ) 
											{
												$cc .= "<input data-name=\"commission_level_" . $level["key"] . "_" . $ids . "\"  type=\"text\" class=\"form-control commission_" . $level["key"] . " commission_" . $level["key"] . "_" . $ids . "\" value=\"" . $c_val[$c_i] . "\" style=\"display:inline;width: " . 96 / $shopset_level . "%;\"/> ";
											}
										}
										else 
										{
											for( $c_i = 0; $c_i < $shopset_level; $c_i++ ) 
											{
												$cc .= "<input data-name=\"commission_level_" . $level["id"] . "_" . $ids . "\"  type=\"text\" class=\"form-control commission_level" . $level["id"] . " commission_level" . $level["id"] . "_" . $ids . "\" value=\"" . $c_val[$c_i] . "\" style=\"display:inline;width: " . 96 / $shopset_level . "%;\"/> ";
											}
										}
									}
								}
							}
							else 
							{
								if( $level["key"] == "default" ) 
								{
									for( $c_i = 0; $c_i < $shopset_level; $c_i++ ) 
									{
										$cc .= "<input data-name=\"commission_level_" . $level["key"] . "_" . $ids . "\"  type=\"text\" class=\"form-control commission_" . $level["key"] . " commission_" . $level["key"] . "_" . $ids . "\" value=\"\" style=\"display:inline;width: " . 96 / $shopset_level . "%;\"/> ";
									}
								}
								else 
								{
									for( $c_i = 0; $c_i < $shopset_level; $c_i++ ) 
									{
										$cc .= "<input data-name=\"commission_level_" . $level["id"] . "_" . $ids . "\"  type=\"text\" class=\"form-control commission_level" . $level["id"] . " commission_level" . $level["id"] . "_" . $ids . "\" value=\"\" style=\"display:inline;width: " . 96 / $shopset_level . "%;\"/> ";
									}
								}
							}
							$cc .= "</td>";
						}
						$cc .= "<input data-name=\"commission_id_" . $ids . "\"  type=\"hidden\" class=\"form-control commission_id commission_id_" . $ids . "\" value=\"" . $commissions_val["id"] . "\"/>";
						$cc .= "<input data-name=\"commission_ids\"  type=\"hidden\" class=\"form-control commission_ids commission_ids_" . $ids . "\" value=\"" . $ids . "\"/>";
						$cc .= "<input data-name=\"commission_title_" . $ids . "\"  type=\"hidden\" class=\"form-control commission_title commission_title_" . $ids . "\" value=\"" . $commissions_val["title"] . "\"/>";
						$cc .= "<input data-name=\"commission_virtual_" . $ids . "\"  type=\"hidden\" class=\"form-control commission_title commission_virtual_" . $ids . "\" value=\"" . $commissions_val["virtual"] . "\"/>";
						$cc .= "</tr>";
						$hh .= '<td>';
						$hh .= '<input data-name="option_stock_' . $ids . '"  type="text" class="form-control option_stock option_stock_' . $ids . '" value="' . $val['stock'] . '"/>';
						$hh .= '</td>';
						$hh .= '<input data-name="option_id_' . $ids . '"  type="hidden" class="form-control option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
						$hh .= '<input data-name="option_ids"  type="hidden" class="form-control option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
						$hh .= '<input data-name="option_title_' . $ids . '"  type="hidden" class="form-control option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
						$hh .= '<input data-name="option_virtual_' . $ids . '"  type="hidden" class="form-control option_virtual option_virtual_' . $ids . '" value="' . $val['virtual'] . '"/>';
						$hh .= '<td><input data-name="option_presell_' . $ids . '" type="text" class="form-control option_presell option_presell_' . $ids . '" value="' . $val['presell'] . '"/></td>';
						$hh .= '<td><input data-name="option_marketprice_' . $ids . '" type="text" class="form-control option_marketprice option_marketprice_' . $ids . '" value="' . $val['marketprice'] . '"/></td>';
						$hh .= '<td><input data-name="option_productprice_' . $ids . '" type="text" class="form-control option_productprice option_productprice_' . $ids . '" " value="' . $val['productprice'] . '"/></td>';
						$hh .= '<td><input data-name="option_costprice_' . $ids . '" type="text" class="form-control option_costprice option_costprice_' . $ids . '" " value="' . $val['costprice'] . '"/></td>';
						$hh .= '<td><input data-name="option_goodssn_' . $ids . '" type="text" class="form-control option_goodssn option_goodssn_' . $ids . '" " value="' . $val['goodssn'] . '"/></td>';
						$hh .= '<td><input data-name="option_productsn_' . $ids . '" type="text" class="form-control option_productsn option_productsn_' . $ids . '" " value="' . $val['productsn'] . '"/></td>';
						$hh .= '<td><input data-name="option_weight_' . $ids . '" type="text" class="form-control option_weight option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
						$hh .= '</tr>';
					} else {
						$hh .= '<td>' . $val['stock'] . '</td>';
						$hh .= '<td>' . $val['presell'] . '</td>';
						$hh .= '<td>' . $val['marketprice'] . '</td>';
						$hh .= '<td>' . $val['productprice'] . '</td>';
						$hh .= '<td>' . $val['costprice'] . '</td>';
						$hh .= '<td>' . $val['goodssn'] . '</td>';
						$hh .= '<td>' . $val['productsn'] . '</td>';
						$hh .= '<td>' . $val['weight'] . '</td>';
						$hh .= '</tr>';
					}

					++$i;
				}

				$discounts_html .= $dd;
				$discounts_html .= '</table>';
				$isdiscount_discounts_html .= $isdd;
				$isdiscount_discounts_html .= '</table>';
				$html .= $hh;
				$html .= '</table>';
				$commission_html .= $cc;
				$commission_html .= "</table>";
			}
			if ($item['showlevels'] != '') {
				$item['showlevels'] = explode(',', $item['showlevels']);
			}
			if ($item['buylevels'] != '') {
				$item['buylevels'] = explode(',', $item['buylevels']);
			}
			if ($item['showgroups'] != '') {
				$item['showgroups'] = explode(',', $item['showgroups']);
			}
			if ($item['buygroups'] != '') {
				$item['buygroups'] = explode(',', $item['buygroups']);
			}
			if ($merchid == 0) {
				$stores = array();
				if (!(empty($item['storeids']))) {
					$stores = Db::name('shop_store')->where('id','in',$item['storeids'])->select();
				}
			}
			if( !empty($item["noticemid"]) ) {
				$salers = array( );
				if( isset($item["noticemid"]) && !empty($item["noticemid"]) ) 
				{
					$openids = array( );
					$strsopenids = explode(",", $item["noticemid"]);
					foreach( $strsopenids as $openid ) 
					{
						$openids[] = "'" . $openid . "'";
					}
					$salers = Db::name('member')->where("id in (" . implode(",", $openids) . ")")->field('id,nickname,avatar')->select();
				}
			}
			$dispatch_data = Db::name('shop_dispatch')->where('merchid',$merchid)->where('enabled',1)->order('displayorder','desc')->select();
			if ($merchid == 0) {
				$details = Db::name('shop_goods')->whereNotNull('detail_shopname')->group('detail_shopname')->field('detail_logo,detail_shopname,detail_btntext1, detail_btnurl1 ,detail_btntext2,detail_btnurl2,detail_totaltitle')->select();

				foreach ($details as &$d ) {
					$d['detail_logo_url'] = tomedia($d['detail_logo']);
				}
				unset($d);
			}
		}
		$this->assign(['item'=>$item,'category'=>$category,'merchid'=>$merchid,'dispatch_data'=>$dispatch_data,'new_area'=>$new_area,'address_street'=>$address_street,'levels'=>$levels,'groups'=>$groups,'virtual_types'=>$virtual_types,'allspecs'=>$allspecs,'cates'=>$cates,'piclist'=>$piclist,'params'=>$params,'html'=>$html,'discounts_html'=>$discounts_html,'isdiscount_discounts_html'=>$isdiscount_discounts_html,'areas'=>$areas,'labelname'=>$labelname,'intervalprices'=>$intervalprices,'details'=>$details,'discounts'=>$discounts,'stores'=>$stores,'salers'=>$salers,'endtime'=>$endtime,'noticetype'=>$noticetype,'tab'=>$tab,'com_set'=>$com_set,'commission_level'=>$commission_level,'commission_html'=>$commission_html,'shopset_level'=>$shopset_level,'commission_type'=>$commission_type,'commission'=>$commission]);
		return $this->fetch('goods/post');
	}

	public function quick()
	{
		$merchid = input('merchid/d',0);
		if (Request::instance()->isPost()) {
			$data = array('title' => trim(input('goodsname')), 'unit' => trim(input('unit')), 'keywords' => trim(input('keywords')), 'type' => input('type/d',0), 'thumb_first' => input('thumb_first/d',0), 'isrecommand' => input('isrecommand/d',0), 'isnew' => input('isnew/d',0), 'ishot' => input('ishot/d',0), 'issendfree' => input('issendfree/d',0), 'isnodiscount' => input('isnodiscount/d',0), 'marketprice' => input('marketprice/f'), 'minprice' => input('marketprice/f'), 'maxprice' => input('marketprice/f'), 'productprice' => trim(input('productprice')), 'costprice' => input('costprice'), 'virtualsend' => input('virtualsend/d',0), 'virtualsendcontent' => trim(input('virtualsendcontent','')), 'virtual' => input('type/d') == 3 ? input('virtual/d') : 0, 'cash' => input('cash/d',0), 'cashier' => input('cashier/d',0), 'invoice' => input('invoice/d',0), 'dispatchtype' => input('dispatchtype/d',0), 'dispatchprice' => trim(input('dispatchprice','')), 'dispatchid' => input('dispatchid/d',0), 'status' => input('status/d',0), 'goodssn' => trim(input('goodssn','')), 'productsn' => trim(input('productsn','')), 'weight' => input('weight'), 'total' => input('total/d',0), 'showtotal' => input('showtotal/d',0), 'totalcnf' => input('totalcnf/d',0), 'hasoption' => input('hasoption/d',0), 'subtitle' => trim(input('subtitle','')), 'shorttitle' => trim(input('shorttitle','')), 'content' => model('common')->html_images($_POST['content']), 'createtime' => time());
			$cateset = model('common')->getSysset('shop');
			$pcates = array();
			$ccates = array();
			$tcates = array();
			$fcates = array();
			$cates = array();
			$pcateid = 0;
			$ccateid = 0;
			$tcateid = 0;
			if (is_array($_POST['cates'])) {
				$cates = input('cates/a');

				foreach ($cates as $key => $cid) {
					$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();

					if ($c['level'] == 1) {
						$pcates[] = $cid;
					}
					else if ($c['level'] == 2) {
						$ccates[] = $cid;
					}
					else {
						if ($c['level'] == 3) {
							$tcates[] = $cid;
						}
					}

					if ($key == 0) {
						if ($c['level'] == 1) {
							$pcateid = $cid;
						}
						else if ($c['level'] == 2) {
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow['parentid'];
							$ccateid = $cid;
						}
						else {
							if ($c['level'] == 3) {
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate['parentid'];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate['parentid'];
							}
						}
					}
				}
			}
			$data['pcate'] = $pcateid;
			$data['ccate'] = $ccateid;
			$data['tcate'] = $tcateid;
			$data['cates'] = implode(',', $cates);
			$data['pcates'] = implode(',', $pcates);
			$data['ccates'] = implode(',', $ccates);
			$data['tcates'] = implode(',', $tcates);

			if (is_array($_POST['thumbs'])) {
				$thumbs = $_POST['thumbs'];
				$thumb_url = array();

				foreach ($thumbs as $th) {
					$thumb_url[] = trim($th);
				}

				$data['thumb'] = $thumb_url[0];
				unset($thumb_url[0]);
				$data['thumb_url'] = serialize($thumb_url);
			}
			if ($data['type'] == 4) {
				$intervalfloor = input('intervalfloor/d',0);
				if ((3 < $intervalfloor) || ($intervalfloor < 1)) {
					show_json(0, '请至少添加一个区间价格！');
				}

				$intervalprices = array();

				if (0 < $intervalfloor) {
					if (input('intervalnum1/d',0) <= 0) {
						show_json(0, '请设置起批发量！');
					}

					if (input('intervalprice1/f') <= 0) {
						show_json(0, '批发价必须大于0！');
					}

					$intervalprices[] = array('intervalnum' => input('intervalnum1/d',0), 'intervalprice' => input('intervalprice1/f'));
				}

				if (1 < $intervalfloor) {
					if (input('intervalnum2/d',0) <= 0) {
						show_json(0, '请设置起批发量！');
					}

					if (input('intervalnum2/d',0) <= input('intervalnum1/d',0)) {
						show_json(0, '批发量需大于上级批发量！');
					}

					if (input('intervalprice1/f') <= input('intervalprice2/f')) {
						show_json(0, '批发价需小于上级批发价！');
					}

					$intervalprices[] = array('intervalnum' => input('intervalnum2/d',0), 'intervalprice' => input('intervalprice2/f'));
				}

				if (2 < $intervalfloor) {
					if (input('intervalnum3/d',0) <= 0) {
						show_json(0, '请设置起批发量！');
					}

					if (input('intervalnum3/d',0) <= input('intervalnum2/d',0)) {
						show_json(0, '批发量需大于上级批发量！');
					}

					if (input('intervalprice2/f') <= input('intervalprice3/f')) {
						show_json(0, '批发价需小于上级批发价！');
					}

					$intervalprices[] = array('intervalnum' => input('intervalnum3/d',0), 'intervalprice' => input('intervalprice3/f'));
				}

				$intervalprice = iserializer($intervalprices);
				$data['intervalfloor'] = $intervalfloor;
				$data['intervalprice'] = $intervalprice;
				$data['minbuy'] = input('intervalnum1/d',0);
				$data['marketprice'] = input('intervalprice1/f');
				$data['productprice'] = 0;
				$data['costprice'] = 0;
			}
			$id = Db::name('shop_goods')->insertGetId($data);
			model('shop')->plog('goods.add', '添加商品 ID: ' . $id . '<br>');

			$spec_ids = $_POST['spec_id'];
			$spec_titles = $_POST['spec_title'];
			$specids = array();
			$len = count($spec_ids);
			$specids = array();
			$spec_items = array();
			$k = 0;

			while ($k < $len) {
				$spec_id = '';
				$get_spec_id = $spec_ids[$k];
				$a = array('goodsid' => $id, 'displayorder' => $k, 'title' => $spec_titles[$get_spec_id]);

				if (is_numeric($get_spec_id)) {
					Db::name('shop_goods_spec')->where('id',$get_spec_id)->update($a);
					$spec_id = $get_spec_id;
				}
				else {					
					$spec_id = Db::name('shop_goods_spec')->insertGetId($a);
				}

				$spec_item_ids = $_POST['spec_item_id_' . $get_spec_id];
				$spec_item_titles = $_POST['spec_item_title_' . $get_spec_id];
				$spec_item_shows = $_POST['spec_item_show_' . $get_spec_id];
				$spec_item_thumbs = $_POST['spec_item_thumb_' . $get_spec_id];
				$spec_item_oldthumbs = $_POST['spec_item_oldthumb_' . $get_spec_id];
				$spec_item_virtuals = $_POST['spec_item_virtual_' . $get_spec_id];
				$itemlen = count($spec_item_ids);
				$itemids = array();
				$n = 0;

				while ($n < $itemlen) {
					$item_id = '';
					$get_item_id = $spec_item_ids[$n];
					$d = array('specid' => $spec_id, 'displayorder' => $n, 'title' => $spec_item_titles[$n], 'show' => $spec_item_shows[$n], 'thumb' => trim($spec_item_thumbs[$n]), 'virtual' => $data['type'] == 3 ? $spec_item_virtuals[$n] : 0);
					$f = 'spec_item_thumb_' . $get_item_id;

					if (is_numeric($get_item_id)) {
						Db::name('shop_goods_spec_item')->where('id',$get_item_id)->update($d);
						$item_id = $get_item_id;
					}
					else {
						$item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
					}

					$itemids[] = $item_id;
					$d['get_id'] = $get_item_id;
					$d['id'] = $item_id;
					$spec_items[] = $d;
					++$n;
				}

				if (0 < count($itemids)) {
					Db::name('shop_goods_spec_item')->where('specid',$spec_id)->where('id','not in',implode(',', $itemids))->delete();
				}
				else {
					Db::name('shop_goods_spec_item')->where('specid',$spec_id)->delete();
				}
				Db::name('shop_goods_spec')->where('id',$spec_id)->update(array('content' => serialize($itemids)));
				$specids[] = $spec_id;
				++$k;
			}
			if (0 < count($specids)) {
				Db::name('shop_goods_spec')->where('goodsid',$id)->where('id','not in',implode(',', $specids))->delete();
			}
			else {
				Db::name('shop_goods_spec')->where('goodsid',$id)->delete();
			}

			$totalstocks = 0;
			$optionArray = json_decode($_POST['optionArray'], true);
			$option_idss = $optionArray['option_ids'];
			$len = count($option_idss);
			$optionids = array();
			$k = 0;
			while ($k < $len) {
				$option_id = '';
				$ids = $option_idss[$k];
				$get_option_id = $optionArray['option_id'][$k];
				$idsarr = explode('_', $ids);
				$newids = array();

				foreach ($idsarr as $key => $ida) {
					foreach ($spec_items as $it) {
						if ($it['get_id'] == $ida) {
							$newids[] = $it['id'];
							break;
						}
					}
				}

				$newids = implode('_', $newids);
				$a = array('title' => $optionArray['option_title'][$k], 'productprice' => $optionArray['option_productprice'][$k], 'costprice' => $optionArray['option_costprice'][$k], 'marketprice' => $optionArray['option_marketprice'][$k],'presellprice' => $optionArray['option_presellprice'][$k],  'stock' => $optionArray['option_stock'][$k], 'weight' => $optionArray['option_weight'][$k], 'goodssn' => $optionArray['option_goodssn'][$k], 'productsn' => $optionArray['option_productsn'][$k], 'goodsid' => $id, 'specs' => $newids, 'virtual' => $data['type'] == 3 ? $optionArray['option_virtual'][$k] : 0);

				if ($data['type'] == 4) {
					$a['presellprice'] = 0;
					$a['productprice'] = 0;
					$a['costprice'] = 0;
					$a['marketprice'] = input('intervalprice1');
				}

				$totalstocks += $a['stock'];
				$option_id = Db::name('shop_goods_option')->insertGetId($a);
				$optionids[] = $option_id;
				if ((0 < count($optionids)) && ($data['hasoption'] !== 0)) {
					Db::name('shop_goods_option')->where('goodsid',$id)->where('id','not in',implode(',', $optionids))->delete();
					$sql = 'update ' . tablename('shop_goods') . ' g set g.minprice = (select min(marketprice) from ' . tablename('shop_goods_option') . ' where goodsid = ' . $id . '), g.maxprice = (select max(marketprice) from ' . tablename('shop_goods_option') . ' where goodsid = ' . $id . ") where g.id = " . $id . ' and g.hasoption=1';
					Db::query($sql);
				}
				else {
					Db::name('shop_goods_option')->where('goodsid',$id)->delete();
					$sql = 'update `shop_goods` set minprice = marketprice,maxprice = marketprice where id = ' . $id . ' and hasoption=0;';
					Db::query($sql);
				}
				++$k;
			}
			$goodsinfo = Db::name('shop_goods')->where('id',$id)->field('id,title,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,isdiscount_discounts,sales,total,description,merchsale')->find();
			$goodsinfo = model('goods')->getOneMinPrice($goodsinfo);
			Db::name('shop_goods')->where('id',$id)->update(array('minprice' => $goodsinfo['minprice'], 'maxprice' => $goodsinfo['maxprice']));
			if (($data['hasoption'] !== 0) && ($data['totalcnf'] != 2) && empty($data['unite_total'])) {
				Db::name('shop_goods')->where('id',$id)->update(array('total' => $totalstocks));
			}

			show_json(1, array('url' => url('admin/goods/edit', array('id' => $id))));
		}

		$statustimestart = time();
		$statustimeend = strtotime('+1 month');
		$category = model('shop')->getFullCategory(true, true);
		$levels = model('member')->getLevels();
		foreach ($levels as &$l) {
			$l['key'] = 'level' . $l['id'];
		}
		unset($l);
		$dispatch_data = Db::name('shop_dispatch')->where('merchid',$merchid)->where('enabled',1)->order('displayorder','desc')->select();
		$levels = array_merge(array( array('id' => 0, 'key' => 'default', 'levelname' => empty($shopset['shop']['levelname']) ? '默认会员' : $shopset['shop']['levelname']) ), $levels);
		$this->assign(['statustimestart'=>$statustimestart,'category'=>$category,'levels'=>$levels,'dispatch_data'=>$dispatch_data]);
		return $this->fetch('goods/quick');
	}

	public function tpl()
	{
		$tpl = trim(input('tpl'));
		if ($tpl == 'option') {
			$tag = random(32);
			$this->assign(['tag'=>$tag]);
			return $this->fetch('goods/tpl/option');
		} else if ($tpl == 'spec') {
			$spec = array('id' => random(32), 'title' => input('title'));
			$this->assign(['spec'=>$spec]);
			return $this->fetch('goods/tpl/spec');
		} else if ($tpl == 'specitem') {
			$spec = array('id' => input('specid'));
			$specitem = array('id' => random(32), 'title' => input('title'), 'show' => 1);
			$this->assign(['spec'=>$spec,'specitem'=>$specitem]);
			return $this->fetch('goods/tpl/spec_item');
		} else {
			if ($tpl == 'param') {
				$tag = random(32);
				$this->assign(['tag'=>$tag]);
				return $this->fetch('goods/tpl/param');
			}
		}
	}

	public function query()
	{
		$kwd = trim(input('keyword'));
		$type = intval(input('type'));
		$live = intval(input('live'));
		$condition = ' 1 and status=1 and deleted=0 ';

		if (!empty($kwd)) {
			$condition .= ' AND (`title` LIKE "%' . $kwd . '%" OR `keywords` LIKE "%' . $kwd . '%")';
		}
		if (empty($type)) {
			$condition .= ' AND `type` != 10 ';
		} else {
			$condition .= ' AND `type` = ' . $type;
		}

		$ds = Db::name('shop_goods')->where($condition)->field('id,title,thumb,marketprice,productprice,share_title,share_icon,description,minprice,costprice,total,sales,islive,liveprice')->select();

		if (input('suggest')) {
			exit(json_encode(array('value' => $ds)));
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('');
	}

	public function delete()
	{
		$id = input('id/d',0);

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('goods.delete', '删除商品 ID: ' . $item['id'] . ' 商品名称: ' . $item['title'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function status()
	{
		$id = input('id/d',0);
		if (empty($id)) {
			$id = input('ids/a');
		}
		else {
			Db::name('shop_goods')->where('id',$id)->setField('newgoods',0);
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title,status,isstatustime,statustimestart,statustimeend')->select();
		foreach ($items as $item) {
			if (0 < $item['isstatustime']) {
				if ((0 < input('status/d',0)) && ($item['statustimestart'] < time()) && (time() < $item['statustimeend'])) {
				}
				else {
					show_json(0, '商品 [' . $item['title'] . '] 上架时间不符合要求！');
				}
			}
			Db::name('shop_goods')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('goods.edit', ('修改商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . input('status/d')) == 1 ? '上架' : '下架');
		}
		show_json(1, array('url' => referer()));
	}

	public function checked()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->setField('checked',input('checked/d'));
			model('shop')->plog('goods.edit', '修改商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . (input('checked/d') == 0 ? '审核通过' : '审核中'));
		}

		show_json(1, array('url' => referer()));
	}

	public function delete1()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->delete();
			model('shop')->plog('goods.edit', '从回收站彻底删除商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function ajax_batchcates() 
	{
		$iscover = $_POST["iscover"];
		$goodsids = $_POST["goodsids"];
		$cates = $_POST["cates"];
		$data = array( );
		$reust_cates = $this->reust_cates($cates);
		foreach( $goodsids as $goodsid ) 
		{
			if( !empty($iscover) ) 
			{
				$data = $reust_cates;
				$data["cates"] = implode(",", $data["cates"]);
				$data["pcates"] = implode(",", $data["pcates"]);
				$data["ccates"] = implode(",", $data["ccates"]);
				$data["tcates"] = implode(",", $data["tcates"]);
				Db::name('shop_goods')->where('id',$goodsid)->update($data);
			}
			else 
			{
				$goods = Db::name('shop_goods')->where('id',$goodsid)->field('pcate,ccate,tcate,cates,pcates,ccates,tcates')->find();
				if( !empty($goods["cates"]) ) 
				{
					$goods_cates = explode(",", $goods["cates"]);
					if( !empty($reust_cates["cates"]) ) 
					{
						$data["cates"] = implode(",", array_unique(array_merge($goods_cates, $reust_cates["cates"]), SORT_NUMERIC));
					}
				}
				if( !empty($goods["pcates"]) ) 
				{
					$goods_pcates = explode(",", $goods["pcates"]);
					if( !empty($reust_cates["pcates"]) ) 
					{
						$data["pcates"] = implode(",", array_unique(array_merge($goods_pcates, $reust_cates["pcates"]), SORT_NUMERIC));
					}
				}
				if( !empty($goods["ccates"]) ) 
				{
					$goods_ccates = explode(",", $goods["ccates"]);
					if( !empty($reust_cates["ccates"]) ) 
					{
						$data["ccates"] = implode(",", array_unique(array_merge($goods_ccates, $reust_cates["ccates"]), SORT_NUMERIC));
					}
				}
				if( !empty($goods["tcates"]) ) 
				{
					$goods_tcates = explode(",", $goods["tcates"]);
					if( !empty($reust_cates["tcates"]) ) 
					{
						$data["tcates"] = implode(",", array_unique(array_merge($goods_tcates, $reust_cates["tcates"]), SORT_NUMERIC));
					}
				}
				if( !empty($reust_cates["pcate"]) ) 
				{
					$data["pcate"] = $reust_cates["pcate"];
				}
				if( !empty($reust_cates["ccate"]) ) 
				{
					$data["ccate"] = $reust_cates["ccate"];
				}
				if( !empty($reust_cates["tcate"]) ) 
				{
					$data["tcate"] = $reust_cates["tcate"];
				}
				Db::name('shop_goods')->where('id',$goodsid)->update($data);
			}
		}
		show_json(1);
	}

	public function reust_cates($param_cates) 
	{
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$cates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($param_cates) ) 
		{
			foreach( $param_cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$data["pcate"] = $pcateid;
		$data["ccate"] = $ccateid;
		$data["tcate"] = $tcateid;
		$data["cates"] = $param_cates;
		$data["pcates"] = $pcates;
		$data["ccates"] = $ccates;
		$data["tcates"] = $tcates;
		return $data;
	}

	public function restore()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('shop_goods')->where('id',$item['id'])->setField('deleted',0);
			model('shop')->plog('goods.edit', '从回收站恢复商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function property()
	{
		$id = input('id/d');
		$type = input('type');
		$data = input('data/d');

		if (in_array($type, array('new', 'hot', 'recommand', 'discount', 'time', 'sendfree', 'nodiscount'))) {
			Db::name('shop_goods')->where('id',$id)->setField('is' . $type,$data);
			if ($type == 'new') {
				$typestr = '新品';
			}
			else if ($type == 'hot') {
				$typestr = '热卖';
			}
			else if ($type == 'recommand') {
				$typestr = '推荐';
			}
			else if ($type == 'discount') {
				$typestr = '促销';
			}
			else if ($type == 'time') {
				$typestr = '限时卖';
			}
			else if ($type == 'sendfree') {
				$typestr = '包邮';
			}
			else {
				if ($type == 'nodiscount') {
					$typestr = '不参与折扣状态';
				}
			}

			model('shop')->plog('goods.edit', '修改商品' . $typestr . '状态   ID: ' . $id);
		}

		if (in_array($type, array('status'))) {
			Db::name('shop_goods')->where('id',$id)->setField($type,$data);
			model('shop')->plog('goods.edit', '修改商品上下架状态   ID: ' . $id);
		}

		if (in_array($type, array('type'))) {
			Db::name('shop_goods')->where('id',$id)->setField($type,$data);
			model('shop')->plog('goods.edit', '修改商品类型   ID: ' . $id);
		}

		show_json(1);
	}

	public function change()
	{
		$id = input('id/d');

		if (empty($id)) {
			show_json(0, array('message' => '参数错误'));
		} else {
			Db::name('shop_goods')->where('id',$id)->setField('newgoods',0);
		}

		$type = trim(input('type'));
		$value = trim(input('value'));

		if (!in_array($type, array('title', 'marketprice', 'total', 'goodssn', 'productsn', 'displayorder', 'dowpayment'))) {
			show_json(0, array('message' => '参数错误'));
		}

		$goods = Db::name('shop_goods')->field('id,hasoption,marketprice,dowpayment')->where('id',$id)->find();

		if (empty($goods)) {
			show_json(0, array('message' => '参数错误'));
		}

		if ($type == 'dowpayment') {
			if ($goods['marketprice'] < $value) {
				show_json(0, array('message' => '定金不能大于总价'));
			}
		} else {
			if ($type == 'marketprice') {
				if ($value < $goods['dowpayment']) {
					show_json(0, array('message' => '总价不能小于定金'));
				}
			}
		}
		Db::name('shop_goods')->where('id',$id)->setField($type,$value);

		if ($goods['hasoption'] == 0) {
			$sql = 'update ' . tablename('shop_goods') . ' set minprice = marketprice,maxprice = marketprice where id = ' . $goods['id'] . ' and hasoption=0;';
			Db::query($sql);
		}
		model('shop')->plog('goods.edit', '修改商品' . $type . '参数 ID: ' . $id);
		show_json(1);
	}

}