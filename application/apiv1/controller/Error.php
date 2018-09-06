<?php
namespace app\apiv1\controller;
use think\Controller;
use think\Request;

class Error extends Controller
{
    public function _empty(Request $request)
    {
    	$this->result(0,'empty method!');
    }
}