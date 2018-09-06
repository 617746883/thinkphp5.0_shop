<?php
date_default_timezone_set('PRC');

define("YM_SMS_ADDR",                   "bjmtn.b2m.cn");/*接口地址,请联系销售获取*/
define("YM_SMS_SEND_URI",               "/inter/sendSingleSMS");/*发送单条短信接口*/
define("YM_SMS_SEND_BATCH_URI",         "/inter/sendBatchSMS");/*发送批次短信接口*/
define("YM_SMS_SEND_BATCHONLY_SMS_URI",         "/inter/sendBatchOnlySMS");/*发送批次[不支持自定义smsid]短信接口*/
define("YM_SMS_SEND_PERSONALITY_SMS_URI",       "/inter/sendPersonalitySMS");/*发送个性短信接口*/
define("YM_SMS_GETREPORT_URI",          "/inter/getReport");/*获取状态报告接口*/
define("YM_SMS_GETMO_URI",              "/inter/getMo");/*获取上行接口*/
define("YM_SMS_GETBALANCE_URI",         "/inter/getBalance");  /*获取余额接口*/
define("EN_GZIP",                        true);/* 是否开启GZIP */

define("END",               "\n");

class Emay {
    private $iv = "0102030405060708";//密钥偏移量IV，可自定义
    public $encryptKey;

    public $appid;

    //加密
    public function encrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
        
        if (true == EN_GZIP)   $encryptStr = gzencode($encryptStr);
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples
 
        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);
 
        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
 
        return $encrypted;
    }
 
    //解密
    public function decrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, $localIV);
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        $encryptedData = mdecrypt_generic($module, $encryptStr);
        
        if (true == EN_GZIP)   $encryptedData = gzdecode($encryptedData);
 
        return $encryptedData;
    }

    public function http_request($url, $data = null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $header[] = "appId: ".$this->appid;
        if (true == EN_GZIP)   $header[] = "gzip: on";
        
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $res = curl_exec($curl);
        
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);
        
        $header = substr($res, 0, $headerSize);
        
        //echo "HEADER=".$header.END;
        //echo "URL=".$url.END;
        
        $outobj = new stdClass();
        
        $lines = explode("\r\n",$header);
        foreach($lines as $line)
        {
            $items = explode(": ",$line);
            if(isset($items[0]) and !empty($items[0]) and 
               isset($items[1]) and !empty($items[1]))
                $outobj->$items[0] = $items[1];
        }
        
        $outobj->ciphertext = substr($res, $headerSize);

        return $outobj;
    }


    public function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    public function SendSMS($mobile, $content, $timerTime = "", $customSmsId = "", $extendedCode = "", $validPeriodtime= 120)
    {   


        $item = new stdClass();
        $item->mobile   = $mobile;
        $item->content  = $content;
        
        /* 选填内容 */ 
        if("" != $timerTime)    $item->timerTime    = $timerTime;
        if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
        if("" != $extendedCode) $item->extendedCode = $extendedCode;    
        
        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_SEND_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);
        return $resobj;
    }  
     
    public function SendBatchSMS($mobiles, $content, 
                $timerTime = "", $customSmsId = "",
                $extendedCode = "", 
                $validPeriodtime= 120)
    {   
        $item = new stdClass();

        $smses = array();
        foreach($mobiles as $mobile)    $smses[] = $mobile;

        $item->smses   = $smses;

        // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
        // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

        $item->content  = $content;
        /* 选填内容 */ 
        if("" != $timerTime)    $item->timerTime    = $timerTime;
        if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
        if("" != $extendedCode) $item->extendedCode = $extendedCode;    
        
        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_SEND_BATCH_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }   

    public function sendBatchOnlySMS($mobiles, $content, 
                $timerTime = "", $customSmsId = "",
                $extendedCode = "", 
                $validPeriodtime= 120)
    {   

        // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
        // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

        $item = new stdClass();

        $item->mobiles  = $mobiles;
        $item->content  = $content;
        /* 选填内容 */ 
        if("" != $timerTime)    $item->timerTime    = $timerTime;
        if("" != $extendedCode) $item->extendedCode = $extendedCode;    
        
        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_SEND_BATCHONLY_SMS_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }   

    public function sendPersonalitySMS($mobiles, 
                $timerTime = "", $customSmsId = "",
                $extendedCode = "", 
                $validPeriodtime= 120)
    {   

        // 如果您的系统环境不是UTF-8，内容需要转码到UTF-8。如下：从gb2312转到了UTF-8
        // $content = mb_convert_encoding( $content,"UTF-8","gb2312");

        $item = new stdClass();

        $smses = array();
        foreach($mobiles as $mobile)    $smses[] = $mobile;

        $item->smses   = $smses;

        /* 选填内容 */ 
        if("" != $timerTime)    $item->timerTime    = $timerTime;
        if("" != $customSmsId)  $item->customSmsId  = $customSmsId;
        if("" != $extendedCode) $item->extendedCode = $extendedCode;    
        
        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_SEND_PERSONALITY_SMS_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }   

    public function getReport($number = 0, $validPeriodtime= 120)
    {   


        $item = new stdClass();
        /* 选填内容 */ 
        if(0 != $number)    $item->number    = $number;

        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_GETREPORT_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }  

    public function getMo($number = 0, $validPeriodtime= 120)
    {   
        $item = new stdClass();
        /* 选填内容 */ 
        if(0 != $number)    $item->number    = $number;

        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_GETMO_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }  

    public function getBalance($validPeriodtime= 120)
    {   
        $item = new stdClass();

        $item->requestTime = $this->getMillisecond();
        $item->requestValidPeriod = $validPeriodtime;
        
        $json_data = json_encode($item, JSON_UNESCAPED_UNICODE);

        $senddata = $this->encrypt($json_data);//加密结果
        
        $url = YM_SMS_ADDR.YM_SMS_GETBALANCE_URI;
        $resobj = $this->http_request($url, $senddata);
        $resobj->plaintext = $this->decrypt($resobj->ciphertext);

        return $resobj;
    }
} 

?>
