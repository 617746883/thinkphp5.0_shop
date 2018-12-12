<?php
namespace app\merch\model;
use think\Db;
use think\Request;
class Shop extends \think\Model
{
	public static function mplog($merchid = 0, $type = '', $op = '')
    {
		$account = session('account');
        $accountid = $account['id'];

		$log = array('uid' => $accountid, 'name' => $type, 'type' => $type, 'op' => $op, 'ip' => request()->ip(), 'createtime' => time(), 'merchid' => $merchid);
		Db::name('shop_merch_account_log')->insert($log);
		return;
    }
}