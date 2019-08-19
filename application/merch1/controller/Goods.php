<?php
/**
 * 商品管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Goods extends Base
{
	public function index()
	{
		header('location: ' . url('merch/goods/sale'));exit;
	}

	public function category()
	{
		$children = array();
		$merch=$this->merch;
		$category = Db::name('shop_store_goods_category')->where('merchid',$merch['id'])->order('parentid','asc')->order('displayorder','desc')->select();
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
		$merch=$this->merch;
		$parentid = input('parentid/d');
		$id = input('id/d');
		$parent = array();
		$parent1 = array();
		if (!empty($id)) {
			$item = Db::name('shop_store_goods_category')->where('id',$id)->find();
			$parentid = $item['parentid'];
		} else {
			$item = array('displayorder' => 0);
		}

		if (!empty($parentid)) {
			$parent = Db::name('shop_store_goods_category')->where('id',$parentid)->find();

			if (empty($parent)) {
				$this->error('抱歉，上级分类不存在或是已经被删除！', url('merch/goods/categoryadd'));
			}

			if (!empty($parent['parentid'])) {
				$parent1 = Db::name('shop_store_goods_category')->where('id',$parent['parentid'])->find();
			}
		}

		if (empty($parent)) {
			$level = 1;
		} else if (empty($parent['parentid'])) {
			$level = 2;
		} else {
			$level = 3;
		}

		if (!empty($item)) {
			$item['url'] = url('merch/goods/list', array('cate' => $item['id']));
		}

		if (Request::instance()->isPost()) {
			$data = array('name' => trim(input('catename')), 'merchid' => intval($merch['id']), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'isrecommand' => intval(input('isrecommand')), 'ishome' => intval(input('ishome')), 'description' => input('description'), 'parentid' => intval($parentid), 'thumb' => trim(input('thumb')), 'advimg' => trim(input('advimg')), 'advurl' => trim(input('advurl')), 'level' => $level);

			if (!empty($id)) {
				unset($data['parentid']);
				Db::name('shop_store_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('shop.category.edit', '修改分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_store_goods_category')->insertGetId($data);
				model('shop')->plog('shop.category.add', '添加分类 ID: ' . $id);
			}

			model('shop')->getCategory();
			model('shop')->getAllCategory();
			show_json(1, array('url' => url('merch/goods/category')));
		}
		$this->assign(['item'=>$item,'parentid'=>$parentid,'parent'=>$parent,'parent1'=>$parent1]);
		return $this->fetch('goods/category/post');
	}

	public function categorydelete()
	{
		$id = input('id/d');
		$item = Db::name('shop_store_goods_category')->where('id',$id)->field('id,name,parentid')->find();
		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		$child = Db::name('shop_store_goods_category')->where('parentid',$id)->count();
		if($child > 0)
		{
			show_json(0,'请先删除下级分类');
		}
		Db::name('shop_store_goods_category')->where('id',$id)->whereOr('parentid',$id)->delete();
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

		$items = Db::name('shop_store_goods_category')->where('id','in',$id)->select();

		foreach ($items as $item) {
			Db::name('shop_store_goods_category')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('shop.dispatch.edit', ('修改分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['name'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}

		model('shop')->getCategory();
		show_json(1);
	}

	public function label()
	{
		$psize = 20;
		$merch=$this->merch;
		$condition = ' merchid =  ' . $merch['id'];

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
		$merch=$this->merch;
		if (!empty($id)) {
			$item = Db::name('shop_goods_label')->where('id',$id)->find();

			if (json_decode($item['labelname'], true)) {
				$labelname = json_decode($item['labelname'], true);
			} else {
				$labelname = unserialize($item['labelname']);
			}
		}

		if (Request::instance()->isPost()) {
			if (empty(input('labelname/a'))) {
				$labelname = array();
			}
			$labelname = input('labelname/a');
			$data = array('merchid' => $merch['id'], 'displayorder' => input('displayorder/d'), 'label' => trim(input('label')), 'labelname' => serialize(array_filter($labelname)), 'status' => intval(input('status')));

			if (!empty($item)) {
				Db::name('shop_goods_label')->where('id',$item['id'])->update($data);
				model('shop')->plog('goods.label.edit', '修改标签组 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_goods_label')->insertGetId($data);
				model('shop')->plog('goods.label.add', '添加标签组 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/goods/labeledit', array('id' => $id))));
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
		$merch=$this->merch;
		$condition = ' status = 1 and merchid =  ' . $merch['id'];

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
		$merch = $this->merch;
		$store_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		$psize = 20;
		$condition = ' 1 and isgroups = 0 and merchid = ' . $merch['id'];
		$keyword = input('keyword');
		$cate = input('cate');
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' AND ( g.`title` LIKE "%' . $keyword . '%" or g.`keywords` LIKE "%' . $keyword . '%" or g.`goodssn` LIKE "%' . $keyword . '%" or g.`productsn` LIKE "%' . $keyword . '%"';

			$condition .= ')';
		}

		if(empty($goodsfrom))
		{
			$goodsfrom = 'sale';
		}

		if ($goodsfrom == 'sale') {
			$condition .= ' AND g.status > 0 and g.checked=0 and g.total>0 and g.deleted=0 ';
			$status = 1;
		} else if ($goodsfrom == 'out') {
			$condition .= ' AND g.status > 0 and g.total <= 0 and g.deleted=0 and g.type!=30 ';
			$status = 1;
		} else if ($goodsfrom == 'stock') {
			$status = 0;
			$condition .= ' AND (g.status = 0 or g.checked=1) and g.deleted=0 ';
		} else if ($goodsfrom == 'cycle') {
			$status = 0;
			$condition .= ' AND g.deleted=1 ';
		} else {
			if ($goodsfrom == 'verify') {
				$status = 0;
				$condition .= ' AND g.deleted=0 and merchid>0 and checked=1 ';
			}
		}

		if (!empty($cate)) {
			$cate = intval($cate);
			$condition .= ' AND FIND_IN_SET(' . $cate . ',cates)<>0 ';
		}
		
		$list = Db::name('shop_goods')
			->alias('g')
			// ->join('shop_goods_option op','g.id = op.goodsid','left')
			// ->join('shop_store store','store.id = g.merchid','left')
			->where($condition)
			->field('g.*')
			->order('g.createtime','desc')
			->paginate($psize);
		$pager = $list->render();
		$categorys = model('shop')->getFullCategory(true);
		$category = array();

		foreach ($categorys as $val) {
			$category[$val['id']] = $val;
		}

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
		$merch = $this->merch;
		$id = input('id/d');
		if (!(empty($id))) {
			Db::name('shop_goods')->where('id',$id)->setField('newgoods',0);
		}
		$item = Db::name('shop_goods')->where('id',$id)->find();

		if (!(empty($item)) && ($item['type'] == 5) && !(empty($item['opencard'])) && !(empty($item['cardid']))) {
			$card = Db::name('shop_goods_cards')->where('id',$item['cardid'])->find();
		}
		$status = $item['status'];

		if (json_decode($item['labelname'], true)) {
			$labelname = json_decode($item['labelname'], true);
		}
		else {
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

		$merchid = $merch['id'];
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

		if (Request::instance()->isPost()) {
			if (empty($id)) {
				$goodstype = input('type/d');
			} else {
				$goodstype = input('goodstype/d');
			}

			if (($goodstype != 1) && (input('status/d') == 2)) {
				show_json(0, '赠品只能是实体商品');
			}
			$data = array('displayorder' => input('displayorder/d',0), 'title' => trim(input('goodsname/s','')), 'unit' => trim(input('unit/s')), 'subtitle' => trim(input('subtitle/s')), 'shorttitle' => trim(input('shorttitle/s')), 'keywords' => trim(input('keywords/s')), 'type' => $goodstype, 'ispresell' => input('ispresell/d',0), 'presellprice' => input('presellprice/f'), 'presellstart' => input('presellstart/d',0), 'presellend' => input('presellend/d',0), 'preselltimestart' => (0 < input('presellstart/d',0) ? strtotime(input('preselltimestart')) : 0), 'preselltimeend' => (0 < input('presellend/d',0) ? strtotime(input('preselltimeend')) : 0), 'presellsendtype' => input('presellsendtype/d',0), 'presellsendstatrttime' => strtotime(input('presellsendstatrttime')), 'presellsendtime' => input('presellsendtime/d',0), 'presellover' => input('presellover/d',0), 'presellovertime' => (0 < input('presellovertime/d',0) ? input('presellovertime/d') : 0), 'virtualsend' => input('virtualsend/d',0), 'virtualsendcontent' => trim(input('virtualsendcontent/s')), 'virtual' => ($goodstype == 3 ? input('virtual/d',0) : 0), 'isrecommand' => input('isrecommand/d',0), 'ishot' => input('ishot/d',0), 'isnew' => input('isnew/d',0), 'issendfree' => input('issendfree/d',0), 'isnodiscount' => input('isnodiscount/d',0), 'marketprice' => input('marketprice'), 'productprice' => trim(input('productprice')), 'costprice' => input('costprice'), 'thumb_first' => input('thumb_first/d',0), 'video' => trim(input('video/s')), 'sales' => input('sales/d',0), 'showsales' => input('showsales/d',0), 'ednum' => input('ednum/d',0), 'edmoney' => trim(input('edmoney')), 'edareas' => trim(input('edareas')), 'edareas_code' => trim(input('edareas_code')), 'province' => trim(input('province/s')), 'city' => trim(input('city/s')), 'invoice' => input('invoice/d',0), 'quality' => input('quality/d',0), 'seven' => input('seven/d',0), 'repair' => input('repair/d',0), 'labelname' => serialize(input('labelname/a')), 'status' => ($status != 2 ? input('status/d',0) : $status), 'isstatustime' => input('isstatustime/d',0), 'nosearch' => input('nosearch/d',0), 'groupstype' => input('groupstype/d',0), 'cannotrefund' => input('cannotrefund/d',0), 'autoreceive' => input('autoreceive/d',0), 'goodssn' => trim(input('goodssn/s')), 'productsn' => trim(input('productsn/s')), 'weight' => input('weight'), 'total' => input('total/d',0), 'totalcnf' => input('totalcnf/d',0), 'hasoption' => input('hasoption/d',0), 'maxbuy' => input('maxbuy/d',0), 'minbuy' => input('minbuy/d',0), 'usermaxbuy' => input('usermaxbuy/d',0), 'showlevels' => (is_array(input('showlevels/a')) ? implode(',', input('showlevels/a')) : ''), 'buylevels' => (is_array(input('buylevels/a')) ? implode(',', input('buylevels/a')) : ''), 'showgroups' => (is_array(input('showgroups/a')) ? implode(',', input('showgroups/a')) : ''), 'buygroups' => (is_array(input('buygroups/a')) ? implode(',', input('buygroups/a')) : ''), 'isdiscount' => input('isdiscount/d',0), 'isdiscount_title' => trim(mb_substr(input('isdiscount_title'), 0, 5, 'UTF-8')), 'isdiscount_time' => strtotime(input('isdiscount_time')), 'money' => input('money'), 'deduct' => input('deduct'), 'manydeduct' => input('manydeduct'), 'deduct2' => input('deduct2'), 'buyagain' => input('buyagain/f'), 'buyagain_islong' => input('buyagain_islong/d',0), 'buyagain_condition' => input('buyagain_condition/d',0), 'buyagain_sale' => input('buyagain_sale/d',0), 'istime' => input('istime/d',0), 'timestart' => strtotime(input('saletime/a')['start']), 'timeend' => strtotime(input('saletime/a')['end']), 'description' => trim(input('description/s')), 'createtime' => time(), 'showtotal' => input('showtotal/d',0), 'unite_total' => input('unite_total/d',0), 'credit' => trim(input('credit')), 'buyshow' => input('buyshow/d',0));
			if($goodstype!=4) {
				if($data['marketprice']==''||$data['productprice']==''||$data['costprice']=='') {
					show_json(0, '商品价格必填！');
				}
			}
			$statustimestart = strtotime(input('statustime/a')['start']);
			$statustimeend = strtotime(input('statustime/a')['end']);
			$data['statustimestart'] = $statustimestart;
			$data['statustimeend'] = $statustimeend;
			if (($data['status'] == 1) && (0 < $data['isstatustime'])) {
				if (!(($statustimestart < time()) && (time() < $statustimeend))) {
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

			$data['isendtime'] = input('isendtime');
			$data['usetime'] = input('usetime');
			$data['endtime'] = strtotime('endtime');
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
				foreach ($cates as $key => $cid ) {
					$c = Db::name('shop_store_goods_category')->where('id',$id)->field('level')->find();

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
							$crow = Db::name('shop_store_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow['parentid'];
							$ccateid = $cid;
						} else if ($c['level'] == 3) {
							$tcateid = $cid;
							$tcate = Db::name('shop_store_goods_category')->where('id',$cid)->field('id,parentid')->find();
							$ccateid = $tcate['parentid'];
							$ccate = Db::name('shop_store_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
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
			if (empty($id)) {
				$data['merchid'] = $merch['id'];
				$id = Db::name('shop_goods')->insertGetId($data);
				model('shop')->plog('goods.add', '添加商品 ID: ' . $id);
			} else {
				$data['merchid'] = $merch['id'];
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
			$option_idss = $optionArray['option_ids'];
			$len = count($option_idss);
			$optionids = array();
			$levelArray = array();
			$isDiscountsArray = array();
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
			show_json(1, array('url' => url('merch/goods/edit', array('id' => $id))));
		}

		if (!(empty($id))) {
			if (empty($item)) {
				$this->error('抱歉，商品不存在或是已经删除！');
			}
			$cates = explode(',', $item['cates']);
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

					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">库存</div><div class="input-group"><input type="text" class="form-control input-sm option_stock_all"  VALUE=""/><span class="input-group-addon" ><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_stock\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">预售价</div><div class="input-group"><input type="text" class="form-control  input-sm option_presell_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_presell\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">现价</div><div class="input-group"><input type="text" class="form-control  input-sm option_marketprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_marketprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">原价</div><div class="input-group"><input type="text" class="form-control input-sm option_productprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">成本价</div><div class="input-group"><input type="text" class="form-control input-sm option_costprice_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_costprice\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">编码</div><div class="input-group"><input type="text" class="form-control input-sm option_goodssn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_goodssn\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">条码</div><div class="input-group"><input type="text" class="form-control input-sm option_productsn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productsn\');"></a></span></div></div></th>';
					$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">重量（克）</div><div class="input-group"><input type="text" class="form-control input-sm option_weight_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></div></th>';
				}
				 else {
					foreach ($levels as $level ) {
						$discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div></div></th>';
						$isdiscount_discounts_html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">' . $level['levelname'] . '</div></div></th>';
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
					}
					 else {
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
			$dispatch_data = Db::name('shop_dispatch')->where('merchid',$merchid)->where('enabled',1)->order('displayorder','desc')->select();
			if ($merchid == 0) {
				$details = Db::name('shop_goods')->whereNotNull('detail_shopname')->group('detail_shopname')->field('detail_logo,detail_shopname,detail_btntext1, detail_btnurl1 ,detail_btntext2,detail_btnurl2,detail_totaltitle')->select();

				foreach ($details as &$d ) {
					$d['detail_logo_url'] = tomedia($d['detail_logo']);
				}
				unset($d);
			}
		}
		$this->assign(['item'=>$item,'category'=>$category,'dispatch_data'=>$dispatch_data,'new_area'=>$new_area,'address_street'=>$address_street,'levels'=>$levels,'groups'=>$groups,'virtual_types'=>$virtual_types,'allspecs'=>$allspecs,'cates'=>$cates,'piclist'=>$piclist,'params'=>$params,'html'=>$html,'discounts_html'=>$discounts_html,'isdiscount_discounts_html'=>$isdiscount_discounts_html,'areas'=>$areas,'labelname'=>$labelname,'intervalprices'=>$intervalprices,'details'=>$details,'discounts'=>$discounts,'no_left'=>true,'merchid'=>$merchid]);
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
					$c = Db::name('shop_store_goods_category')->where('id',$cid)->field('level')->find();

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
							$crow = Db::name('shop_store_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow['parentid'];
							$ccateid = $cid;
						}
						else {
							if ($c['level'] == 3) {
								$tcateid = $cid;
								$tcate = Db::name('shop_store_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate['parentid'];
								$ccate = Db::name('shop_store_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
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

			show_json(1, array('url' => url('merch/goods/edit', array('id' => $id))));
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
		}
		else if ($tpl == 'spec') {
			$spec = array('id' => random(32), 'title' => input('title'));
			$this->assign(['spec'=>$spec]);
			return $this->fetch('goods/tpl/spec');
		}
		else if ($tpl == 'specitem') {
			$spec = array('id' => input('specid'));
			$specitem = array('id' => random(32), 'title' => input('title'), 'show' => 1);
			$this->assign(['spec'=>$spec,'specitem'=>$specitem]);
			return $this->fetch('goods/tpl/spec_item');
		}
		else {
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
		}
		else {
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
				} else {
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
			model('shop')->plog('goods.edit', ('修改商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . input('checked/d')) == 0 ? '审核通过' : '审核中');
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

		show_json(1);
	}

}