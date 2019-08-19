<?php
/**
 * apiv1 订单售后退换货
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Refund extends Base
{
	/**
	 * 售后列表
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [售后列表]
	 **/
	public function refundlist()
	{
		$mid = $this->getMemberId();
		$rtype = input('rtype');
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$shopset = $this->shopset;
		$condition = ' f.mid = ' . $mid;
		if($rtype != '') {
			$condition .= ' f.rtype = ' . $rtype;
		}
		$list = Db::name('shop_order_refund')->where('mid',$mid)->order('createtime','desc')->page($page,$pagesize)->select();
		$refundlist = array();

        $refundstatus = array(
			-2 => array('css' => 'default', 'name' => '客户取消'),
			-1 => array('css' => 'default', 'name' => '已拒绝'),
			0  => array('css' => 'danger', 'name' => '等待商家处理申请'),
			1  => array('css' => 'success', 'name' => '售后已完成'),
			3  => array('css' => 'warning', 'name' => '等待客户退回物品'),
			4  => array('css' => 'info', 'name' => '客户退回物品，等待商家处理'),
			5  => array('css' => 'info', 'name' => '等待客户收货'),
		);
		foreach ($list as &$val) {
			$goodsids = array_unique(array_filter(explode(",", $val['goodsids'])));
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$val['id'])->where('og.id','in',$goodsids)->field('og.goodsid,og.total,g.title,g.thumb,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs,og.refundid,og.rstate,og.refundtime')->select();
			$goods = set_medias($goods,'thumb');
			$val['refundstatus'] = $refundstatus[$val['status']]['name'];
			$val['goods'] = $goods;
			if(empty($val['merchid'])) {
            	$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
            } else {
            	$merch = Db::name('shop_store')->where('id',$val['merchid'])->field('id,logo,merchname')->find();
            }
            $merch['logo'] = tomedia($merch['logo']);
            $merch['refund'] = $val;
            $refundlist[] = $merch;
		}
		unset($val);
		
		$this->result(1,'success',array('list'=>$refundlist,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 售后详情
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [售后列表]
	 **/
	public function refunddetail()
	{
		$mid = $this->getMemberId();
		$refundid = input('refundid/d');
		$refund = Db::name('shop_order_refund')->where('mid',$mid)->where('id',$refundid)->find();
		if(empty($refund)) {
			$this->result(0,'您访问的信息不存在');
		}
		if (!empty($refund['refundaddress'])) {
			$refund['refundaddress'] = iunserializer($refund['refundaddress']);
		}
		if (!empty($refund['imgs'])) {
			$refund['imgs'] = iunserializer($refund['imgs']);
		}
		$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$refund['id'])->where('og.id','in',$goodsids)->field('og.goodsid,og.total,g.title,g.thumb,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs,og.refundid,og.rstate,og.refundtime')->select();
		$goods = set_medias($goods,'thumb');
		if(empty($goods)) {
			$this->result(0,'您访问的信息不存在');
		}
		$shopset = $this->shopset;
		if(!empty($refund['merchid'])) {
			$merch = Db::name('shop_store')->where('id',$merchid)->field('id,merchname,logo,tel,mobile')->find();
		} else {
			$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo'],'tel'=>$shopset['contact']['phone']);
		}
		if(!empty($merch)) {
			$merch['logo'] = tomedia($merch['logo']);
		}
		$refund['merch'] = $merch;
		$refund['goods'] = $goods;
		$rexpresses = (object)null;
		if (($refund['status'] == 5) && !empty($refund['rexpress']) && !empty($refund['rexpresssn'])) {
			$expresslist = model('util')->getExpressList($refund['rexpress'], $refund['rexpresssn']);
			if (0 < count($expresslist['list'])) {				
				$rexpresses = $expresslist['list'][0];
				$rexpresses['rexpresscom'] = $refund['rexpresscom'];
				$rexpresses['rexpress'] = $refund['rexpress'];
				$rexpresses['rexpresssn'] = $refund['rexpresssn'];
			}
		}
		$expresses = (object)null;
		if (($refund['status'] == 4) && !empty($refund['express']) && !empty($refund['expresssn'])) {
			$expresslist = model('util')->getExpressList($refund['express'], $refund['expresssn']);
			if (0 < count($expresslist['list'])) {
				$expresses = $expresslist['list'][0];
				$expresses['expresscom'] = $refund['expresscom'];
				$expresses['express'] = $refund['express'];
				$expresses['expresssn'] = $refund['expresssn'];
			}
		}
		$refund['expresses'] = $expresses;
		$refund['rexpresses'] = $rexpresses;
		$this->result(1,'success',$refund);
	}

	/**
	 * 协商历史
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [售后列表]
	 **/
	public function consulthistorey()
	{
		$mid = $this->getMemberId();
		$refundid = input('refundid/d');
		$list = Db::name('shop_order_refund_log')->where('refundid',$refundid)->order('createtime','asc')->select();
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
     * 退换货申请-信息确认
     * @global type $_W
     * @global type 
     */
	public function refundconfirm()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$shopset = $this->shopset;
		$tradeset = model('common')->getSysset('trade');
		$refundcontent = $tradeset['refundcontent'] ? $tradeset['refundcontent'] : '';
		$rtype = input('rtype/d');
		$goodsid = input('goodsid/d');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,`virtual`,ordersn,paytype,discountprice,dispatchprice,addressid,carrier,merchid,isverify,dispatchtype,verifyinfo,merchshow,verified,verifycode,address,userdeleted,isparent,storeid,virtual_str,finishtime,createtime,paytime,sendtime,canceltime,refundstate,refundid,refundtime')->find();
		if (empty($order)) {
			$this->result(0, '订单未找到');
		}
		$orderprice = $order['price'];
		if ($order['status'] <= 0) {
			$this->result(0, '订单未付款或已取消，不能申请退款');
		} else {
			if ($order['status'] == 3) {
				if (!empty($order['virtual']) || ($order['isverify'] == 1)) {
					$this->result(0, '此订单不允许退款');
				} else {
					if ($order['refundstate'] == 0) {
						$refunddays = intval($tradeset['refunddays']);

						if (0 < $refunddays) {
							$days = intval((time() - $order['finishtime']) / 3600 / 24);

							if ($refunddays < $days) {
								$this->result(0,'订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!');
							}
						} else {
							$this->result(0, '订单完成, 无法申请退款');
						}
					}
				}
			}
		}

		$fullback_log = $fullbackgoods = array();
		$fullback_log = Db::name('shop_fullback_log')->where('orderid',$orderid)->find();

		if ($fullback_log) {
			$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$fullback_log['goodsid'])->field('refund')->find();

			if (0 < $fullback_log['fullbackday']) {
				if ($fullback_log['fullbackday'] < $fullback_log['day']) {
					$order['price'] = $order['price'] - ($fullback_log['priceevery'] * $fullback_log['fullbackday']);
				}
				else {
					$order['price'] = $order['price'] - $fullback_log['price'];
				}
			}
		}

		$order['refundprice'] = $order['price'] + $order['deductcredit2'];
		$refundid = $order['refundid'];
		if (2 <= $order['status']) {
			$order['refundprice'] -= $order['dispatchprice'];
		}

		$order['refundprice'] = round($order['refundprice'], 2);

		if ($order['isparent'] == 1) {
			$scondition = ' og.parentorderid=' . $orderid;
		} else {
			$scondition = ' og.orderid=' . $orderid;
		}
		if(empty($goodsid)) {
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where($scondition)->field('og.id,og.goodsid,og.price as marketprice,g.title,g.thumb,og.prohibitrefund, g.cannotrefund,og.price, og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids,og.refundid,og.rstate,og.refundtime')->select();
		} else {
			$scondition .= ' and og.id = ' . $goodsid;
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where($scondition)->field('og.id,og.goodsid,og.price as marketprice,g.title,g.thumb,og.prohibitrefund, g.cannotrefund,og.price, og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids,og.refundid,og.rstate,og.refundtime')->select();
		}
		$goodsprice = 0;
		foreach ($goods as &$val) {
			$canrefund = false;
			if((($order['status'] == 1 || $order['status'] == 2) && empty($val['prohibitrefund'])) || ($order['status'] == 3 && empty($val['prohibitrefund']) && empty($val['cannotrefund']))) {
				$canrefund = true;
			}
			$val['canrefund'] = $canrefund;
			$goodsprice += $val['price'];
		}
		unset($val);
		if(!empty($goodsid)) {
			$order['refundprice'] = round($goodsprice, 2);
		}
		$goods = set_medias($goods,'thumb');
		$address = array();
		if (!empty($order['addressid'])) {
			$address = iunserializer($order['address']);
			if (!is_array($address)) {
				$address = Db::name('shop_member_address')->where('id',$order['addressid'])->find();
			}
		}
		$carrier = @iunserializer($order['carrier']);
		if (!is_array($carrier) || empty($carrier)) {
			$carrier = false;
		}
		$order['carrier'] = $carrier;
		$merch = array();
		$merchid = $order['merchid'];
		if (0 < $merchid) {
			$merch = Db::name('shop_store')->where('id',$merchid)->field('id,merchname,logo')->find();
		} else{
			$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
		}
		if(!empty($merch)) {
			$merch['logo'] = tomedia($merch['logo']);
		}		
		$order['goods'] = $goods;
		$order['address'] = $address ? $address : (object)null;
		$merch['order'] = $order;
		$this->result(1,'success',array('order' => $merch, 'fullback_log' => $fullback_log ? $fullback_log : array(), 'fullbackgoods' => $fullbackgoods, 'orderprice' => $orderprice, 'refundprice' => $order['refundprice'],'refundcontent'=>$refundcontent));
	}

	/**
     * 退换货申请-确认
     * @global type $_W
     * @global type 
     */
	public function refundsubmit()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$goodsids = input('goodsids/s','');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,`virtual`,refundstate,merchid')->find();
		if (empty($order)) {
			$this->result(0, '订单未找到');
		}
		
		if ($order['status'] <= 0) {
			$this->result(0, '订单未付款或已取消，不能申请退款');
		} else {
			if ($order['status'] == 3) {
				if (!empty($order['virtual']) || ($order['isverify'] == 1)) {
					$this->result(0, '此订单不允许退款');
				} else {
					if ($order['refundstate'] == 0) {
						$tradeset = model('common')->getSysset('trade');
						$refunddays = intval($tradeset['refunddays']);

						if (0 < $refunddays) {
							$days = intval((time() - $order['finishtime']) / 3600 / 24);

							if ($refunddays < $days) {
								$this->result(0,'订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!');
							}
						} else {
							$this->result(0, '订单完成, 无法申请退款');$_err = '订单完成, 无法申请退款!';
						}
					}
				}
			}
		}
		
		$goodsids = array_unique(array_filter(explode(",", $goodsids)));
		if(empty($goodsids) || !is_array($goodsids)) {
			$this->result(0, '请选择需要维权的商品');
		}
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$order['id'])->where('og.id','in',$goodsids)->field('og.id,og.goodsid, og.price, og.total, og.optionname, g.thumb, g.title,g.isfullback,og.prohibitrefund,g.cannotrefund,og.refundid,og.rstate,og.refundtime')->select();
		$goodsprice = 0;
		foreach ($goods as $key => $row) {
			if(!empty($row['prohibitrefund']) || !empty($row['cannotrefund'])) {
				$this->result(0, '商品' . $row['title'] . '不支持退换货');
			}
			$goodsprice += $row['price'];
		}
		$reason = trim(input('reason'));
		if(empty($goodsids)) {
			$this->result(0, '请填写退款原因');
		}

		$fullback_log = Db::name('shop_fullback_log')->where('orderid',$orderid)->find();

		if ($fullback_log) {
			$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$fullback_log['goodsid'])->field('refund')->find();

			if (0 < $fullback_log['fullbackday']) {
				if ($fullback_log['fullbackday'] < $fullback_log['day']) {
					$order['price'] = $order['price'] - ($fullback_log['priceevery'] * $fullback_log['fullbackday']);
				} else {
					$order['price'] = $order['price'] - $fullback_log['price'];
				}
			}
		}

		$order['refundprice'] = $order['price'] + $order['deductcredit2'];

		if (2 <= $order['status']) {
			$order['refundprice'] -= $order['dispatchprice'];
		}

		$order['refundprice'] = round($order['refundprice'], 2);
		$price = trim(input('price'));
		$rtype = intval(input('rtype'));
		if ($rtype != 2) {
			if (empty($price) && ($order['deductprice'] == 0)) {
				$this->result(0, '退款金额不能为0元');
			}

			if ($order['refundprice'] < $price) {
				$this->result(0, '退款金额不能超过' . $order['refundprice'] . '元');
			}
		}

		if ((($rtype == 0) || ($rtype == 1)) && (3 <= $order['status'])) {
			if ((($orderprice <= $fullback_log['price']) || ($fullbackgoods['refund'] == 0)) && $fullback_log) {
				$this->result(0, '此订单不可退款');
			}

			if ($fullback_log) {
				model('order')->fullbackstop($orderid);
			}
		}
		$images = '';
		$imgs = input('imgs');
		if(!empty($imgs)) {
			$images = json_decode($imgs,true);
		}
		$refund = array('merchid' => $order['merchid'], 'applyprice' => $price, 'rtype' => $rtype, 'reason' => trim(input('reason')), 'content' => trim(input('content')), 'imgs' => iserializer($images));

		if ($refund['rtype'] == 2) {
			$refundstate = 40;
		} elseif ($refund['rtype'] == 0) {
			$refundstate = 30;
		} else {
			if ($refund['rtype'] == 1) {
				$refundstate = 20;
			}
		}
		$r_type = array('退款', '退货退款', '换货');
		switch ($order['status']) {
			case '1':
				$orderstate = '未发货';
				break;
			
			case '2':
				$orderstate = '已发货';
				break;
			
			case '3':
				$orderstate = '已收货';
				break;
			
			default:
				$orderstate = '未发货';
				break;
		}
		Db::startTrans();
		try{
		    $refund['mid'] = $mid;
	    	$refund['goodsids'] = implode(',',$goodsids);
			$refund['createtime'] = time();
			$refund['orderid'] = $orderid;
			$refund['orderprice'] = $order['refundprice'];
			$refund['lastupdate'] = time();
			$refund['refundno'] = model('common')->createNO('shop_order_refund', 'refundno', 'SR');
			$refundid = Db::name('shop_order_refund')->insertGetId($refund);
			Db::name('shop_order')->where('id',$orderid)->update(array('refundstate' => $refundstate));
			foreach ($goods as $key => $value) {
				Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
			}
			Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'operator' => '我', 'msgtype' => 1, 'title' => '买家发起' . $r_type[$rtype] . '申请', 'content' => '发起了退货退款申请，货物状态:' . $orderstate . '，原因：' . trim(input('reason')), 'createtime' => time()));
			model('notice')->sendOrderMessage($refundid, true, null, $refundid);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1,'success',array('refundid'=>$refundid));
	}

	/**
     * 退换货申请-取消
     * @global type $_W
     * @global type 
     */
	public function refundcancel()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$refundid = input('refundid/d');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,`virtual`,refundstate,merchid')->find();
		$refund = Db::name('shop_order_refund')->where('id',$refundid)->where('orderid',$orderid)->find();
		if (empty($order)) {
			$this->result(0, '订单未找到');
		}
		if (empty($refund)) {
			$this->result(0, '维权信息未找到');
		}
		if ($order['status'] <= 0) {
			$this->result(0, '订单已经处理完毕');
		}

		Db::startTrans();
		try{
		    $change_refund = array();
			$change_refund['status'] = -2;
			$change_refund['refundtime'] = time();
			$change_refund['lastupdate'] = time();
			Db::name('shop_order_refund')->where('id',$refundid)->update($change_refund);

			$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where('og.orderid',$order['id'])->where('og.refundid',$refundid)->field('og.id,og.goodsid, og.price, og.total, og.optionname, g.thumb, g.title,g.isfullback,og.prohibitrefund,g.cannotrefund,og.refundid,og.rstate,og.refundtime')->select();
			if ($refund['rtype'] == 2) {
				$refundstate = 42;
			} elseif ($refund['rtype'] == 0) {
				$refundstate = 32;
			} else {
				if ($refund['rtype'] == 1) {
					$refundstate = 22;
				}
			}
			foreach ($goods as $key => $value) {
				Db::name('shop_order_goods')->where('id',$value['id'])->update(array('rstate' => $refundstate, 'prohibitrefund' => 0));
			}
			$refundcount = Db::name('shop_order_refund')->where('orderid',$orderid)->where('status','in',[0,3,4,5])->count();
			if($refundcount == 0) {
				Db::name('shop_order')->where('id',$orderid)->update(array('refundstate' => 0));
			}
			Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 1, 'operator' => '我', 'title' => '买家取消' . $r_type[$rtype] . '申请', 'content' => '取消了退货退款申请', 'createtime' => time()));
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1,'success',array('refundid'=>$refundid));
	}

	protected function globalData()
	{
		$mid = $this->getMemberId();
		$orderid = intval(input('orderid'));
		$refundid = intval(input('refundid'));
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,`virtual`,refundstate,merchid')->find();
		$orderprice = $order['price'];

		if (empty($order)) {
			$this->result(0, '订单未找到');
		}

		if ($order['status'] == 0) {
			$this->result(0, '订单未付款，不能申请退款');
		} else {
			if ($order['status'] == 3) {
				if (!empty($order['virtual']) || ($order['isverify'] == 1)) {
					$this->result(0, '此订单不允许退款');
				} else {
					if ($order['refundstate'] == 0) {
						$tradeset = model('common')->getSysset('trade');
						$refunddays = intval($tradeset['refunddays']);

						if (0 < $refunddays) {
							$days = intval((time() - $order['finishtime']) / 3600 / 24);

							if ($refunddays < $days) {
								$this->result(0, '订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!');
							}
						} else {
							$this->result(0, '订单完成, 无法申请退款!');
						}
					}
				}
			}
		}

		$fullback_log = Db::name('shop_fullback_log')->where('orderid',$orderid)->find();

		if ($fullback_log) {
			$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$fullback_log['goodsid'])->find();
			if (0 < $fullback_log['fullbackday']) {
				if ($fullback_log['fullbackday'] < $fullback_log['day']) {
					$order['price'] = $order['price'] - ($fullback_log['priceevery'] * $fullback_log['fullbackday']);
				} else {
					$order['price'] = $order['price'] - $fullback_log['price'];
				}
			}
		}

		$order['refundprice'] = $order['price'] + $order['deductcredit2'];

		if (2 <= $order['status']) {
			$order['refundprice'] -= $order['dispatchprice'];
		}

		$order['refundprice'] = round($order['refundprice'], 2);
		return array('mid' => $mid, 'orderid' => $orderid, 'order' => $order, 'refundid' => $refundid, 'fullback_log' => $fullback_log, 'fullbackgoods' => $fullbackgoods, 'orderprice' => $orderprice);
	}

	/**
     * 退换货申请-填写换货物流
     * @global type $_W
     * @global type 
     */
	public function express()
	{
		extract($this->globalData());

		if (empty($refundid)) {
			$this->result(0, '参数错误!');
		}

		if (empty(input('expresssn'))) {
			$this->result(0, '请填写快递单号');
		}
		$refund = Db::name('shop_order_refund')->where('id',$refundid)->where('orderid',$orderid)->find();
		$refund_data = array('status' => 4, 'express' => trim(input('express')), 'expresscom' => trim(input('expresscom')), 'expresssn' => trim(input('expresssn')), 'sendtime' => time(), 'lastupdate' => time());
		Db::name('shop_order_refund')->where('id',$refundid)->update($refund_data);
		Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 1, 'operator' => '我', 'title' => '买家寄回退换货商品', 'content' => '填写了退换货商品，快递：' . trim(input('expresscom')) . ',快递单号：' . trim(input('expresssn')), 'link' => 'expresslist?expresscom=' . $refund_data['expresscom'] . '&express=' . $refund_data['express'] . '&expresssn=' . $refund_data['expresssn'], 'createtime' => time()));
		$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$refund['id'])->where('og.id','in',$goodsids)->field('og.id,og.goodsid,og.total,g.title,g.thumb,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs')->select();
		if ($refund['rtype'] == 2) {
			$refundstate = 44;
		} elseif ($refund['rtype'] == 0) {
			$refundstate = 34;
		} else {
			if ($refund['rtype'] == 1) {
				$refundstate = 24;
			}
		}

		foreach ($goods as $key => $value) {
			Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
		}
		$this->result(1,'success');
	}

	/**
     * 退换货申请-确认收货
     * @global type $_W
     * @global type 
     */
	public function receive()
	{
		extract($this->globalData());
		$refund = Db::name('shop_order_refund')->where('id',$refundid)->where('orderid',$orderid)->find();

		if (empty($refund)) {
			$this->result(0, '换货申请未找到!');
		}

		$time = time();
		$refund_data = array();
		$refund_data['status'] = 1;
		$refund_data['refundtime'] = $time;
		$refund_data['lastupdate'] = $time;
		Db::name('shop_order_refund')->where('id',$refundid)->update($refund_data);
		$order_data = array();

		$refundcount = Db::name('shop_order_refund')->where('orderid',$orderid)->where('status','in',[0,3,4,5])->count();
		if($refundcount == 0) {
			$order_data['status'] = -1;
			$order_data['refundstate'] = 0;
		}		
		$order_data['refundtime'] = $time;
		Db::name('shop_order')->where('id',$orderid)->update($order_data);
		Db::name('shop_order_refund_log')->insert(array('refundid' => $refundid, 'msgtype' => 1, 'operator' => '我', 'title' => '买家签收换货物流', 'content' => '签收了换货物流', 'createtime' => time()));
		$goodsids = array_unique(array_filter(explode(",", $refund['goodsids'])));
		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option op','og.optionid = op.id','left')->where('og.refundid',$refund['id'])->where('og.orderid',$orderid)->field('og.id,og.goodsid,og.total,g.title,g.thumb,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs')->select();
		if ($refund['rtype'] == 2) {
			$refundstate = 47;
		} elseif ($refund['rtype'] == 0) {
			$refundstate = 37;
		} else {
			if ($refund['rtype'] == 1) {
				$refundstate = 27;
			}
		}
		foreach ($goods as $key => $value) {
			Db::name('shop_order_goods')->where('id',$value['id'])->update(array('refundid' => $refundid, 'rstate' => $refundstate, 'prohibitrefund' => 1));
		}
		$this->result(1,'success');
	}

}