<?php
/**
 * 后台系统设置
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Sysset extends Base
{
	public function index()
	{
		$merch=$this->merch;
		if (Request::instance()->isPost()) {
			if (empty(input('groupid'))) {
				show_json(0, '请选择店鋪组!');
			}
			if (empty(input('cateid'))) {
				show_json(0, '请选择店鋪分类!');
			}
			$lng = $lat = '';
			if(!empty(input('map/a')) && is_array(input('map/a')))
			{
				$lng = input('map/a')['lng'];
				$lat = input('map/a')['lat'];
			}
			$banner = input('banner/s');
			$data = array('merchname' => trim(input('merchname')), 'salecate' => trim(input('salecate')), 'realname' => trim(input('realname')), 'mobile' => trim(input('mobile')), 'address' => trim(input('address')), 'tel' => trim(input('tel')), 'lng' => $lng, 'lat' => $lat, 'accounttime' => strtotime(input('accounttime')), 'accounttotal' => input('accounttotal/d'), 'groupid' => input('groupid/d'), 'cateid' => input('cateid/d'), 'isrecommand' => input('isrecommand/d'), 'remark' => trim(input('remark')), 'desc' => trim(input('desc1')), 'logo' => trim(input('logo')), 'banner' => $banner, 'paymid' => input('paymid/d',0), 'payrate' => trim(input('payrate'), '%'));
			
			if (empty($item)) {
				$item['applytime'] = time();
				$id = Db::name('shop_store')->insertGetId($data);
				model('shop')->plog('merch.user.add', '添加店鋪 ID: ' . $data['id'] . ' 店鋪名: ' . $data['merchname'] . '<br/>帐号: ' . $data['username'] . '<br/>到期时间: ' . date('Y-m-d', $data['accounttime']));
			} else {
				Db::name('shop_store')->where('id',$id)->update($data);
				model('shop')->plog('merch.user.edit', '编辑店鋪 ID: ' . $data['id'] . ' 店鋪名: ' . $item['merchname'] . ' -> ' . $data['merchname'] . '<br/>帐号: ' . $item['username'] . ' -> ' . $data['username'] . '<br/>到期时间: ' . date('Y-m-d', $item['accounttime']) . ' -> ' . date('Y-m-d', $data['accounttime']));
			}
			show_json(1, array('url' => url('merch/store/edit',array('id'=>$id))));
		}
		$item = Db::name('shop_store')->where('id',$merch['id'])->find();
		$this->assign(['item' => $item]);
		return $this->fetch('');
	}

}