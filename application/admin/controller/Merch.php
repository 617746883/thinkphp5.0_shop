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

	public function ajaxmerch()
	{
		$totals = model('merch')->getMerchTotals();
		$order0 = model('merch')->getMerchOrderTotals(0);
		$order3 = model('merch')->getMerchOrderTotals(3);
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

	protected function regdata($status = '')
	{
		$groups = model('merch')->getGroups();
		$psize = 20;
		$condition = ' 1 ';
		$keyword = trim($_GET['keyword']);

		if (!empty($keyword)) {
			$condition .= ' and ( merchname like "%' . $keyword . '%" or realname like "%' . $keyword . '%" or mobile like "%' . $keyword . '%")';
		}

		if ($status !== '') {
			$condition .= ' and status=' . intval($status);
		}
		$list = Db::name('shop_merch_reg')->where($condition)->order('applytime desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword,'groups'=>$groups]);
		return $this->fetch('merch/reg/index');
	}

	public function regdetail()
	{
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$id = intval(input('id'));
		$item = Db::name('shop_merch_reg')->where('id = ' . $id)->find();

		if (empty($item)) {
			$this->error('未找到商户入驻申请!');
		}

		$member = model('member')->getMember($item['mid']);
		if (Request::instance()->isPost()) {
			$status = intval($_POST['status']);
			$reason = trim($_POST['reason']);

			if ($status == -1) {
				if (empty($reason)) {
					show_json(0, '请填写驳回理由.');
				}
			} else {
				model('merch')->checkMaxMerchUser();
			}

			$item['status'] = $status;
			$item['reason'] = $reason;
			$item['merchname'] = trim($_POST['merchname']);
			$item['salecate'] = trim($_POST['salecate']);
			$item['desc'] = trim($_POST['desc']);
			$item['realname'] = trim($_POST['realname']);
			$item['mobile'] = trim($_POST['mobile']);
			Db::name('shop_merch_reg')->where('id',$item['id'])->update($item);

			if ($status == 1) {
				$usercount = Db::name('shop_merch')->where('regid = ' . $item['id'])->find();
				if (empty($usercount)) {
					$user = $item;
					unset($user['id']);
					unset($user['reason']);
					$user['regid'] = $item['id'];
					$user['status'] = 0;
					$userid = Db::name('shop_merch')->insertGetId($user);
					Db::name('shop_merch_reg')->where('id',$item['id'])->update($item);
					show_json(1, array('message' => '允许入驻成功，请编辑商户账户资料!', 'url' => url('admin/merch/edit', array('id' => $userid))));
				} else {
					$user = $item;
					unset($user['id']);
					unset($user['reason']);
					$user['status'] = 0;
					Db::name('shop_merch')->where('regid = ' . $item['id'])->update($user);
					Db::name('shop_merch_reg')->where('id',$item['id'])->update($item);
					show_json(1, array('message' => '允许入驻成功，请编辑商户账户资料!', 'url' => url('admin/merch/edit', array('id' => $usercount['id']))));
				}
				model('notice')->sendMerchMessage();
			} else {
				if ($status == -1) {
					Db::name('shop_merch_reg')->where('id',$item['id'])->update($item);
					model('notice')->sendMerchMessage();
				}
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
		$regs = Db::name('shop_merch_reg')->where('id','in',$id)->field('id,merchname')->select();
		foreach ($regs as $reg ) 
		{
			Db::name('shop_merch_reg')->where('id',$res['id'])->delete();
			model('shop')->plog('merch.reg.delete', '删除入驻申请 店鋪名称:  ' . $reg['merchname']);
		}
		show_json(1, array('url' => referer()));
	}

	public function user0()
	{
		$userdata = $this->userdata(0);
		return $userdata;
	}

	public function user1()
	{
		$userdata = $this->userdata(1);
		return $userdata;
	}

	public function user2()
	{
		$userdata = $this->userdata(2);
		return $userdata;
	}

	public function user3()
	{
		$userdata = $this->userdata(3);
		return $userdata;
	}

	protected function userdata($status = '')
	{		
		$psize = 20;
        $condition = " 1 ";
        $keyword = trim($_GET["keyword"]);
        $groupid = $_GET["groupid"];
        if( !empty($keyword) ) {
            $condition .= " and ( u.merchname like '%" . $keyword . "%' or u.realname like '%" . $keyword . "%' or u.mobile like '%" . $keyword . "%')";
        }

        if( $groupid != "" ) 
        {
            $condition .= " and u.groupid=" . intval($groupid);
        }

        if( $status !== "" ) 
        {
            $status = intval($status);
            if( $status == 3 ) 
            {
                $condition .= " and u.status=1 and TIMESTAMPDIFF(DAY,now(),FROM_UNIXTIME(u.accounttime))<=30 ";
            }
            else
            {
                $condition .= " and u.status=" . $status;
            }

        }

        if( $status == "0" ) 
        {
            $sortfield = "u.applytime";
        }
        else
        {
            $sortfield = "u.jointime";
        }

        $list = Db::name('shop_merch')->alias('u')->join('shop_merch_group g','u.groupid = g.id','left')->where($condition)->field('u.*,g.groupname')->order($sortfield,'desc')->paginate($psize);
		$pager = $list->render();
		$groups = model('merch')->getGroups();
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
		$id = intval(input('id/d'));
        $area_set = model("util")->get_area_config_set();
        $new_area = intval($area_set["new_area"]);
        if( empty($id) ) {
            $max_flag = model('merch')->checkMaxMerchUser(1);
            if( $max_flag == 1 ) {
                $this->error("已经达到最大商户数量,不能再添加商户");
            }
        }

        $item = Db::name('shop_merch')->where('id',$id)->find();
        if( empty($item) ) {
            $item["iscredit"] = 1;
            $item["iscreditmoney"] = 1;
        }

        if( !empty($item["mid"]) ) 
        {
            $member = model("member")->getMember($item["mid"]);
        }

        if( !empty($item["paymid"]) ) 
        {
            $user = model("member")->getMember($item["paymid"]);
        }

        if( empty($item) || empty($item["accounttime"]) ) 
        {
            $accounttime = strtotime("+365 day");
        }
        else
        {
            $accounttime = $item["accounttime"];
        }

        if( !empty($item["accountid"]) ) 
        {
            $account = Db::name('shop_merch_account')->where('id',$item['accountid'])->find();
        }

        if( !empty($item["pluginset"]) ) 
        {
            $item["pluginset"] = iunserializer($item["pluginset"]);
        }

        if( empty($account) ) 
        {
            $show_name = $item["uname"];
            $show_pass = model("util")->pwd_encrypt($item["upass"], "D");
        }
        else
        {
            $show_name = $account["username"];
        }

        if(Request::instance()->isPost()) 
        {
            $status = intval($_POST["status"]);
            $username = trim($_POST["username"]);
            $checkUser = false;
            if( 0 < $status ) 
            {
                $checkUser = true;
            }

            if( empty($_POST["groupid"]) ) 
            {
                show_json(0, "请选择商户组!");
            }

            if( empty($_POST["cateid"]) ) 
            {
                show_json(0, "请选择商户分类!");
            }

            if( $checkUser ) 
            {
                if( empty($username) ) 
                {
                    show_json(0, "请填写账户名!");
                }

                if( empty($account) && empty($_POST["pwd"]) ) 
                {
                    show_json(0, "请填写账户密码!");
                }

                $where = " username= '" . $username . "'";
                if( !empty($account) ) 
                {
                    $where .= " and id<> " . $account["id"];
                }

                $usercount = Db::name('shop_merch_account')->where($where)->count();
                if( 0 < $usercount ) 
                {
                    show_json(0, "账户名 " . $username . " 已经存在!");
                }

                if( !empty($account) && empty($account["pwd"]) && empty($_POST["pwd"]) ) 
                {
                    show_json(0, "请填写账户密码!");
                }

            }

            $where = " username= '" . $username . "'";
            if( !empty($account) ) 
            {
                $where .= " and id <> " . $account["id"];
            }

            $usercount = Db::name('shop_merch_account')->where($where)->count();
            if( 0 < $usercount ) 
            {
                show_json(0, "账户名 " . $username . " 已经存在!");
            }

            $salt = "";
            $pwd = "";
            if( empty($account) || empty($account["salt"]) || !empty($_POST["pwd"]) ) 
            {
                $salt = random(8);
                while( 1 ) 
                {
                    $saltcount = Db::name('shop_merch_account')->where('salt',$salt)->count();
                    if( $saltcount <= 0 ) 
                    {
                        break;
                    }

                    $salt = random(8);
                }
                $pwd = md5(trim($_POST["pwd"]) . $salt);
            }
            else
            {
                $salt = $account["salt"];
                $pwd = $account["pwd"];
            }

            if( $_POST["iscreditmoney"] == 0 && $_POST["creditrate"] == 0 ) 
            {
                show_json(0, "开启积分提现，比例不能为0");
            }

            if( $_POST["iscreditmoney"] == 1 ) 
            {
                $_POST["creditrate"] = 0;
            }

            $data = array("merchname" => trim($_POST["merchname"]), "salecate" => trim($_POST["salecate"]), "realname" => trim($_POST["realname"]), "mobile" => trim($_POST["mobile"]), "address" => trim($_POST["address"]), "tel" => trim($_POST["tel"]), "lng" => $_POST["map"]["lng"], "lat" => $_POST["map"]["lat"], "accounttime" => strtotime($_POST["accounttime"]), "accounttotal" => intval($_POST["accounttotal"]), "maxgoods" => intval($_POST["maxgoods"]), "groupid" => intval($_POST["groupid"]), "cateid" => intval($_POST["cateid"]), "isrecommand" => intval($_POST["isrecommand"]), "remark" => trim($_POST["remark"]), "status" => $status, "desc" => trim($_POST["desc1"]), "logo" => trim($_POST["logo"]), "paymid" => intval($_POST["paymid"]), "payrate" => trim($_POST["payrate"], "%"), "pluginset" => iserializer($_POST["pluginset"]), "creditrate" => intval($_POST["creditrate"]), "iscredit" => intval($_POST["iscredit"]), "iscreditmoney" => intval($_POST["iscreditmoney"]) );

            if( empty($item["jointime"]) && $status == 1 ) 
            {
                $data["jointime"] = time();
            }

            $account = array( "merchid" => $id, "username" => $username, "pwd" => $pwd, "salt" => $salt, "status" => 1, "perms" => serialize(array(  )), "isfounder" => 1 );
            $item = Db::name('shop_merch')->where('id = ' . $id)->find();
            if( empty($item) ) 
            {
                $item["applytime"] = time();
                $id = Db::name('shop_merch')->insertGetId($data);
                $account["merchid"] = $id;
                $accountid = Db::name('shop_merch_account')->insertGetId($account);
                Db::name('shop_merch')->where('id',$id)->update(array( "accountid" => $accountid ));
                model('shop')->plog("merch.user.add", "添加商户 ID: " . $data["id"] . " 商户名: " . $data["merchname"] . "<br/>帐号: " . $data["username"] . "<br/>子帐号数: " . $data["accounttotal"] . "<br/>到期时间: " . date("Y-m-d", $data["accounttime"]));
            }
            else
            {
                Db::name('shop_merch')->where('id',$id)->update($data);
                if( !empty($item["accountid"]) ) 
                {
                    Db::name('shop_merch_account')->where('id',$item["accountid"])->update($account);
                } else {
                    $accountid = Db::name('shop_merch_account')->insertGetId($account);
                    Db::name('shop_merch')->where('id',$id)->update(array( "accountid" => $accountid ));
                }

                model('shop')->plog("merch.user.edit", "编辑商户 ID: " . $data["id"] . " 商户名: " . $item["merchname"] . " -> " . $data["merchname"] . "<br/>帐号: " . $item["username"] . " -> " . $data["username"] . "<br/>子帐号数: " . $item["accounttotal"] . " -> " . $data["accounttotal"] . "<br/>到期时间: " . date("Y-m-d", $item["accounttime"]) . " -> " . date("Y-m-d", $data["accounttime"]));
            }

            show_json(1, array( "url" => url("admin/merch/user{$data["status"]}") ));
        }

        $groups = model('merch')->getGroups();
        $category = model('merch')->getCategory();
		
		$this->assign(['item'=>$item,'new_area'=>$new_area,'address_street'=>$address_street,'groups'=>$groups,'category'=>$category,'accounttime'=>$accounttime,'member'=>$member,'user'=>$user,'account'=>$account]);
		return $this->fetch('merch/post');
	}

	public function delete()
	{
		$id = input('id/d');

		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_merch')->where('id','in',$id)->field('id,merchname')->select();

		foreach ($items as $item) {
			Db::name('shop_merch')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('merch.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['merchname'] . ' ');
		}
		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		$id = input('id/d');
		$displayorder = input('value/d');
		$item = Db::name('shop_merch')->where('id',$id)->field('id,merchname')->find();

		if (!empty($item)) {
			Db::name('shop_merch')->where('id',$id)->setField('displayorder',$displayorder);
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
		$items = Db::name('shop_merch')->where('id','in',$id)->field('id,merchname')->select();

		foreach ($items as $item) {
			Db::name('shop_merch')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('merch.edit', ('修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['merchname'] . '<br/>状态: ' . $status) == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function goods()
	{
		$sql = 'SELECT *  FROM ' . tablename('shop_newstore_goodsgroup') . ' WHERE  uniacid=:uniacid  ORDER BY id DESC ';
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
			$condition .= ' and EXISTS(select id from ' . tablename('shop_newstore_goodsgroup_goods') . ' gg where  gg.goodsgroupid=:goodsgroupid and gg.goodsid = ng.goodsid) ';
			$params[':goodsgroupid'] = $goodsgroupid;
		}

		$sql = 'SELECT ng.*,g.title,g.thumb,g.hasoption,g.type  FROM ' . tablename('shop_newstore_goods') . "  ng\r\n        INNER JOIN " . tablename('shop_goods') . "  g ON ng.goodsid = g.id\r\n        WHERE   1 and " . $condition . ' ORDER BY ng.id DESC ';
		$sql .= ' LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize;
		$list = pdo_fetchall($sql, $params);
		$total = pdo_fetchcolumn('SELECT count(*)  FROM ' . tablename('shop_newstore_goods') . "  ng\r\n        INNER JOIN " . tablename('shop_goods') . "  g ON ng.goodsid = g.id\r\n        WHERE   1 and " . $condition . ' ORDER BY ng.id DESC ', $params);
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
		$list = Db::name('shop_merch_group')->where($condition)->order('isdefault desc, id DESC')->paginate($psize);
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
		if (Request::instance()->isPost()) {
			$data = array('groupname' => trim($_POST['groupname']), 'status' => intval($_POST['status']), 'isdefault' => intval($_POST['isdefault']), 'goodschecked' => intval($_POST['goodschecked']), 'commissionchecked' => intval($_POST['commissionchecked']), 'changepricechecked' => intval($_POST['changepricechecked']), 'finishchecked' => intval($_POST['finishchecked']));
			if ($data['isdefault'] == 1) {
				Db::name('shop_merch_group')->where('isdefault',1)->setField('isdefault',0);
			}
			if (!(empty($id))) 
			{
				Db::name('shop_merch_group')->where('id',$id)->update($data);
				model('shop')->plog('merch.group.edit', '修改店鋪分组 ID: ' . $id);
			}
			else 
			{
				$data['createtime'] = time();
				$id = Db::name('shop_merch_group')->insertGetId($data);
				model('shop')->plog('store.group.add', '添加店鋪分组 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/merch/group')));
		}
		$item = Db::name('shop_merch_group')->where('id',$id)->find();
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
		$items = Db::name("shop_merch_group")->where('id','in',$id)->field('id,groupname')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_merch_group')->where('id',$item['id'])->delete();
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
		$items = Db::name("shop_merch_group")->where('id','in',$id)->field('id,groupname')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_merch_group')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->plog('merch.group.edit', (('修改店鋪分组状态<br/>ID: ' . $item['id'] . '<br/>分组名称: ' . $item['groupname'] . '<br/>状态: ' . input('status/d')) == 1 ? '显示' : '隐藏'));
		}
		show_json(1, array('url' => referer()));
	}

	public function groupsetdefault() 
	{
		$id = input('id/d');
		$isdefault = input('isdefault/d');
		$group = Db::name('shop_merch_group')->where('id',$id)->find();
		if (empty($group)) 
		{
			show_json(0, '抱歉，店鋪分组不存在或是已经被删除！');
		}
		if($isdefault == 1)
		{
			Db::name('shop_merch_group')->where('isdefault',1)->setField('isdefault',0);
			Db::name('shop_merch_group')->where('id',$group['id'])->setField('isdefault',1);
		}
		else
		{
			Db::name('shop_merch_group')->where('id',$group['id'])->setField('isdefault',0);
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
		$list = Db::name('shop_merch_category')->where($condition)->order('createtime','desc')->paginate($psize);
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
				Db::name('shop_merch_category')->where('id',$id)->update($data);
				model('shop')->plog('merch.category.edit', '修改店鋪分类 ID: ' . $id);
			}
			else 
			{
				$data['createtime'] = time();
				$id = Db::name('shop_merch_category')->insertGetId($data);
				model('shop')->plog('merch.category.add', '添加店鋪分类 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/merch/category')));
		}
		$item = Db::name('shop_merch_category')->where('id',$id)->find();
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
		$items = Db::name('shop_merch_category')->where('id','in',$id)->field('id,catename')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_merch_category')->where('id',$item['id'])->delete();
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
		$items = Db::name('shop_merch_category')->where('id','in',$id)->field('id,catename')->select();
		foreach ($items as $item ) 
		{
			Db::name('shop_merch_category')->where('id',$item['id'])->setField('status',input('status/d'));
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
			->join('shop_merch s','s.id = o.merchid','left')
			->where($condition)
			->field('o.*, a.realname as addressname,m.realname,s.merchname')
			->order('o.createtime','desc')
			->group('o.id')
			->paginate($psize);
		foreach ($list as $k => $row) 
		{
			$row['ordersn'] = $row['ordersn'] . ' ';
			$row['goods'] = Db::name('shop_order_goods')
				->alias('og')
				->join('shop_goods g','g.id=og.goodsid','left')
				->where('og.orderid',$row['id'])
				->field('g.thumb,og.price,og.total,og.realprice,g.title,og.optionname')
				->select();
			$totalmoney += $row['price'];
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}
		unset($row);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'starttime'=>$starttime,'endtime'=>$endtime]);
		return $this->fetch('merch/statistics/order');
	}

	public function merch()
	{
		$psize = 20;
		$condition = ' o.`status`=3';
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['datetime']['start']) && !empty($_GET['datetime']['end'])) {
			$starttime = strtotime($_GET['datetime']['start']);
			$endtime = strtotime($_GET['datetime']['end']);
			$condition .= ' AND o.createtime >= ' . $starttime . ' AND o.createtime <= ' . $endtime;
		}

		$groupname = intval($_GET['groupname']);
		if (!empty($_GET['groupname'])) {
			$condition .= ' and u.groupid=' . $groupname;
		}

		$realname = trim($_GET['realname']);
		if (!empty($_GET['realname'])) {
			$condition .= ' and ( u.merchname like "%' . $_GET['realname'] . '%" or u.mobile like "%' . $_GET['realname'] . '%" or u.realname like "%' . $_GET['realname'] . '%")';
		}

		$list = Db::name('shop_merch')->alias('u')->join('shop_order o','u.id=o.merchid','left')->field('u.*,sum(o.price) price,sum(o.goodsprice) goodsprice,sum(o.dispatchprice) dispatchprice,sum(o.discountprice) discountprice,sum(o.deductprice) deductprice,sum(o.deductcredit2) deductcredit2,sum(o.isdiscountprice) isdiscountprice')->where($condition)->group('u.id')->order('u.id DESC')->paginate($psize);
		$pager = $list->render();
		$groups = model('merch')->getGroups();
		$this->assign(['list'=>$list,'pager'=>$pager,'groups'=>$groups,'starttime'=>$starttime,'endtime'=>$endtime,'groupname'=>$groupname,'realname'=>$realname]);
		return $this->fetch('merch/statistics/merch');
	}

	public function query()
	{
		$kwd = trim(input('keyword'));

		$condition = ' status=1 and deleted = 0 ';

		if (!empty($kwd)) {
			$condition .= ' AND `merchname` LIKE "%' . $kwd . '%"';
		}

		$ds = Db::name('shop_merch')->where($condition)->order('id','asc')->field('id,merchname')->select();

		if (input('suggest')) {
			exit(json_encode(array('value' => $ds)));
		}
		$this->assign(['ds'=>$ds]);
		echo $this->fetch('');
		exit();
	}

	public function check1()
	{
		$checkdata = $this->checkdata(1);
		return $checkdata;
	}

	public function check2()
	{
		$checkdata = $this->checkdata(2);
		return $checkdata;
	}

	public function check3()
	{
		$checkdata = $this->checkdata(3);
		return $checkdata;
	}

	public function check_1()
	{
		$checkdata = $this->checkdata(-1);
		return $checkdata;
	}

	protected function checkdata($status = 1)
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
		$condition = ' 1 and (creditstatus = 2 OR creditstatus = 0) ';
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
		$list = Db::name('shop_merch_bill')->alias('b')->join('shop_merch u','b.merchid = u.id','left')->where($condition)->field('b.*,u.merchname,u.realname,u.mobile')->order('b.id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'applytitle'=>$applytitle,'status'=>$status,'keyword'=>$keyword,'timetype'=>$timetype,'starttime'=>$starttime,'endtime'=>$endtime,'searchfield'=>$searchfield]);
		return $this->fetch('merch/check/index');
	}

	public function checkdetail()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		$item = model('merch')->getOneApply($id);
		if( empty($item["applytype"]) ) 
		{
			$merch_user = Db::name('shop_merch')->where('id = ' . intval($item['merchid']))->find();
			if( !empty($merch_user["paymid"]) ) {
				$member = model("member")->getMember($merch_user["paymid"]);
			}
		}
		$apply_type = array( "微信钱包", 2 => "支付宝", 3 => "银行卡" );
		if( $status == 1 ) 
		{
			$is_check = 1;
		}
		if( $status <= 1 ) 
		{
			$orderids = iunserializer($item["orderids"]);
		}
		else 
		{
			$orderids = iunserializer($item["passorderids"]);
		}
		$keyword = trim(input('keyword'));
		$list = array( );
		foreach( $orderids as $key => $orderid ) 
		{
			$data = model('merch')->getMerchPriceList($item["merchid"], $orderid, 10, $id);
			if( !empty($data) ) 
			{
				$flag = 1;
				if( !empty($keyword) ) 
				{
					if( strpos(trim($data["ordersn"]), $keyword) !== false ) 
					{
						$flag = 1;
					}
					else 
					{
						$flag = 0;
					}
				}
				if( $flag ) 
				{
					$list[] = $data;
				}
			}
		}
		if( $_GET["export"] == "1" ) 
		{
			foreach( $list as &$row ) 
			{
				$row["finishtime"] = date("Y-m-d H:i", $row["finishtime"]);
			}
			$columns = array( );
			$columns[] = array( "title" => "订单编号", "field" => "ordersn", "width" => 24 );
			$columns[] = array( "title" => "可提现金额", "field" => "realprice", "width" => 24 );
			$columns[] = array( "title" => "抽成比例", "field" => "payrate", "width" => 12 );
			$columns[] = array( "title" => "抽成后获得金额", "field" => "realpricerate", "width" => 24 );
			$columns[] = array( "title" => "订单完成时间", "field" => "finishtime", "width" => 24 );
			$columns[] = array( "title" => "订单商品总额", "field" => "goodsprice", "width" => 24 );
			$columns[] = array( "title" => "快递金额", "field" => "dispatchprice", "width" => 24 );
			$columns[] = array( "title" => "积分抵扣金额", "field" => "deductprice", "width" => 24 );
			$columns[] = array( "title" => "余额抵扣金额", "field" => "deductcredit2", "width" => 24 );
			$columns[] = array( "title" => "会员折扣金额", "field" => "discountprice", "width" => 24 );
			$columns[] = array( "title" => "促销金额", "field" => "isdiscountprice", "width" => 24 );
			$columns[] = array( "title" => "满减金额", "field" => "deductenough", "width" => 24 );
			$columns[] = array( "title" => "实际支付金额", "field" => "price", "width" => 24 );
			$columns[] = array( "title" => "商户满减金额", "field" => "merchdeductenough", "width" => 24 );
			$columns[] = array( "title" => "商户优惠券金额", "field" => "merchcouponprice", "width" => 24 );
			$columns[] = array( "title" => "分销佣金", "field" => "commission", "width" => 24 );
			model("excel")->export($list, array( "title" => "提现申请订单数据-" . date("Y-m-d-H-i", time()), "columns" => $columns ));
		}
		$this->assign(['id'=>$id,'status'=>$status,'apply_type'=>$apply_type,'item'=>$item,'keyword'=>$keyword,'list'=>$list]);
		return $this->fetch('merch/check/detail');
	}

	public function checkmerchpay() 
	{
		$id = intval(input('id'));
		$handpay = intval(input('handpay'));
		$finalprice = floatval(input('finalprice'));
		if( empty($id) ) 
		{
			show_json(0, "参数错误!");
		}
		if( $finalprice <= 0 ) 
		{
			show_json(0, "打款金额错误!");
		}
		$item = model('merch')->getOneApply($id);
		if( empty($item) ) 
		{
			show_json(0, "未找到提现申请!");
		}
		$payprice = $finalprice * 100;
		if( empty($handpay) && empty($item["applytype"]) ) 
		{
			$merch_user = Db::name('shop_merch')->where('id = ' . intval($item['merchid']))->find();
			if( empty($merch_user["paymid"]) ) 
			{
				show_json(0, "请先设置商户结算收款人!");
			}
			$result = model("payment")->wechat_pay($merch_user["paymid"], 1, $payprice, $item["applyno"], "商户提现申请打款");
			if( is_error($result) ) 
			{
				show_json(0, $result["message"]);
			}
		}
		if( empty($handpay) && $item["applytype"] == 2 ) 
		{
			$sec = model("common")->getSec();
			$sec = iunserializer($sec["sec"]);
			if( !empty($sec["alipay_pay"]["open"]) ) 
			{
				if( empty($sec["alipay_pay"]["sign_type"]) ) 
				{
					show_json(0, "支付宝仅支持单笔转账打款!");
				}
				$billminey = $finalprice * 100;
				$batch_no = "D" . date("Ymdhis") . "RW" . $item["id"] . "MERCH" . $billminey;
				$single_res = model("payment")->singleAliPay(array( "account" => $item["alipay"], "name" => $item["applyrealname"], "money" => $finalprice ), $batch_no, $sec["alipay_pay"], "商户提现申请打款");
				if( $single_res["errno"] == "-1" ) 
				{
					show_json(0, $single_res["message"]);
				}
				$order_id = $single_res["order_id"];
				$query_res = model("payment")->querySingleAliPay($sec["alipay_pay"], $order_id, $batch_no);
				if( $query_res["errno"] == "-1" ) 
				{
					show_json(0, $query_res["message"]);
				}
			}
			else 
			{
				show_json(0, "未开启,支付宝打款!");
			}
		}
		$change_data = array( );
		$change_data["paytime"] = time();
		$change_data["status"] = 3;
		$change_data["finalprice"] = $finalprice;
		$change_data["handpay"] = $handpay;
		Db::name('shop_merch_bill')->where('id = ' . intval($id))->update($change_data);
		$orderids = iunserializer($item["passorderids"]);
		foreach( $orderids as $key => $orderid ) 
		{
			Db::name('shop_order')->where('id = ' . intval($orderid))->update(array( "merchapply" => 3 ));
		}
		show_json(1);
	}

	public function checkconfirm() 
	{
		$id = intval(input('id'));
		$bpid = input('bpid');
		$type = intval(input('type'));
		if( empty($bpid) ) 
		{
			if( $type == 1 ) 
			{
				show_json(0, "参数错误!");
			}
		}
		else 
		{
			$bpid = array_unique($bpid);
		}
		$item = model('merch')->getOneApply($id);
		if( empty($item) ) 
		{
			show_json(0, "未找到提现申请!");
		}
		$orderids = iunserializer($item["orderids"]);
		$orderids = array_unique($orderids);
		if( empty($orderids) ) 
		{
			show_json(0, "参数错误!");
		}
		if( $type == 1 ) 
		{
			$change_data = array( );
			$change_data["checktime"] = time();
			$change_data["status"] = 2;
			$pass_data = model('merch')->getPassApplyPrice($item["merchid"], $bpid, $id);
			$change_data["passrealprice"] = $pass_data["realprice"];
			$change_data["passrealpricerate"] = $pass_data["realpricerate"];
			$change_data["passorderprice"] = $pass_data["orderprice"];
			$change_data["passorderids"] = iserializer($bpid);
			$change_data["passordernum"] = count($bpid);
			Db::name('shop_merch_bill')->where('id',$id)->update($change_data);
			foreach( $orderids as $key => $orderid ) 
			{
				if( in_array($orderid, $bpid) ) 
				{
					Db::name('shop_order')->where('id',$orderid)->update(array( "merchapply" => 2 ));
				}
				else 
				{
					Db::name('shop_order')->where('id',$orderid)->update(array( "merchapply" => -1 ));
				}
			}
		}
		else 
		{
			if( $type == -1 ) 
			{
				$change_data = array( );
				$change_data["invalidtime"] = time();
				$change_data["status"] = -1;
				Db::name('shop_merch_bill')->where('id',$id)->update($change_data);
				foreach( $orderids as $key => $orderid ) 
				{
					Db::name('shop_order')->where('id',$orderid)->update(array( "merchapply" => -1 ));
				}
			}
		}
		show_json(1);
	}

	public function store()
	{
		$psize = 20;
		$condition = ' merchid = 0 ';
		$keyword = trim($_GET['keyword']);
		$type = intval($_GET['type']);
		if (!empty($keyword)) {
			$condition .= ' AND (storename LIKE \'%' . $keyword . '%\' OR address LIKE \'%' . $keyword . '%\' OR tel LIKE \'%' . $keyword . '%\')';
		}

		if (!empty($type)) {
			$condition .= ' AND type = ' . $type;
		}

		$list = Db::name('shop_store')->where($condition)->order('displayorder desc,id desc')->paginate($psize);

		if(!empty($list)) {
			foreach ($list as $k => $value) { 
				$row['salercount'] = Db::name('shop_saler')->where('storeid = ' . $value['id'])->count();
				$data = array();
	    		$data = $value;
	    		$list->offsetSet($k,$data);
			}
		}
		unset($value);
		$this->assign(['list'=>$list,'pager'=>$pager,'type'=>$type,'keyword'=>$keyword]);
		return $this->fetch('merch/store/index');
	}

	public function storeadd()
	{
		$storedata = $this->storepost();
		return $storedata;
	}

	public function storeedit()
	{
		$storedata = $this->storepost();
		return $storedata;
	}

	protected function storepost()
	{
		$id = intval(input('id'));
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$address_street = intval($area_set['address_street']);

		if (Request::instance()->isPost()) {
			if (!empty($_POST['perms'])) {
				$perms = implode(',', $_POST['perms']);
			} else {
				$perms = '';
			}

			if (empty($_POST['logo'])) {
				show_json(0, '门店LOGO不能为空');
			}

			if (empty($_POST['map']['lng']) || empty($_POST['map']['lat'])) {
				show_json(0, '门店位置不能为空');
			}

			if (empty($_POST['address'])) {
				show_json(0, '门店地址不能为空');
			} else {
				if (30 < mb_strlen($_POST['address'], 'UTF-8')) {
					show_json(0, '门店地址不能超过30个字符');
				}
			}

			$label = '';

			if (!empty($_POST['lab'])) {
				if (8 < count($_POST['lab'])) {
					show_json(0, '标签不能超过8个');
				}

				foreach ($_POST['lab'] as $lab) {
					if (20 < mb_strlen($lab, 'UTF-8')) {
						show_json(0, '标签长度不能超过20个字符');
					}

					if (strlen(trim($lab)) <= 0) {
						show_json(0, '标签不能为空');
					}
				}
				$label = implode(',', $_POST['lab']);
			}

			$tag = '';

			if (!empty($_POST['tag'])) {
				if (3 < count($_POST['tag'])) {
					show_json(0, '角标不能超过3个');
				}

				foreach ($_POST['tag'] as $tg) {
					if (3 < mb_strlen($tg, 'UTF-8')) {
						show_json(0, '角标长度不能超过3个字符');
					}

					if (strlen(trim($tg)) <= 0) {
						show_json(0, '角标不能为空');
					}
				}

				$tag = implode(',', $_POST['tag']);
			}

			$cates = '';

			if (!empty($_POST['cates'])) {
				if (3 < count($_POST['cates'])) {
					show_json(0, '门店分类不能超过3个');
				}
				$cates = implode(',', $_POST['cates']);
			}

			if (empty($_POST['tel']) || strlen(trim($_POST['tel'])) <= 0) {
				show_json(0, '门店电话不能为空');
			} else {
				if (20 < strlen($_POST['tel'])) {
					show_json(0, '门店电话不能大于20个字符');
				}
			}

			if (!empty($_POST['saletime'])) {
				if (20 < strlen($_POST['saletime'])) {
					show_json(0, '营业时间不能大于20个字符');
				}
			}

			$data = array('storename' => trim($_POST['storename']), 'address' => trim($_POST['address']), 'province' => trim($_POST['province']), 'city' => trim($_POST['city']), 'area' => trim($_POST['area']), 'provincecode' => trim($_POST['chose_province_code']), 'citycode' => trim($_POST['chose_city_code']), 'areacode' => trim($_POST['chose_area_code']), 'tel' => trim($_POST['tel']), 'lng' => $_POST['map']['lng'], 'lat' => $_POST['map']['lat'], 'type' => intval($_POST['type']), 'realname' => trim($_POST['realname']), 'mobile' => trim($_POST['mobile']), 'label' => $label, 'tag' => $tag, 'fetchtime' => trim($_POST['fetchtime']), 'saletime' => trim($_POST['saletime']), 'logo' => trim($_POST['logo']), 'desc' => trim($_POST['desc']), 'opensend' => intval($_POST['opensend']), 'status' => intval($_POST['status']), 'cates' => $cates, 'perms' => $perms);

			$data['order_printer'] = is_array($_POST['order_printer']) ? implode(',', $_POST['order_printer']) : '';
			$data['order_template'] = intval($_POST['order_template']);
			$data['ordertype'] = is_array($_POST['ordertype']) ? implode(',', $_POST['ordertype']) : '';

			if (!empty($id)) {
				Db::name('shop_store')->where('id',$id)->update($data);
				model('shop')->plog('shop.verify.store.edit', '编辑门店 ID: ' . $id);
			} else {
				$id = Db::name('shop_store')->insertGetId($data);
				model('shop')->plog('shop.verify.store.add', '添加门店 ID: ' . $id);
			}
			show_json(1, array('url' => url('admin/merch/store')));
		}

		$item = Db::name('shop_store')->where('id',$id)->find();
		$perms = explode(',', $item['perms']);

		$label = explode(',', $item['label']);
		$tag = explode(',', $item['tag']);
		$cates = explode(',', $item['cates']);
		$this->assign(['item'=>$item,'perms'=>$perms,'label'=>$label,'tag'=>$tag,'cates'=>$cates,'new_area'=>$new_area,'address_street'=>$address_street]);
		return $this->fetch('merch/store/post');
	}

	public function storedelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_store')->where('id in( ' . $id . ' )')->field('id,storename')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id = ' . $item['id'])->delete();
			model('shop')->plog('shop.verify.store.delete', '删除门店 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function storedisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_store')->where('id = ' . $id)->field('id,storename')->find();
		if (!empty($item)) {
			Db::name('shop_store')->where('id = ' . $id)->update(array('displayorder' => $displayorder));
			model('shop')->plog('shop.verify.store.edit', '修改门店排序 ID: ' . $item['id'] . ' 门店名称: ' . $item['storename'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function storestatus()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_store')->where('id in( ' . $id . ' )')->field('id,storename')->select();

		foreach ($items as $item) {
			Db::name('shop_store')->where('id = ' . $item['id'])->update(array('status' => intval($status)));
			model('shop')->plog('shop.verify.store.edit', '修改门店状态<br/>ID: ' . $item['id'] . '<br/>门店名称: ' . $item['storename'] . '<br/>状态: ' . $status == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function storequery()
	{
		$kwd = trim($_POST['keyword']);
		$limittype = empty($_POST['limittype']) ? 0 : intval($_POST['limittype']);
		$condition = ' status=1 ';

		if ($limittype == 0) {
			$condition .= '  and type in (1,2,3) ';
		}

		if (!empty($kwd)) {
			$condition .= ' AND `storename` LIKE "%' . $kwd . '%"';
		}

		$ds = Db::name('shop_store')->where($condition)->field('id,storename')->order('id asc')->select();
		if ($_POST['suggest']) {
			exit(json_encode(array('value' => $ds)));
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('merch/store/query');
		exit();
	}

	public function saler()
	{
		$condition = ' s.merchid = 0 ';
		$status = input('status');
		$keyword = input('keyword');
		if ($status != '') {
			$condition .= ' and s.status = ' . $status;
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and ( s.salername like "%' . $keyword . '%" or m.realname like "%' . $keyword . '%" or m.mobile like "%' . $keyword . '%" or m.nickname like "%' . $keyword . '%")';
		}

		$list = Db::name('shop_saler')->alias('s')->join('member m','s.mid=m.id','left')->join('shop_store store','store.id=s.storeid','left')->where($condition)->field('s.*,m.nickname,m.avatar,m.realname,store.storename')->order('s.id asc')->paginate(20);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('merch/saler/index');
	}

	public function saleradd()
	{
		$data = $this->salerpost();
		return $data;
	}

	public function saleredit()
	{
		$data = $this->salerpost();
		return $data;
	}

	protected function salerpost()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_saler')->where('id = ' . $id)->find();
		$saler = array();
		if (!empty($item)) {
			$saler = model('member')->getMember($item['mid']);
			$store = Db::name('shop_store')->where('id = ' . $item['storeid'])->find();
		}

		if (Request::instance()->isPost()) {
			$data = array('storeid' => intval($_POST['storeid']), 'mid' => trim($_POST['mid']), 'status' => intval($_POST['status']), 'salername' => trim($_POST['salername']), 'mobile' => trim($_POST['mobile']), 'roleid' => intval($_POST['roleid']));

			if (empty($data['storeid'])) {
				show_json(0, '请选择所属门店');
			}

			if (empty($item['username'])) {
				if (empty($_POST['username'])) {
					show_json(0, '用户名不能为空!');
				}

				$usernames = Db::name('shop_saler')->where('username',$_POST['username'])->count();

				if (0 < $usernames) {
					show_json(0, '该用户名已被使用，请修改后重新提交!');
				}

				$data['username'] = $_POST['username'];
			}

			if (!empty($_POST['pwd'])) {
				$salt = random(8);
				while (1) {
					$saltcount = Db::name('shop_saler')->where('salt',$salt)->count();
					if ($saltcount <= 0) {
						break;
					}

					$salt = random(8);
				}

				$pwd = md5(trim($_POST['pwd']) . $salt);
				$data['pwd'] = $pwd;
				$data['salt'] = $salt;
			} else {
				if (empty($item)) {
					show_json(0, '用户密码不能为空!');
				}
			}

			$m = model('member')->getMember($data['mid']);

			if (!empty($id)) {
				Db::name('shop_saler')->where('id',$id)->update($data);
				model('shop')->plog('shop.verify.saler.edit', '编辑店员 ID: ' . $id . ' <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['mid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
			} else {
				$scount = Db::name('shop_saler')->where('mid',$data['mid'])->count();

				if (0 < $scount) {
					show_json(0, '此会员已经成为店员，没法重复添加');
				}

				$id = Db::name('shop_saler')->insertGetId($data);
				model('shop')->plog('shop.verify.saler.add', '添加店员 ID: ' . $id . '  <br/>店员信息: ID: ' . $m['id'] . ' / ' . $m['mid'] . '/' . $m['nickname'] . '/' . $m['realname'] . '/' . $m['mobile'] . ' ');
			}

			show_json(1, array('url' => url('admin/merch/saler')));
		}
		$stores = Db::name('shop_store')->where('status = 1 and merchid = 0')->field('id,storename')->order('id asc')->select();
		$this->assign(['item'=>$item,'saler'=>$saler,'stores'=>$stores]);
		return $this->fetch('merch/saler/post');
	}

	public function salerdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_saler')->where('id in( ' . $id . ' )')->field('id,salername')->select();
		foreach ($items as $item) {
			Db::name('shop_saler')->where('id',$item['id'])->delete();
			model('shop')->plog('shop.verify.saler.delete', '删除店员 ID: ' . $item['id'] . ' 店员名称: ' . $item['salername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function salerstatus()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));

		if (empty($id)) {
			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
		}

		$items = Db::name('shop_saler')->where('id in( ' . $id . ' )')->field('id,salername')->select();
		foreach ($items as $item) {
			Db::name('shop_saler')->where('id',$item['id'])->update(array('status' => intval($status)));
			model('shop')->plog('shop.verify.saler.edit', '修改店员状态<br/>ID: ' . $item['id'] . '<br/>店员名称: ' . $item['salername'] . '<br/>状态: ' . $status == 1 ? '启用' : '禁用');
		}

		show_json(1, array('url' => referer()));
	}

	public function salerquery()
	{
		$kwd = trim($_POST['keyword']);
		$condition = ' 1 ';

		if (!empty($kwd)) {
			$condition .= ' AND ( m.nickname LIKE "%' . $kwd . '%" or m.realname LIKE "%' . $kwd . '%" or m.mobile LIKE "%' . $kwd . '%" or store.storename like "%' . $kwd . '%" )';
		}

		$ds = Db::name('shop_saler')->alias('s')->join('member m','s.mid=m.mid','left')->join('shop_store store','store.id=s.storeid','left')->where($condition)->field('s.*,m.nickname,m.avatar,m.mobile,m.realname,store.storename')->order('id asc')->select();
		$this->assign(['ds'=>$ds]);
		return $this->fetch('merch/saler/query');
		exit();
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
			model('common')->updatePluginset(array('merch' => $data));
			model('shop')->plog('merch.set.edit', '修改基本设置');
			show_json(1, array('url' => url('admin/merch/set')));
		}
		$url = getHttpHost()  . url('merch/index/index', true);
		$data = model('common')->getPluginset('merch');
		$this->assign(['data'=>$data,'url'=>$url]);
		return $this->fetch('');
	}

}