<?php
define("YM_SMS_ADDR",                   "http://m.5c.com.cn/api/send/index.php?");/*接口地址,//如连接超时，可能是您服务器不支持域名解析，请将下面连接中的：【m.5c.com.cn】修改为IP：【115.28.23.78】*/

define("END",               "\n");

class Meilian {
	public $config;

	public function sendSMS($mobile,$contentUrlEncode) {

		$data = $this->config;

	    //发送链接（用户名，密码，apikey，手机号，内容）

	    $url = YM_SMS_ADDR;  

	    $data['mobile'] = $mobile;
	    $data['content'] = $contentUrlEncode;
	    $data['encode'] = 'UTF-8';

	    $result = $this->curlSMS($url,$data);

	    //print_r($data); //测试

	    return $result;

	}

	public function curlSMS($url,$post_fields=array()) {

	    $ch=curl_init();

	    curl_setopt($ch,CURLOPT_URL,$url);//用PHP取回的URL地址（值将被作为字符串）

	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);//使用curl_setopt获取页面内容或提交数据，有时候希望返回的内容作为变量存储，而不是直接输出，这时候希望返回的内容作为变量

	    curl_setopt($ch,CURLOPT_TIMEOUT,30);//30秒超时限制

	    curl_setopt($ch,CURLOPT_HEADER,1);//将文件头输出直接可见。

	    curl_setopt($ch,CURLOPT_POST,1);//设置这个选项为一个零非值，这个post是普通的application/x-www-from-urlencoded类型，多数被HTTP表调用。

	    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_fields);//post操作的所有数据的字符串。

	    $data = curl_exec($ch);//抓取URL并把他传递给浏览器

	    curl_close($ch);//释放资源

	    $res = explode("\r\n\r\n",$data);//explode把他打散成为数组

	    return $res[2]; //然后在这里返回数组。

	}

}