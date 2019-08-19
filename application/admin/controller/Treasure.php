<?php
/**
 * 众筹夺宝
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Treasure extends Base
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
		if ($enabled != '') {
			$condition .= ' and enabled=' . intval(input('enabled'));
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and bannername like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_treasure_banner')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
    	return $this->fetch('treasure/banner/index');
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
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim(input('bannername')), 'link' => trim(input('link')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'thumb' => trim(input('thumb')));
			if (!empty($id)) {
				Db::name('shop_treasure_banner')->where('id',$id)->update($data);
				model('shop')->plog('treasure.banner.edit', '修改幻灯片 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_treasure_banner')->insertGetId($data);
				model('shop')->plog('treasure.banner.add', '添加幻灯片 ID: ' . $id);
			}
			show_json(1);
		}
		$item = Db::name('shop_treasure_banner')->where('id',$id)->find();

		$request = Request::instance();
		$controller = strtolower($request->controller());
		$this->assign(['item'=>$item,'controller'=>$controller]);
		return $this->fetch('treasure/banner/post');
	}

	public function bannerdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_treasure_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_treasure_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('treasure.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1);
	}

	public function bannerdisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item =  Db::name('shop_treasure_banner')->where('id',$id)->field('id,bannername')->find();

		if (!empty($item)) {
			Db::name('shop_treasure_banner')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('treasure.banner.edit', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function bannerenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_treasure_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_treasure_banner')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('treasure.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function category()
	{
		$list = Db::name('shop_treasure_goods_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('treasure/category/index');
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
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('name' => trim($_POST['catename']), 'enabled' => intval($_POST['enabled']), 'isrecommand' => intval($_POST['isrecommand']), 'displayorder' => intval($_POST['displayorder']), 'thumb' => trim($_POST['thumb']));

			if (!empty($id)) {
				Db::name('shop_treasure_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('treasure.category.edit', '修改积分商城分类 ID: ' . $id);
			} else {
				$id = Db::name('shop_treasure_goods_category')->insertGetId($data);
				model('shop')->plog('treasure.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/treasure/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_treasure_goods_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('treasure/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_treasure_goods_category')->where('id',$id)->field('id,name')->find();
		if (!empty($item)) {
			Db::name('shop_treasure_goods_category')->where('id',$id)->update(array('displayorder' => $displayorder));
			model('shop')->plog('treasure.category.edit', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}
		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_treasure_goods_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}

		Db::name('shop_treasure_goods_category')->where('id',$id)->delete();
		model('shop')->plog('treasure.category.delete', '删除积分商城分类 ID: ' . $id . ' 标题: ' . $item['name'] . ' ');
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$enabled = input('enabled');
		$items = Db::name('shop_treasure_goods_category')->where('id','in',$id)->field('id,name')->select();
		foreach ($items as $item) {
			Db::name('shop_treasure_goods_category')->where('id',$item['id'])->update(array('enabled' => intval($enabled)));
			model('shop')->plog('treasure.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function categoryquery()
	{
		$keyword = trim(input('keyword'));
		$condition = ' 1 and enabled=1 ';

		if (!empty($keyword)) {
			$condition .= ' AND `name` LIKE "%' . $keyword . '%"';
		}

		$list = Db::name('shop_treasure_goods_category')->where($condition)->order('displayorder','desc')->select();

		if (!empty($list)) {
			$list = set_medias($list, array('thumb', 'bannerimg'));
		}
		$this->assign(['lsit'=>$list]);
		return $this->fetch('treasure/category/query');
	}

	public function goods()
	{
		$psize = 20;
		$keyword = input('keyword');
		$status = input('status');
		$category = input('category');
		$psize = 15;
		$condition = ' 1 ';
		$tab = (!empty(input('tab')) ? trim(input('tab')) : 'sell');
		if (empty($tab) || ($tab == 'sell')) {
			$condition .= ' and status = 1 and deleted = 0 ';
		} else if ($tab == 'sellout') {
			$condition .= ' and status = 1 and deleted = 0 ';
		} else if ($tab == 'warehouse') {
			$condition .= ' and status = 0 and deleted = 0 ';
		} else {
			if ($tab == 'recycle') {
				$condition .= ' and deleted = 1 ';
			}
		}
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and title like "%' . $keyword . '%"';
		}

		if ($status != '') {
			$condition .= ' AND status = ' . $status;
		}

		if ($category != '') {
			$condition .= ' AND category = ' . $category;
		}
		
		$list = Db::name('shop_treasure_goods')->where($condition)->order('sort desc,id asc')->paginate($psize);
		$pager = $list->render();
		$categorys = Db::name('shop_treasure_goods_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'categorys'=>$categorys,'type'=>$type,'status'=>$status,'category'=>$category,'keyword'=>$keyword]);
		return $this->fetch('treasure/goods/index');
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
		$item = Db::name('shop_treasure_goods')
			->alias('g')
			->join('shop_treasure_goods_category c','g.category = c.id','left')
			->field('g.*,c.name')
			->where('g.id',$id)
			->find();
		if( !empty($item["thumb"]) ) {
			$piclist = iunserializer($item["thumb_url"]);
		}
		if(!empty($item)) {
			$period = Db::name('shop_treasure_goods_period')->where('goodsid=' . $item['id'] . ' and periods = ' . $item['periods'])->find();
			$item['code'] = $period['code'];
			$a = unserialize($item['automatic']);
			$item['select_automatic'] = $a['select'];
			$item['automatic'] = $a['num'];
		}
		if (Request::instance()->isPost()) {
			$data = $_POST['goods'];
			
			if (empty($data['title'])) {
				show_json(0, '请填写商品标题');
			}
			if (empty($_POST["thumbs"])) {
				show_json(0, "请上传图片");
			}
			if (empty($data['price'])) {
				show_json(0, '请填写商品价格');
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
			if($data['select_automatic'] == 2 || $data['select_automatic'] == 3){
				$data['automatic'] = array(
					'select' => $data['select_automatic'],
					'num' => $data['automatic']
				);
				$data['automatic'] = serialize($data['automatic']);
			}elseif($data['select_automatic'] == 1){
				$data['automatic'] = '';
			}else{
				unset($data['automatic']);
			}
			unset($data['select_automatic']);
			if (!empty($id)) {
				if(input('code')){
					$code = intval(input('code'));
					$maxcode = 1000000+intval(input('maxcode'));
					if($code >= 1000001 && $code <= $maxcode){
						Db::name('shop_treasure_goods_period')->where('goodsid = ' . $id . ' and periods = ' . input('periods'))->update(array('code'=>$code));
					}
				}

				$datam['category'] = $data['category'];
				$datam['next_init_money'] = $data['init_money'];
				$datam['sort'] = $data['sort'];
				$datam['title'] = $data['title'];
				$datam['jiexiaotime'] = $data['jiexiaotime'];
				$datam['maxperiods'] = $data['maxperiods'];
				$datam['content'] = $data['content'];
				$datam['picarr'] = $data['picarr'];
				$datam['maxnum'] = $data['maxnum'];
				$datam['automatic'] = $data['automatic'];
				$datam['is_alert'] = $data['is_alert'];
				$datam['is_alone'] = $data['is_alone'];
				$datam['aloneprice'] = $data['aloneprice'];
				$goods_update = Db::name('shop_treasure_goods')->where('id',$id)->update($datam);

				if (!$goods_update) {
					show_json(0, '商品编辑失败！');
				}
				Db::name('shop_treasure_goods_period')->where('goodsid = ' . $id . ' and status = 1')->update(array('sort'=>$datam['sort']));//修改进行期商品
				model('shop')->plog('treasure.goods.edit', '编辑拍卖商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			} else {
				$data['createtime'] = time();
				$data['periods'] = 0;
				$id = Db::name('shop_treasure_goods')->insertGetId($data);

				if (!$id) {
					show_json(0, '商品添加失败！');
				}
				//第一期夺宝码计算
				$period_number = model('treasure')->create_newgoods($id);
				model('shop')->plog('treasure.goods.add', '添加拍卖商品 ID: ' . $id . '  <br/>商品名称: ' . $data['title']);
			}

			show_json(1, array('url' => url('admin/treasure/goodsedit', array('id' => $id))));
		}
		$category = array();
		$category = Db::name('shop_treasure_goods_category')->order('displayorder','desc')->select();
		$this->assign(['item'=>$item,'category'=>$category,'piclist'=>$piclist]);
		return $this->fetch('treasure/goods/post');
	}

	public function goodsdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_treasure_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_treasure_goods')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('treasure.goods.delete', '删除积分商城商品 ID: ' . $item['id'] . '  <br/>商品名称: ' . $item['title'] . ' ');
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
		$items = Db::name('shop_treasure_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_treasure_goods')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('treasure.goods.edit', '修改积分商城商品 ' . $item['id'] . ' <br /> 状态: ' . ($status == 0 ? '下架' : '上架'));
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsproperty()
	{
		$id = intval(input('id'));
		$type = trim(input('type'));
		$value = intval(input('value'));

		if (in_array($type, array('status', 'displayorder', 'title'))) {
			Db::name('shop_treasure_goods')->where('id',$id)->update(array($type => $value));
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
			model('shop')->plog('treasure.goods.edit', '修改积分商城商品' . $typestr . '状态   ID: ' . $id . ' ' . $statusstr . ' ');
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
		} else {
			if( $type == 2 ) 
			{
				$condition .= " and status = 1 and dealmid = 0 and starttime > " . time();
			} else {
				if( $type == 3 ) 
				{
					$condition .= " and status = 1 and dealmid <> 0 ";
				} else {
					if( $type == 4 ) 
					{
						$condition .= " and status = 1 and dealmid = 0 and endtime < " . time();
					} else {
						if( $type == 5 ) 
						{
							$condition .= " and status != 1 ";
						} 
					}
				}
			}
		}
		$total = Db::name('shop_treasure_goods')->where($condition)->count();
		echo json_encode($total);
	}


}