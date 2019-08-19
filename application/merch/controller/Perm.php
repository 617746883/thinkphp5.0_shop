<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Perm extends Base
{	
	public function index()
	{
		header('location: ' . url('merch/perm/role'));exit;
	}

	public function role()
	{
		$psize = 20;
		$merch = $this->merch;
		$status = $_POST['status'];
		$condition = ' deleted=0 and merchid=' . $merch['id'];

		if (!empty($_POST['keyword'])) {
			$keyword = trim($_POST['keyword']);
			$condition .= ' and rolename like "%' . $_POST['keyword'] . '%"';
		}

		if ($_POST['status'] != '') {
			$status = $_POST['status'];
			$condition .= ' and status=' . intval($_POST['status']);
		}

		$list = Db::name('shop_merch_perm_role')->where($condition)->order('id desc')->paginate($psize);
		foreach( $list as $key => &$value ) 
		{
			$usercount = Db::name('shop_merch_account')->where('roleid = ' . $value['id'])->count();
			$value["usercount"] = $usercount;
			$data = array();
    		$data = $value;
    		$list->offsetSet($key,$data);
		}
		unset($value);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'keyword'=>$keyword,'status'=>$status]);
		return $this->fetch('perm/role/index');
	}

	public function roleadd()
	{
		$data = $this->rolepost();
		return $data;
	}

	public function roleedit()
	{
		$data = $this->rolepost();
		return $data;
	}

	protected function rolepost()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$item = Db::name('shop_merch_perm_role')->where('id = ' . $id . ' and deleted=0')->find();
		$perms = model('merch')->formatPerms();
		$role_perms = array();
		$user_perms = array();
		if (!empty($item)) {
			$user_perms = $role_perms = explode(',', $item['perms']);
		}

		if (Request::instance()->isPost()) {
			$data = array('merchid' => $merch['id'], 'rolename' => trim($_POST['rolename']), 'status' => intval($_POST['status']), 'perms' => is_array($_POST['perms']) ? implode(',', $_POST['perms']) : '');

			if (!empty($id)) {
				Db::name('shop_merch_perm_role')->where('id = ' . $id . ' and merchid = ' . $merch['id'])->update($data);
				model('shop')->mplog($merch['id'],'perm.role.edit', '修改角色 ID: ' . $id);
			} else {
				$id = Db::name('shop_merch_perm_role')->insertGetId($data);
				model('shop')->mplog($merch['id'],'perm.role.add', '添加角色 ID: ' . $id . ' ');
			}
			show_json(1, array('url' => url('merch/perm/role')));
		}
		
		$this->assign(['item'=>$item,'perms'=>$perms,'role_perms'=>$role_perms,'user_perms'=>$user_perms]);
		return $this->fetch('perm/role/post');
	}

	public function roledelete()
	{
		$id = intval(input('id'));
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_merch_perm_role')->where('id in( ' . $id . ' )')->field('id,rolename')->select();
		foreach ($items as $item) {
			Db::name('shop_merch_perm_role')->where('id = ' . $item['id'])->delete();
			model('shop')->mplog($merch['id'],'perm.role.delete', '删除角色 ID: ' . $item['id'] . ' 角色名称: ' . $item['rolename'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function rolestatus()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_merch_perm_role')->where('id in( ' . $id . ' )')->field('id,rolename')->select();
		foreach ($items as $item) {
			Db::name('shop_merch_perm_role')->where('id = ' . $item['id'])->update(array('status' => $status));
			model('shop')->mplog($merch['id'],'perm.role.edit', '修改角色状态 ID: ' . $item['id'] . ' 角色名称: ' . $item['rolename'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}
		show_json(1, array('url' => referer()));
	}

	public function rolequery()
	{
		$kwd = trim(input('keyword'));
		$merch = $this->merch;
		$condition = ' status=1 and merchid=' . $merch['id'] . ' and deleted=0';
		if (!empty($kwd)) {
			$condition .= ' AND `rolename` LIKE "%' . $kwd . '%"';
		}
		$ds = Db::name('shop_merch_perm_role')->where($condition)->field('id,rolename,perms')->order('id asc')->select();
		$this->assign(['ds'=>$ds]);
		return $this->fetch('perm/role/query');
		exit();
	}

	public function log()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = " log.merchid=".$merch['id'];
		if( !empty($_POST["keyword"]) ) 
		{
			$keyword = trim($_POST["keyword"]);
			$condition .= " and ( log.op like '%" . $keyword . "%' or u.username like '%" . $keyword . "%')";
		}
		if( !empty($_POST["logtype"]) ) 
		{
			$logtype = trim($_POST["logtype"]);
			$condition .= " and log.type= '" . trim($logtype) . "'";
		}
		if( empty($starttime) || empty($endtime) ) 
		{
			$starttime = strtotime("-1 month");
			$endtime = time();
		}
		if( !empty($_POST["searchtime"]) ) 
		{
			$starttime = strtotime($_POST["time"]["start"]);
			$endtime = strtotime($_POST["time"]["end"]);
			if( !empty($timetype) ) 
			{
				$condition .= " AND log.createtime >= " . $starttime . " AND log.createtime <= " . $endtime;
			}
		}
		$list = Db::name('shop_merch_account_log')->alias('log')->join('shop_merch_account u','log.uid = u.id and log.merchid = u.merchid')->field('log.* ,u.username')->where($condition)->order('id desc')->paginate($psize);
		$pager = $list->render();
		// $types = model("merch")->getLogTypes();
		$this->assign(['list'=>$list,'pager'=>$pager,'keyword'=>$keyword,'logtype'=>$logtype,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('perm/log');
	}

	public function user()
	{
		$psize = 20;
		$merch = $this->merch;
		$condition = ' u.merchid = ' . $merch['id'] . ' and u.isfounder<>1';
		if (!empty($_POST['keyword'])) {
			$keyword = trim($_POST['keyword']);
			$condition .= ' and u.username like "%' . $_POST['keyword'] . '%"';
		}

		if ($_POST['roleid'] != '') {
			$roleid = $_POST['roleid'];
			$condition .= ' and u.roleid=' . intval($roleid);
		}

		if ($_POST['status'] != '') {
			$status = $_POST['status'];
			$condition .= ' and u.status=' . intval($status);
		}

		$list = Db::name('shop_merch_account')->alias('u')->join('shop_merch_perm_role r','u.roleid =r.id','left')->field('u.*,r.rolename')->where($condition)->order('id desc')->paginate($psize);
		$pager = $list->render();
		$roles = Db::name('shop_merch_perm_role')->where('deleted=0')->field('id,rolename')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'roles'=>$roles,'keyword'=>$keyword,'roleid'=>$roleid,'status'=>$status]);
		return $this->fetch('perm/user/index');
	}

	public function useradd()
	{
		$data = $this->userpost();
		return $data;
	}

	public function useredit()
	{
		$data = $this->userpost();
		return $data;
	}

	protected function userpost()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		$total = model('merch')->select_operator();
		if ($id) {
			$item = Db::name('shop_merch_account')->alias('u')->join('shop_merch_perm_role r','u.roleid = r.id','left')->where('u.id=' . $id . ' AND u.merchid=' . $merch['id'] . ' AND r.deleted=0 ')->field('u.*,r.rolename,r.merchid')->find();
		}

		if (empty($item)) {
			$merch['accounttotal'] <= $total && $this->error('你最多添加' . $merch['accounttotal'] . '个操作员');
		}

		if (Request::instance()->isPost()) {
			$data = array('username' => trim($_POST['username']), 'pwd' => trim($_POST['password']), 'roleid' => trim($_POST['roleid']), 'status' => trim($_POST['status']), 'isfounder' => 0, 'merchid' => $merch['id'], 'mid' => intval($_POST['mid']));
			if ($id && !empty($item)) {
				if (empty($data['pwd'])) {
					unset($data['pwd']);
				} else {
					$data['salt'] = random(8);
					strlen($data['pwd']) < 6 && show_json(0, '密码至少6位!');
					$data['pwd'] = md5($data['pwd'] . $data['salt']);
				}
				Db::name('shop_merch_account')->where('id = ' . $id . ' and merchid = ' . $merch['id'])->update($data);
				show_json(1);
			}

			$merch['accounttotal'] <= $total && show_json(0, '你最多添加' . $merch['accounttotal'] . '个操作员');
			strlen($data['pwd']) < 6 && show_json(0, '密码至少6位!');
			$data['salt'] = random(8);
			$data['pwd'] = md5($data['pwd'] . $data['salt']);
			$is_has = Db::name('shop_merch_account')->where('username="' . $data['username'] . '" AND merchid=' . $merch['id'])->count();

			if ($is_has) {
				show_json(0, '用户名已存在!');
			}
			Db::name('shop_merch_account')->insert($data);
			show_json(1, array('url' => url('merch/perm/user')));
		}

		if (!empty($item['mid'])) {
			$member = model('mid')->getMember($item['mid']);
		}

		$this->assign(['item'=>$item,'member'=>$member]);
		return $this->fetch('perm/user/post');
	}

	public function userdelete()
	{
		$id = intval(input('id'));
		$merch = $this->merch;
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_merch_account')->where('id in( ' . $id . ' ) AND isfounder=0')->field('id,username')->select();
		foreach ($items as $item) {
			Db::name('shop_merch_account')->where('id',$item)->delete();
			model('shop')->mplog($merch['id'],'perm.user.delete', '删除操作员 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function userstatus()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		$merch = $this->merch;
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_merch_account')->where('id in( ' . $id . ' ) AND isfounder=0')->field('id,username')->select();
		foreach ($items as $item) {
			Db::name('shop_merch_account')->where('id',$item)->update(array('status' => $status));
			model('shop')->mplog($merch['id'],'perm.user.edit', '修改操作员状态 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}
		show_json(1, array('url' => referer()));
	}

}