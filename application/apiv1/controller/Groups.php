<?php
/**
 * apiv1 拼团
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
        	$this->result(0,'未开启拼团');
        }
        $this->set = $set;
    }

    protected function merchData()
	{
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}

		return array('is_openmerch' => $is_openmerch, 'merch_data' => $merch_data);
	}

	/**
	 * 拼团首页
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
			$row["fightnum"] = $row["teamnum"] + $row["fightnum"];
			$team = Db::name('shop_groups_order')->where('paytime > 0 and heads = 1 and is_team = 1 and goodsid = ' . $row['id'])->field('id,mid,teamid')->order('createtime','desc')->find();
			$teams = array();
			if(!empty($team)) {
				$teams = Db::name('shop_groups_order')->alias('o')->join('member m','o.mid = m.id','left')->where("o.deleted = 0 and o.teamid = " . $team['teamid'] . " and o.status > 0 and o.is_team = 1 and o.success = 0 and o.paytime > 0")->field('o.id,m.avatar')->select();
			}			
			$row['teams'] = $teams;
		}
		unset($row);
		if($page == 1) {
			$this->result(1,'success',array('banner'=>$banner,'category'=>$category,'goods'=>array('list'=>$goods,'page'=>$page,'pagesize'=>$pagesize)));
		} else {
			$this->result(1,'success',array('goods'=>array('list'=>$goods,'page'=>$page,'pagesize'=>$pagesize)));
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
			$id = intval(input('id'));
		    $goods = Db::name('shop_groups_goods')->where('id = ' . $id . ' and status = 1 and deleted = 0')->find();
		}catch(\Exception $e){
		    $this->result(0,'执行错误');
		}
		if (empty($id) || empty($goods)) {
			$this->result(0,'你访问的商品不存在或已删除!');
		}
		if( !empty($goods["thumb_url"]) ) 
		{
			$goods["thumb_url"] = array_merge(iunserializer($goods["thumb_url"]));
		}
		$goods["thumbs"] = set_medias($goods["thumb_url"]);
		unset($goods['thumb_url']);
		$goods = set_medias($goods, "thumb");
		$goods["fightnum"] = Db::name('shop_groups_order')->where('goodsid = ' . $goods['id'] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
		$goods["fightnum"] = $goods["teamnum"] + $goods["fightnum"];
		$goods["content"] = lazy($goods["content"]);
		if( empty($goods) ) 
		{
			$this->result(0,"商品已下架或被删除!");
		}
		if( !empty($groupsset["discount"]) ) 
		{
			if( empty($goods["discount"]) ) 
			{
				$goods["headstype"] = $groupsset["headstype"];
				$goods["headsmoney"] = $groupsset["headsmoney"];
				$goods["headsdiscount"] = $groupsset["headsdiscount"];
			}
			if( $goods["groupsprice"] < $goods["headsmoney"] ) 
			{
				$goods["headsmoney"] = $goods["groupsprice"];
			}
		}

		$goodsoption = array('hasoption'=>0);
		if (!(empty($goods['more_spec']))) {
			$goodsoption['hasoption'] = 1;
			if( empty($goods["gid"]) ) {
				$goodsoption['hasoption'] = 0;
			}
			$options = Db::name('shop_groups_goods_option')->where('groups_goods_id',$goods['id'])->order('id','asc')->select();
			$options_stock = array_keys($options);
			if(!empty($options)) {
                $spec = array();
                $specs = Db::name('shop_groups_goods_option')->where('groups_goods_id',$goods['id'])->order('id','asc')->column('specs');
                $specs = implode("_",$specs);
                $specs = implode(',',array_values(array_flip(array_flip(explode("_",$specs)))));
                $filter_spec = Db::name('shop_goods_spec')
                    ->where('goodsid',$goods['gid'])
                    ->order('displayorder', 'asc')
                    ->field('id,title')
                    ->select();
                foreach ($filter_spec as $key => $val) {
                    $item = array();
                    $item = Db::name('shop_goods_spec_item')
                    	->where('id','in',$specs)
                        ->where('specid',$val['id'])
                        ->where('show', 1)
                        ->order('displayorder', 'asc')
                        ->field('id,title,thumb')
                        ->select();
                    if(!empty($item) && is_array($item)) {
                        $item = set_medias($item,'thumb');
                    }                    
                    
                    if(empty($item)) {
                    	unset($filter_spec[$key]);continue;
                    } else {
                    	$filter_spec[$key]['item'] = $item;
                    }
                }
                unset($val);
                foreach ($options as $v) {    //  赋值
                    $spec_goods_price[$v['specs']] = array('optionid'=>$v['id'],'specs'=>$v['specs'], 'stock'=>$v['stock'], 'price'=>$v['price'], 'marketprice'=>$v['marketprice'], 'single_price'=>$v['single_price']);
                }
                $goodsoption['filter_spec'] = $filter_spec;
                $goodsoption['spec_goods_price'] = $spec_goods_price;
			}
		}
		$goods['goodsoption'] = $goodsoption;
		$ladder = array( );
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $id)->select();
		}
		$goods['ladder'] = $ladder;
		$goodsteams = array('total'=>0);
		$teamstotal = Db::name('shop_groups_order')->where('deleted = 0 and success = 0 and is_team =1 and heads = 1 and status>0 and goodsid = ' . $goods['id'])->count();
		if($teamstotal != 0) {
			$goodsteams['total'] = $teamstotal;
			$teams = Db::name('shop_groups_order')
				->alias('o')
				->join('member m','m.id=o.mid','left')
				->join('shop_groups_goods g','g.id=o.goodsid','left')
				->where('o.goodsid = ' . $goods['id'] . ' and o.deleted = 0  and is_team =1 and o.heads = 1 and o.status > 0 and o.success = 0')
				->field('o.id,o.paytime,o.starttime,o.endtime,o.goodsid,o.ladder_id,o.is_ladder,o.teamid,m.id as mid,m.nickname,m.realname,m.mobile,m.avatar,g.groupnum')
				->order('o.createtime','desc')
				->limit(2)
				->select();
			foreach ($teams as &$val) {
				$num = Db::name('shop_groups_order')->where("deleted = 0 and teamid = " . $val['teamid'] . " and status > 0 and is_team = 1 and success = 0 and paytime > 0")->count();
				$val["num"] = $num;
				if( $val["is_ladder"] == 1 ) 
				{
					$ladder_num = Db::name('shop_groups_ladder')->where('id = ' . $val['ladder_id'])->value('ladder_num');
					$val["groupnum"] = $ladder_num;
					$val["residualnum"] = $ladder_num - $num;
				} else {
					$val["residualnum"] = $val["groupnum"] - $num;
				}
				$val['endtime'] = $val['starttime'] + ($val['endtime'] * 60 * 60);
				$val['residualtime'] = $val['endtime'] - time();
			}
			unset($val);
			$goodsteams['teams'] = $teams;
		}
		$goods['goodsteams'] = $goodsteams;
		$comments = Db::name('shop_groups_order_comment')->where('goodsid',$goods['id'])->where('level >= 0 and deleted = 0 and checked = 0')->field('nickname,headimgurl,level,content,images,createtime,isanonymous')->limit(3)->select();
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
		$goods['contentdetail'] = tomedia(url('index/webview/groupsgoodsdetail',array('id'=>$goods['id'])));
		$this->result(1,'success',$goods);
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
		$condition = ' goodsid = ' . intval($id) . ' and deleted = 0 and checked = 0';

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


		$list = Db::name('shop_groups_order_comment')->where($condition)->order('istop desc,createtime desc')->page($page,$pagesize)->select();

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
	}

	/**
	 * 商品-正在拼单
	 * @param $goodsid [int]
	 * @return  [array]    $list  []
	 **/
	public function goodsteams()
	{
		$goodsid = input('goodsid/d');
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$goods = Db::name('shop_groups_goods')->where('id = ' . $goodsid . ' and status = 1 and deleted = 0')->find();
		if(empty($goods)) {
			$this->result(0,'商品已下架或被删除!');
		}
		$condition = 'o.goodsid = ' . $goods['id'] . '  and is_team =1 and o.deleted = 0 and o.heads = 1 and o.status > 0 and o.success = 0';
		$teams = array();
		$mid = 0;
        if(!empty($this->mid)) {           
            $mid = $this->mid;
        }
        // if(!empty($mid))
        // {
        // 	$condition .= ' and o.mid != ' . $mid ;
        // }
		$teams = Db::name('shop_groups_order')
			->alias('o')
			->join('member m','m.id=o.mid','left')
			->join('shop_groups_goods g','g.id=o.goodsid','left')
			->where($condition)
			->field('o.id,o.paytime,o.starttime,o.endtime,o.goodsid,o.ladder_id,o.is_ladder,o.teamid,m.id as mid,m.nickname,m.realname,m.mobile,m.avatar,g.groupnum')
			->order('o.createtime','desc')
			->page($page,$pagesize)
			->select();
		foreach ($teams as &$val) {
			$num = Db::name('shop_groups_order')->where("deleted = 0 and teamid = " . $val['teamid'] . " and status > 0 and is_team = 1 and success = 0 and paytime > 0")->count();
			$val["num"] = $num;
			if( $val["is_ladder"] == 1 ) 
			{
				$ladder_num = Db::name('shop_groups_ladder')->where('id = ' . $val['ladder_id'])->value('ladder_num');
				$val["groupnum"] = $ladder_num;
				$val["residualnum"] = $ladder_num - $num;
			} else {
				$val["residualnum"] = $val["groupnum"] - $num;
			}
			$val['endtime'] = $val['starttime'] + ($val['endtime'] * 60 * 60);
			$val['residualtime'] = $val['endtime'] - time();
		}
		unset($val);
		$this->result(1,'success',array('list'=>$teams,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 商品检查
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function goodsCheck() 
	{
		$id = intval(input('id'));
		$type = input('type');
		$fightgroups = intval(input('fightgroups'));
		$mid = $this->getMemberId();
		if( empty($id) ) 
		{
			$this->result(0, array( "message" => "商品不存在！" ));
		}
		$goods = Db::name('shop_groups_goods')->where('id = ' . $id . ' and status = 1 and deleted = 0')->find();
		if( empty($goods) ) 
		{
			$this->result(0, array( "message" => "商品不存在！" ));
		}
		if( $goods["stock"] <= 0 ) 
		{
			$this->result(0, array( "message" => "您选择的商品库存不足，请浏览其他商品或联系商家！" ));
		}
		if( empty($goods["status"]) ) 
		{
			$this->result(0, array( "message" => "您选择的商品已经下架，请浏览其他商品或联系商家！" ));
		}
		$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $id)->count();
		if( !empty($goods["purchaselimit"]) && $goods["purchaselimit"] <= $ordernum ) 
		{
			$this->result(0, array( "message" => "您已到达此商品购买上限，请浏览其他商品或联系商家！" ));
		}
		$order = Db::name('shop_groups_order')->where('goodsid = ' . $id . ' and status >= 0  and mid = ' . $mid . ' and success = 0  and is_team = 1 and deleted = 0 ')->select();
		if( $order && $order["status"] == 0 ) 
		{
			$this->result(0, array( "message" => "您的订单已存在，请尽快完成支付！" ));
		}
		if( $order && $order["status"] == 1 && $type == "groups" ) 
		{
			$this->result(0, array( "message" => "您已经参与了该团，请等待拼团结束后再进行购买！" ));
		}
		if( $type == "single" && empty($goods["single"]) ) 
		{
			$this->result(0, array( "message" => "商品不允许单购，请重新选择！" ));
		}
		$ladder = array( );
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $id)->select();
			if( $fightgroups == 1 ) 
			{
				$info = Db::name('shop_groups_order')->where('mid != ' . $mid . ' and success = 0 and status>0 and ladder_id >0 and goodsid = ' . $id)->field('ladder_id,count(ladder_id) as order_num')->group('ladder_id')->select();
				if( !empty($info) && !empty($ladder) ) 
				{
					foreach( $ladder as $key => $value ) 
					{
						foreach( $info as $k => $v ) 
						{
							if( $value["id"] == $v["ladder_id"] ) 
							{
								$ladder[$key]["order_num"] = $v["order_num"];
							}
						}
					}
				}
			}
		}
		if( empty($goods["stock"]) ) 
		{
			$this->result(0, array( "message" => "商品库存为0，暂时无法购买，请浏览其他商品！" ));
		}
		$specArr = array( );
		if( $goods["more_spec"] == 1 ) 
		{
			$group_goods = Db::name('shop_groups_goods')->where('id = ' . $id)->find();
			if( empty($group_goods["gid"]) ) 
			{
				$this->result(0,"缺少商品");
			}
			$specArr = Db::name('shop_goods_spec')->where('goodsid = ' . $group_goods["gid"])->field('id,title')->select();
			foreach( $specArr as $k => $v ) 
			{
				$specArr[$k]["item"] = Db::name('shop_goods_spec_item')->where('specid = ' . $v["id"])->field('id,title,specid,thumb')->select();
			}
		}
		$this->result(1,'success', array( "ladder" => $ladder, "specArr" => $specArr ));
	}

	/**
	 * 订单确认
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function confirm()
	{
		$goodsid = intval(input('goodsid'));
		$type = input('type') ? trim(input('type')) : 'groups';
		$heads = intval(input('heads'));
		$teamid = intval(input('teamid'));
		$ladder_id = intval(input('ladder_id'));
		$options_id = intval(input('options_id'));
		$mid = $this->getMemberId();
		$isverify = false;
		$merchdata = $this->merchData();
		extract($merchdata);
		$shopset = $this->shopset;
		$member = model("member")->getMember($mid);
		$credit = array( );
		$goods = Db::name('shop_groups_goods')->where('id = ' . $goodsid . ' and deleted = 0')->find();
		if( $goods["stock"] <= 0 ) 
		{
			$this->result(0,"您选择的商品库存不足，请浏览其他商品或联系商家！");
		}
		if( empty($goods["status"]) ) 
		{
			$this->result(0,"您选择的商品已经下架，请浏览其他商品或联系商家！");
		}
		$ladder = array( );
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $goodsid)->find();
		}
		if( 0 < $ladder_id ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('id = ' . $ladder_id)->find();
		}
		$goods['optionid'] = 0;
		$goods['optiontitle'] = '';
		if( $goods["more_spec"] == 1 ) 
		{
			$option_id = $options_id;
			if( !empty($option_id) ) {
				$option = Db::name('shop_groups_goods_option')->where('id = ' . $option_id . ' and groups_goods_id=' . $goods["id"])->find();
			} 
			if( empty($option) ) {
				$this->result(0,"请选择商品规格！");
			}
			$goods['optionid'] = $option['id'];
			$goods['optiontitle'] = $option['title'];
		}
		if( $type == "groups" ) 
		{
			$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 1')->count();
		} else {
			if( $type == "single" ) 
			{
				$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 0')->count();
			}
		}
		if( !empty($goods["purchaselimit"]) && $goods["purchaselimit"] <= $ordernum ) {
			$this->result(0,"您已到达此商品购买上限，请浏览其他商品或联系商家！");
		}
		if( $type == "groups" ) 
		{
			$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 1')->find();
		}
		else 
		{
			if( $type == "single" ) 
			{
				$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 0')->find();
			}
		}
		if( $order && $order["status"] == 0 ) 
		{
			$this->result(0,"您的订单已存在，请尽快完成支付！");
		}
		if( $order && $order["status"] == 1 && $type == "groups" ) 
		{
			$this->result(0,"您已经参与了该团，请等待拼团结束后再进行购买！");
		}
		if( !empty($teamid) ) 
		{
			$orders = Db::name('shop_groups_order')->where('teamid = ' . $teamid)->select();
			foreach( $orders as $key => $value ) 
			{
				if( $orders && $value["success"] == -1 ) 
				{
					$this->result(0,"该活动已过期，请浏览其他商品或联系商家！");
				}
				if( $orders && $value["success"] == 1 ) 
				{
					$this->result(0,"该活动已结束，请浏览其他商品或联系商家！");
				}
			}
			$num = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and status > 0 and goodsid = ' . $goods["id"])->count();
			if( !empty($ladder) ) 
			{
				if( $ladder["ladder_num"] <= $num ) 
				{
					$this->result(0,"该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
			else 
			{
				if( $goods["groupnum"] <= $num ) 
				{
					$this->result(0,"该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
		}
		$goods['marketprice'] = 0;
		if( $type == "groups" && $goods["more_spec"] == 0 && $goods["is_ladder"] == 0 ) 
		{
			$goodsprice = $goods["groupsprice"];
			$price = $goods["groupsprice"];
			$groupnum = intval($goods["groupnum"]);
			$is_team = 1;
		}
		else 
		{
			if( $type == "single" && $goods["more_spec"] == 1 ) 
			{
				$goodsprice = $option["single_price"];
				$price = $option["single_price"];
				$goods["singleprice"] = $option["single_price"];
				$groupnum = 1;
				$is_team = 0;
				$teamid = 0;
			}
			else 
			{
				if( $type == "groups" && !empty($ladder) && $goods["is_ladder"] == 1 ) 
				{
					$goodsprice = $ladder["ladder_price"];
					$price = $ladder["ladder_price"];
					$groupnum = $ladder["ladder_num"];
					$is_team = 1;
					$goods["groupsprice"] = $ladder["ladder_price"];
				}
				else 
				{
					if( $type == "groups" && !empty($option) && $goods["more_spec"] == 1 ) 
					{
						$goodsprice = $option["price"];
						$price = $option["price"];
						$groupnum = intval($goods["groupnum"]);
						$is_team = 1;
						$goods["groupsprice"] = $option["price"];
					}
					else 
					{
						if( $type == "single" ) 
						{
							$goodsprice = $goods["singleprice"];
							$price = $goods["singleprice"];
							$groupnum = 1;
							$is_team = 0;
							$teamid = 0;
						}
					}
				}
			}
		}
		$set = Db::name('shop_groups_set')->field('discount,headstype,headsmoney,headsdiscount')->find();
		if( !empty($set["discount"]) && $heads == 1 ) 
		{
			if( !empty($goods["discount"]) ) 
			{
				if( empty($goods["headstype"]) ) 
				{
				}
				else 
				{
					if( 0 < $goods["headsdiscount"] ) 
					{
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $goods["headsdiscount"]) / 100, 2);
					}
					else 
					{
						if( $goods["headsdiscount"] == 0 ) 
						{
							$goods["headsmoney"] = 0;
						}
					}
				}
			}
			else 
			{
				if( empty($set["headstype"]) ) 
				{
					$goods["headsmoney"] = $set["headsmoney"];
				}
				else 
				{
					if( 0 < $set["headsdiscount"] ) 
					{
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $set["headsdiscount"]) / 100, 2);
					}
				}
				$goods["headstype"] = $set["headstype"];
				$goods["headsdiscount"] = $set["headsdiscount"];
			}
			if( $goods["groupsprice"] < $goods["headsmoney"] ) 
			{
				$goods["headsmoney"] = $goods["groupsprice"];
			}
			$price = $price - $goods["headsmoney"];
			if( $price < 0 ) 
			{
				$price = 0;
			}
		} else {
			$goods["headsmoney"] = 0;
		}
		$stores = array();
		$needaddress = false;
		$address = array();
		if( !empty($goods["isverify"]) ) {
			$isverify = true;
			$goods["freight"] = 0;
			$storeids = array( );
			$merchid = 0;
			if( !empty($goods["storeids"]) ) 
			{
				$merchid = $goods["merchid"];
				$storeids = array_merge(explode(",", $goods["storeids"]), $storeids);
			}
			if( empty($storeids) ) 
			{
				if( 0 < $merchid ) 
				{
					$stores = Db::name('shop_store')->where('merchid=' . $merchid . ' and status=1 and type in(2,3)')->select();
				}
				else 
				{
					$stores = Db::name('shop_store')->where('status=1 and type in(2,3)')->select();
				}
			} else {
				if( 0 < $merchid ) 
				{
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->select();
				}
				else 
				{
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->select();
				}
			}
		} else {
			$needaddress = true;
			$address = Db::name('shop_member_address')->where(" mid = " . $mid . ' and deleted=0 and isdefault=1 ')->find();
		}
		$creditdeduct =  Db::name('shop_groups_set')->field('creditdeduct,groupsdeduct,credit,groupsmoney')->find();
		if( intval($creditdeduct["creditdeduct"]) ) 
		{
			if( intval($creditdeduct["groupsdeduct"]) ) 
			{
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $creditdeduct["groupsmoney"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $creditdeduct["groupsmoney"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $creditdeduct["groupsmoney"];
				} else {
					$credit["deductprice"] = 0;
				}
			} else {
				$sys_data = model("common")->getPluginset("sale");
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $sys_data["money"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $sys_data["money"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $sys_data["money"];
				} else {
					$credit["deductprice"] = 0;
				}
			}
		}
		if ($is_openmerch == 1) {
			$merchid = $goods['merchid'];
		}
		$goods['marketprice'] = $goodsprice;
		if ($merchid == 0) {
			$goods_list[0]['id'] = 0;
			$goods_list[0]['logo'] = tomedia($shopset['shop']['logo']);
			$goods_list[0]['merchname'] = $shopset['shop']['name'];
			$dispatch_list = Db::name('shop_dispatch')->where('merchid',0)->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
			if(empty($dispatch_list)) {
				$dispatch_list = array('id'=>0,'dispatchname'=>'快递配送');
			}
			if(empty($goods['freight'])) {
				$goods_list[0]['dispatch_list'] = array('id'=>0,'dispatchname'=>'包邮','dispatchprice'=>0);
			} else {
				$dispatch_list['dispatchprice'] = round($goods['freight'],2);
				$goods_list[0]['dispatch_list'] = $dispatch_list;
			}
		} else {
			$merch_data = model('merch')->getListUserOne($merchid);
			$goods_list[0]['id'] = $merch_data['id'];
			$goods_list[0]['logo'] = tomedia($merch_data['logo']);
			$goods_list[0]['merchname'] = $merch_data['merchname'];
			$dispatch_list = Db::name('shop_dispatch')->where('merchid',$merchid)->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
			if(empty($dispatch_list)) {
				$dispatch_list = array('id'=>0,'dispatchname'=>'快递配送');
			}
			if(empty($goods['freight'])) {
				$goods_list[0]['dispatch_list'] = array('id'=>0,'dispatchname'=>'包邮','dispatchprice'=>0);
			} else {
				$dispatch_list['dispatchprice'] = round($goods['freight'],2);
				$goods_list[0]['dispatch_list'] = $dispatch_list;
			}
		}
		$goods['thumb'] = tomedia($goods['thumb']);
		$goods_list[0]['goods'] = $goods;
		$goods_list[0]['totalprice'] = $price+$goods['freight'];
		$goods_list[0]['goodsnum'] = 1;
		$this->result(1,'success',array('list'=>$goods_list,'total'=>1,'realprice'=>$price+$goods['freight'],'goodsprice'=>$goodsprice,'deductprice'=>$credit["deductprice"] ? $credit["deductprice"] : 0,'needaddress'=>$needaddress,'address'=>$needaddress ? ($address ? $address : (object)null) : (object)null,'dispatch_price'=>round($goods['freight'],2), 'headsmoney' => $goods['headsmoney'], 'isverify' => $isverify, 'stores' => $stores ? $stores : (object)null));		
	}

	/**
	 * 提交订单
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function submit()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		if ($member['isblack'] == 1) {
			$this->result(0,'操作失败');
		}
		$isverify = false;		
		$groupnum = 0;
		$is_team = 0;
		$teamid = 0;
		$goodsid = intval(input('goodsid'));
		$type = input('type') ? trim(input('type')) : 'groups';
		$heads = intval(input('heads'));
		$teamid = intval(input('teamid'));
		$ladder_id = intval(input('ladder_id'));
		$options_id = intval(input('options_id'));
		$credit = array( );
		$goods = Db::name('shop_groups_goods')->where('id = ' . $goodsid . ' and deleted = 0')->find();
		if( $goods["stock"] <= 0 ) {
			$this->result(0,"您选择的商品库存不足，请浏览其他商品或联系商家！");
		}
		if( empty($goods["status"]) ) {
			$this->result(0,"您选择的商品已经下架，请浏览其他商品或联系商家！");
		}
		$ladder = array();
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $goodsid)->find();
		}
		if( 0 < $ladder_id ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('id = ' . $ladder_id)->find();
		}
		if( $goods["more_spec"] == 1 ) 
		{
			$option_id = $options_id;
			if( !empty($option_id) ) 
			{
				$option = Db::name('shop_groups_goods_option')->where('id=' . $option_id . ' and groups_goods_id=' . $goods["id"])->find();
			}
			if(empty($option)) {
				$this->result(0,"请选择规格！");
			}
		}
		if( $type == "groups" ) {
			$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 1')->count();
		} else {
			if( $type == "single" ) {
				$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 0')->count();
			}
		}
		if( !empty($goods["purchaselimit"]) && $goods["purchaselimit"] <= $ordernum ) {
			$this->result(0,"您已到达此商品购买上限，请浏览其他商品或联系商家！");
		}
		if( $type == "groups" ) {
			$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 1')->find();
		} else {
			if( $type == "single" ) {
				$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 0')->find();
			}
		}
		if( $order && $order["status"] == 0 ) {
			$this->result(0,"您的订单已存在，请尽快完成支付！");
		}
		if( $order && $order["status"] == 1 && $type == "groups" ) {
			$this->result(0,"您已经参与了该团，请等待拼团结束后再进行购买！");
		}
		if( !empty($teamid) ) {
			$orders = Db::name('shop_groups_order')->where('teamid = ' . $teamid)->select();
			foreach( $orders as $key => $value ) {
				if( $orders && $value["success"] == -1 ) 
				{
					$this->result(0,"该活动已过期，请浏览其他商品或联系商家！");
				}
				if( $orders && $value["success"] == 1 ) 
				{
					$this->result(0,"该活动已结束，请浏览其他商品或联系商家！");
				}
			}
			$num = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and status > 0 and goodsid = ' . $goods["id"])->count();
			if( !empty($ladder) ) 
			{
				if( $ladder["ladder_num"] <= $num ) 
				{
					$this->result(0,"该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
			else 
			{
				if( $goods["groupnum"] <= $num ) 
				{
					$this->result(0,"该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
		}
		if( $type == "groups" && $goods["more_spec"] == 0 && $goods["is_ladder"] == 0 ) 
		{
			$goodsprice = $goods["groupsprice"];
			$price = $goods["groupsprice"];
			$groupnum = intval($goods["groupnum"]);
			$is_team = 1;
		}
		else 
		{
			if( $type == "single" && $goods["more_spec"] == 1 ) 
			{
				$goodsprice = $option["single_price"];
				$price = $option["single_price"];
				$goods["singleprice"] = $option["single_price"];
				$groupnum = 1;
				$is_team = 0;
				$teamid = 0;
			}
			else 
			{
				if( $type == "groups" && !empty($ladder) && $goods["is_ladder"] == 1 ) 
				{
					$goodsprice = $ladder["ladder_price"];
					$price = $ladder["ladder_price"];
					$groupnum = $ladder["ladder_num"];
					$is_team = 1;
					$goods["groupsprice"] = $ladder["ladder_price"];
				}
				else 
				{
					if( $type == "groups" && !empty($option) && $goods["more_spec"] == 1 ) 
					{
						$goodsprice = $option["price"];
						$price = $option["price"];
						$groupnum = intval($goods["groupnum"]);
						$is_team = 1;
						$goods["groupsprice"] = $option["price"];
					}
					else 
					{
						if( $type == "single" ) 
						{
							$goodsprice = $goods["singleprice"];
							$price = $goods["singleprice"];
							$groupnum = 1;
							$is_team = 0;
							$teamid = 0;
						}
					}
				}
			}
		}
		$set = Db::name('shop_groups_set')->field('discount,headstype,headsmoney,headsdiscount')->find();
		if( !empty($set["discount"]) && $heads == 1 ) 
		{
			if( !empty($goods["discount"]) ) 
			{
				if( empty($goods["headstype"]) ) 
				{
				} else {
					if( 0 < $goods["headsdiscount"] ) {
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $goods["headsdiscount"]) / 100, 2);
					} else {
						if( $goods["headsdiscount"] == 0 ) {
							$goods["headsmoney"] = 0;
						}
					}
				}
			} else {
				if( empty($set["headstype"]) ) 
				{
					$goods["headsmoney"] = $set["headsmoney"];
				}
				else 
				{
					if( 0 < $set["headsdiscount"] ) 
					{
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $set["headsdiscount"]) / 100, 2);
					}
				}
				$goods["headstype"] = $set["headstype"];
				$goods["headsdiscount"] = $set["headsdiscount"];
			}
			if( $goods["groupsprice"] < $goods["headsmoney"] ) 
			{
				$goods["headsmoney"] = $goods["groupsprice"];
			}
			$price = $price - $goods["headsmoney"];
			if( $price < 0 ) 
			{
				$price = 0;
			}
		}
		else 
		{
			$goods["headsmoney"] = 0;
		}
		if( !empty($goods["isverify"]) ) 
		{
			$isverify = true;
			$goods["freight"] = 0;
			$merchid = 0;
			$verifycode = "PT" . random(8, true);
			while( 1 ) 
			{
				$count = Db::name('shop_groups_order')->where("verifycode=" . $verifycode)->count();
				if( $count <= 0 ) 
				{
					break;
				}
				$verifycode = "PT" . random(8, true);
			}
			$verifynum = (!empty($goods["verifytype"]) ? ($verifynum = $goods["verifynum"]) : 1);
		}
		else 
		{
			$address = Db::name('shop_member_address')->where("mid=" . $mid . ' and deleted=0 and isdefault=1 ')->find();
		}
		$creditdeduct =  Db::name('shop_groups_set')->field('creditdeduct,groupsdeduct,credit,groupsmoney')->find();
		if( intval($creditdeduct["creditdeduct"]) ) 
		{
			if( intval($creditdeduct["groupsdeduct"]) ) 
			{
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $creditdeduct["groupsmoney"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $creditdeduct["groupsmoney"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $creditdeduct["groupsmoney"];
				}
				else 
				{
					$credit["deductprice"] = 0;
				}
			} else {
				$sys_data = model("common")->getPluginset("sale");
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $sys_data["money"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $sys_data["money"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $sys_data["money"];
				}
				else 
				{
					$credit["deductprice"] = 0;
				}
			}
		}
		$ordersn = model("common")->createNO("shop_groups_order", "orderno", "PT");
		$aid = intval(input('addressid/d',0));
		$realname = trim(input('realname/s',''));
		$mobile = trim(input('mobile/s',''));
		$isdeduct = intval(input('isdeduct/d',0));
		$credit = input('credit');
		$creditmoney = input('creditmoney');
		$message = trim(input('message/s',''));
		$paytype = intval(input('paytype/d',0));
		if( empty($aid) && !$isverify ) {
			$this->result(0,"请选择地址");
		}
		if( $isverify && (empty($realname) || empty($mobile)) ) {
			$this->result(0,"联系人或联系电话不能为空！");
		}
		if( 0 < intval($aid) && !$isverify ) {
			$order_address = Db::name('shop_member_address')->where('id = ' . intval($aid) . ' and mid = ' . $mid)->find();
			if( empty($order_address) ) 
			{
				$this->result(0,"未找到地址");
			}
			if( empty($order_address["province"]) || empty($order_address["city"]) ) 
			{
				$this->result(0,"地址请选择省市信息");
			}
		}
		$data = array("groupnum" => $groupnum, "mid" => $mid, "paytime" => "", "orderno" => $ordersn, "paytype" => $paytype, "credit" => (intval($isdeduct) ? $credit : 0), "creditmoney" => (intval($isdeduct) ? $creditmoney : 0), "price" => $price, "freight" => $goods["freight"], "status" => 0, "goodsid" => $goodsid, "teamid" => $teamid, "is_team" => $is_team, "more_spec" => $goods["more_spec"], "heads" => $heads, "discount" => (!empty($heads) ? $goods["headsmoney"] : 0), "addressid" => intval($aid), "address" => iserializer($order_address), "message" => trim($message), "realname" => ($isverify ? trim($realname) : ""), "mobile" => ($isverify ? trim($mobile) : ""), "endtime" => $goods["endtime"], "isverify" => intval($goods["isverify"]), "verifytype" => intval($goods["verifytype"]), "verifycode" => (!empty($verifycode) ? $verifycode : 0), "verifynum" => (!empty($verifynum) ? $verifynum : 1), "createtime" => time() );
		if( $goods["is_ladder"] == 1 && 0 < $ladder_id ) {
			$data["is_ladder"] = 1;
			$data["ladder_id"] = $ladder_id;
		}
		if( $goods["more_spec"] == 1 && 0 < $options_id ) 
		{
			$data["specs"] = $option["specs"];
		}
		$order_insert = Db::name('shop_groups_order')->insertGetId($data);
		if( !$order_insert ) 
		{
			$this->result(0,"生成订单失败！");
		}
		$orderid = $order_insert;
		if( empty($teamid) && $type == "groups" ) 
		{
			Db::name('shop_groups_order')->where('id = ' . intval($orderid))->update(array( "teamid" => $orderid ));
		}
		if( !empty($orderid)) 
		{
			if($goods["more_spec"] == 1 ) {
				$_data = array( "goods_id" => $goods["gid"], "groups_goods_id" => $goods["id"], "groups_goods_option_id" => $options_id, "option_name" => $option["title"], "groups_order_id" => $orderid, "price" => $price, "create_time" => time() );
			} else {
				$_data = array( "goods_id" => $goods["gid"], "groups_goods_id" => $goods["id"], "groups_goods_option_id" => 0, "option_name" => '', "groups_order_id" => $orderid, "price" => $price, "create_time" => time() );
			}			
			Db::name('shop_groups_order_goods')->insert($_data);
		}
		$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid))->find();
 		return $this->redirect('apiv1/groups/pay',['orderid' => $orderid,'teamid' => empty($teamid) ? $order['teamid'] : $teamid]);
	}

	/**
	 * 订单支付
	 * @param $mid [会员id]
	 * @param $orderstatus [订单状态]
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
			->join('shop_groups_goods g','g.id = o.goodsid','left')
			->where('o.id',$orderid)
			->order('o.createtime','desc')
			->field('o.*,g.title,g.status as gstatus,g.deleted as gdeleted,g.stock')
			->find();

		if (empty($order)) {
			$this->result(0,'订单未找到！');
		}

		if (!empty($isteam) && ($order['success'] == -1)) {
			$this->result(0,'该活动已失效，请浏览其他商品或联系商家！');
		}

		if (empty($order['gstatus']) || !empty($order['gdeleted'])) {
			$this->result(0,$order['title'] . ' 已下架!');
		}

		if ($order['stock'] <= 0) {
			$this->result(0,$order['title'] . ' 库存不足!');
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
			$credit = array('success' => true, 'current' => $member['credit2']);
		}
		$order['price'] = floatval($order['price']);
		if (empty($order['price']) && !$credit['success']) {
			$complete = model('order')->complete($order['id'],'credit',$order['ordersn']);
			if($complete['status'] == 0)
			{
				$this->result(0,'支付出错');
			}
			$this->result(0,'订单已付款');
		}

		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['product_id'] = $order['teamid'];
		$params['user'] = $mid;
		$params['fee'] = $log["fee"];
		$params['title'] = $param_title;
		$paytype = input('paytype/d') ? input('paytype/d') : $order['paytype'];
		$headerinfo = $this->headerinfo;
		if(!in_array($headerinfo['device-type'], array('iOS','android','wechat','web'))) {
			$this->result(0,'支付出错!');
		}

		$wechat = array('success' => false);
		if ($paytype == 1) {
			// if(is_weixin())	{
			// 	if (isset($set['pay']) && ($set['pay']['wx_wechat'] == 1)) {
			// 		$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 1, 'wechat');
			// 		if (!is_array($wechat)) {
			// 			$this->result(0,$wechat);
			// 		}
			// 		$wechat = json_encode($wechat);
			// 	}
			// } elseif (is_mobile()) {
			// 	if (isset($set['pay']) && ($set['pay']['web_wechat'] == 1)) {
			// 		$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 1, 'web');
			// 		if (!is_array($wechat)) {
			// 			$this->result(0,$wechat);
			// 		}
			// 	}
			// } else {
			// 	if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
			// 		$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 1, 'app');
			// 		if (!is_array($wechat)) {
			// 			$this->result(0,$wechat);
			// 		}
			// 	}
			// }
			if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
				$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 1, 'app');
				if (!is_array($wechat)) {
					$this->result(0,$wechat);
				}
			}
			$wechat['product_id'] = $order['teamid'];			
			$this->result(1,'success',$wechat);
		}
		$alipay = array('success' => false);
		if($paytype == 2) {
			// if(is_weixin() || is_mobile()) {
			// 	if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
			// 		$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 1, getHttpHost() . '/public/dist/order','web');
			// 		if (empty($alipay)) {
			// 			$this->result(0,'参数错误');
			// 		}
			// 	}
			// } else {
			// 	if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
			// 		$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 1, getHttpHost() . '/public/dist/order','app');
			// 		if (empty($alipay)) {
			// 			$this->result(0,'参数错误');
			// 		}
			// 	}
			// }
			if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
				$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 1, getHttpHost() . '/public/dist/order','app');
				if (empty($alipay)) {
					$this->result(0,'参数错误');
				}
			}			
			$product_id = $order['teamid'];			
			$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$product_id));
		}

		$cash = array('success' => false);
		$payinfo = array('orderid' => $orderid, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $order['price']);
		$this->result(1,'success',$payinfo);
	}

	public function getorderlist()
	{
		$list = array( );
		$mid = $this->getMemberId();
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$orderstatus = input('status');
		if( $orderstatus == 0 ) {
			$tab_all = true;
			$condition = " o.mid= " . $mid . "  and o.deleted = 0 ";
		} else {
			$condition = " o.mid= " . $mid . "  and o.deleted = 0 ";
			if( $orderstatus == 1 ) {
				$tab0 = true;
				$condition .= " and o.status= 0 ";
			} else {
				if( $orderstatus == 2 ) {
					$tab1 = true;
					$condition .= " and o.status= 1 and (o.is_team = 0 or o.success = 1) ";
				} else {
					if( $orderstatus == 3 ) {
						$tab2 = true;
						$condition .= "and ( o.status = 2  or( o.status = 1 and o.isverify = 1)) ";
					} else {
						if( $orderstatus == 4 ) {
							$tab3 = true;
							$condition .= " and o.status= 3 ";
						}
					}
				}
			}
		}
		$orders = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('shop_groups_ladder l','l.id = o.ladder_id','left')->join('shop_groups_order_goods p','p.groups_order_id = o.id','left')->join('shop_groups_goods_option op','op.specs = o.specs','left')->where($condition)->order('o.createtime desc')->field('o.id,o.orderno,o.createtime,o.price,o.more_spec,o.is_ladder,o.freight,o.ladder_id,o.specs,o.creditmoney,o.goodsid,o.teamid,o.status,o.is_team,o.success,o.teamid,o.mid,g.title,g.thumb,g.units,g.goodsnum,g.groupsprice,g.singleprice,o.verifynum,o.verifytype,o.isverify,o.verifycode,g.thumb_url,l.ladder_price,l.ladder_num,p.option_name,op.title as optiontitle')->page($page,$pagesize)->select();
		$total = Db::name('shop_groups_order')->alias('o')->where($condition)->count();
		foreach( $orders as $key => $value ) 
		{
			$verifytotal = Db::name('shop_groups_verify')->where('orderid = ' . $value['id'] . ' and mid = ' . $mid . ' and verifycode = ' . $value["verifycode"])->count();
			if( !$verifytotal ) 
			{
				$verifytotal = 0;
			}
			$order["vnum"] = $value["verifynum"] - intval($verifytotal);
			$order["amount"] = ($value["price"] + $value["freight"]) - $value["creditmoney"];
			$orderstatuscss = "text-cancel";
			switch( $value["status"] ) 
			{
				case "-1": $orderstatus = "已取消";$status = 10;
				break;
				case "0": $orderstatus = "待付款";$status = 21;
				$orderstatuscss = "text-cancel";
				break;
				case "1": if( $value["is_team"] == 0 || $value["success"] == 1 ) 
				{
					$orderstatus = "待发货";$status = 31;
					$orderstatuscss = "text-warning";
				}
				else 
				{
					if( $value["success"] == -1 ) 
					{
						$orderstatus = "已过期";$status = 31;
						$orderstatuscss = "text-warning";
					}
					else 
					{
						$orderstatus = "已付款";$status = 31;
						$orderstatuscss = "text-success";
					}
				}
				break;
				case "2": $orderstatus = "待收货";$status = 40;
				$orderstatuscss = "text-danger";
				break;
				case "3": $orderstatus = "已完成";$status = 51;
				$orderstatuscss = "text-success";
				break;
			}
			$order["statusstr"] = $orderstatus;
			$order["statuscss"] = $orderstatuscss;
		}
		$orders = set_medias($orders, "thumb");
		$this->result(1, 'success', array( "list" => $orders, "page" => $page, "pagesize" => $pagesize ));
	}

	public function getteamlist()
	{
		$mid = $this->getMemberId();
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$success = input('success');
		$condition = " o.mid= " . $mid . " and o.is_team = 1 and o.paytime > 0 and o.deleted = 0 ";
		if($success != '') {
			if( $success == 0 ) 
			{
				$condition .= " and o.success = " . $success;
			} else {
				if( $success == 1 ) {
					$condition .= " and o.success = " . $success;
				} else {
					if( $success == -1 ) {
						$condition .= " and o.success = " . $success;
					}
				}
			}
		}

		$orders = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('shop_groups_ladder l','l.id = o.ladder_id','left')->join('shop_groups_order_goods p','p.groups_order_id = o.id','left')->where($condition)->field('o.*,g.title,g.price as gprice,g.groupsprice,l.ladder_price,l.ladder_num,p.option_name,g.thumb,g.thumb_url,g.units,g.goodsnum')->order('o.createtime desc')->page($page,$pagesize)->select();
		$total = Db::name('shop_groups_order')->alias('o')->where($condition)->count();
		foreach( $orders as &$order ) 
		{
			$order["amount"] = ($order["price"] + $order["freight"]) - $order["creditmoney"];
			$goods = Db::name('shop_groups_goods')->where('id = ' . $order['goodsid'])->find();
			$alltuan = Db::name('shop_groups_order')->where('teamid = ' . $order['teamid'] . ' and success = 1')->select();
			$item = array( );
			foreach( $alltuan as $num => $all ) 
			{
				$item[$num] = $all["id"];
			}
			$order["itemnum"] = count($item);
			$tuan_first_order = Db::name('shop_groups_order')->where('teamid = ' . $order["teamid"] . ' and paytime > 0 and heads = 1')->find();
			$hours = $tuan_first_order["endtime"];
			$time = time();
			$date = date("Y-m-d H:i:s", $tuan_first_order["starttime"]);
			$endtime = date("Y-m-d H:i:s", strtotime(" " . $date . " + " . $hours . " hour"));
			$date1 = date("Y-m-d H:i:s", $time);
			$order["lasttime"] = strtotime($endtime) - strtotime($date1);
			$order["starttime"] = date("Y-m-d H:i:s", $order["starttime"]);
			$verifytotal = Db::name('shop_groups_verify')->where('orderid = ' . $order['id'] . ' and mid = ' . $mid . ' and verifycode = ' . $order["verifycode"])->count();
			if( !$verifytotal ) 
			{
				$verifytotal = 0;
			}
			$order["vnum"] = $order["verifynum"] - intval($verifytotal);
			$order["amount"] = ($order["price"] + $order["freight"]) - $order["creditmoney"];
			switch( $order["status"] ) 
			{
				case "-1": $orderstatus = "已取消";
				break;
				case "0": $orderstatus = "待付款";
				break;
				case "1": if( $order["is_team"] == 0 || $order["success"] == 1 ) 
				{
					$orderstatus = "待发货";
				}
				else 
				{
					if( $order["success"] == -1 ) 
					{
						$orderstatus = "已过期";
					}
					else 
					{
						$orderstatus = "已付款";
					}
				}
				break;
				case "2": $orderstatus = "待收货";
				break;
				case "3": $orderstatus = "已完成";
				break;
			}
			$order["orderstatusstr"] = $orderstatus;
			if($order['itemnum'] == $order['groupnum']) {
				$teamstatus = "拼团已成功";$status = 2;
			} else {
				if($order['success'] == 1) {
					$teamstatus = "拼团已成功";$status = 2;
				} else {
					if($order['lasttime'] >0) {
						if($order['status']==0) {
							$teamstatus = "未支付";$status = -1;
						} elseif( $order['itemnum'] < $order['groupnum']) {
							if($order['status'] > 0) {
								$teamstatus = "拼团进行中";$status = 1;
							} else {
								$teamstatus = "拼团已取消";$status = 3;
							}
						}						
					} else {
						$teamstatus = "拼团已过期";$status = 3;
					}
				}
			}
			$order["status"] = $status;
			$order["teamstatusstr"] = $teamstatus;
		}
		unset($order);
		$orders = set_medias($orders, "thumb");
		$this->result(1, 'success', array( "list" => $orders, "page" => $page , "pagesize" => $pagesize));
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
		$mid = 0;
		if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }
		$order = Db::name('shop_groups_order')->alias('o')->join('shop_groups_ladder l','l.id = o.ladder_id','left')->where('o.teamid = ' . $teamid . ' and o.heads = 1')->field('o.*,ifnull(l.ladder_num,0) as ladder_num')->find();	
		if(empty($order)) {
			$this->result(0,'该团不存在!');
		}
		if(!empty($order['ladder_num'])) {
			$order['groupnum'] = $order['ladder_num'];
		}	
		if ($order['groupnum'] == 1) {
			$order['single'] = 1;
		}
		$isjoin = false;
		if(!empty($mid)) {
			$myjoin = Db::name('shop_groups_order')->where("mid = " . $mid . " and deleted = 0 and teamid = " . $order['teamid'] . " and status > 0")->count();
			$isjoin = $myjoin ? true : false;
		}
		$order['isjoin'] = $isjoin;

		$goods = Db::name('shop_groups_order_goods')->alias('og')->join('shop_groups_goods g','g.id = og.groups_goods_id')->join('shop_groups_goods_option op','op.id = og.groups_goods_option_id','left')->where('og.groups_order_id = ' . $order['id'])->field('g.id,g.title,g.thumb_url,g.thumb,g.groupsprice,g.units,g.goodsnum,g.price,g.is_ladder,g.content,g.more_spec,g.endtime,ifnull(op.title,"") as optiontitle')->find();
		$goods = set_medias($goods, "thumb");
		if (!empty($goods['thumb_url'])) {
			$goods['thumb_url'] = array_merge(iunserializer($goods['thumb_url']));
		}
		$goods['thumb_url'] = set_medias($goods['thumb_url']);
		$goods['contentdetail'] = tomedia(url('index/webview/groupsgoodsdetail',array('id'=>$goods['id'])));
		if(!empty($goods['more_spec'])) {
			$maxprice = Db::name('shop_groups_goods_option')->where('groups_goods_id = ' . $goods['id'])->max('price');
			$minprice = Db::name('shop_groups_goods_option')->where('groups_goods_id = ' . $goods['id'])->min('price');
			if(!empty($maxprice) && !empty($minprice)) {
				if($minprice != $maxprice) {
					$goods['groupsprice'] = $minprice . '~' . $maxprice;
				} else {
					$goods['groupsprice'] = $minprice;
				}
			}
		} else {
			$maxprice = Db::name('shop_groups_ladder')->where('goods_id = ' . $goods['id'])->max('ladder_price');
			$minprice = Db::name('shop_groups_ladder')->where('goods_id = ' . $goods['id'])->min('ladder_price');
			if(!empty($maxprice) && !empty($minprice)) {
				if($minprice != $maxprice) {
					$goods['groupsprice'] = $minprice . '~' . $maxprice;
				} else {
					$goods['groupsprice'] = $minprice;
				}
			}
		}

		$alltuan = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and status > 0')->select();
		$needmembers = intval($order['groupnum']) - count($alltuan);
		if ($needmembers <= 0) {
			Db::name('shop_groups_order')->where('teamid',$teamid)->setField('success',1);
		}
		$order['needmembers'] = $needmembers;
		$order['shareurl'] = 'http://119.23.225.225:81/group/detail/' . $order['teamid'];
		$order["itemnum"] = Db::name('shop_groups_order')->where('teamid = ' . $order['teamid'] . ' and success = 1')->count();
		$hours = $order["endtime"];
		$time = time();
		$date = date("Y-m-d H:i:s", $order["starttime"]);
		$endtime = date("Y-m-d H:i:s", strtotime($date . " + " . $hours . " hour"));
		$date1 = date("Y-m-d H:i:s", $time);
		$order["lasttime"] = strtotime($endtime) - strtotime($date1);
		$order["endtime"] = strtotime($endtime);
		switch( $order["status"] ) 
		{
			case "-1": $orderstatus = "已取消";
			break;
			case "0": $orderstatus = "待付款";
			break;
			case "1": if( $order["is_team"] == 0 || $order["success"] == 1 ) 
			{
				$orderstatus = "待发货";
			}
			else 
			{
				if( $order["success"] == -1 ) 
				{
					$orderstatus = "已过期";
				}
				else 
				{
					$orderstatus = "已付款";
				}
			}
			break;
			case "2": $orderstatus = "待收货";
			break;
			case "3": $orderstatus = "已完成";
			break;
		}
		$order["orderstatusstr"] = $orderstatus;
		if($order['itemnum'] == $order['groupnum']) {
			$teamstatus = "拼团已成功";$status = 2;
		} else {
			if($order['success'] == 1) {
				$teamstatus = "拼团已成功";$status = 2;
			} else {
				if($order['lasttime'] >0) {
					if($order['status']==0) {
						$teamstatus = "未支付";$status = -1;
					} elseif( $order['itemnum'] < $order['groupnum']) {
						if($order['status'] > 0) {
							$teamstatus = "拼团进行中";$status = 1;
						} else {
							$teamstatus = "拼团已取消";$status = 3;
						}
					}						
				} else {
					$teamstatus = "拼团已过期";$status = 3;
				}
			}
		}
		$order["status"] = $status;
		$order["teamstatusstr"] = $teamstatus;

		$orders = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and paytime > 0')->field('id,mid,groupnum,success,ladder_id')->order('heads desc,id asc')->select();
		foreach ($orders as &$value) {
			$avatar = Db::name('member')->where('id',$value['mid'])->field('id,avatar,nickname')->find();
			$value['mid'] = $avatar['id'];
			$value['nickname'] = $avatar['nickname'];
			$value['avatar'] = $avatar['avatar'];

			if ($value['avatar'] == '') {
				$value['avatar'] = '/public/static/plugin/groups/images/avatar.jpg';
			}
			$value['avatar'] = tomedia($value['avatar']);
		}
		unset($value);
		$this->result(1,'success',array('goods'=>$goods,'order'=>$order,'teams'=>$orders));
	}

	public function orderdetail()
	{
		$mid = $this->getMemberId();
		$shopset = $this->shopset;
		$orderid = intval(input('orderid'));
		$teamid = intval(input('teamid'));
		$condition = " and mid=" . $mid . "  and id = " . $orderid . " and teamid = " . $teamid;
		$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and id = ' . $orderid)->find();
		if(empty($order)) {
			$this->result(0,'未找到订单');
		}
		$merchid = $order['merchid'];
		$refund = $option = $ladder = array();
		if( $order["refundid"] != 0 ) {
			$refund = Db::name('shop_groups_order_refund')->where('orderid = ' . $order['id'])->find();
		}
		if( $order["more_spec"] == 1 ) {
			$option = Db::name('shop_groups_order_goods')->where('groups_order_id = ' . $orderid)->find();
		}
		if( $order["is_ladder"] == 1 ) {
			$ladder = Db::name('shop_groups_ladder')->where('id = ' . $order['ladder_id'])->find();
		}
		$order['refund'] = $refund;
		$order['ladder'] = $ladder;
		$goods = Db::name('shop_groups_goods')->where('id = ' . $order['goodsid'])->find();
		if(!empty($option)) {
			$goods['optionid'] = $option['id'];
			$goods['optiontitle'] = $option['option_name'];
		}
		$goods['thumb'] = tomedia($goods['thumb']);
		$order['goods'][0] = $goods;
		$stores = array();
		$verifynum = 0;
		if( !empty($order["isverify"]) ) 
		{
			$storeids = array( );
			$merchid = 0;
			if( !empty($good["storeids"]) ) {
				$merchid = $good["merchid"];
				$storeids = array_merge(explode(",", $good["storeids"]), $storeids);
			}
			if( empty($storeids) ) {
				if( 0 < $merchid ) {
					$stores = Db::name('shop_store')->where('merchid=' . $merchid . ' and status=1 and type in(2,3)')->select();
				} else {
					$stores = Db::name('shop_store')->where(' status=1 and type in(2,3)')->select();
				}
			} else {
				if( 0 < $merchid ) {
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->select();
				} else {
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->select();
				}
			}
			$verifytotal = Db::name('shop_groups_verify')->where('orderid = ' . $order["id"] . ' and mid = ' . $mid . ' and verifycode = ' . $order["verifycode"])->count();
			if( $order["verifytype"] == 0 ) {
				$verify = Db::name('shop_groups_verify')->where('orderid = ' . $order["id"] . ' and mid = ' . $mid . ' and verifycode = ' . $order["verifycode"])->find();
			}
			$verifynum = $order["verifynum"] - $verifytotal;
			if( $verifynum < 0 ) {
				$verifynum = 0;
			}
		} else {
			$address = false;
			if( !empty($order["addressid"]) ) {
				$address = iunserializer($order["address"]);
				if( !is_array($address) ) {
					$address = Db::name('shop_member_address')->where('id = ' . $order['addressid'])->find();
				}
			}
			$order["address"] = $address;
		}
		if (0 < $merchid) {
			$merch = Db::name('shop_merch')->where('id',$merchid)->field('id,merchname,logo')->find();
		} else{
			$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
		}
		if(!empty($merch)) {
			$merch['logo'] = tomedia($merch['logo']);
		}	
		$order['verifystores'] = array('list'=>$stores,'total'=>count($stores));
		$order['verifynum'] = $verifynum;
		$carrier = @iunserializer($order["carrier"]);
		if( !is_array($carrier) || empty($carrier) ) 
		{
			$carrier = false;
		}
		switch( $order["status"] ) 
		{
			case "-1": $statusstr = "已取消";$status = 10;
			break;
			case "0": $statusstr = "待付款";$status = 21;
			break;
			case "1": if( $order["is_team"] == 0 || $order["success"] == 1 ) 
			{
				$statusstr = "待发货";$status = 30;
			} else {
				if( $order["success"] == -1 ) {
					$statusstr = "已过期，组团失败";$status = 31;
				} else {
					$statusstr = "已付款,组团中";$status = 32;
				}
			}
			break;
			case "2": $statusstr = "待收货";$status = 40;
			break;
			case "3": if(!empty($order['iscomment'])) {$statusstr = "已完成";$status = 51;} else {$statusstr = "待评价";$status = 50;}
			break;
		}
		$order['statusstr'] = $statusstr;    
		$order['status'] = $status;
		$orderprice = array(array('name'=>'商品总价','value'=>$order['price']));
        if(!empty($order['discount'])) {
        	$orderprice = array_merge($orderprice,array(array('name'=>'折扣','value'=>$order['discount'])));
        }
        if(!empty($order['freight'])) {
        	$orderprice = array_merge($orderprice,array(array('name'=>'运费','value'=>$order['freight'])));
        }

        $order['price'] = number_format($order['price']+$order['freight'],2);
        $orderprice = array_merge($orderprice,array(array('name'=>'订单总价','value'=>$order['price'])));    
        $order['orderprice'] = $orderprice;
		$order['carrier'] = $carrier;
		$log = array();
        if($order['status'] > 21) {
            if(!empty($order['paytime']) && $order['status'] >= 30) {
            	if($order['canverify']) {
            		$log[] = array('type' => 30, 'time' => $order['paytime'], 'remark' => '买家付款成功，等待核销');
            	} else {
            		$log[] = array('type' => 30, 'time' => $order['paytime'], 'remark' => '买家付款成功，等待发货(取货)');
            	}
            }
            if(!empty($order['sendtime']) && $order['status'] >= 40) {
                $log[] = array('type' => 40, 'time' => $order['sendtime'], 'remark' => '卖家已发货，待收货', 'expres' => array('expressname'=>$order['expresscom'],'express'=>$order['express'],'expresssn'=>$order['expresssn']));
            }
            if(!empty($order['finishtime']) && $order['status'] >= 50) {
                $log[] = array('type' => 50, 'time' => $order['finishtime'], 'remark' => '订单已签收，状态：交易成功');
            }
            $log[] = array('type' => 21, 'time' => $order['createtime'], 'remark' => '订单提交成功,等待付款');
        } elseif($order['status'] == 21) {
            $log[0] = array('type' => 21, 'time' => $order['createtime'], 'remark' => '待付款');
        } elseif($order['status'] == 10) {
            $log[0] = array('type' => 10, 'time' => $order['canceltime'], 'remark' => '买家取消订单或拼团未成功取消订单');
        }

        if ((0 < $order['refundstate']) && !empty($order['refundid'])) {
            $log[] = array('type' => $order['status'], 'time' => $order['refundtime'], 'remark' => $statusstr);
        }
        $order['log'] = $log;
		$merch['order'] = $order;
		$this->result(1,'success',$merch);
	}

	public function orderfinish() 
	{
		$orderid = intval(input('orderid'));
		$mid = $this->getMemberId();
		$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->find();
		if( empty($order) ) 
		{
			$this->result(0, "订单未找到");
		}
		if( $order["status"] != 2 ) 
		{
			$this->result(0, "订单不能确认收货");
		}
		if( 0 < $order["refundstate"] && !empty($order["refundid"]) ) 
		{
			$change_refund = array( );
			$change_refund["refundstatus"] = -2;
			$change_refund["refundtime"] = time();
			Db::name('shop_groups_order_refund')->where('id',$order['refundid'])->update($change_refund);
		}
		Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "status" => 3, "finishtime" => time(), "refundstate" => 0 ));
		model("notice")->sendTeamMessage($orderid);
		$this->result(1);
	}

	public function ordercancel() 
	{
		try 
		{
			$orderid = intval(input('orderid'));
			$mid = $this->getMemberId();
			$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->field('id,orderno,mid,status,credit,teamid,groupnum,creditmoney,price,freight,pay_type,discount,success')->find();
			$total = Db::name('shop_groups_order')->where('teamid = ' . $order["teamid"])->count();
			if( empty($order) ) 
			{
				$this->result(0, "订单未找到");
			}
			if( $order["status"] != 0 ) 
			{
				$this->result(0, "订单不能取消");
			}

			Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "status" => -1, "canceltime" => time() ));
			model("notice")->sendTeamMessage($orderid);
			$this->result(1);
		}
		catch( Exception $e ) 
		{
			$this->result(0, "操作失败");
		}
	}

	public function orderdelete() 
	{
		$orderid = intval(input('orderid'));
		$mid = $this->getMemberId();
		$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->field('id,status')->find();
		if( empty($order) ) 
		{
			$this->result(0, "订单未找到!");
		}
		if( $order["status"] != 3 && $order["status"] != -1 ) 
		{
			$this->result(0, "无法删除");
		}
		Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "deleted" => 1 ));
		$this->result(1);
	}

	/**
     * 订单评价
     * @global type $_POST
     * @global type $_GET
     */
	public function comment()
	{
		$mid = $this->getMemberId();
		$trade = model('common')->getSysset('trade');

		if (!empty($trade['closecomment'])) {
			$this->result(0,'不允许评论!');
		}
		$orderid = input('orderid/d');
		$order = Db::name('shop_groups_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,iscomment')->find();

		if (empty($order)) {
			$this->result(0, '订单未找到!');
		}

		if (($order['status'] != 3) && ($order['status'] != 4)) {
			$this->result(0,'订单未收货，不能评价!');
		}

		// if (2 <= $order['iscomment']) {
		// 	$this->result(0,'您已经评价过了!');
		// }

		$goods = Db::name('shop_groups_order_goods')->alias('og')->join('shop_groups_goods g','g.id=og.groups_goods_id','left')->join('shop_groups_goods_option o','o.id=og.groups_goods_option_id','left')->where('og.groups_order_id',$orderid)->field('og.id,og.groups_goods_id as goodsid,og.price,g.title,g.thumb,g.stock,og.groups_goods_option_id as optionid,ifnull(og.option_name,"") as optiontitle')->select();
		$goods = set_medias($goods, 'thumb');
		foreach ($goods as &$row) {
			$goodscomment = Db::name('shop_groups_order_comment')->where('orderid', $orderid)->where('goodsid', $row['goodsid'])->where('optionid', $row['optionid'])->find();
			if(!empty($goodscomment)) {
				$goodscomment['images'] = array_values(iunserializer($goodscomment['images']));
				$goodscomment['images'] = set_medias($goodscomment['images']);
				$goodscomment['append_images'] = array_values(iunserializer($goodscomment['append_images']));
				$goodscomment['append_images'] = set_medias($goodscomment['append_images']);
				$goodscomment['append_reply_images'] = array_values(iunserializer($goodscomment['append_reply_images']));
				$goodscomment['append_reply_images'] = set_medias($goodscomment['append_reply_images']);
			}			
        	$row['comment'] = $goodscomment ? $goodscomment : (object) NULL;
		}
		$this->result(1,'success',array('order'=>$order,'goods'=>$goods));
	}

	/**
     * 提交订单评价
     * @global type $_POST
     * @global type $_GET
     */
	public function commentsubmit()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$order = Db::name('shop_groups_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,iscomment')->find();
		if (empty($order)) {
			$this->result(0, '订单未找到');
		}
		if (($order['status'] != 3) && ($order['status'] != 4)) {
			$this->result(0,'订单未收货，不能评价!');
		}

		if (2 <= $order['iscomment']) {
			$this->result(0,'您已经评价过了!');
		}

		$member = model('member')->getMember($mid);
		$comments = '';
        if (Request::instance()->has('comments')) {
            $comments = $_POST['comments'];
        } 
        $comments = json_decode($comments,true);
        if (empty($comments) || !(is_array($comments))) 
        {
            $this->result(0, '数据出错，请重试!');
        }

        $trade = model('common')->getSysset('trade');

		if (!empty($trade['commentchecked'])) {
			$checked = 0;
		} else {
			$checked = 1;
		}
		Db::startTrans();
		try{
			$thumbs = '';
	        if(!empty($comments['thumbs']))
	        {
	            $thumbs = iserializer($comments['thumbs']);
	        }
			$comment = array('orderid' => $orderid, 'goodsid' => intval($comments['goodsid']), 'optionid' => intval($comments['optionid']), 'level' => intval($comments['level']), 'content' => trim($comments['content']), 'images' => $thumbs, 'mid' => $mid, 'nickname' => $member['nickname'], 'headimgurl' => $member['avatar'], 'createtime' => time(), 'checked' => $checked, 'isanonymous' => intval($comments['isanonymous']));

			$id = Db::name('shop_groups_order_comment')->insertGetId($comment);
			Db::name('shop_groups_order')->where('id',$orderid)->update(array('iscomment'=>2));
			$goods = Db::name('shop_groups_order_goods')->alias('og')->join('shop_groups_goods g','g.id=og.groups_goods_id','left')->join('shop_groups_goods_option o','o.id=og.groups_goods_option_id','left')->where('og.groups_order_id',$orderid)->where('og.groups_goods_id', intval($comments['goodsid']))->where('og.groups_goods_option_id', intval($comments['optionid']))->field('og.id,og.groups_goods_id  as goodsid,og.price,g.title,g.thumb,o.stock,og.groups_goods_option_id,ifnull(og.option_name,"") as optiontitle')->find();
			$goods['thumb'] = tomedia($goods['thumb']);
	        $goodscomment = Db::name('shop_groups_order_comment')->where('id', $id)->find();
	        if(!empty($goodscomment)) {
				$goodscomment['images'] = array_values(iunserializer($goodscomment['images']));
				$goodscomment['images'] = set_medias($goodscomment['images']);
				$goodscomment['append_images'] = array_values(iunserializer($goodscomment['append_images']));
				$goodscomment['append_images'] = set_medias($goodscomment['append_images']);
				$goodscomment['append_reply_images'] = array_values(iunserializer($goodscomment['append_reply_images']));
				$goodscomment['append_reply_images'] = set_medias($goodscomment['append_reply_images']);
				$goods['comment'] = $goodscomment;
	        }
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
        
		$this->result(1,'success',$goods);
	}

	public function rules()
	{
		$set = Db::name('shop_groups_set')->find();
		$this->assign(['set'=>$set]);
		return $this->fetch('groups/rules/index');
	}


}