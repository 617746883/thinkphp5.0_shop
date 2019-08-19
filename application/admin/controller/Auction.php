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
		$condition = ' o.deleted = 0 and o.status = 1 ';
		$order_ok = Db::name('shop_auction_order')
			->alias('o')
			->join('shop_auction_goods g','g.id = o.goodsid','left')
			->join('member m','m.id=o.mid','left')
			->where($condition)
			->order('o.createtime','desc')
			->field('o.*,g.title,g.thumb,m.nickname,m.realname,m.mobile')
			->limit(0,10)
			->select();
		$this->assign(['order_ok'=>$order_ok]);
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
			case "sale": $condition .= " and g.status = 1 and g.dealmid = 0 and g.starttime < " . time() . " and g.endtime > " . time();
			break;
			case "wait": $condition .= " and g.status = 1 and g.dealmid = 0 and g.starttime > " . time();
			break;
			case "finish": $condition .= " and g.status = 1 and g.dealmid <> 0 ";
			break;
			case "auctions": $condition .= " and g.status = 1 and g.dealmid = 0 and g.endtime < " . time();
			break;
			case "store": $condition .= " and g.status != 1 ";
			break;
			default: $condition .= " and g.status = 1 and g.dealmid = 0 and g.starttime < " . time() . " and g.endtime > " . time();
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
		if( !empty($item["thumb"]) ) {
			$piclist = iunserializer($item["thumb_url"]);
		}
		if (Request::instance()->isPost()) {
			$data = $_POST['goods'];
			
			if (empty($data['title'])) {
				show_json(0, '请填写商品标题');
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
			if (empty($_POST["thumbs"])) {
				show_json(0, "请上传图片");
			}
			if( is_array($_POST["thumbs"]) ) {
				$thumbs = $_POST["thumbs"];
				$thumb_url = array( );
				foreach( $thumbs as $th ) {
					$thumb_url[] = trim($th);
				}
				$data["thumb"] = $thumb_url[0];
				$data["thumb_url"] = iserializer($thumb_url);
			}
			$data['content'] = htmlspecialchars_decode($data['content']);
			$data['starttime'] = strtotime($data['starttime']);
			$data['endtime'] =strtotime($data['endtime']);
			if (time()<$data['starttime']) {
				$data['stprice'] = $data['shprice'];
			}
			if (!empty($id)) {
				if($item['dealmid'] != 0) {
					show_json(0, '拍品已成功拍卖，不可编辑!');
				}
				$goods_update = Db::name('shop_auction_goods')->where('id',$id)->update($data);

				if (!$goods_update) {
					show_json(0, '商品编辑失败！');
				}
				model('shop')->plog('auction.goods.edit', '编辑拍卖商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			} else {
				$data['stprice'] = $data['shprice'];
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
		$this->assign(['item'=>$item,'category'=>$category,'piclist'=>$piclist]);
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
			$condition .= " and status = 1 and dealmid = 0 and starttime < " . time() . " and endtime > " . time();
		}
		else 
		{
			if( $type == 2 ) 
			{
				$condition .= " and status = 1 and dealmid = 0 and starttime > " . time();
			}
			else 
			{
				if( $type == 3 ) 
				{
					$condition .= " and status = 1 and dealmid <> 0 ";
				}
				else 
				{
					if( $type == 4 ) 
					{
						$condition .= " and status = 1 and dealmid = 0 and endtime < " . time();
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

	protected function auctionall($type = '')
	{
		$psize = 15;
		$list = Db::name('shop_auction_record')->where(' status > 0 ')->order('createtime desc')->paginate($psize);
		$number=0;
		foreach($list as $key => $value) {
			$p_record[$number] = Db::name('shop_auction_goods')->where('id = ' . $value['goodsid'])->find();
			if ($p_record[$number]['endtime'] < time()) {
				$p_record[$number]['state']=0;
				$redata = Db::name('shop_auction_record')->where('goodsid = ' . $value['goodsid'])->order('createtime desc')->find();
				if (empty($p_record[$number]['dealmid'])) {
		  			$data['dealmid']=$redata['mid'];
					Db::name('shop_auction_goods')->where('id = ' . $p_record[$number]['id'])->update($data);
				}
				$redatamember = model('member')->getMember($redata['mid']);
		  		$goods['dealmember'] = $redatamember['nickname'];
			} else {
				$p_record[$number]['state']=1;
			}
			$number++;
		}
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager]);
		return $this->fetch('auction/auction/index');
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
			$condition .= " and o.status = 1 and o.paystatus > 0 ";
		} else {
			if( intval($status) == 2 ) 
			{
				$condition .= " and o.status = 2 ";
			} else {
				if( intval($status) == 3 ) {
					$condition .= " and o.status = 0 ";
				} else {
					if( intval($status) == 4 ) {
						$condition .= " and o.status = 3 ";
					} else {
						if( intval($status) == 5 ) {
							$condition .= " and o.status = -1 ";
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
			$condition .= " and o.paytype = " . $paytype;
		}
		$searchfield = trim(strtolower($_GET["searchfield"]));
		$keyword = trim($_GET["keyword"]);
		if( !empty($searchfield) && !empty($keyword) ) 
		{		
			if( $searchfield == "ordersn" ) 
			{
				$condition .= " AND locate('%" . $keyword . "%',o.ordersn)>0 ";
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
		$list = Db::name('shop_auction_order')->alias('o')->join('shop_auction_goods g','g.id = o.goodsid','left')->join('member m','m.id=o.mid','left')->join('shop_member_address a','a.id=o.addressid','left')->where($condition)->group('o.id')->field('o.*,g.thumb_url,g.title,g.category,g.thumb,g.shprice,g.addprice,g.stprice as gprice,m.nickname,m.id as mid,m.realname as mrealname,m.mobile as mmobile,a.realname as arealname,a.mobile as amobile,a.province as aprovince ,a.city as acity , a.area as aarea,a.address as aaddress')->order('o.createtime DESC')->paginate($psize);
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
		$paytype = array( "3" => "余额支付", "1" => "微信支付", "2" => "其他支付" );
		$paystatus = array( "未付款", "已付款", "待收货", "已完成", -1 => "已取消", 4 => "待发货" );
		if( $_GET["export"] == 1 ) 
		{
			model('shop')->plog("auction.order.export", "导出订单");
			$columns = array( array( "title" => "订单编号", "field" => "ordersn", "width" => 24 ), array( "title" => "粉丝昵称", "field" => "nickname", "width" => 12 ), array( "title" => "会员姓名", "field" => "mrealname", "width" => 12 ), array( "title" => "mid", "field" => "mid", "width" => 30 ), array( "title" => "会员手机手机号", "field" => "amobile", "width" => 15 ), array( "title" => "收货姓名(或自提人)", "field" => "arealname", "width" => 15 ), array( "title" => "联系电话", "field" => "amobile", "width" => 12 ), array( "title" => "收货地址", "field" => "aprovince", "width" => 12 ), array( "title" => "", "field" => "acity", "width" => 12 ), array( "title" => "", "field" => "aarea", "width" => 12 ), array( "title" => "", "field" => "street", "width" => 15 ), array( "title" => "", "field" => "aaddress", "width" => 20 ), array( "title" => "商品名称", "field" => "title", "width" => 30 ), array( "title" => "商品编码", "field" => "goodssn", "width" => 15 ), array( "title" => "商品规格", "field" => "optionname", "width" => 30 ), array( "title" => "团购价", "field" => "stprice", "width" => 12 ), array( "title" => "原价", "field" => "price", "width" => 12 ), array( "title" => "商品数量", "field" => "goods_total", "width" => 15 ), array( "title" => "商品小计", "field" => "goodsprice", "width" => 12 ), array( "title" => "积分抵扣", "field" => "credit", "width" => 12 ), array( "title" => "积分抵扣金额", "field" => "creditmoney", "width" => 12 ), array( "title" => "运费", "field" => "freight", "width" => 12 ), array( "title" => "应收款", "field" => "amount", "width" => 12 ), array( "title" => "支付方式", "field" => "paytype", "width" => 12 ), array( "title" => "状态", "field" => "status", "width" => 12 ), array( "title" => "下单时间", "field" => "createtime", "width" => 24 ), array( "title" => "付款时间", "field" => "paytime", "width" => 24 ), array( "title" => "发货时间", "field" => "sendtime", "width" => 24 ), array( "title" => "完成时间", "field" => "finishtime", "width" => 24 ), array( "title" => "快递公司", "field" => "expresscom", "width" => 24 ), array( "title" => "快递单号", "field" => "expresssn", "width" => 24 ), array( "title" => "买家备注", "field" => "message", "width" => 36 ), array( "title" => "卖家备注", "field" => "remark", "width" => 36 ) );
			$exportlist = array( );
			foreach( $list as $key => $value ) 
			{
				$r["ordersn"] = $value["ordersn"];
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
				$r["paytype"] = $paytype["" . $value["paytype"] . ""];
				$r["freight"] = $value["freight"];
				$r["stprice"] = $value["stprice"];
				$r["price"] = $value["price"];
				$r["credit"] = (!empty($value["credit"]) ? "-" . $value["credit"] : 0);
				$r["creditmoney"] = (!empty($value["creditmoney"]) ? "-" . $value["creditmoney"] : 0);
				$r["goodsprice"] = $value["stprice"] * 1;
				$r["status"] = ($value["status"] == 1 && $value["status"] == 1 ? $paystatus[4] : $paystatus["" . $value["status"] . ""]);
				$r["createtime"] = date("Y-m-d H:i:s", $value["createtime"]);
				$r["paytime"] = (!empty($value["paytime"]) ? date("Y-m-d H:i:s", $value["paytime"]) : "");
				$r["sendtime"] = (!empty($value["sendtime"]) ? date("Y-m-d H:i:s", $value["sendtime"]) : "");
				$r["finishtime"] = (!empty($value["finishtime"]) ? date("Y-m-d H:i:s", $value["finishtime"]) : "");
				$r["expresscom"] = $value["expresscom"];
				$r["expresssn"] = $value["expresssn"];
				$r["amount"] = $value["stprice"] * 1 - $value["creditmoney"] + $value["freight"];
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
		return $this->fetch('auction/order/index');
	}

	public function orderdetail()
	{
		$status = input('status');
		$orderid = intval(input('orderid'));
		$order = Db::name('shop_auction_order')->alias('o')->join('shop_auction_goods g','g.id = o.goodsid','left')->where('o.id = ' . $orderid)->field('o.*,g.title,g.category,g.stprice,g.thumb,g.thumb_url,g.id as gid')->find();
		$order = set_medias($order, "thumb");
		$member = model("member")->getMember($order["mid"]);
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
		$this->assign(['order'=>$order,'member'=>$member,'user'=>$user]);
		return $this->fetch('auction/order/detail');
	}

	protected function opData() 
	{
		$id = intval(input('id'));
		$item = Db::name('shop_auction_order')->where('id = ' . $id)->find();
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
		$item = Db::name('shop_auction_order')->where('id = ' . $id)->field('id')->find();
		if( empty($item) ) 
		{
			show_json(0, "抱歉，订单不存在或是已经被删除！");
		}
		Db::name('shop_auction_order')->where('id = ' . $id)->delete();
		model('shop')->plog("auction.order.delete", "删除拼团订单 ID: " . $id . " 标题: " . $item["name"] . " ");
		show_json(1);
	}

	public function orderremarksaler() 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( Request::instance()->isPost() ) 
		{
			Db::name('shop_auction_order')->where('id = ' . $item["id"])->update(array( "remark" => input('remark') ));
			model('shop')->plog("auction.order.remarksaler", "订单备注 ID: " . $item["id"] . " 订单号: " . $item["ordersn"] . " 备注内容: " . input('remark'));
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('auction/order/remarksaler');
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
				$item = Db::name('shop_auction_order')->where('id = ' . $id)->field('id, ordersn, address')->find();
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
				Db::name('shop_auction_order')->where('id = ' . $id)->update(array( "address" => $address_array ));
				model('shop')->plog("auction.order.changeaddress", "修改收货地址 ID: " . $item["id"] . " 订单号: " . $item["ordersn"] . " <br>原地址: 收件人: " . $oldaddress["realname"] . " 手机号: " . $oldaddress["mobile"] . " 收件地址: " . $oldaddress["address"] . "<br>新地址: 收件人: " . $realname . " 手机号: " . $mobile . " 收件地址: " . $province . " " . $city . " " . $area . " " . $address);
				show_json(1);
			}
		}
		$this->assign(['id'=>$id,'item'=>$item,'area_set'=>$area_set,'new_area'=>$new_area,'address_street'=>$address_street,'user'=>$user,'address_info'=>$address_info]);
		echo $this->fetch('auction/order/changeaddress');
	}

	public function orderpay($a = array( ), $b = array( )) 
	{
		$opdata = $this->opData();
		extract($opdata);
		if( 1 < $item["status"] ) 
		{
			show_json(0, "订单已付款，不需重复付款！");
		}

		Db::name('shop_auction_order')->where('id = ' . $item['id'])->update(array( "status" => 1, "paytype" => 11, "paytime" => time() ));
		model("notice")->sendTeamMessage($item["id"]);
		model('shop')->plog("auction.order.pay", "订单确认付款 ID: " . $item["id"] . " 订单号: " . $item["ordersn"]);
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
			Db::name('shop_auction_order')->where('id = ' . $item['id'])->update(array( "status" => 2, "express" => trim($_POST["express"]), "expresscom" => trim($_POST["expresscom"]), "expresssn" => trim($_POST["expresssn"]), "sendtime" => time() ));
			model('notice')->sendTeamMessage($item["id"]);
			model('shop')->plog("auction.order.send", "订单发货 ID: " . $item["id"] . " 订单号: " . $item["ordersn"] . " <br/>快递公司: " . $_POST["expresscom"] . " 快递单号: " . $_POST["expresssn"]);
			show_json(1);
		}
		$address = iunserializer($item["address"]);
		if( !is_array($address) ) 
		{
			$address = Db::name('shop_member_address')->where('id = ' . $item['addressid'])->find();
		}
		$express_list = model("shop")->getExpressList();
		$this->assign(['id'=>$id,'item'=>$item,'address'=>$address,'express_list'=>$express_list]);
		echo $this->fetch('auction/order/send');
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

			Db::name('shop_auction_order')->where('id = ' . $item['id'])->update(array( "status" => 1, "sendtime" => 0, "remarksend" => $remark ));
			model('shop')->plog("auction.order.sendcancel", "订单取消发货 ID: " . $item["id"] . " 订单号: " . $item["ordersn"] . " 原因: " . $remark);
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('auction/order/sendcancel');
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
				pdo_update("shop_auction_order", $change_data, array( "id" => $id, "uniacid" => $_W["uniacid"] ));
				model('shop')->plog("auction.order.changeexpress", "修改快递状态 ID: " . $item["id"] . " 订单号: " . $item["ordersn"] . " 快递公司: " . $expresscom . " 快递单号: " . $expresssn);
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
		echo $this->fetch('auction/order/send');
	}

	public function orderfinish() 
	{
		$opdata = $this->opData();
		extract($opdata);
		Db::name('shop_auction_order')->where('id = ' . $item['id'])->update(array( "status" => 3, "finishtime" => time() ));
		model("notice")->sendTeamMessage($item["id"]);
		model('shop')->plog("auction.order.finish", "订单完成 ID: " . $item["id"] . " 订单号: " . $item["ordersn"]);
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
			Db::name('shop_auction_order')->where('id = ' . $item['id'])->update(array( "status" => -1, "refundstate" => 0, "canceltime" => $time, "remarkclose" => trim($_POST["remark"]) ));
			model('shop')->plog("auction.order.close", "订单关闭 ID: " . $item["id"] . " 订单号: " . $item["ordersn"]);
			show_json(1);
		}
		$this->assign(['id'=>$id,'item'=>$item]);
		echo $this->fetch('auction/order/close');
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

		$pdo_res = Db::name('shop_auction_order')->where('status','>',0)->where('paytime','between',[$createtime1,$createtime2])->field('id,price,mid')->select();
		$price = 0;

		foreach ($pdo_res as $key => $value) {
			$price += floatval($value['price']);
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