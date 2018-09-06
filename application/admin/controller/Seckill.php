<?php
/**
 * 拍卖
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Seckill extends Base
{
	public function index()
	{
		header('location: ' . url('admin/seckill/task'));exit;
		return $this->fetch('');
	}
}	