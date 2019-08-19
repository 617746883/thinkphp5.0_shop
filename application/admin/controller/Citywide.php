<?php
/**
 * 同城
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Citywide extends Base
{
	public function index()
    {
    	header('location: ' . url('admin/citywide/banner'));exit;
    }

    public function lifestore()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$status = input('status');
		$keyword = trim(input('keyword'));
		if ($status != '') {
			$condition .= ' and status=' . $status;
		}

		if (!empty($keyword)) {
			$condition .= ' and storename like "%' . $keyword . '%"';
		}

		$list = Db::name('citywide_life_store')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
    	return $this->fetch('citywide/life/store/index');
    }

    public function lifestoreadd()
	{
		$storedata = $this->lifestorepost();
		return $storedata;
	}

	public function lifestoreedit()
	{
		$storedata = $this->lifestorepost();
		return $storedata;
	}

	public function lifestorestatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('citywide_life_store')->where('id','in',$id)->field('id,storename')->select();

		foreach ($items as $item) {
			Db::name('citywide_life_store')->where('id',$item['id'])->setField('status',input('status'));
			model('shop')->plog('citywide_life_store.edit', ('修改门店状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['storename'] . '<br/>状态: ' . input('status')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	protected function lifestorepost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$map = input('map/a');
			if(!empty($map) && !empty($map['lat']) && !empty($map['lng']))
			{
				$lat = $map['lat'];
				$lng = $map['lng'];
			}
			$data = array('storename' => trim(input('storename')),'displayorder'=>input('displayorder/d'),'cate'=>input('cate/d'),'saletime' => trim(input('saletime')), 'logo' => trim(input('logo')), 'status' => intval(input('status')), 'tel' => trim(input('tel')), 'mobile' => trim(input('mobile')), 'province' => input('province/s','云南省'),'contacts'=>input('contacts/s'), 'city' => input('city/s'), 'area' => input('area/s'), 'provincecode' => input('chose_province_code'), 'citycode' => input('chose_city_code'), 'areacode' => input('chose_area_code'), 'lat' => $lat, 'lng' => $lng, 'address' => input('address'), 'isrecommand'=>input('isrecommand'),'desc' => trim(input('desc')));
			$banner = input('banner/a');
			$data['banner'] = iserializer($banner);
			if (!empty($id)) {
				Db::name('citywide_life_store')->where('id',$id)->update($data);
				model('shop')->plog('Citywide.edit', '修改门店  ID: ' . $id);
			}
			else {
				$id = Db::name('citywide_life_store')->insertGetId($data);
				model('shop')->plog('Citywide.add', '添加门店 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/Citywide/index', array('id' => $id))));
			
		}
		$item = Db::name('citywide_life_store')->where('id',$id)->find();
		$banner = iunserializer($item['banner']);
		$area_set = model('util')->get_area_config_set();
		$new_area = 1;
		$address_street = 0;
		$cate = Db::name('citywide_life_store_category')->field('id,catename')->select();
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street,'banner'=>$banner,'cate'=>$cate]);
		return $this->fetch('citywide/life/store/post');
	}

	public function lifestoredelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$delete = Db::name('citywide_life_store')->where('id','in',$id)->field('id,storename')->select();

		foreach ($delete as $del) {
			Db::name('citywide_life_store')->where('id',$del['id'])->delete();
			model('shop')->plog('citywide_life_store.delete', '删除门店 ID: ' . $del['id'] . ' 标题: ' . $del['storename'] . ' ');
		}

		show_json(1);
	}

	public function lifecategory()
	{
		
		$list = Db::name('citywide_life_store_category')->order('id','desc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('citywide/life/category/index');
	}

	public function lifecategoryadd()
	{
		
		$categorydata = $this->lifecategorypost();
		return $categorydata;
	}

	public function lifecategoryedit()
	{
		
		$categorydata = $this->lifecategorypost();
		return $categorydata;
	}

	public function lifecategorypost()
	{
		
		$id = intval(input('id'));
		if (Request::instance()->isPost()) {
			$data = array('catename' => trim(input('catename')),'color'=>input('color'),'displayorder'=>input('displayorder/d'), 'thumb' => trim(input('thumb')), 'status' => intval(input('status')), 'isrecommand' => intval(input('isrecommand')),'isrecommand' => intval(input('isrecommand')));
			if (!empty($id)) {
				Db::name('citywide_life_store_category')->where('id',$id)->update($data);
				model('shop')->plog('Citywide.edit', '修改门店分类  ID: ' . $id);
			}
			else {
				$data['createtime'] = time();
				$id = Db::name('citywide_life_store_category')->insertGetId($data);
				model('shop')->plog('Citywide.add', '添加门店分类 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/Citywide/lifecategory', array('id' => $id))));
			
		}
		$item = Db::name('citywide_life_store_category')->where('id',$id)->find();
		$area_set = model('util')->get_area_config_set();
		$new_area = 1;
		$address_street = 0;
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('citywide/life/category/post');
	}

	public function lifecategorystatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('citywide_life_store_category')->where('id','in',$id)->field('id,catename')->select();

		foreach ($items as $item) {
			Db::name('citywide_life_store_category')->where('id',$item['id'])->setField('status',input('status'));
			model('shop')->plog('Citywide.edit', ('修改类型状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['catename'] . '<br/>状态: ' . input('status')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function lifecategorydelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$delete = Db::name('citywide_life_store_category')->where('id','in',$id)->field('id,catename')->select();

		foreach ($delete as $del) {
			Db::name('citywide_life_store_category')->where('id',$del['id'])->delete();
			model('shop')->plog('Citywide.delete', '删除门店类型 ID: ' . $del['id'] . ' 标题: ' . $del['catename'] . ' ');
		}

		show_json(1);
	}

	public function banner()
	{
		
		$list = Db::name('citywide_banner')
		->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('citywide/banner/index');
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

	public function bannerpost()
	{
		
		$id = intval(input('id'));
		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim(input('bannername')), 'thumb' => trim(input('thumb')), 'link' => input('link'), 'shopid' => intval(input('shopid')),'displayorder'=>input('displayorder/d'));
			if (!empty($id)) {
				Db::name('citywide_banner')->where('id',$id)->update($data);
				model('shop')->plog('Citywide.edit', '修改幻灯片  ID: ' . $id);		
			}
			else {
				$id = Db::name('citywide_banner')->insertGetId($data);
				model('shop')->plog('Citywide.add', '添加幻灯片 ID: ' . $id);
				
			}
			show_json(1, array('url' => url('admin/Citywide/banner', array('id' => $id))));
		}
		$item = Db::name('citywide_banner')->where('id',$id)->find();
		$area_set = model('util')->get_area_config_set();
		$new_area = 1;
		$address_street = 0;
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('citywide/banner/post');
	}

	public function bannerstatus()
	{
		$id = input('id/d');
		$enabled = input('enabled');
		if (empty($id)) {
			$id = input('ids/a');
		}
		$items = Db::name('citywide_banner')->where('id','in',$id)->field('id,bannername')->select();
		foreach ($items as $item) {
			Db::name('citywide_banner')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('Citywide.edit', ('修改类型状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}


	public function bannerdelete()
	{
		
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}
		$delete = Db::name('citywide_banner')->where('id','in',$id)->field('id,bannername')->select();
		foreach ($delete as $del) {
			Db::name('citywide_banner')->where('id',$del['id'])->delete();
			model('shop')->plog('Citywide.delete', '删除幻灯片 ID: ' . $del['id'] . ' 标题: ' . $del['bannername'] . ' ');
		}

		show_json(1);
	}
	public function lifebanner()
	{
		
		$list = Db::name('citywide_life_banner')
		->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('citywide/life/banner/index');
	}

	public function lifebanneradd()
	{
		
		$bannerdata = $this->lifebannerpost();
		return $bannerdata;
	}

	public function lifebanneredit()
	{
		
		$bannerdata = $this->lifebannerpost();
		return $bannerdata;
	}

	public function lifebannerpost()
	{
		
		$id = intval(input('id'));
		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim(input('bannername')), 'thumb' => trim(input('thumb')), 'link' => input('link'),'displayorder'=>input('displayorder/d'));
			if (!empty($id)) {
				Db::name('citywide_life_banner')->where('id',$id)->update($data);
				model('shop')->plog('Citywide.edit', '修改幻灯片  ID: ' . $id);		
			}
			else {
				$id = Db::name('citywide_life_banner')->insertGetId($data);
				model('shop')->plog('Citywide.add', '添加幻灯片 ID: ' . $id);
				
			}
			show_json(1, array('url' => url('admin/Citywide/lifebanner', array('id' => $id))));
		}
		$item = Db::name('citywide_life_banner')->where('id',$id)->find();
		$area_set = model('util')->get_area_config_set();
		$new_area = 1;
		$address_street = 0;
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('citywide/life/banner/post');
	}

	public function lifebannerstatus()
	{
		$id = input('id/d');
		$enabled = input('enabled');
		if (empty($id)) {
			$id = input('ids/a');
		}
		$items = Db::name('citywide_life_banner')->where('id','in',$id)->field('id,bannername')->select();
		foreach ($items as $item) {
			Db::name('citywide_life_banner')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('Citywide.edit', ('修改类型状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function lifebannerdelete()
	{
		
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}
		$id2 = Db::name('citywide_life_banner')->where('shopid',$id)->value('id');
		$delete = Db::name('citywide_life_banner')->where('id','in',$id2)->field('id,bannername')->select();

		foreach ($delete as $del) {
			Db::name('citywide_life_banner')->where('id',$del['id'])->delete();
			model('shop')->plog('Citywide.delete', '删除幻灯片 ID: ' . $del['id'] . ' 标题: ' . $del['bannername'] . ' ');
		}

		show_json(1);
	}

	public function secondcategory()
	{
		$children = array();
		$category = Db::name('citywide_secondgoods_category')->order('parentid','asc')->order('displayorder','desc')->select();
		foreach ($category as $index => $row) 
		{
			if (!empty($row['parentid'])) 
			{
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}
		$this->assign(['children'=>$children,'category'=>$category]);
		return $this->fetch('citywide/secondgoods/category/index');
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

	public function categorypost()
	{
		$parentid = input('parentid/d');
		$id = input('id/d');
		$parent = array();
		$parent1 = array();
		if (!empty($id)) {
			$item = Db::name('citywide_secondgoods_category')->where('id',$id)->find();
			$parentid = $item['parentid'];
		} else {
			$item = array('displayorder' => 0);
		}

		if (!empty($parentid)) {
			$parent = Db::name('citywide_secondgoods_category')->where('id',$parentid)->find();

			if (empty($parent)) {
				$this->error('抱歉，上级分类不存在或是已经被删除！', url('admin/citywide/categoryadd'));
			}

			if (!empty($parent['parentid'])) {
				$parent1 = Db::name('citywide_secondgoods_category')->where('id',$parent['parentid'])->find();
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
			$item['url'] = url('citywide/secondcategory', array('cate' => $item['id']));
		}

		if (Request::instance()->isPost()) {
			$data = array('name' => trim(input('name')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'isrecommand' => intval(input('isrecommand')),'description' => input('description'), 'parentid' => intval($parentid), 'thumb' => trim(input('thumb')),'level' => $level);
			if (!empty($id)) {
				unset($data['parentid']);
				Db::name('citywide_secondgoods_category')->where('id',$id)->update($data);
				model('shop')->plog('citywide.category.edit', '修改分类 ID: ' . $id);
			} else {
				$id = Db::name('citywide_secondgoods_category')->insertGetId($data);
				model('shop')->plog('citywide.category.add', '添加分类 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/citywide/secondcategory')));
		}
		$this->assign(['item'=>$item,'parentid'=>$parentid,'parent'=>$parent,'parent1'=>$parent1]);
		return $this->fetch('citywide/secondgoods/category/post');
	}

	public function categoryenabled()
	{
		$enabled = input('enabled/d');
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}
		$items = Db::name('citywide_secondgoods_category')->where('id','in',$id)->select();

		foreach ($items as $item) {
			Db::name('citywide_secondgoods_category')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('citywide.dispatch.edit', ('修改分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['name'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$delete = Db::name('citywide_secondgoods_category')->where('id','in',$id)->field('id,name')->select();

		foreach ($delete as $del) {
			Db::name('citywide_secondgoods_category')->where('id',$del['id'])->delete();
			model('shop')->plog('Citywide.delete', '删除二手 ID: ' . $del['id'] . ' 标题: ' . $del['name'] . ' ');
		}

		show_json(1);
	}

	public function secondgoods()
	{
		$cate = input('cate');
		$keyword = input('keyword');
		$psize = 15;
		$condition = ' 1 ';
		if ($cate != '') {
			$condition .= ' and s.cate=' . $cate;
		}
		if (!empty($keyword)) {
			$condition .= ' and s.title like "%' . $keyword . '%"';
		}
		$list = Db::name('citywide_secondgoods')->alias('s')->join('citywide_secondgoods_category c','s.cate = c.id','left')->where($condition)->order('s.createtime','desc')->field('s.*,c.name as catename')->paginate($psize);
		$pager = $list->render();
		$category = Db::name('citywide_secondgoods_category')->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();
		$category = model('goods')->getCategoryTree($category, 3);
		$this->assign(['list'=>$list,'pager'=>$pager,'category'=>$category,'cate'=>$cate,'keyword'=>$keyword]);
		return $this->fetch('citywide/secondgoods/index');
	}

	public function secondgoodsstatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}
		$status = input('status/d');

		$items = Db::name('citywide_secondgoods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('citywide_secondgoods')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('secondgoods.goods.edit', ('修改二手商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . $status) == 0 ? '下架' : '上架');
		}

		show_json(1, array('url' => referer()));
	}

	public function secondgoodschecked()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('citywide_secondgoods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('citywide_secondgoods')->where('id',$item['id'])->setField('checked',input('checked/d'));
			model('notice')->sendSecondgoodschecked($item['id']);
			model('shop')->plog('secondgoods.goods.edit', ('修改二手商品状态<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title'] . '<br/>状态: ' . input('checked/d')) == 0 ? '审核通过' : '审核中');
		}

		show_json(1, array('url' => referer()));
	}

	public function secondgoodsdelete1()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('citywide_secondgoods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('citywide_secondgoods')->where('id',$item['id'])->delete();
			model('shop')->plog('secondgoods.goods.edit', '从回收站彻底删除商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function secondgoodsdelete()
	{
		$id = input('id/d',0);

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('citywide_secondgoods')->where('id','in',$id)->field('id,title')->select();
		foreach ($items as $item) {
			Db::name('citywide_secondgoods')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('secondgoods.goods.delete', '删除商品 ID: ' . $item['id'] . ' 商品名称: ' . $item['title'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function secondgoodsdetail()
	{
		$id = input('id/d');
		$item = Db::name('citywide_secondgoods')->where('id',$id)->find();
		$thumb_url = array();
		if(!empty($item['thumb_url'])) {
			$thumb_url = iunserializer($item['thumb_url']);
		}

		if (Request::instance()->isPost()) {
			$description = input('description/s');
			$data = array('cate'=>input('cate'),'title'=>input('title'),'thumb' => $thumb,'thumb_url' => $thumb_url,'description'=>$description,'mobile'=>input('mobile'),'degree'=>input('degree'),'buytime'=>strtotime(input('buytime')),'productprice'=>input('productprice'),'marketprice'=>input('marketprice'),'content'=>model('common')->html_images($_POST['content']),'province'=>input('province'),'city'=>input('city'),'area'=>input('area'),'checked'=>input('checked/d'),'status'=>input('status/d'),'failedreason'=>trim(input('failedreason/s','')));

			if (is_array($_POST['thumb_url'])) {
				$thumbs = input('thumb_url/a');
				$thumb_url = array();

				foreach ($thumbs as $th ) {
					$thumb_url[] = trim($th);
				}

				$data['thumb'] = trim($thumb_url[0]);
				$data['thumb_url'] = serialize($thumb_url);
			}
			if (!empty($item)) {
				Db::name('citywide_secondgoods')->where('id',$id)->update($data);
				model('shop')->plog('citywide.secondgoods.edit', '修改二手商品 ID: ' . $id);
			} else {
				$data['createtime'] = time();
				$id = Db::name('citywide_secondgoods')->insertGetId($data);
				model('shop')->plog('citywide.secondgoods.add', '添加二手商品 ID: ' . $id);
			}
			if(input('checked/d') != 1) {
				model('notice')->sendSecondgoodschecked($item['id']);
			}
			show_json(1, array('url' => url('admin/citywide/secondgoodsdetail',array('id'=>$id))));
		}

		$categorys = Db::name('citywide_secondgoods_category')->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();
		$allcategory = array();
		if (empty($categorys)) {
			$allcategory = array();
		} else {
			foreach ($categorys as &$c) {
				if (empty($c['parentid'])) {
					$allcategory[] = $c;
					foreach ($categorys as &$c1) {
						if ($c1['parentid'] != $c['id']) {
							continue;
						}
						$c1['name'] = $c['name'] . '-' . $c1['name'];
						$allcategory[] = $c1;
						foreach ($categorys as &$c2) {
							if ($c2['parentid'] != $c1['id']) {
								continue;
							}
							$c2['name'] = $c1['name'] . '-' . $c2['name'];
							$allcategory[] = $c2;
							foreach ($categorys as &$c3) {
								if ($c3['parentid'] != $c2['id']) {
									continue;
								}
								$c3['name'] = $c2['name'] . '-' . $c3['name'];
								$allcategory[] = $c3;
							}
							unset($c3);
						}
						unset($c2);
					}
					unset($c1);
				}
				unset($c);
			}
		}
		$category = array();
		foreach ($allcategory as $val) {
			$category[$val['id']] = $val;
		}
		$this->assign(['item'=>$item,'category'=>$category,'thumb_url'=>$thumb_url]);
		return $this->fetch('citywide/secondgoods/detail');
	}

}