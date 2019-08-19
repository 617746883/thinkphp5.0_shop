<?php
/**
 * 夺宝
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\mobile\controller;
use think\Db;
use think\Request;
class Auction extends Base
{
	public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $set = model('common')->getPluginset('auction');
        if(!empty($set) && !empty($set['openauction']))
        {
        	$this->result(0, '拍卖未开启','');
        }
        $this->set = $set;
    }

	public function index()
	{
		$banners = Db::name('shop_auction_banner')->where('enabled = 1')->select();
		foreach ($banners as &$banner) {
			if (substr($banner['link'], 0, 5) != 'http:') {
				$banner['link'] = "http://" . $banner['link'];
			}
		}
		unset($banner);
		$category = Db::query("SELECT * FROM " . tablename('shop_auction_goods_category') . " WHERE enabled=1 ORDER BY displayorder DESC");
		$nowtime = time();
		$contion =' deleted = 0 ';
		$gid = intval(input('gid'));
		if (!empty($gid)) {
			$contion .= "and category = '{$gid}'";
		}
		$type = input('type/s','sale');
		switch( $type ) {
			case "sale": $contion .= " and status = 1 and dealmid = 0 and starttime < " . time() . " and endtime > " . time();
			break;
			case "wait": $contion .= " and status = 1 and dealmid = 0 and starttime > " . time();
			break;
			case "finish": $contion .= " and status = 1 and dealmid <> 0 ";
			break;
			default: $contion .= " ";
		}
		$list = Db::name('shop_auction_goods')->where($contion)->order('id DESC')->limit(1,10)->select();
		foreach ($list as $key => $value) {
			$list[$key]['bili'] = (time()-$value['starttime'])/($value['endtime']-$value['starttime'])*100;
		}
		$this->assign(['banners'=>$banners,'category'=>$category,'list'=>$list]);
		return $this->fetch('');
	}

	public function getlist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$nowtime = time();
		$contion =' deleted = 0 ';
		$gid = intval(input('gid'));
		if (!empty($gid)) {
			$contion .= "and category = '{$gid}'";
		}
		$type = input('type/s','sale');
		switch( $type ) {
			case "sale": $contion .= " and status = 1 and dealmid = 0 and starttime < " . time() . " and endtime > " . time();
			break;
			case "wait": $contion .= " and status = 1 and dealmid = 0 and starttime > " . time();
			break;
			case "finish": $contion .= " and status = 1 and dealmid <> 0 ";
			break;
			default: $contion .= " ";
		}
		$list = Db::name('shop_auction_goods')->where($contion)->order('id DESC')->page($page,$pagesize)->select();
		foreach ($list as $key => $value) {
			$list[$key]['bili'] = (time()-$value['starttime'])/($value['endtime']-$value['starttime'])*100;
		}
		$this->assign(['banners'=>$banners,'category'=>$category,'list'=>$list]);
		return $this->fetch('');
	}

	public function detail()
	{
		$mid = $this->getMemberId();
		if (empty(input('id'))) {
	        $this->error('抱歉，参数错误！');
	    }
		$id = intval(input('id'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $id)->find();
		if($goods['endtime']!=$goods['starttime']){
		$goods['bili'] = (time()-$goods['starttime'])/($goods['endtime']-$goods['starttime'])*100;
		}
		if ($goods['bili']>100) {
			$goods['bili'] = 100;
		}
		if( !empty($goods["thumb"]) ) {
			$goods["thumb_url"] = iunserializer($goods["thumb_url"]);
		}
		$list = Db::name('shop_auction_record')->alias('r')->join('member m','m.id = r.mid','left')->where('r.goodsid = ' . $id . ' and r.status = 1')->field('r.*,m.nickname')->order('createtime DESC')->limit(10)->select();
		$bondorder = Db::name('shop_auction_bondorder')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'] . ' and status = 1 and paytime > 0')->find(); 
		$this->assign(['goods'=>$goods,'list'=>$list,'bondorder'=>$bondorder]);
		return $this->fetch('auction/goods/detail');
	}

	public function bondpay()
	{
		$mid = $this->getMemberId();
		$goodsid = intval(input('id'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $goodsid)->find();
		if(empty($goods)) {
			$this->error('您访问的信息不存在');
		}
		if($goods['status'] != 1 || !empty($goods['dealmid']) || $goods['starttime'] >= time() || $goods['endtime'] <= time()) {
			$this->error('此商品不可参与竞拍');
		}
		$bondorder = Db::name('shop_auction_bondorder')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'])->find();
		if(!empty($bondorder)) {
			if($bondorder['status'] == 1 && $bondorder['paytime'] > 0) {
				$this->error('您已缴纳保证金');
			} else {
				Db::name('shop_auction_bondorder')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'])->delete();
			}
		}
		$ordersn = model('common')->createNO('shop_auction_bondorder','ordersn','AUB');	
		$data = array('ordersn'=>$ordersn, 'mid'=>$mid, 'goodsid'=>$goods['id'],'price'=>$goods['bond'], 'createtime' => time());

		$id = Db::name('shop_auction_bondorder')->insertGetId($data);
		if(empty($id)) {
			$this->error('缴纳失败，请重试');
		}
		$member = model('member')->getMember($mid);
		$log = Db::name('shop_core_paylog')->where('module','auction_bond')->where('tid',$data['ordersn'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->error('订单已付款');
			exit();
		}
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'auction_bond', 'tid' => $data['ordersn'], 'fee' => $data['price'], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}

		$set = model('common')->getSysset(array('shop', 'pay'));
		$param_title = $set['shop']['name'] . '订单';
		$credit = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['credit'] == 1)) {
			$credit = array('success' => true, 'current' => $member['credit2']);
		}
		$cash = array('success' => false);
		$cash = array('success' => isset($set['pay']) && ($set['pay']['cash'] == 1));
		$data['price'] = floatval($data['price']);

		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$wechat = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['product_id'] = $id;
		$params['user'] = $mid;
		$params['fee'] = $data['price'];
		$params['title'] = $param_title;
		$wechat = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
			if(is_weixin()) {
				$type = 'wechat';
			} else {
				$type = 'web';
			}
			$wechat = model('payment')->wechat_build($params, 'web', 5, $type, '{"h5_info": {"type":"Wap","wap_url": "http://aoao.doncheng.cn","wap_name": "订单支付"}}');
			if (!is_array($wechat)) {
				$wechat = array('success' => false);
			}
		}
		$alipay = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
			$alipay = model('payment')->alipay_build($params, 'web', 5, getHttpHost() . '/public/dist/order','web');
			if (empty($alipay)) {
				$alipay = array('success' => false);
			}
		}
		$payinfo = array('orderid' => $id, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $data['price']);
		$this->assign(['data'=>$data,'goods'=>$goods,'wechat'=>$wechat,'alipay'=>$alipay,'credit'=>$credit,'cash'=>$cash,'payinfo'=>$payinfo]);
		return $this->fetch('auction/order/bondpay');
	}

	public function bidconfirm()
	{
		if (empty(input('id'))) {
	        $this->error('抱歉，参数错误！');
	    }
		$id = intval(input('id'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $id)->find();
		if(empty($goods)) {
			$this->error('您访问的信息不存在');
		}
		if($goods['status'] != 1 || !empty($goods['dealmid']) || $goods['starttime'] >= time() || $goods['endtime'] <= time()) {
			$this->error('此商品不可参与竞拍');
		}
	    $mid = $this->getMemberId();
	    $member = model('member')->getMember($mid);
	    $bond = Db::name('shop_auction_bondorder')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'] . ' and status = 1 and paytime > 0')->count();
	    if(!$bond) {
	    	$this->error('请缴纳保证金');
	    } 
	    $record = Db::name('shop_auction_record')->where('goodsid = ' . $id . ' and status > 0')->order('createtime DESC')->field('id')->select();
		$myrecord = Db::name('shop_auction_record')->where('goodsid = ' . $id . ' and status > 0 and mid = ' . $mid)->order('createtime DESC')->count();
		$this->assign(['goods'=>$goods,'bond'=>$bond,'record'=>$record,'member'=>$member]);
	    return $this->fetch('auction/order/bid');
	}

	public function bidsubmit()
	{
		$mid = $this->getMemberId();
		$goodsid = intval(input('id'));
		if(empty($goodsid)) {
			$this->error('抱歉，参数错误！');
		}

		$oneChangeNum = floatval(input('oneChangeNum'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $goodsid)->find();
		if($goods['status'] != 1 || !empty($goods['dealmid']) || $goods['starttime'] >= time() || $goods['endtime'] <= time()) {
			$this->error('此商品不可参与竞拍');
		}
		$myrecord = Db::name('shop_auction_record')->where(' goodsid = ' . $goodsid . ' and status > 0')->count();
		$experience = $myrecord ? ($goods['stprice'] + $goods['addprice']) : $goods['stprice'];
		if ($oneChangeNum < $experience) {
			$this->error('当前加价小于最低加价！');
		}
		$nowrecord = Db::name('shop_auction_record')->where('mid = ' . $mid . ' and goodsid = ' . $goodsid . ' and status > 0 and price >= ' . $oneChangeNum)->count();
		if ($nowrecord) {
			$this->error('您已出价，请勿重复出价！');
		}

		$member = model('member')->getMember($mid);
		$recordsn = model('common')->createNO('shop_auction_record','recordsn','AU');
		$data = array('recordsn' => $recordsn, 'mid' => $mid, 'goodsid' => $goods['id'], 'price' => $oneChangeNum, 'createtime' => time(), 'status' => 1);
		$recordid = Db::name('shop_auction_record')->insertGetId($data);
		if(empty($recordid)) {
			$this->error('出价失败！');
		}
		$s_data=array( 'stprice'=>$oneChangeNum, 'pos' => $goods['pos'] + 1);
		Db::name('shop_auction_goods')->where('id = ' . $goods['id'])->update($s_data);
		model('notice')->sendAuctionBid($recordid);
		$this->success('出价成功！',url('mobile/auction/record'));
	}

	public function record()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$ar = Db::name('shop_auction_record')->where('mid = ' . $mid . ' and status > 0')->order('createtime desc')->select();
		$number=0;
		foreach($ar as $key => $value) {
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
		$this->assign(['p_record'=>$p_record]);
		return $this->fetch('auction/order/record');
	}

	public function recorddetail()
	{
		if (empty(input('goodsid'))) {
		    $this->error('抱歉，参数错误！');
		}
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$id = intval(input('goodsid'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $id)->find();
		if ($goods['endtime']<time()) {
		  	$goods['state']='已结束';
	  		$redata = Db::name('shop_auction_record')->where('goodsid = ' . $id)->order('createtime desc')->find();
		  	if (empty($goods['dealmid'])) {
		  		$data['dealmid']=$redata['mid'];
		  		Db::name('shop_auction_goods')->where('id = ' . $id)->update($data);
		  	}
		  	$redatamember = model('member')->getMember($redata['mid']);
		  	$goods['dealmember'] = $redatamember['nickname'];
		} else {
			$goods['state']='进行中';
		}
		$records = Db::name('shop_auction_record')->where('goodsid = ' . $id . ' and mid = ' . $mid )->order('createtime desc')->select();
		$this->assign(['goods'=>$goods,'records'=>$records]);
		return $this->fetch('auction/order/recorddetail');
	}

	public function myauction()
	{		
		$mid = $this->getMemberId();
		$myauction = Db::name('shop_auction_goods')->where('dealmid = ' . $mid)->order('endtime desc')->select();
		foreach ($myauction as &$val) {
			$order = Db::name('shop_auction_order')->where('mid = ' . $mid . ' and goodsid = ' . $val['id'])->find();
			$val['order'] = $order;
		}
		unset($val);
		$this->assign(['myauction'=>$myauction]);
		return $this->fetch('auction/order/myauction');
	}

	public function confirm()
	{
		$mid = $this->getMemberId();
		$goodsid = intval(input('id'));
		$price = floatval(input('price'));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $goodsid)->find();
		if(empty($goods)) {
			$this->error('您访问的信息不存在');
		}
		if(empty($goods['dealmid']) || $goods['dealmid'] != $mid) {
			$this->error('此商品未拍卖成功');
		}

		$address = Db::name('shop_member_address')->where('mid = ' . $mid . ' and deleted = 0 and isdefault = 1 ')->find();
		$this->assign(['goods'=>$goods,'address'=>$address,'price'=>$price]);
		return $this->fetch('auction/order/confirm');
	}

	public function pay()
	{
		$mid = $this->getMemberId();
		$goodsid = intval(input('id'));
		$price = floatval(input('price'));
		$aid = intval(input('aid'));
		$remark = trim(input('remark/s',''));
		$goods = Db::name('shop_auction_goods')->where('id = ' . $goodsid)->find();
		if(empty($goods)) {
			$this->error('您访问的信息不存在');
		}
		if(empty($goods['dealmid']) || $goods['dealmid'] != $mid) {
			$this->error('此商品未拍卖成功');
		}
		if(empty($aid)) {
			$this->error('请选择地址');
		}

		$address = Db::name('shop_member_address')->where('mid = ' . $mid . ' and id = ' . $aid)->find();
		if( empty($address) ) 
		{
			$this->error("未找到地址");
		}
		if( empty($address["province"]) || empty($address["city"]) ) 
		{
			$this->error("地址请选择省市信息");
		}

		$order = Db::name('shop_auction_order')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'])->find();
		if(empty($order)) {
			$ordersn = model('common')->createNO('shop_auction_bondorder','ordersn','AUB');	
			$order = array('ordersn'=>$ordersn, 'mid'=>$mid, 'goodsid'=>$goods['id'], 'price'=>$price, 'bondprice'=>$goods['bond'], 'stprice'=>$goods['stprice'], 'remark'=>$remark, 'createtime'=>time());
			if(empty($address)) {
				$order['addressid'] = $address['id'];
				$order['address'] = iserializer($address);
			}
			$orderid = Db::name('shop_auction_order')->insertGetId($order);
			if(empty($orderid)) {
				$this->error('支付失败，请重试');
			}
		} else {
			$uporder['remark'] = $remark;
			if(empty($address)) {
				$uporder['addressid'] = $address['id'];
				$uporder['address'] = iserializer($address);
			}
			Db::name('shop_auction_order')->where('id = ' . $order['id'])->update($uporder);
			$orderid = $order['id']; 
		}
		
		$member = model('member')->getMember($mid);
		$log = Db::name('shop_core_paylog')->where('module','auction')->where('tid',$order['ordersn'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->error('订单已付款');
			exit();
		}
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'auction', 'tid' => $order['ordersn'], 'fee' => $order['price'], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}

		$set = model('common')->getSysset(array('shop', 'pay'));
		$param_title = $set['shop']['name'] . '订单';
		$credit = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['credit'] == 1)) {
			$credit = array('success' => true, 'current' => $member['credit2']);
		}
		$cash = array('success' => false);
		$cash = array('success' => isset($set['pay']) && ($set['pay']['cash'] == 1));
		$order['price'] = floatval($order['price']);

		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$wechat = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['product_id'] = $orderid;
		$params['user'] = $mid;
		$params['fee'] = $order['price'];
		$params['title'] = $param_title;
		$wechat = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
			if(is_weixin()) {
				$type = 'wechat';
			} else {
				if(is_mobile()) {
					$type = 'web';
				}
			}
			$wechat = model('payment')->wechat_build($params, 'web', 3, $type, '{"h5_info": {"type":"Wap","wap_url": "http://aoao.doncheng.cn","wap_name": "订单支付"}}');
			if (!is_array($wechat)) {
				$wechat = array('success' => false);
			}
		}
		$alipay = array('success' => false);
		if (isset($set['pay']) && ($set['pay']['app_alipay'] == 1)) {
			$alipay = model('payment')->alipay_build($params, 'web', 3, getHttpHost() . '/mobile/auction/myauction','web');
			if (empty($alipay)) {
				$alipay = array('success' => false);
			}
		}
		$payinfo = array('orderid' => $orderid, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $order['price']);
		$this->assign(['order'=>$order,'goods'=>$goods,'wechat'=>$wechat,'alipay'=>$alipay,'credit'=>$credit,'cash'=>$cash,'payinfo'=>$payinfo,'address'=>$address]);
		return $this->fetch('auction/order/pay');
	}

	public function rules()
	{
		$data = model('common')->getPluginset('auction');
		$this->assign(['set'=>$data]);
		return $this->fetch('auction/rules/index');
	}
	
}