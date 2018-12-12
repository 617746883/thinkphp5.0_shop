<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Util extends \think\Model
{
	public static function get_area_config_set()
	{
		$data = model('common')->getSysset('area_config');

		if (empty($data)) {
			$data = self::get_area_config_data();
		}
		return $data;
	}

	public static function get_area_config_data()
	{
		$data = Db::name('shop_area_config')->limit(1)->find();
		return $data;
	}

	public static function pwd_encrypt($string, $operation, $key = 'key')
	{
		$key = md5($key);
		$key_length = strlen($key);
		$string = ($operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string);
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		$i = 0;

		while ($i <= 255) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
			++$i;
		}

		$j = $i = 0;

		while ($i < 256) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
			++$i;
		}

		$a = $j = $i = 0;

		while ($i < $string_length) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
			++$i;
		}

		if ($operation == 'D') {
			if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
				return substr($result, 8);
			}

			return '';
		}

		return str_replace('=', '', base64_encode($result));
	}

	public static function send_sms_captcha($mobile, $captcha, $type)
	{
	    $log = Db::name('sms_log')
        	->where('mobile', $mobile)
        	->where('type', $type)
        	->order('createtime', 'desc')
        	->find();
	    if((time() - $log['createtime']) < 120){
	    	return array('code'=>-1,'msg'=>'120秒内不允许重复发送','data'=>'');
	    }	        
	    $row = Db::name('sms_log')->insert(array('mobile'=>$mobile,'code'=>$captcha,'type'=>$type,'createtime'=>time()));
	    if(!$row) {
	    	return array('code'=>-2,'msg'=>'发送失败!','data'=>'123');
	    }	
	    $shopset = model('common')->getSysset();  
	    $set=Db::name('shop_sms_set')->find();   
	    if (!empty($set['aliyun_new'])) {
	    	$send = self::sendSMS($mobile,$captcha,'SMS_148614721','IWE');
	    } else {
	    	$content = '【' . $shopset['shop']['name'] . '】您的验证码是：'.$captcha.'      5分钟内有效，请妥善保管。';	    
        	$send = self::sendSMS($mobile,$content);
	    }
	    
	    if($send['status'] !== 1) {
	    	return array('code'=>-1,'msg'=>'发送失败!!!','data'=>$send);
	    }       
	    return array('code'=>1,'msg'=>'发送成功','data'=>$send);
	}

	public static function sendSMS($mobile, $message = '', $templatecode = '', $signname = '')
    {
        if(!check_mobile($mobile)){
            return array('status'=>0, 'msg' => '手机号码格式有误');
        }
        if (empty($message)) {
            return array('status'=>0, 'msg' => '信息内容不能为空');
        }
        $set=Db::name('shop_sms_set')->find();
        if(!empty($set['emay'])) {
        	vendor('Emay.Emay');
			$emay = new \Emay();
			$emay->appid = $set['emay_appid'];
	        $emay->encryptKey = $set['emay_pw'];
			$result=$emay->SendSMS($mobile, $message);
			$result = get_object_vars($result);
			if($result['result'] == 'SUCCESS'){
	            return array('status'=>1, 'msg' => $result);
	        }else{
	            return array('status'=>0, 'msg' => $result);
	        }
        } elseif (!empty($set['meilian'])) {
        	vendor('meilian.Meilian');
        	$config = array('username' => $set['meilian_username'],'password_md5' => $set['meilian_password_md5'],'apikey' => $set['meilian_apikey']);
			$meilian = new \Meilian();
			$meilian->config = $config;
			$result=$meilian->sendSMS($mobile, $message);
			if($result) {
	            return array('status'=>1, 'msg' => $result);
	        } else {
	            return array('status'=>0, 'msg' => array());
	        }
        } elseif (!empty($set['aliyun_new'])) {
        	vendor('alisms.SignatureHelper');
        	$params = Array (
		        "code" => $message
		    );
        	$option = array('keyid' => $set['aliyun_new_keyid'], 'keysecret' => $set['aliyun_new_keysecret'], 'phonenumbers' => $mobile, 'signname' => $signname, 'templatecode' => $templatecode, 'templateparam' => $params);
        	$aliyun_new = new \SignatureHelper();
			$result = $aliyun_new->sendSms($option);return $result;
			if ($result['Message'] != 'OK') {
				return array('status' => 0, 'msg' => '短信发送失败(错误信息: ' . $result['Message'] . ')');
			}
        }
    }

    public function getExpressList($express, $expresssn)
	{
		$shopset = model('common')->getSysset();
		$express_set = $shopset['express'];
		$express = ($express == 'jymwl' ? 'jiayunmeiwuliu' : $express);
		$express = ($express == 'TTKD' ? 'tiantian' : $express);
		$express = ($express == 'jjwl' ? 'jiajiwuliu' : $express);
		$express = ($express == 'zhongtiekuaiyun' ? 'ztky' : $express);
		if (!empty($express_set['isopen']) && !empty($express_set['apikey'])) {
			if (!empty($express_set['cache']) && (0 < $express_set['cache'])) {
				$cache_time = $express_set['cache'] * 60;
				$cache = Db::name('shop_express_cache')->where('express',$express)->where('expresssn',$expresssn)->find();
				if ((time() <= $cache['lasttime'] + $cache_time) && !empty($cache['datas'])) {
					return iunserializer($cache['datas']);
				}
			}
			if ($express_set['isopen'] == 1) {
				$url = 'http://api.kuaidi100.com/api?id=' . $express_set['apikey'] . '&com=' . $express . '&nu=' . $expresssn;
				$params = array();
			} else {
				$url = 'http://poll.kuaidi100.com/poll/query.do';
				$params = array('customer' => $express_set['customer'], 'param' => json_encode(array('com' => $express, 'num' => $expresssn)));
				$params['sign'] = md5($params['param'] . $express_set['apikey'] . $params['customer']);
				$params['sign'] = strtoupper($params['sign']);
			}

			$response = ihttp_post($url, $params);
			$content = $response['content'];
			$info = json_decode($content, true);
		}

		if (!isset($info) || empty($info['data']) || !is_array($info['data'])) {
			$url = 'https://www.kuaidi100.com/query?type=' . $express . '&postid=' . $expresssn . '&id=1&valicode=&temp=';
			$ch = curl_init(); 
	        $timeout = 5; 
	        curl_setopt($ch, CURLOPT_URL, $url); 
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);  
	        $contents = curl_exec($ch); 
	        curl_close($ch);
			// $response = ihttp_request($url);
			// $content = $response['content'];
			$info = json_decode($contents, true);
			$useapi = false;
		} else {
			$useapi = true;
		}
		
		$list = array();
		if (!empty($info['data']) && is_array($info['data'])) {
			foreach ($info['data'] as $index => $data) {
				$list[] = array('time' => trim($data['time']), 'step' => trim($data['context']));
			}
		}

		if ($useapi && (0 < $express_set['cache']) && !empty($list)) {
			if (empty($cache)) {
				Db::name('shop_express_cache')->insert(array('expresssn' => $expresssn, 'express' => $express, 'lasttime' => time(), 'datas' => iserializer($list)));
			} else {
				Db::name('shop_express_cache')->where('id',$cache['id'])->update(array('lasttime' => time(), 'datas' => iserializer($list)));
			}
		}

		return array('status' => $info['status'],'state' => $info['state'],'list' => $list);
	}

}