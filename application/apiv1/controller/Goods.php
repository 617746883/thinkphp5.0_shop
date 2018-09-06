<?php
/**
 * apiv1 商城商品
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Goods extends Base
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
	 * 获取商品一级分类
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function cateparent()
    {
    	$merchid = input('merchid/d');
    	$shopset = $this->shopset;
		$category_set = $shopset['category'];
		$category_set['advimg'] = tomedia($category_set['advimg']);
		$category = array();

		if ($category_set['level'] == -1) {
			$this->result(0,'暂时未开启分类');
		}
		$category = Db::name('shop_goods_category')->where('enabled',1)->where('level',1)->order('displayorder','desc')->select();
		if (0 < $merchid) {
			$merch_data = model('common')->getPluginset('store');
			if ($merch_data['is_openmerch']) {
				$is_openmerch = 1;
			}
			else {
				$is_openmerch = 0;
			}

			if ($is_openmerch) {
				$category = Db::name('shop_store_goods_category')->where('merchid',$merchid)->where('level',1)->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();
			}
		}
		$category = set_medias($category,'advimg');
		$this->result(1,'success',$category);
    }

    /**
	 * 获取商品子级分类
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function catechild()
    {
    	$parentid = input('parentid/d');
    	$merchid = input('merchid/d');
    	if(empty($parentid))
    	{
    		$this->result(0,'暂时未开启分类');
    	}
    	$shopset = $this->shopset;
		$category_set = $shopset['category'];
		$category_set['advimg'] = tomedia($category_set['advimg']);
		$category = array();
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		else {
			$is_openmerch = 0;
		}
		if ($category_set['level'] == -1) {
			$this->result(0,'暂时未开启分类');
		}
		if($category_set['level'] > 1)
		{
			if(0 < $merchid && $is_openmerch) {
				$category = Db::name('shop_store_goods_category')->where('merchid',$merchid)->where('enabled',1)->where('parentid',$parentid)->order('displayorder','desc')->select();
			} else {
				$category = Db::name('shop_goods_category')->where('enabled',1)->where('parentid',$parentid)->order('displayorder','desc')->select();
			}
		}
		if($category_set['level'] == 3)
		{
			foreach ($category as &$row) {
				$row['thumb'] = tomedia($row['thumb']);
				$row['advimg'] = tomedia($row['advimg']);
				if(0 < $merchid && $is_openmerch) {
					$row['children'] = Db::name('shop_store_goods_category')->where('enabled',1)->where('merchid',$merchid)->where('parentid',$row['id'])->order('displayorder','desc')->select();
				} else {
					$row['children'] = Db::name('shop_goods_category')->where('enabled',1)->where('parentid',$row['id'])->order('displayorder','desc')->select();
				}
				
				$row['children'] = set_medias($row['children'],'thumb');
				$row['children'] = set_medias($row['children'],'advimg');
			}
			unset($row);
		}
		$this->result(1,'success',$category);
    }

	/**
	 * 获取商品分类
	 * @param 
	 * @return  [array]    $list  []
	 **/
	public function category()
	{
		$merchid = input('merchid/d');
		$shopset = $this->shopset;
		$category_set = $shopset['category'];
		$category_set['advimg'] = tomedia($category_set['advimg']);
		$category = array();
		if ($category_set['level'] == -1) {
			$this->result(0,'暂时未开启分类');
		}
		$category = Db::name('shop_goods_category')->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();

		if (0 < $merchid) {
			$merch_data = model('common')->getPluginset('store');
			if ($merch_data['is_openmerch']) {
				$is_openmerch = 1;
			}
			else {
				$is_openmerch = 0;
			}

			if ($is_openmerch) {
				$category = Db::name('shop_store_goods_category')->where('merchid',$merchid)->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();
			}
		}
		$category = model('goods')->getCategoryTree($category, $category_set['level']);
		$set = model('common')->getSysset('category');
		$set['advimg'] = tomedia($set['advimg']);
		$set['outlink'] = array('id'=>0,'type'=>0,'url'=>'');
		$this->result(1,'success',array('category'=>$category,'set'=>$set));
	}

	/**
	 * 获取商品列表
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function goodslist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$isnew = input('isnew');
		$isrecommand = input('isrecommand');
		$isdiscount = input('isdiscount');
		$istime = input('istime');
		$issendfree = input('issendfree');
		$keyword = trim(input('keyword'));
		$cate = input('cate');
		$random = input('random/d',0);
		$startprice = trim(input('startprice','0'));
        $endprice = trim(input('endprice','0'));
		$order = input('order');
		$by = input('by','desc');
		if(!empty($order) && !in_array($order, array('productprice','sales','salesreal','viewcount'))) {
			$order = '';
		}
		$args = array('page' => $page, 'pagesize' => $pagesize, 'isnew' => $isnew, 'ishot' => $ishot, 'isrecommand' => trim($isrecommand), 'isdiscount' => trim($isdiscount), 'istime' => trim($istime), 'issendfree' => trim($issendfree), 'keyword' => trim($keyword), 'cate' => trim($cate), 'order' => trim($order), 'by' => trim($by), 'startprice' => $startprice, 'endprice' => $endprice);
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		else {
			$is_openmerch = 0;
		}

		if ($is_openmerch) {
			$merchid = input('merchid/d');
			$args['merchid'] = $merchid;
		}
		$mid = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
            $args['mid'] = $mid;
        }
        $list = model('goods')->getList($args);
        $this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 商品详情
	 * @param $id [int]
	 * @return  [array]    $data  [获取商品详情信息]
	 **/
	public function goodsdetail()
	{	
		$id = input('id/d',0);
		$mid = 0;
		$shopset = $this->shopset;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
        $merch_data = model('common')->getPluginset('store');
        if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		 else {
			$is_openmerch = 0;
		}
		$goods = Db::name('shop_goods')->where('id',$id)->field('id,title,subtitle,thumb,type,status,unit,content,productprice,marketprice,minprice,maxprice,sales,salesreal,thumb_url,merchid,thumb_first,labelname,cannotrefund,hasoption,dispatchtype,dispatchid,dispatchprice,province,city,deleted,quality,seven,repair,total')->find();
		if(empty($goods) || $goods['status'] != 1 || $goods['deleted'] == 1) {
			$this->result(0,'您访问的商品不存在');
		}
		if ($is_openmerch == 1) {
			$set = Db::name('shop_store')->where('id',$goods['merchid'])->find();

			if ($set['status'] != 1) {
				$is_openmerch = 0;
			}
		}
		$merchinfo = array('id'=>0,'logo'=>$shopset['shop']['logo'],'merchname'=>$shopset['shop']['name']);
		if($is_openmerch) {
			$merchinfo = Db::name('shop_store')->where('id',$goods['merchid'])->field('id,logo,merchname')->find();
		}
		$merchinfo['logo'] = tomedia($merchinfo['logo']);
		$goods['merch'] = $merchinfo;
		$threenprice = json_decode($goods['threen'], 1);
		if ((0 < $goods['ispresell']) && (((0 < $goods['presellend']) && (time() < $goods['preselltimeend'])) || ($goods['preselltimeend'] == 0))) {
			$goods['minprice'] = $goods['presellprice'];

			if ($goods['hasoption'] == 0) {
				$goods['maxprice'] = $goods['presellprice'];
			}
		}

		if ($goods['type'] == 4) {
			$intervalprice = iunserializer($goods['intervalprice']);

			if (0 < $goods['intervalfloor']) {
				$goods['intervalprice1'] = $intervalprice[0]['intervalprice'];
				$goods['intervalnum1'] = $intervalprice[0]['intervalnum'];
			}

			if (1 < $goods['intervalfloor']) {
				$goods['intervalprice2'] = $intervalprice[1]['intervalprice'];
				$goods['intervalnum2'] = $intervalprice[1]['intervalnum'];
			}

			if (2 < $goods['intervalfloor']) {
				$goods['intervalprice3'] = $intervalprice[2]['intervalprice'];
				$goods['intervalnum3'] = $intervalprice[2]['intervalnum'];
			}
		}

		$isfullback = false;
		if ($goods['isfullback']) {
			$isfullback = true;
			$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$id)->find();

			if ($goods['hasoption'] == 1) {
				$fullprice = Db::name('shop_goods_option')->where('goodsid',$id)->field('min(allfullbackprice) as minfullprice,max(allfullbackprice) as maxfullprice,min(allfullbackratio) as minfullratio,max(allfullbackratio) as maxfullratio,min(fullbackprice) as minfullbackprice,max(fullbackprice) as maxfullbackprice,min(fullbackratio) as minfullbackratio,max(fullbackratio) as maxfullbackratio,min(`day`) as minday,max(`day`) as maxday')->find();
				$fullbackgoods['minallfullbackallprice'] = $fullprice['minfullprice'];
				$fullbackgoods['maxallfullbackallprice'] = $fullprice['maxfullprice'];
				$fullbackgoods['minallfullbackallratio'] = $fullprice['minfullratio'];
				$fullbackgoods['maxallfullbackallratio'] = $fullprice['maxfullratio'];
				$fullbackgoods['minfullbackprice'] = $fullprice['minfullbackprice'];
				$fullbackgoods['maxfullbackprice'] = $fullprice['maxfullbackprice'];
				$fullbackgoods['minfullbackratio'] = $fullprice['minfullbackratio'];
				$fullbackgoods['maxfullbackratio'] = $fullprice['maxfullbackratio'];
				$fullbackgoods['fullbackratio'] = $fullprice['minfullbackratio'];
				$fullbackgoods['fullbackprice'] = $fullprice['minfullbackprice'];
				$fullbackgoods['minday'] = $fullprice['minday'];
				$fullbackgoods['maxday'] = $fullprice['maxday'];
			}
			 else {
				$fullbackgoods['maxallfullbackallprice'] = $fullbackgoods['minallfullbackallprice'];
				$fullbackgoods['maxallfullbackallratio'] = $fullbackgoods['minallfullbackallratio'];
				$fullbackgoods['minday'] = $fullbackgoods['day'];
			}
		}
		$merchid = $goods['merchid'];

		if (json_decode($goods['labelname'], true)) {
			$labelname = json_decode($goods['labelname'], true);
		}
		else {
			$labelname = unserialize($goods['labelname']);
		}

		$member = model('member')->getMember($mid);
		$showgoods = model('goods')->visit($goods, $member);

		$seckillinfo = false;
		$seckill = model('common')->getPluginset('seckill');

		if ($seckill['openseckill']) {
			$time = time();
			$seckillinfo = model('seckill')->getSeckill($goods['id'], 0, false);

			if (!(empty($seckillinfo))) {
				if (($seckillinfo['starttime'] <= $time) && ($time < $seckillinfo['endtime'])) {
					$seckillinfo['status'] = 0;
				}
				else if ($time < $seckillinfo['starttime']) {
					$seckillinfo['status'] = 1;
				}
				else {
					$seckillinfo['status'] = -1;
				}
			}
		}
		$thumbs = array_values(iunserializer($goods['thumb_url']));

		if (empty($thumbs)) {
			$thumbs = array($goods['thumb']);
		}
		unset($goods['thumb_url']);
		$labelname = iunserializer($goods['labelname']);
		if (empty($labels)) {
			$labels = array($shopset['shop']['name']);
		}
		unset($goods['labelname']);

		if (!(empty($goods['thumb_first'])) && !(empty($goods['thumb']))) {
			$thumbs = array_merge(array($goods['thumb']), $thumbs);
		}
		$thumbs = set_medias($thumbs);

		$goods['thumbs'] = $thumbs;
		$goods['thumb'] = tomedia($goods['thumb']);
		$goods['labels'] = $labels;

		$goods['label'] = array();
		$quality = $seven = $repair = '';
		if(!empty($goods['quality'])) {
			$quality = '正品保证';
			array_unshift($goods['label'], $quality);
		}
		if(!empty($goods['seven'])) {
			$seven = '7天无理由退换';
			array_unshift($goods['label'], $seven);
		}
		if(!empty($goods['repair'])) {
			$repair = '报修';
			array_unshift($goods['label'], $repair);
		}

		$goods['sales'] = $goods['sales'] + $goods['salesreal'];
		if(empty($goods['dispatchtype']) && !empty($goods['dispatchid']))
		{
			$shop_dispatch = Db::name('shop_dispatch')->where('id',$goods['dispatchid'])->find();
			$dispatch = $shop_dispatch['dispatchname'] . $shop_dispatch['firstprice'];
		}
		else {
			if($goods['dispatchprice'] == 0)
			{
				$dispatch = '包邮';
			} else {
				$dispatch = '统一邮费' . $goods['dispatchprice'];
			}			
		}
		$goods['dispatch'] = $dispatch;
		$goods['isfavorite'] = 0;
		if(!empty($mid)) {
			$favorite_count = Db::name('shop_goods_favorite')->where('goodsid',$goods['id'])->where('mid',$mid)->field('id,deleted')->find();
			if(empty($favorite_count))
			{
				$goods['isfavorite'] = 0;
			}
			else
			{
				if($favorite_count['deleted'] == 1)
				{
					$goods['isfavorite'] = 0;
				}
				else
				{
					$goods['isfavorite'] = 1;
				}
			}
		}
		model('goods')->addHistory($id,$mid);

		$specs = Db::name('shop_goods_spec')->where('goodsid',$id)->order('displayorder','asc')->select();
		$spec_titles = array();

		foreach ($specs as $key => $spec ) {
			if (2 <= $key) {
				break;
			}
			$spec_titles[] = $spec['title'];
		}

		if (0 < $goods['hasoption']) {
			$spec_titles = implode('、', $spec_titles);
		}
		 else {
			$spec_titles = '';
		}

		$params = Db::name('shop_goods_param')->where('goodsid',$id)->order('displayorder','asc')->select();
		$goods['canbuy'] = ($goods['status'] == 1) && empty($goods['deleted']);
		$goodsoption = array('hasoption'=>0);
		if (!(empty($goods['hasoption']))) {
			$goodsoption['hasoption'] = 1;
			$options = Db::name('shop_goods_option')->where('goodsid',$id)->order('displayorder','asc')->select();
			$options_stock = array_keys($options);
			if(!empty($options)) {
                $spec = array();
                $filter_spec = Db::name('shop_goods_spec')
                    ->where('goodsid',$goods['id'])
                    ->order('displayorder', 'asc')
                    ->field('id,title')
                    ->select();
                foreach ($filter_spec as &$val) {
                    $item = array();
                    $item = Db::name('shop_goods_spec_item')
                        ->where('specid',$val['id'])
                        ->where('show', 1)
                        ->order('displayorder', 'asc')
                        ->field('id,title,thumb')
                        ->select();
                    if(!empty($item) && is_array($item))
                    {
                        $item = set_medias($item,'thumb');
                    }                    
                    $val['item'] = $item;
                }
                foreach ($options as $v) {    //  赋值
                    $spec_goods_price[$v['specs']] = array('optionid'=>$v['id'],'specs'=>$v['specs'], 'stock'=>$v['stock'], 'productprice'=>$v['productprice'], 'marketprice'=>$v['marketprice'], 'costprice'=>$v['costprice'], 'weight'=>$v['weight']);
                }
                $goodsoption['filter_spec'] = $filter_spec;
                $goodsoption['spec_goods_price'] = $spec_goods_price;
			}
		}
		$goods['goodsoption'] = $goodsoption;
		$canAddCart = true;
		if (($goods['isverify'] == 2) || ($goods['type'] == 2) || ($goods['type'] == 3) || ($goods['type'] == 20) || !(empty($goods['cannotrefund'])) || !(empty($is_task_goods)) || !(empty($gifts))) {
			$canAddCart = false;
		}
		$goods['canAddCart'] = $canAddCart;
		$params = Db::name('shop_goods_param')->where('goodsid',$goods['id'])->field('title,value')->order('displayorder','asc')->select();
		// $goods['params'] = $params;
		$comments = Db::name('shop_order_comment')->where('goodsid',$goods['id'])->where('level','>=',0)->where('deleted',0)->where('checked',0)->field('nickname,headimgurl,level,content,images,createtime,isanonymous')->limit(3)->select();
		foreach ($comments as &$row ) {
			if ($row['level'] <= 1) {
				$row['desc'] = '差评';
			}
			 else if ($row['level'] >= 2 && $row['level'] <= 4) {
				$row['desc'] = '中评';
			}
			 else if ($row['level'] == 5) {
				$row['desc'] = '好评';
			}
			$row['headimgurl'] = tomedia($row['headimgurl']);
			$row['images'] = set_medias(iunserializer($row['images']));
			$row['nickname'] = $row['isanonymous'] ? '匿名' : cut_str($row['nickname'], 1, 0) . '**' . cut_str($row['nickname'], 1, -1);
		}
		unset($row);
		$goods['comments'] = $comments;
		$this->result(1,'success',$goods);
	}

	/**
	 * 商品评论
	 * @param $goodsid [int]
	 * @return  [array]    $data  []
	 **/
	public function getcomments()
	{
		$id = input('id/d');
		$percent = 100;
		$count = array('all' => Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 and level>=0 ')->count(), 'good' => Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 and level>=5 ')->count(), 'normal' => Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 and level>=2 and level<=4 ')->count(), 'bad' => Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 and level<=1 ')->count(), 'pic' =>Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 and ifnull(images,\'a:0:{}\')<>\'a:0:{}\' ')->count());
		$list = array();

		if (0 < $count['all']) {
			$percent = intval(($count['good'] / ((empty($count['all']) ? 1 : $count['all']))) * 100);
			$list = Db::name('shop_order_comment')->where('goodsid = ' . $id . ' and deleted = 0 and checked = 0 ')->order('istop desc,createtime desc')->limit(3)->select();

			foreach ($list as &$row ) {
				$row['images'] = set_medias(iunserializer($row['images']));
				$row['nickname'] = $row['isanonymous'] ? '匿名' : cut_str($row['nickname'], 1, 0) . '**' . cut_str($row['nickname'], 1, -1);
			}

			unset($row);
		}
		$this->result(1,'success',array('count' => $count, 'percent' => $percent, 'list' => $list));
	}

	/**
	 * 商品评论列表
	 * @param $goodsid [int]
	 * @return  [array]    $data  []
	 **/
	public function commentslist()
	{
		$id = input('id/d');
		$level = trim(input('level'));
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$condition = ' goodsid = ' . $id . ' and deleted = 0 and checked = 0';

		if ($level == 'good') {
			$condition .= ' and level=5';
		}
		 else if ($level == 'normal') {
			$condition .= ' and level>=2 and level<=4';
		}
		 else if ($level == 'bad') {
			$condition .= ' and level<=1';
		}
		 else if ($level == 'pic') {
			$condition .= ' and ifnull(images,\'a:0:{}\')<>\'a:0:{}\'';
		}


		$list = Db::name('shop_order_comment')->where($condition)->order('istop desc,createtime desc')->page($page,$pagesize)->select();

		foreach ($list as &$row ) {
			if ($row['level'] <= 1) {
				$row['desc'] = '差评';
			}
			 else if ($row['level'] >= 2 && $row['level'] <= 4) {
				$row['desc'] = '中评';
			}
			 else if ($row['level'] == 5) {
				$row['desc'] = '好评';
			}
			$row['headimgurl'] = tomedia($row['headimgurl']);
			$row['images'] = set_medias(iunserializer($row['images']));
			$row['reply_images'] = set_medias(iunserializer($row['reply_images']));
			$row['append_images'] = set_medias(iunserializer($row['append_images']));
			$row['append_reply_images'] = set_medias(iunserializer($row['append_reply_images']));
			$row['nickname'] = $row['isanonymous'] ? '匿名' : cut_str($row['nickname'], 1, 0) . '**' . cut_str($row['nickname'], 1, -1);
		}

		unset($row);
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
		show_json(1, array('list' => $list, 'total' => $total, 'pagesize' => $psize));
	}

	/**
	 * 推荐商品列表
	 * @param $goodsid [int]
	 * @return  [array]    $data  []
	 **/
	public function recommendlist()
	{
		$goodsid = input('goodsid/d');
		$num = input('num/d',3);
		$shopset = model('common')->getSysset('shop');
		$condition = 't1.deleted = 0 and t1.status = 1 ';
		if(!empty($goodsid))
		{
			$goods = Db::name('shop_goods')->where('id',$goodsid)->field('cates,marketprice')->find();
			if(!empty($goods))
			{
				$cates = $goods['cates'];
				$marketprice = $goods['marketprice'];
				if($cates != '')
				{
					$condition .= ' and t1.cates = ' . $cates;
				}
				if(!empty($marketprice))
				{
					$condition .= ' and t1.marketprice > ' . intval($marketprice-200) . ' and t1.marketprice < ' . intval($marketprice+150);
				}
			}			
		}

		$list = Db::query("SELECT t1.id,t1.title,t1.subtitle,t1.thumb,t1.marketprice,t1.productprice,t1.minprice,t1.maxprice,t1.isdiscount,t1.isdiscount_time,t1.sales,t1.salesreal,t1.total,t1.description,t1.`type`,t1.ispresell,t1.merchid,t1.labelname,t1.quality,t1.seven,t1.repair FROM " . tablename('shop_goods') . " AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM " . tablename('shop_goods') . ")-(SELECT MIN(id) FROM " . tablename('shop_goods') . "))) AS id) AS t2 WHERE t1.id >= t2.id AND " . $condition . " ORDER BY t1.id desc LIMIT " . $num
		);
		foreach ($list as $lk => $lv ) {
			if ($lv['type'] == 3) { 
				$vData = Db::name('shop_virtual_type')->where('id',intval($lv['virtual']))->find();

				if ($vData['recycled'] == 1) {
					array_splice($list, $lk, 1);
				}
			}
		}
		foreach ($list as &$val) {
			$val['labelname'] = iunserializer($val['labelname']);
			if(empty($val['labelname']))
			{
				$val['labelname'] = array();
			}
			$merchinfo = array('id'=>0,'logo'=>$shopset['logo'],'merchname'=>$shopset['name']);
			if(!empty($val['merchid']))
			{
				$merchinfo = Db::name('shop_store')->where('id',$val['merchid'])->field('id,logo,merchname')->find();
			}
			$merchinfo['logo'] = tomedia($merchinfo['logo']);
			$val['merchinfo'] = $merchinfo;
			$quality = $seven = $repair = '';
			if(!empty($val['quality'])) {
				$quality = '正品保证';
				array_unshift($val['labelname'], $quality);
			}
			if(!empty($val['seven'])) {
				$seven = '7天无理由退换';
				array_unshift($val['labelname'], $seven);
			}
			if(!empty($val['repair'])) {
				$repair = '报修';
				array_unshift($val['labelname'], $repair);
			}
			array_unshift($val['labelname'], $merchinfo['merchname']);
			unset($val['quality'],$val['seven'],$val['repair']);
		}
		unset($val);
		$list = set_medias($list, 'thumb');
		$this->result(1,'success',$list);
	}

	/**
	 * 商品关注列表
	 * @param $goodsid [int]
	 * @return  [array]    $data  []
	 **/
	public function favoritelist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$merch_data = model('common')->getPluginset('store');
		$condition = ' f.mid = ' . $mid . ' and f.deleted = 0 ';
		if ($merch_data['is_openmerch']) {
			$condition = ' f.mid = ' . $mid . ' and f.deleted=0 and f.type=0';
		}

		$list = array();
		$list = Db::name('shop_goods_favorite')
			->alias('f')
			->join('shop_goods g','f.goodsid = g.id','left')
			->where($condition)
			->field('f.id as collectid,g.id,g.title,g.thumb,g.marketprice,g.productprice,g.merchid')
			->order('id','desc')
			->page($page,$pagesize)
			->select();
		$list = set_medias($list, 'thumb');
		foreach ($list as &$row) {
			if(empty($val['id'])) {
				Db::name('shop_goods_favorite')->where('id',$val['collectid'])->setField('deleted',1);
			}
			$merch_user = Db::name('shop_store')->where('id',$row['merchid'])->find();
			$shopset = $this->shopset;
			$row['merchname'] = $merch_user['merchname'] ? $merch_user['merchname'] : $shopset['shop']['name'];
			$collect_count = Db::name('shop_goods_favorite')->where('goodsid',$row['id'])->count();
			$row['collect_count'] = $collect_count;
		}
		unset($row);
		foreach($list as $key => $row) {
			if(empty($row['id'])) {
				unset($list[$key]);
                continue;
			}
		}
		$this->result(1, 'success', array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 商品关注
	 * @param $goodsid [int]
	 * @return  [array]    $data  []
	 **/
	public function favorite()
	{
		$id = input('id/d');
		$mid = $this->getMemberId();
		
		$goods = Db::name('shop_goods')->where('id',$id)->find();

		if (empty($goods)) {
			$this->result(0, '商品未找到');
		}

		$data = Db::name('shop_goods_favorite')->where('goodsid',$id)->where('mid',$mid)->field('id,deleted')->find();
		$isfavorite = 1;
		if (empty($data)) {
			$data = array('goodsid' => $id, 'mid' => $mid, 'createtime' => time());
			Db::name('shop_goods_favorite')->insert($data);
		}
		else {
			if($data['deleted'] == 0)
			{
				$deleted = 1;
				$isfavorite = 0;
			}
			else
			{
				$deleted = 0;
				$isfavorite = 1;
			}
			Db::name('shop_goods_favorite')->where('id',$data['id'])->setField('deleted',$deleted);
		}

		$this->result(1,'success',array('isfavorite'=>$isfavorite));
	}
	
	/**
	 * 删除商品关注
	 * @param $ids [array]
	 * @return  [array]    $data  []
	 **/
	public function removefavorite()
	{
		$id = input('id/d');
		if (empty($id)) {
			$this->result(0, '参数错误');
		}
		$mid = $this->getMemberId();
		Db::name('shop_goods_favorite')->where('mid',$mid)->where('id',$id)->setField('deleted',1);
		$this->result(1, 'success');
	}

	protected function merchData()
	{
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		 else {
			$is_openmerch = 0;
		}

		return array('is_openmerch' => $is_openmerch, 'merch_data' => $merch_data);
	}
		
}