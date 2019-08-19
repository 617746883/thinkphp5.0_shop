<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Fanwe extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $db = Db::connect('db_configfanwe')->name('user')->where('id = 1')->find();
        dump($db);
    }

}
