<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;

class Error extends Controller
{
    public function _empty(Request $request)
    {
        $this->redirect(url('admin/index/error'));
    }
}