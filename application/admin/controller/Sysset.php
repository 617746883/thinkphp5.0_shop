<?php
/**
 * 后台系统设置
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
use think\File;
class Sysset extends Base
{
	public function index()
	{
		$data = model('common')->getSysset('shop');
		if (Request::instance()->isPost()) {
			$data = ((is_array(input('data/a')) ? input('data/a') : array()));
			$data['name'] = trim($data['name']);
			model('common')->updateSysset(array('shop' => $data));
			model('shop')->plog('sysset.shop.edit', '修改系统设置-商城设置');
			show_json(1);
		}
		$this->assign('data',$data);
		return $this->fetch('sysset/index');
	}

	public function close()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data['flag'] = intval($data['flag']);
			$data['detail'] = model('common')->html_images($data['detail']);
			$data['url'] = trim($data['url']);
			model('common')->updateSysset(array('close' => $data));
			$shop = model('common')->getSysset('shop');
			$shop['close'] = $data['flag'];
			$shop['closedetail'] = $data['detail'];
			$shop['closeurl'] = $data['url'];
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('sysset.close.edit', '修改系统设置-商城关闭设置');
			show_json(1);
		}

		$data = model('common')->getSysset('close');
		if (empty($data)) {
			$shop = model('common')->getSysset('shop');
			$data['flag'] = $shop['close'];
			$data['detail'] = $shop['closedetail'];
			$data['url'] = $shop['closeurl'];
		}
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function category()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$shop = model('common')->getSysset('shop');
			$shop['level'] = intval($data['level']);
			$shop['show'] = intval($data['show']);
			$shop['advimg'] = trim($data['advimg']);
			$shop['advurl'] = trim($data['advurl']);
			model('common')->updateSysset(array('category' => $data));
			$shop = model('common')->getSysset('shop');
			$shop['catlevel'] = $data['level'];
			$shop['catshow'] = $data['show'];
			$shop['catadvimg'] = trim($data['advimg']);
			$shop['catadvurl'] = $data['advurl'];
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('sysset.category.edit', '修改系统设置-分类层级设置');
			model('shop')->getCategory();
			show_json(1);
		}


		$data = model('common')->getSysset('category');
		if (empty($data)) {
			$shop = model('common')->getSysset('shop');
			$data['level'] = $shop['catlevel'];
			$data['show'] = $shop['catshow'];
			$data['advimg'] = $shop['catadvimg'];
			$data['advurl'] = $shop['catadvurl'];
		}
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function trade()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			if ($data['maxcredit'] < 0) {
				$data['maxcredit'] = 0;
			}

			$data['nodispatchareas'] = iserializer($data['nodispatchareas']);
			$data['nodispatchareas_code'] = iserializer($data['nodispatchareas_code']);

			if (!(empty($data['closeorder']))) {
				$data['closeorder'] = intval($data['closeorder']);
			}

			if (!(empty($data['willcloseorder']))) {
				$data['willcloseorder'] = intval($data['willcloseorder']);
			}

			model('common')->updateSysset(array('trade' => $data));
			model('shop')->plog('sysset.trade.edit', '修改系统设置-交易设置');
			show_json(1);
		}


		$areas = model('common')->getAreas();
		$data = model('common')->getSysset('trade');
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);
		$data['nodispatchareas'] = iunserializer($data['nodispatchareas']);
		$data['nodispatchareas_code'] = iunserializer($data['nodispatchareas_code']);
		$this->assign(['areas'=>$areas,'data'=>$data,'area_set'=>$area_set,'new_area'=>$new_area]);
		return $this->fetch('');
	}

	public function member()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data['levelname'] = trim($data['levelname']);
			$data['levelurl'] = trim($data['levelurl']);
			$data['leveltype'] = intval($data['leveltype']);
			model('common')->updateSysset(array('member' => $data));
			$shop = model('common')->getSysset('shop');
			$shop['levelname'] = $data['levelname'];
			$shop['levelurl'] = $data['levelurl'];
			$shop['leveltype'] = $data['leveltype'];
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('sysset.member.edit', '修改系统设置-会员设置');
			show_json(1);
		}
		$data = model('common')->getSysset('member');
		if (!(isset($data['levelname']))) {
			$shop = model('common')->getSysset('shop');
			$data['levelname'] = $shop['levelname'];
			$data['levelurl'] = $shop['levelurl'];
			$data['leveltype'] = $shop['leveltype'];
		}
		$this->assign(['data'=>$data]);

		return $this->fetch('');
	}

	public function contact()
	{
		if (Request::instance()->isPost()) {
			$data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data['province'] = trim(input('province'));
			$data['city'] = trim(input('city'));
			$data['provincecode'] = trim(input('chose_province_code'));
			$data['citycode'] = trim(input('chose_city_code'));
			$data['qq'] = trim($data['qq']);
			$data['address'] = trim($data['address']);
			$data['phone'] = trim($data['phone']);
			model('common')->updateSysset(array('contact' => $data));
			$shop = model('common')->getSysset('shop');
			$shop['qq'] = $data['qq'];
			$shop['address'] = $data['address'];
			$shop['phone'] = $data['phone'];
			$shop['province'] = $data['province'];
			$shop['city'] = $data['city'];
			$shop['provincecode'] = $data['provincecode'];
			$shop['citycode'] = $data['citycode'];
			model('common')->updateSysset(array('shop' => $shop));
			model('shop')->plog('sysset.contact.edit', '修改系统设置-联系方式设置');
			show_json(1);
		}
		$data = model('common')->getSysset('contact');
		if (empty($data)) {
			$shop = model('common')->getSysset('shop');
			$data['qq'] = $shop['qq'];
			$data['address'] = $shop['address'];
			$data['phone'] = $shop['phone'];
			$data['province'] = $shop['province'];
			$data['city'] = $shop['city'];
			$data['provincecode'] = $shop['provincecode'];
			$data['citycode'] = $shop['citycode'];
		}
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function area()
	{
		$data = model('util')->get_area_config_data();

		if (Request::instance()->isPost()) {
			$submit_data = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$array = array();
			if (empty($data) || empty($data['new_area'])) {
				$array['new_area'] = intval($submit_data['new_area']);
				if (!(empty($array['new_area']))) {
					$array['address_street'] = intval($submit_data['address_street']);
				}
				 else {
					$array['address_street'] = 0;
				}
			}
			else if (!(empty($data['new_area']))) {
				$array['address_street'] = intval($submit_data['address_street']);
			}


			if (empty($data)) {
				$data['createtime'] = time();
				Db::name('shop_area_config')->insert($data);
			}
			 elseif (!(empty($array))) {
			 	Db::name('shop_area_config')->where('id',$data['id'])->update($array);
			}

			$data = model('util')->get_area_config_data();
			model('common')->updateSysset(array('area_config' => $data));
			model('shop')->plog('sysset.area.edit', '修改系统设置-地址库设置');
			show_json(1);
		}
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function express()
	{
		$data = model('common')->getSysset('express');

		if (Request::instance()->isPost()) {
			$data = array('apikey' => trim($_POST['apikey']), 'customer' => trim($_POST['customer']), 'isopen' => intval($_POST['isopen']), 'cache' => intval($_POST['cache']));
			model('common')->updateSysset(array('express' => $data));
			model('shop')->plog('sysset.express.edit', '修改系统设置-物流信息接口');
			show_json(1);
		}
		return $this->fetch('');
	}

	public function smsset()
	{		
		$item = Db::name('shop_sms_set')->limit(1)->find();
		$balance = 0;
		if(!empty($item) && !empty($item['emay'])) {
			$balance = $this->smsgetnum();
		}
		
		if (Request::instance()->isPost()) {
			$arr = array('juhe' => input('juhe/d'), 'juhe_key' => trim(input('juhe_key')), 'aliyun_new' => input('aliyun_new/d'), 'aliyun_new_keyid' => trim(input('aliyun_new_keyid')), 'aliyun_new_keysecret' => trim(input('aliyun_new_keysecret')), 'emay' => input('emay/d'), 'emay_url' => trim(input('emay_url')), 'emay_appid' => trim(input('emay_appid')), 'emay_pw' => trim(input('emay_pw')),'emay_sk'=>trim(input('emay_sk')) , 'emay_phost' => trim(input('emay_phost')), 'emay_pport' => input('emay_pport/d'), 'emay_puser' => trim(input('emay_puser')), 'emay_ppw' => trim(input('emay_ppw')), 'emay_out' => input('emay_out/d'), 'emay_outresp' => (empty(input('emay_outresp/d')) ? 30 : input('emay_outresp/d')), 'emay_warn' => input('emay_warn/d'), 'emay_mobile' => input('emay_mobile/d'), 'meilian' => input('meilian/d'), 'meilian_username' => trim(input('meilian_username')), 'meilian_password_md5' => trim(input('meilian_password_md5')), 'meilian_apikey' => trim(input('meilian_apikey')));
			if (empty($item)) {
				$id = Db::name('shop_sms_set')->insertGetId($arr);
			}
			else {
				Db::name('shop_sms_set')->where('id',$item['id'])->update($arr);
			}
			show_json(1);
		}		
		
		$notice = $this->smsbanlance();
		$this->assign(['item'=>$item,'balance' => $balance]);
		return $this->fetch('');
	}

	public function smsgetnum()
	{
		$item = Db::name('shop_sms_set')->limit(1)->find();	
		vendor('Emay.Emay');
		$emay=new \Emay();
        $emay->appid = $item['emay_appid'];
        $emay->encryptKey = $item['emay_pw'];
		$result = $emay->getBalance();
        $result = get_object_vars($result);
        $a = $result['plaintext'];
        $jsons= json_decode($a,true);
        $balance = $jsons['balance'];
        return $balance;
	}

	public function smsbanlance()
	{
		$item = Db::name('shop_sms_set')->limit(1)->find();
		if(empty($item) || empty($item['emay'])) {
			return;
		}
		vendor('Emay.Emay');
		$emay = new \Emay();
        $emay->appid = $item['emay_appid'];
        $emay->encryptKey = $item['emay_pw'];
		$result = $emay->getBalance();
        $result = get_object_vars($result);
        $a = $result['plaintext'];
        $jsons = json_decode($a,true);
        $balance = $jsons['balance'];

        $log = Db::name('sms_log')
	    	->where('type', 'sysset')
	    	->order('createtime', 'desc')
	    	->find();

        if($item['emay_warn']>$balance) {
	        if((time() - $log['createtime']) > 86400) {
	    		$content = '【天润】您的短信剩余条数是：'.$balance.'   条, 请尽快充值。';
    			$result=$emay->SendSMS($id['emay_mobile'], $content);
	    	}
        }
        return true;
	}

	public function notice()
	{
		$set = model('common')->getSec();
		$sec = iunserializer($set['sec']);
		$data = model('common')->getSysset('notice');
		if (Request::instance()->isPost()) {
			$sec['j_push']['jiguang_appKey'] = trim($_POST['data']['jiguang_appKey']);
			$sec['j_push']['jiguang_masterSecret'] = trim($_POST['data']['jiguang_masterSecret']);
			$sec['ali_push']['ali_accessKeyId'] = trim($_POST['data']['ali_accessKeyId']);
			$sec['ali_push']['ali_accessKeySecret'] = trim($_POST['data']['ali_accessKeySecret']);
			$sec['ali_push']['ali_appKey'] = trim($_POST['data']['ali_appKey']);
			
			if(!empty($set['id'])) {
				Db::name('shop_sysset')->where('id',$set['id'])->update(array('sec' => iserializer($sec)));
			} else {
				Db::name('shop_sysset')->insert(array('sec' => iserializer($sec)));
			}
			$inputdata = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data = array();
			$data['j_push'] = intval($inputdata['j_push']);
			$data['ali_push'] = intval($inputdata['ali_push']);
			model('common')->updateSysset(array('notice' => $data));
			model('shop')->plog('sysset.payset.edit', '修改系统设置-推送设置');
			show_json(1);
		}
		
		$this->assign(['sec'=>$sec,'data'=>$data]);
		return $this->fetch('');
	}

	public function payset()
	{
		$set = model('common')->getSec();
		$sec = iunserializer($set['sec']);
		$data = model('common')->getSysset('pay');
		$payments = Db::name('shop_payment')->where('paytype',0)->field('id,title')->select();
		$paymentalis = Db::name('shop_payment')->where('paytype',1)->field('id,title')->select();

		// if (empty($payments)) {
		// 	$payments = array();
		// 	$setting = uni_setting($_W['uniacid'], array('payment'));
		// 	$payment = $setting['payment'];

		// 	if (!(empty($payment['wechat']['mchid']))) {
		// 		if (IMS_VERSION <= 0.80000000000000004) {
		// 			$payment['wechat']['apikey'] = $payment['wechat']['signkey'];
		// 		}


		// 		$default = array('title' => '微信支付', 'type' => 0, 'sub_appid' => $_W['account']['key'], 'sub_appsecret' => $_W['account']['secret'], 'sub_mch_id' => $payment['wechat']['mchid'], 'apikey' => $payment['wechat']['apikey'], 'cert_file' => $sec['cert'], 'key_file' => $sec['key'], 'root_file' => $sec['root'], 'createtime' => time());
		// 		$payments[] = $default;
		// 		pdo_insert('ewei_shop_payment', $default);
		// 		$default_0 = pdo_insertid();
		// 	}


		// 	if ($data['weixin'] == 1) {
		// 		$data['weixin_id'] = $default_0;
		// 	}


		// 	model('common')->updateSysset(array('pay' => $data));
		// }
		if (Request::instance()->isPost()) {
			$sec['app_wechat']['appid'] = trim($_POST['data']['app_wechat_appid']);
			$sec['app_wechat']['appsecret'] = trim($_POST['data']['app_wechat_appsecret']);
			$sec['app_wechat']['merchname'] = trim($_POST['data']['app_wechat_merchname']);
			$sec['app_wechat']['merchid'] = trim($_POST['data']['app_wechat_merchid']);
			$sec['app_wechat']['apikey'] = trim($_POST['data']['app_wechat_apikey']);
			$sec['alipay_pay'] = ((is_array($_POST['data']['alipay_pay']) ? $_POST['data']['alipay_pay'] : array()));
			$sec['app_alipay']['public_key'] = trim($_POST['data']['app_alipay_public_key']);
			$sec['app_alipay']['private_key'] = trim($_POST['data']['app_alipay_private_key']);
			$sec['app_alipay']['appid'] = trim($_POST['data']['app_alipay_appid']);
			$sec['alipay'] = $_POST['data']['alipay'];

			if (request()->file('cert_file')) {
				$sec['app_wechat']['cert_file'] = $this->upload_cert('cert_file');
			}

			if (request()->file('key_file')) {
				$sec['app_wechat']['key_file'] = $this->upload_cert('key_file');
			}

			if(!empty($set['id'])) {
				Db::name('shop_sysset')->where('id',$set['id'])->update(array('sec' => iserializer($sec)));
			} else {
				Db::name('shop_sysset')->insert(array('sec' => iserializer($sec)));
			}
			$inputdata = ((is_array($_POST['data']) ? $_POST['data'] : array()));
			$data = array();
			$data['credit'] = intval($inputdata['credit']);
			$data['cash'] = intval($inputdata['cash']);
			$data['app_wechat'] = intval($inputdata['app_wechat']);
			$data['app_alipay'] = intval($inputdata['app_alipay']);
			$data['paytype'] = ((isset($inputdata['paytype']) ? $inputdata['paytype'] : array()));
			model('common')->updateSysset(array('pay' => $data));
			model('shop')->plog('sysset.payset.edit', '修改系统设置-支付设置');
			show_json(1);
		}
		$url = getHttpHost() . url('payment/wechat/notify');
		// $oCurl = curl_init();
		// curl_setopt($oCurl, CURLOPT_URL, $url);//目标URL
	 //    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );//设定是否显示头信息,1为显示
	 //    curl_setopt($oCurl, CURLOPT_BINARYTRANSFER, true) ;//在启用CURLOPT_RETURNTRANSFER时候将获取数据返回
	 //    $sContent = curl_exec($oCurl);
	 //    $aStatus = curl_getinfo($oCurl);//获取页面各种信息
	 //    curl_close($oCurl);
		// $resp = $sContent;dump($aStatus);
		$this->assign(['sec'=>$sec,'data'=>$data,'url'=>$url]);
		return $this->fetch('');
	}

	protected function upload_cert($fileinput)
	{
		$path = VENDOR_PATH . '/wechatpay/cert';
		$file = request()->file($fileinput);
		if(empty($file)) {
            show_json(0,'请选择文件');
        } 
        $f = $fileinput . '_1.pem';
		$outfilename = $path . '/' . $f;
		// 移动到框架应用根目录/public/uploads/ 目录下
	    if($file){
	        $info = $file->validate(['ext'=>'pem'])->move($path,'');
	        if($info){
	            return $info->getSaveName();
	        }else{
	            // 上传失败获取错误信息
	            show_json(0,$file->getError());
	        }
	    }
		return '';
	}

	public function bank()
	{
		$list = Db::name('system_bank')->order('id','desc')->paginate(20);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager]);
		return $this->fetch('');
	}

	public function bankadd()
	{
		$data = $this->bankpost();
		return $data;
	}

	public function bankedit()
	{
		$data = $this->bankpost();
		return $data;
	}

	protected function bankpost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$bankname = trim(input('bankname'));
			$status = intval(input('status'));

			if (empty($bankname)) {
				show_json(0, '请输入银行名称');
			}

			$data = array();
			$data['bankname'] = $bankname;
			$data['status'] = $status;

			if (!empty($id)) {
				Db::name('system_bank')->where('id',$id)->update($data);
			} else {
				$id = Db::name('system_bank')->insertGetId($data);
			}
			show_json(1);
		}

		$item = Db::name('system_bank')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		echo $this->fetch('sysset/bankpost');
	}

	public function bankdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('system_bank')->where('id','in',$id)->field('id')->select();

		foreach ($items as $item) {
			Db::name('system_bank')->where('id',$item['id'])->delete();
		}

		show_json(1, array('url' => referer()));
	}

	public function bankstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$status = input('status');
		$items = Db::name('system_bank')->where('id','in',$id)->field('id')->select();

		foreach ($items as $item) {
			Db::name('system_bank')->where('id',$item['id'])->update(array('status' => intval($status)));
		}

		show_json(1, array('url' => referer()));
	}

	public function bankdisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('system_bank')->where('id',$id)->find();

		if (!empty($item)) {
			Db::name('system_bank')->where('id',$item['id'])->update(array('displayorder' => $displayorder));
		}

		show_json(1);
	}

}