<?php
/**
 * apiv1 小区
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Community extends Base
{
	protected static $token;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        self::$token = $this->request->header('token','');

        if(!empty(self::$token))
        {
            $this->mid = Db::name('member')->where('token', self::$token)->value('id');
        }
    }

	/**
	 * 小区首页
	 * @param 
	 * @return  [array]    $data  [小区首页数据-幻灯、通知公告、租房房源推荐]
	 **/
	public function index()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$banner = $notice = $housing = array();
		$banner = Db::name('community_banner')->where('enabled',1)->order('displayorder','desc')->field('displayorder,enabled',true)->limit(3)->select();
		foreach ($banner as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
		}
		unset($val);
		$notice = Db::name('community_notice')->where('status',1)->order('createtime','desc')->field('id,title,cate')->limit(2)->select();
		foreach ($notice as &$val) {
			if($val['cate'] == 0)
			{
				$val['catename'] = '公告';
			}
			elseif($val['cate'] == 1)
			{
				$val['catename'] = '通知';
			}
			unset($val['cate']);
		}
		unset($val);
		$housing = Db::name('community_housing')->field('id,title,thumb,hall,room,toilet,acreage')->where('status',1)->where('deleted',0)->where('ishome',1)->order('displayorder','desc')->order('clickcount','desc')->limit(6)->select();
		foreach ($housing as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			$val['subtitle'] = '';
			if(!empty($val['room']))
			{
				$val['subtitle'] .= $val['room'] . '室';
			}
			if(!empty($val['hall']))
			{
				$val['subtitle'] .= $val['hall'] . '厅';
			}
			if(!empty($val['toilet']))
			{
				$val['subtitle'] .= $val['toilet'] . '卫  ';
			}
			if(!empty($val['acreage']))
			{
				$val['subtitle'] .= $val['acreage'] . '㎡';
			}
			unset($val['hall'],$val['room'],$val['toilet'],$val['acreage']);
		}
		unset($val);
		$this->result(1,'success',array('banner'=>$banner,'notice'=>$notice,'housing'=>$housing));
	}

	/**
	 * 小区列表
	 * @param 
	 * @return  [array]    $settlesRes  [根据小区名称第一个汉字的首字母正序排序]
	 **/
	public function community()
	{
		$list = Db::name('community')->where('status',1)->field('id,communityname')->select(); 
        foreach ($list as &$val) {
        	$val['name'] = msubstr($val['communityname'],0,1);
        }
        unset($val);
        //根据小区名称第一个汉字的首字母正序排序  
        $settlesRes = array();  
        $settlesRes = groupByInitials($list,'name'); 
        foreach ($settlesRes as $key => $val) {
        	$settlesResarr[] = array('name'=>$key,'list'=>$val);
        }
       $this->result(1,'success',$settlesResarr);
	}

	/**
	 * 根据小区ID获取楼栋单元
	 * @param [int]    $communityid
	 * @return  [array]    $list  []
	 **/
	public function building()
	{
		$communityid = input('communityid/d');
		if(empty($communityid))
		{
			$this->result(0,'缺少必传参数');
		}
		$list = Db::name('community_building')->where('status',1)->where('communityid',$communityid)->field('id,buildingname')->select();
		$this->result(1,'success',$list);
	}

	/**
	 * 房号-根据小区ID和楼栋ID获取
	 * @param [int]    $communityid
	 * @param [int]    $buildingid
	 * @return  [array]    $list  []
	 **/
	public function house()
	{
		$communityid = input('communityid/d');
		$buildingid = input('buildingid/d');
		if(empty($buildingid))
		{
			$this->result(0,'缺少必传参数');
		}
		if(empty($communityid))
		{
			$communityid = Db::name('community_building')->where('id',$buildingid)->value('communityid');
		}
		$list = Db::name('community_house')->where('status',1)->where('communityid',$communityid)->where('buildingid',$buildingid)->field('id,housename')->select();
		$this->result(1,'success',$list);
	}

	/**
	 * 户号-根据房号id获取
	 * @param [int]    $houseid
	 * @return  [array]    $list  []
	 **/
	public function housesn()
	{
		$houseid = input('houseid/d');
		if(empty($houseid))
		{
			$this->result(0,'缺少必传参数');
		}
		$smscode = input('smscode');
		$mobile = input('mobile');
		$check = model('common')->sms_captcha_verify($mobile,$smscode,'housesn');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}
		$housesn = Db::name('community_house')->where('id',$houseid)->field('id,housesn')->find();
		if(empty($housesn))
		{
			$this->result(0,'信息不存在或已被删除');
		}
		$this->result(1,'success',$housesn);
	}

	/**
	 * 税费账单-根据户号获取
	 * @param [string]    $housesn
	 * @return  [array]    $list  []
	 **/
	public function housewaterorder()
	{
		$housesn = input('housesn');
		if(empty($housesn))
		{
			$this->result(0,'缺少必传参数');
		}
		$mid = $this->getMemberId();
		$house = Db::name('community_house')->where('housesn',$housesn)->field('createtime,desc,status',true)->find();
		if(empty($house))
		{
			$this->result(0,'未查询到相关信息');
		}
		$house['community'] = Db::name('community')->where('id',$house['communityid'])->value('communityname');
		$house['building'] = Db::name('community_building')->where('id',$house['buildingid'])->value('buildingname');
		$list = Db::name('community_house_water_order')->where('houseid',$house['id'])->where('status',0)->field('id,water,water_m,watermoney,timestart,timeend')->select();
		if(empty($list)) {
			$this->result(0,'未查询到水费欠费账单');
		}
		$result = $house;
		$totalprice = 0;
		foreach ($list as &$val) {
			$val['time'] = date('Y.m',$val['timeend']);
			$totalprice += $val['watermoney'];
		}
		unset($val);
		$result['bill']['list'] = $list;
		$set = model('common')->getSysset('community');
		$poundage = 0;
		if(!empty($set['commissioncharge'])) {
			$commissioncharge = floatval($set['commissioncharge'] * 0.01);
			$poundage = floatval($totalprice * $commissioncharge);
			$totalprice += $poundage;
		}
		$extra = array(array('name'=>'手续费','value'=>$poundage));
		$result['bill']['extra'] = $extra;
		$result['bill']['totalprice'] = $totalprice;
		$this->result(1,'success',$result);
	}

	/**
	 * 税费账单-根据户号获取
	 * @param [string]    $housesn
	 * @return  [array]    $list  []
	 **/
	public function houseelectricityorder()
	{
		$housesn = input('housesn');
		if(empty($housesn))
		{
			$this->result(0,'缺少必传参数');
		}
		$mid = $this->getMemberId();
		$house = Db::name('community_house')->where('housesn',$housesn)->field('createtime,desc,status',true)->find();
		if(empty($house))
		{
			$this->result(0,'未查询到相关信息');
		}
		$house['community'] = Db::name('community')->where('id',$house['communityid'])->value('communityname');
		$house['building'] = Db::name('community_building')->where('id',$house['buildingid'])->value('buildingname');
		$list = Db::name('community_house_electricity_order')->where('houseid',$house['id'])->where('status',0)->field('id,electricity,electricity_o,electricitymoney,timestart,timeend')->select();
		if(empty($list)) {
			$this->result(0,'未查询到电费欠费账单');
		}
		$result = $house;
		$totalprice = 0;
		foreach ($list as &$val) {
			$val['time'] = date('Y.m',$val['timeend']);
			$totalprice += $val['electricitymoney'];
		}
		unset($val);
		$result['bill']['list'] = $list;
		$set = model('common')->getSysset('community');
		$poundage = 0;
		if(!empty($set['commissioncharge'])) {
			$commissioncharge = floatval($set['commissioncharge'] * 0.01);
			$poundage = floatval($totalprice * $commissioncharge);
			$totalprice += $poundage;
		}
		$extra = array(array('name'=>'手续费','value'=>$poundage));
		$result['bill']['extra'] = $extra;
		$result['bill']['totalprice'] = $totalprice;
		$this->result(1,'success',$result);
	}

	/**
	 * 税费账单-根据户号获取
	 * @param [string]    $housesn
	 * @return  [array]    $list  []
	 **/
	public function housepropertyorder()
	{
		$housesn = input('housesn');
		if(empty($housesn))
		{
			$this->result(0,'缺少必传参数');
		}
		$mid = $this->getMemberId();
		$house = Db::name('community_house')->where('housesn',$housesn)->field('createtime,desc,status',true)->find();
		if(empty($house))
		{
			$this->result(0,'未查询到相关信息');
		}
		$house['community'] = Db::name('community')->where('id',$house['communityid'])->value('communityname');
		$house['building'] = Db::name('community_building')->where('id',$house['buildingid'])->value('buildingname');
		$bill = Db::name('community_house_property_order')->where('houseid',$house['id'])->where('timestart','<',time())->where('timeend','>',time())->where('status',0)->field('id,propertymoney,basicmoney,othermoney,timestart,timeend')->find();
		if(empty($bill)) {
			$this->result(0,'未查询到物业费欠费账单');
		}
		$result = $house;
		$totalprice = 0;
		$basicmoney[0] = array('title'=>'基础费用', 'value' => $bill['basicmoney']);
		$othermoney = array_values(iunserializer($bill['othermoney']));
		$othermoney = array_merge($basicmoney,$othermoney);
		$bill['fees'] = $othermoney;
		unset($bill['othermoney']);
		$totalprice = $bill['propertymoney'];
		$result['bill']['fee'] = $bill;
		// $set = model('common')->getSysset('community');
		// $poundage = 0;
		// if(!empty($set['commissioncharge'])) {
		// 	$commissioncharge = floatval($set['commissioncharge'] * 0.01);
		// 	$poundage = floatval($totalprice * $commissioncharge);
		// 	$totalprice += $poundage;
		// }
		// $extra = array(array('name'=>'手续费','value'=>$poundage));
		$result['bill']['extra'] = $extra;
		$result['bill']['totalprice'] = $totalprice;
		$this->result(1,'success',$result);
	}

	/**
	 * 小区公告
	 * @param [int] 
	 * @return  [array]    $list  []
	 **/
	public function notice()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$list = Db::name('community_notice')->where('status',1)->order('createtime','desc')->order('displayorder','desc')->field('id,title,subtitle,thumb,createtime,cate')->page($page,$pagesize)->select();
		foreach ($list as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			if($val['cate'] == 0)
			{
				$val['catename'] = '公告';
			}
			elseif($val['cate'] == 1)
			{
				$val['catename'] = '通知';
			}
			unset($val['cate']);
		}
		unset($val);
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 小区租房房源-列表
	 * @param [int] 
	 * @return  [array]    $list  []
	 **/
	public function housinglist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$keyword = input('keyword/s');
		$provincecode = input('provincecode');
		$citycode = input('citycode');
		$areacode = input('areacode');
		$lat = input('lat');
		$lng = input('lng');
		$hall = input('hall');
		$room = input('room');
		$toilet = input('toilet');
		$pricestart = input('pricestart');
		$priceend = input('priceend');
		$condition = ' h.status = 1 and h.deleted = 0 ';
		if(!empty($lat) && !empty($lng))
		{
			$map = returnSquarePoint($lat, $lng, 50);
		}
		if(!empty($provincecode))
		{
			$condition .= ' and c.provincecode = ' . $provincecode; 
		}
		if(!empty($citycode))
		{
			$condition .= ' and c.citycode = ' . $citycode; 
		}
		if(!empty($areacode))
		{
			$condition .= ' and c.areacode = ' . $areacode; 
		}
		if(!empty($hall))
		{
			$condition .= ' and h.hall = ' . $hall; 
		}
		if(!empty($room))
		{
			$condition .= ' and h.room = ' . $room; 
		}
		if(!empty($toilet))
		{
			$condition .= ' and h.toilet = ' . $toilet; 
		}
		if(!empty($pricestart))
		{
			$condition .= ' and h.rent >= ' . $pricestart; 
		}
		if(!empty($priceend))
		{
			$condition .= ' and h.rent <= ' . $priceend; 
		}
		if(!empty($keyword))
		{
			$condition .= ' and h.title like "%' . $keyword . '%"';
		}
		$list = Db::name('community_housing')
			->alias('h')
			->join('community c','c.id = h.communityid')
			->where($condition)
			->where($map)
			->order('h.displayorder','desc')
			->order('h.clickcount','desc')
			->order('h.createtime','desc')
			->field('h.id,h.title,h.thumb,h.acreage,h.hall,h.room,h.toilet,h.orientations,h.contactsidentity,h.rent,h.rentstyle,h.decorating,h.housingtype,h.area,c.communityname')
			->page($page,$pagesize)
			->select();
		foreach ($list as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			$val['subtitle'] = '';
			if(!empty($val['room']))
			{
				$val['subtitle'] .= $val['room'] . '室';
			}
			if(!empty($val['hall']))
			{
				$val['subtitle'] .= $val['hall'] . '厅';
			}
			if(!empty($val['toilet']))
			{
				$val['subtitle'] .= $val['toilet'] . '卫 ';
			}
			if(!empty($val['acreage']))
			{
				$val['subtitle'] .= $val['acreage'] . '㎡ ';
			}

			if($val['contactsidentity'] == 1)
			{
				$val['contactsidentitys'] == '房东';
			}
			elseif ($val['contactsidentity'] == 2) {
				$val['contactsidentitys'] == '转租';
			}
			elseif ($val['contactsidentity'] == 3) {
				$val['contactsidentitys'] == '经纪人';
			}

			$housingtype = '';
			if($val['housingtype'] == 1)
			{
				$housingtype = '整租';
			}
			elseif($val['housingtype'] == 2)
			{
				$housingtype = '合租';
			}
			elseif($val['housingtype'] == 3)
			{
				$housingtype = '短租';
			}
			else
			{
				if($val['housingtype'] == 4)
				{
					$housingtype = '二手房';
				}
			}

			if($val['rentstyle'] == 1)
			{
				$val['rentstyles'] = '押一付一';
			}
			elseif($val['rentstyle'] == 2)
			{
				$val['rentstyles'] = '押一付三';
			}
			elseif($val['rentstyle'] == 3)
			{
				$val['rentstyles'] = '押一付半年';
			}
			else
			{
				if($val['rentstyle'] == 4)
				{
					$val['rentstyles'] = '押一付一年';
				}
			}
			if($val['decorating'] == 1)
			{
				$decorat = '精装';
				$val['subtitle'] .= $decorat;
			}
			elseif($val['decorating'] == 2)
			{
				$decorat = '普通装修';
				$val['subtitle'] .= $decorat;
			}
			else
			{
				if($val['decorating'] == 3)
				{
					$decorat = '毛坯';
					$val['subtitle'] .= $decorat;
				}
			}
			$val['subtitle'] .= ' ' . $housingtype;
			unset($val['hall'],$val['room'],$val['toilet'],$val['acreage'],$val['orientations'],$val['contactsidentity'],$val['rentstyle'],$val['decorating'],$val['housingtype']);
		}
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 小区租房房源-详情
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function housingdetail()
	{
		$housingid = input('housingid/d');
		if(empty($housingid))
		{
			$this->result(0,'缺少必传参数');
		}
		$result = Db::name('community_housing')->where('id',$housingid)->where('status',1)->where('deleted',0)->field('status,deleted,ishome,clickcount,displayorder,createtime',true)->find();
		if(empty($result))
		{
			$this->result(0,'您访问的信息不存在或已被删除');
		}
		$result['communityname'] = Db::name('community')->where('id',$result['communityid'])->value('communityname');
		$result['buildingname'] = Db::name('community_building')->where('id',$result['buildingid'])->value('buildingname');
		$result['thumb'] = tomedia($result['thumb']);
		$result['thumb_url'] = set_medias(iunserializer($result['thumb_url']));
		$result['allocation'] = iunserializer($result['allocation']);
		$result['rentdetail'] = iunserializer($result['rentdetail']);

		$decorat = '';
		if($result['decorating'] == 1)
		{
			$decorat = '精装';
		}
		elseif($result['decorating'] == 2)
		{
			$decorat = '普通装修';
		}
		else
		{
			if($result['decorating'] == 3)
			{
				$decorat = '毛坯';
			}
		}
		$housingtype = '';
		if($result['housingtype'] == 1)
		{
			$housingtype = '整租';
		}
		elseif($result['housingtype'] == 2)
		{
			$housingtype = '合租';
		}
		elseif($result['housingtype'] == 3)
		{
			$housingtype = '短租';
		}
		else
		{
			if($result['housingtype'] == 4)
			{
				$housingtype = '二手房';
			}
		}
		$rentstyles = '';
		if($result['rentstyle'] == 1)
		{
			$rentstyles = '押一付一';
		}
		elseif($result['rentstyle'] == 2)
		{
			$rentstyles = '押一付三';
		}
		elseif($result['rentstyle'] == 3)
		{
			$rentstyles = '押一付半年';
		}
		else
		{
			if($result['rentstyle'] == 4)
			{
				$rentstyles = '押一付一年';
			}
		}

		$contactssex = '未知';
		if($result['contactssex'] == 0)
		{
			$contactssex = '未知';
		}
		elseif ($result['contactssex'] == 1) {
			$contactssex = '男';
		}
		else
		{
			if($result['contactssex'] == 2)
			{
				$contactssex = '女';
			}
		}

		$contactsidentity = '房东';
		if($result['contactsidentity'] == 1)
		{
			$contactsidentity = '房东';
		}
		elseif ($result['contactsidentity'] == 2) {
			$contactsidentity = '转租';
		}
		else
		{
			if($result['contactsidentity'] == 3)
			{
				$contactsidentity = '经纪人';
			}
		}

		$result['contactssex'] = $contactssex;
		$result['contactsidentity'] = $contactsidentity;
		$result['decorat'] = $decorat;
		$result['housingtype'] = $housingtype;
		$result['rentstyles'] = $rentstyles;
		$houseinfo = array(array('title'=>'装修','value'=>$decorat),array('title'=>'面积','value'=>$result['acreage'] . '㎡'),array('title'=>'楼层','value'=>$result['floor'] . '层/共' . $result['totalfloor'] . '层'),array('title'=>'朝向','value'=>$result['orientations']),array('title'=>'小区','value'=>$result['communityname']),array('title'=>'地址','value'=>$result['address']));
		$result['houseinfo'] = $houseinfo;
		$params = Db::name('community_housing_param')->where('housingid',$result['id'])->field('id,title,value')->order('displayorder','desc')->select();
		$result['params'] = $params;
		$mid = 0;
		$result['iscollect'] = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
		if(!empty($mid))
		{
			$collect_count = Db::name('community_housing_collect')->where('housingid',$result['id'])->where('mid',$mid)->field('id,deleted')->find();
			if(empty($collect_count))
			{
				$result['iscollect'] = 0;
			}
			else
			{
				if($collect_count['deleted'] == 1)
				{
					$result['iscollect'] = 0;
				}
				else
				{
					$result['iscollect'] = 1;
				}
			}
		}
		$this->result(1,'success',$result);
	}

	/**
	 * 小区租房房源-相关推荐
	 * @param [int] 
	 * @return  [array]    $list  []
	 **/
	public function housingrecom()
	{
		$housingid = input('housingid/d');
		$num = input('num/d',5);
		$condition = ' t1.status = 1 and t1.deleted = 0 ';
		if(!empty($housingid))
		{
			$housing = Db::name('community_housing')->where('id',$housingid)->field('housingtype,acreage,room')->find();
			if(!empty($housing))
			{
				$housingtype = $housing['housingtype'];
				$acreage = $housing['acreage'];
				$room = $housing['room'];
				if($housingtype != '')
				{
					$condition .= ' and housingtype = ' . $housingtype;
				}
				if(!empty($room))
				{
					$condition .= ' and room = ' . $room;
				}
				if(!empty($acreage))
				{
					$condition .= ' and acreage >= ' . ($acreage-15) . ' and acreage <= ' . ($acreage+15);
				}
			}			
		}
		$list = Db::query(
			"SELECT t1.id,t1.communityid,t1.title,t1.thumb,t1.acreage,t1.hall,t1.room,t1.toilet,t1.orientations,t1.contactsidentity,t1.rent,t1.housingtype,t1.rentstyle,t1.decorating,t1.area FROM `suliss_community_housing` AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM `suliss_community_housing`)-(SELECT MIN(id) FROM `suliss_community_housing`))) AS id) AS t2 WHERE t1.id >= t2.id AND " . $condition . " ORDER BY t1.id LIMIT " . $num
		);
		foreach ($list as &$val) {
			$val['thumb'] = tomedia($val['thumb']);
			$val['subtitle'] = '';
			if(!empty($val['room']))
			{
				$val['subtitle'] .= $val['room'] . '室';
			}
			if(!empty($val['hall']))
			{
				$val['subtitle'] .= $val['hall'] . '厅';
			}
			if(!empty($val['toilet']))
			{
				$val['subtitle'] .= $val['toilet'] . '卫 ';
			}
			if(!empty($val['acreage']))
			{
				$val['subtitle'] .= $val['acreage'] . '㎡ ';
			}

			if($val['contactsidentity'] == 1)
			{
				$val['contactsidentitys'] == '房东';
			}
			elseif ($val['contactsidentity'] == 2) {
				$val['contactsidentitys'] == '转租';
			}
			elseif ($val['contactsidentity'] == 3) {
				$val['contactsidentitys'] == '经纪人';
			}

			$housingtype = '';
			if($val['housingtype'] == 1)
			{
				$housingtype = '整租';
			}
			elseif($val['housingtype'] == 2)
			{
				$housingtype = '合租';
			}
			elseif($val['housingtype'] == 3)
			{
				$housingtype = '短租';
			}
			else
			{
				if($val['housingtype'] == 4)
				{
					$housingtype = '二手房';
				}
			}

			if($val['rentstyle'] == 1)
			{
				$val['rentstyles'] = '押一付一';
			}
			elseif($val['rentstyle'] == 2)
			{
				$val['rentstyles'] = '押一付三';
			}
			elseif($val['rentstyle'] == 3)
			{
				$val['rentstyles'] = '押一付半年';
			}
			else
			{
				if($val['rentstyle'] == 4)
				{
					$val['rentstyles'] = '押一付一年';
				}
			}
			if($val['decorating'] == 1)
			{
				$decorat = '精装';
				$val['subtitle'] .= $decorat;
			}
			elseif($val['decorating'] == 2)
			{
				$decorat = '普通装修';
				$val['subtitle'] .= $decorat;
			}
			else
			{
				if($val['decorating'] == 3)
				{
					$decorat = '毛坯';
					$val['subtitle'] .= $decorat;
				}
			}
			$val['subtitle'] .= ' ' . $housingtype;
			$community = Db::name('community')->where('id',$val['communityid'])->value('communityname');
			$val['communityname'] = $community;
			unset($val['hall'],$val['room'],$val['toilet'],$val['acreage'],$val['orientations'],$val['contactsidentity'],$val['rentstyle'],$val['decorating'],$val['housingtype'],$val['communityid']);
		}
		$this->result(1,'success',$list);
	}

	/**
	 * 小区租房房源-收藏
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function housingcollect()
	{
		$housingid = input('housingid/d');
		$mid = $this->getMemberId();
		if(empty($housingid))
		{
			$this->result(0,'缺少必传参数');
		}
		$housing = Db::name('community_housing')->where('id',$housingid)->find();
		if (empty($housing)) {
			$this->result(1,'房源未找到');
		}
		$iscollect = 1;
		$data = Db::name('community_housing_collect')->where('housingid',$housingid)->where('mid',$mid)->find();
		if (empty($data)) {
			$data = array('housingid' => $housingid, 'mid' => $mid, 'createtime' => time());
			Db::name('community_housing_collect')->insert($data);
		}
		else {
			if($data['deleted'] == 0)
			{
				$deleted = 1;
				$iscollect = 0;
			}
			else
			{
				$deleted = 0;
				$iscollect = 1;
			}
			Db::name('community_housing_collect')->where('id',$data['id'])->setField('deleted',$deleted);
		}
		$this->result(1,'success',array('iscollect'=>$iscollect));
	}

	/**
	 * 小区租房房源-收藏列表
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function collectlist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$list = array();
		$list = Db::name('community_housing_collect')
			->alias('c')
			->join('community_housing h','c.housingid = h.id','left')
			->where('c.deleted = 0 and c.mid = ' . $mid)
			->field('c.id as collectid,h.id,h.title,h.thumb,h.acreage,h.hall,h.room,h.toilet,h.orientations,h.contactsidentity,h.rent,h.housingtype,h.rentstyle,h.decorating,h.area')
			->order('id','desc')
			->page($page,$pagesize)
			->select();
		foreach ($list as &$val) {
			if(empty($val['id'])) {
				Db::name('community_housing_collect')->where('id',$val['collectid'])->setField('deleted',1);
			}
			$val['subtitle'] = '';
			if(!empty($val['room']))
			{
				$val['subtitle'] .= $val['room'] . '室';
			}
			if(!empty($val['hall']))
			{
				$val['subtitle'] .= $val['hall'] . '厅';
			}
			if(!empty($val['toilet']))
			{
				$val['subtitle'] .= $val['toilet'] . '卫 ';
			}
			if(!empty($val['acreage']))
			{
				$val['subtitle'] .= $val['acreage'] . '㎡ ';
			}

			if($val['contactsidentity'] == 1)
			{
				$val['contactsidentitys'] == '房东';
			}
			elseif ($val['contactsidentity'] == 2) {
				$val['contactsidentitys'] == '转租';
			}
			elseif ($val['contactsidentity'] == 3) {
				$val['contactsidentitys'] == '经纪人';
			}

			$housingtype = '';
			if($val['housingtype'] == 1)
			{
				$housingtype = '整租';
			}
			elseif($val['housingtype'] == 2)
			{
				$housingtype = '合租';
			}
			elseif($val['housingtype'] == 3)
			{
				$housingtype = '短租';
			}
			else
			{
				if($val['housingtype'] == 4)
				{
					$housingtype = '二手房';
				}
			}

			if($val['rentstyle'] == 1)
			{
				$val['rentstyles'] = '押一付一';
			}
			elseif($val['rentstyle'] == 2)
			{
				$val['rentstyles'] = '押一付三';
			}
			elseif($val['rentstyle'] == 3)
			{
				$val['rentstyles'] = '押一付半年';
			}
			else
			{
				if($val['rentstyle'] == 4)
				{
					$val['rentstyles'] = '押一付一年';
				}
			}
			if($val['decorating'] == 1)
			{
				$decorat = '精装';
				$val['subtitle'] .= $decorat;
			}
			elseif($val['decorating'] == 2)
			{
				$decorat = '普通装修';
				$val['subtitle'] .= $decorat;
			}
			else
			{
				if($val['decorating'] == 3)
				{
					$decorat = '毛坯';
					$val['subtitle'] .= $decorat;
				}
			}
			$val['subtitle'] .= ' ' . $housingtype;
			$collect_count = Db::name('community_housing_collect')->where('housingid',$val['id'])->count();
			$val['collect_count'] = $collect_count;
			unset($val['acreage'],$val['hall'],$val['room'],$val['toilet'],$val['orientations'],$val['contactsidentity'],$val['housingtype'],$val['rentstyle'],$val['decorating'],$val['area'],$val['rentstyles']);
		}
		unset($val);
		foreach($list as $key => $row) {
			if(empty($row['id'])) {
				unset($list[$key]);
                continue;
			}
		}
		$list = set_medias($list, 'thumb');
		$this->result(1, 'success', array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 小区服务-报修
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function repair()
	{
		$smscode = input('smscode');
		$bookingtime = input('bookingtime');
		$mobile = input('mobile');
		$housesn = input('housesn');
		$description = trim(input('description'));
		$thumbs = input('thumbs');
		$check = model('common')->sms_captcha_verify($mobile,$smscode,'repair');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}
		if(empty($housesn))
		{
			$this->result(0,'缺少必传参数');
		}
		$house = Db::name('community_house')->where('housesn',$housesn)->find();
		if(empty($house))
		{
			$this->result(0,'未找到户号相关信息');
		}
		$mid = $this->getMemberId();
		if(empty($description))
		{
			$this->result(0,'请仔细描述报修内容');
		}
		$thumb_url = '';
		if(!empty($thumbs))
		{
			$thumbs = json_decode($thumbs,true);
			$thumb_url = iserializer($thumbs);
		}
		if(empty($bookingtime))
		{
			$this->result(0,'请填写预约时间');
		}
		if($bookingtime < time() || ($bookingtime-time() < 7200))
		{
			$this->result(0,'请预约两小时后');
		}
		if(empty($mobile))
		{
			$this->result(0,'请填写联系电话');
		}
		$data = array('mid'=>$mid,'houseid'=>$house['id'],'description'=>$description,'thumb_url'=>$thumb_url,'createtime'=>time(),'bookingtime'=>$bookingtime,'mobile'=>$mobile,'status'=>1);
		$payment['repairsn'] = model('common')->createNO('community_apply_repair','repairsn','RE');
		$id = Db::name('community_apply_repair')->insertGetId($data);
		if(empty($id)) {
			$this->result(0,'操作失败，请重试');
		}
		model('notice')->sendCommunityRepair($id);
		$this->result(1,'success',array('id'=>$id));
	}

	/**
	 * 小区服务-缴费
	 * @param [int] 
	 * @return  [array]    $data  [返回唤起支付所需参数]
	 **/
	public function payment()
	{
		$type = trim(input('ordertype/s','water'));
		$housesn = input('housesn');
		$paytype = input('paytype/d',1);
		if(empty($housesn))
		{
			$this->result(0,'缺少必传参数');
		}
		$mid = $this->getMemberId();
		$house = Db::name('community_house')->where('housesn',$housesn)->field('createtime,desc,status',true)->find();
		if(empty($house)) {
			$this->result(0,'未查询到相关信息');
		}
		$set = model('common')->getSysset('community');

		if ($type == 'water') {
			$list = Db::name('community_house_water_order')->where('houseid',$house['id'])->where('status',0)->field('id,water,water_m,watermoney,timestart,timeend')->select();
			if(empty($list)) {
				$this->result(0,'未查询到水费欠费账单');
			}
			$totalprice = 0;
			$orderids = '';
			foreach ($list as &$val) {
				$orderids .= $val['id'] . ',';
				$watermoney += $val['watermoney'];
			}
			unset($val);
			$poundage = 0;
			if(!empty($set['commissioncharge'])) {
				$commissioncharge = floatval($set['commissioncharge'] * 0.01);
				$poundage = floatval($watermoney * $commissioncharge);
				$totalprice = $poundage + $watermoney;
			}
			$payment['type'] = 'water';
			$payment['price'] = $totalprice;
			$payment['watermoney'] = $watermoney;
			$payment['poundage'] = $poundage;
			$payment['orderids'] = $orderids;
		} elseif ($type == 'electricity') {
			$list = Db::name('community_house_electricity_order')->where('houseid',$house['id'])->where('status',0)->field('id,electricity,electricity_o,electricitymoney,timestart,timeend')->select();
			if(empty($list)) {
				$this->result(0,'未查询到电费欠费账单');
			}
			$totalprice = 0;
			$orderids = '';
			foreach ($list as &$val) {
				$orderids .= $val['id'] . ',';
				$electricitymoney += $val['electricitymoney'];
			}
			unset($val);
			$poundage = 0;
			if(!empty($set['commissioncharge'])) {
				$commissioncharge = floatval($set['commissioncharge'] * 0.01);
				$poundage = floatval($electricitymoney * $commissioncharge);
				$totalprice = $poundage + $electricitymoney;
			}
			
			$payment['type'] = 'electricity';
			$payment['price'] = $totalprice;
			$payment['electricitymoney'] = $electricitymoney;
			$payment['poundage'] = $poundage;
			$payment['orderids'] = $orderids;
		} else {
			if($type == 'property') {
				$bill = Db::name('community_house_property_order')->where('houseid',$house['id'])->where('timestart','<',time())->where('timeend','>',time())->where('status',0)->field('id,propertymoney,basicmoney,othermoney,timestart,timeend')->find();
				if(empty($bill)) {
					$this->result(0,'未查询到物业费欠费账单');
				}
				$totalprice = 0;
				$totalprice = $bill['propertymoney'];
				$poundage = 0;
				// if(!empty($set['commissioncharge'])) {
				// 	$commissioncharge = floatval($set['commissioncharge'] * 0.01);
				// 	$poundage = floatval($totalprice * $commissioncharge);
				// 	$totalprice += $poundage;
				// }
				$payment['type'] = 'property';
				$payment['price'] = $totalprice;
				$payment['propertymoney'] = $bill['propertymoney'];
				$payment['poundage'] = $poundage;
				$payment['orderids'] = $bill['id'];
			}
		}
		$payment['paytype'] = $paytype;
		$payment['mid'] = $mid;
		$payment['houseid'] = $house['id'];
		$payment['applysn'] = model('common')->createNO('community_apply_payment','applysn','JF');
		$payment['createtime'] = time();

		Db::startTrans();
		try{
		    $orderid = Db::name('community_apply_payment')->insertGetId($payment);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0,'操作失败');
		}
		return $this->redirect(url('apiv1/community/pay',['orderid' => $orderid]));
	}

	/**
	 * 订单支付
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function pay()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$orderid = input('orderid/d');

		$order = Db::name('community_apply_payment')->where('id',$orderid)->where('mid',$mid)->find();
		if(empty($order)) {
			$this->result(0,'订单不存在');
		}

		if (1 <= $order['status']) {
			$this->result(0,'订单已付款');
		}

		$log = Db::name('shop_core_paylog')->where('module','community')->where('tid',$order['applysn'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->result(0,'订单已付款');
		}

		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'community', 'tid' => $order['applysn'], 'fee' => $order['price'], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}

		$set = model('common')->getSysset(array('shop', 'pay'));
		$param_title = $set['shop']['name'] . '订单';
		$credit = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['credit'] == 1)) {
			$credit = array('success' => true, 'current' => $member['credit2']);
		}
		$order['price'] = floatval($order['price']);
		if (empty($order['price']) && !$credit['success']) {
			Db::name('community_apply_payment')->where('id',$orderid)->setField('status',1);
			$this->result(0,'订单已付款');
		}

		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$wechat = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['product_id'] = $orderid;

		$paytype = input('paytype/d') ? input('paytype/d') : $order['paytype'];

		if ($paytype == 1) {
			$params['user'] = $mid;
			$params['fee'] = $order['price'];
			$params['title'] = $param_title;
			if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
				$wechat = model('payment')->wechat_build($params, 123);
				if (is_error($wechat)) {
					$this->result(0,$wechat);
				}
			}
			$wechat['product_id'] = $orderid;
			$this->result(1,'success',$wechat);
		}
		$alipay = array('success' => false);
		if($paytype == 2) {
			if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
				$params = array();
				$params['tid'] = $log['tid'];
				$params['user'] = $mid;
				$params['fee'] = $order['price'];
				$params['title'] = $param_title;

				$alipay = model('payment')->alipay_build($params, 123);
				if (empty($alipay)) {
					$this->result(0,$alipay);
				}
			}
			$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$orderid));
		}
		$payinfo = array('orderid' => $orderid, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $order['price']);
		$this->result(1,'success',$payinfo);
	}


}