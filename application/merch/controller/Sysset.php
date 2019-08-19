<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Sysset extends Base
{
	public function index()
	{
		$merch = $this->merch;
		$item = Db::name('shop_merch')->where('id = ' . $merch['id'])->find();
		if( empty($item) || empty($item["accoutntime"]) ) 
		{
			$accounttime = strtotime("+365 day");
		} else {
			$accounttime = $item["accounttime"];
		}
		if( !empty($item["accountid"]) ) 
		{
			$account = Db::name('shop_merch_account')->where('id = ' . $item['accountid'])->find();
		}
		if(Request::instance()->isPost()) 
		{
			$fdata = array( );
			$data = array( "merchname" => trim($_POST["merchname"]), "salecate" => trim($_POST["salecate"]), "realname" => trim($_POST["realname"]), "mobile" => trim($_POST["mobile"]), "desc" => trim($_POST["desc1"]), "address" => trim($_POST["address"]), "tel" => trim($_POST["tel"]), "lng" => $_POST["map"]["lng"], "lat" => $_POST["map"]["lat"], "logo" => trim($_POST["logo"]), "banner" => trim($_POST["banner"]) );
			Db::name('shop_merch')->where('id = ' . $merch['id'])->update($data);
			show_json(1);
		}
		$this->assign(['item'=>$item,'accounttime'=>$accounttime,'account'=>$account]);
		return $this->fetch('sysset/index');
	}

}