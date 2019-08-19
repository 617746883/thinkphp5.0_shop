<?php
/**
 * 应用管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Plugins extends Base
{
	public function index()
	{
		return $this->fetch('');
	}
}