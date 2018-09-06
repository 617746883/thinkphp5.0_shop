<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Db;
class User extends Base
{
    public function index()
    {        
        $admin = session('admin');
        $admin_info = Db::name('admin')->where('id',$admin['id'])->find();
        if (Request::instance()->isPost()) {
            $data = array('username' => trim(input('username')), 'avatar' => trim(input('avatar')));
            $password = input('password/s');
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

            if ($admin_info) {
                $id = $admin_info['id'];
                if (!empty($password)) {
                    $salt = $item['salt'];
                    $password = md5(config('AUTH_CODE').trim($password) . $salt);
                    $data['password'] = $password;
                }

                Db::name('admin')->where('id',$id)->update($data);
                model('shop')->plog('system.admin.edit', '编辑个人信息 ID: ' . $id . ' 用户名: ' . $data['username'] . ' ');
            } else {
                show_json(0);
            }

            show_json(1,array('url'=>url('admin/user/index')));
        }
        $this->assign(['admin_info'=>$admin_info]);
        return $this->fetch();
    }

    public function password()
    {
        return $this->fetch();
    }

}