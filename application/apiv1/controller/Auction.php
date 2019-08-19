<?php
/**
 * apiv1 拍卖
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
use think\Exception;
use think\Queue;
class Auction extends Base
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

        $set = model('common')->getPluginset('auction');
        if(!empty($set['openauction']) && $set['openauction'] == 1)
        {
        	$this->result(0,'未开启拍卖');
        }
        $this->set = $set;
    }

	/**
	 * 拍卖首页
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function index()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$cateid = input('cateid');
		$formtime = input('formtime/s','sale');
		$banner = array();
		$category = array();
		$goods = array();
		if($page == 1) {
			$banner = Db::name('shop_auction_banner')->where('enabled',1)->order('displayorder','desc')->field('enabled,displayorder',true)->select();
			$banner = set_medias($banner, 'thumb');
			$category = Db::name('shop_auction_goods_category')->where('enabled=1')->order('displayorder desc')->field('id,name,thumb')->select();
			$category = set_medias($category, 'thumb');
		}

		$condition = ' deleted = 0 ';
		if(!empty($formtime) && in_array($formtime, array('sale','wait','finish'))) {
			if($formtime == 'sale') {
				$condition .= " and status = 1 and dealmid = 0 and starttime < " . time() . " and endtime > " . time();
			} else {
				if($formtime == 'wait') {
					$condition .= " and status = 1 and dealmid = 0 and starttime > " . time();
				} else {
					if($formtime == 'finish') {
						$condition .= " and status = 1 and dealmid <> 0 ";
					}
				}
			}
		}
		
		if(!empty($cateid))	{
			$cateid = intval($cateid);
			$condition .= ' and category = ' . $cateid;
		}
		$goods = Db::name('shop_auction_goods')->field('*')->where($condition)->order('displayorder desc,id DESC')->page($page,$pagesize)->select();
		foreach ($goods as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			$row['resttime'] = $row['endtime'] - $row['starttime'];
			$row['bili'] = (time() - $row['starttime']) / ($row['endtime'] - $row['starttime']) * 100;
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
	public function goodsdetail()
	{
		if (empty(input('id'))) {
	        $this->result(0,'抱歉，参数错误！');
	    }
		$id = intval(input('id'));
		$goods = Db::name('shop_auction_goods')->where('id',$id)->find();
		if($goods['endtime']!=$goods['starttime']){
			$goods['bili'] = (time()-$goods['start_time'])/($goods['end_time']-$goods['start_time'])*100;
		}
		if ($goods['bili']>100) {
			$goods['bili'] = 100;
		}
		$set = $this->set;
		$goods['explain'] = $set['explain'];
		$page = 1;
		$pagesize = 10;
		$records = Db::name('shop_auction_record')->where('goodsid = ' . $id)->order('createtime desc')->page($page,$pagesize)->select();
		$recordlist = array('list'=>$records,'total'=>count($records),'page'=>$page,'pagesize'=>$pagesize);
		$goods['records'] = $recordlist;
		$this->result(1,'success',$goods);
	}

	/**
	 * 出价记录
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function recordlist()
	{
		if (empty(input('goodsid'))) {
	        $this->result(0,'抱歉，参数错误！');
	    }
	    $page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$goodsid = intval(input('goodsid'));
		$records = Db::name('shop_auction_record')->where('goodsid = ' . $goodsid)->order('createtime desc')->page($page,$pagesize)->select();
		$recordlist = array('list'=>$records,'total'=>count($records),'page'=>$page,'pagesize'=>$pagesize);
		$this->result(1,'success',$recordlist);
	}

	/**
	 * 商品出价
	 * @param $id [int]
	 * @return  [array]    $data  []
	 **/
	public function bidding()
	{
		if (empty(input('id'))) {
	        $this->result(0,'抱歉，参数错误！');
	    }
	    $mid = $this->getMemberId();
		$goodsid = intval(input('id'));
		$addprice = floatval(input('addprice'));
		$bond = floatval(input('bond'));
		$paytype = input('paytype/d');
		$record = Db::name('shop_auction_record')->where('mid = ' . $mid . ' and goodsid = ' . $goodsid . ' and status > 0')->find();
		$member = model('member')->getMember($mid);
		$goods = Db::name('shop_auction_goods')->where('id',$goodsid)->find();
		$ordersn = model('common')->createNO('shop_auction_record','ordersn','AU');
		$nowrecord = Db::name('shop_auction_record')->where('mid = ' . $mid . ' and goodsid = ' . $goodsid . ' and status > 0 and addprice <= ' . $addprice)->count();
		if($goods['status'] != 1 || $goods['deleted'] != 0 || !empty($goods['dealmid']) || $goods['starttime'] > time() || $goods['endtime'] < time()) {
			$this->result(0,'商品信息错误');
		}
		if ($nowrecord > 0) {
			$this->result(0,'您已出价，请勿重复出价！');
		}
		if (empty($addprice) || (!empty($addprice) && ($addprice < $goods['shprice']))) {
			$this->result(0,'起拍价为' . $goods['shprice']);
		}
		if (empty($record) && ((empty($bond) || $bond != $goods['bond']) && !empty($goods['bond']))) {
			$this->result(0,'请缴纳保证金' . $goods['bond']);
		}
		if($addprice < $goods['stprice']) {
			$this->result(0,'当前加价小于最低加价！');
		}
		if(($addprice - $goods['stprice']) < $goods['addprice']) {
			$this->result(0,'加价幅度最少' . $goods['addprice']);
		}
		Db::name('shop_auction_record')->where('goodsid = ' . $goodsid  . ' and mid = ' . $mid . ' and status = 0')->delete();
		try{
			if ($record) {
				$price = $addprice;
				$data=array(
					'mid'=>$mid,
					'nickname'=>$member['nickname'],
					'goodsid'=>$goodsid,
					'ordersn'=>$ordersn,
					'price'=>$price,
					'addprice'=>$addprice,
					'paytype'=>$paytype,
					'createtime' => time(),
					);
				$recordid = Db::name('shop_auction_record')->insertGetId($data);
			} else {		
				$price = $addprice + $bond + $goods['shprice'];	
				$data=array(
					'mid'=>$mid,
					'nickname'=>$member['nickname'],
					'goodsid'=>$goodsid,
					'ordersn'=>$ordersn,
					'price'=>$price,
					'addprice'=>$addprice,
					'bond'=>$bond,
					'paytype'=>$paytype,
					'createtime' => time(),
					);
				$recordid = Db::name('shop_auction_record')->insertGetId($data);
			}
		}catch(\Exception $e){
		    $this->result(0,'出价失败');
		}
		if(empty($recordid)) {
			$this->result(0,'出价失败');
		}
		$payment = input('payment/s') ? input('payment/s') : 'app';
		$data = json_encode($data);
		Queue::push( 'application\apiv1\job\Hello' , $data , '拍卖队列' . $recordid );	
		return $this->redirect(url('apiv1/auction/pay',['recordid' => $recordid,'payment' => $payment]));
	}

	/**
	 * 拍卖-提交订单支付
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function pay()
    {
    	$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$credit = $member["credit1"];
		$money = $member["credit2"];
		$recordid = input('recordid/d');

		$order = Db::name('shop_auction_record')->where('id',$recordid)->where('mid',$mid)->find();
		if(empty($order)) {
			$this->result(0,'出价记录不存在');
		}
		$goods = Db::name('shop_auction_goods')->where('id',$order['goodsid'])->find();
		if($goods['status'] != 1 || $goods['deleted'] != 0 || !empty($goods['dealmid']) || $goods['starttime'] > time() || $goods['endtime'] < time()) {
			$this->result(0,'商品信息错误');
		}
    	$headerinfo = $this->headerinfo;
		if(!in_array($headerinfo['device-type'], array('iOS','android','wechat','web'))) {
			$this->result(0,'支付环境出错!');
		}
		$log = Db::name('shop_core_paylog')->where('module','auction')->where('tid',$order['ordersn'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->result(0,'订单已付款');
			exit();
		}
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'auction', 'tid' => $order['ordersn'], 'fee' => $order["price"], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}
		$paytype = input('paytype/d') ? input('paytype/d') : $order['paytype'];
		$payment = input('payment/s') ? input('payment/s') : 'app';
		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$set = model("common")->getSysset();
		$headerinfo = $this->headerinfo;
		if(!in_array($headerinfo['device-type'], array('iOS','android','wechat','web'))) {
			$this->result(0,'支付出错!');
		}
		if( $paytype == 3 ) {
			if( $order["price"] <= $money ) {
				$paystatus = 3;
			} else {
				$this->result(0, "余额不足!");
			}
			Db::name('shop_auction_record')->where('id',$recordid)->update(array( "paytype" => $paystatus ));
		} else {
			if( $paytype == 1 ) {
				$paystatus = 1;
				Db::name('shop_auction_record')->where('id',$recordid)->update(array( "paytype" => $paystatus ));
				if( empty($set["pay"]["app_wechat"]) ) {
					$this->result(0, "未开启微信支付!");
				}
				$wechat = array( "success" => false );
				$params = array( );
				$params["tid"] = $order["ordersn"];
				$params["user"] = $mid;
				$params['product_id'] = $recordid;
				$params["fee"] = $order["price"];
				$params["title"] = $set["shop"]["name"] . '参与竞拍' . " 单号:" . $order["ordersn"];
				if( isset($set["pay"]) && $set["pay"]["app_wechat"] == 1) {
					$wechat = model('payment')->wechat_build($params, 'web', 3, 'web');
					if (!is_array($wechat)) {
						$this->result(0,$wechat);
					}
				}
				$wechat['product_id'] = $recordid;				
				$this->result(1,'success',$wechat);
			} else {
				if( $paytype == 2 ) {
					$paystatus = 2;
					Db::name('shop_auction_record')->where('id',$recordid)->update(array( "paytype" => $paystatus ));
					$ordersnother = str_replace("EE", "EP", $order["ordersn"]);
					$params = array( );
					$params["tid"] = $order["ordersn"];
					$params['product_id'] = $recordid;
					$params["user"] = $mid;
					$params["fee"] = $order["price"];
					$params["title"] = $set["shop"]["name"] . '参与竞拍' . " 单号:" . $order["ordersn"];
					if( isset($set["pay"]) && $set["pay"]["app_alipay"] == 1 ) 
					{
						$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 3, getHttpHost() . '/public/dist/order','app');
						if (empty($alipay)) {
							$this->result(0,'参数错误');
						}
					}
					$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$recordid));
				}
			}
		}
		$this->result(0, "支付出错!");
    }

}