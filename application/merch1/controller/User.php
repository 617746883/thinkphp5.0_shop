<?php
/**
 * 用户管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class User extends Base
{
	public function index()
	{
		$account = $this->account;
        $merch_info = Db::name('shop_store_account')->where('id',$account['id'])->find();
        if (Request::instance()->isPost()) {
        	$password = trim(input('password/s'));
        	$pwd = trim(input('pwd/s'));
            $repwd = trim(input('repwd/s'));
            if(empty($password)) {
            	show_json(0, '请输入原密码');
            }
            $salt = $merch_info['salt'];
            $oldpwd = md5(trim($password) . $salt);
            if($oldpwd !== $merch_info['pwd']) {
            	show_json(0, '原密码不正确');
            }
            if($pwd !== $repwd) {
            	show_json(0, '两次输入密码不一样');
            }
            if (!empty($pwd)) {
                $pwd = trim($pwd);

                if (strlen($pwd) < 8) {
                    show_json(0, '密码长度至少8位');
                }

                $score = 0;

                if (preg_match('/[0-9]+/', $pwd)) {
                    ++$score;
                }

                if (preg_match('/[a-z]+/', $pwd)) {
                    ++$score;
                }

                if (preg_match('/[A-Z]+/', $pwd)) {
                    ++$score;
                }

                if (preg_match('/[_|\\-|+|=|*|!|@|#|$|%|^|&|(|)]+/', $pwd)) {
                    ++$score;
                }

                if ($score < 2) {
                    show_json(0, '密码必须包含大小写字母、数字、标点符号的其中两项');
                }
            }

            if ($merch_info) {
                $id = $merch_info['id'];
                if (!empty($pwd)) {
                    $pwd = md5(trim(input('pwd')) . $salt);
                    $data['pwd'] = $pwd;
                }

                Db::name('shop_store_account')->where('id',$id)->update($data);
                model('shop')->plog('system.merch.edit', '编辑个人信息 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
            } else {
                show_json(0);
            }

            show_json(1,array('url'=>url('merch/user/index')));
        }
        $this->assign(['merch_info'=>$merch_info,'no_left'=>true]);
        return $this->fetch();
	}

	public function user()
	{
		$account = $this->account;
		$merch = $this->merch;
		$psize = 20;
		$status = input('status');
		$keyword = input('keyword');
		$roleid = input('roleid');
		$condition = ' u.id<>' . $account['id'] . ' and merchid = ' . $merch['id'];

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and u.username like "%' . $keyword . '%"';
		}

		if ($status != '') {
			$condition .= ' and u.status=' . intval($status);
		}

		$list = Db::name('shop_store_account')->alias('u')->where($condition)->order('u.id','desc')->paginate($psize);
		$pager = $list->render();

		$this->assign(['list'=>$list]);
		return $this->fetch();
	}

	public function useradd()
	{
		$userdata = $this->userpost();
		return $userdata;
	}

	public function useredit()
	{
		$userdata = $this->userpost();
		return $userdata;
	}

	protected function userpost()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_store_account')->where('id',$id)->find();
		$account = $this->account;
		$merch = $this->merch;
		if (Request::instance()->isPost()) {
			$data = array('merchid' => $merch['id'], 'username' => trim(input('username')), 'status' => intval(input('status')), 'perms' => serialize(array()), 'isfounder' => 1);
			$password = input('password');
			if (!empty($password)) {
				$password = trim($password);

				if (strlen($password) < 8) {
					show_json(0, '密码长度至少8位');
				}

				$score = 0;

				if (preg_match('/[0-9]+/', $password)) {
					++$score;
				}

				if (preg_match('/[a-z]+/', $password)) {
					++$score;
				}

				if (preg_match('/[A-Z]+/', $password)) {
					++$score;
				}

				if (preg_match('/[_|\\-|+|=|*|!|@|#|$|%|^|&|(|)]+/', $password)) {
					++$score;
				}

				if ($score < 2) {
					show_json(0, '密码必须包含大小写字母、数字、标点符号的其中两项');
				}
			}

			if (!empty($item['id'])) {
				if (!empty($password)) {
					$salt = $item['salt'];
					$password = md5(trim(input('pwd')) . $salt);
					$data['pwd'] = $password;
				}

				Db::name('shop_store_account')->where('id',$item['id'])->update($data);
				model('shop')->plog('system.shop_store_account.edit', '编辑操作员 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
			} else {
				$record = Db::name('shop_store_account')->where('username',$data['username'])->find();
				
				if ($record) {
					show_json(0, '此用户名为系统存在用户');
				} else {
					$salt = random(8);
					while (1) 
					{
						$saltcount = Db::name('shop_store_account')->where('salt',$salt)->count();
						if ($saltcount <= 0) 
						{
							break;
						}
						$salt = random(8);
					}
					$password = md5(config('AUTH_CODE').trim($password) . $salt);
					$data['salt'] = $salt;
					$data['pwd'] = $password;
					$id = Db::name('shop_store_account')->insertGetId($data);
				}
				model('shop')->plog('system.shop_store_account.add', '添加操作员 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
			}

			show_json(1,array('url'=>url('merch/user/useredit',array('id'=>$id))));
		}
		$this->assign(['item'=>$item]);
		return $this->fetch('user/post');
	}

	public function userdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_store_account')->where('id','in',$id)->field('id,username')->select();

		foreach ($items as $item) {
			Db::name('shop_store_account')->where('id',$item['id'])->delete();
			model('shop')->plog('system.shop_store_account.delete', '删除操作员 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function userstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('shop_store_account')->where('id','in',$id)->field('id,username')->select();

		foreach ($items as $item) {
			Db::name('shop_store_account')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('system.shop_store_account.edit', '修改操作员状态 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}

		show_json(1, array('url' => referer()));
	}

	public function loginout()
	{
		session_unset();
        session_destroy();
		$this->success('退出成功!',url('merch/login/index'));
	}

}
