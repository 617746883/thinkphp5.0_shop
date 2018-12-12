<?php
/**
 * apiv1 团购
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request; 
class Groups extends Base
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

        $set = Db::name('shop_groups_set')->limit(1)->find();
        if(!empty($set['opengroups']) && $set['opengroups'] == 1)
        {
        	$this->result(0,'未开启团购');
        }
        $this->set = $set;
    }

	/**
	 * 团购首页
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function index()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$banner = array();
		$category = array();
		$goods = array();
		if($page == 1)
		{
			$banner = Db::name('shop_groups_banner')->where('enabled',1)->order('displayorder','desc')->field('enabled,displayorder',true)->select();
			$banner = set_medias($banner, 'thumb');
			$category = Db::name('shop_groups_goods_category')->where('enabled=1')->order('displayorder desc')->field('id,name,thumb')->select();
			$category = set_medias($category, 'thumb');
		}		

		$goods = Db::name('shop_groups_goods')->field('id,title,is_ladder,thumb,price,groupnum,groupsprice,teamnum,single,singleprice,isindex,goodsnum,units,description')->where('status=1 and deleted=0')->order('displayorder desc,id DESC')->page($page,$pagesize)->select();
		foreach ($goods as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			$row["fightnum"] = Db::name('shop_groups_order')->where('goodsid = ' . $row['id'] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
			$row["fightnum"] = $row["teamnum"] + $goods["fightnum"];
			$team = Db::name('shop_groups_order')->where('paytime > 0 and heads = 1 and is_team = 1 and goodsid = ' . $row['id'])->field('id,mid,teamid')->order('createtime','desc')->find();
			$teams = Db::name('shop_groups_order')->alias('o')->join('member m','o.mid = m.id','left')->where('o.teamid',$team['teamid'])->field('o.id,m.avatar')->select();
			$row['teams'] = $teams;
		}
		unset($row);
		if($page == 1) {
			$this->result(1,'success',array('banner'=>$banner,'category'=>$category,'goods'=>$goods,'page'=>$page,'pagesize'=>$pagesize));
		} else {
			$this->result(1,'success',array('goods'=>$goods,'page'=>$page,'pagesize'=>$pagesize));
		}
	}

	/**
	 * 商品详情
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function goods()
	{
		try{
			$id = input('id/d');
		    $goods = Db::name('shop_goods')
		    	->where('id',$id)->where('isgroups',1)
		    	->field('id,title,thumb,thumb_url,unit,description,content,goodssn,productsn,marketprice,total,sales,category,groupsprice,single,singleprice,goodsnum,purchaselimit,teamnum,endtime,groupnum,discount,headstype,headsmoney,headsdiscount,isdiscount,merchid,checked')
		    	->find();
		}catch(\Exception $e){
		    $this->result(0,'执行错误');
		}

		if (empty($id) || empty($goods)) {
			$this->result(0,'你访问的商品不存在或已删除!');
		}
		$goods['thumb'] = tomedia($goods['thumb']);
		if (!empty($goods['thumb_url'])) {
			$goods['thumb_url'] = array_values(set_medias(iunserializer($goods['thumb_url'])));
		}
		$goods['fightnum'] = Db::name('shop_groups_order')->where('goodsid',$item['id'])->where('deleted',0)->where('is_team',1)->where('status','>',0)->count();
		$goods['fightnum'] = $goods['teamnum'] + $goods['fightnum'];
		if (empty($goods)) {
			$this->result(0,'商品已下架或被删除!');
		}

		$groupsset = Db::name('shop_groups_set')->limit(1)->find();
		if (!empty($groupsset['discount'])) {
			if (empty($goods['discount'])) {
				$goods['headstype'] = $groupsset['headstype'];
				$goods['headsmoney'] = $groupsset['headsmoney'];
				$goods['headsdiscount'] = $groupsset['headsdiscount'];
			}

			if ($goods['groupsprice'] < $goods['headsmoney']) {
				$goods['headsmoney'] = $goods['groupsprice'];
			}
		}
		$mid = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
        $isfavorite = 0;
        if(!empty($mid))
        {
        	$favorite = Db::name('shop_goods_favorite')->where('mid',$mid)->where('goodsid',$goods['id'])->where('deleted',0)->count();
        	if($favorite)
        	{
        		$isfavorite = 1;
        	}
        }
		$goods['isfavorite'] = $isfavorite;
		$goods['merchinfo'] = array();
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		}
		 else {
			$is_openmerch = 0;
		}

		if($is_openmerch == 1 && (0 < $goods['merchid']) && ($goods['checked'] == 1))
		{
			$merch = Db::name('shop_store')->where('id',$goods['merchid'])->field('id,merchname,tel')->find();
			$goods['merchinfo'] = $merch;
		}
		else
		{
			$shop_data = model('common')->getSysset('shop');
			$goods['merchinfo'] = array('id'=>0,'merchname'=>$shop_data['name'],'tel'=>$shop_data['phone']);
		}

		$this->result(1,'success',$goods);
	}

	/**
	 * 商品-正在拼单
	 * @param $goodsid [int]
	 * @return  [array]    $list  []
	 **/
	public function teams()
	{
		$goodsid = input('goodsid/d');
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$goods = Db::name('shop_goods')->where('id',$goodsid)->where('isgroups',1)->find();
		if(empty($goods))
		{
			$this->result(0,'商品已下架或被删除!');
		}
		$condition = ' o.goodsid = ' . $goodsid . ' and o.deleted = 0 and o.heads = 1 and o.paytime > 0 and o.success = 0 ';
		$teams = array();
		$mid = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
        if(!empty($mid))
        {
        	$condition .= ' and o.mid != ' . $mid ;
        }
		$teams = Db::name('shop_groups_order')
			->alias('o')
			->join('member m','m.id=o.mid','left')
			->join('shop_goods g','g.id=o.goodsid','left')
			->where($condition)
			->field('o.paytime,o.id,o.goodsid,o.teamid,m.nickname,m.realname,m.mobile,m.avatar,g.endtime,g.groupnum')
			->order('o.createtime','desc')
			->page($page,$pagesize)
			->select();

		foreach ($teams as $key => $value) {
			$num = Db::name('shop_groups_order')->where("deleted = 0 and teamid = {$value['teamid']} and status > 0")->count();
			$value['num'] = $value['groupnum'] - $num;
			$value['residualtime'] = ($value['paytime'] + ($value['endtime'] * 60 * 60)) - time();
			$value['hour'] = intval($value['residualtime'] / 3600);
			$value['minite'] = intval(($value['residualtime'] / 60) % 60);
			$value['second'] = intval($value['residualtime'] % 60);
			$teams[$key] = $value;
		}
		$this->result(1,'success',array('list'=>$teams,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 开团-信息确认
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function openGroups()
	{
		$mid = $this->getMemberId();
		$id = input('id/d');
		if (empty($id)) {
			$this->result(0,'你访问的商品不存在或已删除!');
		}

		$goods = Db::name('shop_goods')
			->where('id',$id)->where('isgroups',1)->where('status',1)->where('deleted',0)
			->field('id,title,thumb,thumb_url,unit,description,content,goodssn,productsn,marketprice,total,sales,category,groupsprice,single,singleprice,goodsnum,purchaselimit,endtime,teamnum,groupnum,discount,headstype,headsmoney,headsdiscount,isdiscount,merchid,checked')
			->find();
		if (empty($goods)) {
			$this->result(0,'商品已下架或被删除!');
		}
		$goods['fightnum'] = Db::name('shop_groups_order')->where('goodsid',$goods['id'])->where('deleted',0)->where('is_team',1)->where('status','>',0)->count();
		$goods['fightnum'] = $goods['teamnum'] + $goods['fightnum'];
		$goods['thumb'] = tomedia($goods['thumb']);

		if (empty($goods)) {
			$this->result(0,'商品已下架或被删除!');
		}
		$this->result(1,'success',array('goods'=>$goods));
	}

	/**
	 * 参与拼团-信息确认
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function fightGroups()
	{
		$teamid = input('teamid/d');
		if (empty($teamid)) {
			$this->result(0,'该团不存在!');
		}
		$team = Db::name('shop_groups_order')->where('id',$teamid)->field('id,goodsid,mid,starttime,endtime')->find();
		if (empty($team)) {
			$this->result(0,'该团不存在!');
		}
		$goods = Db::name('shop_goods')->where('id',$team['goodsid'])->where('deleted',0)->find();
		if ($goods['stock'] <= 0) {
			$this->result(0,'您选择的商品已经下架，请浏览其他商品或联系商家!');
		}

		$orders = Db::name('shop_groups_order')->where('teamid',$teamid)->field('id,mid,teamid,heads,success,starttime,endtime')->select();
		foreach ($orders as $key => $value) {
			if ($orders && ($value['success'] == -1)) {
				$this->result(0,'该活动已过期，请浏览其他商品!');
			}

			if ($orders && ($value['success'] == 1)) {
				$this->result(0,'该活动已结束，请浏览其他商品!');
			}
		}
		$num = Db::name('shop_groups_order')->where('teamid',$teamid)->where('status','>',0)->where('goodsid',$goods['id'])->count();

		if ($num == $goods['groupnum']) {
			$this->result(0,'该活动已成功组团，请浏览其他商品!');
		}
		foreach ($orders as &$row) {
			$avatar = Db::name('member')->where('mid',$row['mid'])->field('mid,avatar,nickname')->find();
			$row['avatar'] = $avatar['avatar'];
			$row['nickname'] = $avatar['nickname'];
		}
		unset($row);
		$item['groupnum'] = $goods['groupnum'];
		$item['teamnum'] = $num;
		$this->result(1,'success',array('team'=>$team,'teams'=>$orders));
	}

	/**
	 * 团详情
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function teamdetail()
	{
		$teamid = input('teamid/d');
		$condition = '';

		if (empty($teamid)) {
			$this->result(0,'该团不存在!');
		}
		$mid = $this->getMemberId();
		$myorder = Db::name('shop_groups_order')->where('mid',$mid)->where('teamid',$teamid)->where('paytime','>',0)->find();

		$orders = Db::name('shop_groups_order')->where('teamid',$teamid)->where('paytime','>',0)->order('id','asc')->select();
		$profileall = array();

		foreach ($orders as $key => $value) {
			if ($value['groupnum'] == 1) {
				$single = 1;
			}

			$order['goodsid'] = $value['goodsid'];
			$order['groupnum'] = $value['groupnum'];
			$order['success'] = $value['success'];
			$avatar = Db::name('member')->where('mid',$value['mid'])->field('mid,avatar,nickname')->find();
			$orders[$key]['mid'] = $avatar['mid'];
			$orders[$key]['nickname'] = $avatar['nickname'];
			$orders[$key]['avatar'] = $avatar['avatar'];

			if ($orders[$key]['avatar'] == '') {
				$orders[$key]['avatar'] = '../public/static/images/user/' . mt_rand(1, 20) . '.jpg';
			}
		}

		$groupsset = Db::name('shop_groups_set')->field('description,groups_description,discount,headstype,headsmoney,headsdiscount')->limit(1)->find();
		$goods = Db::name('shop_goods')->where('id',$order['goodsid'])->find();

		if (!empty($goods['thumb_url'])) {
			$goods['thumb_url'] = array_merge(iunserializer($goods['thumb_url']));
		}

		$alltuan = Db::name('shop_groups_order')->where('teamid',$teamid)->where('status','>',0)->select();
		$item = array();

		foreach ($alltuan as $num => $all) {
			$item[$num] = $all['id'];
		}

		$n = intval($order['groupnum']) - count($alltuan);

		if ($n <= 0) {
			Db::name('shop_groups_order')->where('teamid',$teamid)->setField('success',1);
		}

		$nn = intval($order['groupnum']) - 1;
		$arr = array();
		$i = 0;

		while ($i < $n) {
			$arr[$i] = 0;
			++$i;
		}

		$tuan_first_order = Db::name('shop_groups_order')->where('teamid',$teamid)->where('heads',1)->find();
		$hours = $tuan_first_order['endtime'];
		$time = time();
		$date = date('Y-m-d H:i:s', $tuan_first_order['starttime']);
		$endtime = date('Y-m-d H:i:s', strtotime(' ' . $date . ' + ' . $hours . ' hour'));
		$date1 = date('Y-m-d H:i:s', $time);
		$lasttime2 = strtotime($endtime) - strtotime($date1);
		$tuan_first_order['endtime'] = strtotime(' ' . $date . ' + ' . $hours . ' hour');
		$set = $this->shopset;
		$this->result(1,'success',array('item'=>$item,'set'=>$set));
	}

	/**
	 * 商品检查
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function goodsCheck()
	{
		try{
			$id = input('id/d');
			$type = input('type');
			$goods = Db::name('shop_goods')->where('id',$id)->where('status',1)->where('isgroups',1)->where('deleted',0)->find();
		}catch(\Exception $e){
		    $this->result(0,'执行错误');
		}

		if (empty($goods)) {
			$this->result(0,'商品不存在!');
		}

		if ($type == 'single') {
			if (empty($goods['single'])) {
				$this->result(0,'商品不允许单购，请重新选择！');
			}
		}

		if (empty($goods['total']) || $goods['total'] <= 0) {
			$this->result(0,'商品库存为0，暂时无法购买，请浏览其他商品！');
		}
		$this->result(1,'success');
	}

	/**
	 * 订单确认
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function confirm()
	{
		$goodsid = input('goodsid/d');
		$type = input('type');
		$heads = input('heads/d');
		$teamid = input('teamid/d');
		$mid = $this->getMemberId();
		$isverify = false;
		$address = array();
		$goods = Db::name('shop_goods')->where('id',$goodsid)->where('deleted',0)->field('id,title,thumb,unit,description,goodssn,productsn,marketprice,total,sales,category,groupsprice,single,singleprice,goodsnum,purchaselimit,teamnum,endtime,groupnum,discount,headstype,headsmoney,headsdiscount,isdiscount,merchid,checked,isverify,dispatchprice,storeids')->find();

		if (empty($goods) || $goods['total'] <= 0) {
			$this->result(0,'您选择的商品已经下架，请浏览其他商品！');
		}

		$ordernum = Db::name('shop_groups_order')->where('mid',$mid)->where('status','>=',0)->where('goodsid',$goodsid)->count();
		if (!empty($goods['purchaselimit']) && ($goods['purchaselimit'] <= $ordernum)) {
			$this->result(0,'您已到达此商品购买上限，请浏览其他商品或联系商家！');
		}

		$order = Db::name('shop_groups_order')->where('goodsid',$goodsid)->where('status','>=',0)->where('success',0)->where('deleted',0)->where('is_team',1)->where('mid',$mid)->find();
		if ($order && ($order['status'] == 0)) {
			$this->result(0,'您的订单已存在，请尽快完成支付！');
		}

		if ($order && ($order['status'] == 1)) {
			$this->result(0,'您已经参与了该团，请等待拼团结束后再进行购买！');
		}

		if ($order && ($order['groupnum'] <= $ordernum)) {
			$this->result(0,'该团人数已达上限，请浏览其他商品或联系商家！');
		}

		if (!empty($teamid)) {
			$orders = Db::name('shop_groups_order')->where('teamid',$teamid)->select();

			foreach ($orders as $key => $value) {
				if ($orders && ($value['success'] == -1)) {
					$this->result(0,'该活动已过期，请浏览其他商品或联系商家！');
				}

				if ($orders && ($value['success'] == 1)) {
					$this->result(0,'该活动已结束，请浏览其他商品或联系商家！');
				}
			}

			$num = Db::name('shop_groups_order')->where('teamid',$teamid)->where('status','>',0)->where('goodsid',$goods['id'])->count();

			if ($num == $goods['groupnum']) {
				$this->result(0,'该活动已成功组团，请浏览其他商品或联系商家！');
			}
		}
		if ($type == 'groups') {
			$goodsprice = $goods['groupsprice'];
			$price = $goods['groupsprice'];
			$groupnum = intval($goods['groupnum']);
			$is_team = 1;
		} else {
			if ($type == 'single') {
				$goodsprice = $goods['singleprice'];
				$price = $goods['singleprice'];
				$groupnum = 1;
				$is_team = 0;
				$teamid = 0;
			}
		}
		$set = $this->set;
		if (!empty($set['discount']) && ($heads == 1)) {
			if (!empty($goods['discount'])) {
				if (empty($goods['headstype'])) {
				}
				else {
					if (0 < $goods['headsdiscount']) {
						$goods['headsmoney'] = $goods['groupsprice'] - price_format(($goods['groupsprice'] * $goods['headsdiscount']) / 100, 2);
					}
				}
			} else {
				if (empty($set['headstype'])) {
					$goods['headsmoney'] = $set['headsmoney'];
				}
				else {
					if (0 < $set['headsdiscount']) {
						$goods['headsmoney'] = $goods['groupsprice'] - price_format(($goods['groupsprice'] * $set['headsdiscount']) / 100, 2);
					}
				}

				$goods['headstype'] = $set['headstype'];
				$goods['headsdiscount'] = $set['headsdiscount'];
			}

			if ($goods['groupsprice'] < $goods['headsmoney']) {
				$goods['headsmoney'] = $goods['groupsprice'];
			}

			$price = $price - $goods['headsmoney'];

			if ($price < 0) {
				$price = 0;
			}
		} else {
			$goods['headsmoney'] = 0;
		}	

		if (!empty($goods['isverify'])) {
			$isverify = true;
			$goods['dispatchprice'] = 0;
			$storeids = array();
			$merchid = 0;

			if (!empty($goods['storeids'])) {
				$merchid = $goods['merchid'];
				$storeids = array_merge(explode(',', $goods['storeids']), $storeids);
			}

			if (empty($storeids)) {
				if (0 < $merchid) {
					$stores = Db::name('shop_store')->where('merchid',$merchid)->where('status',1)->select();
				}
				else {
					$stores = Db::name('shop_store')->where('status',1)->select();
				}
			}
			else if (0 < $merchid) {
				$stores = Db::name('shop_store')->where('id','in',implode(',', $storeids))->where('merchid',$merchid)->where('status',1)->select();
			}
			else {
				$stores = Db::name('shop_store')->where('id','in',implode(',', $storeids))->where('status',1)->select();
			}
		} else {
			$address = Db::name('shop_member_address')->where('mid',$mid)->where('deleted',0)->where('isdefault',1)->find();
		}
		$goods['thumb'] = tomedia($goods['thumb']);

		$creditdeduct = array('creditdeduct'=>$set['creditdeduct'],'groupsdeduct'=>$set['groupsdeduct'],'credit'=>$set['credit'],'groupsmoney'=>$set['groupsmoney']);
		$credit = array();
		if (intval($creditdeduct['creditdeduct'])) {
			if (intval($creditdeduct['groupsdeduct'])) {
				if (0 < $goods['deduct']) {
					$credit['deductprice'] = round(intval($member['credit1']) * $creditdeduct['groupsmoney'], 2);

					if ($price <= $credit['deductprice']) {
						$credit['deductprice'] = $price;
					}

					if ($goods['deduct'] <= $credit['deductprice']) {
						$credit['deductprice'] = $goods['deduct'];
					}

					$credit['credit'] = floor($credit['deductprice'] / $creditdeduct['groupsmoney']);

					if ($credit['credit'] < 1) {
						$credit['credit'] = 0;
						$credit['deductprice'] = 0;
					}

					$credit['deductprice'] = $credit['credit'] * $creditdeduct['groupsmoney'];
				}
				else {
					$credit['deductprice'] = 0;
				}
			} else {
				$sys_data = model('common')->getPluginset('sale');

				if (0 < $goods['deduct']) {
					$credit['deductprice'] = round(intval($member['credit1']) * $sys_data['money'], 2);

					if ($price <= $credit['deductprice']) {
						$credit['deductprice'] = $price;
					}

					if ($goods['deduct'] <= $credit['deductprice']) {
						$credit['deductprice'] = $goods['deduct'];
					}

					$credit['credit'] = floor($credit['deductprice'] / $sys_data['money']);

					if ($credit['credit'] < 1) {
						$credit['credit'] = 0;
						$credit['deductprice'] = 0;
					}

					$credit['deductprice'] = $credit['credit'] * $sys_data['money'];
				}
				else {
					$credit['deductprice'] = 0;
				}
			}
		}
		$this->result(1,'success',array('goods'=>$goods,'address'=>$address,'credit'=>$credit));		
	}

	/**
	 * 提交订单
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function submit()
	{
		$mid = $this->getMemberId();
		$addressid = input('addressid/d');
		$realname = input('realname');
		$mobile = input('mobile');
		$goods = '';
        if (Request::instance()->has('goods')) {
            $goods = $_POST['goods'];
        }        
        $goods = json_decode($goods,true);
        if (empty($goods) || !(is_array($goods))) 
        {
            $this->result(0, '未找到任何商品');
        }
		if($goods['isverify']) {
			$isverify = false;
			$verifycode = 'PT' . random(8, true);
			while (1) {
				$count = Db::name('shop_groups_order')->where('verifycode',$verifycode)->count();

				if ($count <= 0) {
					break;
				}

				$verifycode = 'PT' . random(8, true);
			}
			$verifynum = (!empty($goods['verifytype']) ? $verifynum = $goods['verifynum'] : 1);
		}
		if (empty($addressid) && !$isverify) {
			$this->result(0,'请选择收货地址');
		}
		if ($isverify) {
			if (empty($realname) || empty($mobile)) {
				$this->result(0,'联系人或联系电话不能为空！');
			}
		}

		if ((0 < intval($addressid)) && !$isverify) {
			$order_address = Db::name('shop_member_address')->where('id',$addressid)->where('mid',$mid)->find();

			if (empty($order_address)) {
				$this->result(0,'未找到地址');
			} else {
				if (empty($order_address['province']) || empty($order_address['city'])) {
					$this->result(0,'地址请选择省市信息');
				}
			}
		}
 		$ordersn = model('common')->createNO('shop_groups_order', 'orderno', 'PT');
 		$data = array('groupnum' => $groupnum, 'mid' => $mid, 'paytime' => 0, 'orderno' => $ordersn, 'credit' => intval(input('isdeduct')) ? input('credit') : 0, 'creditmoney' => intval(input('isdeduct')) ? input('creditmoney') : 0, 'price' => $price, 'freight' => $goods['freight'], 'status' => 0, 'goodsid' => $goodsid, 'teamid' => $teamid, 'is_team' => $is_team, 'heads' => $heads, 'discount' => !empty($heads) ? $goods['headsmoney'] : 0, 'addressid' => intval($addressid), 'address' => iserializer($order_address), 'message' => trim(input('message')), 'realname' => $isverify ? trim($realname) : '', 'mobile' => $isverify ? trim($mobile) : '', 'endtime' => $goods['endtime'], 'isverify' => intval($goods['isverify']), 'verifytype' => intval($goods['verifytype']), 'verifycode' => !empty($verifycode) ? $verifycode : 0, 'verifynum' => !empty($verifynum) ? $verifynum : 1, 'createtime' => time());
		$orderid = Db::name('shop_groups_order')->insertGetId($data);

		if (!$orderid) {
			$this->result(0,'生成订单失败！');
		}

		if (empty($teamid) && ($type == 'groups')) {
			Db::name('shop_groups_order')->where('id',$orderid)->setField('teamid',$orderid);
		}

		$order = Db::name('shop_groups_order')->where('id',$orderid)->find();
 		return $this->redirect('apiv1/groups/pay',['orderid' => $orderid,'teamid' => empty($teamid) ? $order['teamid'] : $teamid]);
	}

	/**
	 * 订单支付
	 * @param $mid [会员id]
	 * @param $status [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function pay()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$orderid = input('orderid/d');
		$teamid = input('teamid/d');
		$order = Db::name('shop_groups_order')
			->alias('o')
			->join('shop_goods g','on g.id = o.goodsid','left')
			->where('o.id',$orderid)
			->order('o.createtime','desc')
			->field('o.*,g.title,g.status as gstatus,g.deleted as gdeleted,g.total')
			->find();

		if (empty($order)) {
			$this->result(0,'订单未找到！');
		}

		if (!empty($isteam) && ($order['success'] == -1)) {
			$this->result(0,'该活动已失效，请浏览其他商品或联系商家！');
		}

		if (empty($order['gstatus']) || !empty($order['gdeleted'])) {
			$this->result(0,$order['title'] . '<br/> 已下架!');
		}

		if ($order['total'] <= 0) {
			$this->result(0,$order['title'] . '<br/>库存不足!');
		}

		if (!empty($teamid)) {
			$team_orders = Db::name('shop_groups_order')->where('teamid',$teamid)->select();

			foreach ($team_orders as $key => $value) {
				if ($team_orders && ($value['success'] == -1)) {
					$this->result(0,'该活动已过期，请浏览其他商品或联系商家！');
				}

				if ($team_orders && ($value['success'] == 1)) {
					$this->result(0,'该活动已结束，请浏览其他商品或联系商家！');
				}
			}

			$num = Db::name('shop_groups_order')->where('teamid',$teamid)->where('status','>',0)->count();

			if ($order['groupnum'] <= $num) {
				$this->result(0,'该活动已成功组团，请浏览其他商品或联系商家！');
			}
		}

		if (empty($order)) {
			$this->result(0,'订单未找到！');
		}

		if ($order['status'] == -1) {
			$this->result(0,'订单已取消！');
		} else {
			if (1 <= $order['status']) {
				$this->result(0,'订单已支付！');
			}
		}

		$log = Db::name('shop_core_paylog')->where('module','groups')->where('tid',$order['orderno'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->result(0,'订单已支付！');
		}
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $mid, 'module' => 'groups', 'tid' => $order['orderno'], 'credit' => $order['credit'], 'creditmoney' => $order['creditmoney'], 'fee' => ($order['price'] - $order['creditmoney']) + $order['freight'], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}

		$set = model('common')->getSysset(array('shop', 'pay'));
		$param_title = $set['shop']['name'] . '订单';
		$credit = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['credit'] == 1)) {
			if ($order['deductcredit2'] <= 0) {
				$credit = array('success' => true, 'current' => $member['credit2']);
			}
		}

		$order['price'] = floatval($order['price']);
		if (empty($order['price']) && !$credit['success']) {
			$complete = $this->complete($order['id'],'credit',$order['ordersn']);
			if($complete['status'] == 0)
			{
				$this->result(0,'支付出错');
			}
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
			$params['fee'] = $log['fee'];
			$params['title'] = $param_title;
			if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
				$wechat = model('payment')->wechat_build($params, 2);
				if (!is_error($wechat)) {
					$this->result(0,$wechat);
				}
			}
			$this->result(2,'success',$wechat);
		}
		$alipay = array('success' => false);
		if($paytype == 2) {
			if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
				$params = array();
				$params['tid'] = $log['tid'];
				$params['user'] = $mid;
				$params['fee'] = $log['fee'];
				$params['title'] = $param_title;

				$alipay = model('payment')->alipay_build($params, 2);
				if (is_error($alipay)) {
					$this->result(0,$alipay);
				}
			}
			$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$orderid));
		}
		$payinfo = array('orderid' => $orderid, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $order['price']);
		$this->result(1,'success',$payinfo);

	}

	/**
	 * 检查订单支付情况
	 * @param $mid [会员id]
	 * @param $status [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function complete()
	{
		$orderid = input('orderid/d');
		$teamid = input('teamid/d');
		$isteam = input('isteam/d');
		$mid = $this->getMemberId();

		if (empty($orderid)) {
			$this->result(0,'参数错误!');
		}

		$order = Db::name('shop_groups_order')->where('id',$orderid)->where('mid',$mid)->find();

		if (empty($order)) {
			$this->result(0,'订单不存在!');
		}

		$order_goods = Db::name('shop_goods')->where('id',$order['goodsid'])->find();
 
		if (empty($order_goods)) {
			$this->result(0,'商品不存在!');
		}

		$type = input('type');

		if (!in_array($type, array('wechat', 'alipay', 'credit', 'cash'))) {
			$this->result(0,'未找到支付方式!');
		}

		$log = Db::name('shop_groups_paylog')->where('module','groups')->where('tid',$order['orderno'])->find();

		if (empty($log)) {
			$this->result(0,'支付出错,请重试(0)!');
		}

		if ($type == 'credit') {
			$orderno = $order['orderno'];
			$credits = model('member')->getCredit($mid, 'credit2');
			if (($credits < $log['fee']) || ($credits < 0)) {
				$this->result(0,'余额不足,请充值');
			}

			$fee = floatval($log['fee']);
			$result = model('member')->setCredit($mid, 'credit2', 0 - $fee, array($_W['member']['uid'], $_W['shopset']['shop']['name'] . '消费' . $fee));

			if (is_error($result)) {
				$this->result(0,$result['message']);
			}

			model('groups')->payResult($log['tid'], $type);
			Db::name('shop_groups_order')->where('id',$orderid)->update(array('pay_type' => 'credit', 'status' => 1, 'paytime' => time(), 'starttime' => time()));
			$this->result(1,'success');
		}
		else {
			if ($type == 'wechat') {
				$orderno = $order['orderno'];

				if (!empty($order['ordersn2'])) {
					$orderno .= 'GJ' . sprintf('%02d', $order['ordersn2']);
				}

				$payquery = model('finance')->isWeixinPay($orderno, $log['fee'], is_h5app() ? true : false);
				$payqueryBorrow = model('finance')->isWeixinPayBorrow($orderno, $log['fee']);
				if (!is_error($payquery) || !is_error($payqueryBorrow)) {
					model('groups')->payResult($log['tid'], $type);
					pdo_update('ewei_shop_groups_order', array('pay_type' => 'wechat', 'status' => 1, 'paytime' => time(), 'starttime' => time(), 'apppay' => is_h5app() ? 1 : 0), array('id' => $orderid));
					Db::name('shop_groups_order')->where('id',$orderid)->update(array('pay_type' => 'wechat', 'status' => 1, 'paytime' => time(), 'starttime' => time(), 'apppay' => 1));
					$this->result(1,'success');
				}
				else
				{
					$this->result(0,'支付出错,请重试(1)!');
				}
			}
		}
	}


}