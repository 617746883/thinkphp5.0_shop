<?php
/**
 * 财务管理
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Finance extends Base
{
	public function index()
    {
    	header('location: ' . url('merch/finance/withdraw'));exit;
    }

    public function withdraw()
    {

    	return $this->fetch('');
    }

}