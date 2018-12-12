<?php
header("content-type:text/xml;charset=utf-8");
error_reporting(E_ALL);
ini_set('display_errors', '1');
// 定义时区
ini_set('date.timezone','Asia/Shanghai');
class WechatPay {
    public $config;
    // 构造函数
    public function __construct(){
        // 如果是在thinkphp中 那么需要补全/Application/Common/Conf/config.php中的配置
        // 如果不是在thinkphp框架中使用；那么注释掉下面一行代码；直接补全 private $config 即可
        // $this->config=config('wechatpay');
        // $set = model('common')->getSec();
        // $sec = iunserializer($set['sec']);
        // $this->config=array('APPID'=>$sec['app_wechat']['appid'], 'MCHID'=>$sec['app_wechat']['merchid'], 'KEY'=>$sec['app_wechat']['apikey'], 'NOTIFY_URL'=>getHttpHost() . '/payment/wechat/notify.php');
    }
    /**
     * 统一下单
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function unifiedOrder($order){
        // 获取配置项
        $config=$this->config;
        $config=array(
            'appid'=>$config['APPID'],
            'mch_id'=>$config['MCHID'],
            'nonce_str'=>'test',
            'spbill_create_ip'=>get_client_ip(),
            'notify_url'=>$config['NOTIFY_URL']
        );

        // 合并配置数据和订单数据
        $data=array_merge($order,$config);
        // 生成签名
        $sign=$this->makeSign($data);
        $data['sign']=$sign;
        $xml=$this->toXml($data);
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//接收xml数据的文件
        $header[] = "Content-type: text/xml";//定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 兼容本地没有指定curl.cainfo路径的错误
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            // 显示报错信息；终止继续执行
            die(curl_error($ch));
        }
        curl_close($ch);
        $result=$this->toArray($response);
        // 显示错误信息
        if ($result['return_code']=='FAIL') {
            return $result['return_msg'];
        }
        $result['sign']=$sign;
        $result['nonce_str']='5K8264ILTKCH16CQ2502SI8ZNMTM67VS';
        return $result;
    }

    /**
     * 验证
     * @return array 返回数组格式的notify数据
     */
    public function notify(){
        // 获取xml
        $xml=file_get_contents('php://input', 'r'); 
        // 转成php数组
        $data=$this->toArray($xml);
        // 保存原sign
        $data_sign=$data['sign'];
        // sign不参与签名
        unset($data['sign']);
        $sign=$this->makeSign($data);
        // 判断签名是否正确  判断支付状态
        if ($sign===$data_sign && $data['return_code']=='SUCCESS' && $data['result_code']=='SUCCESS') {
            $result=$data;
        } else {
            $result=false;
        }
        // 返回状态给微信服务器
        if ($result) {
            $str='<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        } else {
            $str='<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        echo $str;
        return $result;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    public function toXml($data){
        if(!is_array($data) || count($data) <= 0){
            throw new WxPayException("数组数据异常！");
        }
        $xml = "<xml>";
        foreach ($data as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    /**
     * 生成签名
     * @param WxPayConfigInterface $config  配置对象
     * @param bool $needSignType  是否需要补signtype
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($data)
    {
        $config=$this->config;
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$config['KEY'];
        //签名步骤三：MD5加密或者HMAC-SHA256
        $string = md5($string);        
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v !== "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        
        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 将xml转为array
     * @param  string $xml xml字符串
     * @return array       转换得到的数组
     */
    public function toArray($xml){   
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $result= json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
        return $result;
    }

    /**
      * 生成APP端支付参数
      * @param  $prepayid   预支付id
      */
     public function getAppPayParams($order){
        // 统一下单 获取prepay_id
        $config=$this->config;
        $unified_order=$this->unifiedOrder($order);
        if(empty($unified_order) || !is_array($unified_order) || $unified_order['result_code'] == 'FAIL') {
            if(isset($unified_order['err_code_des'])) {
                return $unified_order['err_code_des'];
            } else {
                return '支付参数出错';
            }
        }
        $data['appid'] = $config['APPID'];
        $data['partnerid'] = $config['MCHID'];
        $data['prepayid'] = $unified_order['prepay_id'];
        $data['package'] = "Sign=WXPay";// 预支付交易会话标识
        $data['noncestr'] = $unified_order['nonce_str'];// 随机字符串
        $data['timestamp'] = time();
        $data['sign'] = $this->makeSign( $data ); 
        return $data;
     }

    /**
     * 获取jssdk需要用到的数据
     * @return array jssdk需要用到的数据
     */
    public function getJsapiPayParams($order){
        // 统一下单 获取prepay_id
        $config = $this->config;
        // 组合获取openid的url
        
        if(isset($order['openid']) && !empty($order['openid'])) {

        } else {
            $order['openid']=$this->GetOpenid();
        }
        
        $unified_order = $this->unifiedOrder($order);
        if(empty($unified_order) || (!empty($unified_order) && (!is_array($unified_order) || $unified_order['result_code'] == 'FAIL'))) {
            if(isset($unified_order['err_code_des'])) {
                return $unified_order['err_code_des'];
            } else {
                return $unified_order;
            } 
            return '支付出错';           
        }
        $data['appId'] = $config['APPID'];
        $data['package'] = 'prepay_id='.$unified_order['prepay_id'];// 预支付交易会话标识
        $data['nonceStr'] = $unified_order['nonce_str'];// 随机字符串
        $data['timeStamp'] = strval($this->getMillisecond());
        $data['signType'] = 'MD5';
        $data['paySign'] = $this->makeSign( $data ); 
        return $data;
    }

    /**
     * 获取H5需要用到的数据
     * @return array H5需要用到的数据
     */
    public function getMwebPayParams($order){
        // 统一下单 获取prepay_id
        $config = $this->config;
        
        $unified_order = $this->unifiedOrder($order);
        if(empty($unified_order) || (!empty($unified_order) && (!is_array($unified_order) || $unified_order['result_code'] == 'FAIL'))) {
            if(isset($unified_order['err_code_des'])) {
                return $unified_order['err_code_des'];
            } else {
                return $unified_order;
            } 
            return '支付出错';           
        }
        $data['appid'] = $config['APPID'];
        $data['partnerid'] = $config['MCHID'];
        $data['prepayid'] = $unified_order['prepay_id'];
        $data['package'] = "Sign=WXPay";// 预支付交易会话标识
        $data['noncestr'] = $unified_order['nonce_str'];// 随机字符串
        $data['timestamp'] = $this->getMillisecond();
        $data['sign'] = $this->makeSign( $data ); 
        return $data;
    }

    /**
     * 生成支付二维码
     * @param  array $order 订单 必须包含支付所需要的参数 body(产品描述)、total_fee(订单金额)、out_trade_no(订单号)、product_id(产品id)、trade_type(类型：JSAPI，NATIVE，APP)
     */
    public function qrcodepay($order){
        $config=$this->config;
        $result=$this->unifiedOrder($config, $order);
        $decodeurl=urldecode($result['code_url']);
        return $decodeurl;
        // qrcode($decodeurl);
    }

    /**
     * 
     * 通过跳转获取用户的openid，跳转流程如下：
     * 1、设置自己需要调回的url及其其他参数，跳转到微信服务器https://open.weixin.qq.com/connect/oauth2/authorize
     * 2、微信服务处理完成之后会跳转回用户redirect_uri地址，此时会带上一些参数，如：code
     * 
     * @return 用户的openid
     */
    public function GetOpenid()
    {
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
            $url = $this->_CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $openid = $this->getOpenidFromMp($code);
            return $openid;
        }
    }

    /**
     * 
     * 构造获取code的url连接
     * @param string $redirectUrl 微信服务器回跳的url，需要url编码
     * 
     * @return 返回构造好的url
     */
    private function _CreateOauthUrlForCode($redirectUrl)
    {
        $config = $this->config;
        $urlObj["appid"] = 'wx1daf30a5ae26e09e';
        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /**
     * 
     * 构造获取open和access_toke的url地址
     * @param string $code，微信跳转带回的code
     * 
     * @return 请求的url
     */
    private function __CreateOauthUrlForOpenid($code)
    {
        $config = $this->config;
        $urlObj["appid"] = 'wx1daf30a5ae26e09e';
        $urlObj["secret"] = '993981ce0f28be38d59bb48706b18db8';
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * 
     * 通过code从工作平台获取openid机器access_token
     * @param string $code 微信跳转回来带上的code
     * 
     * @return openid
     */
    public function GetOpenidFromMp($code)
    {
        $url = $this->__CreateOauthUrlForOpenid($code);

        //初始化curl
        $ch = curl_init();
        $curlVersion = curl_version();
        $config = $this->config;
        $ua = "WXPaySDK/3.0.9 (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." ".$config['MCHID'];

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $proxyHost = "0.0.0.0";
        $proxyPort = 0;
        if($proxyHost != "0.0.0.0" && $proxyPort != 0){
            curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
            curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
        }
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res,true);
        $this->data = $data;
        $openid = $data['openid'];
        return $openid;
    }

    /**
     * 
     * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
     * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
     * appid、mchid、spbill_create_ip、nonce_str不需要填入
     * @param WxPayConfigInterface $config  配置对象
     * @param WxPayRefund $inputObj
     * @param int $timeOut
     * @throws WxPayException
     * @return 成功时返回，其他抛异常
     */
    public function refund($inputObj, $timeOut = 6)
    {
        $url = "https://api.mch.weixin.qq.com/secapi/pay/refund";
        $config=$this->config;
        //检测必填参数
        if(!$inputObj['out_refund_no']){
            return "退款申请接口中，缺少必填参数out_refund_no！";
        }else if(!$inputObj['total_fee']){
            return "退款申请接口中，缺少必填参数total_fee！";
        }else if(!$inputObj['refund_fee']){
            return "退款申请接口中，缺少必填参数refund_fee！";
        }else if(!$inputObj['op_user_id']){
            return "退款申请接口中，缺少必填参数op_user_id！";
        }
        $inputObj['appid'] = $config['APPID'];//公众账号ID
        $inputObj['mch_id'] = $config['MCHID'];//商户号
        $inputObj['nonce_str'] = self::getNonceStr();//随机字符串
        
        $inputObj['sign'] = $this->makeSign($inputObj);;//签名
        $xml = $this->toXml($inputObj);
        $startTimeStamp = $this->getMillisecond();//请求开始时间
        $response = self::postXmlCurl($xml, $url, true, $timeOut);
        if(!$this->xml_parser($response)) {
            return $response;
        }
        $result = $this->toArray($response);   
        return $result;
    }

    //自定义xml验证函数xml_parser()
    public function xml_parser($str){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$str,true)){
          xml_parser_free($xml_parser);
          return false;
        }else {
          return true;
        }
    }

    /**
     * 
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) 
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
        } 
        return $str;
    }

    /**
     * 获取毫秒级别的时间戳
     */
    public function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }
    
    /**
     * curl 请求http
     */
    public function curl_get_contents($url){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);                //设置访问的url地址
        // curl_setopt($ch,CURLOPT_HEADER,1);               //是否显示头部信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);               //设置超时
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   //用户访问代理 User-Agent
        curl_setopt($ch, CURLOPT_REFERER,$_SERVER['HTTP_HOST']);        //设置 referer
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);          //跟踪301
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
        $r=curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    /**
     * 以post方式提交xml到对应的接口url
     * 
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {       
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        $config=$this->config;
        //如果有配置代理这里就设置代理
        if(isset($config['curl_proxy_host']) && $config['curl_proxy_host'] != "0.0.0.0" && $config['curl_proxy_host'] != 0){
            curl_setopt($ch,CURLOPT_PROXY, $config['curl_proxy_host']);
            curl_setopt($ch,CURLOPT_PROXYPORT, $config['curl_proxy_host']);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, dirname(__FILE__).'/'.'cert/apiclient_cert.pem');  
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, dirname(__FILE__).'/'.'cert/apiclient_key.pem');  
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else { 
            $error = curl_errno($ch);
            curl_close($ch);
            return "curl出错，错误码:" . $error;
        }
    }
}