<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use Workerman\Lib\Timer;
use Redis\Redis;
class Index extends Base
{
    public function index()
    {
        return $this->fetch('/index');
    }

    public function _empty()
    {
    	return $this->fetch('/error');
    }

}