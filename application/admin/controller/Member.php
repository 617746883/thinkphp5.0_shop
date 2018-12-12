<?php
/**
 * 会员管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Member extends Base
{
	public function index()
	{
		$day=array('day'=>0,'day1'=>0,'day7'=>0,'ascension'=>0,'ascension1'=>0,'ascension7'=>0);
		$time=strtotime(date('Y-m-d'));
		$createtime=Db::name('member')->field('createtime')->select();
		foreach ($createtime as $key => $value) {
			if($value['createtime']>=$time)
			{
				$day['day']=$day['day']+1;
			}if(($time-$value['createtime'])<=86400 && $value['createtime']< $time)
			{
				$day['day1']=$day['day1']+1;
			}if($value['createtime']>=$time || ($time-$value['createtime'])<=518400)
			{
				$day['day7']=$day['day7']+1;
			}
		}
		$is=count($createtime);
		$day['ascension']=round(($day['day']*100)/$is,2);
		$day['ascension1']=round(($day['day1']*100)/$is,2);
		$day['ascension7']=round(($day['day7']*100)/$is,2);
		$this->assign(['day'=>$day]);
		return $this->fetch('member/index');
	}

	public function mlist()
	{
		$psize = 20;
		$condition = ' 1 ';
		$mid = input('mid');
		$realname = input('realname');
		$level = input('level');
		$groupid = input('groupid');
		$isblack = input('isblack');
		if (!(empty($mid))) {
			$condition .= ' and id= ' . $mid;
		}

		if (!(empty($realname))) {
			$keyword = trim($realname);
			$condition .= ' and ( realname like "%' . $keyword . '%" or nickname like "%' . $keyword . '%" or mobile like "%' . $keyword . '%" or id like "%' . $keyword . '%")';
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!(empty($_GET['time']['start'])) && !(empty($_GET['time']['end']))) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND createtime >= ' . $starttime . ' AND createtime <= ' . $endtime;
		}

		if ($level != '') {
			$condition .= ' and level=' . intval($level);
		}

		if ($groupid != '') {
			$condition .= ' and find_in_set(' . intval($groupid) . ',groupid) ';
		}

		if ($isblack != '') {
			$condition .= ' and isblack=' . intval($isblack);
		}

		$list = Db::name('member')->where($condition)->order('id,createtime desc')->paginate($psize);
		$pager = $list->render();

		foreach ($list as $val ) {
			$list_group[] = trim($val['groupid'], ',');
			$list_level[] = trim($val['level'], ',');
		}

		isset($list_group) && ($list_group = array_values(array_filter($list_group)));
		if (!(empty($list_group))) {
			$res_group = Db::name('member_group')->where('id','in',implode(',', $list_group))->field('id,groupname')->select();
			foreach ($res_group as $val) {
				$res_group[$val['id']] = $val;
			}
		}

		isset($list_level) && ($list_level = array_values(array_filter($list_level)));
		if (!(empty($list_level))) {
			$res_level = Db::name('member_level')->where('id','in',implode(',', $list_level))->field('id,levelname')->select();
			foreach ($res_level as $val) {
				$res_level[$val['id']] = $val;
			}
		}

		$shop = model('common')->getSysset('shop');
		foreach ($list as $k => $row) {
			$row['groupname'] = ((isset($res_group[$row['groupid']]) ? $res_group[$row['groupid']]['groupname'] : ''));
			$row['levelname'] = ((isset($res_level[$row['level']]) ? $res_level[$row['level']]['levelname'] : ''));			
			$row['levelname'] = ((empty($row['levelname']) ? ((empty($shop['levelname']) ? '普通会员' : $shop['levelname'])) : $row['levelname']));
			$row['ordercount'] = Db::name('shop_order')->where('mid',$row['id'])->where('status',3)->count();
			$row['ordermoney'] = Db::name('shop_order')->where('mid',$row['id'])->where('status',3)->sum('price');
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}		
		unset($row);
		$groups = model('member')->getGroups();
		$levels = model('member')->getLevels();
		$set = model('common')->getSysset();
		$default_levelname = ((empty($set['shop']['levelname']) ? '普通等级' : $set['shop']['levelname']));
		$this->assign(['list'=>$list,'pager'=>$pager,'mid' => $mid,'realname'=>$realname,'starttime'=>$starttime,'endtime'=>$endtime,'level'=>$level,'groupid'=>$groupid,'isblack'=>$isblack,'levels'=>$levels,'groups'=>$groups,'set'=>$set,'default_levelname'=>$default_levelname]);
		return $this->fetch('member/list');
	}

	public function detail()
	{	
		$id = input('id/d');
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$shopset = model('common')->getSysset();
		$shop = $shopset['shop'];
		$member = model('member')->getMember($id);
		$groups = model('member')->getGroups();
		$levels = model('member')->getLevels();
		$member['self_ordercount'] = Db::name('shop_order')->where('mid',$id)->where('status',3)->count();
		$member['self_ordermoney'] = Db::name('shop_order')->where('mid',$id)->where('status',3)->sum('price');
		$order = Db::name('shop_order')->where('mid',$id)->where('status','>=',1)->field('finishtime')->order('finishtime','desc')->find();
		$member['last_ordertime'] = $order['finishtime'];
		$this->assign(['member'=>$member,'groups' => $groups,'levels'=>$levels,'shop' => $shop]);
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));

			if ($data['maxcredit'] < 0) {
				$data['maxcredit'] = 0;
			}
			if (!(empty($data['mobileverify']))) {
				if (empty($data['mobile'])) {
					show_json(0, '绑定手机号请先填写用户手机号!');
				}
				$m = Db::name('member')->where('mobile',$data['mobile'])->where('mobileverify',1)->field('id')->find();

				if (!(empty($m)) && ($m['id'] != $id)) {
					show_json(0, '此手机号已绑定其他用户!(uid:' . $m['id'] . ')');
				}
			}


			$data['pwd'] = trim($data['pwd']);

			if (!(empty($data['pwd']))) {
				$salt = $member['salt'];

				if (empty($salt)) {
					$salt = model('member')->getSalt();
				}

				$data['password'] = md5($salt . $data['pwd'] . config('authkey'));
				$data['salt'] = $salt;
				unset($data['pwd']);
			} else {
				unset($data['pwd'], $data['salt']);
			}
			if (is_array($data['groupid'])) {
				$data['groupid'] = implode(',', $data['groupid']);
			}
			if (empty($data['groupid'])) {
				$data['groupid'] = 0;
			}
			Db::name('member')->where('id',$id)->update($data);
			$member = array_merge($member, $data);
			model('shop')->plog('member.list.edit', '修改会员资料  ID: ' . $member['id'] . ' <br/> 会员信息:  ' . '/' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			show_json(1);
		}
		return $this->fetch('member/detail/detail');
	}

	public function setblack()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = ((is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0));
		}
		$members = Db::name('member')->where('id','in',$id)->field('id,nickname,realname,mobile')->select();
		$black = intval(input('isblack'));

		foreach ($members as $member ) {
			if (!(empty($black))) {
				Db::name('member')->where('id','eq',$member['id'])->setField('isblack',1);
				model('shop')->plog('member.list.edit', '设置黑名单 <br/>用户信息:  ID: ' . $member['id'] . ' /  ' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			} else {
				Db::name('member')->where('id','eq',$member['id'])->setField('isblack',0);
				model('shop')->plog('member.list.edit', '取消黑名单 <br/>用户信息:  ID: ' . $member['id'] . ' /  ' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
			}
		}

		show_json(1);
	}

	public function delete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = ((is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0));
		}

		$members = Db::name('member')->where('id','in',$id)->field('id,nickname,realname,mobile')->select();

		foreach ($members as $member ) {
			Db::name('member')->where('id','eq',$member['id'])->delete();
			model('shop')->plog('member.list.delete', '删除会员  ID: ' . $member['id'] . ' <br/>会员信息: ' . $member['nickname'] . '/' . $member['realname'] . '/' . $member['mobile']);
		}

		show_json(1, array('url' => referer()));
	}

	public function changelevel()
	{
		if (Request::instance()->isPost()) {
			$toggle = trim(input('toggle'));
			$ids = $_POST['ids'];
			$levelid = $_POST['level'];
			!(strpos($levelid, ',')) && ($levelid = intval($_POST['level']));
			if (empty($ids) || !(is_array($ids))) {
				show_json(0, '请选择要操作的会员');
			}

			if (empty($toggle)) {
				show_json(0, '请选择要操作的类型');
			}

			$ids = array_filter($ids);
			$idsstr = implode(',', $ids);
			$loginfo = '批量修改';

			if ($toggle == 'group') {
				if (!(empty($levelid))) {
					$levelid_arr = explode(',', $levelid);

					if (!(empty($levelid_arr))) {
						foreach ($levelid_arr as $id ) {
							$group = Db::name('member_group')->where('id',$id)->find();
							if (empty($group)) {
								show_json(0, '此分组不存在');
							}
						}
					} else {
						show_json(0, '此分组不存在');
					}
				} else {
					$group = array('groupname' => '无分组');
				}

				$loginfo .= '用户分组 分组名称：' . $group['groupname'];
			} else {
				if (!(empty($levelid))) {
					$level = Db::name('member_level')->where('id',$levelid)->find();

					if (empty($level)) {
						show_json(0, '此等级不存在');
					}
				} else {
					$set = model('common')->getSysset();
					$level = array('levelname' => (empty($set['shop']['levelname']) ? '普通等级' : $set['shop']['levelname']));
				}

				$arr = array('level' => $levelid);
				$loginfo .= '用户等级 等级名称：' . $level['levelname'];
			}

			$changeids = array();
			$members = Db::name('member')->where('id','in',$idsstr)->field('id,nickname,realname,mobile')->select();
			if (!(empty($members))) {
				foreach ($members as $member ) {
					if ($toggle == 'group') {
						model('member')->setGroups($member['id'], $levelid, '管理员设置批量分组');
					} else {
						Db::name('member')->where('id',$member['id'])->update($arr);
						$changeids[] = $member['id'];
					}
				}
			}

			if (!(empty($changeids))) {
				$loginfo .= ' 用户id：' . implode(',', $changeids);
				model('shop')->plog('member.list.edit', $loginfo);
			}

			show_json(1);
		}
		return $this->fetch('member/changelevel');
	}

	public function level()
	{
		$enabled = input('enabled');
		$keyword = input('keyword');
		$set = model('common')->getSysset();
		$shopset = $set['shop'];
		$default = array('id' => 'default', 'levelname' => empty($shopset['levelname']) ? '普通等级' : $shopset['levelname'], 'discount' => $shopset['leveldiscount'], 'ordermoney' => 0, 'ordercount' => 0, 'membercount' => Db::name('member')->where('level',0)->count());
		$condition = ' 1 ';
		if ($enabled != '') {
			$condition .= ' and enabled=' . intval($enabled);
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and ( levelname like "%' . $keyword . '%")';
		}

		$others = Db::name('member_level')->where($condition)->order('level','asc')->select();

		foreach ($others as &$row) {
			$row['membercount'] = Db::name('member')->where('level',$row['id'])->count();
		}

		unset($row);

		if (empty($keyword)) {
			$list = array_merge(array($default), $others);
		}
		else {
			$list = $others;
		}
		$this->assign(['list'=>$list,'enabled'=>$enabled,'keyword'=>$keyword,'shopset'=>$shopset]);
		return $this->fetch('member/level/list');
	}

	public function leveladd()
	{
		$leveldata = $this->levelpost();
		return $leveldata;
	}

	public function leveledit()
	{
		$leveldata = $this->levelpost();
		return $leveldata;
	}

	protected function levelpost()
	{
		$id = trim(input('id'));
		$goods = array();
		$set = model('common')->getSysset();
		if ($id == 'default') {
			$level = array('id' => 'default', 'levelname' => empty($set['shop']['levelname']) ? '普通等级' : $set['shop']['levelname'], 'discount' => $set['shop']['leveldiscount'], 'ordermoney' => 0, 'ordercount' => 0);
		} else {
			$level = Db::name('member_level')->where('id',intval($id))->find();

			if (!empty($level)) {
				$goodsids = iunserializer($level['goodsids']);

				if (!empty($goodsids)) {
					$goods = Db::name('shop_goods')->where('id','in',implode(',', $goodsids))->field('id,title,thumb')->select();
				}
			}
		}

		if (Request::instance()->isPost()) {
			$enabled = intval(input('enabled'));
			$data = array('level' => intval(input('level')), 'levelname' => trim(input('levelname')), 'ordercount' => intval(input('ordercount')), 'ordermoney' => trim(input('ordermoney')), 'creditnum' => trim(input('creditnum')), 'discount' => trim(input('discount')), 'enabled' => $enabled);
			$goodsids = iserializer($_POST['goodsids']);
			$buygoods = intval($_POST['buygoods']);

			if (!empty($id)) {
				if ($id == 'default') {
					$updatecontent = '<br/>等级名称: ' . $set['shop']['levelname'] . '->' . $data['levelname'] . '<br/>折扣: ' . $set['shop']['leveldiscount'] . '->' . $data['discount'];
					$set['shop']['levelname'] = $data['levelname'];
					$set['shop']['leveldiscount'] = $data['discount'];
					model('common')->updateSysset($set);
					model('shop')->plog('member.level.edit', '修改会员默认等级' . $updatecontent);
				} else {
					$data['goodsids'] = $goodsids;
					$data['buygoods'] = $buygoods;
					$updatecontent = '<br/>等级名称: ' . $level['levelname'] . '->' . $data['levelname'] . '<br/>折扣: ' . $level['leveldiscount'] . '->' . $data['discount'];
					Db::name('member_level')->where('id',$id)->update($data);
					model('shop')->plog('member.level.edit', '修改会员等级 ID: ' . $id . $updatecontent);
				}
			} else {
				$data['goodsids'] = $goodsids;
				$data['buygoods'] = $buygoods;
				$id = Db::name('member_level')->insertGetId($data);
				model('shop')->plog('member.level.add', '添加会员等级 ID: ' . $id);
			}

			show_json(1);
		}

		$level_array = array();
		$i = 0;

		while ($i < 101) {
			$level_array[$i] = $i;
			++$i;
		}

		$this->assign(['id'=>$id,'level'=>$level,'level_array'=>$level_array,'goods'=>$goods,'set'=>$set]);
		return $this->fetch('member/level/post');
	}

	public function leveldelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('member_level')->where('id','in',$id)->field('id,levelname')->select();

		foreach ($items as $item) {
			Db::name('member_level')->where('id',$item['id'])->delete();
			model('shop')->plog('member.level.delete', '删除等级 ID: ' . $item['id'] . ' 标题: ' . $item['levelname'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function levelenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$enabled = input('enabled');
		$items = Db::name('member_level')->where('id','in',$id)->field('id,levelname')->select();

		foreach ($items as $item) {
			Db::name('member_level')->where('id',$item['id'])->setField('enabled',$enabled);
			model('shop')->plog('member.level.edit', '修改会员等级状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['levelname'] . '<br/>状态: ' . ($enabled) == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function group()
	{
		$list = array(
			array('id' => 'default', 'groupname' => '无分组', 'membercount' => Db::name('member')->where('groupid',0)->count())
			);
		$keyword = input('keyword');
		$condition = ' 1 ';
		if (!(empty($keyword))) {
			$keyword = trim($keyword);
			$condition .= ' and ( groupname like "%' . $keyword . '%")';
		}

		$alllist = Db::name('member_group')->where($condition)->select();

		foreach ($alllist as &$row ) {
			$row['membercount'] = Db::name('member')->where('groupid',$row['id'])->count();
		}
		unset($row);

		if (empty($keyword)) {
			$list = array_merge($list, $alllist);
		} else {
			$list = $alllist;
		}

		$this->assign(['list'=>$list]);
		return $this->fetch('member/group/list');
	}

	public function groupadd()
	{
		$groupdata = $this->grouppost();
		return $groupdata;
	}

	public function groupedit()
	{
		$groupdata = $this->grouppost();
		return $groupdata;
	}

	protected function grouppost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('groupname' => trim(input('groupname')),'description' => trim(input('description')));

			if (!empty($id)) {
				Db::name('member_group')->where('id',$id)->update($data);
				model('shop')->plog('member.group.edit', '修改会员分组 ID: ' . $id);
			}
			else {
				$id = Db::name('member_group')->insertGetId($data);
				model('shop')->plog('member.group.add', '添加会员分组 ID: ' . $id);
			}
			show_json(1);
		}

		$group = Db::name('member_group')->where('id',$id)->find();
		$this->assign(['group'=>$group]);
		echo $this->fetch('member/group/post');
	}

	public function groupdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$items = Db::name('member_group')->where('id','in',$id)->field('id,groupname')->select();

		foreach ($items as $item ) {
			Db::name('member')->where('groupid',$item['id'])->setField('groupid',0);
			Db::name('member_group')->where('id',$item['id'])->delete();
			model('shop')->plog('member.group.delete', '删除分组 ID: ' . $item['id'] . ' 名称: ' . $item['groupname'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function set()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data['levelname'] = trim($data['levelname']);
			$data['levelurl'] = trim($data['levelurl']);
			$data['leveltype'] = intval($data['leveltype']);
			$data['explain'] = model('common')->html_images($data['explain']);
			model('common')->updateSysset(array('member' => $data));
			$shop = model('common')->getSysset('shop');
			$shop['levelname'] = $data['levelname'];
			$shop['levelurl'] = $data['levelurl'];
			$shop['leveltype'] = $data['leveltype'];
			$shop['memberexplain'] = $data['explain'];
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('member.sysset.edit', '修改系统设置-会员设置');
			show_json(1);
		}
		$data = model('common')->getSysset('member');
		if (!(isset($data['levelname']))) {
			$shop = model('common')->getSysset('shop');
			$data['levelname'] = $shop['levelname'];
			$data['levelurl'] = $shop['levelurl'];
			$data['leveltype'] = $shop['leveltype'];
			$data['explain'] = $shop['memberexplain'];
		}
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function ajaxall()
	{
		echo json_encode(array('ajaxmembergender' => $this->ajaxmembergender(), 'ajaxmemberlevel' => $this->ajaxmemberlevel(), 'ajaxprovince' => $this->ajaxprovince(), 'ajaxnewmember0' => $this->ajaxnewmember(0), 'ajaxnewmember1' => $this->ajaxnewmember(1), 'ajaxnewmember7' => $this->ajaxnewmember(7)));
	}

	protected function ajaxnewmember($day = 0)
	{
		$day = (int) $day;

		if (isset($_GET['day'])) {
			$day = (int) input('day');
		}

		$member_count = Db::name('member')->count();
		$newmember = $this->selectMemberCreate($day);
		return array('count' => (int) $newmember, 'rate' => empty($member_count) ? 0 : (int) number_format(round($newmember / $member_count, 3) * 100));
	}

	protected function selectMemberCreate($day = 0)
	{
		$day = (int) $day;

		if ($day != 0) {
			$createtime1 = strtotime(date('Y-m-d', time() - ($day * 3600 * 24)));
			$createtime2 = strtotime(date('Y-m-d', time()));
		}
		else {
			$createtime1 = strtotime(date('Y-m-d', time()));
			$createtime2 = strtotime(date('Y-m-d', time() + (3600 * 24)));
		}

		return Db::name('member')->where('createtime','between',[$createtime1,$createtime2])->count();
	}

	protected function ajaxmembergender()
	{
		$gender_array = array(0, 0, 0);
		$member = Db::name('member')->group('gender')->field('gender,count(gender) as gender_num')->select();

		foreach ($member as $key => $val) {
			if ($val['gender'] == -1) {
				$gender_array[0] += (int) $val['gender_num'];
			}
			else {
				$gender_array[$val['gender']] += (int) $val['gender_num'];
			}
		}

		return $gender_array;
	}

	protected function ajaxmemberlevel()
	{
		$levels = Db::name('member_level')->order('level','asc')->select();
		$levelname = array();

		foreach ($levels as $l) {
			$levelname[$l['id']] = $l['levelname'];
		}

		$levelname[0] = '普通等级';
		ksort($levelname);
		$member_level = Db::name('member')->group('level')->field('level,count(level) as level_num')->select();
		$levels_array = array();

		foreach ($levelname as $lkey => $lvalue) {
			$levels_array[$lkey] = 0;
		}

		foreach ($member_level as $key => $val) {
			if (array_key_exists($val['level'], $levelname)) {
				$levels_array[$val['level']] = $val['level_num'];
			}
			else {
				$levels_array[0] += $val['level_num'];
			}
		}

		if (!array_key_exists(0, $levels_array)) {
			$levels_array[0] = 0;
		}

		$count = array_values($levels_array);
		$name = array_values($levelname);
		$res = array();

		foreach ($count as $key => $value) {
			$res[$key]['value'] = $value;
			$res[$key]['name'] = $name[$key];
		}

		return array('count' => $count, 'name' => $name, 'data' => $res);
	}

	protected function ajaxprovince()
	{
		$province = Db::name('member')->group('province')->field('province,count(province) as province_num')->select();
		$result = array();

		foreach ($province as $array) {
			$array['province'] = preg_replace('/(市|省)(.*)/', '', $array['province']);
			$res = array('name' => $array['province'], 'value' => (int) $array['province_num']);
			$result[] = $res;
		}

		return $result;
	}

	public function query()
	{
		$keyword = trim(input('keyword'));
		$condition = ' 1 ';
		if (!empty($keyword)) {
			$condition .= ' AND (`realname` LIKE "%' . $keyword . '%" or `nickname` LIKE "%' . $keyword . '%" or `mobile` LIKE "%' . $keyword . '%" or `id` LIKE "%' . $keyword . '%")';
		}

		$ds = Db::name('member')->where($condition)->select();

		foreach ($ds as &$value) {
			$value['nickname'] = htmlspecialchars($value['nickname'], ENT_QUOTES);
		}

		unset($value);

		if (input('suggest')) {
			exit(json_encode(array('value' => $ds)));
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('');
	}

	public function recharge()
	{
		$type = trim(input('type'));
		$id = intval(input('id'));
		$profile = model('member')->getMember($id);

		if (Request::instance()->isPost()) {
			$typestr = ($type == 'credit1' ? '积分' : '余额');
			$num = floatval(input('num'));
			$remark = trim(input('remark'));

			if ($num <= 0) {
				show_json(0, array('message' => '请填写大于0的数字!'));
			}

			$changetype = input('changetype');
			if (intval($changetype) == 2) {
				$num -= $profile[$type];
			} else {
				if (intval($changetype) == 1) {
					$num = 0 - $num;
				}
			}

			model('member')->setCredit($profile['id'], $type, $num, array(0, '后台会员充值' . $typestr . ' ' . $remark));
			$changetype = 0;
			$changenum = 0;

			if (0 <= $num) {
				$changetype = 0;
				$changenum = $num;
			} else {
				$changetype = 1;
				$changenum = 0 - $num;
			}
			
			if ($type == 'credit1') {
				model('notice')->sendMemberPointChange($profile['id'], $changenum, $changetype);
			}

			model('shop')->plog('member.recharge.' . $type, '充值' . $typestr . ': ' . $num . ' 会员信息: ID: ' . $profile['id'] . ' /  ' . $profile['id'] . '/' . $profile['nickname'] . '/' . $profile['realname'] . '/' . $profile['mobile']);
			show_json(1, array('url' => referer()));
		}
		$this->assign(['id'=>$id,'profile'=>$profile,'type'=>$type]);
		echo $this->fetch('member/detail/recharge');
	}

}