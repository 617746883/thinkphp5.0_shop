<?php
/**
 * 后台系统设置
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
use think\Cache;
use think\File;
use think\Log;
class System extends Base
{
	public function index()
	{
		header('location: ' . url('admin/system/admin'));exit;
	}

	public function admin()
	{
		$admin = session('admin');
		$psize = 20;
		$status = input('status');
		$keyword = input('keyword');
		$roleid = input('roleid');
		$condition = ' 1 ';
		$condition = ' u.id<>' . $admin['id'];

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and u.username like "%' . $keyword . '%"';
		}

		if ($roleid != '') {
			$condition .= ' and u.roleid=' . intval($roleid);
		}

		if ($status != '') {
			$condition .= ' and u.status=' . intval($status);
		}

		$list = Db::name('admin')->alias('u')->where($condition)->order('u.id','desc')->paginate($psize);
		$pager = $list->render();

		$this->assign(['list'=>$list]);
		return $this->fetch('system/admin/index');
	}

	public function adminadd()
	{
		$admindata = $this->adminpost();
		return $admindata;
	}

	public function adminedit()
	{
		$admindata = $this->adminpost();
		return $admindata;
	}

	protected function adminpost()
	{
		$id = intval(input('id'));
		$item = Db::name('admin')->where('id',$id)->find();
		$admin = session('?admin');
		$group_data = array();
		$auth_group = array();
		if (!empty($item)) {
			if ($item['id'] == $admin['id']) {
				$this->error('无法修改自己的权限！');
			}
			$group_data = Db::name('auth_group_access')->where('uid', $item['id'])->column('group_id');
        	$auth_group = Db::name('auth_group')->select();
		}

		if (Request::instance()->isPost()) {
			$group_ids = input('group_ids/a');
			$data = array('username' => trim(input('username')), 'avatar' => trim(input('avatar')), 'status' => intval(input('status')), 'remark' => trim(input('remark')));
			$usertime = $_POST['usertime'];
			$data['starttime'] = strtotime($usertime['start']);
			$data['endtime'] = strtotime($usertime['end']);
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
					$password = md5(config('AUTH_CODE').trim($password) . $salt);
					$data['password'] = $password;
				}

				Db::name('admin')->where('id',$item['id'])->update($data);
				if(!empty($group_ids)) {
                    Db::name('auth_group_access')->where('uid', $id)->delete();
                    foreach ($group_ids as $k => $v) {
                        $group=array('uid'=>(int) $id,'group_id'=>(int) $v);
                        Db::name('auth_group_access')->insert($group);
                    }
                }
				model('shop')->plog('system.admin.edit', '编辑操作员 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
			} else {
				$record = Db::name('admin')->where('username',$data['username'])->find();
				
				if ($record) {
					show_json(0, '此用户为系统存在用户');
				} else {
					$salt = random(8);
					while (1) 
					{
						$saltcount = Db::name('admin')->where('salt',$salt)->count();
						if ($saltcount <= 0) 
						{
							break;
						}
						$salt = random(8);
					}
					$password = md5(config('AUTH_CODE').trim($password) . $salt);
					$data['salt'] = $salt;
					$data['password'] = $password;
					$id = Db::name('admin')->insertGetId($data);
				}
				if(!empty($id) && !empty($group_ids)) {
                    Db::name('auth_group_access')->where('uid', $id)->delete();
                    foreach ($group_ids as $k => $v) {
                        $group=array('uid'=>(int) $id,'group_id'=>(int) $v);
                        Db::name('auth_group_access')->insert($group);
                    }
                }
				model('shop')->plog('system.admin.add', '添加操作员 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
			}

			show_json(1,array('url'=>url('admin/system/adminedit',array('id'=>$id))));
		}
		$this->assign(['item'=>$item,'group_data'=>$group_data,'auth_group'=>$auth_group]);
		return $this->fetch('system/admin/post');
	}

	public function admindelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('admin')->where('id','in',$id)->field('id,username')->select();

		foreach ($items as $item) {
			Db::name('admin')->where('id',$item['id'])->delete();
			model('shop')->plog('system.admin.delete', '删除操作员 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function adminstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('admin')->where('id','in',$id)->field('id,username')->select();

		foreach ($items as $item) {
			Db::name('admin')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('system.admin.edit', '修改操作员状态 ID: ' . $item['id'] . ' 操作员名称: ' . $item['username'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}

		show_json(1, array('url' => referer()));
	}

	public function rolequery()
	{
		$kwd = trim(input('keyword'));
		$condition = ' 1 ';

		if (!empty($kwd)) {
			$condition .= ' AND `title` LIKE "%' . $kwd . '%"';
		}

		$ds = Db::name('auth_group')->where($condition)->select();
		$this->assign('list',$list);
		return $this->fetch('system/role/query');
	}

	public function plog()
	{
		$psize = 20;
		$condition = ' 1 ';
		$keyword = input('keyword');
		$logtype = input('logtype');
		$searchtime = input('searchtime');

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and ( log.op like "%' . $keyword . '%" or u.username like "%' . $keyword . '%")';
		}

		if (!empty($logtype)) {
			$condition .= ' and log.type= ' . $logtype;
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($searchtime)) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);

			if (!empty($timetype)) {
				$condition .= ' AND log.createtime >= ' . $starttime . ' AND log.createtime <= ' . $endtime;
			}
		}

		$list = Db::name('admin_log')->alias('log')->join('admin u','log.adminid = u.id','left')->where($condition)->field('log.*,u.username')->order('id','desc')->paginate($psize);
		/*foreach ($list as $k => $row) {
			$row['name'] = ((isset($res_group[$row['groupid']]) ? $res_group[$row['groupid']]['groupname'] : ''));
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}		
		unset($row);*/
		$pager = $list->render();

		$this->assign(['list'=>$list,'pager'=>$pager]);
		return $this->fetch('system/plog/index');
	}

	public function copyrightweb()
	{
		$copyrights = Db::name('shop_system_copyright')->where('ismanage',0)->find();

		if (Request::instance()->isPost()) {
			$data = array('bgcolor'=>input('bgcolor'),'ismanage'=>0,'title'=>'','agreement'=>input('agreement/s',''));
			$data['copyright'] = model('common')->html_images($_POST['copyright']);
			if(empty($copyrights)) {
				$id = Db::name('shop_system_copyright')->insertGetId($data);
			} else {
				Db::name('shop_system_copyright')->where('id',$copyrights['id'])->update($data);
			}
			show_json(1);
		}

		$this->assign(['copyrights'=>$copyrights]);
		return $this->fetch('system/copyright/web');
	}

	public function copyrightmanage()
	{
		$copyrights = Db::name('shop_system_copyright')->where('ismanage',1)->find();

		if (Request::instance()->isPost()) {
			$data = array('bgcolor'=>'','ismanage'=>1,'title'=>input('title'),'logo'=>input('logo'));			
			$data['copyright'] = model('common')->html_images($_POST['copyright']);
			if(empty($copyrights)) {
				$id = Db::name('shop_system_copyright')->insertGetId($data);
			} else {
				Db::name('shop_system_copyright')->where('id',$copyrights['id'])->update($data);
			}
			show_json(1);
		}

		$this->assign(['copyrights'=>$copyrights]);
		return $this->fetch('system/copyright/manage');
	}

	public function role()
	{
		// 所有权限
        $data=Db::name('auth_rule')->select();
        // 获取树形或者结构数据
        vendor('tree.Tree');  
        $tree = new \Tree();     
        $rules=$tree->trees($data,'title','id','pid');
        $this->assign(['rules'=>$rules]);
		return $this->fetch('system/auth/role');
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

	public function rolepost()
	{	
        $id = input('id/d');
        $pid = input('pid/d');
        $item = Db::name('auth_rule')->where('id',$id)->find();
        if(!empty($item['pid'])) {
            $pid = $item['pid'];
        }
        $parent = Db::name('auth_rule')->where('id',$pid)->where('status',1)->find();
        $parents = Db::name('auth_rule')->where('status',1)->select();
		if(Request::instance()->isPost())
        {
            $data = array('title' => trim(input('post.title')), 'pid' => input('post.pid'),  'name' => trim(input('post.name')), 'status' => intval(input('post.status')));
            if(empty($item)) {
                $id = Db::name('auth_rule')->insertGetId($data);
            } else {
                Db::name('auth_rule')->where('id',$id)->update($data);
            }

            show_json(1,array('url'=>url('admin/system/roleedit',array('id'=>$id))));
        }
        
        $this->assign([
            'item' => $item,
            'parent' => $parent,
            'parents' => $parents
        ]);
		return $this->fetch('system/auth/rolepost');
	}

	public function rolestatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('auth_rule')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('auth_rule')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('perm.role.edit', '修改权限状态 ID: ' . $item['id'] . ' 角色名称: ' . $item['title'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}

		show_json(1, array('url' => referer()));
	}

	public function roledelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('auth_rule')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('auth_rule')->where('id',$item['id'])->delete();
			model('shop')->plog('perm.role.delete', '删除权限 ID: ' . $item['id'] . ' 角色名称: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function perm()
	{
		$status = input('status');
		$keyword = input('keyword');
		$condition = ' 1 ';
		if($status != '') {
			$condition .= ' and status = ' . $status;
		}
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and title like "%' . $keyword . '%"';
		}
		$list = Db::name('auth_group')->where($condition)->paginate(30);
		foreach ($list as $k => $row) {
			$row['usercount'] = Db::name('auth_group_access')->where('group_id',$row['id'])->count();
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}		
		unset($row);
        $pager = $list->render();
        $this->assign(['list'=>$list,'status'=>$status,'keyword'=>$keyword]);
		return $this->fetch('system/auth/perm');
	}

	public function permadd()
	{
		$data = $this->permpost();
		return $data;
	}

	public function permedit()
	{
		$data = $this->permpost();
		return $data;
	}

	protected function permpost()
	{
		$id = intval(input('id'));
		$item = Db::name('auth_group')->where('id',$id)->find();
		if(!empty($item)) {
			$item['rules']=explode(',', $item['rules']);  
		}
		
		if (Request::instance()->isPost()) {			
            $rules=implode(',', $_POST['rule_ids']);
			if(empty(input('title')) || empty($rules)) {
                show_json(0,'数据不能为空！');
            }
            $data = array('title' => trim(input('title')), 'status' => intval(input('status')),'rules'=>$rules);
            if(empty($item))
            {
                $id = Db::name('auth_group')->insertGetId($data);
            } else {
                Db::name('auth_group')->where('id',$item['id'])->update($data);
            }

			show_json(1,array('url' => url('admin/system/permedit',array('id'=>$id))));
		}

		vendor('tree.Tree');  
        $tree = new \Tree(); 
        $data=Db::name('auth_rule')->where('status', 1)->select();    
        $perms = $tree->channelLevel($data,0,'&nbsp;','id','pid',1);
		$this->assign(['item' => $item, 'perms' => $perms]);
		return $this->fetch('system/auth/permpost');
	}

	public function permdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('auth_group')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('auth_group')->where('id',$item['id'])->delete();
			model('shop')->plog('perm.role.delete', '删除角色 ID: ' . $item['id'] . ' 角色名称: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function permstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('auth_group')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('auth_group')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('perm.role.edit', '修改角色状态 ID: ' . $item['id'] . ' 角色名称: ' . $item['title'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}

		show_json(1, array('url' => referer()));
	}

	public function loginout()
	{
        $admin = session('admin');
        Db::name('member')->where('id',$admin_info['id'])->setField('token','');
		session_unset();
        session_destroy();
		$this->success('退出成功!',url('admin/login/index'));
	}

	public function feedback()
	{
		$psize = 20;
		$status = input('status');
		$keyword = input('keyword');
		$condition = ' 1 ';

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and f.desc like "%' . $keyword . '%"';
		}
		if ($status != '') {
			$condition .= ' and f.status=' . intval($status);
		}

		$list = Db::name('system_feedback')->alias('f')->join('member m','m.id = f.mid','left')->where($condition)->field('f.*,m.realname,m.mobileverify,m.mobile,m.isblack,m.avatar,m.nickname')->order('f.createtime','desc')->paginate($psize);
		$pager = $list->render();

		$this->assign(['list'=>$list,'pager'=>$pager]);
		return $this->fetch('system/feedback/index');
	}

	public function feedbackdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('system_feedback')->where('id','in',$id)->field('id,desc')->select();

		foreach ($items as $item) {
			Db::name('system_feedback')->where('id',$item['id'])->delete();
			model('shop')->plog('perm.role.delete', '删除意见反馈 ID: ' . $item['id'] . ' 意见反馈: ' . $item['desc'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function feedbackstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('system_feedback')->where('id','in',$id)->field('id,desc')->select();

		foreach ($items as $item) {
			Db::name('system_feedback')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('perm.role.edit', '修改意见反馈状态 ID: ' . $item['id'] . ' 意见反馈: ' . $item['desc'] . ' 状态: ' . ($status == 0 ? '禁用' : '启用'));
		}

		show_json(1, array('url' => referer()));
	}

	public function feedbackdetail()
	{
		$id = intval(input('id'));
		$item = Db::name('system_feedback')->alias('f')->join('member m','m.id = f.mid','left')->where('f.id','eq',$id)->field('f.*,m.realname,m.mobileverify,m.mobile,m.isblack,m.avatar,m.nickname')->find();
		$piclist = array();
		if (!(empty($item['thumbs_url']))) {
			$piclist = iunserializer($item['thumbs_url']);
		}
		$this->assign(['item'=>$item,'piclist'=>$piclist]);
		return $this->fetch('system/feedback/detail');
	}

	public function updatecache()
	{
		if (Request::instance()->isPost()) {			
            $cache = input('cache/a');
            if(empty($cache)) {
            	show_json(0,'请选择缓存类型');
            }
            if(isset($cache['tpl']) && !empty($cache['tpl'])) {
            	delete_dir_file(CACHE_PATH);
            	delete_dir_file(TEMP_PATH);
            }
            if(isset($cache['data']) && !empty($cache['data'])) {
            	Log::clear();
            }
            model('shop')->plog('system.updatecache', '更新缓存');
			show_json(1,array('url' => url('admin/system/updatecache')));
		}
		return $this->fetch('system/updatecache/index');
	}

	public function database()
	{
		$do = input('do');
		$dos = array('backup', 'restore', 'optimize');
		$do = in_array($do, $dos) ? $do : '';
		$optimize_table = array();
		$tables = Db::query("show table status");
		foreach ($tables as $tableinfo) {
			if ($tableinfo['Engine'] == 'InnoDB') {
				continue;
			}
			if (!empty($tableinfo) && !empty($tableinfo['Data_free'])) {
				$row = array(
					'title' => $tableinfo['Name'],
					'type' => $tableinfo['Engine'],
					'rows' => $tableinfo['Rows'],
					'data' => sizecount($tableinfo['Data_length']),
					'index' => sizecount($tableinfo['Index_length']),
					'free' => sizecount($tableinfo['Data_free'])
				);
				$optimize_table[$row['title']] = $row;
			}
		}

		$reduction = $this->system_database_backup();

		if ($do == 'backup') {
			$title = '备份 - 数据库 - 常用系统工具 - 系统管理';
			$shopset = model('common')->getSysset();
	        if($shopset['shop']['close'] != 1) {
	            show_json(0,'为了保证备份数据完整请关闭商城后再进行此操作');
	        }

			$tables = Db::query("show table status");
			if (empty($tables)) {
				show_json(0,'数据已经备份完成');
			}
			$series = 1;
			$volume_suffix = random(10);

			$folder_suffix = time() . '_' . random(8);
			$bakdir = ROOT_PATH . '/public/data/backup/' . $folder_suffix;
			$result = mkdirs($bakdir);
			$size = 300;
			$volumn = 1024 * 1024 * 2;
			$dump = '';
			$last_table ='';
			$catch = true;
			if(!$result) {
				show_json(0,'目录权限不可写入');
			}

			// vendor('Backup.Backup');
			// $config=array(
			//     'path'     => $bakdir,//数据库备份路径
			//     'part'     => 20971520,//数据库备份卷大小
			//     'compress' => 0,//数据库备份文件是否启用压缩 0不压缩 1 压缩
			//     'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
			// );
   // 			$backup= new \Backup($config);
			
			foreach ($tables as $table) {
				$table = array_shift($table);
				if (!empty($last_table) && $table == $last_table) {
					$catch = true;
				}
				if (!$catch) { 
					continue;
				}
				if (!empty($dump)) {
					$dump .= "\n\n";
				}
				if ($table != $last_table) {
					$row = $this->db_table_schemas($table);
					$dump .= $row;
				}
				$index = 0;
				while (true) {
					$start = $index * $size;
					$result = $this->db_table_insert_sql($table, $start, $size);
					if (!empty($result)) {
						$dump .= $result['data'];
						if (strlen($dump) > $volumn) {
							$bakfile = $bakdir . "/volume-{$volume_suffix}-{$series}.sql";
							$dump .= "\n\n";
							file_put_contents($bakfile, $dump);
							$series++;
							$index++;
							$current = array(
								'last_table' => $table,
								'index' => $index,
								'series' => $series,
								'volume_suffix'=>$volume_suffix,
								'folder_suffix'=>$folder_suffix,
								'status'=>1
							);
							$current_series = $series-1;
						}						
					}
					
					if (empty($result) || count($result['result']) < $size) {
						break;
					}
					$index++;
				}
			}
			$bakfile = $bakdir . "/volume-{$volume_suffix}-{$series}.sql";
			$dump .= "\n\n----SUL1SS MySQL Dump End";
			file_put_contents($bakfile, $dump);
			model('shop')->plog('system.database.backup', '备份数据库，备份文件名：' . $folder_suffix);
			show_json(1,array('url' => url('admin/system/database',array('tab'=>'tab_backup'))));
		} elseif ($do == 'restore') {
			$title = '还原 - 数据库 - 常用系统工具 - 系统管理';
			$restore_dirname = input('restore_dirname');
			$delete_dirname = input('delete_dirname');
			if (!empty($restore_dirname)) {
				$restore_dirname = $restore_dirname;
				$restore_dirname_list = array_keys($reduction);
				if (!in_array($restore_dirname, $restore_dirname_list)) {
					show_json(0,'非法访问');
					exit;
				} 
				
				$volume_list = $reduction[$restore_dirname]['volume_list'];
				$restore_volume_name = $volume_list[0];
				$restore_volume_sizes = 1;
				if ($reduction[$restore_dirname]['volume'] < $restore_volume_sizes) {
					show_json(1,'成功恢复数据备份');
					exit;
				} 
				$volume_sizes = $restore_volume_sizes;
				system_database_volume_restore($restore_volume_name);
				$next_restore_volume_name = system_database_volume_next($restore_volume_name);
				$restore_volume_sizes ++;
				$restore = array (
						'restore_volume_name' => $next_restore_volume_name,
						'restore_volume_sizes' => $restore_volume_sizes,
						'restore_dirname' => $restore_dirname
				);
				message('正在恢复数据备份, 请不要关闭浏览器, 当前第 ' . $volume_sizes . ' 卷.', url('admin/system/database/restore',$restore), 'success');
			}
			if ($delete_dirname) {
				if(!empty($reduction[$delete_dirname]) && $this->system_database_backup_delete($delete_dirname)) {
					model('shop')->plog('system.database.restore', '删除备份成功,' . $delete_dirname);
					show_json(1,array('url' => url('admin/system/database',array('tab'=>'tab_restore'))));
				}
			}
		} elseif ($do == 'optimize') {
			$select = input('select/a');
			foreach ($select as $tablename) {
				if (!empty($optimize_table[$tablename])) {
					Db::query("OPTIMIZE TABLE {$tablename}");
				}
			}
			model('shop')->plog('system.database.optimize', '数据库优化');
			show_json(1,array('url' => url('admin/system/database',array('tab'=>'tab_optimize'))));
		}
		
		$this->assign(['optimize_table'=>$optimize_table,'reduction'=>$reduction,'tab'=>$tab]);
		return $this->fetch('system/database/index');
	}

	protected function db_table_schemas($table) {
		$dump = "DROP TABLE IF EXISTS {$table};\n";
		$sql = "SHOW CREATE TABLE {$table}";
		$row = Db::query($sql);
		$dump .= $row['Create Table'];
		$dump .= ";\n\n";
		return $dump;
	}

	protected function db_table_insert_sql($tablename, $start, $size) {
		$data = '';
		$tmp = '';
		$sql = "SELECT * FROM {$tablename} LIMIT {$start}, {$size}";
		$result = Db::query($sql);
		if (!empty($result)) {
			foreach($result as $row) {
				$tmp .= '(';
				foreach($row as $k => $v) {
					$value = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $v);
					$tmp .= "'" . $value . "',";
				}
				$tmp = rtrim($tmp, ',');
				$tmp .= "),\n";
			}
			$tmp = rtrim($tmp, ",\n");
			$data .= "INSERT INTO {$tablename} VALUES \n{$tmp};\n";
			$datas = array (
					'data' => $data,
					'result' => $result
			);
			return $datas;
		} else {
			return false ;
		}
	}

	protected function system_database_backup() {
		$path = ROOT_PATH . '/public/data/backup/';
		$reduction = array();
		if (!is_dir($path)) {
			return array();
		}
		if ($handle = opendir($path)) {
			while (false !== ($bakdir = readdir($handle))) {
				if ($bakdir == '.' || $bakdir == '..') {
					continue;
				}
				$times[] = date("Y-m-d H:i:s", filemtime($path.$bakdir));
				if (preg_match('/^(?P<time>\d{10})_[a-z\d]{8}$/i', $bakdir, $match)) {
					$time = $match['time'];
					if ($handle1= opendir($path . $bakdir)) {
						while (false !== ($filename = readdir($handle1))) {
							if ($filename == '.' || $filename == '..') {
								continue;
							}
							if (preg_match('/^volume-(?P<prefix>[a-z\d]{10})-\d{1,}\.sql$/i', $filename, $match1)) {
								$volume_prefix = $match1['prefix'];
								if (!empty($volume_prefix)) {
									break;
								}
							}
						}
					}
					$volume_list = array();
					for ($i = 1;;) {
						$last = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
						array_push($volume_list, $last);
						$i++;
						$next = $path . $bakdir . "/volume-{$volume_prefix}-{$i}.sql";
						if (!is_file($next)) {
							break;
						}
					}
					if (is_file($last)) {
						$fp = fopen($last, 'r');
						fseek($fp, -27, SEEK_END);
						$end = fgets($fp);
						fclose($fp);
						$row = array(
							'bakdir' => $bakdir,
							'time' => $time,
							'volume' => $i - 1,
							'volume_list' => $volume_list,
						);
						$reduction[$bakdir] = $row;
						continue;
					}
				}
				// rmdirs($path . $bakdir);
			}
			closedir($handle);
		}
		if (!empty($times)) {
			array_multisort($times, SORT_DESC, SORT_STRING, $reduction);
		}
		return $reduction;
	}

	protected function system_database_backup_delete($delete_dirname) {
		$path = ROOT_PATH . '/data/backup/';
		$dir = $path . $delete_dirname;
		if (empty($delete_dirname) || !is_dir($dir)) {
			return false;
		}
		return rmdirs($dir);
	}

	protected function system_database_volume_restore($volume_name) {
		if (empty($volume_name) || !is_file($volume_name)) {
			return false;
		}
		$sql = file_get_contents($volume_name);
		Db::query($sql);
		return true;
	}

	protected function system_database_volume_next($volume_name) {
		$next_volume_name = '';
		if (!empty($volume_name) && preg_match('/^([^\s]*volume-(?P<prefix>[a-z\d]{10})-)(\d{1,})\.sql$/i', $volume_name, $match)) {
			$next_volume_name = $match[1] . ($match[3] + 1) . ".sql";
		}
		return $next_volume_name;
	}

	public function push()
	{
		$psize = 20;
		$condition = ' 1 ';
		$keyword = trim(input('keyword'));
		if (!empty($keyword)) {
			$condition .= ' and mm.title like "%' . $keyword . '%"';
		}

		$list = Db::name('member_message')->alias('mm')->join('member m','m.id = mm.mid','left')->where($condition)->field('mm.*,m.nickname')->order('mm.id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager]);
		return $this->fetch('system/push/index');
	}

	public function pushsend()
	{
		if (Request::instance()->isPost()) {
			$class1 = input('send1');
			$title = input('title');
			$subtitle = input('subtitle');
			$link = input('link');
			if(empty($title)) {
				show_json(0,'请填写标题');
			}
			if ($class1 == 1) {
				$send_mid = input('send_mid');
				$mids = explode(',', $send_mid);
				$plog = '推送消息 ，方式: 指定 ID 人数: ' . count($mids);
			} else if ($class1 == 2) {
				$where = ' 1 ';
				$send_level = input('send_level');
				if (!empty($send_level)) {
					$where .= ' and level =' . intval($send_level);
				}

				$members = Db::name('member')->where($where)->column('id');

				if (!empty($send_level)) {
					$levelname = Db::name('member_level')->where('id',$send_level)->value('levelname');
				} else {
					$levelname = '全部';
				}

				$mids = $members;
				$plog = '推送消息 ，方式: 等级-' . $levelname . ' 人数: ' . count($members);
			} else if ($class1 == 3) {
				$where = ' 1 ';
				$send_group = input('send_group');
				if (!empty($send_group)) {
					$where .= ' and groupid =' . intval($send_group);
				}

				$members = Db::name('member')->where($where)->column('id');

				if (!empty($send_group)) {
					$groupname = Db::name('member_group')->where('id',$send_group)->value('groupname');
				} else {
					$groupname = '全部分组';
				}

				$mids = $members;
				$plog = '推送消息 方式: 分组-' . $groupname . ' 人数: ' . count($members);
			} else if ($class1 == 4) {
				$where = '';
				$members = Db::name('member')->where('status = 1 and isblack = 0')->column('id');
				$mids = $members;
				$plog = '推送消息 方式: 全部会员 人数: ' . count($members);
			}
			foreach ($mids as $mid) {
				$mmids[] = $mid;
			}

			if (empty($mmids)) {
				show_json(0, '未找到发送的会员!');
			}
			$members = Db::name('member')->where('id','in',$mmids)->field('id,nickname')->select();
			if (empty($members)) {
				show_json(0, '未找到发送的会员!!');
			}
			$msg = trim($title);
			$text = trim($subtitle);

			$datas = array(
				array('name' => '商城名称', 'value' => $shopset['shop']['name'])
				);
			foreach ($members as $m) {
				$datas[] = array('name' => '粉丝名称', 'value' => $n['nickname']);
				model('notice')->sendNotice(array('mid' => $m['id'], 'first' => $msg, 'default' => $text, 'datas' => $datas, 'type' => 'system', 'tid' => 0, 'id' => 0));
			}
			show_json(1, array('url' => url('admin/system/push')));	
		}
		$level = Db::name('member_level')->where('enabled', 1)->select();
        $group = Db::name('member_group')->select();
        $this->assign(['list'=>$level,'list2'=>$group]);
		return $this->fetch('system/push/send');
	}
	
}