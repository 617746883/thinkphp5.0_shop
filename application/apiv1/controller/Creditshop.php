<?php
/**
 * apiv1 购物车
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Creditshop extends Base
{
	protected static $token;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $set = model('Common')->getPluginset('creditshop');
        if(!empty($set) && !empty($set['opencreditshop']))
        {
        	$this->result(0, '积分商城未开启','');
        }
        $this->set = $set;
    }

    /**
	 * 积分商城首页
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function index()
    {    	
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$shopset = $this->shopset;
    	$merchid = intval(input('merchid'));
    	$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		if($page <= 1) {
			$condition = ' 1 ';
			if (0 < intval($merchid) && $is_openmerch == 1) {
				$contation .= 'and merchid = ' . intval($merchid) . ' ';
			}
	    	$banner = Db::name('shop_creditshop_banner')->where($condition . ' and enabled=1 ')->order('displayorder','desc')->field('id,bannername,link,thumb')->select();
	    	if(!empty($banner)) {
	    		$banner = set_medias($banner,'thumb');
	    	}
	    	$category = array();

			if (0 < intval($merchid) && $is_openmerch == 1) {
				$merch_category = model('merch')->getSet('merch_creditshop_category', $merchid);

				if (!empty($merch_category)) {
					$i = 0;
					foreach ($merch_category as $index => $row) {
						if (0 < $row) {
							$list = Db::name('shop_creditshop_goods_category')->where('id = ' . $index . ' enabled=1')->field('id,name,thumb,isrecommand')->order('displayorder','desc')->select();
							$list = set_medias($list, 'thumb');
							$category[$i] = $list;
							++$i;
						}
					}
				}
			} else {
				$category = Db::name('shop_creditshop_goods_category')->where('enabled=1')->field('id,name,thumb,isrecommand')->order('displayorder','desc')->select();
				$category = set_medias($category, 'thumb');
			}
			array_values($category);
		}		

		$cate = input('cate/d');
		$order_key = input('order_key','');
        $order_method = input('order_method','');
		$goodscondition = 'status=1 and deleted=0';
		if (!empty($cate)) {
            $goodscondition .= 'cate = ' . $cate;
        }
        $keywords = trim(input('keywords'));
		if (!empty($keywords)) {
			$goodscondition .= ' AND title like \'%' . $keywords . '%\' ';
		}
		if (0 < $merchid && $is_openmerch == 1) {
			$goodscondition .= ' and merchid = ' . $merchid . ' ';
		}
        if (!empty($order_key) && !empty($order_method)) {
            $sort = $order_key. " " .$order_method;           
        } else {
            $sort = "displayorder desc";
        }
    	$goodslist = Db::name('shop_creditshop_goods')->where($goodscondition)->order($sort)->field("id,title,thumb,price,credit,total,money,type")->page($page,$pagesize)->select();
    	if(!empty($goodslist)) {
    		$goodslist = set_medias($goodslist,'thumb');
    	}
    	if($page <= 1) {
    		$this->result(1,'success',array('credit' => $member['credit1'], 'banner'=>$banner, 'category'=>$category, 'goods'=>array('list'=>$goodslist,'page'=>$page,'pagesize'=>$pagesize)));
    	}
    	$this->result(1,'success',array('goods'=>array('list'=>$goodslist,'page'=>$page,'pagesize'=>$pagesize)));
    }

    /**
	 * 积分商城-商品详情
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function goodsdetail()
    {
    	$mid = $this->getMemberId();
		$id = intval(input('id'));
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}

		$merchid = intval(input('merchid'));
		if (!$id) {
			$this->result(0,'该商品不存在或已删除!');
		}

		$shop = model('common')->getSysset('shop');
		$member = model('member')->getMember($mid);
		$goods = model('creditshop')->getGoods($id, $member);

		if (empty($goods)) {
			$this->result(0,'该商品不存在或已删除!');
		}

		$showgoods = model('goods')->visit($goods, $member);

		if (empty($showgoods)) {
			$this->result(0,'您没有权限浏览此商品!');
		}

		$pay = model('common')->getSysset('pay');
		$set = $this->set;		
		$goods['subdetail'] = $goods['subdetail'];
		$goods['noticedetail'] = $goods['noticedetail'];
		$goods['usedetail'] = $goods['usedetail'];
		$goods['goodsdetail'] = $goods['goodsdetail'];
		$credit = $member['credit1'];
		$money = $member['credit2'];

		if (!empty($goods)) {
			Db::name('shop_creditshop_goods')->where('id',$id)->setInc('views');
		} else {
			$this->result(0,'商品已下架或被删除!');
		}

		$log = array();
		$log = Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and status > 0 ')->order('createtime','desc')->field('mid,createtime')->limit(2)->select();

		foreach ($log as $key => $value) {
			$mem = model('member')->getMember($value['mid']);
			$log[$key]['avatar'] = $mem['avatar'];
			$log[$key]['nickname'] = $mem['nickname'];
			$log[$key]['createtime_str'] = date('Y/m/d H:i', $value['createtime']);
			unset($mem);
		}

		$logtotal = 0;
		$logtotal = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and status > 0 ')->count();
		$replys = array();
		$replys = Db::name('shop_creditshop_comment')->where('goodsid = ' . $id . ' and checked = 1 and deleted = 0')->order('time','desc')->limit(2)->select();
		$replykeywords = explode(',', $set['desckeyword']);
		$replykeystr = trim($set['replykeyword']);

		if (empty($replykeystr)) {
			$replykeystr = '**';
		}

		foreach ($replys as $key => $value) {
			foreach ($replykeywords as $k => $val) {
				if (!empty($value['content'])) {
					if (!strstr($val, $value['content'])) {
						$value['content'] = str_replace($val, $replykeystr, $value['content']);
					}
				}

				if (!empty($value['reply_content'])) {
					if (!strstr($val, $value['reply_content'])) {
						$value['reply_content'] = str_replace($val, $replykeystr, $value['reply_content']);
					}
				}

				if (!empty($value['append_content'])) {
					if (!strstr($val, $value['append_content'])) {
						$value['append_content'] = str_replace($val, $replykeystr, $value['append_content']);
					}
				}

				if (!empty($value['append_reply_content'])) {
					if (!strstr($val, $value['append_reply_content'])) {
						$value['append_reply_content'] = str_replace($val, $replykeystr, $value['append_reply_content']);
					}
				}
			}

			$replys[$key]['content'] = $value['content'];
			$replys[$key]['reply_content'] = $value['reply_content'];
			$replys[$key]['append_content'] = $value['append_content'];
			$replys[$key]['append_reply_content'] = $value['append_reply_content'];
			$replys[$key]['time_str'] = date('Y/m/d', $value['time']);
			$replys[$key]['images'] = set_medias(iunserializer($value['images']));
			$replys[$key]['reply_images'] = set_medias(iunserializer($value['reply_images']));
			$replys[$key]['append_images'] = set_medias(iunserializer($value['append_images']));
			$replys[$key]['append_reply_images'] = set_medias(iunserializer($value['append_reply_images']));
			$replys[$key]['nickname'] = cut_str($value['nickname'], 1, 0) . '**' . cut_str($value['nickname'], 1, -1);
			$replys[$key]['content'] = str_replace('=', '**', $value['content']);
		}

		$replytotal = 0;
		$replytotal = Db::name('shop_creditshop_comment')->where('goodsid = ' . $id . ' and checked = 1 and deleted = 0')->order('time','desc')->count();

		if ($goods['goodstype'] == 0) {
			$stores = array();

			if (!empty($goods['isverify'])) {
				$storeids = array();

				if (!empty($goods['storeids'])) {
					$storeids = array_merge(explode(',', $goods['storeids']), $storeids);
				}

				if (empty($storeids)) {
					if (0 < $merchid) {
						$stores = Db::name('shop_store')->where('id = ' . $merchid . ' and status = 1 ')->select();
					} else {
						$stores = Db::name('shop_store')->where(' status = 1 ')->select();
					}
				} else if (0 < $merchid) {
					$stores = Db::name('shop_store')->where(' id in (' . implode(',', $storeids) . ') and id = ' . $merchid . ' and status = 1 ')->select();
				} else {
					$stores = Db::name('shop_store')->where(' id in (' . implode(',', $storeids) . ') and status = 1 ')->select();
				}
			}
		}

		$goodsrec = Db::name('shop_creditshop_goods')->where('goodstype = ' . $goods['goodstype'] . ' and `type` = ' . $goods['type'] . ' and status = 1 and deleted = 0')->field('id,thumb,title,credit,money,mincredit,minmoney')->orderRaw('rand()')->limit(3)->select();

		foreach ($goodsrec as $key => $value) {
			$goodsrec[$key]['credit'] = intval($value['credit']);

			if ((intval($value['money']) - $value['money']) == 0) {
				$goodsrec[$key]['money'] = intval($value['money']);
			}

			$goodsrec[$key]['mincredit'] = intval($value['mincredit']);

			if ((intval($value['minmoney']) - $value['minmoney']) == 0) {
				$goodsrec[$key]['minmoney'] = intval($value['minmoney']);
			}
		}
		$goodsoption = array('hasoption'=>0);
		if (!(empty($goods['hasoption']))) {
			$goodsoption['hasoption'] = 1;
			$options = Db::name('shop_creditshop_goods_option')->where('goodsid',$id)->order('displayorder','asc')->select();
			$options_stock = array_keys($options);
			if(!empty($options)) {
                $spec = array();
                $filter_spec = Db::name('shop_creditshop_goods_spec')
                    ->where('goodsid',$goods['id'])
                    ->order('displayorder', 'asc')
                    ->field('id,title')
                    ->select();
                foreach ($filter_spec as &$val) {
                    $item = array();
                    $item = Db::name('shop_creditshop_goods_spec_item')
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
                    $spec_goods_price[$v['specs']] = array('optionid'=>$v['id'],'specs'=>$v['specs'], 'total'=>$v['total'], 'credit'=>$v['credit'], 'money'=>$v['money'], 'goodssn'=>$v['goodssn'], 'weight'=>$v['weight']);
                }
                $goodsoption['filter_spec'] = $filter_spec;
                $goodsoption['spec_goods_price'] = $spec_goods_price;
			}
		}
		$goods['goodsoption'] = $goodsoption;

		$goods['creditshopdetail'] = getHttpHost() . url('index/webview/creditshopdetail');
		$this->result(1,'success',$goods);
    }

    /**
	 * 积分商城-积分明细
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function creditlog()
    {
    	$mid = $this->getMemberId();
    	$member = model('member')->getMember($mid);
    	$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$condition = ' mid = ' . $mid . ' and credittype = \'credit1\' ';
    	$list = Db::name('member_credits_record')->where($condition)->page($page,$pagesize)->order('createtime','desc')->select();
    	$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
    }

    /**
	 * 积分商城-创建订单信息确认
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function confirm()
    {
    	$mid = $this->getMemberId();
		$id = intval(input('id'));
		$merch_data = model("common")->getPluginset("store");
		if($merch_data["is_openmerch"] ) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		$merchid = intval(input('merchid'));
		$optionid = intval(input('optionid'));
		$shop = model("common")->getSysset("shop");
		$member = model("member")->getMember($mid);
		$num = empty(input('num')) ? 1 : intval(input('num'));
		$goods = model('creditshop')->getGoods($id, $member, $optionid, $num);
		if( empty($goods) ) {
			$this->result(0,"商品已下架或被删除!");
		}
		if( $goods["hasoption"] && empty($optionid) ) {
			$this->result(0,"请选择商品规格!");
		}
		if(!$goods['canbuy']) {
			$this->result(0, $goods["buymsg"]);
		}
		$set = $this->set;

		$stores = array( );
		if( $goods["goodstype"] == 0 ) {
			if( !empty($goods["isverify"])) {
				$storeids = array( );
				if( !empty($goods["storeids"]) ) {
					$storeids = array_merge(explode(",", $goods["storeids"]), $storeids);
				}
				if (empty($storeids)) {
					if (0 < $merchid) {
						$stores = Db::name('shop_store')->where('id',$merchid)->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->select();
					} else {
						$stores = Db::name('shop_store')->where('status',1)->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->select();
					}
				} else if (0 < $merchid) {
					$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and id = ' . $merchid)->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->select();
				} else {
					$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and status = 1 ')->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->select();
				}
				$stores = set_medias($stores,'logo');
			}
		}
		$goods['stores'] = $stores;
		$needaddress = false;
		$needcarrier = false;
		if($goods['type']==0) {
			if($goods['isverify']==0 && $goods['goodstype']==0) {
				$needaddress = true;
			} elseif($goods['isverify']==1 && $goods['goodstype']==0) {
				$needcarrier = true;
			}
		} else {
			if($goods['isverify']==0 && $goods['goodstype']==0) {
				$needaddress = true;
			} elseif($goods['isverify']==1 && $goods['goodstype']==0) {
				$needcarrier = true;
			}
		}
		$goods['needaddress'] = $needaddress;
		$goods['needcarrier'] = $needcarrier;
		$needpay = false;	
		if( (0 < $goods["money"]) || !empty(floatval($goods["dispatch"])) ) {
			$needpay = true;
		}
		$goods['needpay'] = $needpay;
		$address = Db::name('shop_member_address')->where('mid = ' . $mid . ' and deleted = 0 and isdefault = 1 ')->find();
		$dispatch = 0;
		if( $address['id'] ) {
			$dispatch = model('creditshop')->dispatchPrice($id, $address['id'], $optionid, 1, $mid);
		}
		$goods['dispatch'] = $dispatch;
		$goods['price'] = $goods["dispatch"] + ($goods["money"] * $num);
		$goods['address'] = $address;
		$this->result(1,'success',$goods);
    }

    /**
	 * 积分商城-提交订单支付
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function create()
    {
    	$mid = $this->getMemberId();
		$num = empty(input('num')) ? 1 : intval(input('num'));
		$id = intval(input('id'));
		$shop = model("common")->getSysset("shop");
		$member = model("member")->getMember($mid);
		$optionid = intval(input('optionid'));
		$goods = model('creditshop')->getGoods($id, $member, $optionid, $num);
		if( empty($goods) ) {
			$this->result(0,"商品已下架或被删除!");
		}
		$credit = $member["credit1"];
		$money = $member["credit2"];
		$paytype = intval(input('paytype'));
		$addressid = intval(input('addressid'));
		$storeid = intval(input('storeid'));
		$realname = trim(input('realname'));
		$mobile = trim(input('mobile'));
		$paystatus = 0;
		$dispatch = 0;

		if( $goods["hasoption"] && $optionid ) {
			$option = Db::name('shop_creditshop_goods_option')->where(" id = " . $optionid . " and goodsid = " . $id . " ")->field('total')->find();
			if( $option["total"] <= 0 ) {
				$this->result(0, $goods["buymsg"]);
			}
		}
		if( $addressid ) {
			$dispatch = model('creditshop')->dispatchPrice($id, $addressid, $optionid, $num, $mid);
		}
		$goods["dispatch"] = $dispatch;
		if( empty($goods["canbuy"]) ) {
			$this->result(0, $goods["buymsg"]);
		}
		if ($goods['isverify']==0 && $goods['goodstype']==0 && empty($addressid)) {
			$this->result(0,"请选择收货地址!");
		} else {
			if(($goods['isverify']==1 || $goods['goodstype']!=0) && (empty($realname) || empty($mobile))) {
				$this->result(0,"请填写联系方式!");
			}
		}
		$needpay = false;	
		if( (0 < $goods["money"]) || !empty(floatval($goods["dispatch"])) ) {
			Db::name('shop_creditshop_log')->where('goodsid = ' . $id  . ' and mid = ' . $mid . ' and status = 0 and paystatus = 0')->delete();
			$needpay = true;
			$lastlog = Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and mid = ' . $mid . ' and status=0 and paystatus=1')->find();
			if( !empty($lastlog) ) {
				$this->result(1,'success', array( "logid" => $lastlog["id"] ));
			}
		} else {
			Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and mid = ' . $mid . ' and status = 0')->delete();
		}
		$dispatchstatus = 0;
		if( $goods["isverify"] == 1 || 0 < $goods["goodstype"] || $goods["dispatch"] == 0 || $goods["type"] == 1 ) {
			$dispatchstatus = -1;
		}
		$address = false;
		if( !empty($addressid) ) {
			$address = Db::name('shop_member_address')->where('id = ' . $addressid)->field('id,realname,mobile,address,province,city,area')->find();
			if( empty($address) ) {
				$this->result(0, "未找到地址");
			}
		}
		$log = array( "merchid" => intval($goods["merchid"]), "mid" => $mid, "logno" => model("common")->createNO("shop_creditshop_log", "logno", ($goods["type"] == 0 ? "EE" : "EL")), "goodsid" => $id, "storeid" => $storeid, "optionid" => $optionid, "addressid" => $addressid, "address" => iserializer($address), "status" => 0, "paystatus" => (0 < $goods["money"] ? 0 : -1), "dispatchstatus" => $dispatchstatus, "createtime" => time(), "realname" => trim($realname), "mobile" => trim($mobile), "goods_num" => $num, 'paytype' => $paytype );
		$logid = Db::name('shop_creditshop_log')->insertGetId($log);
		if( !empty($log["realname"]) && !empty($log["mobile"]) ) {
			$up = array( "realname" => $log["realname"], "carrier_mobile" => $log["mobile"] );
			Db::name('member')->where('id',$member['id'])->update($up);
		}
		if( $needpay ) {
			return $this->redirect(url('apiv1/creditshop/pay',['logid' => $logid]));
		} else {
			return $this->redirect(url('apiv1/creditshop/lottery',['logid' => $logid,'num' => $num, 'id' => $id]));
		}
    }

    /**
	 * 积分商城-处理订单
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function lottery()
    {
    	$number = max(1, input('num'));
		$mid = $this->getMemberId();
		$id = intval(input('id'));
		$logid = intval(input('logid'));
		if( !$logid ) {
			$logid = $id;
		}
		$shop = model("common")->getSysset("shop");
		$member = model("member")->getMember($mid);
		$goodsid = intval(input('goodsid'));
		$log = Db::name('shop_creditshop_log')->where('id',$logid)->find();
		if( empty($log) ) {
			$logno = input('logno');
			$log = Db::name('shop_creditshop_log')->where('logno',$logno)->find();
		}
		$optionid = $log["optionid"];
		$goods = model('creditshop')->getGoods($log["goodsid"], $member, $log["optionid"], $number);
		$goods["money"] *= $number;
		$goods["credit"] *= $number;
		$goods["dispatch"] = model('creditshop')->dispatchPrice($log["goodsid"], $log["addressid"], $log["optionid"], $number, $mid);
		$credit = $member["credit1"];
		$money = $member["credit2"];
		if( empty($log) ) {
			$this->result(0, "服务器错误!");
		}
		if( empty($goods["canbuy"]) ) 
		{
			$this->result(0, $goods["buymsg"]);
		}
		$update = array( "couponid" => $goods["couponid"] );
		if( empty($log["paystatus"]) ) 
		{
			if( 0 < $goods["credit"] && $credit < $goods["credit"] ) 
			{
				$this->result(0, "积分不足!");
			}
			if( 0 < $goods["money"] && $money < $goods["money"] && $log["paytype"] == 0 ) 
			{
				$this->result(0, "余额不足!");
			}
		}
		$update["money"] = $goods["money"];
		if( 0 < $goods["money"] + $goods["dispatch"] && $log["paystatus"] < 1 ) 
		{
			if( $log["paytype"] == 0 ) 
			{
				model("member")->setCredit($mid, "credit2", 0 - ($goods["money"] + $goods["dispatch"]), "积分商城扣除余额度 " . $goods["money"]);
				$update["paystatus"] = 1;
			}
			if( $log["paytype"] == 1 ) 
			{
				$payquery = m("finance")->isWeixinPay($log["logno"], $goods["money"] + $goods["dispatch"], (is_h5app() ? true : false));
				$payqueryBorrow = m("finance")->isWeixinPayBorrow($log["logno"], $goods["money"] + $goods["dispatch"]);
				if( !is_error($payquery) || !is_error($payqueryBorrow) ) 
				{
					model('creditshop')->payResult($log["logno"], "wechat", $goods["money"] + $goods["dispatch"], (is_h5app() ? true : false));
				}
				else 
				{
					$this->result(0, array( "status" => "-1", "message" => "支付出错,请重试(1)!" ));
				}
			}
			if( $log["paytype"] == 2 && $log["paystatus"] < 1 ) 
			{
				$this->result(0, array( "status" => "-1", "message" => "未支付成功!" ));
			}
		}
		if( 0 < $goods["credit"] && empty($log["creditpay"]) ) 
		{
			$update["credit"] = $goods["credit"];
			model("member")->setCredit($mid, "credit1", 0 - $goods["credit"], "积分商城扣除积分 " . $goods["credit"]);
			$update["creditpay"] = 1;
			Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setInc('joins');
		}
		$status = 1;
		if( $goods["type"] == 1 ) 
		{
			if( 0 < $goods["rate1"] && 0 < $goods["rate2"] ) 
			{
				if( $goods["rate1"] == $goods["rate2"] ) 
				{
					$status = 2;
				}
				else 
				{
					$rand = rand(0, intval($goods["rate2"]));
					if( $rand <= intval($goods["rate1"]) ) 
					{
						$status = 2;
					}
				}
			}
		}
		else 
		{
			$status = 2;
		}
		if( $status == 2 && $goods["isverify"] == 1 ) 
		{
			$update["eno"] = model('creditshop')->createENO();
		}
		if( $goods["isverify"] == 1 ) 
		{
			$update["verifynum"] = (0 < $goods["verifynum"] ? $goods["verifynum"] : 1);
			if( $goods["isendtime"] == 0 ) 
			{
				if( 0 < $goods["usetime"] ) 
				{
					$update["verifytime"] = time() + 3600 * 24 * intval($goods["usetime"]);
				}
				else 
				{
					$update["verifytime"] = 0;
				}
			}
			else 
			{
				$update["verifytime"] = intval($goods["endtime"]);
			}
		}
		$update["status"] = $status;
		if( 0 < $goods["dispatch"] && $goods["goodstype"] == 0 && $goods["type"] == 0 ) 
		{
			$update["dispatchstatus"] = "1";
			$update["dispatch"] = $goods["dispatch"];
		}
		Db::name('shop_creditshop_log')->where('id',$log['id'])->update($update);
		$log = Db::name('shop_creditshop_log')->where('id',$logid)->find();
		if( $status == 2 && $update["creditpay"] == 1 ) 
		{
			if( $goods["goodstype"] == 1 ) 
			{
				if( model("coupon") ) 
				{
					for( $i = 0; $i < $number; $i++ ) 
					{
						model("coupon")->creditshop($logid);
					}
					$status = 3;
				}
				$update["time_finish"] = time();
			}
			else 
			{
				if( $goods["goodstype"] == 2 ) 
				{
					$credittype = "credit2";
					$creditstr = "积分商城兑换余额";
					$num = abs($goods["grant1"]);
					$credit2 = floatval($member["credit2"]) + $num;
					model("member")->setCredit($log["mid"], $credittype, $num, array(0, $creditstr ));
					$set = model("common")->getSysset("shop");
					$data = array( "mid" => $log["mid"], "credittype" => "credit2", "createtime" => time(), "remark" => $set["name"] . "积分商城兑换余额", "num" => $num, "module" => "creditshop" );
					$mlogid = Db::name('member_credits_record')->insertGetId($data);
					model("notice")->sendMemberLogMessage($mlogid);
					$status = 3;
					$update["time_finish"] = time();
				}
				else 
				{
					if( $goods["goodstype"] == 3 ) 
					{
					}
				}
			}
			$update["status"] = $status;
			Db::name('shop_creditshop_log')->where('id',$logid)->update($update);
			model('notice')->sendCreditshopMessage($log["id"]);
			if( $status == 3 ) 
			{
				Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('total',1);
			}
			if( $goods["goodstype"] == 0 && $status == 2 ) 
			{
				Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('total',1);
			}
			if( $goods["goodstype"] == 3 && $status == 2 ) 
			{
				Db::name('shop_creditshop_goods')->where('id',$log['goodsid'])->setDec('packetsurplus',1);
			}
			if( $goods["hasoption"] && $log["optionid"] ) 
			{
				Db::name('shop_creditshop_goods_option')->where('id',$log['optionid'])->setDec('total',1);
			}
		}
		$this->result(1,'success',array('logid'=>$log['id']));
    }

    /**
	 * 积分商城-提交订单支付
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function pay()
    {
    	$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$credit = $member["credit1"];
		$money = $member["credit2"];
		$logid = input('logid/d');

		$order = Db::name('shop_creditshop_log')->where('id',$logid)->where('mid',$mid)->find();
		if(empty($order)) {
			$this->result(0,'订单不存在');
		}
		$num = $order["goods_num"];
		$goods = model('creditshop')->getGoods($order["goodsid"], $member, $order["optionid"], $order["goods_num"]);
		$dispatch = 0;
		if( $order['addressid'] ) {
			$dispatch = model('creditshop')->dispatchPrice($order['goodsid'], $order['addressid'], $order['optionid'], $order['goods_num'], $mid);
		}
		$goods["dispatch"] = $dispatch;

		if( empty($goods["canbuy"]) ) {
			$this->result(0, $goods["buymsg"]);
		}
    	$headerinfo = $this->headerinfo;
		if(!in_array($headerinfo['device-type'], array('iOS','android','wechat','web'))) {
			$this->result(0,'支付环境出错!');
		}
		$log = Db::name('shop_core_paylog')->where('module','creditshop')->where('tid',$order['logno'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			$this->result(0,'订单已付款');
			exit();
		}
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'creditshop', 'tid' => $order['logno'], 'fee' => $goods["money"] * $num + $goods["dispatch"], 'status' => 0);
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}
		$paytype = input('paytype/d') ? input('paytype/d') : $order['paytype'];
		$sec = model('common')->getSec();
		$sec = iunserializer($sec['sec']);
		$set = model("common")->getSysset();
		if( $paytype == 3 ) {
			if( $goods["money"] + $goods["dispatch"] <= $money ) {
				$paystatus = 3;
			} else {
				$this->result(0, "余额不足!");
			}
			Db::name('shop_creditshop_log')->where('id',$logid)->update(array( "paytype" => $paystatus ));
		} else {
			if( $paytype == 1 ) {
				$paystatus = 1;
				Db::name('shop_creditshop_log')->where('id',$logid)->update(array( "paytype" => $paystatus ));
				if( empty($set["pay"]["app_wechat"]) ) {
					$this->result(0, "未开启微信支付!");
				}
				$wechat = array( "success" => false );
				$params = array( );
				$params["tid"] = $order["logno"];
				$params["user"] = $mid;
				$params['product_id'] = $logid;
				$params["fee"] = $goods["money"] * $num + $goods["dispatch"];
				$params["title"] = $set["shop"]["name"] . ((empty($goods["type"]) ? "积分兑换" : "积分抽奖")) . " 单号:" . $order["logno"];
				if( isset($set["pay"]) && $set["pay"]["app_wechat"] == 1) {
					$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 2, $member['wechat_mid']);
					if (!is_array($wechat)) {
						$this->result(0,$wechat);
					}
				}
				$wechat['product_id'] = $logid;				
				$this->result(1,'success',$wechat);
			} else {
				if( $paytype == 2 ) {
					$paystatus = 2;
					Db::name('shop_creditshop_log')->where('id',$logid)->update(array( "paytype" => $paystatus ));
					$lognoother = str_replace("EE", "EP", $order["logno"]);
					$params = array( );
					$params["tid"] = $order["logno"];
					$params['product_id'] = $logid;
					$params["user"] = $mid;
					$params["fee"] = $goods["money"] * $num + $goods["dispatch"];
					$params["title"] = $set["shop"]["name"] . ((empty($goods["type"]) ? "积分兑换" : "积分抽奖")) . " 单号:" . $order["logno"];
					if( isset($set["pay"]) && $set["pay"]["app_alipay"] == 1 ) 
					{
						$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 2, getHttpHost() . '/public/dist/order');
						if (empty($alipay)) {
							$this->result(0,'参数错误');
						}
					}
					$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$logid));
				}
			}
		}
		$this->result(0, "支付出错!");
    }



    /**
	 * 积分商城-我的记录
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function log()
    {
    	$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		$shop = model('common')->getSysset('shop');
		$status = intval(input('status'));
		$set = model('common')->getPluginset('creditshop');
		$merchid = intval(input('merchid'));
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$condition = ' log.mid=' . $mid . ' and log.status>0';

		if (0 < $merchid) {
			$condition .= ' and log.merchid = ' . $merchid . ' ';
		}

		if ($status == 1) {
			$condition .= ' and g.type = 0 ';
		} else {
			if ($status == 2) {
				$condition .= ' and g.type = 1 ';
			}
		}
		
		$list = array();
		$list = Db::name('shop_creditshop_log')->alias('log')->join('shop_creditshop_goods g','log.goodsid = g.id','left')->join('shop_creditshop_goods_option op','op.id = log.optionid','left')->where($condition)->order('log.createtime','desc')->field('log.id,log.logno,log.goodsid,log.goods_num,log.status,log.eno,log.paystatus,g.title,g.type,g.thumb,log.credit,log.money,log.dispatch,g.isverify,g.goodstype,log.addressid,log.storeid,g.goodstype,log.time_send,log.time_finish,log.iscomment,op.title as optiontitleg,g.merchid,log.verifytime')->page($page,$pagesize)->select();
		$list = set_medias($list, 'thumb');
		foreach ($list as &$row) {
			if (0 < $row['credit'] & 0 < $row['money']) {
				$row['acttype'] = 0;
			} else if (0 < $row['credit']) {
				$row['acttype'] = 1;
			} else if (0 < $row['money']) {
				$row['acttype'] = 2;
			} else {
				$row['acttype'] = 3;
			}

			if ($row['money'] - intval($row['money']) == 0) {
				$row['money'] = intval($row['money']);
			}

			$row['isreply'] = $set['isreply'];

			$statusstr = '';
			$status = 0;
			if($row['status'] == 1 && $row['type']==1) {
				$statusstr = '未中奖';$status = 1;
			}
			if($row['goodstype'] == 0) {
				if($row['isverify'] == 1) {
					if($row['status'] == 2) {
						if($row['verifytime'] < time()) {
							$statusstr = '已失效';$status = 21;
						} else {
							$statusstr = '待使用';$status = 22;
						}
					} else {
						if($row['status'] == 3) {
							$statusstr = '已使用';$status = 31;
						}
					}
				} else {
					if($row['status'] ==2 && $row['addressid'] == 0) {
						if($row['type']==0) {
							$statusstr = '已兑换';$status = 3;
						} else {
							$statusstr = '已中奖';$status = 25;
						}
					}
					if($row['status'] ==2 && $row['addressid'] > 0 && $row['time_send'] == 0) {
						$statusstr = '待发货';$status = 26;
					}
					if($row['status'] ==3 && $row['time_send'] > 0 && $row['time_finish'] ==0) {
						$statusstr = '已发货';$status = 27;
					} else {
						if($row['status'] ==3 && $row['time_finish'] > 0) {
							$statusstr = '已完成';$status = 32;
						}
					}
				}
			}
			$row['status'] = $status;
			$row['statusstr'] = $statusstr;
		}

		unset($row);
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
    }

    /**
	 * 积分商城-我的记录
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function logdetail()
    {
    	$mid = $this->getMemberId();
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}

		$member = model('member')->getMember($mid);
		$shop = model('common')->getSysset('shop');
		$set = model('common')->getPluginset('creditshop');
		$pay = model('common')->getSysset('pay');
		$merchid = intval(input('merchid'));
		$condition = ' 1 ';
		$id = intval(input('id'));
		$log = Db::name('shop_creditshop_log')->where('id',$id)->where('mid',$mid)->find();
		$log['goods_num'] = max(1, intval($log['goods_num']));

		if (empty($log)) {
			$this->result(0, '兑换记录不存在!');
		}
		$goods = model('creditshop')->getGoods($log['goodsid'], $member, $log['optionid']);

		$ordermoney = price_format($goods['money'] * $log['goods_num'], 2);
		$ordercredit = $goods['credit'] * $log['goods_num'];

		if (empty($goods['id'])) {
			$this->result(0, '商品记录不存在!');
		}

		$address = false;

		if (!empty($log['addressid'])) {
			$address = Db::name('shop_member_address')->where('id',$log['addressid'])->where('mid',$mid)->find();
			$goods['dispatch'] = model('creditshop')->dispatchPrice($log['goodsid'], $log['addressid'], $log['optionid'], $log['goods_num'], $mid);
		}

		$goods['currenttime'] = time();
		$stores = array();
		$store = false;

		if (!empty($goods['isverify'])) {
			$verifytotal = Db::name('shop_creditshop_verify')->where('logid = ' . $id . ' and mid = ' . $mid . ' and verifycode = ' . $log['eno'])->count();

			if ($goods['verifytype'] == 0) {
				$verify = Db::name('shop_creditshop_verify')->where('logid = ' . $id . ' and mid = ' . $mid . ' and verifycode = ' . $log['eno'])->field('isverify')->find();
			}

			$verifynum = $log['verifynum'] - $verifytotal;

			if ($verifynum < 0) {
				$verifynum = 0;
			}

			$storeids = array();
			$storeids = array_merge(explode(',', $goods['storeids']), $storeids);

			if (empty($goods['storeids'])) {
				if (0 < $merchid) {
					$stores = Db::name('shop_store')->where('merchid = ' . $merchid . ' and status = 1')->select();
				} else {
					$stores = Db::name('shop_store')->where('status = 1')->select();
				}
			} else if (0 < $merchid) {
				$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and merchid = ' . $merchid . ' and status = 1')->select();
			} else {
				$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and status = 1')->select();
			}

			$isverify = Db::name('shop_creditshop_verify')->where('logid = ' . $id . ' and isverify = 1')->find();

			if (0 < $isverify['isverify']) {
				$carrier = model('member')->getMember($isverify['verifier']);
				if (!is_array($carrier) || empty($carrier)) {
					$carrier = false;
				}

				$store = Db::name('shop_store')->where('id = ' . $isverify['storeid'] . 'status = 1' )->find();
			}
		}
		$statusstr = '';
		$status = 0;
		if($log['status'] == 1 && $log['type']==1) {
			$statusstr = '未中奖';$status = 1;
		}
		if($log['goodstype'] == 0) {
			if($goods['isverify'] == 1) {
				if($log['status'] == 2) {
					if($log['verifytime'] < time()) {
						$statusstr = '已失效';$status = 21;
					} else {
						$statusstr = '待使用';$status = 22;
					}
				} else {
					if($log['status'] == 3) {
						$statusstr = '已使用';$status = 31;
					}
				}
			} else {
				if($log['status'] ==2 && $log['addressid'] == 0) {
					if($log['type']==0) {
						$statusstr = '已兑换';$status = 3;
					} else {
						$statusstr = '已中奖';$status = 25;
					}
				}
				if($log['status'] ==2 && $log['addressid'] > 0 && $log['time_send'] == 0) {
					$statusstr = '待发货';$status = 26;
				}
				if($log['status'] ==3 && $log['time_send'] > 0 && $log['time_finish'] ==0) {
					$statusstr = '已发货';$status = 27;
				} else {
					if($log['status'] ==3 && $log['time_finish'] > 0) {
						$statusstr = '已完成';$status = 32;
					}
				}
			}
		}
		$log['status'] = $status;
		$log['statusstr'] = $statusstr;
		$log['address'] = iunserializer($log['address']);
		$log['goods'] = $goods;
		$log['stores'] = $stores;
		$this->result(1,'success',$log);
    }

    /**
	 * 积分商城-确认收货
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function finish()
    {
    	$logid = intval(input('logid'));
    	$mid = $this->getMemberId();
		$merchid = intval(input('merchid'));
		$condition = ' mid = ' . $mid . ' and id = ' . $logid;

		if (0 < $merchid) {
			$condition .= ' and merchid = ' . $merchid . ' ';
		}

		$log = Db::name('shop_creditshop_log')->where($condition)->find();

		if (empty($log)) {
			$this->result(0, '订单未找到');
		}

		if ($log['status'] != 3 && empty($log['expresssn'])) {
			$this->result(0, '订单不能确认收货');
		}
		Db::name('shop_creditshop_log')->where('id',$logid)->update(array('time_finish' => time()));
		model('notice')->sendCreditshopMessage($log["id"]);
		$this->result(1,'success',array('logid'=>$logid));
    }

    /**
	 * 订单核销适用门店
	 * @param $orderid [订单id]
	 * @return  [array]    $list  [门店列表]
	 **/
	public function stores()
	{
		$orderid = intval(input('orderid'));
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		if (empty($orderid)) {
			$this->result(0,'订单不存在');
		}
		$mid = $this->getMemberId();
		$order = Db::name('shop_creditshop_log')->where('id',$orderid)->where('mid',$mid)->find();

		if (empty($order)) {
			$this->result(0,'订单不存在');
		}
		$goods = Db::name('shop_creditshop_goods')->where('id = ' . intval($order['goodsid']))->field('storeids')->find();

		$storeids = array();
		if (!empty($goods['storeids'])) {
			$storeids = array_merge(explode(',', $goods['storeids']), $storeids);
		}
		if (empty($storeids)) {
			if (0 < $merchid) {
				$stores = Db::name('shop_store')->where('id',$merchid)->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->page($page,$pagesize)->select();
			} else {
				$stores = Db::name('shop_store')->where('status=1')->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->page($page,$pagesize)->select();
			}
		} else if (0 < $merchid) {
			$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and id = ' . $merchid)->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->page($page,$pagesize)->select();
		} else {
			$stores = Db::name('shop_store')->where('id in (' . implode(',', $storeids) . ') and status = 1 ')->field('id,merchname,logo,realname,mobile,address,tel,lat,lng')->page($page,$pagesize)->select();
		}
		$stores = set_medias($stores,'logo');
		$this->result(1,'success',array('list'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
	}

}