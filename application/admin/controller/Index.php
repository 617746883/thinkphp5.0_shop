<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
class Index extends Base
{
    public function index()
    {
        return $this->fetch('/index');
    }

    public function error()
    {
    	return $this->fetch('/error');
    }

    public function test()
    { 	
        
    }
}