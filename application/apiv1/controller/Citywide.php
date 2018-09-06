<?php
/**
 * apiv1 同城
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Citywide extends Base
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
	 * 同城生活服务首页
	 * @param 
	 * @return  [array]    $data  [首页数据-幻灯、周边门店推荐]
	 **/
	public function index()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$lat = input('lat');
		$lng = input('lng');
		$banner = array();
		$recom = array();
		$stores = array();
		if($page == 1)
		{
			$banner = Db::name('citywide_banner')->where('enabled',1)->order('displayorder','desc')->field('displayorder,enabled',true)->select();
			foreach ($banner as &$val) {
				$val['thumb'] = tomedia($val['thumb']);
			}
			unset($val);
			$recom = Db::name('citywide_life_store')->where('status',1)->order('displayorder','desc')->field('id,storename,logo,address,cate,lat,lng')->limit(3)->select();
			foreach($recom as &$val)
			{
				$val['logo'] = tomedia($val['logo']);
			}
		}
		
		$condition = ' status = 1 and deleted = 0 and isrecommand = 1 ';
		if(!empty($lat) && !empty($lng))
		{
			$map = returnSquarePoint($lat, $lng, 50);
		}
		$stores = Db::name('citywide_life_store')->where($condition)->where($map)->order('displayorder','desc')->field('id,storename,logo,address,cate,lat,lng')->page($page,$pagesize)->select();
		// dump($stores);die;
		foreach ($stores as &$val) {
			$val['logo'] = tomedia($val['logo']);
			// $stores['logo'] = $val['logo'];
			$cate = Db::name('citywide_life_store_category')->where('id',$val['cate'])->field('catename,color')->find();
			$val['catename'] = $cate['catename'];
			$val['color'] = $cate['color'];
			$val['distance'] = '>100m';
			if(!empty($lat) && !empty($lng))
			{
				$distance = getDistance($val['lat'],$val['lng'],$lat,$lng);
				$store['distance'] = '距离 ' . $distance . 'm';
			}
		}
		unset($val);
		if($page == 1)
		{
			$this->result(1,'success',array('banner'=>$banner,'recom'=>$recom,'stores'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
		}
		else
		{
			$this->result(1,'success',array('stores'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
		}
	}

	/**
	 * 同城生活服务首页
	 * @param 
	 * @return  [array]    $data  [首页数据-幻灯、周边门店推荐]
	 **/
	public function life()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$banner = array();
		$category = array();
		$stores = array();
		if($page == 1)
		{
			$banner = Db::name('citywide_life_banner')->where('enabled',1)->order('displayorder','desc')->field('displayorder,enabled',true)->select();
			foreach ($banner as &$val) {
				$val['thumb'] = tomedia($val['thumb']);
			}
			unset($val);

			$category = Db::name('citywide_life_store_category')->where('status',1)->where('isrecommand',1)->order('displayorder','desc')->field('displayorder,status,createtime,isrecommand',true)->limit(5)->select();
			foreach ($category as &$val) {
				$val['thumb'] = tomedia($val['thumb']);
			}
			unset($val);
		}
		

		$stores = Db::name('citywide_life_store')->where('status',1)->where('deleted',0)->where('isrecommand',1)->order('displayorder','desc')->field('id,storename,logo,address,cate')->page($page,$pagesize)->select();
		foreach ($stores as &$val) {
			$val['logo'] = tomedia($val['logo']);
			
			$cate = Db::name('citywide_life_store_category')->where('id',$val['cate'])->field('catename,color')->find();
			$val['catename'] = $cate['catename'];
			$val['color'] = $cate['color'];
		}
		unset($val);
		if($page == 1)
		{
			$this->result(1,'success',array('banner'=>$banner,'category'=>$category,'stores'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
		}
		else
		{
			$this->result(1,'success',array('stores'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
		}
	}

	/**
	 * 门店分类列表
	 * @param 
	 * @return  [array]    $category  [门店列表]
	 **/
	public function storecate()
	{
		$category = Db::name('citywide_life_store_category')->where('status',1)->field('id,catename')->select();
		$this->result(1,'success',$category);
	}


	/**
	 * 门店列表
	 * @param 
	 * @return  [array]    $list  [门店列表]
	 **/
	public function storelist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$keyword = input('keyword/s');
		$provincecode = input('provincecode');
		$citycode = input('citycode');
		$areacode = input('areacode');
		$cate = input('cate/d');
		$lat = input('lat');
		$lng = input('lng');
		$order = input('order');
		$by = input('by/s','desc');
		$condition = ' s.status = 1 and s.deleted = 0 ';
		if(!empty($lat) && !empty($lng))
		{
			$map = returnSquarePoint($lat, $lng, 100);
		}
		if(!empty($cate))
		{
			$condition .= ' and s.cate = ' . $cate; 
		}
		if(!empty($provincecode))
		{
			$condition .= ' and s.provincecode = ' . $provincecode; 
		}
		if(!empty($citycode))
		{
			$condition .= ' and s.citycode = ' . $citycode; 
		}
		if(!empty($areacode))
		{
			$condition .= ' and s.areacode = ' . $areacode; 
		}
		if(!empty($keyword))
		{
			$condition .= ' and s.storename like "%' . $keyword . '%"';
		}
		$orderby = 's.displayorder desc';
		if(!empty($order) && !empty($by))
		{
			$orderby = 's.'.$order . ' ' . $by;
		}
		$list = Db::name('citywide_life_store')
			->alias('s')
			->join('citywide_life_store_category cate','cate.id = s.cate','left')
			->where($condition)->field('s.id,s.storename,s.logo,s.address,s.cate,cate.catename,cate.color,s.collectcount')
			->order($orderby)
			->page($page,$pagesize)
			->select();
		$mid = 0;		
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
        foreach ($list as &$val) {
        	$val['logo'] = tomedia($val['logo']);
        	$iscollect = 0;
        	if(!empty($mid))
        	{
        		$collect_count = Db::name('citywide_life_store_collect')->where('storeid',$val['id'])->where('mid',$mid)->field('id,deleted')->find();
				if(empty($collect_count))
				{
					$iscollect = 0;
				}
				else
				{
					if($collect_count['deleted'] == 1)
					{
						$iscollect = 0;
					}
					else
					{
						$iscollect = 1;
					}
				}
        	}
        	$val['iscollect'] = $iscollect;
        }
        unset($val);
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 门店详情
	 * @param 
	 * @return  [array]    $list  [门店列表]
	 **/
	public function storedetail()
	{
		$id = input('id/d');
		$lat = input('lat');
		$lng = input('lng');
		$store = Db::name('citywide_life_store')->where('id',$id)->field('deleted,displayorder,status','true')->find();
		if(empty($store))
		{
			$this->result(0,'您访问的信息不存在或已被删除');
		}
		$mid = 0;
		$store['iscollect'] = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
		if(!empty($mid))
		{
			$collect_count = Db::name('citywide_life_store_collect')->where('storeid',$store['id'])->where('mid',$mid)->field('id,deleted')->find();
			if(empty($collect_count))
			{
				$store['iscollect'] = 0;
			}
			else
			{
				if($collect_count['deleted'] == 1)
				{
					$store['iscollect'] = 0;
				}
				else
				{
					$store['iscollect'] = 1;
				}
			}
		}
		$store['distance'] = '距离>50m';
		if(!empty($lat) && !empty($lng))
		{
			$distance = getDistance($store['lat'],$store['lng'],$lat,$lng);
			$store['distance'] = '距离 ' . $distance . 'm';
		}
		
		$store['banner'] = set_medias(iunserializer($store['banner']));
		Db::name('citywide_life_store')->where('id',$id)->setInc('clickcount');
		$this->result(1,'success',$store);
	}

	/**
	 * 附近门店-收藏
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function storecollect()
	{
		$storeid = input('storeid/d');
		$mid = $this->getMemberId();
		if(empty($storeid))
		{
			$this->result(0,'缺少必传参数');
		}
		$store = Db::name('citywide_life_store')->where('id',$storeid)->find();
		if (empty($store)) {
			$this->result(1,'门店未找到');
		}
		$iscollect = 1;
		$data = Db::name('citywide_life_store_collect')->where('storeid',$storeid)->where('mid',$mid)->find();
		if (empty($data)) {
			$data = array('storeid' => $storeid, 'mid' => $mid, 'createtime' => time());
			Db::name('citywide_life_store_collect')->insert($data);
		} else {
			if($data['deleted'] == 0) {
				$deleted = 1;
				$iscollect = 0;
			} else {
				$deleted = 0;
				$iscollect = 1;
			}
			Db::name('citywide_life_store_collect')->where('id',$data['id'])->setField('deleted',$deleted);
		}
		if($iscollect) {
			Db::name('citywide_life_store')->where('id',$storeid)->setInc('collectcount');
		} else {
			if($store['collectcount'] >= 1) {
				Db::name('citywide_life_store')->where('id',$storeid)->setDec('collectcount');
			}
		}
		$this->result(1,'success',array('iscollect'=>$iscollect));
	}

	/**
	 * 附近门店-收藏列表
	 * @param [int] 
	 * @return  [array]    $data  []
	 **/
	public function collectlist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$list = array();
		$list = Db::name('citywide_life_store_collect')
			->alias('c')
			->join('citywide_life_store s','c.storeid = s.id','left')
			->join('citywide_life_store_category cate','s.cate = cate.id','left')
			->where('c.deleted = 0 and c.mid = ' . $mid)
			->field('c.id as collectid,s.id,s.storename,s.logo,s.address,cate.catename,cate.color')
			->order('id','desc')
			->page($page,$pagesize)
			->select();
		foreach ($list as &$val) {
			$collect_count = Db::name('citywide_life_store_collect')->where('storeid',$val['id'])->count();
			$val['collect_count'] = $collect_count;
			if(empty($val['id'])) {
				Db::name('citywide_life_store_collect')->where('id',$val['collectid'])->setField('deleted',1);
			}
		}
		unset($val);
		foreach($list as $key => $row) {
			if(empty($row['id'])) {
				unset($list[$key]);
                continue;
			}
		}
		$list = set_medias($list, 'logo');
		$this->result(1, 'success', array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	public function publish()
	{
		$mid = $this->getMemberId();
		$id = input('id/d',0);
		$item = Db::name('citywide_secondgoods')->where('id',$id)->where('mid',$mid)->where('deleted',0)->field('id,title,status')->find();

		$description = input('description');
		$thumbs = input('thumbs');
		$thumb = '';
		$thumb_url = '';
		if(!empty($thumbs)) {
			$thumbs = json_decode($thumbs,true);
			$thumb = $thumbs[0];
			$thumb_url = iserializer($thumbs);
		}
		
		$content = '';
		if(!empty($description)) {
			$content .= '<p>' . $description . '</p>';
		}
		if(!empty($thumbs)) {
			foreach ($thumbs as $value) {
				$content .= '<p><img src="' . $value . '" width="100%" style=""/></p>';
			}
		}

		$info = array('mid'=>$mid,'cate'=>input('cate'),'title'=>input('title'),'thumb' => $thumb,'thumb_url' => $thumb_url,'description'=>$description,'mobile'=>input('mobile'),'degree'=>input('degree'),'buytime'=>input('buytime'),'productprice'=>input('productprice'),'marketprice'=>input('marketprice'),'content'=>$content,'createtime'=>time(),'province'=>input('province'),'city'=>input('city'),'area'=>input('area'),'status'=>1,'checked'=>1);
		$validate = validate('Secondgoods');
		if(!$validate->check($info)) {
		    $this->result(0,$validate->getError());
		} 

		Db::startTrans();
		try{
			if(empty($item)) {
				$id = Db::name('citywide_secondgoods')->insertGetId($info);
			} else {
				Db::name('citywide_secondgoods')->where('id',$item['id'])->update($info);
			}		    
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}		
		$this->result(1,'success',$id);
	}

	public function secondgoodscateparent()
	{
		$cate_id = input('id');
		$category = Db::name('citywide_secondgoods_category')->where('enabled',1)->where('level',1)->select();
		$category = set_medias($category,'advimg');
		$this->result(1,'success',$category);
	}

	public function secondgoodscatechild()
	{
		$parentid = input('parentid/d');
		if(empty($parentid)) {
    		$this->result(0,'暂时未开启分类');
    	}
    	$category = Db::name('citywide_secondgoods_category')->where('enabled',1)->where('parentid',$parentid)->select();
    	$category = set_medias($category,'advimg');
    	$category = set_medias($category,'thumb');
    	foreach ($category as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			$row['advimg'] = tomedia($row['advimg']);
			$row['children'] = Db::name('citywide_secondgoods_category')->where('enabled',1)->where('parentid',$row['id'])->order('displayorder','desc')->select();
			
			$row['children'] = set_medias($row['children'],'thumb');
			$row['children'] = set_medias($row['children'],'advimg');
		}
		unset($row);
    	$this->result(1,'success',$category);
	}

	public function category()
	{
		$category = array();
		$category = Db::name('citywide_secondgoods_category')->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();

		$category = model('goods')->getCategoryTree($category, 3);
		$this->result(1,'success',array('category'=>$category));
	}

	public function secondgoodslist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$order = input('order');
		$where['status'] = 1;
		$where['deleted'] = 0;
		$where['checked'] = 0;
		if($title = input('title')) {
			$where['title'] = array('like',"%{$title}%");
		}
 		$cate = input('cate');
		if($cate) {
			$where['cate'] = array('eq',$cate);
		}
			
		$result = Db::name('citywide_secondgoods')->where($where)->order('marketprice',$order)->field('id,title,marketprice,createtime,description,productprice,thumb,cate,degree')->order('createtime','desc')->page($page,$pagesize)->select();
		foreach($result as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			$cate = Db::name('citywide_secondgoods_category')->where('id',$row['cate'])->field('name')->find();
			$row['name'] = $cate['name'];
		}
		$this->result(1,'success',array('list'=>$result,'page'=>$page,'pagesize'=>$pagesize));
	}

	public function secondgoodsdetail()
	{
		$id = input('id/d');
		$data = Db::name('citywide_secondgoods')->where('id',$id)->find();
		if(empty($data))
		{
			$this->result(0,'您访问的信息不存在或已被删除');
		}
		$data['catename'] = Db::name('citywide_secondgoods_category')->where('id',$data['cate'])->value('name');
		$data['thumb_url'] = set_medias(iunserializer($data['thumb_url']));
		$this->result(1,'success',$data);
	}

	public function mysecondgoods()
	{
		$mid = $this->getMemberId();
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$list = Db::name('citywide_secondgoods')->alias('g')->join('citywide_secondgoods_category c','g.cate = c.id','left')->where('deleted = 0 and mid = ' . $mid)->field('g.id,g.title,g.marketprice,g.createtime,g.description,g.productprice,g.thumb,g.cate,g.degree,g.status,g.checked,c.name')->page($page,$pagesize)->select();
		foreach($list as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			if ($row['checked'] == 1) {
				$statusstr = '审核中'; 
			} else {
				$statusstr = '审核通过'; 
			}
			$row['statusstr'] = $statusstr;
		}
		unset($row);
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	public function secondgoodsstatus()
	{
		$id = intval(input('id'));
		$mid = $this->getMemberId();

		$item = Db::name('citywide_secondgoods')->where('id',$id)->where('mid',$mid)->where('deleted',0)->field('id,title,status')->find();
		if(empty($item)) {
			$this->result(0,'您访问的信息不存在或已被删除');
		}
		if($item['status'] == 1) {
			$status = 0;
		} else {
			$status = 1;
		}

		Db::name('citywide_secondgoods')->where('id',$item['id'])->setField('status',$status);
		$this->result(1,'success',$status);
	}

	public function secondgoodsdelete()
	{
		$id = intval(input('id'));
		$mid = $this->getMemberId();

		$item = Db::name('citywide_secondgoods')->where('id',$id)->where('mid',$mid)->where('deleted',0)->field('id,title,status')->find();
		if(empty($item)) {
			$this->result(0,'您访问的信息不存在或已被删除');
		}

		Db::name('citywide_secondgoods')->where('id',$item['id'])->setField('deleted',1);
		$this->result(1,'success',$id);
	}

}