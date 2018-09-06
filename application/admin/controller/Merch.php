<?php
/**
 * 门店管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Merch extends Base
{
	public function index()
	{
		return $this->fetch('index');
	}

	public function ajaxstore()
	{
		$totals = model('store')->getStoreTotals();
		$order0 = model('store')->getStoreOrderTotals(0);
		$order3 = model('store')->getStoreOrderTotals(3);
		$totals['totalmoney'] = $order0['totalmoney'];
		$totals['totalcount'] = $order0['totalcount'];
		$totals['tmoney'] = $order3['totalmoney'];
		$totals['tcount'] = $order3['totalcount'];
		show_json(1, $totals);
	}

	public function reg1()
	{
		$regdata = $this->regdata(0);
		return $regdata;
	}

	public function reg0()
	{
		$regdata = $this->regdata(-1);
		return $regdata;
	}

	protected function regdata($type = 0)
	{
		$psize = 20;
		$condition = ' 1 ';
		$keyword = trim(input('keyword'));
		$status = $type;
		if (!(empty($keyword))) 
		{
			$condition .= ' and ( merchname like ' . $keyword . ' or realname like ' . $keyword . ' or mobile like ' . $keyword . ')';
		}
		if($status !== '')
		{
			$condition .= ' and status=' . $status;
		}
		
		$list = Db::name('shop_store_reg')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('merch/reg/index');
	}

	public function regdetail()
	{
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$id = input('id/d');
		$item = Db::name('shop_store_reg')->where('id',$id)->find();
		if (empty($item)) {
			$this->error('未找到店鋪入驻申请!', url('admin/merch/reg1'));
		}
		$member = model('member')->getMember($item['mid']);

		if (Request::instance()->isPost()) 
		{
			$status = input('status');
			$reason = trim(input('reason'));
			if ($status == -1) {
				if (empty($reason)) {
					show_json(0, '请填写驳回理由.');
				}
			} 
			
			$item['status'] = $status;
			$item['reason'] = $reason;
			$item['merchname'] = trim($_POST['merchname']);
			$item['salecate'] = trim($_POST['salecate']);
			$item['desc'] = trim($_POST['desc']);
			$item['realname'] = trim($_POST['realname']);
			$item['mobile'] = trim($_POST['mobile']);
			Db::name('shop_store_reg')->where('id',$item['id'])->update($item);
			if ($status == 1) {
				$usercount = Db::name('shop_store')->where('regid',$item['id'])->count();
				if (empty($usercount)) {
					$user = $item;
					unset($user['id']);
					unset($user['reason']);
					$user['regid'] = $item['id'];
					$user['status'] = 0;
					$userid = Db::name('shop_store')->insertGetId($user);
					Db::name('shop_store_reg')->where('id',$item['id'])->update($item);
					model('notice')->sendMerchReg($item['id']);
					show_json(1, array('message' => '允许入驻成功，请编辑店鋪账户资料!', 'url' => url('admin/merch/edit', array('id' => $userid))));
				} else {
					show_json(0);
				}
			} else if ($status == -1) {
				Db::name('shop_store_reg')->where('id',$item['id'])->update($item);
				model('notice')->sendMerchReg($item['id']);
			}
			show_json(1);
		}
		$this->assign(['new_area'=>$new_area,'item'=>$item,'member'=>$member]);
		return $this->fetch('merch/reg/detail');
	}

	public function regdelete()
	{
		$id = input('id/d');
		if (empty($id)) 
		{
			$id = input('ids/a');
		}
		$regs = Db::name('shop_store_reg')->where('id','in',$id)->field('id,merchname')->select();
		foreach ($regs as $reg ) 
		{
			Db::name('shop_store_reg')->where('id',$res['id'])->delete();
			model('shop')->plog('merch.reg.delete', '删除入驻申请 <br/> 店鋪名称:  ' . $reg['merchname']);
		}
		show_json(1, array('url' => referer()));
	}

	public function store0()
	{
		$storedata = $this->storedata(0);
		return $storedata;
	}

	public function store1()
	{
		$storedata = $this->storedata(1);
		return $storedata;
	}

	public function store2()
	{
		$storedata = $this->storedata(2);
		return $storedata;
	}

	public function store3()
	{
		$storedata = $this->storedata(3);
		return $storedata;
	}

	protected function storedata($type = 0)
	{		
		$psize = 20;
		$condition = ' 1 and deleted = 0 ';
		$keyword = trim(input('keyword'));
		$groupid = input('groupid');
		$status = input('status');
		if($status == '') {
			$status = $type;
		}
		if (!(empty($keyword))) {
			$condition .= ' and ( u.merchname like ' . $keyword . ' or u.realname like ' . $keyword . ' or u.mobile like ' . $keyword . ')';
		}

		if ($groupid != '') {
			$condition .= ' and u.groupid=' . $groupid;
		}

		if ($status !== '') {
			$status = intval($status);
			if ($status == 3) {
				$condition .= ' and u.status=1 and TIMESTAMPDIFF(DAY,now(),FROM_UNIXTIME(u.accounttime))<=30 ';
			}
			 else {
				$condition .= ' and u.status = ' . $status;
			}
		}

		if ($status == '0') {
			$sortfield = 'u.applytime';
		}
		else {
			$sortfield = 'u.jointime';
		}
		$list = Db::name('shop_store')
			->alias('u')
			->join('shop_store_group g','u.groupid = g.id','left')
			->where($condition)
			->order($sortfield,'desc')
			->field('u.*,g.groupname')
			->paginate($psize);
		$pager = $list->render();		
		$groups = model('store')->getGroups();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword,'groups'=>$groups,'groupid'=>$groupid]);
		return $this->fetch('merch/list');
	}

	public function add()
	{
		$data = $this->post();
		return $data;
	}

	public function edit()
	{
		$data = $this->post();
		return $data;
	}

	protected function post()
	{
		$id = input('id/d');
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);
		$groups = model('store')->getGroups();
		$category = model('store')->getCategory();

		$item = Db::name('shop_store')->where('id',$id)->find();
		if (empty($item) || empty($item['accounttime'])) {
			$accounttime = strtotime('+365 day');
		}
		else {
			$accounttime = $item['accounttime'];
		}
		if (!(empty($item['mid']))) {

			$member = model('member')->getMember($item['mid']);
		}
		if (!(empty($item['paymid']))) {
			$user = model('member')->getMember($item['paymid']);
		}
		if (!(empty($item['accountid']))) {

			$account = Db::name('shop_store_account')->where('id',$item['accountid'])->find(); 
		}
		if (empty($account)) {
			$show_name = $item['uname'];
			$show_pass = model('util')->pwd_encrypt($item['upass'], 'D');
		} else {
			$show_name = $account['username'];
		}		

		if (Request::instance()->isPost()) {
			$status = input('status/d');
			$username = trim(input('username/s'));
			$checkUser = false;
			if (0 < $status) {
				$checkUser = true;
			}
			if (empty(input('groupid'))) {
				show_json(0, '请选择店鋪组!');
			}
			if (empty(input('cateid'))) {
				show_json(0, '请选择店鋪分类!');
			}
			if ($checkUser) {
				if (empty($username)) {
					show_json(0, '请填写账户名!');
				}
				if (empty($account) && empty(input('pwd'))) {
					show_json(0, '请填写账户密码!');
				}
				$where = ' username= "' . $username . '"';
				if (!(empty($account))) {
					$where .= ' and id<> ' . $account['id'];
				}
				$usercount = Db::name('shop_store_account')->where($where)->count();
				if (0 < $usercount) {
					show_json(0, '账户名 ' . $username . ' 已经存在!');
				}
				if (!(empty($account))) {
					if (empty($account['pwd']) && empty(input('pwd'))) {
						show_json(0, '请填写账户密码!');
					}
				}
			}
			$salt = '';
			$pwd = '';
			if (empty($account) || empty($account['salt']) || !(empty(input('pwd')))) {
				$salt = random(8);
				while (1) {
					$saltcount = Db::name('shop_store_account')->where('salt',$salt)->count();
					if ($saltcount <= 0) {
						break;
					}
					$salt = random(8);
				}
				$pwd = md5(trim(input('pwd')) . $salt);
			}
			else {
				$salt = $account['salt'];
				$pwd = $account['pwd'];
			}
			$lng = $lat = '';
			if(!empty(input('map/a')) && is_array(input('map/a')))
			{
				$lng = input('map/a')['lng'];
				$lat = input('map/a')['lat'];
			}
			$banner = input('banner/s');
			$data = array('merchname' => trim(input('merchname')), 'salecate' => trim(input('salecate')), 'realname' => trim(input('realname')), 'mobile' => trim(input('mobile')), 'address' => trim(input('address')), 'tel' => trim(input('tel')), 'lng' => $lng, 'lat' => $lat, 'accounttime' => strtotime(input('accounttime')), 'accounttotal' => input('accounttotal/d'), 'groupid' => input('groupid/d'), 'cateid' => input('cateid/d'), 'isrecommand' => input('isrecommand/d'), 'remark' => trim(input('remark')), 'status' => $status, 'desc' => trim(input('desc1')), 'logo' => trim(input('logo')), 'banner' => $banner, 'paymid' => input('paymid/d',0), 'payrate' => trim(input('payrate'), '%'));
			if (empty($item['jointime']) && ($status == 1)) {
				$data['jointime'] = time();
			}
			$account = array('merchid' => $id, 'username' => $username, 'pwd' => $pwd, 'salt' => $salt, 'status' => 1, 'perms' => serialize(array()), 'isfounder' => 1);
			if (empty($item)) {
				$item['applytime'] = time();
				$id = Db::name('shop_store')->insertGetId($data);
				$account['merchid'] = $id;
				$accountid = Db::name('shop_store_account')->insertGetId($account);
				Db::name('shop_store')->where('id',$id)->setField('accountid',$accountid);
				model('shop')->plog('merch.user.add', '添加店鋪 ID: ' . $data['id'] . ' 店鋪名: ' . $data['merchname'] . '<br/>帐号: ' . $data['username'] . '<br/>到期时间: ' . date('Y-m-d', $data['accounttime']));
			} else {
				Db::name('shop_store')->where('id',$id)->update($data);
				if (!(empty($item['accountid']))) {
					Db::name('shop_store_account')->where('id',$item['accountid'])->update($account);
				}
				else {
					$accountid = Db::name('shop_store_account')->insertGetId($account);
					Db::name('shop_store')->where('id',$id)->setField('accountid',$accountid);
				}
				model('shop')->plog('merch.user.edit', '编辑店鋪 ID: ' . $data['id'] . ' 店鋪名: ' . $item['merchname'] . ' -> ' . $data['merchname'] . '<br/>帐号: ' . $item['username'] . ' -> ' . $data['username'] . '<br/>到期时间: ' . date('Y-m-d', $item['accounttime']) . ' -> ' . date('Y-m-d', $data['accounttime']));
			}
			if($status == 1) {
				model('notice')->sendMerchMessage($id);
			} else {
				if($status == 2) {
					model('notice')->sendMerchMessage($id);
				}
			}
			show_json(1, array('url' => url('admin/merch/edit',array('id'=>$id))));
		}
		
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street,'groups'=>$groups,'category'=>$category,'accounttime'=>$accounttime,'member'=>$member,'user'=>$user,'account'=>$account]);
		return $this->fetch('merch/post');
	}

	public function delete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_store')->where('id','in',$id)->field('id,merchname')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('merch.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['merchname'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_store')->where('id',$id)->field('id,merchname')->find();

		if (!empty($item)) {
			Db::name('shop_store')->where('id',$id)->setField('displayorder',$displayorder);
			model('shop')->plog('merch.edit', '修改门店排序 ID: ' . $item['id'] . ' 门店名称: ' . $item['merchname'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function status()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}
		$status = input('status/d');
		$items = Db::name('shop_store')->where('id','in',$id)->field('id,merchname')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('merch.edit', ('修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['merchname'] . '<br/>状态: ' . $status) == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function goods()
	{
		$sql = 'SELECT *  FROM ' . tablename('ewei_shop_newstore_goodsgroup') . ' WHERE  uniacid=:uniacid  ORDER BY id DESC ';
		$grouplist = pdo_fetchall($sql, array(':uniacid' => $_W['uniacid']));
		$pindex = max(1, intval($_GET['page']));
		$psize = 20;
		$id = intval($_GET['id']);
		$condition = '  ng.storeid = :storeid AND ng.uniacid = :uniacid';
		$params = array(':uniacid' => $_W['uniacid'], ':storeid' => $id);
		$keyword = trim($_GET['keyword']);

		if (!empty($keyword)) {
			$condition .= ' and g.title like "%' . $keyword . '%"';
			$params['"%' . $keyword . '%"'] = '%' . $keyword . '%';
		}

		$goodsgroupid = intval($_GET['goodsgroupid']);

		if (!empty($goodsgroupid)) {
			$condition .= ' and EXISTS(select id from ' . tablename('ewei_shop_newstore_goodsgroup_goods') . ' gg where  gg.goodsgroupid=:goodsgroupid and gg.goodsid = ng.goodsid) ';
			$params[':goodsgroupid'] = $goodsgroupid;
		}

		$sql = 'SELECT ng.*,g.title,g.thumb,g.hasoption,g.type  FROM ' . tablename('ewei_shop_newstore_goods') . "  ng\r\n        INNER JOIN " . tablename('ewei_shop_goods') . "  g ON ng.goodsid = g.id\r\n        WHERE   1 and " . $condition . ' ORDER BY ng.id DESC ';
		$sql .= ' LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		$list = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('ewei_shop_newstore_goods') . "  ng\r\n        INNER JOIN " . tablename('ewei_shop_goods') . "  g ON ng.goodsid = g.id\r\n        WHERE   1 and " . $condition . ' ORDER BY ng.id DESC ', $params);
		$pager = pagination2($total, $pindex, $psize);
		return $this->fetch('merch/goods/index');
	}

	public function group()
	{
		$psize = 20;
		$status = input('status');
		$keyword = input('keyword');
		$condition = ' 1 ';
		if ($status != '') 
		{
			$condition .= ' and status=' . intval($status);
		}
		if (!(empty($keyword))) 
		{
			$keyword = trim($keyword);
			$condition .= ' and groupname like "%' . $keyword . '%"';
		}
		$list = Db::name('shop_store_group')->where($condition)->order('createtime','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('merch/group/index');
	}

	public function groupadd()
	{
		$data = $this->grouppost();
		return $data;
	}

	public function groupedit()
	{
		$data = $this->grouppost();
		return $data;
	}

	protected function grouppost() 
	{
		$id = input('id/d');
		if (Request::instance()->isPost()) 
		{
			$data = array('groupname' => trim(input('groupname')), 'status' => input('status/d'), 'isdefault' => input('isdefault/d'));
			if ($data['isdefault'] == 1) 
			{
				Db::name('shop_store_group')->where('isdefault',1)->setField('isdefault',0);
			}
			if (!(empty($id))) 
			{
				Db::name('shop_store_group')->where('id',$id)->update($data);
				model('shop')->plog('merch.group.edit', '修改店鋪分组 ID: ' . $id);
			}
			else 
			{
				$data['createtime'] = time();
				$id = Db::name('shop_store_group')->insertGetId($data);
				model('shop')->plog('store.group.add', '添加店鋪分组 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/merch/group')));
		}
		$item = Db::name('shop_store_group')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('merch/group/post');
	}

	public function groupdelete() 
	{
		$id = input('id/d');
		if (empty($id)) 
		{
			$id = input('ids/a');
		}
		$items = Db::name("shop_store_group")->where('id','in',$id)->field('id,groupname')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_store_group')->where('id',$item['id'])->delete();
			model('shop')->plog('merch.group.delete', '删除店鋪分组 ID: ' . $item['id'] . ' 标题: ' . $item['groupname'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function groupstatus() 
	{
		$id = input('id/d');
		if (empty($id)) 
		{
			$id = input('ids/a');
		}
		$items = Db::name("shop_store_group")->where('id','in',$id)->field('id,groupname')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_store_group')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('merch.group.edit', (('修改店鋪分组状态<br/>ID: ' . $item['id'] . '<br/>分组名称: ' . $item['groupname'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏'));
		}
		show_json(1, array('url' => referer()));
	}

	public function groupsetdefault() 
	{
		$id = input('id/d');
		$isdefault = input('isdefault/d');
		$group = Db::name('shop_store_group')->where('id',$id)->find();
		if (empty($group)) 
		{
			show_json(0, '抱歉，店鋪分组不存在或是已经被删除！');
		}
		if($isdefault == 1)
		{
			Db::name('shop_store_group')->where('isdefault',1)->setField('isdefault',0);
			Db::name('shop_store_group')->where('id',$group['id'])->setField('isdefault',1);
		}
		else
		{
			Db::name('shop_store_group')->where('id',$group['id'])->setField('isdefault',0);
		}
		
		model('shop')->plog('merch.group.setdefault', '设置默认店鋪分组 ID: ' . $id . ' 分组名称: ' . $group['groupname']);
		show_json(1);
	}

	public function category()
	{
		$psize = 20;
		$condition = ' 1 ';
		$status = input('status');
		$keyword = input('keyword');
		if ($status != '') 
		{
			$condition .= ' and status=' . intval($_GET['status']);
		}
		if (!(empty($keyword))) 
		{
			$keyword = trim($keyword);
			$condition .= ' and catename like "%' . $keyword . '%"';
		}
		$list = Db::name('shop_store_category')->where($condition)->order('createtime','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('merch/category/index');
	}

	public function categoryadd() 
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	public function categoryedit() 
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	protected function categorypost() 
	{
		$id = input('id');
		if (Request::instance()->isPost()) 
		{
			$data = array('catename' => trim(input('catename')), 'status' => input('status'), 'displayorder' => input('displayorder/d'), 'thumb' => input('thumb'), 'isrecommand' => input('isrecommand/d'));
			if (!(empty($id))) 
			{
				Db::name('shop_store_category')->where('id',$id)->update($data);
				model('shop')->plog('merch.category.edit', '修改店鋪分类 ID: ' . $id);
			}
			else 
			{
				$data['createtime'] = time();
				$id = Db::name('shop_store_category')->insertGetId($data);
				model('shop')->plog('merch.category.add', '添加店鋪分类 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/merch/category')));
		}
		$item = Db::name('shop_store_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('merch/category/post');
	}

	public function categorydelete() 
	{
		$id = input('id/d');
		if (empty($id)) 
		{
			$id = input('ids/a');
		}
		$items = Db::name('shop_store_category')->where('id','in',$id)->field('id,catename')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_store_category')->where('id',$item['id'])->delete();
			model('shop')->plog('merch.category.delete', '删除店鋪分类 ID: ' . $item['id'] . ' 标题: ' . $item['catename'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function categorystatus() 
	{
		$id = input('id/d');
		if (empty($id)) 
		{
			$id = input('ids/a');
		}
		$items = Db::name('shop_store_category')->where('id','in',$id)->field('id,catename')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_store_category')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('merch.category.edit', (('修改店鋪分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['catename'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏'));
		}
		show_json(1, array('url' => referer()));
	}

	public function order()
	{
		$psize = 20;
		$condition = ' 1 and o.merchid>0 and o.status>=1';
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$datetime = input('datetime/a');
		if (!(empty($datetime['start'])) && !(empty($datetime['end']))) 
		{
			$starttime = strtotime($datetime['start']);
			$endtime = strtotime($datetime['end']);
			$condition .= ' AND o.createtime >= ' . $starttime .' AND o.createtime <= ' . $endtime;
		}
		$searchfield = strtolower(trim(input('searchfield')));
		$keyword = trim(input('keyword'));
		if (!(empty($searchfield)) && !(empty($keyword))) 
		{
			if ($searchfield == 'ordersn') 
			{
				$condition .= ' and o.ordersn like ' . $keyword;
			}
			else if ($searchfield == 'member') 
			{
				$condition .= ' and ( m.realname like ' . $keyword . ' or m.mobile like ' . $keyword . ')';
			}
			else if ($searchfield == 'address') 
			{
				$condition .= ' and a.realname like ' . $keyword;
			}
			else if ($searchfield == 'merchname') 
			{
				$condition .= ' and u.merchname like ' . $keyword;
			}
		}
		$condition .= ' and o.deleted = 0 ';
		$list = Db::name('shop_order')
			->alias('o')
			->join('member m','o.mid = m.id','left')
			->join('shop_member_address a','a.id = o.addressid','left')
			->join('shop_store s','s.id = o.merchid','left')
			->where($condition)
			->order('o.createtime','desc')
			->group('o.id')
			->paginate($psize);
		foreach ($list as &$row ) 
		{
			$row['ordersn'] = $row['ordersn'] . ' ';
			$row['goods'] = Db::name('shop_order_goods')
				->alias('og')
				->join('shop_goods g','g.id=og.goodsid','left')
				->where('og.orderid',$row['id'])
				->field('g.thumb,og.price,og.total,og.realprice,g.title,og.optionname')
				->select();
			$totalmoney += $row['price'];
		}
		unset($row);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('merch/statistics/order');
	}

	public function merch()
	{
		$psize = 20;
		$condition = ' 1 and o.`status`=3';
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$datetime = input('datetime/a');
		if (!(empty($datetime['start'])) && !(empty($datetime['end']))) 
		{
			$starttime = strtotime($datetime['start']);
			$endtime = strtotime($datetime['end']);
			$condition .= ' AND o.createtime >= ' . $starttime .' AND o.createtime <= ' . $endtime;
		}
		if (!(empty(input('groupname')))) 
		{
			$groupname = input('groupname');
			$condition .= ' and u.groupid= ' . $groupname;
		}
		if (!(empty(input('realname')))) 
		{
			$realname = trim(input('realname'));
			$condition .= ' and ( u.merchname like ' . $realname . ' or u.mobile like ' . $realname . ' or u.realname like ' . $realname . ')';
		}
		$list = Db::name('shop_store')
			->alias('s')
			->join('shop_order o','s.id=o.merchid','left')
			->where($condition)
			->group('s.id')
			->order('s.id','desc')
			->field('s.*,sum(o.price) price,sum(o.goodsprice) goodsprice,sum(o.dispatchprice) dispatchprice,sum(o.discountprice) discountprice,sum(o.deductprice) deductprice,sum(o.deductcredit2) deductcredit2,sum(o.isdiscountprice) isdiscountprice')
			->paginate($psize);
		
		$pager = $list->render();
		$groups = model('store')->getGroups();
		$this->assign(['list'=>$list,'pager'=>$pager,'groups'=>$groups,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('merch/statistics/merch');
	}

	public function set()
	{
		if (Request::instance()->isPost()) 
		{
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data['applycontent'] = model('common')->html_images($data['applycontent']);
			$data['applycashweixin'] = intval($data['applycashweixin']);
			$data['applycashalipay'] = intval($data['applycashalipay']);
			$data['applycashcard'] = intval($data['applycashcard']);
			model('common')->updatePluginset(array('store' => $data));
			model('shop')->plog('merch.set.edit', '修改基本设置');
			show_json(1, array('url' => url('admin/store/set')));
		}
		$data = model('common')->getPluginset('merch');
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function check0()
	{
		$checkdata = $this->checkdata(0);
		return $checkdata;
	}

	public function check1()
	{
		$checkdata = $this->checkdata(-1);
		return $checkdata;
	}

	public function check2()
	{
		$checkdata = $this->checkdata(-1);
		return $checkdata;
	}

	public function check_1()
	{
		$checkdata = $this->checkdata(-1);
		return $checkdata;
	}

	protected function checkdata($type = 0)
	{
		$action_status = $status;
		$applytitle = '';
		if ($status == 1) {
			$applytitle = '待确认';
		} else if ($status == 2) {
			$applytitle = '待打款';
		} else if ($status == 3) {
			$applytitle = '已打款';
		} else if ($status == -1) {
			$action_status = '_1';
			$applytitle = '已无效';
		}
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		$psize = 20;
		$condition = ' 1 ';
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$timetype = input('timetype');
		if (!(empty($timetype))) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			if (!(empty($timetype))) {
				$condition .= ' AND b.' . $timetype . ' >= ' . $starttime . ' AND b.' . $timetype . '  <= :endtime ' . $endtime;
			}
		}
		if (($_GET['status'] !== '') && ($_GET['status'] !== NULL)) 
		{
			$status = intval(input('status'));
		}
		$condition .= ' and b.status=' . (int) $status;
		$searchfield = strtolower(trim(input("searchfield")));
		$keyword = trim(input('keyword'));
		if (!(empty($searchfield)) && !(empty($keyword))) {
			if ($searchfield == 'applyno') {
				$condition .= ' and b.applyno like "%' . $keyword . '%"';
			} else if ($searchfield == 'member') {
				$condition .= ' and ( u.merchname like "%' . $keyword . '%" or u.mobile like "%' . $keyword . '%" or u.realname like "%' . $keyword . '%")';
			}
		}
		$list = Db::name('shop_store_bill')->alias('b')->join('shop_store u','b.merchid = u.id','left')->where($condition)->field('b.*,u.merchname,u.realname,u.mobile')->order('b.id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword,'timetype'=>$timetype,'starttime'=>$starttime,'endtime'=>$endtime,'searchfield'=>$searchfield]);
		return $this->fetch('merch/check/index');
	}

}