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
		$condition = ' o.deleted = 0 and o.status = 1 and (o.success = 1 or o.is_team = 0) ';
		$order_ok = Db::name('shop_groups_order')
			->alias('o')
			->join('shop_groups_goods g','g.id = o.goodsid','left')
			->join('member m','m.id=o.mid','left')
			->where($condition)
			->order('o.createtime','desc')
			->field('o.*,g.title,g.thumb,m.nickname,m.realname,m.mobile')
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

		$pdo_res = Db::name('shop_groups_order')->where('status','>',0)->where('paytime','between',[$createtime1,$createtime2])->field('id,price,freight,starttime,mid')->select();
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
		$pdo_res = Db::name('shop_groups_order')->where('paytime','>',0)->where('is_team',1)->where('success',$success)->field('id,price,freight,starttime')->select();
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
		$list = Db::name('shop_groups_goods_category')->order('displayorder','desc')->select();
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
				Db::name('shop_groups_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('groups.category.edit', '修改积分商城分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_groups_goods_category')->insertGetId($data);
				model('shop')->plog('groups.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/groups/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_groups_goods_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('groups/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_groups_goods_category')->where('id',$id)->field('id,name')->find();

		if (!empty($item)) {
			Db::name('shop_groups_goods_category')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('groups.category.delete', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_groups_goods_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		Db::name('shop_groups_goods_category')->where('id',$id)->delete();
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

		$items = Db::name('shop_groups_goods_category')->where('id','in',$id)->field('id,name')->select();

		foreach ($items as $item) {
			Db::name('shop_groups_goods_category')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('groups.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function goods()
	{
		$psize = 20;
		$condition = " 1 ";
		$type = input('type');
		switch( $type ) 
		{
			case "sale": $condition .= " and g.deleted = 0 and g.stock > 0 and g.status = 1 ";
			break;
			case "sold": $condition .= " and g.deleted = 0 and g.stock <= 0 and g.status = 1 ";
			break;
			case "store": $condition .= " and g.deleted = 0 and g.status = 0 ";
			break;
			case "recycle": $condition .= " and g.deleted = 1 ";
			break;
			default: $condition .= " and g.deleted = 0 and g.stock > 0 and g.status = 1 ";
		}
		if( !empty($_GET["keyword"]) ) 
		{
			$_GET["keyword"] = trim($_GET["keyword"]);
			$condition .= " AND title LIKE '%" . trim($_GET["keyword"]) . "%'";
		}
		if( $_GET["status"] != "" ) 
		{
			$condition .= " AND status = " . intval($_GET["status"]);
		}
		if( $_GET["category"] != "" ) 
		{
			$condition .= " AND category = " . intval($_GET["category"]);
		}

		$list = Db::name('shop_groups_goods')->alias('g')->join('shop_groups_goods_category c','g.category = c.id','left')->where($condition)->field('c.*,g.*')->order('g.displayorder DESC,g.id DESC')->paginate($psize);
		$pager = $list->render();
		$categorys = Db::name('shop_groups_goods_category')->where('enabled',1)->order('displayorder','desc')->field('id,name,thumb')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'categorys'=>$categorys,'type'=>$type,'status'=>$_GET['status'],'category'=>$_GET['category'],'keyword'=>$_GET['keyword']]);
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
		$item = Db::name('shop_groups_goods')->alias('g')->join('shop_groups_goods_category c','c.id = g.category','left')->where('g.id',$id)->field('g.*,c.name as catename')->find();
		$group_goods_id = $item["id"];
		if( !empty($item["thumb"]) ) 
		{
			$piclist = iunserializer($item["thumb_url"]);
		}
		$stores = array( );
		if( !empty($item["storeids"]) ) 
		{
			$stores = Db::name('shop_store')->where('id','in',$item["storeids"])->field('id,storename')->select();
		}
		$specs = array( );
		if( !empty($item["more_spec"]) ) 
		{
			$specs = Db::name('shop_groups_goods_option')->where('groups_goods_id',$item["id"])->select();
		}
		$ladder = array( );
		if( !empty($item["is_ladder"]) ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id',$item["id"])->select();
		}
		$dispatch_data = Db::name('shop_dispatch')->where('enabled',1)->order('displayorder','desc')->select();
		$category = Db::name('shop_groups_goods_category')->where('enabled',1)->order('displayorder','desc')->field('id,name,thumb')->select();
		if( Request::instance()->isPost() ) 
		{
			$data = array( "displayorder" => intval($_POST["displayorder"]), "gid" => intval($_POST["gid"]), "title" => trim($_POST["title"]), "category" => intval($_POST["category"]), "thumb" => "", "thumb_url" => "", "price" => floatval($_POST["price"]), "groupsprice" => floatval($_POST["groupsprice"]), "single" => intval($_POST["single"]), "singleprice" => floatval($_POST["singleprice"]), "goodsnum" => (intval($_POST["goodsnum"]) < 1 ? 1 : intval($_POST["goodsnum"])), "purchaselimit" => intval($_POST["purchaselimit"]), "units" => trim($_POST["units"]), "stock" => intval($_POST["stock"]), "showstock" => intval($_POST["showstock"]), "sales" => intval($_POST["sales"]), "teamnum" => intval($_POST["teamnum"]), "dispatchtype" => intval($_POST["dispatchtype"]), "freight" => floatval($_POST["freight"]), "status" => intval($_POST["status"]), "isindex" => intval($_POST["isindex"]), "groupnum" => intval($_POST["groupnum"]), "endtime" => intval($_POST["endtime"]), "description" => trim($_POST["description"]), "goodssn" => trim($_POST["goodssn"]), "productsn" => trim($_POST["productsn"]), "content" => model("common")->html_images($_POST["content"]), "goodsid" => intval($_POST["goodsid"]), "deduct" => floatval($_POST["deduct"]), "isdiscount" => intval($_POST["isdiscount"]), "discount" => intval($_POST["discount"]), "headstype" => intval($_POST["headstype"]), "headsmoney" => floatval($_POST["headsmoney"]), "headsdiscount" => intval($_POST["headsdiscount"]), "isverify" => intval($_POST["isverify"]), "verifytype" => intval($_POST["verifytype"]), "verifynum" => intval($_POST["verifynum"]), "storeids" => (is_array($_POST["storeids"]) ? implode(",", $_POST["storeids"]) : ""), "more_spec" => intval($_POST["more_spec"]), "is_ladder" => intval($_POST["is_ladder"]) );
			if( $data["is_ladder"] == 1 && $data["more_spec"] == 1 ) 
			{
				show_json(0, "多规格和阶梯图不能同时开启");
			}
			if( $data["groupsprice"] < $data["headsmoney"] ) 
			{
				$data["headsmoney"] = $data["groupsprice"];
			}
			if( !empty($data["verifytype"]) && $data["verifynum"] < 1 ) 
			{
				$data["verifynum"] = 1;
			}
			if( $data["headsmoney"] < 0 ) 
			{
				$data["headsmoney"] = 0;
			}
			if( $data["headsdiscount"] < 0 ) 
			{
				$data["headsdiscount"] = 0;
			}
			if( 100 < $data["headsdiscount"] ) 
			{
				$data["headsdiscount"] = 100;
			}
			if( $data["goodsnum"] < 0 && empty($data["is_ladder"]) ) 
			{
				show_json(0, "数量不能小于1！");
			}
			if( $data["groupnum"] < 2 && empty($data["is_ladder"]) ) 
			{
				show_json(0, "开团人数至少为2人！");
			}
			if( $data["endtime"] < 1 ) 
			{
				show_json(0, "组团限时不能小于1小时！");
			}
			if( $data["groupsprice"] <= 0 && empty($data["is_ladder"]) && empty($data["more_spec"]) ) 
			{
				show_json(0, "拼团价格不符合要求！");
			}
			if( $data["singleprice"] <= 0 && $data["single"] == 1 && empty($data["more_spec"]) ) 
			{
				show_json(0, "单购价格不符合要求！");
			}
			$data["title"] = (empty($data["goodstype"]) ? trim($_POST["goodsid_text"]) : trim($_POST["couponid_text"]));
			$spec = array( );
			if( $data["more_spec"] ) 
			{
				if( empty($_POST["spec"]) ) 
				{
					show_json(0, "请填写商品规格");
				}
				$spec = $_POST["spec"];
				$stock = 0;
				foreach( $spec as $v ) 
				{
					$stock += $v["stock"];
				}
				$data["stock"] = $stock;
			}
			$ladder = array( );
			if( $data["is_ladder"] ) 
			{
				$ladder_num = $_POST["ladder_num"];
				$ladder_price = $_POST["ladder_price"];
				if( empty($ladder_num) || empty($ladder_price) ) 
				{
					show_json(0, "请填写正确阶梯团数据");
				}
				foreach( (array) $ladder_num as $k => $v ) 
				{
					if( empty($v) || empty($ladder_price[$k]) ) 
					{
						show_json(0, "请填写正确阶梯团数据");
					}
					if( $v == 1 ) 
					{
						show_json(0, "阶梯团不能少于两人哦");
					}
					$ladder[$k]["ladder_num"] = $v;
					$ladder[$k]["ladder_price"] = $ladder_price[$k];
				}
			}
			if( empty($_POST["thumbs"]) ) 
			{
				show_json(0, "请上传图片");
			}
			if( is_array($_POST["thumbs"]) ) 
			{
				$thumbs = $_POST["thumbs"];
				$thumb_url = array( );
				foreach( $thumbs as $th ) 
				{
					$thumb_url[] = trim($th);
				}
				$data["thumb"] = $thumb_url[0];
				$data["thumb_url"] = serialize($thumb_url);
			}
			if( !empty($id) ) 
			{
				$goods_update = Db::name('shop_groups_goods')->where('id',$id)->update($data);
				if( !empty($ladder) ) 
				{
					$ladder_id = $_POST["ladder_id"];
					$ladder_id_str = trim(@implode(",", $ladder_id), ",");
					foreach( $ladder as $k => $v ) 
					{
						if( $ladder_id[$k] ) 
						{
							Db::name('shop_groups_ladder')->where('id',$ladder_id[$k])->update($v);
						}
						else 
						{
							$v["goods_id"] = $id;
							$ladderid = Db::name('shop_groups_ladder')->insertGetId($v);
							$ladder_id_str .= "," . $ladderid;
						}
					}
					$ladder_id_str = trim($ladder_id_str, ",");
					if( !empty($ladder_id_str) ) 
					{
						Db::query("DELETE FROM " . tablename("shop_groups_ladder") . " WHERE id NOT IN(" . $ladder_id_str . ") AND goods_id = " . $id);
					}
				}
				if( $data["more_spec"] != 1 ) 
				{
					model('groups')->del_spec($id);
				}
				if( !empty($spec) ) 
				{
					model('groups')->dispose_spec($spec, $id, intval($_POST["gid"]));
				}
				model('shop')->plog("groups.goods.edit", "编辑拼团商品 ID: " . $id . " 商品名称: " . $data["title"]);
			}
			else 
			{
				$data['createtime'] = time();
				$goods_insert = Db::name('shop_groups_goods')->insertGetId($data);
				if( !$goods_insert ) 
				{
					show_json(0, "商品添加失败！");
				}
				$id = $goods_insert;
				$gid = intval($data["gid"]);
				if( $gid ) 
				{
					Db::name('shop_goods')->where('id',$gid)->update(array( "groupstype" => 1 ));
					if( !empty($spec) ) 
					{
						model('groups')->dispose_spec($spec, $id, $gid);
					}
				}
				if( !empty($ladder) ) 
				{
					foreach( $ladder as $k => $v ) 
					{
						$v["goods_id"] = $id;
						Db::name('shop_groups_ladder')->insert($v);
					}
				}
				model('shop')->plog("groups.goods.add", "添加拼团商品 ID: " . $id . "  商品名称: " . $data["title"]);
			}
			show_json(1, array( "url" => url("admin/groups/goodsedit", array( "op" => "post", "id" => $id, "tab" => str_replace("#tab_", "", $_POST["tab"]) )) ));
		}
		$this->assign(['item'=>$item,'category'=>$category,'stores'=>$stores,'dispatch_data'=>$dispatch_data,'piclist'=>$piclist,'ladder'=>$ladder,'specs'=>$specs]);
		return $this->fetch('groups/goods/post');
	}

	public function goodsquery()
	{
		$kwd = trim($_GET["keyword"]);
		$psize = 8;
		$condition = " merchid = 0 and type = 1 and status = 1 and deleted = 0 ";
		if( !empty($kwd) ) 
		{
			$condition .= " AND `title` LIKE '%" . $kwd . "%'";
		}
		$ds = Db::name('shop_goods')->where($condition)->field('id as gid,title,subtitle,thumb,thumb_url,marketprice,content,productprice,subtitle,goodssn,productsn')->order('createtime','desc')->paginate($psize);
		foreach( $ds as $key => $d ) 
		{
			if( !empty($d["thumb_url"]) ) 
			{
				$d["thumb_url"] = iunserializer($d["thumb_url"]);
			}
			$d["content"] = model("common")->html_to_images($d["content"]);
			$d["content"] = str_replace("'", "\"", $d["content"]);
			$data = array();
    		$data = $d;
    		$ds->offsetSet($key,$data);
		}
		unset($d);
		if( $_GET["suggest"] ) 
		{
			exit( json_encode(array( "value" => $ds )) );
		}

		$pager = $ds->render();
		$this->assign(['ds'=>$ds]);
		return $this->fetch('groups/goods/query');
	}

	public function get_spec() 
	{
		$goods_id = intval(input('goods_id'));
		$group_goods_id = intval(input('group_goods_id'));
		$shop_groups_goods_id = intval(input('shop_groups_goods_id'));
		if( !$goods_id && !$group_goods_id ) 
		{
			show_json(0, "请先选择商品");
		}
		if( $group_goods_id && empty($shop_groups_goods_id) ) 
		{
			show_json(0, "没有关联商城多规格商品无法添加多规格");
		}
		if( $shop_groups_goods_id ) 
		{
			$goods_id = $shop_groups_goods_id;
		}
		$specArr = Db::name('shop_goods_option')->where('goodsid',$goods_id)->field('id,title,thumb,marketprice,stock,specs')->select();
		if( !empty($specArr) ) 
		{
			$stock = 0;
			foreach( $specArr as $k => $v ) 
			{
				$stock += $v["stock"];
			}
		} else {
			show_json(0, "此商品没有多规格");
		}
		show_json(1, array( "data" => $specArr, "stock" => $stock ));
	}

	public function goodsdelete1() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_groups_goods')->where('id in( ' . $id . ' )')->field('id,title,gid')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_groups_goods')->where('id',$item['id'])->delete();
			Db::name('shop_groups_goods')->where('shop_groups_goods_option',$item['id'])->delete();
			model('shop')->plog("groups.goods.edit", "从回收站彻底删除商品<br/>ID: " . $item["id"] . "<br/>商品名称: " . $item["title"]);
		}
		show_json(1, array( "url" => referer() ));
	}

	public function goodsrestore() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_groups_goods')->where('id in( ' . $id . ' )')->field('id,title,gid')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_groups_goods')->where('id',$item['id'])->update(array( "deleted" => 0, "status" => 0 ));
			if( intval($item["gid"]) ) 
			{
				Db::name('shop_groups_goods')->where('id',$item["gid"])->update(array( "groupstype" => 1 ));
			}
			model('shop')->plog("groups.goods.edit", "从回收站恢复商品<br/>ID: " . $item["id"] . "<br/>商品名称: " . $item["title"]);
		}
		show_json(1, array( "url" => referer() ));
	}

	public function goodsdelete() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_groups_goods')->where('id in( ' . $id . ' )')->field('id,title,gid')->select();
		foreach( $items as $item ) 
		{
			$delete_update = Db::name('shop_groups_goods')->where('id',$item['id'])->update(array( "deleted" => 1, "status" => 0 ));
			if( !$delete_update ) 
			{
				show_json(0, "删除商品失败！");
			}
			if( intval($item["gid"]) ) 
			{
				Db::name('shop_groups_goods')->where('id',$item["gid"])->update(array( "groupstype" => 0 ));
			}
			model('shop')->plog("groups.goods.delete", "删除拼团商品 ID: " . $item["id"] . "  <br/>商品名称: " . $item["title"] . " ");
		}
		show_json(1, array( "url" => referer() ));
	}

	public function goodsstatus() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$status = intval(input('status'));
		$items = Db::name('shop_groups_goods')->where('id in( ' . $id . ' )')->field('id,title,gid')->select();
		foreach( $items as $item ) 
		{
			$status_update = Db::name('shop_groups_goods')->where('id',$item['id'])->update(array( "status" => $status ));
			if( !$status_update ) 
			{
				show_json(0,"商品状态修改失败！");
			}
			model('shop')->plog("groups.goods.edit", "修改拼团商品 " . $item["id"] . " <br /> 状态: " . (($status == 0 ? "下架" : "上架")));
		}
		show_json(1, array( "url" => referer() ));
	}

	public function goodsproperty() 
	{
		$id = intval(input('id'));
		$type = trim(input('type'));
		$value = intval(input('value'));
		if( in_array($type, array( "status", "displayorder" )) ) 
		{
			$statusstr = "";
			if( $type == "status" ) 
			{
				$typestr = "上下架";
				$statusstr = ($value == 1 ? "上架" : "下架");
			}
			else 
			{
				if( $type == "displayorder" ) 
				{
					$typestr = "排序";
					$statusstr = "序号 " . $value;
				}
				else 
				{
					if( $type == "isindex" ) 
					{
						$typestr = "是否首页显示";
						$statusstr = ($value == 1 ? "是" : "否");
					}
				}
			}
			$property_update = Db::name('shop_groups_goods')->where('id',$id)->update(array( $type => $value ));
			if( !$property_update ) 
			{
				show_json(0,"" . $typestr . "修改失败");
			}
			model('shop')->plog("groups.goods.edit", "修改拼团商品" . $typestr . "状态   ID: " . $id . " " . $statusstr . " ");
		}
		show_json(1);
	}

	public function goodstotal()
	{
		$type = intval($_GET["type"]);
		$condition = " 1 ";
		if( $type == 1 ) 
		{
			$condition .= " and deleted = 0 and stock > 0 and status = 1 ";
		}
		else 
		{
			if( $type == 2 ) 
			{
				$condition .= " and deleted = 0 and stock = 0 and status = 1";
			}
			else 
			{
				if( $type == 3 ) 
				{
					$condition .= " and deleted = 0 and status = 0 ";
				}
				else 
				{
					if( $type == 4 ) 
					{
						$condition .= " and deleted = 1 ";
					}
				}
			}
		}
		$total = Db::name('shop_groups_goods')->where($condition)->count();
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
		$condition = ' paytime > 0 and heads = 1 and is_team = 1 and status > 0 ';

		if ($type == 'ing') {
			$condition .= ' and success = 0 ';
		} elseif ($type == 'success') {
			$condition .= ' and success = 1 ';
		} elseif ($type == 'error') {
			$condition .= ' and success = -1 ';
		} else {
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
		$orderby = 'createtime desc';
		$teams = Db::name('shop_groups_order')->where($condition)->order($orderby)->paginate($psize);

		foreach ($teams as $key => $value) {
			$good = Db::name('shop_groups_goods')->where('id',$value['goodsid'])->field('title')->find();
			$value['title'] = $good['title'];
			$value['num'] = Db::name('shop_groups_order')->where('status > 0 and deleted = 0 and teamid = ' . $value['teamid'])->count();
			$value['groups_team'] = $value['groupnum'] - $value['num'];
			$hours = $value['endtime'];
			$date = date('Y-m-d H:i:s', $value['starttime']);
			$value['starttime'] = date('Y-m-d H:i', $value['starttime']);
			$value['endtime'] = date('Y-m-d H:i', strtotime(' ' . $date . ' + ' . $hours . ' hour'));
			$data = array();
    		$data = $value;
    		$teams->offsetSet($key,$data);
		}	
		unset($value);	
		$pager = $teams->render();
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
		$this->assign(['teams'=>$teams,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime,'type'=>$type,'sort'=>$sort,'team'=>$team]);
		return $this->fetch('groups/team/index');
	}

	public function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC) 
	{
		if( is_array($multi_array) ) {
			foreach( $multi_array as $row_array ) {
				if( is_array($row_array) ) {
					$key_array[] = $row_array[$sort_key];
				} else {
					return false;
				}
			}
			if( empty($multi_array) ) {
				return false;
			}
			array_multisort($key_array, $sort, $multi_array);
			return $multi_array;
		} else {
			return false;
		}
	}

	public function teamdetail()
	{
		$teamid = input('teamid');
		$teaminfo = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->where('o.teamid =' . $teamid . ' and o.is_team = 1 and heads = 1')->field('o.*,g.id as gid,g.title,g.thumb,g.thumb_url')->find();
		$total = Db::name('shop_groups_order')->where('teamid =' . $teamid . ' and is_team = 1 and status > 0')->count();
		$orders = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->where('o.teamid =' . $teamid . ' and o.is_team = 1 and o.status != 0')->field('o.*,g.thumb,g.thumb_url')->select();
		foreach( $orders as $key => $value ) 
		{
			$member = model("member")->getMember($value["mid"]);
			$orders[$key]["avatar"] = $member["avatar"];
			$orders[$key]["nickname"] = $member["nickname"];
		}
		$member = model("member")->getMember($teaminfo["mid"]);
		$dispatch = Db::name('shop_dispatch')->where('id',$teaminfo["dispatchid"])->find();
		if( empty($teaminfo["addressid"]) ) 
		{
			$user = unserialize($teaminfo["carrier"]);
		}
		else 
		{
			$user = iunserializer($teaminfo["address"]);
			if( !is_array($user) ) 
			{
				$user = Db::name('shop_member_address')->where('id = ' . $teaminfo['addressid'])->find();
			}
			$address_info = $user["address"];
			$user["address"] = $user["province"] . " " . $user["city"] . " " . $user["area"] . " " . $user["address"];
			$teaminfo["addressdata"] = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "address" => $user["address"] );
		}
		$this->assign(['teaminfo'=>$teaminfo,'total'=>$total,'orders'=>$orders,'member'=>$member,'dispatch'=>$dispatch,'user'=>$user]);
		return $this->fetch('groups/team/detail');
	}

	public function teamgroup() 
	{
		$teamid = intval(input('id'));
		if( empty($teamid) ) 
		{
			$teamid = (is_array($_POST["ids"]) ? $_POST["ids"] : 0);
		}
		else 
		{
			$teamid = array( $teamid );
		}
		foreach( $teamid as $key => $value ) 
		{
			$order =  Db::name('shop_groups_order')->where('teamid = ' . $value . ' and heads = 1 and success = 0 ')->field('id,groupnum,goodsid,endtime')->find();
			$order_count =  Db::name('shop_groups_order')->where('teamid = ' . $value . ' and status = 1 and success = 0 ')->count();
			$num = $order["groupnum"] - $order_count;
			for( $i = 0; $i < $num; $i++ ) 
			{
				$orderno = model("common")->createNO("shop_groups_order", "orderno", "PT");
				$system_order_data = array( "groupnum" => $order["groupnum"], "mid" => 0, "paytime" => time(), "starttime" => time(), "finishtime" => time(), "pay_type" => "system", "orderno" => $orderno, "status" => 3, "goodsid" => $order["goodsid"], "teamid" => $value, "is_team" => 1, "endtime" => $order["endtime"], "sendtime" => time(), "createtime" => time(), "success" => 1 );
				$order_insert = Db::name('shop_groups_order')->insertGetId($system_order_data);
			}
			Db::name('shop_groups_order')->where(' status = 1 and teamid = ' . $value)->update(array( "success" => 1 ));
			Db::name('shop_groups_order')->where(' status = 0 and teamid = ' . $value)->update(array( "status" => -1 ));
			model('notice')->sendTeamMessage($order["id"]);
		}
		show_json(1);
	}

	public function cancel_group() 
	{
		$teamid = intval(input('id'));
		if( empty($teamid) ) 
		{
			show_json(0, "该团不存在！");
		}
		$orderNum = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and status >1 and success = 1 ')->count();
		if( !empty($orderNum) ) 
		{
			show_json(0, "该团商品已发货或已使用，不可取消！");
		}
		$headgroup = Db::name('shop_groups_order')->where(' heads = 1 and teamid = ' . $teamid)->find();
		if( $headgroup["success"] < 1 ) 
		{
			show_json(0, "该团不满足取消条件！");
		}
		Db::name('shop_groups_order')->where(' status = 1 and teamid = ' . $teamid)->update(array( "success" => -1 ));
		Db::name('shop_groups_order')->where(' status = 0 and teamid = ' . $teamid)->update(array( "status" => -1 ));
		show_json(1);
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
		$condition = " 1 ";
		if( intval($status) == 1 ) {
			$condition .= " and o.status = 1 and (o.success = 1 or o.is_team = 0) ";
		} else {
			if( intval($status) == 2 ) 
			{
				$condition .= " and o.status = 2 ";
			}
			else 
			{
				if( intval($status) == 3 ) 
				{
					$condition .= " and o.status = 0 ";
				}
				else 
				{
					if( intval($status) == 4 ) 
					{
						$condition .= " and o.status = 3 ";
					}
					else 
					{
						if( intval($status) == 5 ) 
						{
							$condition .= " and o.status = 5 ";
						}
					}
				}
			}
		}
		if( empty($starttime) || empty($endtime) ) 
		{
			$starttime = strtotime("-1 month");
			$endtime = time();
		}
		$searchtime = trim(input('searchtime'));
		if( !empty($searchtime) ) 
		{
			$condition .= " and o." . $searchtime . "time > " . strtotime($_GET["time"]["start"]) . " and o." . $searchtime . "time < " . strtotime($_GET["time"]["end"]) . " ";
			$starttime = strtotime($_GET["time"]["start"]);
			$endtime = strtotime($_GET["time"]["end"]);
		}
		$paytype = trim($_GET["paytype"]);
		if( !empty($_GET["paytype"]) ) 
		{			
			$condition .= " and o.pay_type = " . $paytype;
		}
		$searchfield = trim(strtolower($_GET["searchfield"]));
		$keyword = trim($_GET["keyword"]);
		if( !empty($searchfield) && !empty($keyword) ) 
		{		
			if( $searchfield == "orderno" ) 
			{
				$condition .= " AND locate('%" . $keyword . "%',o.orderno)>0 ";
			}
			else 
			{
				if( $searchfield == "member" ) 
				{
					$condition .= " AND (locate('%" . $keyword . "%',m.realname)>0 or locate('%" . $keyword . "%',m.mobile)>0 or locate('%" . $keyword . "%',m.nickname)>0)";
				}
				else 
				{
					if( $searchfield == "address" ) 
					{
						$condition .= " AND ( locate('%" . $keyword . "%',a.realname)>0 or locate('%" . $keyword . "%',a.mobile)>0) ";
					}
					else 
					{
						if( $searchfield == "expresssn" ) 
						{
							$condition .= " AND locate('%" . $keyword . "%',o.expresssn)>0";
						}
						else 
						{
							if( $searchfield == "goodstitle" ) 
							{
								$condition .= " and locate('%" . $keyword . "%',g.title)>0 ";
							}
							else 
							{
								if( $searchfield == "goodssn" ) 
								{
									$condition .= " and locate('%" . $keyword . "%',g.goodssn)>0 ";
								}
							}
						}
					}
				}
			}
		}
		$list = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('member m','m.id=o.mid','left')->join('shop_member_address a','a.id=o.addressid','left')->join('shop_groups_order_goods og','og.groups_order_id = o.id','left')->where($condition)->group('o.id')->field('o.id,o.orderno,o.status,o.expresssn,o.paytime,o.addressid,o.express,o.remark,o.is_team,o.pay_type,o.isverify,o.refundtime,o.price,o.creditmoney,o.refundstate,o.refundid,o.message,o.freight,o.discount,o.creditmoney,o.createtime,o.success,o.deleted,o.address,o.message,o.mid,g.thumb_url,og.option_name as optionname,g.title,g.category,g.goodsnum,g.thumb,g.groupsprice,g.singleprice,g.price as gprice,g.goodssn,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea,a.address as aaddress')->order('o.createtime DESC')->paginate($psize);
		foreach( $list as $key => $value ) 
		{
			if( !empty($value["address"]) ) 
			{
				$user = unserialize($value["address"]);
				$list[$key]["addressdata"] = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "province" => $user["province"], "city" => $user["city"], "area" => $user["area"], "street" => $user["street"], "address" => $user["address"] );
			}
			else 
			{
				$user = iunserializer($value["addressid"]);
				if( !is_array($user) ) 
				{
					$user = pdo_fetch("SELECT * FROM " . tablename("shop_member_address") . " WHERE id = :id and uniacid=:uniacid", array( ":id" => $value["addressid"], ":uniacid" => $_W["uniacid"] ));
				}
				$list[$key]["addressdata"] = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "province" => $user["province"], "city" => $user["city"], "area" => $user["area"], "street" => $user["street"], "address" => $user["address"] );
			}
		}
		$pager = $list->render();
		$paytype = array( "credit" => "余额支付", "wechat" => "微信支付", "other" => "其他支付" );
		$paystatus = array( "未付款", "已付款", "待收货", "已完成", -1 => "已取消", 4 => "待发货" );
		if( $_GET["export"] == 1 ) 
		{
			model('shop')->plog("groups.order.export", "导出订单");
			$columns = array( array( "title" => "订单编号", "field" => "orderno", "width" => 24 ), array( "title" => "粉丝昵称", "field" => "nickname", "width" => 12 ), array( "title" => "会员姓名", "field" => "mrealname", "width" => 12 ), array( "title" => "mid", "field" => "mid", "width" => 30 ), array( "title" => "会员手机手机号", "field" => "amobile", "width" => 15 ), array( "title" => "收货姓名(或自提人)", "field" => "arealname", "width" => 15 ), array( "title" => "联系电话", "field" => "amobile", "width" => 12 ), array( "title" => "收货地址", "field" => "aprovince", "width" => 12 ), array( "title" => "", "field" => "acity", "width" => 12 ), array( "title" => "", "field" => "aarea", "width" => 12 ), array( "title" => "", "field" => "street", "width" => 15 ), array( "title" => "", "field" => "aaddress", "width" => 20 ), array( "title" => "商品名称", "field" => "title", "width" => 30 ), array( "title" => "商品编码", "field" => "goodssn", "width" => 15 ), array( "title" => "商品规格", "field" => "optionname", "width" => 30 ), array( "title" => "团购价", "field" => "groupsprice", "width" => 12 ), array( "title" => "单购价", "field" => "singleprice", "width" => 12 ), array( "title" => "原价", "field" => "price", "width" => 12 ), array( "title" => "商品数量", "field" => "goods_total", "width" => 15 ), array( "title" => "商品小计", "field" => "goodsprice", "width" => 12 ), array( "title" => "积分抵扣", "field" => "credit", "width" => 12 ), array( "title" => "积分抵扣金额", "field" => "creditmoney", "width" => 12 ), array( "title" => "运费", "field" => "freight", "width" => 12 ), array( "title" => "应收款", "field" => "amount", "width" => 12 ), array( "title" => "支付方式", "field" => "pay_type", "width" => 12 ), array( "title" => "状态", "field" => "status", "width" => 12 ), array( "title" => "下单时间", "field" => "createtime", "width" => 24 ), array( "title" => "付款时间", "field" => "paytime", "width" => 24 ), array( "title" => "发货时间", "field" => "sendtime", "width" => 24 ), array( "title" => "完成时间", "field" => "finishtime", "width" => 24 ), array( "title" => "快递公司", "field" => "expresscom", "width" => 24 ), array( "title" => "快递单号", "field" => "expresssn", "width" => 24 ), array( "title" => "买家备注", "field" => "message", "width" => 36 ), array( "title" => "卖家备注", "field" => "remark", "width" => 36 ) );
			$exportlist = array( );
			foreach( $list as $key => $value ) 
			{
				$r["orderno"] = $value["orderno"];
				$r["nickname"] = str_replace("=", "", $value["nickname"]);
				$r["mrealname"] = $value["mrealname"];
				$r["mid"] = $value["mid"];
				$r["mmobile"] = $value["mmobile"];
				$r["arealname"] = $value["addressdata"]["realname"];
				$r["amobile"] = $value["addressdata"]["mobile"];
				$r["aprovince"] = $value["addressdata"]["province"];
				$r["acity"] = $value["addressdata"]["city"];
				$r["aarea"] = $value["addressdata"]["area"];
				$r["street"] = $value["addressdata"]["street"];
				$r["aaddress"] = $value["addressdata"]["address"];
				$r["pay_type"] = $paytype["" . $value["pay_type"] . ""];
				$r["freight"] = $value["freight"];
				$r["groupsprice"] = $value["groupsprice"];
				$r["singleprice"] = $value["singleprice"];
				$r["price"] = $value["price"];
				$r["credit"] = (!empty($value["credit"]) ? "-" . $value["credit"] : 0);
				$r["creditmoney"] = (!empty($value["creditmoney"]) ? "-" . $value["creditmoney"] : 0);
				$r["goodsprice"] = $value["groupsprice"] * 1;
				$r["status"] = ($value["status"] == 1 && $value["status"] == 1 ? $paystatus[4] : $paystatus["" . $value["status"] . ""]);
				$r["createtime"] = date("Y-m-d H:i:s", $value["createtime"]);
				$r["paytime"] = (!empty($value["paytime"]) ? date("Y-m-d H:i:s", $value["paytime"]) : "");
				$r["sendtime"] = (!empty($value["sendtime"]) ? date("Y-m-d H:i:s", $value["sendtime"]) : "");
				$r["finishtime"] = (!empty($value["finishtime"]) ? date("Y-m-d H:i:s", $value["finishtime"]) : "");
				$r["expresscom"] = $value["expresscom"];
				$r["expresssn"] = $value["expresssn"];
				$r["amount"] = $value["groupsprice"] * 1 - $value["creditmoney"] + $value["freight"];
				$r["message"] = $value["message"];
				$r["remark"] = $value["remark"];
				$r["title"] = $value["title"];
				$r["goodssn"] = $value["goodssn"];
				$r["optionname"] = $value["optionname"];
				$r["goods_total"] = 1;
				$exportlist[] = $r;
			}
			unset($r);
			model("excel")->export($exportlist, array( "title" => "订单数据-" . date("Y-m-d-H-i", time()), "columns" => $columns ));
		}
		$this->assign(['list'=>$list,'pager'=>$pager,'paytype'=>$paytype,'paystatus'=>$paystatus,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('groups/order/index');
	}

	public function orderdetail()
	{
		$status = input('status');
		$orderid = intval(input('orderid'));
		$order = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('shop_groups_goods_option op','op.specs = o.specs','left')->where('o.id = ' . $orderid)->field('o.*,g.title,g.category,op.title as optiontitle,g.groupsprice,g.singleprice,g.goodsnum,g.thumb,g.thumb_url,g.id as gid')->find();
		$order = set_medias($order, "thumb");
		$member = model("member")->getMember($order["mid"]);
		if( $order["verifytype"] == 0 ) 
		{
			$verify = Db::name('shop_groups_verify')->where('orderid = ' . $order['id'])->find();
			if( !empty($verify["verifier"]) ) 
			{
				$saler = model("member")->getMember($verify["verifier"]);
				$saler["salername"] = Db::name('shop_saler')->where('mid=' . $verify["verifier"])->value('salername');
			}
			if( !empty($order["storeid"]) ) 
			{
				$store = Db::name('shop_store')->where('id = ' . $verify["storeid"])->find();
			}
		}
		else 
		{
			if( $order["verifytype"] == 1 ) 
			{
				$verifyinfo = Db::name('shop_groups_verify')->alias('v')->join('shop_saler s','s.mid = v.verifier','left')->join('member sm','sm.id = s.mid','left')->join('shop_store store','store.id = v.storeid','left')->where('v.orderid = ' . $orderid)->field('v.*,sm.id as salerid,sm.nickname as salernickname,s.salername,store.storename')->select();
			}
		}
		if( !empty($order["address"]) ) 
		{
			$user = unserialize($order["address"]);
			$user["address"] = $user["province"] . "," . $user["city"] . "," . $user["area"] . "," . $user["street"] . "," . $user["address"];
			$order["addressdata"] = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "address" => $user["address"] );
		}
		else 
		{
			$user = iunserializer($order["addressid"]);
			if( !is_array($user) ) 
			{
				$user = Db::name('shop_member_address')->where('id = ' . $order["addressid"])->find();
			}
			$user["address"] = $user["province"] . "," . $user["city"] . "," . $user["area"] . "," . $user["street"] . "," . $user["address"];
			$order["addressdata"] = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "address" => $user["address"] );
		}
		$this->assign(['order'=>$order,'member'=>$member,'verify'=>$verify,'saler'=>$saler,'store'=>$store,'user'=>$user,'verifyinfo'=>$verifyinfo]);
		return $this->fetch('groups/order/detail');
	}

	protected function opData() 
	{
		$id = intval(input('id'));
		$item = Db::name('shop_groups_order')->where('id = ' . $id)->find();
		if( empty($item) ) 
		{
			if(Request::instance()->isAjax()) 
			{
				show_json(0, "未找到订单!");
			}
			$this->error("未找到订单!", "", "error");
		}
		return array( "id" => $id, "item" => $item );
	}

	public function orderdelete() 
	{
		$id = intval(input('id'));
		$item = Db::name('shop_groups_order')->where('id = ' . $id)->field('id,isverify,refundid')->find();
		if( empty($item) ) 
		{
			show_json(0, "抱歉，订单不存在或是已经被删除！");
		}
		Db::name('shop_groups_order')->where('id = ' . $id)->delete();
		if( 0 < $item["isverify"] ) 
		{
			Db::name('shop_groups_verify')->where('orderid = ' . $id)->delete();
		}
		if( 0 < $item["refundid"] ) 
		{
			Db::name('shop_groups_order_refund')->where('orderid = ' . $id)->delete();
		}
		model('shop')->plog("groups.order.delete", "删除拼团订单 ID: " . $id . " 标题: " . $item["name"] . " ");
		show_json(1);
	}

	public function orderremarksaler() 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( Request::instance()->isPost() ) 
		{
			Db::name('shop_groups_order')->where('id = ' . $item["id"])->update(array( "remark" => input('remark') ));
			model('shop')->plog("groups.order.remarksaler", "订单备注 ID: " . $item["id"] . " 订单号: " . $item["orderno"] . " 备注内容: " . input('remark'));
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('groups/order/remarksaler');
	}

	public function orderchangeaddress() 
	{
		$opdata = $this->opData();
		extract($opdata);
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		if( empty($item["addressid"]) ) 
		{
			$user = unserialize($item["carrier"]);
		}
		else 
		{
			$user = iunserializer($item["address"]);
			if( !is_array($user) ) 
			{
				$user = Db::name('shop_member_address')->where('id = ' . $item['addressid'])->find();
			}
			$address_info = $user["address"];
			$user_address = $user["address"];
			$user["address"] = $user["province"] . " " . $user["city"] . " " . $user["area"] . " " . $user["street"] . " " . $user["address"];
			$item["addressdata"] = $oldaddress = array( "realname" => $user["realname"], "mobile" => $user["mobile"], "address" => $user["address"] );
		}
		if( Request::instance()->isPost() ) 
		{
			$realname = $_POST["realname"];
			$mobile = $_POST["mobile"];
			$province = $_POST["province"];
			$city = $_POST["city"];
			$area = $_POST["area"];
			$street = $_POST["street"];
			$changead = intval($_POST["changead"]);
			$address = trim($_POST["address"]);
			if( !empty($id) ) 
			{
				if( empty($realname) ) 
				{
					$ret = "请填写收件人姓名！";
					show_json(0, $ret);
				}
				if( empty($mobile) ) 
				{
					$ret = "请填写收件人手机！";
					show_json(0, $ret);
				}
				if( $province == "请选择省份" ) 
				{
					$ret = "请选择省份！";
					show_json(0, $ret);
				}
				if( empty($address) ) 
				{
					$ret = "请填写详细地址！";
					show_json(0, $ret);
				}
				$item = Db::name('shop_groups_order')->where('id = ' . $id)->field('id, orderno, address')->find();
				$address_array = iunserializer($item["address"]);
				$address_array["realname"] = $realname;
				$address_array["mobile"] = $mobile;
				if( $changead ) 
				{
					$address_array["province"] = $province;
					$address_array["city"] = $city;
					$address_array["area"] = $area;
					$address_array["street"] = $street;
					$address_array["address"] = $address;
				}
				else 
				{
					$address_array["province"] = $user["province"];
					$address_array["city"] = $user["city"];
					$address_array["area"] = $user["area"];
					$address_array["street"] = $user["street"];
					$address_array["address"] = $user_address;
				}
				$address_array = iserializer($address_array);
				Db::name('shop_groups_order')->where('id = ' . $id)->update(array( "address" => $address_array ));
				model('shop')->plog("groups.order.changeaddress", "修改收货地址 ID: " . $item["id"] . " 订单号: " . $item["orderno"] . " <br>原地址: 收件人: " . $oldaddress["realname"] . " 手机号: " . $oldaddress["mobile"] . " 收件地址: " . $oldaddress["address"] . "<br>新地址: 收件人: " . $realname . " 手机号: " . $mobile . " 收件地址: " . $province . " " . $city . " " . $area . " " . $address);
				show_json(1);
			}
		}
		$this->assign(['id'=>$id,'item'=>$item,'area_set'=>$area_set,'new_area'=>$new_area,'address_street'=>$address_street,'user'=>$user,'address_info'=>$address_info]);
		echo $this->fetch('groups/order/changeaddress');
	}

	public function orderpay($a = array( ), $b = array( )) 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( 1 < $item["status"] ) 
		{
			show_json(0, "订单已付款，不需重复付款！");
		}

		Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array( "status" => 1, "pay_type" => "other", "paytime" => time(), "starttime" => time() ));
		model("notice")->sendTeamMessage($item["id"]);
		model('shop')->plog("groups.order.pay", "订单确认付款 ID: " . $item["id"] . " 订单号: " . $item["orderno"]);
		show_json(1);
	}

	public function ordersend() 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( empty($item["addressid"]) ) 
		{
			show_json(0, "无收货地址，无法发货！");
		}
		if( $item["pay_type"] == "" || $item["status"] == 0 ) 
		{
			show_json(0, "订单未付款，无法发货！");
		}
		if( Request::instance()->isPost() ) 
		{
			if( !empty($_POST["isexpress"]) && empty($_POST["expresssn"]) ) 
			{
				show_json(0, "请输入快递单号！");
			}
			if( !empty($item["transid"]) ) 
			{
			}
			Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array( "status" => 2, "express" => trim($_POST["express"]), "expresscom" => trim($_POST["expresscom"]), "expresssn" => trim($_POST["expresssn"]), "sendtime" => time() ));
			if( !empty($item["refundid"]) ) 
			{
				$refund = Db::name('shop_groups_order_refund')->where('id = ' . $item['refundid'])->find();
				if( !empty($refund) ) 
				{
					Db::name('shop_groups_order_refund')->where('id = ' . $item['refundid'])->update(array( "status" => -1, "endtime" => $time ));
					Db::name('shop_groups_order')->where('id = ' . $item['refundid'])->update(array( "refundstate" => 0 ));
				}
			}
			model('notice')->sendTeamMessage($item["id"]);
			model('shop')->plog("groups.order.send", "订单发货 ID: " . $item["id"] . " 订单号: " . $item["orderno"] . " <br/>快递公司: " . $_POST["expresscom"] . " 快递单号: " . $_POST["expresssn"]);
			show_json(1);
		}
		$address = iunserializer($item["address"]);
		if( !is_array($address) ) 
		{
			$address = Db::name('shop_member_address')->where('id = ' . $item['addressid'])->find();
		}
		$express_list = model("shop")->getExpressList();
		$this->assign(['id'=>$id,'item'=>$item,'address'=>$address,'express_list'=>$express_list]);
		echo $this->fetch('groups/order/send');
	}

	public function ordersendcancel() 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( $item["status"] != 2 ) 
		{
			show_json(0, "订单未发货，不需取消发货！");
		}
		if( Request::instance()->isPost() ) 
		{
			if( !empty($item["transid"]) ) 
			{
			}
			$remark = trim($_POST["remark"]);
			if( !empty($item["remarksend"]) ) 
			{
				$remark = $item["remarksend"] . "\r\n" . $remark;
			}

			Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array( "status" => 1, "sendtime" => 0, "remarksend" => $remark ));
			model('shop')->plog("groups.order.sendcancel", "订单取消发货 ID: " . $item["id"] . " 订单号: " . $item["orderno"] . " 原因: " . $remark);
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('groups/order/sendcancel');
	}

	public function orderchangeexpress() 
	{
		$opdata = $this->opData();
		extract($opdata);
		$edit_flag = 1;
		if( Request::instance()->isPost() ) 
		{
			$express = $_POST["express"];
			$expresscom = $_POST["expresscom"];
			$expresssn = trim($_POST["expresssn"]);
			if( empty($id) ) 
			{
				$ret = "参数错误！";
				show_json(0, $ret);
			}
			if( !empty($expresssn) ) 
			{
				$change_data = array( );
				$change_data["express"] = $express;
				$change_data["expresscom"] = $expresscom;
				$change_data["expresssn"] = $expresssn;
				pdo_update("shop_groups_order", $change_data, array( "id" => $id, "uniacid" => $_W["uniacid"] ));
				model('shop')->plog("groups.order.changeexpress", "修改快递状态 ID: " . $item["id"] . " 订单号: " . $item["orderno"] . " 快递公司: " . $expresscom . " 快递单号: " . $expresssn);
				show_json(1);
			}
			else 
			{
				show_json(0, "请填写快递单号！");
			}
		}
		$address = iunserializer($item["address"]);
		if( !is_array($address) ) 
		{
			$address = pdo_fetch("SELECT * FROM " . tablename("shop_member_address") . " WHERE id = :id and uniacid=:uniacid", array( ":id" => $item["addressid"], ":uniacid" => $_W["uniacid"] ));
		}
		$express_list = model("shop")->getExpressList();
		$this->assign(['id'=>$id,'item'=>$item,'address'=>$address,'edit_flag'=>$edit_flag,'express_list'=>$express_list]);
		echo $this->fetch('groups/order/send');
	}

	public function orderfinish() 
	{
		$opdata = $this->opData();
		extract($opdata);
		Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array( "status" => 3, "finishtime" => time() ));
		model("notice")->sendTeamMessage($item["id"]);
		model('shop')->plog("groups.order.finish", "订单完成 ID: " . $item["id"] . " 订单号: " . $item["orderno"]);
		show_json(1);
	}

	public function orderclose() 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( $item["status"] == -1 ) 
		{
			show_json(0, "订单已关闭，无需重复关闭！");
		}
		else 
		{
			if( 1 <= $item["status"] ) 
			{
				show_json(0, "订单已付款，不能关闭！");
			}
		}
		if( Request::instance()->isPost() ) 
		{
			if( !empty($item["transid"]) ) 
			{
			}
			$time = time();
			if( 0 < $item["refundstate"] && !empty($item["refundid"]) ) 
			{
				$change_refund = array( );
				$change_refund["refundstatus"] = -1;
				$change_refund["refundtime"] = $time;
				Db::name('shop_groups_order_refund')->where('id = ' . $item["refundid"])->update($change_refund);
			}
			Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array( "status" => -1, "refundstate" => 0, "canceltime" => $time, "remarkclose" => trim($_POST["remark"]) ));
			model('shop')->plog("groups.order.close", "订单关闭 ID: " . $item["id"] . " 订单号: " . $item["orderno"]);
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('groups/order/close');
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

	public function verify1()
	{
		$verifydata = $this->verifydata('normal');
		return $verifydata;
	}

	public function verify2()
	{
		$verifydata = $this->verifydata('over');
		return $verifydata;
	}

	public function verify0()
	{
		$verifydata = $this->verifydata('cancel');
		return $verifydata;
	}

	protected function verifydata($verify = 'normal')
	{
		$psize = 10;
		$condition = ' o.isverify = 1 ';
		if ($verify == 'normal') {
			$condition .= ' and o.status = 1 ';
		} else if ($verify == 'over') {
			$condition .= ' and o.status = 3 ';
		} else {
			if ($verify == 'cancel') {
				$condition .= ' and o.status <= 0 ';
			}
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		$searchtime = trim(input('searchtime'));
		$time = input('time/a');
		if (!empty($searchtime)) {
			$condition .= ' and o.' . $searchtime . 'time > ' . strtotime($time['start']) . ' and o.' . $searchtime . 'time < ' . strtotime($time['end']) . ' ';
			$starttime = strtotime($time['start']);
			$endtime = strtotime($time['end']);
		}

		$paytype = input('paytype');
		if (!empty($paytype)) {
			$paytype = trim($paytype);
			$condition .= ' and o.pay_type = ' . $paytype;
		}

		$searchfield = input('searchfield');
		$keyword = input('keyword');

		$sql = Db::name('shop_groups_order')->alias('o')->join('shop_groups_verify v','v.orderid = o.id','left')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('member m','m.id=o.mid','left');
		if (!empty($searchfield) && !empty($keyword)) {
			$searchfield = trim(strtolower($searchfield));
			$keyword = trim($keyword);
			$sqlcondition = '';
			$keycondition = '';
			if ($searchfield == 'orderno') {
				$condition .= ' AND locate("%' . $keyword . '%",o.orderno)>0 ';
			} else if ($searchfield == 'member') {
				$condition .= ' AND (locate("%' . $keyword . '%",m.realname)>0 or locate("%' . $keyword . '%",m.mobile)>0 or locate("%' . $keyword . '%",m.nickname)>0)';
			} else if ($searchfield == 'goodstitle') {
				$condition .= ' and locate("%' . $keyword . '%",g.title)>0 ';
			} else if ($searchfield == 'goodssn') {
				$condition .= ' and locate("%' . $keyword . '%",g.goodssn)>0 ';
			} else if ($searchfield == 'saler') {
				$keycondition = ' ,sm.id as salerid,sm.nickname as salernickname,s.salername ';
				$condition .= ' AND (locate("%' . $keyword . '%",sm.realname)>0 or locate("%' . $keyword . '%",sm.mobile)>0 or locate("%' . $keyword . '%",sm.nickname)>0 or locate("%' . $keyword . '%",s.salername)>0 )';
				$sql = $sql->join('shop_saler s','s.mid = v.verifier','left')->join('member sm','sm.id = s.mid','left');
			} else {
				if ($searchfield == 'store') {
					$condition .= ' AND (locate("%' . $keyword . '%",store.storename)>0)';
					$sqlcondition = ' left join ' . tablename('ewei_shop_store') . ' store on store.id = v.storeid and store.uniacid=o.uniacid ';
					$sql = $sql->join('shop_store store','store.id = v.storeid','left');
				}
			}
		}

		if (empty($_GET['export'])) {
			$page = 'LIMIT ' . ($pindex - 1) * $psize . ',' . $psize;
		}

		$list = $sql->where($condition)->group('o.id')->order('o.createtime DESC')->paginate($psize);
		$pager = $list->render();
		foreach ($list as $key => $value) {

		}
		$paytype = array('credit' => '余额支付', 'wechat' => '微信支付', 'other' => '其他支付');
		$paystatus = array(0 => '未付款', 1 => '已付款', 2 => '待收货', 3 => '已完成', -1 => '已取消', 4 => '待发货');

		if ($_GET['export'] == 1) {
			model('shop')->plog('groups.order.export', '导出订单');
			$columns = array(
				array('title' => '订单编号', 'field' => 'orderno', 'width' => 24),
				array('title' => '粉丝昵称', 'field' => 'nickname', 'width' => 12),
				array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
				array('title' => 'openid', 'field' => 'openid', 'width' => 30),
				array('title' => '会员手机手机号', 'field' => 'mmobile', 'width' => 15),
				array('title' => '收货姓名(或自提人)', 'field' => 'arealname', 'width' => 15),
				array('title' => '联系电话', 'field' => 'amobile', 'width' => 12),
				array('title' => '商品名称', 'field' => 'title', 'width' => 30),
				array('title' => '商品编码', 'field' => 'goodssn', 'width' => 15),
				array('title' => '团购价', 'field' => 'groupsprice', 'width' => 12),
				array('title' => '单购价', 'field' => 'singleprice', 'width' => 12),
				array('title' => '原价', 'field' => 'price', 'width' => 12),
				array('title' => '商品数量', 'field' => 'goods_total', 'width' => 15),
				array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
				array('title' => '积分抵扣', 'field' => 'credit', 'width' => 12),
				array('title' => '积分抵扣金额', 'field' => 'creditmoney', 'width' => 12),
				array('title' => '运费', 'field' => 'freight', 'width' => 12),
				array('title' => '应收款', 'field' => 'amount', 'width' => 12),
				array('title' => '支付方式', 'field' => 'pay_type', 'width' => 12),
				array('title' => '状态', 'field' => 'status', 'width' => 12),
				array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
				array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
				array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
				array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
				array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36),
				array('title' => '买家备注', 'field' => 'message', 'width' => 36),
				array('title' => '卖家备注', 'field' => 'remark', 'width' => 36)
				);
			$exportlist = array();
			foreach ($list as $key => $value) {
				$r['salerinfo'] = '';
				$r['storeinfo'] = '';
				$verify = Db::name('shop_groups_verify')->alias('v')->join('shop_saler s','s.mid = v.verifier','left')->join('shop_saler s','s.mid = v.verifier','left')->join('member sm','sm.id = s.mid','left')->join('shop_store store','store.id = v.storeid','left')->where('v.orderid = ' . $value['id'])->field('sm.id as salerid,sm.nickname as salernickname,s.salername,store.storename')->select();
				$vcount = count($verify) - 1;

				foreach ($verify as $k => $val) {
					$r['salerinfo'] .= '[' . $val['salerid'] . ']' . $val['salername'] . '(' . $val['salernickname'] . ')';
					$r['storeinfo'] .= $val['storename'];
					if ($k != $vcount) {
						$r['salerinfo'] .= "\r\n";
						$r['storeinfo'] .= "\r\n";
					} else {
						$r['salerinfo'] .= '';
						$r['storeinfo'] .= '';
					}
				}

				$r['orderno'] = $value['orderno'];
				$r['nickname'] = str_replace('=', '', $value['nickname']);
				$r['mrealname'] = $value['mrealname'];
				$r['openid'] = $value['openid'];
				$r['mmobile'] = $value['mmobile'];
				$r['arealname'] = $value['realname'];
				$r['amobile'] = $value['mobile'];
				$r['pay_type'] = $paytype['' . $value['pay_type'] . ''];
				$r['freight'] = $value['freight'];
				$r['groupsprice'] = $value['groupsprice'];
				$r['singleprice'] = $value['singleprice'];

				$r['price'] = $value['price'];
				$r['credit'] = !empty($value['credit']) ? '-' . $value['credit'] : 0;
				$r['creditmoney'] = !empty($value['creditmoney']) ? '-' . $value['creditmoney'] : 0;
				$r['goodsprice'] = $value['groupsprice'] * 1;
				$r['status'] = $value['status'] == 1 && $value['status'] == 1 ? $paystatus[4] : $paystatus['' . $value['status'] . ''];
				$r['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
				$r['paytime'] = !empty($value['paytime']) ? date('Y-m-d H:i:s', $value['paytime']) : '';
				$r['finishtime'] = !empty($value['finishtime']) ? date('Y-m-d H:i:s', $value['finishtime']) : '';
				$r['expresscom'] = $value['expresscom'];
				$r['expresssn'] = $value['expresssn'];
				$r['amount'] = $value['groupsprice'] * 1 - $value['creditmoney'] + $value['freight'];
				$r['message'] = $value['message'];
				$r['remark'] = $value['remark'];
				$r['title'] = $value['title'];
				$r['goodssn'] = $value['goodssn'];
				$r['goods_total'] = 1;
				$exportlist[] = $r;
			}
			unset($r);
			model('excel')->export($exportlist, array('title' => '核销订单-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
		}
		$this->assign(['list'=>$list,'pager'=>$pager,'verify'=>$verify,'starttime'=>$starttime,'endtime'=>$endtime,'searchtime'=>$searchtime,'paytype'=>$paytype,'searchfield'=>$searchfield,'keyword'=>$keyword,'paytype'=>$paytype,'paystatus'=>$paystatus]);
		return $this->fetch('groups/verify/index');
	}

	public function verifyfetch()
	{
		$opdata = $this->opData();
		extract($opdata);
		if ($item['status'] != 1) {
			show_json(0,'订单未付款，无法确认取货！');
		}
		$time = time();
		$d = array('status' => 3, 'sendtime' => $time, 'finishtime' => $time);

		if ($item['isverify'] == 1) {
			$d['verified'] = 1;
			$d['verifytime'] = $time;
			$d['verifyopenid'] = '';
		}

		Db::name('shop_groups_order')->where('id = ' . $item['id'])->update($d);

		if (!empty($item['refundid'])) {
			$refund = Db::name('shop_groups_order_refund')->where('id = ' . $item['refundid'])->find();
			if (!empty($refund)) {
				Db::name('shop_groups_order_refund')->where('id = ' . $item['refundid'])->update(array('status' => -1));
				Db::name('shop_groups_order')->where('id = ' . $item['id'])->update(array('refundstate' => 0));
			}
		}
		model('shop')->plog('groups.verify.fetch', '订单确认取货 ID: ' . $item['id'] . ' 订单号: ' . $item['orderno']);
		show_json(1);
	}

	public function detail()
	{
		$status = input('status');

		$orderid = intval(input('orderid'));
		$order = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->where('o.id = :orderid')->field('o.*,g.title,g.category,g.groupsprice,g.singleprice,g.thumb,g.thumb_url,g.id as gid')->find();

		$order = set_medias($order, 'thumb');

		$member = model('member')->getMember($order['mid']);

		$verifyinfo = Db::name('shop_groups_verify')->alias('v')->join('shop_saler s','s.mid = v.verifier','left')->join('member sm','sm.id = s.mid','left')->join('shop_store store','store.id = v.storeid','left')->where('v.orderid = ' . $orderid)->field('v.*,sm.id as salerid,sm.nickname as salernickname,s.salername,store.storename')->select();
		if ($order['verifytype'] == 0) {
			$verify = Db::name('shop_groups_verify')->where('orderid = ' . $order['id'])->find();
			if (!empty($verify['verifier'])) {
				$saler = model('member')->getMember($verify['verifier']);
				$saler['salername'] = Db::name('shop_saler')->where('mid = ' . $verify['verifier'])->value('salername');
			}
			if (!empty($verify['storeid'])) {
				$store = Db::name('shop_store')->where('id = ' . $verify['storeid'])->find();
			}
		}
		$this->assign(['order'=>$order,'member'=>$member,'verifyinfo'=>$verifyinfo,'verify'=>$verify,'saler'=>$saler,'store'=>$store]);
		return $this->fetch('groups/verify/detail');
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
			$condition .= ' and ( o.orderno like "%' . $keyword . '%" or g.title like "%' . $keyword . '%")';
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

		$list = Db::name('shop_groups_order_comment')->alias('c')->join('shop_groups_goods g','c.goodsid = g.id','left')->join('shop_groups_order o','c.orderid = o.id','left')->where($condition)->field('c.*, o.orderno,g.title,g.thumb')->order('c.createtime','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime,'fade'=>$fade,'replystatus'=>$replystatus,'keyword'=>$keyword]);
    	return $this->fetch('groups/comment/index');
    }

    public function commentdelete()
	{
		$id = intval($_POST['id']);

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_groups_order_comment')->where('id','in',$id)->field('id')->select();

		foreach ($items as $item) {
			Db::name('shop_groups_order_comment')->where('id',$item['id'])->setField('deleted',1);
			$goods = Db::name('shop_groups_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
			model('shop')->plog('groups.comment.delete', '删除评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
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
		$item = Db::name('shop_groups_order_comment')->where('id',$id)->find();
		$goodsid = input('goodsid');

		if (Request::instance()->isPost()) {
			if (empty($goodsid)) {
				show_json(0, array('message' => '请选择要评价的商品'));
			}

			$goods = set_medias(Db::name('shop_groups_goods')->where('id',$goodsid)->field('id,thumb,title')->find(), 'thumb');

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
				Db::name('shop_groups_order_comment')->where('id',$id)->update($data);
				model('shop')->plog('groups.comment.edit', '编辑商品虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}
			else {
				$id = Db::name('shop_groups_order_comment')->insertGetId($data);
				model('shop')->plog('groups.comment.add', '添加虚拟评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
			}

			show_json(1, array('url' => url('admin/groups/comment')));
		}

		if (empty($goodsid)) {
			$goodsid = intval($item['goodsid']);
		}

		$goods = Db::name('shop_groups_goods')->where('id',$goodsid)->field('id,thumb,title')->find();
		$this->assign(['goods'=>$goods,'item'=>$item]);
		return $this->fetch('groups/comment/virtual');
	}

	public function commentpost()
	{
		$id = input('id');
		$type = input('type');
		$item = Db::name('shop_groups_order_comment')->where('id',$id)->find();
		$goods = Db::name('shop_groups_goods')->where('id',$item['goodsid'])->field('id,thumb,title')->find();
		$order = Db::name('shop_groups_order')->where('id',$item['orderid'])->field('id,orderno')->find();

		if (Request::instance()->isPost()) {
			if ($type == 0) {
				$data = array('reply_content' => $_POST['reply_content'], 'reply_images' => is_array($_POST['reply_images']) ? iserializer(model('common')->array_images($_POST['reply_images'])) : iserializer(array()), 'append_reply_content' => $_POST['append_reply_content'], 'append_reply_images' => is_array($_POST['append_reply_images']) ? iserializer($_POST['append_reply_images']) : iserializer(array()));
				Db::name('shop_groups_order_comment')->where('id',$id)->update($data);
				model('shop')->plog('groups.comment.post', '回复商品评价 ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title']);
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
				Db::name('shop_groups_order_comment')->where('id',$id)->update($change_data);
				$log_msg = '商品首次评价' . $checked_array[$checked];

				if (!empty($item['append_content'])) {
					$log_msg .= ' 追加评价' . $checked_array[$checked];
				}

				$log_msg .= ' ID: ' . $id . ' 商品ID: ' . $goods['id'] . ' 商品标题: ' . $goods['title'];
				model('shop')->plog('groups.comment.post', $log_msg);
			}

			show_json(1, array('url' => url('admin/groups/comment')));
		}
		$append_images = iunserializer($item['append_images']);
		$images = iunserializer($item['images']);
		$this->assign(['goods'=>$goods,'item'=>$item,'type'=>$type,'order'=>$order,'append_images'=>$append_images,'images'=>$images]);
		return $this->fetch('groups/comment/post');
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
			$goods = Db::name('shop_groups_goods')->where('id','in',$data['goodsid'])->field('id,title,thumb')->select();
		}
		$this->assign(['data'=>$data,'goods'=>$goods,'sys_data'=>$sys_data]);
		return $this->fetch('groups/set/index');
	}

}