<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Creditshop extends \think\Model
{
	public static function getGoods($id, $member, $optionid = 0, $merchid = 0)
	{
		global $_W;
		$credit = $member['credit1'];
		$money = $member['credit2'];
		$optionid = intval($optionid);
		$condition = ' 1 ';

		if (0 < $merchid) {
			$condition .= ' and merchid = ' . $merchid . ' ';
		}

		if (empty($id)) {
			return NULL;
		}

		$goods = Db::name('shop_creditshop_goods')->where($condition . ' and id = ' . $id)->find();

		if (empty($goods)) {
			return false;
		}

		if (!empty($goods['status']) && empty($goods['status'])) {
			return array('canbuy' => false, 'buymsg' => '已下架');
		}

		$goods = set_medias($goods, 'thumb');
		if ((0 < $goods['credit']) && (0 < $goods['money'])) {
			$goods['acttype'] = 0;
		} else if (0 < $goods['credit']) {
			$goods['acttype'] = 1;
		} else if (0 < $goods['money']) {
			$goods['acttype'] = 2;
		} else {
			$goods['acttype'] = 3;
		}

		if (intval($goods['isendtime']) == 1) {
			$goods['endtime_str'] = date('Y-m-d H:i', $goods['endtime']);
		}

		$goods['timestart_str'] = date('Y-m-d H:i', $goods['timestart']);
		$goods['timeend_str'] = date('Y-m-d H:i', $goods['timeend']);
		$goods['timestate'] = '';
		$goods['canbuy'] = !empty($goods['status']) && empty($goods['deleted']);

		if (empty($goods['canbuy'])) {
			$goods['buymsg'] = '已下架';
		} else {
			if ($goods['goodstype'] == 3) {
				if (($goods['packetsurplus'] <= 0) || ($goods['surplusmoney'] <= $goods['packetlimit'])) {
					$goods['canbuy'] = false;
					$goods['buymsg'] = empty($goods['type']) ? '已兑完' : '已抽完';
				}
			} else if (0 < $goods['total']) {
				$logcount = Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and status >= 2')->count();
				$goods['logcount'] = $logcount;

				if ($goods['joins'] < $logcount) {
					Db::name('shop_creditshop_goods')->where('id',$id)->update(array('joins' => $logcount));
				}
			} else {
				$goods['canbuy'] = false;
				$goods['buymsg'] = empty($goods['type']) ? '已兑完' : '已抽完';
			}

			if ($goods['hasoption'] && $optionid) {
				$option = Db::name('shop_creditshop_goods_option')->where('id = ' . $optionid . ' and goodsid = ' . $id . ' ')->field('total,credit,money,title as optiontitle,weight')->find();
				$goods['credit'] = $option['credit'];
				$goods['money'] = $option['money'];
				$goods['weight'] = $option['weight'];
				$goods['total'] = $option['total'];
				$goods['optiontitle'] = $option['optiontitle'];

				if ($option['total'] <= 0) {
					$goods['canbuy'] = false;
					$goods['buymsg'] = empty($goods['type']) ? '已兑完' : '已抽完';
				}
			}

			if ($goods['isverify'] == 0) {
				if ($goods['dispatchtype'] == 1) {
					if (empty($goods['dispatchid'])) {
						$dispatch = model('dispatch')->getDefaultDispatch($goods['merchid']);
					}
					else {
						$dispatch = model('dispatch')->getOneDispatch($goods['dispatchid']);
					}

					if (empty($dispatch)) {
						$dispatch = model('dispatch')->getNewDispatch($goods['merchid']);
					}

					$areas = iunserializer($dispatch['areas']);
					if (!empty($areas) && is_array($areas)) {
						$firstprice = array();

						foreach ($areas as $val) {
							$firstprice[] = $val['firstprice'];
						}

						array_push($firstprice, model('dispatch')->getDispatchPrice(1, $dispatch));
						$ret = array('min' => round(min($firstprice), 2), 'max' => round(max($firstprice), 2));
						$goods['areas'] = $ret;
					} else {
						$ret = model('dispatch')->getDispatchPrice(1, $dispatch);
					}

					$goods['dispatch'] = $ret;
				}
			} else {
				$goods['dispatch'] = 0;
			}

			if ($goods['canbuy']) {
				if (0 < $goods['totalday']) {
					$logcount = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and status>=2 and  date_format(from_UNIXTIME(`createtime`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\')')->count();

					if ($goods['totalday'] <= $logcount) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = empty($goods['type']) ? '今日已兑完' : '今日已抽完';
					}
				}
			}

			if ($goods['canbuy']) {
				if (0 < $goods['chanceday']) {
					$logcount = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and mid=' . $member['id'] . ' and status>0 and  date_format(from_UNIXTIME(`createtime`),\'%Y-%m-%d\') = date_format(now(),\'%Y-%m-%d\')')->count();

					if ($goods['chanceday'] <= $logcount) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = empty($goods['type']) ? '今日已兑换' : '今日已抽奖';
					}
				}
			}

			if ($goods['canbuy']) {
				if (0 < $goods['chance']) {
					$logcount = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and mid= ' . $member['id'] . ' and status>0')->count();

					if ($goods['chance'] <= $logcount) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = empty($goods['type']) ? '已兑换' : '已抽奖';
					}
				}
			}

			if ($goods['canbuy']) {
				if (0 < $goods['usermaxbuy']) {
					$logcount = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and mid=' . $member['id'])->count();

					if ($goods['chance'] <= $logcount) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = '已参加';
					}
				}
			}

			if ($goods['canbuy']) {
				if (($credit < $goods['credit']) && (0 < $goods['credit'])) {
					$goods['canbuy'] = false;
					$goods['buymsg'] = '积分不足';
				}
			}

			if ($goods['canbuy']) {
				if ($goods['istime'] == 1) {
					if (time() < $goods['timestart']) {
						$goods['canbuy'] = false;
						$goods['timestate'] = 'before';
						$goods['buymsg'] = '活动未开始';
					}
					else if ($goods['timeend'] < time()) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = '活动已结束';
					}
					else {
						$goods['timestate'] = 'after';
					}
				}
			}

			if ($goods['canbuy']) {
				if (($goods['isendtime'] == 1) && $goods['isverify']) {
					if ($goods['endtime'] < time()) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = '活动已结束(超出兑换期)';
					}
				}
			}

			$levelid = $member['level'];
			$groupid = $member['groupid'];

			if ($goods['canbuy']) {
				if ($goods['buylevels'] != '') {
					$buylevels = explode(',', $goods['buylevels']);

					if (!in_array($levelid, $buylevels)) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = '无会员特权';
					}
				}
			}

			if ($goods['canbuy']) {
				if ($goods['buygroups'] != '') {
					$buygroups = explode(',', $goods['buygroups']);

					if (!in_array($groupid, $buygroups)) {
						$goods['canbuy'] = false;
						$goods['buymsg'] = '无会员特权';
					}
				}
			}
		}

		if ((intval($goods['money']) - $goods['money']) == 0) {
			$goods['money'] = intval($goods['money']);
		}

		if ((intval($goods['minmoney']) - $goods['minmoney']) == 0) {
			$goods['minmoney'] = intval($goods['minmoney']);
		}

		if ((intval($goods['minmoney']) - $goods['minmoney']) == 0) {
			$goods['minmoney'] = intval($goods['minmoney']);
		}

		return $goods;
	}
}