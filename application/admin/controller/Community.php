<?php
/**
 * 小区
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Db;
use think\Request;
use think\Validate;
class Community extends Base
{
	public function index()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$status = input('status/d');
		$keyword = trim(input('keyword'));
		if ($status != '') {
			$condition .= ' and status=' . $status;
		}
		if (!empty($keyword)) {
			$condition .= ' and communityname like "%' . $keyword . '%"';
		}
		$condition .= ' and deleted=' . 0 . ' ';
		$list = Db::name('community')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
    	return $this->fetch('');
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
		if (Request::instance()->isPost()) {
			$map = input('map/a');
			if(!empty($map) && !empty($map['lat']) && !empty($map['lng']))
			{
				$lat = $map['lat'];
				$lng = $map['lng'];
			}
			$data = array('communityname' => trim(input('communityname')), 'logo' => trim(input('logo')), 'status' => intval(input('status')), 'tel' => trim(input('tel')), 'mobile' => trim(input('mobile')), 'province' => input('province/s','云南省'), 'city' => input('city/s'), 'area' => input('area/s'), 'provincecode' => input('chose_province_code'), 'citycode' => input('chose_city_code'), 'areacode' => input('chose_area_code'), 'lat' => $lat, 'lng' => $lng, 'address' => input('address'), 'desc' => trim(input('desc')), 'status' => input('status/d'));
			$validate = \think\Loader::Validate('Community');
			if(!$validate->check($data)){
    		   show_json(0,$validate->getError(), array('url' => url('admin/community/edit', array('id' => $id))));
			}
			if (!empty($id)) {
				Db::name('community')->where('id',$id)->update($data);
				model('shop')->plog('community.edit', '修改小区 ID: ' . $id);
			}
			else {
				$id = Db::name('community')->insertGetId($data);
				model('shop')->plog('community.add', '添加小区 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/community/edit', array('id' => $id))));
		}
		$area_set = model('util')->get_area_config_set();
		$new_area = 1;
		$address_street = 0;
		$item = Db::name('community')->where('id',$id)->find();
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('community/post');
	}

	public function delete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}
		$items = Db::name('community')->where('id','in',$id)->field('id,communityname')->select();

		foreach ($items as $item) {
			Db::name('community')->where('id',$item['id'])->update(['deleted'=>'1']);
			model('shop')->plog('community.delete', '删除小区 ID: ' . $item['id'] . ' 标题: ' . $item['communityname'] . ' ');
		}
		show_json(1);
	}

	public function status()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community')->where('id','in',$id)->field('id,communityname')->select();

		foreach ($items as $item) {
			Db::name('community')->where('id',$item['id'])->setField('status',input('status'));
			model('shop')->plog('community.edit', ('修改小区状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['communityname'] . '<br/>状态: ' . input('status')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

    public function banner()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$enabled = input('enabled/d');
		$keyword = trim(input('keyword'));
		if ($enabled != '') {
			$condition .= ' and enabled=' . $enabled;
		}

		if (!empty($keyword)) {
			$condition .= ' and bannername like "%' . $keyword . '%"';
		}

		$list = Db::name('community_banner')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
    	return $this->fetch('community/banner/index');
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
			$data = array('bannername' => trim(input('bannername')), 'link' => trim(input('link')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'thumb' => trim(input('thumb')));
			if (!empty($id)) {
				Db::name('community_banner')->where('id',$id)->update($data);
				model('shop')->plog('community.banner.edit', '修改幻灯片 ID: ' . $id);
			}
			else {
				$id = Db::name('community_banner')->insertGetId($data);
				model('shop')->plog('community.banner.add', '添加幻灯片 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/community/banneredit', array('id' => $id))));
		}
		
		$this->assign(['item'=>$item]);
		return $this->fetch('community/banner/post');
	}

	public function bannerdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('community_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('community.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1);
	}

	public function bannerdisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item =  Db::name('community_banner')->where('id',$id)->field('id,bannername')->find();

		if (!empty($item)) {
			Db::name('community_banner')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('community.banner.edit', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
		}
		show_json(1);
	}

	public function bannerenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('community_banner')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->plog('community.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function notice()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$status = input('status');
		$keyword = input('keyword');
		if ($status != '') {
			$condition .= ' and status=' . $status;
		}

		if (!empty($keyword)) {
			$condition .= ' and title like "%' . $keyword . '%"';
		}

		$list = Db::name('community_notice')->where($condition)->order('displayorder','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
    	return $this->fetch('community/notice/index');
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

		if (Request::instance()->isPost()) {
			$data = array('displayorder' => input('displayorder/d',0), 'title' => trim(input('title/s')), 'subtitle' => trim(input('subtitle/s')), 'thumb' => trim(input('thumb')), 'content' => model('common')->html_images(input('content')), 'cate' => input('cate/d',0), 'status' => input('status/d',0), 'createtime' => time());

			if (!empty($id)) {
				Db::name('community_notice')->where('id',$id)->update($data);
				model('shop')->plog('community.notice.edit', '修改公告 ID: ' . $id);
			}
			else {
				$id = Db::name('community_notice')->insertGetId($data);
				model('shop')->plog('community.notice.add', '修改公告 ID: ' . $id);
				model('notice')->push();
			}
			show_json(1, array('url' => url('admin/community/notice')));
		}

		$notice = Db::name('community_notice')->where('id',$id)->find();
		$this->assign(['notice'=>$notice]);
		return $this->fetch('community/notice/post');
	}

	public function noticepush()
	{
		$id = input('id/d');
		$notice = Db::name('community_notice')->where('id',$id)->find();
		model('notice')->sendCommunityNotice($notice['id']);
		show_json(1, array('url' => referer()));
	}

	public function noticedisplayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('community_notice')->where('id',$id)->field('id,title')->find();

		if (!empty($item)) {
			Db::name('community_notice')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('community.notice.edit', '修改公告排序 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function noticedelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('community_notice')->where('id',$item['id'])->delete();
			model('shop')->plog('community.notice.delete', '删除公告 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function noticestatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_notice')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('community_notice')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('shop.notice.edit', ('修改公告状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function building()
	{
		$psize = 20;
		$condition = ' 1 ';
		$communityid = input('communityid/d');
		$status = input('status');
		$keyword = input('keyword/s');
		if(!empty($communityid))
		{
			$condition .= ' and c.communityid = ' . $communityid;
		}
		if($status != '')
		{
			$condition .= ' and c.status = ' . $status;
		}
		if (!empty($keyword)) {
			$condition .= ' and c.buildingname like "%' . $keyword . '%"';
		}
		$condition .= ' and c.deleted = ' . 0 . ' ' ;
		$condition .= ' and com.deleted = ' . 0 . ' ' ;
		$list = Db::name('community_building')
			->alias('c')
			->join('community com','com.id = c.communityid','left')
			->where($condition)
			->order('c.createtime','desc')
			->field('c.*,com.communityname')
			->paginate($psize);
		$pager = $list->render();
		$community_data = Db::name('community')->where('status',1)->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'community_data'=>$community_data,'communityid'=>$communityid,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('community/building/index');
	}

	public function buildingadd()
	{
		$buildingdata = $this->buildingpost();
		return $buildingdata;
	}

	public function buildingedit()
	{
		$buildingdata = $this->buildingpost();
		return $buildingdata;
	}

	protected function buildingpost()
	{
		$id = input('id/d');	
		$item = Db::name('community_building')->where('id',$id)->find();

		if (Request::instance()->isPost()) {
			$data = array('communityid' => input('communityid/d',0), 'buildingname' => trim(input('buildingname/s')), 'desc' => input('desc/s'), 'status' => input('status/d',0), 'createtime' => time());

			if (!empty($id)) {
				Db::name('community_building')->where('id',$id)->update($data);
				model('shop')->plog('community.building.edit', '修改楼栋 ID: ' . $id);
			}
			else {
				$id = Db::name('community_building')->insertGetId($data);
				model('shop')->plog('community.building.add', '修改楼栋 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/community/building')));
		}
		$community_data = Db::name('community')->where('status',1)->select();
		$this->assign(['item'=>$item,'community_data'=>$community_data]);
		return $this->fetch('community/building/post');
	}

	public function buildingdelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_building')->where('id','in',$id)->field('id,buildingname')->select();

		foreach ($items as $item) {
			Db::name('community_building')->where('id',$item['id'])->update(['deleted'=>'1']);
			model('shop')->plog('community.building.delete', '删除楼栋 ID: ' . $item['id'] . ' 标题: ' . $item['buildingname'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function buildingstatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_building')->where('id','in',$id)->field('id,buildingname')->select();

		foreach ($items as $item) {
			Db::name('community_building')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('shop.building.edit', ('修改楼栋状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['buildingname'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function house()
	{
		$psize = 20;
		$condition = ' 1 ';
		$communityid = input('communityid/d');
		$status = input('status');
		$keyword = input('keyword/s');
		if(!empty($communityid))
		{
			$condition .= ' and c.communityid = ' . $communityid;
		}
		if($status != '')
		{
			$condition .= ' and c.status = ' . $status;
		}
		if (!empty($keyword)) {
			$condition .= ' and c.housename like "%' . $keyword . '%"';
		}
		$list = Db::name('community_house')
			->alias('c')
			->join('community com','com.id = c.communityid','left')
			->join('community_building cb','cb.id = c.buildingid','left')
			->where($condition)
			->order('c.createtime','desc')
			->field('c.*,com.communityname,cb.buildingname')
			->paginate($psize);
		$pager = $list->render();
		$community_data = Db::name('community')->where('status',1)->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'community_data'=>$community_data,'communityid'=>$communityid,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('community/house/index');
	}

	public function houseadd()
	{
		$housedata = $this->housepost();
		return $housedata;
	}

	public function houseedit()
	{
		$housedata = $this->housepost();
		return $housedata;
	}

	protected function housepost()
	{
		$id = input('id/d');	
		$item = Db::name('community_house')->where('id',$id)->find();

		if (Request::instance()->isPost()) {
			$data = array('communityid' => input('communityid/d',0), 'buildingid' => input('buildingid/d',0), 'housename' => trim(input('housename/s')), 'ownername' => trim(input('ownername/s','')), 'mobile' => trim(input('mobile/s','')), 'desc' => input('desc/s'), 'status' => input('status/d',0), 'createtime' => time());

			if (!empty($id)) {
				if(empty($item['housesn']))
				{
					$data['housesn'] = date('ymd') . $id;
				}
				Db::name('community_house')->where('id',$id)->update($data);
				model('shop')->plog('community.house.edit', '修改房户 ID: ' . $id);
			} else {
				$id = Db::name('community_house')->insertGetId($data);
				$housesn = date('ymd') . $id;
				Db::name('community_house')->where('id',$id)->setField('housesn',$housesn);
				model('shop')->plog('community.house.add', '修改房户 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/community/house')));
		}
		$community_data = Db::name('community')->where('status',1)->select();
		$this->assign(['item'=>$item,'community_data'=>$community_data]);
		return $this->fetch('community/house/post');
	}

	public function housedelete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_house')->where('id','in',$id)->field('id,housename')->select();

		foreach ($items as $item) {
			Db::name('community_house')->where('id',$item['id'])->delete();
			model('shop')->plog('community.house.delete', '删除房户 ID: ' . $item['id'] . ' 标题: ' . $item['housename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function housestatus()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_house')->where('id','in',$id)->field('id,housename')->select();

		foreach ($items as $item) {
			Db::name('community_house')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('shop.house.edit', ('修改房户状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['housename'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function houseorder()
	{
		$houseid = input('houseid/d');		
		if(empty($houseid)) {
			$this->error('请选择户号',referer());
		}

		if(!empty(input('times'))) {
			$timestart=input('times/s');
			$timeend=$timestart+1;
			$timestart1= strtotime($timestart.'-01-01');
			$timeend= strtotime($timeend.'-01-01');
		} elseif (!empty(input('timestart'))) {
			$timestart=input('timestart/s');
			$timeend=$timestart+1;
			$timestart1= strtotime($timestart.'-01-01');
			$timeend= strtotime($timeend.'-01-01');
		} else {
			$timestart=date('Y',time());
			$timeend=$timestart+1;
			$timestart1= strtotime($timestart.'-01-01');
			$timeend= strtotime($timeend.'-01-01');
		}
		$condition = ' timestart <' .$timeend . ' and timestart >= ' . $timestart1 . ' and houseid = ' . $houseid;
		$list = Db::name('community_house_water_order')->where($condition)->order('timestart desc')->select();
		$list2 = Db::name('community_house_property_order')->where($condition)->order('timestart desc')->find();
		$list3 = Db::name('community_house_electricity_order')->where($condition)->order('timestart desc')->select();
		$params = array();
		if(!empty($list2)) {
			$params = iunserializer($list2['othermoney']);
		}
		
		$year_array = array();
		$i = date('Y',time());
		$i1 = $i - 5;
		$i2 = $i + 20;
		while ($i1 < $i2) {
			$year_array[$i1] = $i1;
			++$i1;
		}

		$month=array(1,2,3,4,5,6,7,8,9,10,11,12);
		if (Request::instance()->isPost()) {
			show_json(1, array('url' => url('admin/community/houseorder',array('timestart'=>input('times/s'),'houseid'=>$houseid))));
		}
		$this->assign(['list3'=>$list3,'list2'=>$list2,'list'=>$list,'month'=>$month,'timestart' => $timestart,'houseid'=>$houseid,'year_array'=>$year_array,'params'=>$params]);
		return $this->fetch('community/house/order/index');
	}

	public function houseorderedit()
	{	
		$m = input('m/d');
		$Y = input('nian/d');
		$fei = input('fei');
		$houseid= input('houseid/d');
		if(empty($m)) {
			$m = 1;
		}
		$sjstart=strtotime($Y."-".$m."-01");
		$sjend=strtotime($Y."-".($m+1)."-01");
		if($m==12){
			$Y=$Y+1;
			$sjend=strtotime($Y.'-01-01');
		}
		$condition = ' timestart <' .$sjend . ' and timeend >= ' . $sjstart . ' and houseid = ' . $houseid;

		$item = array();
		if($fei == 'electricity') {
			$item = Db::name('community_house_electricity_order')->where($condition)->find();
			if (Request::instance()->isPost()) {
				$data=array('electricity_o'=>input('electricity_o'),'electricity'=>input('electricity'),'timestart'=>$sjstart,'timeend'=>$sjend,'desc'=>input('desc'),'status'=>input('status'));
				if(empty(input('id'))) {
					$data['electricitymoney']=$data['electricity']*$data['electricity_o'];
					$data['houseid']=$houseid;

					if($data['status']==1) {
						$data['paymenttime']=time();
					} elseif ($data['status']==2) {
						$data['completiontime']=time();
					}
					Db::name('community_house_electricity_order')->insertGetId($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				} else {
					if($data['status']==2) {
						$data['paymenttime']=time();
					} elseif($data['status']==1) {
						$data['completiontime']=time();
					}
					$data['electricitymoney']=$data['electricity']*$data['electricity_o'];
					$id=input('id');
					Db::name('community_house_electricity_order')->where('id',$id)->update($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				}
			}
		} elseif($fei == 'water') {
			$item = Db::name('community_house_water_order')->where($condition)->find();
			if (Request::instance()->isPost()) {
				$data=array('water_m'=>input('water_m'),'water'=>input('water'),'timestart'=>$sjstart,'timeend'=>$sjend,'desc'=>input('desc'),'status'=>input('status'));
				if(empty(input('id'))) {
					$data['watermoney']=$data['water']*$data['water_m'];
					$data['houseid']=$houseid;

					if($data['status']==1) {
						$data['paymenttime']=time();
					} elseif($data['status']==2) {
						$data['completiontime']=time();
					}
					Db::name('community_house_water_order')->insertGetId($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				} else {
					if($data['status']==2) {
						$data['paymenttime']=time();
					} elseif($data['status']==1) {
						$data['completiontime']=time();
					}
					$data['watermoney']=$data['water']*$data['water_m'];
					$id=input('id');
					Db::name('community_house_water_order')->where('id',$id)->update($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				}
			}
		} else {
			$item = Db::name('community_house_property_order')->where($condition)->find();
			if (Request::instance()->isPost()) {
				$param_ids = $_POST['param_id'];
				$param_titles = $_POST['param_title'];
				$param_values = $_POST['param_value'];
				$param_displayorders = $_POST['param_displayorder'];
				$len = count($param_ids);
				$paramids = array();
				$k = 0;
				$omoney = 0;
				while ($k < $len) {
					$param_id = '';
					$omoney += $param_values[$k];
					$a = array('title' => $param_titles[$k], 'value' => $param_values[$k], 'displayorder' => $k, 'id' => $param_ids[$k]);
					$params[] = $a;
					++$k;
				}

				$othermoney = iserializer($params);
				$sjstart=strtotime($Y."-01-01");
				$sjend=strtotime($Y."-12-31");
				$data=array('basicmoney'=>input('basicmoney'),'othermoney'=>$othermoney,'timestart'=>$sjstart,'timeend'=>$sjend,'desc'=>input('desc/s',''),'status'=>input('status'));
				if(empty(input('id'))) {
					$data['propertymoney']=$data['basicmoney'] + $omoney;
					$data['houseid']=$houseid;

					if($data['status']==1) {
						$data['paymenttime']=time();
					}
					Db::name('community_house_property_order')->insertGetId($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				} else {
					if($data['status']==1) {
						$data['paymenttime']=time();
					}
					$data['propertymoney']=$data['basicmoney'] + $omoney;
					$id=input('id');
					Db::name('community_house_property_order')->where('id',$id)->update($data);
					show_json(1, array('url' => url('admin/community/houseorder',array('houseid'=>$houseid,'timestart'=>$Y))));
				}
			}
		}

		$this->assign(['item'=>$item,'sjstart'=>$sjstart,'fei'=>$fei,'sjend'=>$sjend,'y'=>$Y,'m'=>$m,'houseid'=>$houseid]);
		return $this->fetch('community/house/order/orderedit');
	}

	public function get_building()
	{
        $communityid = input('route.communityid/d'); // 父id 

        $building = Db::name('community_building')->where('communityid', $communityid)->where('status', 1)->field('id,buildingname')->select();
        $building_html = "<option value='0'>请选择楼栋</option>";   
        foreach($building as $k => $v)
        {
            $building_html .= "<option value='{$v['id']}'>{$v['buildingname']}</option>";        
        }
        return array('building_html'=>$building_html);
    }

    public function tpl()
	{
		$tpl = trim(input('tpl'));
		if ($tpl == 'param') {
			$tag = random(32);
			$this->assign(['tag'=>$tag]);
			return $this->fetch('community/housing/param');
		}
	}

    public function housing()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$communityid = input('communityid/d');
		$status = input('status');
		$keyword = input('keyword/s');
		if(!empty($communityid))
		{
			$condition .= ' and c.communityid = ' . $communityid;
		}
		if($status != '')
		{
			$condition .= ' and c.status = ' . $status;
		}
		if (!empty($keyword)) {
			$condition .= ' and c.title like "%' . $keyword . '%"';
		}
		$condition .= ' and c.deleted = ' . '0';
		$list = Db::name('community_housing')
			->alias('c')
			->join('community com','com.id = c.communityid','left')
			->join('community_building cb','cb.id = c.buildingid','left')
			->where($condition)
			->order('c.createtime','desc')
			->field('c.*,com.communityname,cb.buildingname')
			->paginate($psize);
		$pager = $list->render();
		$community_data = Db::name('community')->where('status',1)->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'community_data'=>$community_data,'communityid'=>$communityid,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('community/housing/index');
    }

    public function housingadd()
    {
		$housingdata = $this->housingpost();
		return $housingdata;
    }

    public function housingedit()
    {
		$housingdata = $this->housingpost();
		return $housingdata;
    }

    protected function housingpost(){
       	$id = input('id/d');	
		$item = Db::name('community_housing')->where('id',$id)->find();
		if (Request::instance()->isPost()) {
			$data = array('title' => trim(input('title/s')),'displayorder' => input('displayorder/d'), 'communityid' => input('communityid/d'), 'buildingid' => input('buildingid/d'), 'housenum' => input('housenum/s'), 'room' => input('room/d'), 'hall' => input('hall/d'), 'toilet' => trim(input('toilet/d')), 'orientations' => trim(input('orientations/s')), 'totalfloor' => input('totalfloor/d'), 'floor' => input('floor/d'), 'contactsidentity' => input('contactsidentity/d'), 'agencyfee' => input('agencyfee/f'), 'rent' => input('rent/f'), 'contacts' => trim(input('contacts/s')), 'contactssex' => trim(input('contactssex/d')),'contactsnumber' => trim(input('contactsnumber/s')), 'acreage' => input('acreage/d'),'detail' => model('common')->html_images(input('detail')), 'decorating' => input('decorating/d'), 'hasparking' => input('hasparking/d'),'housingtype' =>input('housingtype/d'), 'haselevator' => input('haselevator/d'), 'rentstyle' => trim(input('rentstyle/d')), 'ishome' => trim(input('ishome/d')),'address'=>trim(input('address/s')),'area'=>trim(input('area/s')), 'housingtype'=>input('housingtype/d'),'status' => trim(input('status/d')));

			$thumb_url = input('thumbs/a');
			$data['thumb'] = $thumb_url[0];
			$data['thumb_url'] = iserializer($thumb_url);
			$rentdetail = $_POST['rentdetail'];
			$rentdetailarr = array(array('id' => 'water', 'title' => '水费', 'value' => $rentdetail['water'] ? $rentdetail['water'] : 0),array('id' => 'electricity', 'title' => '电费', 'value' => $rentdetail['electricity'] ? $rentdetail['electricity'] : 0),array('id' => 'gas', 'title' => '燃气费', 'value' => $rentdetail['gas'] ? $rentdetail['gas'] : 0),array('id' => 'elevator', 'title' => '电梯费', 'value' => $rentdetail['elevator'] ? $rentdetail['elevator'] : 0),array('id' => 'property', 'title' => '物业费', 'value' => $rentdetail['property'] ? $rentdetail['property'] : 0),array('id' => 'wifi', 'title' => '宽带费', 'value' => $rentdetail['wifi'] ? $rentdetail['wifi'] : 0),array('id' => 'car', 'title' => '停车费', 'value' => $rentdetail['car'] ? $rentdetail['car'] : 0));
			$data['rentdetail'] = iserializer($rentdetailarr);
			$allocation = $_POST['allocation'];
			$allocationarr = array(array('id' => 'tv', 'title' => '电视', 'value' => $allocation['tv'] ? $allocation['tv'] : 0),array('id' => 'laundry', 'title' => '洗衣机', 'value' => $allocation['laundry'] ? $allocation['laundry'] : 0),array('id' => 'wifi1', 'title' => '宽带', 'value' => $allocation['wifi1'] ? $allocation['wifi1'] : 0),array('id' => 'condittioner', 'title' => '空调', 'value' => $allocation['condittioner'] ? $allocation['condittioner'] : 0),array('id' => 'heater', 'title' => '热水器', 'value' => $allocation['heater'] ? $allocation['heater'] : 0),array('id' => 'heating', 'title' => '暖气', 'value' => $allocation['heating'] ? $allocation['heating'] : 0),array('id' => 'tgas', 'title' => '天然气', 'value' => $allocation['tgas'] ? $allocation['tgas'] : 0),array('id' => 'sofa', 'title' => '沙发', 'value' => $allocation['sofa'] ? $allocation['sofa'] : 0),array('id' => 'bed', 'title' => '床', 'value' => $allocation['bed'] ? $allocation['bed'] : 0),array('id' => 'wardrobe', 'title' => '衣柜', 'value' => $allocation['wardrobe'] ? $allocation['wardrobe'] : 0),array('id' => 'stove', 'title' => '灶台', 'value' => $allocation['stove'] ? $allocation['stove'] : 0),array('id' => 'refrigerator', 'title' => '冰箱', 'value' => $allocation['refrigerator'] ? $allocation['refrigerator'] : 0));
			$data['allocation'] = iserializer($allocationarr);
			if (!empty($id)) {
				Db::name('community_housing')->where('id',$id)->update($data);
			}
			else {
				$id = Db::name('community_housing')->insertGetId($data);
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
				$a = array('title' => $param_titles[$k], 'value' => $param_values[$k], 'displayorder' => $k, 'housingid' => $id);

				if (!(is_numeric($get_param_id))) {
					$param_id = Db::name('community_housing_param')->insertGetId($a);
				}
				 else {
				 	Db::name('community_housing_param')->where('id',$get_param_id)->update($a);
					$param_id = $get_param_id;
				}

				$paramids[] = $param_id;
				++$k;
			}

			if (0 < count($paramids)) {
				Db::name('community_housing_param')->where('id','not in',implode(',', $paramids))->where('housingid',$id)->delete();
			}
			else {
			 	Db::name('community_housing_param')->where('housingid',$id)->delete();
			}
			
			//告诉页面操作成功
			show_json(1,array('url'=>url('admin/community/housingedit',array('id'=>$id))));
		}
		$rentdetail = array(array('id' => 'water', 'title' => '水费', 'value' => 0),array('id' => 'electricity', 'title' => '电费', 'value' => 0),array('id' => 'gas', 'title' => '燃气费', 'value' => 0),array('id' => 'elevator', 'title' => '电梯费', 'value' => 0),array('id' => 'property', 'title' => '物业费', 'value' => 0),array('id' => 'wifi', 'title' => '宽带费', 'value' => 0),array('id' => 'car', 'title' => '停车费', 'value' => 0));
		$allocation = array(array('id' => 'tv', 'title' => '电视', 'value' => 0),array('id' => 'laundry', 'title' => '洗衣机', 'value' => 0),array('id' => 'wifi1', 'title' => '宽带', 'value' => 0),array('id' => 'condittioner', 'title' => '空调', 'value' => 0),array('id' => 'heater', 'title' => '热水器', 'value' => 0),array('id' => 'heating', 'title' => '暖气', 'value' => 0),array('id' => 'tgas', 'title' => '天然气', 'value' => 0),array('id' => 'sofa', 'title' => '沙发', 'value' => 0),array('id' => 'bed', 'title' => '床', 'value' => 0),array('id' => 'wardrobe', 'title' => '衣柜', 'value' => 0),array('id' => 'stove', 'title' => '灶台', 'value' => 0),array('id' => 'refrigerator', 'title' => '冰箱', 'value' => 0));
		if(!empty($item))
		{
			$piclist = iunserializer($item['thumb_url']);			
			$params = Db::name('community_housing_param')->where('housingid',$item['id'])->select();
			if(!empty($item['rentdetail']))
			{
				$rentdetail = iunserializer($item['rentdetail']);
			}
			if(!empty($item['allocation']))
			{
				$allocation = iunserializer($item['allocation']);
			}	
		}
	    
	    $community_data = Db::name('community')->where('status',1)->select();
		$level_level = array();
        $i = 1;
        while ($i < 50) {
            $level_level[$i] = $i;
            ++$i;
        }
		$level_level2 = array();
        $j = 1;
        while ($j < 101) {
            $level_level2[$j] = $j;
            ++$j;
        }
		$this->assign(['item'=>$item,'community_data'=>$community_data,'piclist'=>$piclist,'params'=>$params,'allocation'=>$allocation,'rentdetail'=>$rentdetail,'level_level'=>$level_level,'level_level2'=>$level_level2]);
    	return $this->fetch('community/housing/post');
    }

    public function housingdelete()
	{
		$id = input('id/d');
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_housing')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('community_housing')->where('id',$item['id'])->update(['deleted'=>'1']);
			model('shop')->plog('community.house.delete', '删除租房房源 ID: ' . $item['id'] . ' 标题: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}	

    public function housingstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('community_housing')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('community_housing')->where('id',$item['id'])->setField('status',input('status'));
			model('shop')->plog('community.house.status', ('修改租房房源状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['title'] . '<br/>状态: ' . input('status')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function repair()
	{
		$psize = 20;
		$condition = ' 1 ';
		$status = input('status/d');
		$keyword = input('keyword/s');
		if($status != '')
		{
			$condition .= ' and r.status = ' . $status;
		}
		if (!empty($keyword)) {
			$condition .= ' AND ( r.description LIKE "%' . $keyword . '%" or m.nickname LIKE "%' . $keyword . '%" )';
		}
		$list = Db::name('community_apply_repair')
			->alias('r')
			->join('community_house h','h.id = r.houseid','left')
			->join('community c','c.id = h.communityid','left')
			->join('community_building b','b.id = h.buildingid','left')
			->join('member m','r.mid = m.id','left')
			->where($condition)
			->field('r.*,c.communityname,b.buildingname,m.nickname,h.housesn,h.housename,h.ownername')
			->order('createtime','desc')
			->paginate(20);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('community/repair/index');
	}

	public function repairdetail()
	{	
		$id = input('id/d');
		$item = Db::name('community_apply_repair')
			->alias('r')
			->join('community_house h','h.id = r.houseid','left')
			->join('community c','c.id = h.communityid','left')
			->join('community_building b','b.id = h.buildingid','left')
			->join('member m','r.mid = m.id','left')
			->where('r.id',$id)
			->field('r.*,h.housesn,h.housename,c.communityname,b.buildingname,m.avatar,m.nickname')
			->find();
		$piclist = array();
		if (!(empty($item['thumb_url']))) {
			$piclist = iunserializer($item['thumb_url']);
		}

		if (Request::instance()->isPost()) {
			$status = input('status');

			Db::name('community_apply_repair')->where('id',$id)->setField('status',$status);
			model('notice')->sendCommunityRepair($id);
			show_json(1, array('url' => url('admin/community/repairdetail',array('id'=>$id))));
		}

		$this->assign(['item'=>$item,'piclist'=>$piclist]);
		return $this->fetch('community/repair/detail');
	}

	public function repairop()
	{
		$id = input('id/d');
		$item = Db::name('community_apply_repair')->where('id',$id)->find();
		if(empty($item)) {
			show_json(0);
		}
		$ops = input('ops/s');
		if(empty($ops) || !in_array($ops, array('make','finish','close'))) {
			show_json(0);
		}
		if($ops == 'make') {
			if ($item['status'] != 1) {
				show_json(0, '操作失败');
			}
			Db::name('community_apply_repair')->where('id',$id)->update(array('status'=>2,'maketime'=>time()));
			model('shop')->plog('community.repair.repairop', '预约处理中 ID: ' . $item['id'] . ' 业务编号: ' . $item['repairsn']);
			show_json(1);
		} elseif ($ops == 'finish') {
			if ($item['status'] == -1 || $item['status'] == 3) {
				show_json(0, '操作失败');
			}
			Db::name('community_apply_repair')->where('id',$id)->update(array('status'=>3,'finishtime'=>time()));
			model('shop')->plog('community.repair.repairop', '预约处理完成 ID: ' . $item['id'] . ' 业务编号: ' . $item['repairsn']);
			show_json(1);
		} else {
			if($ops == 'close') {
				if ($item['status'] != 1) {
					show_json(0, '操作失败');
				}
				Db::name('community_apply_repair')->where('id',$id)->update(array('status'=>-1,'canceltime'=>time()));
				model('shop')->plog('community.repair.repairop', '预约处理关闭 ID: ' . $item['id'] . ' 业务编号: ' . $item['repairsn']);
				show_json(1);
			}
		}
		show_json(1);
	}

	public function payment()
	{
		$psize = 20;
		$condition = ' p.status > 0 ';
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		$keyword = input('keyword/s');
		if (!empty($keyword)) {
			$condition .= ' and r.description like "%' . $keyword . '%"';' AND ( r.description LIKE "%' . $keyword . '%" or m.nickname LIKE "%' . $keyword . '%" )';
		}
		$list = Db::name('community_apply_payment')
			->alias('p')
			->join('member m','m.id = p.mid','left')
			->where($condition)
			->order('p.createtime','desc')
			->field('p.*,m.nickname,m.mobile')
			->paginate($psize);
		foreach ($list as $key => $val) {
			$orderids = array_unique(array_filter(explode(",", $val['orderids'])));
			$orders = array();
			if($val['type'] == 'water') {
				$orders = Db::name('community_house_water_order')->where('id','in',$orderids)->select();
			} elseif ($val['type'] == 'electricity') {
				$orders = Db::name('community_house_electricity_order')->where('id','in',$orderids)->select();
			} else {
				if($val['type'] == 'property') {
					$orders = Db::name('community_house_property_order')->where('id','in',$orderids)->select();
					$othermoneys = iunserializer($orders['othermoney']);
					$othermoney = 0;
					if(!empty($othermoneys)) {
						foreach ($othermoneys as &$row) {
							$othermoney += $row['value'];
						}
						unset($row);
					}
					$orders['othermoney'] = $othermoney;
					$orders[] = $orders;
				}
			}
			$val['orders'] = $orders;
			$data = array();
    		$data = $val;
    		$list->offsetSet($key,$data);
		}
		unset($val);
		$pager = $list->render();
		$paytype = array('wechat' => '微信支付', 'alipay' => '支付宝支付', 'other' => '其他支付');
		$this->assign(['list'=>$list, 'pager'=>$pager, 'keyword'=>$keyword,'paytype'=>$paytype,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('community/payment/index');
	}

	public function paymentdetail()
	{
		$id = input('id/d');
		$item = Db::name('community_apply_payment')->where('id',$id)->find();
		return $this->fetch('community/payment/detail');
	}

	public function paymentops()
	{
		$ops = input('op/s');
		if(empty($ops) || !in_array($ops, array('pay','close','paycancel','finish','remarksaler'))) {
			show_json(0);
		}
		$id = input('id/d');
		$item = Db::name('community_apply_payment')->where('id',$id)->find();
		if(empty($item)) {
			show_json(0);
		}
		if($ops == 'pay') {
			if (1 < $item['status']) {
				show_json(0, '订单已付款，不需重复付款！');
			}

			Db::name('community_apply_payment')->where('id',$item['id'])->update(array('status' => 1, 'paytype' => 11, 'paytime' => time()));
			model('shop')->plog('order.paymentops.pay', '缴费订单确认付款 ID: ' . $item['id'] . ' 订单号: ' . $item['applysn']);
			$orderids = array_unique(array_filter(explode(",", $item['orderids'])));
			foreach ($orderids as $val) {
				if($item['type'] == 'water') {
					Db::name('community_house_water_order')->where('id',$val)->setField('status',1);
				} elseif($item['type'] == 'electricity') {
					Db::name('community_house_electricity_order')->where('id',$val)->setField('status',1);
				} else {
					if($item['type'] == 'electricity') {
						Db::name('community_house_property_order')->where('id',$val)->setField('status',1);
					}
				}
			}
			model('notice')->sendCommunityPayment($id);
			show_json(1);
		} elseif($ops == 'close') {
			if ($item['status'] == -1) {
				show_json(0, '订单已关闭，无需重复关闭！');
			} else {
				if (1 <= $item['status']) {
					show_json(0, '订单已付款，不能关闭！');
				}
			}

			if (Request::instance()->isPost()) {
				$time = time();
				Db::name('community_apply_payment')->where('id',$item['id'])->update(array('status' => -1, 'canceltime' => $time, 'remarkclose' => input('remark')));
				model('shop')->plog('community.paymentops.close', '缴费订单关闭 ID: ' . $item['id'] . ' 订单号: ' . $item['applysn']);
				model('notice')->sendCommunityPayment($id);
				show_json(1);
			}
			$this->assign(['item'=>$item]);
			echo $this->fetch('community/payment/close');
		} elseif($ops == 'paycancel') {
			if ($item['status'] != 1) {
				show_json(0, '订单未付款，不需取消！');
			}

			if (Request::instance()->isPost()) {
				Db::name('community_apply_payment')->where('id',$item['id'])->update(array('status' => 0, 'cancelpaytime' => time()));
				model('shop')->plog('community.paymentops.paycancel', '缴费订单取消付款 ID: ' . $item['id'] . ' 订单号: ' . $item['applysn']);

				show_json(1);
			}
		} elseif($ops == 'finish') {
			Db::name('community_apply_payment')->where('id',$item['id'])->update(array('status' => 2, 'finishtime' => time()));

			model('shop')->plog('community.paymentops.finish', '缴费订单完成 ID: ' . $item['id'] . ' 订单号: ' . $item['applysn']);

			model('notice')->sendCommunityPayment($id);
			show_json(1);
		} elseif($ops == 'remarksaler') {
			if (Request::instance()->isPost()) {
				Db::name('community_apply_payment')->where('id',$item['id'])->setField('adminremark',input('remark'));
				model('shop')->plog('community.paymentops.remarksaler', '缴费订单备注 ID: ' . $item['id'] . ' 订单号: ' . $item['ordersn'] . ' 备注内容: ' . input('remark'));
				show_json(1);
			}
			$this->assign(['item'=>$item]);
			echo $this->fetch('community/payment/remarksaler');
		}
	}

	public function set()
	{
		$data = model('common')->getSysset('community');
		if (Request::instance()->isPost()) {
			$data = ((is_array(input('data/a')) ? input('data/a') : array()));
			$data['commissioncharge'] = trim($data['commissioncharge']);
			model('common')->updateSysset(array('community' => $data));
			model('shop')->plog('sysset.shop.edit', '修改系统设置-小区设置');
			show_json(1);
		}
		$this->assign('data',$data);
		return $this->fetch('community/set/index');
	}

}