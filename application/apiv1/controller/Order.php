<?php
/**
 * apiv1 订单
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Order extends Base
{
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
	 * 订单列表
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function orderlist()
	{
		$mid = $this->getMemberId();
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$show_status = input('status');
		$r_type = array('退款', '退货退款', '换货');
		$condition = ' mid= ' . $mid .' and ismr=0 and deleted=0 ';
		$merchdata = $this->merchData();
		extract($merchdata);
		$shopset = $this->shopset;
		$condition .= ' and merchshow=1 ';
		if ($show_status != '') {
			$show_status = intval($show_status);
			switch ($show_status) {
				case 0: $condition .= ' and status=0 and paytype!=3';
				break;

				case 1: $condition .= ' and (status=1 or (status=0 and paytype=3))';
				break;

				case 2: $condition .= ' and (status=2 or status=0 and paytype=3)';
				break;

				case 4: $condition .= ' and refundstate>0';
				break;

				case 5: $condition .= ' and userdeleted=1 ';
				break;

				case 7: $condition .= ' and status=3 and iscomment < 2';
				break;

				default: $condition .= ' and status=' . intval($show_status);	
			}
			if ($show_status != 5) {
				$condition .= ' and userdeleted=0 ';
			}
		} else {
			$condition .= ' and userdeleted=0 ';
		}		
		$list = Db::name('shop_order')->where($condition)->order('createtime','desc')->field('id,addressid,ordersn,price,dispatchprice,status,isverify,verifyendtime,verified,verifycode,verifytype,iscomment,refundid,expresscom,express,expresssn,finishtime,`virtual`,sendtype,paytype,refundstate,dispatchtype,verifyinfo,merchid,isparent,userdeleted,createtime')->order('createtime','desc')->page($page,$pagesize)->select();
		$refunddays = intval($shopset['trade']['refunddays']);
		$closeorder = $shopset['trade']['closeorder'] ? intval($shopset['trade']['closeorder']) * 3600 * 24 : intval(7 * 3600 * 24);

		foreach ($list as &$row) {
			if ($row['isparent'] == 1) {
				$scondition = ' og.parentorderid=' . $row['id'];
			}
			else {
				$scondition = ' og.orderid=' . $row['id'];
			}

			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id','left')->join('shop_goods_option op','og.optionid = op.id','left')->where($scondition)->field('og.goodsid,og.total,g.title,g.thumb,g.type,g.status,og.price as marketprice,ifnull(og.optionname,"") as optiontitle,ifnull(og.optionid,0) as optionid,ifnull(op.specs,"") as specs,g.merchid,og.sendtype,og.expresscom,og.expresssn,og.express,og.sendtime,og.finishtime,og.remarksend')->order('og.id','asc')->select();
			$row["isonlyverifygoods"] = false;
			foreach( $goods as &$r ) 
			{
				if( !empty($r["specs"]) ) {
					$thumb = model("goods")->getSpecThumb($r["specs"]);
					if( !empty($thumb) ) {
						$r["thumb"] = $thumb;
					}
				}
				if( $r["type"] == 5 ) {
					$row["isonlyverifygoods"] = true;
				}
				if( empty($r["gtitle"]) != true ) {
					$r["title"] = $r["gtitle"];
				}
			}
			unset($r);
			$goodstotal = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','og.goodsid = g.id','left')->join('shop_goods_option op','og.optionid = op.id','left')->where($scondition)->sum('og.total');
			$row['goods'] = set_medias($goods, 'thumb');
			$row['goodstotal'] = $goodstotal;

			switch ($row['status']) {
				case '-1': $statusstr = '已取消';$status = 10;
				break;

				case '0': if ($row['paytype'] == 3) {
					$statusstr = '待发货';$status = 31;
				} else {
					if((time() - $row['createtime']) > $closeorder) {
						$statusstr = '支付超时';$status = 22;
					} else {
						$statusstr = '待付款';$status = 21;
					}					
				}

				break;

				case '1': if( $row["isverify"] == 1 ) {
					$statusstr = '使用中';$status = 30;
					if( 0 < $row["verifyendtime"] && $row["verifyendtime"] < time() ) {
						$statusstr = '已过期';$status = 31;
					}
				} else {
					if( empty($row["addressid"]) ) {
						if( !empty($row["ccard"]) ) {
							$statusstr = '充值中';$status = 30;
						} else {
							$statusstr = '待取货';$status = 30;
						}
					} else {
						$statusstr = '等待卖家发货';$status = 30;
						if( 0 < $row["sendtype"] ) {
							$statusstr = '部分发货';$status = 30;
						}
					}
				}

				break;
				case '2': $statusstr = '待收货';$status = 40;

				break;
				case '3': if (empty($row['iscomment']) || $row['iscomment'] == 1) {
					if ($show_status == 5) {
						$statusstr = '已完成';$status = 52;
					} else {
						if(time() - $row['finishtime'] > 2592000) {
							$statusstr = ((empty($shopset['trade']['closecomment']) ? '交易完成' : '评价已关闭'));$status = ((empty($shopset['trade']['closecomment']) ? 52 : 51));
						} else {							
							$statusstr = ((empty($shopset['trade']['closecomment']) ? '待评价' : '交易完成'));$status = ((empty($shopset['trade']['closecomment']) ? 50 : 51));
						}
					}
				} else {
					$statusstr = '交易完成';$status = 51;
				}
				break;
			}

			$row['canrefund'] = false;
			$canrefund = false;
			if (($row['status'] == 1) || ($row['status'] == 2)) {
				$canrefund = true;
				if (($row['status'] == 2) && ($row['price'] == $row['dispatchprice'])) {
					if (0 < $row['refundstate']) {
						$canrefund = true;
					} else {
						$canrefund = false;
					}
				}
			} else if ($row['status'] == 3) {
				if (($row['isverify'] != 1) && empty($row['virtual'])) {
					if (0 < $order['refundstate']) {
						$canrefund = true;
					} else {
						$tradeset = model('common')->getSysset('trade');
						$refunddays = intval($tradeset['refunddays']);
						if (0 < $refunddays) {
							$days = intval((time() - $row['finishtime']) / 3600 / 24);
							if ($days <= $refunddays)  {
								$canrefund = true;
							}
						}
					}
				}
			}

			$row['canrefund'] = $canrefund;
			$row['canverify'] = false;
			$canverify = false;
			$showverify = ($row['status'] == 1) && ($row["dispatchtype"] || $row["isverify"]) && !$row["isonlyverifygoods"];
			if ($row['isverify']) {
				if( !$row["isonlyverifygoods"] ) {
					if (($row['verifytype'] == 0) || ($row['verifytype'] == 1) || $row["verifytype"] == 3 ) {
						$vs = iunserializer($row['verifyinfo']);
						$verifyinfo = array( array('verifycode' => $row['verifycode'], 'verified' => ($row['verifytype'] == 0 ? $row['verified'] : $row['goods'][0]['total'] <= count($vs))) );
						if ($row['verifytype'] == 0) {
							$canverify = empty($row['verified']) && $showverify;
						} else if ($row['verifytype'] == 1) {
							$canverify = (count($vs) < $row['goods'][0]['total']) && $showverify;
						}
					} else {
						$verifyinfo = iunserializer($row['verifyinfo']);
						$last = 0;
						foreach ($verifyinfo as $v ) {
							if (!$v['verified']) {
								++$last;
							}
						}
						$canverify = (0 < $last) && $showverify;
					}
				}
				
			} else if (!empty($row['dispatchtype'])) {
				$canverify = ($row['status'] == 1) && $showverify;
			}
			$row['canverify'] = $canverify;		
			$row['statusstr'] = $statusstr;
			$row['status'] = $status;
		}
		unset($row);

		$orderlist = array();
		foreach ($list as $k => $v){
            if(empty($v['merchid'])) {
            	$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
            } else {
            	$merch = Db::name('shop_merch')->where('id',$v['merchid'])->field('id,logo,merchname')->find();
            }
            $merch['logo'] = tomedia($merch['logo']);
            $merch['order'] = $v;
            $orderlist[] = $merch;
        }
		$this->result(1,'success',array('list'=>$orderlist,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 订单详情
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function orderdetail()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$shopset = $this->shopset;
		if (empty($orderid)) {
			$this->result(0,'订单不存在');
		}
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->find();

		if (empty($order)) {
			$this->result(0,'订单不存在');
		}
		if ($order['merchshow'] == 0) {
			$this->result(0,'订单不存在');
		}

		if ($order['userdeleted'] == 2) {
			$this->result(0,'订单已经被删除!');
		}
		$isonlyverifygoods = model("order")->checkisonlyverifygoods($order["id"]);
		$order['isonlyverifygoods'] = $isonlyverifygoods;
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		$merchdata = $this->merchData();
		extract($merchdata);

		$merchid = $order['merchid'];

		if ($order['isparent'] == 1) {
			$scondition = ' og.parentorderid=' . $orderid;
		} else {
			$scondition = ' og.orderid=' . $orderid;
		}

		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->where($scondition)->field('og.id,og.goodsid,og.price as marketprice,g.title,g.thumb,g.status,og.total,g.credit,og.optionid,og.optionname as optiontitle,g.isverify,g.storeids,og.seckill,g.isfullback,og.seckill_taskid,og.prohibitrefund,g.cannotrefund,og.refundid,og.rstate,og.refundtime')->select();
		$prohibitrefund = false;
		foreach ($goods as &$val) {
			if( $val["isfullback"] ) {
				$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid = ' . $val['goodsid'])->find();
				if( $val["optionid"] ) {
					$option = Db::name('shop_goods_option')->where('id',$val['optionid'])->field('`day`,allfullbackprice,fullbackprice,allfullbackratio,fullbackratio,isfullback')->find();
					$fullbackgoods["minallfullbackallprice"] = $option["allfullbackprice"];
					$fullbackgoods["fullbackprice"] = $option["fullbackprice"];
					$fullbackgoods["minallfullbackallratio"] = $option["allfullbackratio"];
					$fullbackgoods["fullbackratio"] = $option["fullbackratio"];
					$fullbackgoods["day"] = $option["day"];
				}
				$val["fullbackgoods"] = $fullbackgoods;
				unset($fullbackgoods);
				unset($option);
			}
			$canrefund = false;
			if(empty($val['prohibitrefund']) && empty($val['cannotrefund'])) {
				$canrefund = true;
			} else {
				$prohibitrefund = true;
			}
			$val['canrefund'] = $canrefund;
		}
		unset($val);
		$goodsrefund = true;
		if( !empty($goods) ) {
			foreach( $goods as &$g ) {
				$goodsid_array[] = $g["goodsid"];
				if( !empty($g["optionid"]) ) {
					$thumb = model("goods")->getOptionThumb($g["goodsid"], $g["optionid"]);
					if( !empty($thumb) ) {
						$g["thumb"] = $thumb;
					}
				}
				if( !empty($g["cannotrefund"]) && $order["status"] == 2 ) {
					$goodsrefund = false;
				}
			}
			unset($g);
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
		$merch = array();

		if (0 < $merchid) {
			$merch = Db::name('shop_merch')->where('id',$merchid)->field('id,merchname,logo')->find();
		} else{
			$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
		}
		if(!empty($merch)) {
			$merch['logo'] = tomedia($merch['logo']);
		}		

		$stores = array();
		$showverify = false;
		$canverify = false;
		$verifyinfo = false;
		$canrefund = false;
		$showverify = ($order['status'] == 1) && ($order['dispatchtype'] || $order['isverify']);
		$vs = array();
		if ($order['isverify']) {
			$storeids = array();
			foreach ($goods as $g ) {
				if (!empty($g['storeids'])) {
					$storeids = array_merge(explode(',', $g['storeids']), $storeids);
				}
			}
			if( empty($storeids) ) {
				if( 0 < $merchid ) {
					$total = Db::name('shop_store')->where(' merchid=' . $merchid . ' and status=1 and type in(2,3) ')->count();
					$stores = Db::name('shop_store')->where(' merchid=' . $merchid . ' and status=1 and type in(2,3) ')->limit(1)->select();
				} else {
					$total = Db::name('shop_store')->where(' status=1 and type in(2,3)')->count();
					$stores = Db::name('shop_store')->where(' status=1 and type in(2,3)')->limit(1)->select();
				}
			} else {
				if( 0 < $merchid ) {
					$total = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->count();
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->limit(1)->select();
				} else {
					$total = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->count();
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->limit(1)->select();
				}
			}
			$stores = set_medias($stores,'logo');
			$stores = array('list'=>$stores,'total'=>$total);
			if (($order['verifytype'] == 0) || ($order['verifytype'] == 1) || ($order['verifytype'] == 3)) {
				$vs = iunserializer($order['verifyinfo']);
				$verifyinfo = array( array('verifycode' => $order['verifycode'], 'verified' => ($order['verifytype'] == 0 ? $order['verified'] : $goods[0]['total'] <= count($vs))) );
				if ($order['verifytype'] == 0) {
					$canverify = empty($order['verified']) && $showverify;
				} else if ($order['verifytype'] == 1) {
					$canverify = (count($vs) < $goods[0]['total']) && $showverify;
				}
			} else {
				$verifyinfo = iunserializer($order['verifyinfo']);
				$last = 0;
				foreach ($verifyinfo as $v ) {
					if (!$v['verified']) {
						++$last;
					}
				}
				$canverify = (0 < $last) && $showverify;
			}
		} else if (!empty($order['dispatchtype'])) {
			$verifyinfo = array( array('verifycode' => $order['verifycode'], 'verified' => $order['status'] == 3) );
		}
		$canverify = ($order['status'] == 1) && $showverify;
		$order['vs'] = $vs;
		$order['canverify'] = $canverify;
		$order['showverify'] = $showverify;
		$order['carrier'] = iunserializer($order['carrier']);
		$order['verifyinfo'] = $verifyinfo;
		$order['virtual_str'] = str_replace("\n", '<br/>', $order['virtual_str']);
		if (($order['status'] == 1) || ($order['status'] == 2)) {
			$canrefund = true;
			if (($order['status'] == 2) && ($order['price'] == $order['dispatchprice'])) {
				if (0 < $order['refundstate']) {
					$canrefund = true;
				} else {
					$canrefund = false;
					if( !$goodsrefund ) {
						$canreturn = false;
					} else {
						$canreturn = true;
					}
				}
			}
		} else if ($order['status'] == 3) {
			if (($order['isverify'] != 1) && empty($order['virtual'])) {
				if (0 < $order['refundstate']) {
					$canrefund = true;
				} else {
					$tradeset = model('common')->getSysset('trade');
					$refunddays = intval($tradeset['refunddays']);
					if (0 < $refunddays) {
						$days = intval((time() - $order['finishtime']) / 3600 / 24);
						if ($days <= $refunddays) {
							$canrefund = true;
						}
					}
				}
			}
		}
		if( $prohibitrefund ) {
			$canrefund = false;
		}
		if( !$goodsrefund && $canrefund ) {
			$canrefund = false;
		}
		$haveverifygoodlog = model("order")->checkhaveverifygoodlog($orderid);
		if( $haveverifygoodlog ) {
			$canrefund = false;
		}
		$order['canrefund'] = $canrefund;
		$order['goods'] = $goods;
		$order['address'] = $address ? $address : (object)null;
		
		$closeorder = $shopset['trade']['closeorder'] ? intval($shopset['trade']['closeorder']) * 3600 * 24 : intval(7 * 3600 * 24);
		switch ($order['status']) {
			case '-1': $statusstr = '已取消';$status = 10;
			break;

			case '0': if ($order['paytype'] == 3) {
				$statusstr = '待发货';$status = 21;
			} else {
				if((time() - $order['createtime']) > $closeorder) {
					$statusstr = '支付超时';$status = 22;
				} else {
					$statusstr = '待付款';$status = 21;
				}					
			}

			break;

			case '1': if ($order['isverify'] == 1) {
				$statusstr = '使用中';$status = 30;
				if( 0 < $order["verifyendtime"] && $order["verifyendtime"] < time() ) {
					$statusstr = '已过期';$status = 31;
				}
			} else {
				if( empty($order["addressid"]) ) {
					if( !empty($order["ccard"]) ) {
						$statusstr = '充值中';$status = 30;
					} else {
						$statusstr = '待取货';$status = 30;
					}
				} else {
					$statusstr = '等待卖家发货';$status = 30;
					if( 0 < $order["sendtype"] ) {
						$statusstr = '部分发货';$status = 30;
					}
				}
			}

			break;
			case '2': $statusstr = '待收货';$status = 40;

			break;
			case '3': if (empty($order['iscomment']) || $order['iscomment'] == 1) {
				if ($show_status == 5) {
					$statusstr = '已完成';$status = 52;
				} else {
					if(time() - $order['finishtime'] > 2592000) {
						$statusstr = ((empty($shopset['trade']['closecomment']) ? '评价已关闭' : '已完成'));$status = ((empty($shopset['trade']['closecomment']) ? 52 : 51));
					} else {
						$statusstr = ((empty($shopset['trade']['closecomment']) ? '待评价' : '评价已关闭'));$status = ((empty($shopset['trade']['closecomment']) ? 50 : 51));
					}
				}
			} else {
				$statusstr = '交易完成';$status = 51;
			}
			break;
		}

		$order['statusstr'] = $statusstr;
		$order['status'] = $status;

        $orderprice = array(array('name'=>'商品总价','value'=>$order['goodsprice']));
        if(!empty($order['discountprice'])) {
        	$orderprice = array_merge($orderprice,array(array('name'=>'折扣','value'=>$order['discountprice'])));
        }
        if(!empty($order['dispatchprice'])) {
        	$orderprice = array_merge($orderprice,array(array('name'=>'运费','value'=>$order['dispatchprice'])));
        }
        $orderprice = array_merge($orderprice,array(array('name'=>'订单总价','value'=>$order['price'])));        
        $order['orderprice'] = $orderprice;

		$expresses = array();
		$order_goods = array();
		if ((2 <= $order['status']) && empty($order['isvirtual']) && empty($order['isverify'])) {
			$expresslist = model('util')->getExpressList($order['express'], $order['expresssn']);
			if (0 < count($expresslist['list'])) {
				$expresses = $expresslist['list'][0];
			}
		}
		if( 0 < $order["sendtype"] && 1 <= $order["status"] ) {
			$order_goods = Db::name('shop_order_goods')->where('orderid = ' . $orderid . ' and sendtype > 0')->group('sendtype')->order('sendtime','asc')->field('orderid,goodsid,sendtype,expresscom,expresssn,express,sendtime')->select();
			$expresslist = model("util")->getExpressList($order["express"], $order["expresssn"]);
			if( 0 < count($expresslist) ) {
				$expresses = $expresslist[0];
			}
			$order["sendtime"] = $order_goods[0]["sendtime"];
		}
		$order['expresses'] = $expresses;
		if( $order["canverify"] && $order["status"] != -1 && $order["status"] != 0 ) {
			$verifycode = $order["verifycode"];
			if( strlen($verifycode) == 8 ) {
				$verifycode = substr($verifycode, 0, 4) . " " . substr($verifycode, 4, 4);
			} else {
				if( strlen($verifycode) == 9 ) {
					$verifycode = substr($verifycode, 0, 3) . " " . substr($verifycode, 3, 3) . " " . substr($verifycode, 6, 3);
				}
			}
		}
		$order["verifycode"] = $verifycode;
		if( !empty($order["virtual"]) && !empty($order["virtual_str"]) ) {
			$ordervirtual = model("order")->getOrderVirtual($order);
			$virtualtemp = Db::name('shop_virtual_type')->where('id',$order["virtual"])->field('linktext, linkurl')->find();
		}
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
            $log[0] = array('type' => 10, 'time' => $order['canceltime'], 'remark' => '买家取消订单');
        }

        if ((0 < $order['refundstate']) && !empty($order['refundid'])) {
            $log[] = array('type' => $order['status'], 'time' => $order['refundtime'], 'remark' => $statusstr);
        }
        $order['log'] = $log;
		$order['verifystores'] = $stores;
		$merch['order'] = $order;
		$this->result(1,'success',$merch);
	}

	/**
	 * 订单核销适用门店
	 * @param $orderid [订单id]
	 * @return  [array]    $list  [门店列表]
	 **/
	public function stores()
	{
		$goodsid = intval(input('goodsid'));
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		if (empty($goodsid)) {
			$this->result(0,'商品不存在');
		}
		$mid = $this->getMemberId();
		$goods = Db::name('shop_goods')->where('id = ' . $goodsid)->field('isverify,storeids')->find();		

		if (empty($goods)) {
			$this->result(0,'订单不存在');
		}
		$storeids = array();

		if ($goods['isverify'] == 2) {
			if (!empty($goods['storeids'])) {
				$storeids = array_merge(explode(',', $goods['storeids']), $storeids);
			}
			if( empty($storeids) ) {
				if( 0 < $merchid ) {
					$stores = Db::name('shop_store')->where(' merchid=' . $merchid . ' and status=1 and type in(2,3) ')->page($page,$pagesize)->select();
				} else {
					$stores = Db::name('shop_store')->where(' status=1 and type in(2,3)')->page($page,$pagesize)->select();
				}
			} else {
				if( 0 < $merchid ) {
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->page($page,$pagesize)->select();
				} else {
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->page($page,$pagesize)->select();
				}
			}
		}
		
		$stores = set_medias($stores,'logo');
		$this->result(1,'success',array('list'=>$stores,'page'=>$page,'pagesize'=>$pagesize));
	}


	/**
	 * 订单信息确认
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function confirm()
	{
		$mid = $this->getMemberId();
		$goodsid = input('goodsid/d');
		$optionid = input('optionid/d');
		$total = input('total/d',1);
		$giftid = input('giftid/d');
		$giftGood = array();
		$packageid = intval(input('packageid'));
		$trade = model('common')->getSysset('trade');
		$shopset = $this->shopset;
		$shop = $shopset['shop'];
		$member = model('member')->getMember($mid);
		$level = model('member')->getLevel($mid);

		$is_openmerch = 0;
		if (!($packageid)) {
			$merchdata = $this->merchData();
			extract($merchdata);
			$merch_array = array();
			$merchs = array();
			$merch_id = 0;
			$total_array = array();
			$member['carrier_mobile'] = $member['mobile'];
			$share = model('common')->getSysset('share');
			$iswholesale = input('iswholesale/d');
			$bargain_id = input('bargainid/d');
			if (!(empty($bargain_id))) {
				$bargain_act = Db::name('shop_bargain_actor')->where('id',$bargain_id)->where('mid',$mid)->where('status',0)->find();

				if (empty($bargain_act)) {
					$this->result(0,'不能重复购买!');
				}

				$bargain_act_id = Db::name('shop_bargain_goods')->where('id',$bargain_act['goods_id'])->find();

				if (empty($bargain_act_id)) {
					$this->result(0,'此商品不能重复购买!');
				}

				$if_bargain = Db::name('shop_goods')->where('id',$bargain_act_id['goods_id'])->field('bargain')->find();

				if (empty($if_bargain['bargain'])) {
					$this->result(0,'此商品不能重复购买!');
				}

				$id = $bargain_act_id['goods_id'];
			}
			if ($total < 1) {
				$total = 1;
			}
			$buytotal = $total;
			$errcode = 0;
			$isverify = false;
			$isvirtual = false;
			$isvirtualsend = false;
			$isonlyverifygoods = true;
			$changenum = false;
			$fromcart = 0;
			$hasinvoice = false;
			$invoicename = '';
			$buyagain_sale = true;
			$buyagainprice = 0;
			$goods = array();
			if (empty($goodsid)) {
				$goods = Db::name('shop_member_cart')->alias('c')
					->join('shop_goods g','c.goodsid = g.id','left')
					->join('shop_goods_option og','c.optionid = og.id','left')
					->where('c.mid = ' . $mid . ' and c.selected = 1 and c.deleted = 0')
					->field('c.goodsid,c.total,g.maxbuy,g.type,g.intervalfloor,g.intervalprice,g.issendfree,g.isnodiscount,g.ispresell,g.presellprice as gpprice,ifnull(og.presellprice,0) as presellprice,g.preselltimeend,g.presellsendstatrttime,g.presellsendtime,g.presellsendtype,g.weight,og.weight as optionweight,g.title,g.thumb,ifnull(og.marketprice, g.marketprice) as marketprice,ifnull(og.title,"") as optiontitle,c.optionid,g.storeids,g.isverify,g.deduct,g.manydeduct,g.virtual,og.virtual as optionvirtual,discounts,g.deduct2,g.ednum,g.edmoney,g.edareas,g.dispatchtype,g.dispatchid,g.dispatchprice,g.minbuy,g.isdiscount,g.isdiscount_time,g.isdiscount_discounts,g.cates,g.virtualsend,invoice,ifnull(og.specs,""),g.merchid,g.checked,g.merchsale,g.buyagain,g.buyagain_islong,g.buyagain_condition, g.buyagain_sale, g.hasoption')
					->order('c.id','desc')
					->select();
				if (empty($goods)) {
					$this->result(0,'购物车没有商品!');
				} else {
					foreach ($goods as $k => $v ) {
						if ($v['type'] == 4) {
							$intervalprice = iunserializer($v['intervalprice']);

							if (0 < $v['intervalfloor']) {
								$goods[$k]['intervalprice1'] = $intervalprice[0]['intervalprice'];
								$goods[$k]['intervalnum1'] = $intervalprice[0]['intervalnum'];
							}

							if (1 < $v['intervalfloor']) {
								$goods[$k]['intervalprice2'] = $intervalprice[1]['intervalprice'];
								$goods[$k]['intervalnum2'] = $intervalprice[1]['intervalnum'];
							}

							if (2 < $v['intervalfloor']) {
								$goods[$k]['intervalprice3'] = $intervalprice[2]['intervalprice'];
								$goods[$k]['intervalnum3'] = $intervalprice[2]['intervalnum'];
							}
						}

						$opdata = array();

						if (0 < $v['hasoption']) {
							$opdata = model('goods')->getOption($v['goodsid'], $v['optionid']);

							if (empty($opdata) || empty($v['optionid'])) {
								$this->result(0,'商品' . $v['title'] . '的规格不存在,请到购物车删除该商品重新选择规格!');
							}

							if (!(empty($v['unite_total']))) {
								$total_array[$v['goodsid']]['total'] += $v['total'];
							}
						}

						if (!(empty($opdata))) {
							$goods[$k]['marketprice'] = $v['marketprice'];
						}

						if ((0 < $v['ispresell']) && (($v['preselltimeend'] == 0) || (time() < $v['preselltimeend']))) {
							$goods[$k]['marketprice'] = ((0 < intval($v['hasoption']) ? $v['presellprice'] : $v['gpprice']));
						}


						$fullbackgoods = array();

						if ($v['isfullback']) {
							$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$v['goodsid'])->find();
						}

						if ($is_openmerch == 0) {
							if (0 < $v['merchid']) {
								$this->result(0,'多商户未开启');
							}
						} else if ((0 < $v['merchid']) && ($v['checked'] == 1)) {
							$this->result(0,'商品' . $v['title'] . '不能购买');
						}

						if (!(empty($v['optionvirtual']))) {
							$goods[$k]['virtual'] = $v['optionvirtual'];
						}

						if (!(empty($v['optionweight']))) {
							$goods[$k]['weight'] = $v['optionweight'];
						}
					}
					$goods = model('goods')->wholesaleprice($goods);
					foreach ($goods as $k => $v ) {
						if ($v['type'] == 4) {
							$goods[$k]['marketprice'] = $v['wholesaleprice'];
						}
					}
				}
				$fromcart = 1;
			} else if(!(empty($goodsid)) && !(empty($iswholesale))) {
				$data = Db::name('shop_goods')->where('id',$goodsid)->field('id as goodsid,type,title,weight,issendfree,isnodiscount,ispresell,presellprice,thumb,marketprice,storeids,isverify,deduct,hasoption,preselltimeend,presellsendstatrttime,presellsendtime,presellsendtype,manydeduct,`virtual`,maxbuy,usermaxbuy,discounts,total as stock,deduct2,showlevels,ednum,edmoney,edareas,edareas_code,unite_total,dispatchtype,dispatchid,dispatchprice,cates,minbuy,isdiscount,isdiscount_time,isdiscount_discounts,virtualsend,invoice,needfollow,followtip,followurl,merchid,checked,merchsale,buyagain,buyagain_islong,buyagain_condition, buyagain_sale ,intervalprice ,intervalfloor')->find();
				if (empty($data) || ($data['type'] != 4)) {
					$this->result(0,'商品信息错误!!');
				}
				$intervalprice = iunserializer($data['intervalprice']);

				if (0 < $data['intervalfloor']) {
					$data['intervalprice1'] = $intervalprice[0]['intervalprice'];
					$data['intervalnum1'] = $intervalprice[0]['intervalnum'];
				}

				if (1 < $data['intervalfloor']) {
					$data['intervalprice2'] = $intervalprice[1]['intervalprice'];
					$data['intervalnum2'] = $intervalprice[1]['intervalnum'];
				}

				if (2 < $data['intervalfloor']) {
					$data['intervalprice3'] = $intervalprice[2]['intervalprice'];
					$data['intervalnum3'] = $intervalprice[2]['intervalnum'];
				}

				$buyoptions = input('buyoptions');
				$optionsdata = json_decode(htmlspecialchars_decode($buyoptions, ENT_QUOTES), true);
				if (empty($optionsdata) || !(is_array($optionsdata))) {
					$this->result(0,'商品' . $data['title'] . '的规格不存在,请重新选择规格!');
				}
				$total = 0;

				foreach ($optionsdata as $option ) {
					$good = $data;
					$num = intval($option['total']);
					if ($num <= 0) {
						continue;
					}

					$total = $total + $num;
					$good['total'] = $num;
					$good['optionid'] = $option['optionid'];

					if (0 < $option['optionid']) {
						$option = Db::name('shop_goods_option')->where('id',$option['optionid'])->where('goodsid',$goodsid)->field('id,title,marketprice,presellprice,goodssn,productsn,`virtual`,stock,weight,specs')->find();

						if (!(empty($option))) {
							$good['optiontitle'] = $option['title'];
							$good['virtual'] = $option['virtual'];
							if (empty($data['unite_total'])) {
								$data['stock'] = $option['stock'];
								if ($option['stock'] < $num) {
									$this->result(0,'商品' . $data['title'] . '的购买数量超过库存剩余数量,请重新选择规格!');
								}
							}

							if (!(empty($option['weight']))) {
								$data['weight'] = $option['weight'];
							}

							if (!(empty($option['specs']))) {
								$thumb = model('goods')->getSpecThumb($option['specs']);

								if (!(empty($thumb))) {
									$data['thumb'] = $thumb;
								}
							}
						} else if (!(empty($data['hasoption']))) {
							$this->result(0,'商品' . $data['title'] . '的规格不存在,请重新选择规格!');
						}
					}
					$goods[] = $good;
				}
				$goods = model('goods')->wholesaleprice($goods);

				foreach ($goods as $k => $v ) {
					if ($v['type'] == 4) {
						$goods[$k]['marketprice'] = $v['wholesaleprice'];
					}

				}
			} else {
				$data = Db::name('shop_goods')->where('id',$goodsid)->field('id as goodsid,type,title,weight,issendfree,isnodiscount,ispresell,presellprice,thumb,marketprice,liveprice,islive,storeids,isverify,deduct,hasoption,preselltimeend,presellsendstatrttime,presellsendtime,presellsendtype,manydeduct,`virtual`,maxbuy,usermaxbuy,discounts,total as stock,deduct2,showlevels,ednum,edmoney,edareas,edareas_code,unite_total,dispatchtype,dispatchid,dispatchprice,cates,minbuy,isdiscount,isdiscount_time,isdiscount_discounts,isfullback,virtualsend,invoice,needfollow,followtip,followurl,merchid,checked,merchsale,buyagain,buyagain_islong,buyagain_condition, buyagain_sale,threen')->find();
				if ((0 < $data['ispresell']) && (($data['preselltimeend'] == 0) || (time() < $data['preselltimeend']))) {
					$data['marketprice'] = $data['presellprice'];
				}

				if ($data['type'] == 4) {
					$this->result(0,'暂不支持批发商品!');
				}
				if ($giftid) {
					$gift = Db::name('shop_gift')->where('id',$giftid)->where('status',1)->where('starttime','<=',time())->where('endtime','>=',time())->find();

					if (!(strstr($gift['goodsid'], (string) $goodsid))) {
						$this->result(0,'赠品与商品不匹配或者商品没有赠品!');
					}

					$giftGood = array();
					if (!(empty($gift['giftgoodsid']))) {
						$giftGoodsid = explode(',', $gift['giftgoodsid']);

						if ($giftGoodsid) {
							foreach ($giftGoodsid as $key => $value ) {
								$giftGood[$key] = Db::name('shop_goods')->where('total','>',0)->where('status',2)->where('id',$value)->where('deleted',0)->find();
							}
							$giftGood = array_filter($giftGood);
						}
					}
				}

				if (!(empty($bargain_act))) {
					$data['marketprice'] = $bargain_act['now_price'];
				}

				$fullbackgoods = array();

				if ($data['isfullback']) {
					$fullbackgoods = Db::name('shop_fullback_goods')->where('goodsid',$data['goodsid'])->find();
				}

				if (empty($data) || ((0 < $data['merchid']) && ($data['checked'] == 1)) || (($is_openmerch == 0) && (0 < $data['merchid']))) {
					$this->result(0,'商品信息错误!');
				}
				if (!(empty($data['showlevels'])) && !(strexists($data['showlevels'], $member['level']))) {
					$this->result(0,'您沒有购买权限!');
				}

				if ((0 < $data['minbuy']) && ($total < $data['minbuy'])) {
					$total = $data['minbuy'];
				}

				$data['total'] = $total;
				$data['optionid'] = $optionid;
				if(!empty($data['hasoption']) && empty($optionid)) {
					$this->result(0,'请选择商品规格!');
				}
				if (!(empty($optionid))) {
					$option = Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goodsid)->field('id,title,marketprice,liveprice,islive,presellprice,goodssn,productsn,`virtual`,stock,weight,specs,`day`,allfullbackprice,fullbackprice,allfullbackratio,fullbackratio,isfullback')->find();

					if (!(empty($option))) {
						$data['optionid'] = $optionid;
						$data['optiontitle'] = $option['title'];

						$data['marketprice'] = (((0 < intval($data['ispresell'])) && ((time() < $data['preselltimeend']) || ($data['preselltimeend'] == 0)) ? $option['presellprice'] : $option['marketprice']));
						if ($isliving && !(empty($option['islive'])) && (0 < $option['liveprice'])) {
							$data['marketprice'] = $option['liveprice'];
						}

						$data['virtual'] = $option['virtual'];

						if ($option['isfullback']) {
							$fullbackgoods['minallfullbackallprice'] = $option['allfullbackprice'];
							$fullbackgoods['fullbackprice'] = $option['fullbackprice'];
							$fullbackgoods['minallfullbackallratio'] = $option['allfullbackratio'];
							$fullbackgoods['fullbackratio'] = $option['fullbackratio'];
							$fullbackgoods['day'] = $option['day'];
						}

						if (empty($data['unite_total'])) {
							$data['stock'] = $option['stock'];
						}

						if (!(empty($option['weight']))) {
							$data['weight'] = $option['weight'];
						}
					} else if (!(empty($data['hasoption']))) {
						$this->result(0,'商品' . $data['title'] . '的规格不存在,请重新选择规格!');
					}
				}
				if ($giftid) {
					$changenum = false;
				} else {
					$changenum = true;
				}

				if ($data['seckillinfo'] && ($data['seckillinfo']['status'] == 0)) {
					$changenum = false;
				}

				$goods[] = $data;
			}

			if(empty($goods)) {
				$this->result(0,'操作失败请重试！');
			}

			$goods = set_medias($goods, 'thumb');

			foreach ($goods as &$g ) {
				if ($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0)) {
					$g['is_task_goods'] = 0;
				}

				if ($is_openmerch == 1) {
					$merchid = $g['merchid'];
					$merch_array[$merchid]['goods'][] = $g['goodsid'];
				}

				if ($g['isverify'] == 2) {
					$isverify = true;
				}

				if (!(empty($g['virtual'])) || ($g['type'] == 2) || ($g['type'] == 3) || ($g['type'] == 20)) {
					$isvirtual = true;
					if ($g['virtualsend']) {
						$isvirtualsend = true;
					}
				}

				if ($g['invoice']) {
					$hasinvoice = $g['invoice'];
				}

				if ($g['type'] != 5) {
					$isonlyverifygoods = false;
				}

				$totalmaxbuy = $g['stock'];

				if (!(empty($g['seckillinfo'])) && ($g['seckillinfo']['status'] == 0)) {
					$seckilllast = 0;

					if (0 < $g['seckillinfo']['maxbuy']) {
						$seckilllast = $g['seckillinfo']['maxbuy'] - $g['seckillinfo']['selfcount'];
					}

					$g['totalmaxbuy'] = $g['total'];
				} else {
					if (0 < $g['maxbuy']) {
						if ($totalmaxbuy != -1) {
							if ($g['maxbuy'] < $totalmaxbuy) {
								$totalmaxbuy = $g['maxbuy'];
							}
						}
						 else {
							$totalmaxbuy = $g['maxbuy'];
						}
					}

					if (0 < $g['usermaxbuy']) {
						$order_goodscount = Db::name('shop_order_goods')->alias('og')->join('shop_order o','og.orderid=og.id','left')->where('og.goodsid',$g['goodsid'])->where('o.status','>=',0)->where('og.mid',$mid)->sum('og.total');
						$last = $data['usermaxbuy'] - $order_goodscount;

						if ($last <= 0) {
							$last = 0;
						}

						if ($totalmaxbuy != -1) {
							if ($last < $totalmaxbuy) {
								$totalmaxbuy = $last;
							}
						}
						else {
							$totalmaxbuy = $last;
						}
					}

					if (!(empty($g['is_task_goods']))) {
						if ($g['task_goods']['total'] < $totalmaxbuy) {
							$totalmaxbuy = $g['task_goods']['total'];
						}

					}


					$g['totalmaxbuy'] = $totalmaxbuy;

					if (($g['totalmaxbuy'] < $g['total']) && !(empty($g['totalmaxbuy']))) {
						$g['total'] = $g['totalmaxbuy'];
					}


					if ((0 < floatval($g['buyagain'])) && empty($g['buyagain_sale'])) {
						if (model('goods')->canBuyAgain($g)) {
							$buyagain_sale = false;
						}

					}

				}
			}
			unset($g);

			if ($hasinvoice) {
				$invoicename = Db::name('shop_order')->where('mid = '.$mid.' and ifnull(invoicename,\'\')<>\'\'')->value('invoicename');
				if (empty($invoicename)) {
					$invoicename = $member['realname'];
				}
			}

			if ($is_openmerch == 1) {
				foreach ($merch_array as $key => $value ) {
					if (0 < $key) {
						$merch_id = $key;
						$merch_array[$key]['set'] = model('merch')->getSet('sale', $key);
						$merch_array[$key]['enoughs'] = model('merch')->getEnoughs($merch_array[$key]['set']);
					}
				}
			}

			$weight = 0;
			$total = 0;
			$goodsprice = 0;
			$realprice = 0;
			$deductprice = 0;
			$taskdiscountprice = 0;
			$lotterydiscountprice = 0;
			$discountprice = 0;
			$isdiscountprice = 0;
			$deductprice2 = 0;
			$stores = array();
			$address = false;
			$needaddress = false;
			$dispatch_list = false;
			$dispatch_price = 0;
			$seckill_dispatchprice = 0;
			$seckill_price = 0;
			$seckill_payprice = 0;
			$ismerch = 0;

			if ($is_openmerch == 1) {
				if (!empty($merch_array)) {
					if (1 < count($merch_array)) {
						$ismerch = 1;
					}
				}
			}
			foreach ($goods as &$g ) {
				if (empty($g['total']) || (intval($g['total']) < 1)) {
					$g['total'] = 1;
				}
				if ($taskcut || ($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0))) {
					$gprice = $g['ggprice'] = $g['seckillinfo']['price'] * $g['total'];
					$seckill_payprice += $g['seckillinfo']['price'] * $g['total'];
					$seckill_price += ($g['marketprice'] * $g['total']) - $gprice;
				} else {
					$gprice = $g['marketprice'] * $g['total'];
					$prices = model('order')->getGoodsDiscountPrice($g, $level);
					$g['ggprice'] = $prices['price'];
					$g['unitprice'] = $prices['unitprice'];
				}

				if ($is_openmerch == 1) {
					$merchid = $g['merchid'];
					$merch_array[$merchid]['ggprice'] += $g['ggprice'];
					$merchs[$merchid] += $g['ggprice'];
				}

				$g['dflag'] = intval($g['ggprice'] < $gprice);
				if (($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0)) || $_SESSION['taskcut']) {
				} else if (empty($bargain_id)) {
					$taskdiscountprice += $prices['taskdiscountprice'];
					$lotterydiscountprice += $prices['lotterydiscountprice'];
					$g['taskdiscountprice'] = $prices['taskdiscountprice'];
					$g['lotterydiscountprice'] = $prices['lotterydiscountprice'];
					$g['discountprice'] = $prices['discountprice'];
					$g['isdiscountprice'] = $prices['isdiscountprice'];
					$g['discounttype'] = $prices['discounttype'];
					$g['isdiscountunitprice'] = $prices['isdiscountunitprice'];
					$g['discountunitprice'] = $prices['discountunitprice'];
					$buyagainprice += $prices['buyagainprice'];

					if ($prices['discounttype'] == 1) {
						$isdiscountprice += $prices['isdiscountprice'];
					}
					else if ($prices['discounttype'] == 2) {
						$discountprice += $prices['discountprice'];
					}
				}

				$realprice += $g['ggprice'];

				if ($g['ggprice'] < $gprice) {
					$goodsprice += $gprice;
					$g['realprice'] = $gprice;
				} else {
					$goodsprice += $g['ggprice'];
					$g['realprice'] = $g['ggprice'];
				}
				$total += $g['total'];
				$weight += $g['weight'];

				if(($g['type'] == 1 || $g['type'] == 4) && !$address && $g['isverify'] != 2) {
					$needaddress = true;
				}

				if (empty($bargain_id)) {
					if ((0 < floatval($g['buyagain'])) && empty($g['buyagain_sale'])) {
						if (model('goods')->canBuyAgain($g)) {
							$g['deduct'] = 0;
						}
					}
					if ($g['manydeduct']) {
						$deductprice += $g['deduct'] * $g['total'];
					} else {
						$deductprice += $g['deduct'];
					}

					if ($g['deduct2'] == 0) {
						$deductprice2 += $g['ggprice'];
					} else if (0 < $g['deduct2']) {
						if ($g['ggprice'] < $g['deduct2']) {
							$deductprice2 += $g['ggprice'];
						} else {
							$deductprice2 += $g['deduct2'];
						}
					}
				}
			}
			unset($g);
			if ($isverify) {
				$storeids = array();
				$merchid = 0;
				foreach ($goods as $g ) {
					$merchid = $g['merchid'];
					if (!(empty($g['storeids']))) {
						$storeids = array_merge(explode(',', $g['storeids']), $storeids);
					}
				}
				$page = 1;$pagesize = 1;
				if( empty($storeids) ) {
					if( 0 < $merchid ) {
						$total = Db::name('shop_store')->where(' merchid=' . $merchid . ' and status=1 and type in(2,3) ')->count();
						$stores = Db::name('shop_store')->where(' merchid=' . $merchid . ' and status=1 and type in(2,3) ')->limit(1)->select();
					} else {
						$total = Db::name('shop_store')->where(' status=1 and type in(2,3)')->count();
						$stores = Db::name('shop_store')->where(' status=1 and type in(2,3)')->limit(1)->select();
					}
				} else {
					if( 0 < $merchid ) {
						$total = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->count();
						$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->limit(1)->select();
					} else {
						$total = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->count();
						$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->limit(1)->select();
					}
				}
				$stores = set_medias($stores,'logo');
				$stores = array('list'=>$stores,'total'=>$total);
			} else {
				$address = Db::name('shop_member_address')->where('mid = ' . $mid . ' and deleted = 0 and isdefault = 1 ')->find();

				if (!(empty($carrier_list))) {
					$carrier = $carrier_list[0];
				}

				if (!($isvirtual) && !($isonlyverifygoods)) {
					$dispatch_array = model('order')->getOrderDispatchPrice($goods, $member, $address, $saleset, $merch_array, 0);
					$dispatch_price = $dispatch_array['dispatch_price'] - $dispatch_array['seckill_dispatch_price'];
					$seckill_dispatchprice = $dispatch_array['seckill_dispatch_price'];
				}
			}
		} else {
			$g = input('goods');
			$g = json_decode(htmlspecialchars_decode($g, ENT_QUOTES), true);
			$package = Db::name('shop_package')->where('id',$packageid)->find();
			$package = set_medias($package, array('thumb'));

			if (time() < $package['starttime']) {
				$this->result(0,'套餐活动还未开始，请耐心等待!');
			}

			if ($package['endtime'] < time()) {
				$this->result(0,'套餐活动已结束，谢谢您的关注，请浏览其他套餐或商品！');
			}

			$goods = array();
			$goodsprice = 0;
			$marketprice = 0;

			foreach ($g as $key => $value ) {
				$goods[$key] = Db::name('shop_goods')->where('id',$value['goodsid'])->field('id,title,thumb,marketprice')->find();
				$option = array();
				$packagegoods = array();

				if (0 < $value['optionid']) {
					$option = Db::name('shop_package_goods_option')->where('optionid',$value['optionid'])->where('goodsid',$value['goodsid'])->where('pid',$packageid)->field('title,packageprice,marketprice')->find();
					$goods[$key]['packageprice'] = $option['packageprice'];
				}
				 else {
					$packagegoods = Db::name('shop_package_goods')->where('goodsid',$value['goodsid'])->where('pid',$packageid)->field('title,packageprice,marketprice')->find();
					$goods[$key]['packageprice'] = $packagegoods['packageprice'];
				}

				$goods[$key]['optiontitle'] = ((!(empty($option['title'])) ? $option['title'] : ''));
				$goods[$key]['optionid'] = ((!(empty($value['optionid'])) ? $value['optionid'] : 0));
				$goods[$key]['goodsid'] = $value['goodsid'];
				$goods[$key]['total'] = 1;

				if ($option) {
					$goods[$key]['packageprice'] = $option['packageprice'];
				}
				 else {
					$goods[$key]['packageprice'] = $goods[$key]['packageprice'];
				}

				$goodsprice += $goods[$key]['packageprice'];
				$marketprice += $goods[$key]['marketprice'];
			}
 			$address = Db::name('shop_member_address')->where('mid',$mid)->where('deleted',0)->where('isdefault',1)->find();
			$total = count($goods);
			$dispatch_price = $package['freight'];
			$realprice = $goodsprice + $package['freight'];
			$goodsprice = 0;

			foreach ($goods as $key => $value ) {
				$goodsprice += $value['marketprice'];
			}

			$createInfo = array('id' => 0, 'gdid' => input('gdid/d'), 'fromcart' => 0, 'packageid' => $packageid, 'addressid' => $address['id'], 'storeid' => 0, 'couponcount' => 0, 'isvirtual' => 0, 'isverify' => 0, 'isonlyverifygoods' => 0, 'goods' => $goods, 'merchs' => 0, 'mustbind' => 0, 'fromquick' => intval($quickid));
			$this->result('1','success',$createInfo);
		}		

		if (!($isonlyverifygoods)) {
			$dispatch_array = model('order')->getOrderDispatchPrice($goods, $member, $address, $saleset, $merch_array, 1);
			$dispatch_price = $dispatch_array['dispatch_price'] - $dispatch_array['seckill_dispatch_price'];
			$nodispatch_array = $dispatch_array['nodispatch_array'];
			$seckill_dispatchprice = $dispatch_array['seckill_dispatch_price'];
		}

		$goods_list = array();
		if ($ismerch) {
			foreach ($goods as $value) {
				$merchid = $value['merchid'];

				if (empty($merchid)) {
					$merchid = 0;
				}

				if (empty($merch[$merchid])) {
					$merch[$merchid] = array();
				}

				array_push($merch[$merchid], $value);
			}
			foreach ($merch as $k => $v ) {
				if (empty($k)) {
					$arr = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>tomedia($shopset['shop']['logo']));
					$dispatch_list = Db::name('shop_dispatch')->where('merchid',0)->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
				} else {
					$arr = Db::name('shop_store')->where('id',$k)->field('id,logo,merchname')->find();
					$arr['logo'] = tomedia($arr['logo']);
					$dispatch_list = Db::name('shop_dispatch')->where('merchid',$merch_user[$k]['id'])->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
				}
				$arr['goods'] = $v;
				
				if (!($isonlyverifygoods)) {
					$dispatch_array = model('order')->getOrderDispatchPrice($v, $member, $address, $saleset, $merch_array, 1);
					$merchdispatch_price = $dispatch_array['dispatch_price'] - $dispatch_array['seckill_dispatch_price'];
				}
				if(empty($dispatch_list)) {
					$dispatch_list = array('id'=>0,'dispatchname'=>'快递配送');
				}
				if(empty($merchdispatch_price)) {
					$arr['dispatch_list'] = array('id'=>0,'dispatchname'=>'包邮','dispatchprice'=>0);
				} else {
					$dispatch_list['dispatchprice'] = $merchdispatch_price;
					$arr['dispatch_list'] = $dispatch_list;
				}

				$totalprice = 0;
				$goodsnum = 0;
				foreach ($arr['goods'] as $row) {
					$totalprice += $row['realprice'];
					$goodsnum += $row['total'];
				}
				unset($row);
				$arr['totalprice'] = round($totalprice + $merchdispatch_price,2);
				$arr['goodsnum'] = $goodsnum;
				$goods_list[] = $arr;
			}
		} else {
			if ($merchid == 0) {
				$goods_list[0]['id'] = 0;
				$goods_list[0]['logo'] = tomedia($shopset['shop']['logo']);
				$goods_list[0]['merchname'] = $shopset['shop']['name'];
				$dispatch_list = Db::name('shop_dispatch')->where('merchid',0)->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
			} else {
				$merch_data = model('merch')->getListUserOne($merchid);
				$goods_list[0]['id'] = $merch_data['id'];
				$goods_list[0]['logo'] = tomedia($merch_data['logo']);
				$goods_list[0]['merchname'] = $merch_data['merchname'];
				$dispatch_list = Db::name('shop_dispatch')->where('merchid',$merchid)->where('isdefault',1)->where('enabled',1)->field('id,dispatchname')->order('isdefault','desc')->find();
			}

			$goods_list[0]['goods'] = $goods;
			if (!($isonlyverifygoods)) {
				$dispatch_array = model('order')->getOrderDispatchPrice($goods, $member, $address, $saleset, $merch_array, 1);
				$merchdispatch_price = $dispatch_array['dispatch_price'] - $dispatch_array['seckill_dispatch_price'];
			}
			if(empty($dispatch_list)) {
				$dispatch_list = array('id'=>0,'dispatchname'=>'快递配送');
			}
			if(empty($merchdispatch_price)) {
				$goods_list[0]['dispatch_list'] = array('id'=>0,'dispatchname'=>'包邮','dispatchprice'=>0);
			} else {
				$dispatch_list['dispatchprice'] = $merchdispatch_price;
				$goods_list[0]['dispatch_list'] = $dispatch_list;
			}
			$totalprice = 0;
			$goodsnum = 0;
			foreach ($goods as $row) {
				$totalprice += $row['realprice'];
				$goodsnum += $row['total'];
			}
			unset($row);
			$goods_list[0]['totalprice'] = round($totalprice + $merchdispatch_price,2);
			$goods_list[0]['goodsnum'] = $goodsnum;
		}
		$address_list = Db::name('shop_member_address')->where('mid',$mid)->where('deleted',0)->where('isdefault',1)->field('zipcode,isdefault,deleted',true)->find();

		$realprice = $realprice + $dispatch_price;
		$this->result(1,'success',array('list'=>$goods_list,'total'=>$total,'weight'=>$weight,'realprice'=>$realprice,'goodsprice'=>$goodsprice,'deductprice'=>$deductprice,'taskdiscountprice'=>$taskdiscountprice,'lotterydiscountprice'=>$lotterydiscountprice,'discountprice'=>$discountprice,'isdiscountprice'=>$isdiscountprice,'deductprice2'=>$deductprice2,'needaddress'=>$needaddress,'address'=>$needaddress ? ($address_list ? $address_list : (object)null) : (object)null,'dispatch_price'=>$dispatch_price, 'isverify' => $isverify, 'isvirtual' => $isvirtual, 'isonlyverifygoods' => $isonlyverifygoods, 'stores' => $stores));
	}

	/**
	 * 订单提交
	 * @param $mid [会员id]
	 * @param $statusstr [订单状态]
	 * @return  [array]    $list  [订单列表]
	 **/
	public function create()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid);
		if ($member['isblack'] == 1) {
			$this->result(0,'操作失败');
		}
		$packageid = input('packageid/d',0);
		$paytype = input('paytype/d',1);
		$fromcart = input('fromcart/d',0);
		$liveid = input('liveid/d',0);
		$package = array();
		$packgoods = array();
		$packageprice = 0;

		if (!(empty($packageid))) {
			$package = Db::name('shop_package')->where('id',$packageid)->where('deleted',0)->where('status',1)->field('id,title,price,freight,cash,starttime,endtime')->find();

			if (empty($package)) {
				$this->result(0, '未找到套餐！');
			}

			if (time() < $package['starttime']) {
				$this->result(0, '套餐活动未开始，请耐心等待！');
			}

			if ($package['endtime'] < time()) {
				$this->result(0, '套餐活动已结束，谢谢您的关注，请您浏览其他套餐或商品！');
			}

			$packgoods = Db::name('shop_package_goods')->where('pid',$packageid)->field('id,title,thumb,packageprice,`option`,goodsid')->select();
			if (empty($packgoods)) {
				$this->result(0, '未找到套餐商品！');
			}
		}

		$merchdata = $this->merchData();
		extract($merchdata);
		$merch_array = array();
		$ismerch = 0;
		$discountprice_array = array();
		$level = model('member')->getLevel($mid);
		$dispatchid = input('dispatchid/d',0);
		$dispatchtype = input('dispatchtype/d',0);
		$carrierid = input('carrierid/d',0);
		$goods = '';
        if (Request::instance()->has('goods')) {
            $goods = $_POST['goods'];
        }        
        $goods = json_decode($goods,true);
        if (empty($goods) || !(is_array($goods))) {
            $this->result(0, '未找到任何商品');
        }

        $remark = '';
        if (Request::instance()->has('remark')) {
            $remark = $_POST['remark'];
        }        
        $remark = json_decode($remark,true);

        $allgoods = array();
		$tgoods = array();
		$totalprice = 0;
		$goodsprice = 0;
		$grprice = 0;
		$weight = 0;
		$taskdiscountprice = 0;
		$lotterydiscountprice = 0;
		$discountprice = 0;
		$isdiscountprice = 0;
		$merchisdiscountprice = 0;
		$cash = 1;
		$deductprice = 0;
		$deductprice2 = 0;
		$virtualsales = 0;
		$dispatch_price = 0;
		$seckill_price = 0;
		$seckill_payprice = 0;
		$seckill_dispatchprice = 0;
		$buyagain_sale = true;
		$buyagainprice = 0;
		$isvirtual = false;
		$isverify = false;
		$isonlyverifygoods = true;
		$isendtime = 0;
		$endtime = 0;
		$verifytype = 0;
		$isvirtualsend = false;
		$couponmerchid = 0;
		$total_array = array();
		$giftid = input('giftid/d');

		if ($giftid) {
			$gift = array();
			$giftdata = Db::name('shop_gift')->where('id',$giftid)->where('status',1)->where('starttime','<=',time())->where('endtime','>=',time())->field('giftgoodsid')->find();

			if ($giftdata['giftgoodsid']) {
				$giftgoodsid = explode(',', $giftdata['giftgoodsid']);

				foreach ($giftgoodsid as $key => $value ) {
					$gift[$key] = Db::name('shop_goods')->where('deleted',0)->where('status',2)->where('id',$value)->field('id as goodsid,title,thumb')->find();
				}

				$gift = array_filter($gift);
				$goods = array_merge($goods, $gift);
			}
		}

		foreach ($goods as $g ) {
			if (empty($g)) {
				continue;
			}
			$goodsid = intval($g['goodsid']);
			$goodstotal = intval($g['total']);
			$total_array[$goodsid]['total'] += $goodstotal;
		}
		foreach ($goods as &$row) {
			$good = Db::name('shop_goods')->where('id',$row['goodsid'])->field('type,intervalfloor,intervalprice')->find();
			$row['type'] = $good['type'];
			$row['intervalfloor'] = $good['intervalfloor'];
			$intervalprice = iunserializer($good['intervalprice']);
			if (0 < $good['intervalfloor']) {
				$row['intervalprice1'] = $intervalprice[0]['intervalprice'];
				$row['intervalnum1'] = $intervalprice[0]['intervalnum'];
			}

			if (1 < $good['intervalfloor']) {
				$row['intervalprice2'] = $intervalprice[1]['intervalprice'];
				$row['intervalnum2'] = $intervalprice[1]['intervalnum'];
			}

			if (2 < $good['intervalfloor']) {
				$row['intervalprice3'] = $intervalprice[2]['intervalprice'];
				$row['intervalnum3'] = $intervalprice[2]['intervalnum'];
			}
		}
		unset($row);
		$goods = model('goods')->wholesaleprice($goods);
		foreach ($goods as $g ) {
			if (empty($g)) {
				continue;
			}
			$goodsid = intval($g['goodsid']);
			$optionid = intval($g['optionid']);
			$goodstotal = intval($g['total']);

			if ($goodstotal < 1) {
				$goodstotal = 1;
			}

			if (empty($goodsid)) {
				$this->result(0, '参数错误');
			}

			$data = Db::name('shop_goods')->where('id',$goodsid)->field('id as goodsid,title,type,intervalfloor,intervalprice, weight,total,issendfree,isnodiscount, thumb,marketprice,liveprice,cash,isverify,verifytype,goodssn,productsn,sales,istime,timestart,timeend,hasoption,isendtime,usetime,endtime,ispresell,presellprice,preselltimeend,usermaxbuy,minbuy,maxbuy,unit,buylevels,buygroups,deleted,unite_total,status,deduct,manydeduct,`virtual`,discounts,deduct2,ednum,edmoney,edareas,edareas_code,dispatchtype,dispatchid,dispatchprice,merchid,merchsale,cates,isdiscount,isdiscount_time,isdiscount_discounts, virtualsend,buyagain,buyagain_islong,buyagain_condition, buyagain_sale ,verifygoodslimittype,verifygoodslimitdate,cannotrefund')->find();

			if ((0 < $data['ispresell']) && (($data['preselltimeend'] == 0) || (time() < $data['preselltimeend']))) {
				$data['marketprice'] = $data['presellprice'];
			}

			if ($data['type'] != 5) {
				$isonlyverifygoods = false;
			} else if (!(empty($data['verifygoodslimittype']))) {
				$verifygoodslimitdate = intval($data['verifygoodslimitdate']);

				if ($verifygoodslimitdate < time()) {
					$this->result(0, '商品:"' . $data['title'] . '"的使用时间已失效,无法购买!');
				}

				if (($verifygoodslimitdate - 7200) < time()) {
					$this->result(0, '商品:"' . $data['title'] . '"的使用时间即将失效,无法购买!');
				}
			}
			if ($data['status'] == 2) {
				$data['marketprice'] = 0;
			}
			if (!(empty($data['hasoption']))) {
				$opdata = model('goods')->getOption($data['goodsid'], $optionid);
				if (empty($opdata) || empty($optionid)) {
					$this->result(0, '商品' . $data['title'] . '的规格不存在,请到购物车删除该商品重新选择规格!');
				}
			}

			if ($data['seckillinfo'] && ($data['seckillinfo']['status'] == 0)) {
				$data['is_task_goods'] = 0;
				$tgoods = false;
			} else {
				$task_goods_data = model('goods')->getTaskGoods($mid, $goodsid, $rank, $log_id, $join_id, $optionid);

				if (empty($task_goods_data['is_task_goods'])) {
					$data['is_task_goods'] = 0;
				} else {
					$allow_sale = false;
					$tgoods['title'] = $data['title'];
					$tgoods['mid'] = $mid;
					$tgoods['goodsid'] = $goodsid;
					$tgoods['optionid'] = $optionid;
					$tgoods['total'] = $goodstotal;
					$data['is_task_goods'] = $task_goods_data['is_task_goods'];
					$data['is_task_goods_option'] = $task_goods_data['is_task_goods_option'];
					$data['task_goods'] = $task_goods_data['task_goods'];
				}
			}

			$merchid = $data['merchid'];
			$merch_array[$merchid]['goods'][] = $data['goodsid'];

			if (0 < $merchid) {
				$ismerch = 1;
			}

			$virtualid = $data['virtual'];
			$data['stock'] = $data['total'];
			$data['total'] = $goodstotal;

			if ($data['cash'] != 2) {
				$cash = 0;
			}

			if (!(empty($packageid))) {
				$cash = $package['cash'];
			}

			$unit = ((empty($data['unit']) ? '件' : $data['unit']));
			if ($data['seckillinfo'] && ($data['seckillinfo']['status'] == 0)) {
				
			} else {
				if ($data['type'] != 4) {
					if (0 < $data['minbuy']) {
						if ($goodstotal < $data['minbuy']) {
							$this->result(0, $data['title'] . '' . $data['minbuy'] . $unit . '起售!');
						}
					}

					if (0 < $data['maxbuy']) {
						if ($data['maxbuy'] < $goodstotal) {
							$this->result(0, $data['title'] . '一次限购 ' . $data['maxbuy'] . $unit . '!');
						}
					}
				}

				if (0 < $data['usermaxbuy']) {
					$order_goodscount = Db::name('shop_order_goods')->alias('og')->join('shop_order o','og.orderid=og.id','left')->where('og.goodsid=' . $data['goodsid'] .' and  o.status>=0 and og.mid = ' . $mid)->sum('og.total');

					if ($data['usermaxbuy'] <= $order_goodscount) {
						$this->result(0, $data['title'] . '最多限购 ' . $data['usermaxbuy'] . $unit . '!');
					}
				}

				if (!(empty($data['is_task_goods']))) {
					if ($data['task_goods']['total'] < $goodstotal) {
						$this->result(0, $data['title'] . '任务活动优惠限购 ' . $data['task_goods']['total'] . $unit . '!');
					}
				}

				if ($data['istime'] == 1) {
					if (time() < $data['timestart']) {
						$this->result(0, $data['title'] . '限购时间未到!');
					}

					if ($data['timeend'] < time()) {
						$this->result(0, $data['title'] . '限购时间已过!');
					}
				}

				$levelid = intval($member['level']);
				$groupid = intval($member['groupid']);

				if ($data['buylevels'] != '') {
					$buylevels = explode(',', $data['buylevels']);

					if (!(in_array($levelid, $buylevels))) {
						$this->result(0, '您的会员等级无法购买' . $data['title'] . '!');
					}
				}

				if ($data['buygroups'] != '') {
					$buygroups = explode(',', $data['buygroups']);

					if (!(in_array($groupid, $buygroups))) {
						$this->result(0, '您所在会员组无法购买' . $data['title'] . '!');
					}
				}
			}
			if ($data['type'] == 4) {
				if (!(empty($g['wholesaleprice']))) {
					$data['wholesaleprice'] = intval($g['wholesaleprice']);
				}

				if (!(empty($g['goodsalltotal']))) {
					$data['goodsalltotal'] = intval($g['goodsalltotal']);
				}

				$data['marketprice'] == 0;
				$intervalprice = iunserializer($data['intervalprice']);

				foreach ($intervalprice as $intervalprice ) {
					if ($intervalprice['intervalnum'] <= $data['goodsalltotal']) {
						$data['marketprice'] = $intervalprice['intervalprice'];
					}
				}

				if ($data['marketprice'] == 0) {
					$this->result(0, $data['title'] . '' . $data['minbuy'] . $unit . '起批!');
				}
			}

			if (!(empty($optionid))) {
				$option = Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goodsid)->field('id,title,marketprice,liveprice,presellprice,goodssn,productsn,stock,`virtual`,weight,exchange_stock')->find();

				if (!(empty($option))) {
					if ($data['seckillinfo'] && ($data['seckillinfo']['status'] == 0)) { 
					} else {
						if (empty($data['unite_total'])) {
							$stock_num = $option['stock'];
						}
						 else {
							$stock_num = $data['stock'];
						}

						if ($stock_num != -1) {
							if (empty($stock_num)) {
								$this->result(0, $data['title'] . '' . $option['title'] . ' 库存不足!stock=' . $stock_num);
							}

							if (!(empty($data['unite_total']))) {
								if (($stock_num - intval($total_array[$goodsid]['total'])) < 0) {
									$this->result(0, $data['title'] . '总库存不足!当前总库存为' . $stock_num);
								}
							}
						}
					}

					$data['optionid'] = $optionid;
					$data['optiontitle'] = $option['title'];

					if ($data['type'] != 4) {
						$data['marketprice'] = (((0 < intval($data['ispresell'])) && ((time() < $data['preselltimeend']) || ($data['preselltimeend'] == 0)) ? $option['presellprice'] : $option['marketprice']));
						$packageoption = array();
						if ($packageid) {
							$packageoption = Db::name('shop_package_goods_option')->where('goodsid',$goodsid)->where('optionid',$optionid)->where('pid',$packageid)->field('packageprice')->find();
							$data['marketprice'] = $packageoption['packageprice'];
							$packageprice += $packageoption['packageprice'];
						}
					}

					$virtualid = $option['virtual'];

					if (!(empty($option['goodssn']))) {
						$data['goodssn'] = $option['goodssn'];
					}

					if (!(empty($option['productsn']))) {
						$data['productsn'] = $option['productsn'];
					}

					if (!(empty($option['weight']))) {
						$data['weight'] = $option['weight'];
					}
				}
			} else {
				if ($packageid) {
					$pg = Db::name('shop_package_goods')->where('goodsid',$goodsid)->where('pid',$packageid)->field('packageprice')->find();
					$data['marketprice'] = $pg['packageprice'];
					$packageprice += $pg['packageprice'];
				}
				if ($data['stock'] != -1) {
				    if (empty($data['stock'])) {
						$this->result(0, $data['title'] . '库存不足!');
					}
				}
			}
			if ($data['seckillinfo'] && ($data['seckillinfo']['status'] == 0)) {
				$data['ggprice'] = $gprice = $data['seckillinfo']['price'] * $goodstotal;
				$seckill_payprice += $gprice;
				$seckill_price += ($data['marketprice'] * $goodstotal) - $gprice;
				$goodsprice += $data['marketprice'] * $goodstotal;
				$data['taskdiscountprice'] = 0;
				$data['lotterydiscountprice'] = 0;
				$data['discountprice'] = 0;
				$data['discountprice'] = 0;
				$data['discounttype'] = 0;
				$data['isdiscountunitprice'] = 0;
				$data['discountunitprice'] = 0;
				$data['price0'] = 0;
				$data['price1'] = 0;
				$data['price2'] = 0;
				$data['buyagainprice'] = 0;
			} else {
				$gprice = $data['marketprice'] * $goodstotal;
				$goodsprice += $gprice;
				$prices = model('order')->getGoodsDiscountPrice($data, $level);
				$data['ggprice'] = $prices['price'];
				$data['taskdiscountprice'] = $prices['taskdiscountprice'];
				$data['lotterydiscountprice'] = $prices['lotterydiscountprice'];
				$data['discountprice'] = $prices['discountprice'];
				$data['discountprice'] = $prices['discountprice'];
				$data['discounttype'] = $prices['discounttype'];
				$data['isdiscountunitprice'] = $prices['isdiscountunitprice'];
				$data['discountunitprice'] = $prices['discountunitprice'];
				$data['price0'] = $prices['price0'];
				$data['price1'] = $prices['price1'];
				$data['price2'] = $prices['price2'];
				$data['buyagainprice'] = $prices['buyagainprice'];
				$buyagainprice += $prices['buyagainprice'];
				$taskdiscountprice += $prices['taskdiscountprice'];
				$lotterydiscountprice += $prices['lotterydiscountprice'];

				if ($prices['discounttype'] == 1) {
					$isdiscountprice += $prices['isdiscountprice'];
					$discountprice += $prices['discountprice'];

					if (!(empty($data['merchsale']))) {
						$merchisdiscountprice += $prices['isdiscountprice'];
						$discountprice_array[$merchid]['merchisdiscountprice'] += $prices['isdiscountprice'];
					}

					$discountprice_array[$merchid]['isdiscountprice'] += $prices['isdiscountprice'];
				} else if ($prices['discounttype'] == 2) {
					$discountprice += $prices['discountprice'];
					$discountprice_array[$merchid]['discountprice'] += $prices['discountprice'];
				}

				$discountprice_array[$merchid]['ggprice'] += $prices['ggprice'];
			}

			$merch_array[$merchid]['ggprice'] += $data['ggprice'];
			$totalprice += $data['ggprice'];

			if ($data['isverify'] == 2) {
				$isverify = true;
				$verifytype = $data['verifytype'];
				$isendtime = $data['isendtime'];

				if ($isendtime == 0) {
					if (0 < $data['usetime']) {
						$endtime = time() + (3600 * 24 * intval($data['usetime']));
					} else {
						$endtime = 0;
					}
				} else {
					$endtime = $data['endtime'];
				}
			}
			if (!(empty($data['virtual'])) || ($data['type'] == 2) || ($data['type'] == 3) || ($data['type'] == 20)) {
				$isvirtual = true;

				if (($data['type'] == 20) && p('ccard')) {
					$ccard = 1;
				}

				if ($data['virtualsend']) {
					$isvirtualsend = true;
				}
			}
			if ((0 < floatval($data['buyagain'])) && empty($data['buyagain_sale'])) {
				if (model('goods')->canBuyAgain($data)) {
					$data['deduct'] = 0;
					$saleset = false;
				}
			}
			if ($open_redis) {
				if ($data['manydeduct']) {
					$deductprice += $data['deduct'] * $data['total'];
				}
				 else {
					$deductprice += $data['deduct'];
				}

				if ($data['deduct2'] == 0) {
					$deductprice2 += $data['ggprice'];
				} else if (0 < $data['deduct2']) {
					if ($data['ggprice'] < $data['deduct2']) {
						$deductprice2 += $data['ggprice'];
					}
					 else {
						$deductprice2 += $data['deduct2'];
					}
				}
			}

			$virtualsales += $data['sales'];
			$allgoods[] = $data;
		}
		$grprice = $totalprice;

		if ((1 < count($goods)) && !(empty($tgoods))) {
			$this->result(0, '任务活动优惠商品' . $tgoods['title'] . '不能放入购物车下单,请单独购买');
		}

		if (empty($allgoods)) {
			$this->result(0, '未找到任何商品');
		}
		$couponid = input('couponid/d');
		$contype = input('contype/d');

		if ($is_openmerch == 1) {
			foreach ($merch_array as $key => $value ) {
				if (0 < $key) {
					$merch_array[$key]['set'] = model('merch')->getSet('sale', $key);
					$merch_array[$key]['enoughs'] = model('merch')->getEnoughs($merch_array[$key]['set']);
				}
			}
			if ($allow_sale && empty($_SESSION['taskcut'])) {
				$merch_enough = model('order')->getMerchEnough($merch_array);
				$merch_array = $merch_enough['merch_array'];
				$merch_enough_total = $merch_enough['merch_enough_total'];
				$merch_saleset = $merch_enough['merch_saleset'];

				if (0 < $merch_enough_total) {
					$totalprice -= $merch_enough_total;
				}
			}
		}

		$deductenough = 0;
		if ($saleset) {
			foreach ($saleset['enoughs'] as $e ) {
				if ((floatval($e['enough']) <= $totalprice - $seckill_payprice) && (0 < floatval($e['money']))) {
					$deductenough = floatval($e['money']);

					if (($totalprice - $seckill_payprice) < $deductenough) {
						$deductenough = $totalprice - $seckill_payprice;
					}
					break;
				}
			}
		}

		$goodsdata_coupon = array();
		$goodsdata_coupon_temp = array();

		foreach ($allgoods as $g ) {
			if ($g['seckillinfo'] && ($g['seckillinfo']['status'] == 0)) {
				$goodsdata_coupon_temp[] = $g;
			} else if (0 < floatval($g['buyagain'])) {
				if (!(model('goods')->canBuyAgain($g)) || !(empty($g['buyagain_sale']))) {
					$goodsdata_coupon[] = $g;
				} else {
					$goodsdata_coupon_temp[] = $g;
				}
			}
			 else {
				$goodsdata_coupon[] = $g;
			}
		}

		$return_array = $this->caculatecoupon($contype, $couponid, $goodsdata_coupon, $totalprice, $discountprice, $isdiscountprice, 1, $discountprice_array, $merchisdiscountprice);
		$couponprice = 0;
		$coupongoodprice = 0;

		if (!(empty($return_array))) {
			$isdiscountprice = $return_array['isdiscountprice'];
			$discountprice = $return_array['discountprice'];
			$couponprice = $return_array['deductprice'];
			$totalprice = $return_array['totalprice'];
			$discountprice_array = $return_array['discountprice_array'];
			$merchisdiscountprice = $return_array['merchisdiscountprice'];
			$coupongoodprice = $return_array['coupongoodprice'];
			$couponmerchid = $return_array['couponmerchid'];
			$allgoods = $return_array['$goodsarr'];
			$allgoods = array_merge($allgoods, $goodsdata_coupon_temp);
		}
		$addressid = input('addressid/d',0);
		$address = false;
		if (!(empty($addressid)) && ($dispatchtype == 0) && !($isonlyverifygoods)) {
			$address = Db::name('shop_member_address')->where('id',$addressid)->where('mid',$mid)->find();

			if (empty($address)) {
				$this->result(0, '未找到地址');
			} else {
				if (empty($address['province']) || empty($address['city'])) {
					$this->result(0, '地址请选择省市信息');
				}
			}
		}

		if (!($isvirtual) && !($isverify) && !($isonlyverifygoods) && ($dispatchtype == 0) && !($isonlyverifygoods)) {
			if (empty($addressid)) {
				$this->result(0, '请选择地址');
			}

			$dispatch_array = model('order')->getOrderDispatchPrice($allgoods, $member, $address, $saleset, $merch_array, 2);
			$dispatch_price = $dispatch_array['dispatch_price'] - $dispatch_array['seckill_dispatch_price'];
			$seckill_dispatchprice = $dispatch_array['seckill_dispatch_price'];
			$nodispatch_array = $dispatch_array['nodispatch_array'];

			if (!(empty($nodispatch_array['isnodispatch']))) {
				$this->result(0, $nodispatch_array['nodispatch']);
			}
		}
		if ($isonlyverifygoods) {
			$addressid = 0;
		}

		$totalprice -= $deductenough;
		$totalprice += $dispatch_price + $seckill_dispatchprice;
		if ($saleset && empty($saleset['dispatchnodeduct'])) {
			$deductprice2 += $dispatch_price;
		}

		if (empty($goods[0]['bargain_id'])) {
			$deductcredit = 0;
			$deductmoney = 0;
			$deductcredit2 = 0;
			if ($sale_plugin) {
				if (!(empty($deduct))) {
					$credit = $member['credit1'];
					if (0 < $credit) {
						$credit = floor($credit);
					}

					if (!(empty($saleset['creditdeduct']))) {
						$pcredit = intval($saleset['credit']);
						$pmoney = round(floatval($saleset['money']), 2);

						if ((0 < $pcredit) && (0 < $pmoney)) {
							if (($credit % $pcredit) == 0) {
								$deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
							}
							else {
								$deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
							}
						}

						if ($deductprice < $deductmoney) {
							$deductmoney = $deductprice;
						}

						if (($totalprice - $seckill_payprice) < $deductmoney) {
							$deductmoney = $totalprice - $seckill_payprice;
						}
						$deductcredit = floor(($deductmoney / $pmoney) * $pcredit);
					}
				}
				$totalprice -= $deductmoney;
			}

			if (!(empty($saleset['moneydeduct']))) {
				if (!(empty($_GET['deduct2']))) {
					$deductcredit2 = $member['credit2'];

					if (($totalprice - $seckill_payprice) < $deductcredit2) {
						$deductcredit2 = $totalprice - $seckill_payprice;
					}

					if ($deductprice2 < $deductcredit2) {
						$deductcredit2 = $deductprice2;
					}
				}
				$totalprice -= $deductcredit2;
			}
		}

		$verifyinfo = array();
		$verifycode = '';
		$verifycodes = array();
		if ($isverify || $dispatchtype) {
			if ($isverify) {
				if (($verifytype == 0) || ($verifytype == 1)) {
					$verifycode = random(8, true);
					while (1) {
						$count = Db::name('shop_order')->where('verifycode',$verifycode)->count();

						if ($count <= 0) {
							break;
						}
						$verifycode = random(8, true);
					}
				} else if ($verifytype == 2) {
					$totaltimes = intval($allgoods[0]['total']);
					if ($totaltimes <= 0) {
						$totaltimes = 1;
					}
					$i = 1;
					while ($i <= $totaltimes) {
						$verifycode = random(8, true);
						while (1) {
							$count = Db::name('shop_order')->where('concat(verifycodes,\'|\' + verifycode +\'|\' ) like ' . '%' . $verifycode . '%')->count();
							if ($count <= 0) {
								break;
							}
							$verifycode = random(8, true);
						}
						$verifycodes[] = '|' . $verifycode . '|';
						$verifyinfo[] = array('verifycode' => $verifycode, 'verifymid' => '', 'verifytime' => 0, 'verifystoreid' => 0);
						++$i;
					}
				}
			} else if ($dispatchtype) {
				$verifycode = random(8, true);
				while (1) {
					$count = Db::name('shop_order')->where('verifycode',$verifycode)->count();

					if ($count <= 0) {
						break;
					}
					$verifycode = random(8, true);
				}
			}
		}

		$carrier_realname = trim(input('carrier_realname'));
		$carrier_mobile = trim(input('carrier_mobile'));
		$carrier = array('carrier_realname' => $carrier_realname, 'carrier_mobile' => $carrier_mobile);
		$carriers = ((is_array($carrier) ? iserializer($carrier) : iserializer(array())));

		if ($totalprice <= 0) {
			$totalprice = 0;
		}

		if (($ismerch == 0) || (($ismerch == 1) && (count($merch_array) == 1))) {
			$multiple_order = 0;
		} else {
			$multiple_order = 1;
		}

		if (0 < $ismerch) {
			$ordersn = model('common')->createNO('shop_order', 'ordersn', 'ME');
		} else {
			$ordersn = model('common')->createNO('shop_order', 'ordersn', 'SH');
		}

		if (!(empty($goods[0]['bargain_id']))) {
			$bargain_act = Db::name('shop_bargain_actor')->where('id',$goods[0]['bargain_id'])->where('mid',$mid)->find();

			if (empty($bargain_act)) {
				$this->result(0,'没有这个商品');
			}

			$totalprice = $bargain_act['now_price'] + $dispatch_price;
			$goodsprice = $bargain_act['now_price'];

			if (!(Db::name('shop_bargain_actor')->where('id',$goods[0]['bargain_id'])->where('mid',$mid)->update(array('status' => 1)))) {
				$this->result(0,'下单失败');
			}
			$ordersn = substr_replace($ordersn, 'KJ', 0, 2);
		}
		$is_package = 0;

		if (!(empty($packageid))) {
			$goodsprice = $packageprice;
			$dispatch_price = $package['freight'];
			$totalprice = $packageprice + $package['freight'];
			$is_package = 1;
		}
		if ($taskgoodsprice) {
			$totalprice = $taskgoodsprice;
			$goodsprice = $taskgoodsprice;

			if ($taskGoodsInfo0['goods'][$goodsid]['num'] <= 1) {
				unset($taskGoodsInfo0['goods'][$goodsid]);
			} else {
				--$taskGoodsInfo0['goods'][$goodsid]['num'];
			}
			Db::name('shop_task_extension_join')->where('id',$task_id)->where('mid',$mid)->update(array('rewarded' => serialize($taskGoodsInfo0)));
		}

		$order = array();
		$order['ismerch'] = $ismerch;
		$order['parentid'] = 0;
		$order['mid'] = $mid;
		$order['ordersn'] = $ordersn;
		$order['price'] = $totalprice;
		$order['oldprice'] = $totalprice;
		$order['grprice'] = $grprice;
		$order['taskdiscountprice'] = $taskdiscountprice;
		$order['lotterydiscountprice'] = $lotterydiscountprice;
		$order['discountprice'] = $discountprice ? $discountprice : 0;
		if (!(empty($goods[0]['bargain_id']))) {
			$order['discountprice'] = 0;
		}
		$order['isdiscountprice'] = $isdiscountprice;
		$order['merchisdiscountprice'] = $merchisdiscountprice;
		$order['cash'] = $cash;
		$order['status'] = 0;
		$order['remark'] = $remark[$merchid]['remark'];
		$order['addressid'] = ((empty($dispatchtype) ? $addressid : 0));
		$order['goodsprice'] = $goodsprice;
		$order['dispatchprice'] = $dispatch_price + $seckill_dispatchprice;
		$order['dispatchtype'] = $dispatchtype;
		$order['dispatchid'] = $dispatchid;
		$order['storeid'] = $carrierid;
		$order['carrier'] = $carriers;
		$order['createtime'] = time();
		$order['olddispatchprice'] = $dispatch_price + $seckill_dispatchprice;
		$order['contype'] = $contype;
		$order['couponid'] = $couponid;
		$order['couponmerchid'] = $couponmerchid;
		$order['paytype'] = $paytype;
		$order['deductprice'] = $deductmoney;
		$order['deductcredit'] = $deductcredit;
		$order['deductcredit2'] = $deductcredit2;
		$order['deductenough'] = $deductenough;
		$order['merchdeductenough'] = $merch_enough_total;
		$order['couponprice'] = $couponprice;
		if ($multiple_order == 0) {
			$order['merchshow'] = 1;
		} else {
			$order['merchshow'] = 0;
		}
		$order['buyagainprice'] = $buyagainprice;
		$order['ispackage'] = $is_package;
		$order['packageid'] = $packageid;
		$order['seckilldiscountprice'] = $seckill_price;
		$order['quickid'] = input('fromquick/d',0);
		$order['officcode'] = input('officcode/d',0);
		if (!(empty($ccard))) {
			$order['ccard'] = 1;
		}
		if ($multiple_order == 0) {
			$order_merchid = current(array_keys($merch_array));
			$order['merchid'] = intval($order_merchid);
			$order['isparent'] = 0;
			$order['transid'] = '';
			$order['isverify'] = (($isverify ? 1 : 0));
			$order['verifytype'] = $verifytype;
			$order['verifyendtime'] = $endtime;
			$order['verifycode'] = $verifycode;
			$order['verifycodes'] = implode('', $verifycodes);
			$order['verifyinfo'] = iserializer($verifyinfo);
			$order['virtual'] = $virtualid;
			$order['isvirtual'] = (($isvirtual ? 1 : 0));
			$order['isvirtualsend'] = (($isvirtualsend ? 1 : 0));
			$order['invoicename'] = trim(input('invoicename/s',''));
			$order['coupongoodprice'] = $coupongoodprice;
		} else {
			$order['isparent'] = 1;
			$order['merchid'] = 0;
		}
		if (!(empty($address))) {
			$order['address'] = iserializer($address);
		}

		Db::startTrans();
		try{
		    $orderid = Db::name('shop_order')->insertGetId($order);
		    if (!$orderid) {
				$this->result(0,'提交订单失败');
			}
			if (!(empty($goods[0]['bargain_id']))) {
				Db::name('shop_bargain_actor')->where('id',$goods[0]['bargain_id'])->where('mid',$mid)->setField('order',$orderid);
			}
			if ($multiple_order == 0) {
				$exchangetitle = '';

				foreach ($allgoods as $goods ) {
					$order_goods = array();
					if (!(empty($bargain_act))) {
						$goods['total'] = 1;
						$goods['ggprice'] = $bargain_act['now_price'];
						Db::name('shop_goods')->where('id',$goods['goodsid'])->setInc('sales',1);
					}

					$order_goods['merchid'] = $goods['merchid'];
					$order_goods['merchsale'] = $goods['merchsale'] ? $goods['merchsale'] : 0;
					$order_goods['orderid'] = $orderid;
					$order_goods['goodsid'] = $goods['goodsid'];
					$order_goods['price'] = $goods['marketprice'] * $goods['total'];
					$order_goods['total'] = $goods['total'];
					$order_goods['optionid'] = $goods['optionid'] ? $goods['optionid'] : 0;
					$order_goods['createtime'] = time();
					$order_goods['optionname'] = $goods['optiontitle'] ? $goods['optiontitle'] : '';
					$order_goods['goodssn'] = $goods['goodssn'];
					$order_goods['productsn'] = $goods['productsn'];
					$order_goods['realprice'] = $goods['ggprice'];
					$order_goods['prohibitrefund'] = $goods['cannotrefund'];
					$exchangetitle .= $goods['title'];
					$order_goods['oldprice'] = $goods['ggprice'];

					if ($goods['discounttype'] == 1) {
						$order_goods['isdiscountprice'] = $goods['isdiscountprice'];
					}
					 else {
						$order_goods['isdiscountprice'] = 0;
					}
					$order_goods['mid'] = $mid;

					if (0 < floatval($goods['buyagain'])) {
						if (!(model('goods')->canBuyAgain($goods))) {
							$order_goods['canbuyagain'] = 1;
						}
					}

					if ($goods['seckillinfo'] && ($goods['seckillinfo']['status'] == 0)) {
						$order_goods['seckill'] = 1;
						$order_goods['seckill_taskid'] = $goods['seckillinfo']['taskid'];
						$order_goods['seckill_roomid'] = $goods['seckillinfo']['roomid'];
						$order_goods['seckill_timeid'] = $goods['seckillinfo']['timeid'];
					}
					Db::name('shop_order_goods')->insertGetId($order_goods);
					if ($goods['seckillinfo'] && ($goods['seckillinfo']['status'] == 0)) {
						
					}
				}
			} else {
				$og_array = array();
				$ch_order_data = model('order')->getChildOrderPrice($order, $allgoods, $dispatch_array, $merch_array, $sale_plugin, $discountprice_array);

				foreach ($merch_array as $key => $value ) {
					$merchid = $key;

					if (!(empty($merchid))) {
						$order_head = 'ME';
					}
					 else {
						$order_head = 'SH';
					}

					$order['ordersn'] = model('common')->createNO('shop_order', 'ordersn', $order_head);
					$order['merchid'] = $merchid;
					$order['parentid'] = $orderid;
					$order['isparent'] = 0;
					$order['remark'] = $remark[$merchid]['remark'];
					$order['merchshow'] = 1;
					$order['dispatchprice'] = $dispatch_array['dispatch_merch'][$merchid];
					$order['olddispatchprice'] = $dispatch_array['dispatch_merch'][$merchid];
					$order['merchisdiscountprice'] = $discountprice_array[$merchid]['merchisdiscountprice'] ? $discountprice_array[$merchid]['merchisdiscountprice'] : 0;
					$order['isdiscountprice'] = $discountprice_array[$merchid]['isdiscountprice'] ? $discountprice_array[$merchid]['isdiscountprice'] : 0;
					$order['discountprice'] = $discountprice_array[$merchid]['discountprice'] ? $discountprice_array[$merchid]['discountprice'] : 0;
					$order['price'] = $ch_order_data[$merchid]['price'];
					$order['grprice'] = $ch_order_data[$merchid]['grprice'];
					$order['goodsprice'] = $ch_order_data[$merchid]['goodsprice'];
					$order['deductprice'] = $ch_order_data[$merchid]['deductprice'] ? $ch_order_data[$merchid]['deductprice'] : 0;
					$order['deductcredit'] = $ch_order_data[$merchid]['deductcredit'] ? $ch_order_data[$merchid]['deductcredit'] : 0;
					$order['deductcredit2'] = $ch_order_data[$merchid]['deductcredit2'] ? $ch_order_data[$merchid]['deductcredit2'] : 0;
					$order['merchdeductenough'] = $ch_order_data[$merchid]['merchdeductenough'] ? $ch_order_data[$merchid]['merchdeductenough'] : 0;
					$order['deductenough'] = $ch_order_data[$merchid]['deductenough'] ? $ch_order_data[$merchid]['deductenough'] : 0;
					$order['coupongoodprice'] = $discountprice_array[$merchid]['coupongoodprice'] ? $discountprice_array[$merchid]['coupongoodprice'] : 0;
					$order['couponprice'] = $discountprice_array[$merchid]['deduct'] ? $discountprice_array[$merchid]['deduct'] : 0;

					if (empty($order['couponprice'])) {
						$order['couponid'] = 0;
						$order['couponmerchid'] = 0;
					} else if (0 < $couponmerchid) {
						if ($merchid == $couponmerchid) {
							$order['couponid'] = $couponid;
							$order['couponmerchid'] = $couponmerchid;
						} else {
							$order['couponid'] = 0;
							$order['couponmerchid'] = 0;
						}
					}

					$ch_orderid = Db::name('shop_order')->insertGetId($order);
					$merch_array[$merchid]['orderid'] = $ch_orderid;

					if (0 < $couponmerchid) {
						if ($merchid == $couponmerchid) {
							$couponorderid = $ch_orderid;
						}
					}

					foreach ($value['goods'] as $k => $v ) {
						$og_array[$v] = $ch_orderid;
					}
				}

				foreach ($allgoods as $goods ) {
					$goodsid = $goods['goodsid'];
					$order_goods = array();
					$order_goods['parentorderid'] = $orderid;
					$order_goods['merchid'] = $goods['merchid'];
					$order_goods['merchsale'] = $goods['merchsale'] ? $goods['merchsale'] : 0;
					$order_goods['orderid'] = $og_array[$goodsid];
					$order_goods['goodsid'] = $goodsid;
					$order_goods['price'] = $goods['marketprice'] * $goods['total'];
					$order_goods['total'] = $goods['total'];
					$order_goods['optionid'] = $goods['optionid'] ? $goods['optionid'] : 0;
					$order_goods['createtime'] = time();
					$order_goods['optionname'] = $goods['optiontitle'] ? $goods['optiontitle'] : '';
					$order_goods['goodssn'] = $goods['goodssn'];
					$order_goods['productsn'] = $goods['productsn'];
					$order_goods['realprice'] = $goods['ggprice'];
					$order_goods['oldprice'] = $goods['ggprice'];
					$order_goods['prohibitrefund'] = $goods['cannotrefund'];
					$order_goods['isdiscountprice'] = $goods['isdiscountprice'] ? $goods['isdiscountprice'] : 0;
					$order_goods['mid'] = $mid;

					if (0 < floatval($goods['buyagain'])) {
						if (!(model('goods')->canBuyAgain($goods))) {
							$order_goods['canbuyagain'] = 1;
						}
					}
					Db::name('shop_order_goods')->insert($order_goods);
				}
			}
			if ($data['type'] == 3) {
				$order_v = Db::name('shop_order')->where('id',$orderid)->field('id,ordersn, price,mid,dispatchtype,addressid,carrier,status,isverify,deductcredit2,`virtual`,isvirtual,couponid,isvirtualsend,isparent,paytype,merchid,agentid,createtime,buyagainprice,istrade,tradestatus')->find();
				model('virtual')->pay_befo($order_v);
			}
			if (!(empty($orderid))) {
				model('coupon')->addtaskdata($orderid);
			}
			if (is_array($carrier)) {
				$up = array('realname' => $carrier['carrier_realname'], 'carrier_realname' => $carrier['carrier_realname'], 'carrier_mobile' => $carrier['carrier_mobile']);
				Db::name('member')->where('id',$member['id'])->update($up);
			}
			if ($fromcart == 1) {
				Db::name('shop_member_cart')->where('mid',$mid)->where('selected',1)->setField('deleted',1);
			}
			if (0 < $deductcredit) {
				model('member')->setCredit($mid, 'credit1', -$deductcredit, array('0', $shopset['shop']['name'] . '购物积分抵扣 消费积分: ' . $deductcredit . ' 抵扣金额: ' . $deductmoney . ' 订单号: ' . $ordersn));
			}

			if (0 < $buyagainprice) {
				model('goods')->useBuyAgain($mid,$orderid);
			}
			if (0 < $deductcredit2) {
				model('member')->setCredit($mid, 'credit2', -$deductcredit2, array('0', $shopset['shop']['name'] . '购物余额抵扣: ' . $deductcredit2 . ' 订单号: ' . $ordersn));
			}
			if (empty($virtualid)) {
				model('order')->setStocksAndCredits($orderid, 0);
			}
			else if (isset($allgoods[0])) {
				$vgoods = $allgoods[0];
				Db::name('shop_goods')->where('id',$vgoods['goodsid'])->update(array('sales' => $vgoods['sales'] + $vgoods['total']));
			}
			if ((0 < $couponmerchid) && ($multiple_order == 1)) {
				$oid = $couponorderid;
			}
			 else {
				$oid = $orderid;
			}

			model('coupon')->useConsumeCoupon($oid);
			if (!(empty($tgoods))) {
				model('goods')->getTaskGoods($tgoods['mid'], $tgoods['goodsid'], $rank, $log_id, $join_id, $tgoods['optionid'], $tgoods['total']);
			}

			// model('notice')->sendOrderMessage($orderid);
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    $this->result(0,'提交订单失败');
		    Db::rollback();
		}
		return $this->redirect(url('apiv1/order/pay',['orderid' => $orderid]));
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

		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->find();
		if(empty($order))
		{
			$this->result(0,'订单不存在');
		}
		$og_array = model('order')->checkOrderGoods($orderid);

		if (!empty($og_array['flag'])) {
			$this->result(0,$og_array['msg']);
		}
		$tradestatus = $order['tradestatus'];
		if (empty($order['istrade'])) {
			if ($order['status'] == -1) {
				$this->result(0,'订单已付款');
			}
			else {
				if (1 <= $order['status']) {
					$this->result(0,'订单已付款');
				}
			}
		} else {
			if (($order['status'] == 1) && ($order['tradestatus'] == 1)) {
				$order['ordersn'] = $order['ordersn_trade'];
				$order['price'] = $order['betweenprice'];
			} else {
				if (($order['status'] == 1) && ($order['tradestatus'] == 2)) {
					$this->result(0,'您访问的信息不存在');
				}
				else {
					if ($order['status'] == 0) {
						$order['price'] = $order['dowpayment'];
					}
				}
			}
		}

		$log = Db::name('shop_core_paylog')->where('module','shop')->where('tid',$order['ordersn'])->find();
		if (!empty($log) && ($log['status'] != '0')) {
			if (empty($order['istrade'])) {
				$this->result(0,'订单已付款');
			} else {
				$this->result(0,'订单已付款');
			}
			exit();
		}
		$seckill_goods = Db::name('shop_order_goods')->where('orderid',$orderid)->where('seckill',1)->select();
		if (!empty($log) && ($log['status'] == '0')) {
			Db::name('shop_core_paylog')->where('plid',$log['plid'])->delete();
			$log = NULL;
		}
		if (empty($log)) {
			$log = array('mid' => $member['id'], 'module' => 'shop', 'tid' => $order['ordersn'], 'fee' => $order['price'], 'status' => 0);
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
		$wechat = array('success' => false);
		$params = array();
		$params['tid'] = $log['tid'];
		$params['product_id'] = $orderid;

		if (!empty($order['ordersn2'])) {
			$var = sprintf('%02d', $order['ordersn2']);
			$params['tid'] .= 'GJ' . $var;
		}
		$paytype = input('paytype/d') ? input('paytype/d') : $order['paytype'];
		$headerinfo = $this->headerinfo;
		if(!in_array($headerinfo['device-type'], array('iOS','android','wechat','web'))) {
			$this->result(0,'支付出错!');
		}
		if ($paytype == 1) {
			$params['user'] = $mid;
			$params['fee'] = $order['price'];
			$params['title'] = $param_title;
			if (isset($set['pay']) && ($set['pay']['app_wechat'] == 1)) {
				$wechat = model('payment')->wechat_build($params, $headerinfo['device-type'], 0, $member['wechat_openid']);
				if (!is_array($wechat)) {
					$this->result(0,$wechat);
				}
			}
			if(empty($order['isparent'])) {
				$wechat['product_id'] = $orderid;
			} else {
				$wechat['product_id'] = 0;
			}
			
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

				$alipay = model('payment')->alipay_build($params, $headerinfo['device-type'], 0, getHttpHost() . '/public/dist/order');
				if (empty($alipay)) {
					$this->result(0,'参数错误');
				}
			}
			if(empty($order['isparent'])) {
				$product_id = $orderid;
			} else {
				$product_id = 0;
			}
			$this->result(1,'success',array('sign'=>$alipay,'product_id'=>$product_id));
		}

		if (empty($seckill_goods)) {
			if (!empty($order['addressid'])) {
				$cash = array('success' => ($order['cash'] == 1) && isset($set['pay']) && ($set['pay']['cash'] == 1) && ($order['isverify'] == 0) && ($order['isvirtual'] == 0));
			}
			$haveverifygood = model('order')->checkhaveverifygoods($orderid);
		}
		else {
			$cash = array('success' => false);
		}
		$payinfo = array('orderid' => $orderid, 'ordersn' => $log['tid'], 'credit' => $credit, 'alipay' => $alipay, 'wechat' => $wechat, 'cash' => $cash, 'money' => $order['price']);
		$this->result(1,'success',$payinfo);
	}

	/**
     * 取消订单
     * @global type $MID
     * @global type $_GET
     */
	public function cancel()
	{
		$mid = $this->getMemberId();
		$shopset = $this->shopset;
		$orderid = input('orderid/d');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,ordersn,mid,status,deductcredit,deductcredit2,deductprice,couponid,isparent,`virtual`,`virtual_info`')->find();

		if (empty($order)) {
			$this->result(0, '订单未找到');
		}

		if (0 < $order['status']) {
			$this->result(0, '订单已支付，不能取消!');
		}

		if ($order['status'] < 0) {
			$this->result(0, '订单已经取消!');
		}

		if (!empty($order['virtual']) && ($order['virtual'] != 0)) {
			$goodsid = Db::name('shop_order_goods')->where('orderid',$order['id'])->field('goodsid')->find();
			$typeid = $order['virtual'];
			$vkdata = ltrim($order['virtual_info'], '[');
			$vkdata = rtrim($vkdata, ']');
			$arr = explode('}', $vkdata);

			foreach ($arr as $k => $v) {
				if (!$v) {
					unset($arr[$k]);
				}
			}

			$vkeynum = count($arr);
			Db::name('shop_virtual_data')->where('typeid',$typeid)->where('orderid',$order['id'])->update(array('mid'=>0,'usetime'=>0,'orderid'=>0,'ordersn'=>'','price'=>0,'merchid'=>0));
			Db::name('shop_virtual_type')->where('id',$typeid)->update(['usedata'=>'usedata-' . $vkeynum]);
		}

		model('order')->setStocksAndCredits($orderid, 2);

		if (0 < $order['deductprice']) {
			model('member')->setCredit($order['mid'], 'credit1', $order['deductcredit'], array('0', $shopset['shop']['name'] . '购物返还抵扣积分 积分: ' . $order['deductcredit'] . ' 抵扣金额: ' . $order['deductprice'] . ' 订单号: ' . $order['ordersn']));
		}

		model('order')->setDeductCredit2($order);
		if (!empty($order['couponid'])) {
			model('coupon')->returnConsumeCoupon($orderid);
		}
		Db::name('shop_order')->where('id',$order['id'])->update(array('status' => -1, 'canceltime' => time(), 'closereason' => trim(input('remark'))));

		model('notice')->sendOrderMessage($orderid);
		$this->result(1,'success');
	}

	/**
     * 确认收货
     * @param type $mid
     * @param type $orderid
     */
	public function finish()
	{
		$mid = $this->getMemberId();
		$shopset = $this->shopset;
		$orderid = input('orderid/d');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,mid,couponid,refundstate,refundid,ordersn,price')->find();

		if (empty($order)) {
			$this->result(0, '订单未找到');
		}

		if ($order['status'] != 2) {
			$this->result(0, '订单不能确认收货');
		}
		$refundcount = Db::name('shop_order_refund')->where('orderid',$orderid)->where('status','in',[0,3,4,5])->count();
		if ((0 < $order['refundstate']) && $refundcount <= 0) {
			$change_refund = array();
			$change_refund['status'] = -2;
			$change_refund['refundtime'] = time();
			Db::name('shop_order_refund')->where('orderid',$order['id'])->update($change_refund);
		}

		Db::name('shop_order')->where('id',$order['id'])->update(array('status' => 3, 'finishtime' => time(), 'refundstate' => 0));
		model('order')->setStocksAndCredits($orderid, 3);
		model('order')->fullback($orderid);
		model('member')->upgradeLevel($order['mid'], $orderid);
		model('order')->setGiveBalance($orderid, 1);

		// $refurnid = model('coupon')->sendcouponsbytask($orderid);

		// if (!empty($order['couponid'])) {
		// 	model('coupon')->backConsumeCoupon($orderid);
		// }
		$order_goods = Db::name('shop_order_goods')->alias('og')
			->join('shop_goods g','g.id=og.goodsid','left')
			->where('og.orderid',$order['id'])
			->field('og.id,og.goodsid,og.total,g.totalcnf,og.refundid,og.rstate,og.refundtime,og.prohibitrefund,g.cannotrefund,g.total as goodstotal,g.sales,g.salesreal,g.type')
			->select();
		foreach ($order_goods as $key => $value) {
			if(!empty($value['cannotrefund'])) {
				$rstate = 11;
			} else {
				$rstate = 12;
			}
			Db::name('shop_order_goods')->where('id',$value['id'])->update(array('rstate' => $rstate, 'prohibitrefund' => $value['cannotrefund']));
		}
		model('notice')->sendOrderMessage($orderid);

		$this->result(1,'success');
	}

	/**
     * 删除或恢复订单
     * @global type $_W
     * @global type $_GET
     */
	public function delete()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$userdeleted = input('userdeleted');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,refundstate,refundid')->find();

		if (empty($order)) {
			$this->result(0, '订单未找到!');
		}

		if ($userdeleted == 0) {
			if ($order['status'] != 3) {
				$this->result(0, '无法恢复');
			}
		} else {
			if (($order['status'] != 3) && ($order['status'] != -1)) {
				$this->result(0, '无法删除');
			}

			if ((0 < $order['refundstate'])) {
				$change_refund = array();
				$change_refund['status'] = -2;
				$change_refund['refundtime'] = time();
				Db::name('shop_order_refund')->where('id',$order['refundid'])->update($change_refund);
			}
		}
		Db::name('shop_order')->where('id',$order['id'])->update(array('userdeleted' => $userdeleted, 'refundstate' => 0));
		$this->result(1,'success');
	}

	/**
     * 订单评价
     * @global type $_W
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
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,iscomment')->find();

		if (empty($order)) {
			$this->result(0, '订单未找到!');
		}

		if (($order['status'] != 3) && ($order['status'] != 4)) {
			$this->result(0,'订单未收货，不能评价!');
		}

		// if (2 <= $order['iscomment']) {
		// 	$this->result(0,'您已经评价过了!');
		// }

		$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option o','o.id=og.optionid','left')->where('og.orderid',$orderid)->field('og.id,og.goodsid,og.price,og.iscomment,g.title,g.thumb,og.total,g.credit,og.optionid,ifnull(o.title,"") as optiontitle')->select();
		$goods = set_medias($goods, 'thumb');
		foreach ($goods as &$row) {
			$goodscomment = Db::name('shop_order_comment')->where('orderid', $orderid)->where('goodsid', $row['goodsid'])->where('optionid', $row['optionid'])->find();
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
     * @global type $_W
     * @global type $_GET
     */
	public function commentsubmit()
	{
		$mid = $this->getMemberId();
		$orderid = input('orderid/d');
		$order = Db::name('shop_order')->where('id',$orderid)->where('mid',$mid)->field('id,status,iscomment')->find();
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

			$id = Db::name('shop_order_comment')->insertGetId($comment);
			Db::name('shop_order_goods')->where('orderid', $orderid)->where('goodsid', intval($comments['goodsid']))->where('optionid', intval($comments['optionid']))->update(array('iscomment'=>1,'prohibitrefund'=>1));
		    $order_nocomment = Db::name('shop_order_goods')->where('orderid',$orderid)->where('iscomment',0)->count();
	        if($order_nocomment <= 0) {
	            $d['iscomment'] = 2;
	        } else {
	        	$d['iscomment'] = 1;
	        }   

			Db::name('shop_order')->where('id',$orderid)->update($d);
			$goods = Db::name('shop_order_goods')->alias('og')->join('shop_goods g','g.id=og.goodsid','left')->join('shop_goods_option o','o.id=og.optionid','left')->where('og.orderid',$orderid)->where('og.goodsid', intval($comments['goodsid']))->where('og.optionid', intval($comments['optionid']))->field('og.id,og.goodsid,og.price,og.iscomment,g.title,g.thumb,og.total,g.credit,og.optionid,ifnull(o.title,"") as optiontitle')->find();
			$goods['thumb'] = tomedia($goods['thumb']);
	        $goodscomment = Db::name('shop_order_comment')->where('id', $id)->find();
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

	public function caculatecoupon($contype, $couponid, $wxid, $wxcardid, $wxcode, $goodsarr, $totalprice, $discountprice, $isdiscountprice, $isSubmit = 0, $discountprice_array = array(), $merchisdiscountprice = 0)
	{
		$mid = $this->getMemberId();
		if (empty($goodsarr)) {
			return false;
		}

		if ($contype == 0) {
			return;
		}

		if ($contype == 1) {
			$data = Db::name('shop_wxcard')->where('id',$wxid)->where('card_id',$wxcardid)->field('id,card_type,logo_url,title, card_id,least_cost,reduce_cost,discount,merchid,limitgoodtype,limitgoodcatetype,limitgoodcateids,limitgoodids,merchid,limitdiscounttype')->find();
			$merchid = intval($data['merchid']);
		} else if ($contype == 2) {
			$data = Db::name('shop_coupon_data')->alias('d')->join('shop_coupon c','d.couponid = c.id','left')->where('d.id = ' . $couponid . ' and d.mid = ' . $mid . 'and d.used = 0')->field('d.id,d.couponid,c.enough,c.backtype,c.deduct,c.discount,c.backmoney,c.backcredit,c.backredpack,c.merchid,c.limitgoodtype,c.limitgoodcatetype,c.limitgoodids,c.limitgoodcateids,c.limitdiscounttype')->find();
			$merchid = intval($data['merchid']);
		}

		if (empty($data)) {
			return;
		}

		if (is_array($goodsarr)) {
			$goods = array();

			foreach ($goodsarr as $g ) {
				if (empty($g)) {
					continue;
				}

				if ((0 < $merchid) && ($g['merchid'] != $merchid)) {
					continue;
				}

				$cates = explode(',', $g['cates']);
				$limitcateids = explode(',', $data['limitgoodcateids']);
				$limitgoodids = explode(',', $data['limitgoodids']);
				$pass = 0;

				if (($data['limitgoodcatetype'] == 0) && ($data['limitgoodtype'] == 0)) {
					$pass = 1;
				}


				if ($data['limitgoodcatetype'] == 1) {
					$result = array_intersect($cates, $limitcateids);
					if (0 < count($result)) {
						$pass = 1;
					}
				}


				if ($data['limitgoodtype'] == 1) {
					$isin = in_array($g['goodsid'], $limitgoodids);

					if ($isin) {
						$pass = 1;
					}
				}


				if ($pass == 1) {
					$goods[] = $g;
				}

			}

			$limitdiscounttype = intval($data['limitdiscounttype']);
			$coupongoodprice = 0;
			$gprice = 0;

			foreach ($goods as $k => $g ) {
				$gprice = (double) $g['marketprice'] * (double) $g['total'];

				switch ($limitdiscounttype) {
				case 1:
					$coupongoodprice += $gprice - ((double) $g['discountunitprice'] * (double) $g['total']);
					$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice - ((double) $g['discountunitprice'] * (double) $g['total']);

					if ($g['discounttype'] == 1) {
						$isdiscountprice -= (double) $g['isdiscountunitprice'] * (double) $g['total'];
						$discountprice += (double) $g['discountunitprice'] * (double) $g['total'];

						if ($isSubmit == 1) {
							$totalprice = ($totalprice - $g['ggprice']) + $g['price2'];
							$discountprice_array[$g['merchid']]['ggprice'] = ($discountprice_array[$g['merchid']]['ggprice'] - $g['ggprice']) + $g['price2'];
							$goodsarr[$k]['ggprice'] = $g['price2'];
							$discountprice_array[$g['merchid']]['isdiscountprice'] -= (double) $g['isdiscountunitprice'] * (double) $g['total'];
							$discountprice_array[$g['merchid']]['discountprice'] += (double) $g['discountunitprice'] * (double) $g['total'];

							if (!(empty($data['merchsale']))) {
								$merchisdiscountprice -= (double) $g['isdiscountunitprice'] * (double) $g['total'];
								$discountprice_array[$g['merchid']]['merchisdiscountprice'] -= (double) $g['isdiscountunitprice'] * (double) $g['total'];
							}

						}

					}


					break;

				case 2:
					$coupongoodprice += $gprice - ((double) $g['isdiscountunitprice'] * (double) $g['total']);
					$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice - ((double) $g['isdiscountunitprice'] * (double) $g['total']);

					if ($g['discounttype'] == 2) {
						$discountprice -= (double) $g['discountunitprice'] * (double) $g['total'];

						if ($isSubmit == 1) {
							$totalprice = ($totalprice - $g['ggprice']) + $g['price1'];
							$discountprice_array[$g['merchid']]['ggprice'] = ($discountprice_array[$g['merchid']]['ggprice'] - $g['ggprice']) + $g['price1'];
							$goodsarr[$k]['ggprice'] = $g['price1'];
							$discountprice_array[$g['merchid']]['discountprice'] -= (double) $g['discountunitprice'] * (double) $g['total'];
						}

					}


					break;

				case 3:
					$coupongoodprice += $gprice;
					$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice;

					if ($g['discounttype'] == 1) {
						$isdiscountprice -= (double) $g['isdiscountunitprice'] * (double) $g['total'];

						if ($isSubmit == 1) {
							$totalprice = ($totalprice - $g['ggprice']) + $g['price0'];
							$discountprice_array[$g['merchid']]['ggprice'] = ($discountprice_array[$g['merchid']]['ggprice'] - $g['ggprice']) + $g['price0'];
							$goodsarr[$k]['ggprice'] = $g['price0'];

							if (!(empty($data['merchsale']))) {
								$merchisdiscountprice -= $g['isdiscountunitprice'] * (double) $g['total'];
								$discountprice_array[$g['merchid']]['merchisdiscountprice'] -= $g['isdiscountunitprice'] * (double) $g['total'];
							}


							$discountprice_array[$g['merchid']]['isdiscountprice'] -= $g['isdiscountunitprice'] * (double) $g['total'];
						}

					} else if ($g['discounttype'] == 2) {
						$discountprice -= (double) $g['discountunitprice'] * (double) $g['total'];
						if ($isSubmit == 1) {
							$totalprice = ($totalprice - $g['ggprice']) + $g['price0'];
							$goodsarr[$k]['ggprice'] = $g['price0'];
							$discountprice_array[$g['merchid']]['ggprice'] = ($discountprice_array[$g['merchid']]['ggprice'] - $g['ggprice']) + $g['price0'];
							$discountprice_array[$g['merchid']]['discountprice'] -= (double) $g['discountunitprice'] * (double) $g['total'];
						}
					}

					break;

				default:
					if ($g['discounttype'] == 1) {
						$coupongoodprice += $gprice - ((double) $g['isdiscountunitprice'] * (double) $g['total']);
						$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice - ((double) $g['isdiscountunitprice'] * (double) $g['total']);
					}
					 else if ($g['discounttype'] == 2) {
						$coupongoodprice += $gprice - ((double) $g['discountunitprice'] * (double) $g['total']);
						$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice - ((double) $g['discountunitprice'] * (double) $g['total']);
					}
					 else if ($g['discounttype'] == 0) {
						$coupongoodprice += $gprice;
						$discountprice_array[$g['merchid']]['coupongoodprice'] += $gprice;
					}
					break;
				}
			}

			if ($contype == 1) {
				$deduct = (double) $data['reduce_cost'] / 100;
				$discount = (double) 100 - intval($data['discount']) / 10;

				if ($data['card_type'] == 'CASH') {
					$backtype = 0;
				}
				 else if ($data['card_type'] == 'DISCOUNT') {
					$backtype = 1;
				}

			}
			 else if ($contype == 2) {
				$deduct = (double) $data['deduct'];
				$discount = (double) $data['discount'];
				$backtype = (double) $data['backtype'];
			}


			$deductprice = 0;
			$coupondeduct_text = '';

			if ((0 < $deduct) && ($backtype == 0) && (0 < $coupongoodprice)) {
				if ($coupongoodprice < $deduct) {
					$deduct = $coupongoodprice;
				}


				if ($deduct <= 0) {
					$deduct = 0;
				}


				$deductprice = $deduct;
				$coupondeduct_text = '优惠券优惠';

				foreach ($discountprice_array as $key => $value ) {
					$discountprice_array[$key]['deduct'] = ((double) $value['coupongoodprice'] / (double) $coupongoodprice) * $deduct;
				}
			} else if ((0 < $discount) && ($backtype == 1)) {
				$deductprice = $coupongoodprice * (1 - ($discount / 10));

				if ($coupongoodprice < $deductprice) {
					$deductprice = $coupongoodprice;
				}


				if ($deductprice <= 0) {
					$deductprice = 0;
				}


				foreach ($discountprice_array as $key => $value ) {
					$discountprice_array[$key]['deduct'] = (double) $value['coupongoodprice'] * (1 - ($discount / 10));
				}

				if (0 < $merchid) {
					$coupondeduct_text = '店铺优惠券折扣(' . $discount . '折)';
				} else {
					$coupondeduct_text = '优惠券折扣(' . $discount . '折)';
				}
			}

		}


		$totalprice -= $deductprice;
		$return_array = array();
		$return_array['isdiscountprice'] = $isdiscountprice;
		$return_array['discountprice'] = $discountprice;
		$return_array['deductprice'] = $deductprice;
		$return_array['coupongoodprice'] = $coupongoodprice;
		$return_array['coupondeduct_text'] = $coupondeduct_text;
		$return_array['totalprice'] = $totalprice;
		$return_array['discountprice_array'] = $discountprice_array;
		$return_array['merchisdiscountprice'] = $merchisdiscountprice;
		$return_array['couponmerchid'] = $merchid;
		$return_array['$goodsarr'] = $goodsarr;
		return $return_array;
	}

}