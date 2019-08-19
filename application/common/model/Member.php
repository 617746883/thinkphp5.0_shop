<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Member extends \think\Model
{
	/**
     * 获取所有会员等级
     * @global type $_W
     * @return type
     */
	public static function getLevels($all = true)
	{
		$condition = ' 1 ';

		if (!$all) {
			$condition = ' and enabled=1 ';
		}

		return Db::name('member_level')->where($condition)->order('level','asc')->select();
	}

	/**
     * 获取所有会员分组
     * @global type $_W
     * @return type
     */
	public static function getGroups()
	{
		return Db::name('member_group')->order('id','asc')->select();
	}

	/**
     * 获取会员信息
     */
	public function getMember($mid = 0)
	{
		$uid = (int) $mid;

		if ($uid == 0) {
			return null;
		}
		$info = Db::name('member')->where('id',$uid)->find();

		return $info;
	}

	/**
     * 获取会员信息
     */
	public function getMemberByUid($uid = 0)
	{
		$uid = (int) $uid;

		if ($uid == 0) {
			return null;
		}
		$info = Db::name('member')->where('uid',$uid)->find();

		return $info;
	}

	// 获取直播平台会员信息 
	public static function getLiveMember($uid = 0) 
	{
		if(empty($uid)) {
			return array();
		}
		$user = array();
		$user = Db::connect('db_configfanwe')->name('user')->where('id = ' . intval($uid))->find();
        return $user;
	}

	public function getCredit($mid = 0, $credittype = 'credit1')
	{
		$item = Db::name('member')->where('id',$mid)->find();

		return (empty($item[$credittype]) ? 0 : $item[$credittype]);
	}

	public function setCredit($mid = 0, $credittype = 'credit1', $credits = 0, $log = array())
	{
		$member = self::getMember($mid);

		if (empty($log)) {
			$log = array($uid, '未记录');
		}
		else if (!(is_array($log))) {
			$log = array(0, $log);
		}


		if (($credittype == 'credit1') && empty($log[0]) && (0 < $credits)) {
			$shopset = model('common')->getSysset('trade');

			if (empty($member['diymaxcredit'])) {
				if (0 < $shopset['maxcredit']) {
					if ($shopset['maxcredit'] <= $member['credit1']) {
						return array('errno'=>0,'message'=>'用户积分已达上限');
					}


					if ($shopset['maxcredit'] < ($member['credit1'] + $credits)) {
						$credits = $shopset['maxcredit'] - $member['credit1'];
					}

				}

			}
			else if (0 < $member['maxcredit']) {
				if ($member['maxcredit'] <= $member['credit1']) {
					return array('errno'=>0,'message'=>'用户积分已达上限');
				}


				if ($member['maxcredit'] < ($member['credit1'] + $credits)) {
					$credits = $member['maxcredit'] - $member['credit1'];
				}

			}

		}

		$log_data = array('mid' => intval($mid), 'credittype' => $credittype, 'num' => $credits, 'createtime' => time(), 'module' => 'shop', 'operator' => intval($log[0]), 'remark' => $log[1]);

		$value = Db::name('member')->where('id',$mid)->value($credittype);
		$newcredit = $credits + $value;

		if ($newcredit <= 0) {
			$newcredit = 0;
		}
		Db::name('member')->where('id',$mid)->setField($credittype,$newcredit);
		$logid = Db::name('member_credits_record')->insertGetId($log_data);
		return array('errno'=>1,'logid'=>$logid);
	}

	public function getLevel($mid)
	{
		if (empty($mid)) {
			return false;
		}

		$member = model('member')->getMember($mid);

		if (!(empty($member)) && !(empty($member['level']))) {
			$level = Db::name('member_level')->where('id',$member['level'])->find();
			if (!(empty($level))) {
				return $level;
			}
		}
		$shopset = model('common')->getSysset();
		return array('levelname' => (empty($shopset['shop']['levelname']) ? '普通会员' : $shopset['shop']['levelname']), 'discount' => (empty($shopset['shop']['leveldiscount']) ? 10 : $shopset['shop']['leveldiscount']));
	}

	/**
     * 会员升级
     * @param type $mid
     */
	public function upgradeLevel($mid, $orderid = 0)
	{
		if (empty($mid)) {
			return;
		}

		$shopset = model('common')->getSysset('shop');
		$leveltype = intval($shopset['leveltype']);
		$member = model('member')->getMember($mid);

		if (empty($member)) {
			return;
		}

		$level = false;

		if (empty($leveltype)) {
			$ordermoney = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id=og.orderid','left')->where('o.mid',$mid)->where('o.status',3)->sum('og.realprice');
			$level = Db::name('member_level')->where('enabled = 1 and ' . $ordermoney . ' >= ordermoney and ordermoney>0')->order('level','desc')->find();
		} else if ($leveltype == 1) {
			$ordercount = Db::name('shop_order')->where('mid',$mid)->where('status',3)->count();
			$level = Db::name('member_level')->where('enabled=1 and ' . $ordercount . ' >= ordercount and ordercount>0')->order('level','desc')->find();
		} else if ($leveltype == 2) {
			$creditnum = $member['credit1'];
			$level = Db::name('member_level')->where('enabled=1 and ' . $creditnum . ' >= creditnum and creditnum>0')->order('level','desc')->find();
		}

		if (!(empty($orderid))) {
			$goods_level = self::getGoodsLevel($mid, $orderid);

			if (empty($level)) {
				$level = $goods_level;
			} else if (!(empty($goods_level))) {
				if ($level['level'] < $goods_level['level']) {
					$level = $goods_level;
				}
			}
		}

		if (empty($level)) {
			return;
		}

		if ($level['id'] == $member['level']) {
			return;
		}

		$oldlevel = self::getLevel($mid);
		$canupgrade = false;

		if (empty($oldlevel['id'])) {
			$canupgrade = true;
		} else if ($oldlevel['level'] < $level['level']) {
			$canupgrade = true;
		}
		if ($canupgrade) {
			Db::name('member')->where('id',$member['id'])->setField('level',$level['id']);
			model('notice')->sendMemberUpgradeMessage($mid, $oldlevel, $level);
		}

	}

	public function getGoodsLevel($mid, $orderid)
	{
		$order_goods = Db::name('shop_order_goods')->where('orderid',$orderid)->field('goodsid')->select();
		$levels = array();
		$data = array();

		if (!(empty($order_goods))) {
			foreach ($order_goods as $k => $v ) {
				$item = self::getOneGoodsLevel($mid, $v['goodsid']);

				if (!(empty($item))) {
					$levels[$item['level']] = $item;
				}
			}
		}

		if (!(empty($levels))) {
			$level = max(array_keys($levels));
			$data = $levels[$level];
		}

		return $data;
	}

	public function getOneGoodsLevel($mid, $goodsid)
	{
		$level_info = self::getLevel($mid);
		$level = intval($level_info['level']);
		$data = array();
		$levels = Db::name('member_level')->where(' buygoods=1 and level and level > ' . $level)->select();

		if (!(empty($levels))) {
			foreach ($levels as $k => $v ) {
				$goodsids = iunserializer($v['goodsids']);

				if (!(empty($goodsids))) {
					if (in_array($goodsid, $goodsids)) {
						$data = $v;
					}
				}
			}
		}

		return $data;
	}

	public static function setGroups($mid, $group_ids, $reason = '')
	{
		$is_id = false;
		$condition = 'id = ' . $mid;

		if (is_array($group_ids)) {
			$group_arr = $group_ids;
			$group_ids = implode(',', $group_ids);
		} else {
			if (is_string($group_ids) || is_numeric($group_ids)) {
				$group_arr = explode(',', $group_ids);
			} else {
				return false;
			}
		}

		$old_group_ids = Db::name('member')->where($condition)->value('groupid');
		$diff_ids = explode(',', $group_ids);

		if (!(empty($old_group_ids))) {
			$old_group_ids = explode(',', $old_group_ids);
			$group_ids = array_merge($old_group_ids, $diff_ids);
			$group_ids = array_flip(array_flip($group_ids));
			$group_ids = implode(',', $group_ids);
			$diff_ids = array_diff($diff_ids, $old_group_ids);
		}

		Db::name('member')->where($condition)->setField('groupid',$group_ids);

		// foreach ($diff_ids as $groupid ) {
		// 	pdo_insert('ewei_shop_member_group_log', array('add_time' => date('Y-m-d H:i:s'), 'group_id' => $groupid, 'content' => $reason, 'mid' => intval($mid), 'mid' => ($is_id ? '' : $mid)));D
		// }

		return true;
	}

	public static function getSalt()
	{
		$salt = random(16);

		while (1) {
			$count = Db::name('member')->where('salt',$salt)->count();

			if ($count <= 0) {
				break;
			}

			$salt = random(16);
		}

		return $salt;
	}


}