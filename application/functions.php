<?php
/**
 * 扩展函数相关
 *
 * @author SUL1SS <617746883@QQ.com>
 */
/*==================================*/
/**
 * 加载静态资源
 *
 * @param string $file 所要加载的资源
 */
if ( ! function_exists('loadStatic'))
{
    function loadStatic($file)
    {
        $realFile = ROOT_PATH . $file;
        if( ! file_exists($realFile)) return '';
        $filemtime = filemtime($realFile);
        return ROOT_PATH . $file.'?v='.$filemtime;
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装） 
 * @return mixed
 */
if ( ! function_exists('get_client_ip'))
{
	function get_client_ip($type = 0,$adv=false) {
	    $type       =  $type ? 1 : 0;
	    static $ip  =   NULL;
	    if ($ip !== NULL) return $ip[$type];
	    if($adv){
	        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	            $pos    =   array_search('unknown',$arr);
	            if(false !== $pos) unset($arr[$pos]);
	            $ip     =   trim($arr[0]);
	        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
	            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
	        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	            $ip     =   $_SERVER['REMOTE_ADDR'];
	        }
	    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
	        $ip     =   $_SERVER['REMOTE_ADDR'];
	    }
	    // IP地址合法验证
	    $long = sprintf("%u",ip2long($ip));
	    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
	    return $ip[$type];
	}
}

/**
 * 获取当前域名
 * 包含http/https
 **/ 
if ( ! function_exists('getHttpHost'))
{
	function getHttpHost()
	{
	    $url = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
	    return $url .= $_SERVER['HTTP_HOST'];
	}	
}

if ( ! function_exists('referer'))
{
	function referer($default = '') {
		$reurl = parse_url($_SERVER['HTTP_REFERER']);

		if (!empty($reurl['host']) && !in_array($reurl['host'], array($_SERVER['HTTP_HOST'], 'www.' . $_SERVER['HTTP_HOST'])) && !in_array($_SERVER['HTTP_HOST'], array($reurl['host'], 'www.' . $reurl['host']))) {
			return $_SERVER['HTTP_HOST'];
		} elseif (empty($reurl['host'])) {
			return $_SERVER['HTTP_REFERER'];
		}
		return strip_tags($_SERVER['HTTP_REFERER']);
	}
}

/**
 * 获取图片完整路径
 *
 * @param string $file 所要加载的资源
 */
if ( ! function_exists('tomedia'))
{
	function tomedia($src, $local_path = false)
	{
		if (empty($src)) {
			return '';
		}
		$host = getHttpHost();
		if (strexists($src, $host)) {
			return $src;
		}
		$t = strtolower($src);
		if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
			return $src;
		}
		if ($local_path && file_exists(ROOT_PATH . '/public/attachment' . DS . $src)) {
			$src = ROOT_PATH . '/public/attachment' . DS . $src;
		} else {
			$src = $host . $src;
		}
		return $src;
	}
}

if (!function_exists('cut_str')) {
	function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
	{
		if ($code == 'UTF-8') {
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);

			if ($sublen < (count($t_string[0]) - $start)) {
				return join('', array_slice($t_string[0], $start, $sublen));
			}

			return join('', array_slice($t_string[0], $start, $sublen));
		}

		$start = $start * 2;
		$sublen = $sublen * 2;
		$strlen = strlen($string);
		$tmpstr = '';
		$i = 0;

		while ($i < $strlen) {
			if (($start <= $i) && ($i < ($start + $sublen))) {
				if (129 < ord(substr($string, $i, 1))) {
					$tmpstr .= substr($string, $i, 2);
				}
				else {
					$tmpstr .= substr($string, $i, 1);
				}
			}

			if (129 < ord(substr($string, $i, 1))) {
				++$i;
			}

			++$i;
		}

		return $tmpstr;
	}
}

if (!function_exists('set_medias')) {
	function set_medias($list = array(), $fields = NULL)
	{
		if (empty($list)) {
			return array();
		}

		if (empty($fields)) {
			foreach ($list as &$row) {
				$row = tomedia($row);
			}
			return $list;
		}

		if (!is_array($fields)) {
			$fields = explode(',', $fields);
		}

		if (is_array2($list)) {
			foreach ($list as $key => &$value) {
				foreach ($fields as $field) {
					if (isset($list[$field])) {
						$list[$field] = tomedia($list[$field]);
					}
					if (is_array($value) && isset($value[$field])) {
						$value[$field] = tomedia($value[$field]);
					}
				}
			}
			return $list;
		}

		foreach ($fields as $field) {
			if (isset($list[$field])) {
				$list[$field] = tomedia($list[$field]);
			}
		}
		return $list;
	}
}

if (!function_exists('is_array2')) {
	function is_array2($array)
	{
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				return is_array($v);
			}
			return false;
		}
		return false;
	}
}

if (!function_exists('array2xml')) {
	function array2xml($arr, $level = 1) {
		$s = $level == 1 ? "<xml>" : '';
		foreach ($arr as $tagname => $value) {
			if (is_numeric($tagname)) {
				$tagname = $value['TagName'];
				unset($value['TagName']);
			}
			if (!is_array($value)) {
				$s .= "<{$tagname}>" . (!is_numeric($value) ? '<![CDATA[' : '') . $value . (!is_numeric($value) ? ']]>' : '') . "</{$tagname}>";
			} else {
				$s .= "<{$tagname}>" . array2xml($value, $level + 1) . "</{$tagname}>";
			}
		}
		$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		return $level == 1 ? $s . "</xml>" : $s;
	}
}

function strexists($string, $find) {
	return !(strpos($string, $find) === FALSE);
}

/**
 * 适用于url的base64加密
 */
if( ! function_exists('base64url_encode') )
{
    function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
    } 
}

/**
 * 适用于url的base64解密
 */
if( ! function_exists('base64url_decode') )
{
    function base64url_decode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
    } 
}

/**
 * 二维数组的排序
 *
 * @param array $arr 所要排序的数组
 * @param string $keys 以哪个key来做排序
 * @param string $type desc|asc
 */
if ( ! function_exists('arraySort'))
{
    function arraySort($arr,$keys,$type='asc')
    {
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v)
        {
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc')
        {
            asort($keysvalue);
        }
        else
        {
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach($keysvalue as $k=>$v)
        {
            $new_array[$k] = $arr[$k];
        }
        $arr = array();
        foreach($new_array as $key => $val)
        {
            $arr[] = $val;
        }
        return $arr; 
    }
}

/**
 * 时间人性化
 *
 * @param int $time 写作的时间
 * @return string
 */
if( ! function_exists('showWriteTime'))
{
    function showWriteTime($time)
    {
        $interval = time() - $time;
        $format = array(
            '31536000'  => '年',
            '2592000'   => '个月',
            '604800'    => '星期',
            '86400'     => '天',
            '3600'      => '小时',
            '60'        => '分钟',
            '1'         => '秒'
        );
        foreach($format as $key => $value)
        {
            $match = floor($interval / (int) $key );
            if(0 != $match)
            {
                return $match . $value . '前';
            }
        }
        return date('Y-m-d', $time);
    }
}

/**
 * 返回json
 *
 * @param string $msg 返回的消息
 * @param boolean $status 是否成功
 */
if (!function_exists('show_json')) {
	function show_json($status = 1, $return = NULL)
	{
		$ret = array('status' => $status, 'result' => array());

		if (!is_array($return)) {
			if ($return) {
				$ret['result']['message'] = $return;
			}
		} else {
			$ret['result'] = $return;
		}
		
		if (isset($return['url'])) {
			$ret['result']['url'] = $return['url'];
		} else {
			if ($status == 1) {
				$ret['result']['url'] = $_SERVER['HTTP_REFERER'];
			}
		}
		exit(json_encode($ret));
	}
}

if (!function_exists('is_error')) {
	function is_error($data) {
		if (empty($data) || !is_array($data) || !array_key_exists('errno', $data) || (array_key_exists('errno', $data) && $data['errno'] != 1)) {
			return false;
		} else {
			return true;
		}
	}
}

if (!function_exists('errormsg')) {
	function errormsg($errno, $message = '') {
		return array(
			'errno' => $errno,
			'message' => $message
		);
	}
}

/**
 * 转化 \ 为 /
 * 
 * @param    string  $path   路径
 * @return   string  路径
 */
if( ! function_exists('dir_path') )
{
    function dir_path($path)
    {
        $path = str_replace('\\', '/', $path);
        if(substr($path, -1) != '/') $path = $path.'/';
        return $path;
    }

}

/**
 * 创建目录
 * 
 * @param    string  $path   路径
 * @param    string  $mode   属性
 * @return   string  如果已经存在则返回true，否则为flase
 */
if( ! function_exists('dir_create') )
{
    function dir_create($path, $mode = 0777)
    {
        if(is_dir($path)) return TRUE;
        $ftp_enable = 0;
        $path = dir_path($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for($i=0; $i<$max; $i++)
        {
            $cur_dir .= $temp[$i].'/';
            if (@is_dir($cur_dir)) continue;
            @mkdir($cur_dir, 0777,true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }
}

/**
 * 根据后缀来简单的判断是不是图片
 * 
 * @return boolean
 */
if( ! function_exists('isImage') )
{
    function isImage($ext)
    {
        $imageExt = 'jpg|gif|png|bmp|jpeg';
        if( ! in_array($ext, explode('|', $imageExt))) return false;
        return true;
    }
}

/**
 * 加密函数
 *
 * @param string $string 所要加密的字符
 * @param string $operation 加密还是解密
 * @param string $key 加密所要的key
 * @param string $expiry 生存时间
 */
if( ! function_exists('cryptcode'))
{
    function cryptcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        return App\Libraries\Crypt::cryptcode($string, $operation, $key, $expiry);
    }
}

/**
 * 主要用于url参数的加密
 *
 * @param string $string 所要加密的字符
 */
if( ! function_exists('url_param_encode'))
{
    function url_param_encode($string)
    {
        return base64url_encode(cryptcode($string, 'ENCODE'));
    }
}

/**
 * 主要用于url参数的解密
 *
 * @param string $string 所要解密的字符
 */
if( ! function_exists('url_param_decode'))
{
    function url_param_decode($string)
    {
        return cryptcode(base64url_decode($string), 'DECODE');
    }
}

/**
 * 主要用于防止表单篡改
 *
 * @param void $data 所要验证的数据，必须以最后提交的数据的数据结构一致。
 */
if( ! function_exists('form_hash'))
{
    function form_hash($data)
    {
        return (new App\Services\Formhash())->hash($data);
    }
}

/**
 * @param $url
 * 获取url扩展名
 */
if( ! function_exists('getExt') )
{
    function getExt($url)
	{
	    $arr = parse_url($url);
	    $file = basename($arr['path']);
	    $ext = explode(".",$file);
	    return $ext;
	}
}

/**
 * @param $value
 * 序列化数组
 */
if( ! function_exists('iserializer') )
{
	function iserializer($value) {
		return serialize($value);
	}
}

/**
 * @param $value
 * 反序列化数组
 */
if( ! function_exists('iunserializer') )
{
	function iunserializer($value) {
		if (empty($value)) {
			return array();
		}
		if (!is_serialized($value)) {
			return $value;
		}
		$result = unserialize($value);
		if ($result === false) {
			$temp = preg_replace_callback('!s:(\d+):"(.*?)";!s', function ($matchs){
				return 's:'.strlen($matchs[2]).':"'.$matchs[2].'";';
			}, $value);
			return unserialize($temp);
		} else {
			return $result;
		}
	}
}

if( ! function_exists('is_serialized') )
{
	function is_serialized($data, $strict = true) {
		if (!is_string($data)) {
			return false;
		}
		$data = trim($data);
		if ('N;' == $data) {
			return true;
		}
		if (strlen($data) < 4) {
			return false;
		}
		if (':' !== $data[1]) {
			return false;
		}
		if ($strict) {
			$lastc = substr($data, -1);
			if (';' !== $lastc && '}' !== $lastc) {
				return false;
			}
		} else {
			$semicolon = strpos($data, ';');
			$brace = strpos($data, '}');
					if (false === $semicolon && false === $brace)
				return false;
					if (false !== $semicolon && $semicolon < 3)
				return false;
			if (false !== $brace && $brace < 4)
				return false;
		}
		$token = $data[0];
		switch ($token) {
			case 's' :
				if ($strict) {
					if ('"' !== substr($data, -2, 1)) {
						return false;
					}
				} elseif (false === strpos($data, '"')) {
					return false;
				}
					case 'a' :
			case 'O' :
				return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
		}
		return false;
	}
}

/**
 * @param 
 * 获取ip
 */
if( ! function_exists('getip') )
{
	function getip() {
		static $ip = '';
		$ip = $_SERVER['REMOTE_ADDR'];
		if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
			return $ip;
		} else {
			return '127.0.0.1';
		}
	}
}

/**
 * @param $length 长度
 * @param $numeric 纯数字
 * 获取指定长度随机字符串
 */
if( ! function_exists('random') )
{
	function random($length, $numeric = FALSE) {
		$seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
		$seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
		if ($numeric) {
			$hash = '';
		} else {
			$hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
			$length--;
		}
		$max = strlen($seed) - 1;
		for ($i = 0; $i < $length; $i++) {
			$hash .= $seed{mt_rand(0, $max)};
		}
		return $hash;
	}
}

/**
 * 检查是否手机等移动设备
 */
if (!function_exists('is_mobile')) {
	function is_mobile()
	{
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/(android|bb\\d+|meego).+mobile|avantgo|bada\\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\\-(n|u)|c55\\/|capi|ccwa|cdm\\-|cell|chtm|cldc|cmd\\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\\-s|devi|dica|dmob|do(c|p)o|ds(12|\\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\\-|_)|g1 u|g560|gene|gf\\-5|g\\-mo|go(\\.w|od)|gr(ad|un)|haie|hcit|hd\\-(m|p|t)|hei\\-|hi(pt|ta)|hp( i|ip)|hs\\-c|ht(c(\\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\\-(20|go|ma)|i230|iac( |\\-|\\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\\/)|klon|kpt |kwc\\-|kyo(c|k)|le(no|xi)|lg( g|\\/(k|l|u)|50|54|\\-[a-w])|libw|lynx|m1\\-w|m3ga|m50\\/|ma(te|ui|xo)|mc(01|21|ca)|m\\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\\-2|po(ck|rt|se)|prox|psio|pt\\-g|qa\\-a|qc(07|12|21|32|60|\\-[2-7]|i\\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\\-|oo|p\\-)|sdk\\/|se(c(\\-|0|1)|47|mc|nd|ri)|sgh\\-|shar|sie(\\-|m)|sk\\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\\-|v\\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\\-|tdg\\-|tel(i|m)|tim\\-|t\\-mo|to(pl|sh)|ts(70|m\\-|m3|m5)|tx\\-9|up(\\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\\-|your|zeto|zte\\-/i', substr($useragent, 0, 4))) {
			return true;
		}
		return false;
	}
}

/**
 * 检查是否微信
 */
if (!function_exists('is_weixin')) {
	function is_weixin()
	{
		if (empty($_SERVER['HTTP_USER_AGENT']) || ((strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') === false) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Windows Phone') === false))) {
			return false;
		}

		return true;
	}
}

/**
 * @param $price 价格
 * 价格格式输出
 */
if (!function_exists('price_format')) {
	function price_format($price)
	{
		$prices = explode('.', $price);

		if (intval($prices[1]) <= 0) {
			$price = $prices[0];
		}
		else {
			if (isset($prices[1][1]) && ($prices[1][1] <= 0)) {
				$price = $prices[0] . '.' . $prices[1][0];
			}
		}
		return $price;
	}
}

/**
 * 单个音频上传
 * @param        $name
 * @param string $value
 * @param array  $options
 *
 * @return string
 */
if (!function_exists('tpl_form_field_audio')) {
	function tpl_form_field_audio($name, $value = '', $options = array()) {
	    if (!is_array($options)) {
	        $options = array();
	    }
	    $options['direct'] = true;
	    $options['multiple'] = false;
	    $options['fileSizeLimit'] = config('UploadFile.audioFileSize');
	    if(empty($options['extras'])) {
	    	$options['extras']['text'] = '';
	    	$options['extras']['image'] = '';
	    }
	    $s = '';
	    if (!defined('TPL_INIT_AUDIO')) {
	        $s = '
	<script type="text/javascript">
		function showAudioDialog(elm, base64options, options) {
			require(["util"], function(util){
				var btn = $(elm);
				var ipt = btn.parent().prev();
				var val = ipt.val();
				util.audio(val, function(url){
					if(url && url.attachment && url.url){
						btn.prev().show();
						ipt.val(url.attachment);
						ipt.attr("filename",url.filename);
						ipt.attr("url",url.url);
						setAudioPlayer();
					}
					if(url && url.media_id){
						ipt.val(url.media_id);
					}
				}, "" , ' . json_encode($options) . ');
			});
		}

		function setAudioPlayer(){
			require(["jquery", "util", "jquery.jplayer"], function($, u){
				$(function(){
					$(".audio-player").each(function(){
						$(this).prev().find("button").eq(0).click(function(){
							var src = $(this).parent().prev().val();
							if($(this).find("i").hasClass("fa-stop")) {
								$(this).parent().parent().next().jPlayer("stop");
							} else {
								if(src) {
									$(this).parent().parent().next().jPlayer("setMedia", {mp3: u.tomedia(src)}).jPlayer("play");
								}
							}
						});
					});

					$(".audio-player").jPlayer({
						playing: function() {
							$(this).prev().find("i").removeClass("fa-play").addClass("fa-stop");
						},
						pause: function (event) {
							$(this).prev().find("i").removeClass("fa-stop").addClass("fa-play");
						},
						swfPath: "resource/components/jplayer",
						supplied: "mp3"
					});
					$(".audio-player-media").each(function(){
						$(this).next().find(".audio-player-play").css("display", $(this).val() == "" ? "none" : "");
					});
				});
			});
		}
		setAudioPlayer();
	</script>';
	        echo $s;
	        define('TPL_INIT_AUDIO', true);
	    }
	    $s .= '
		<div class="input-group">
			<input type="text" value="' . $value . '" name="' . $name . '" class="form-control audio-player-media" autocomplete="off" ' . ($options['extras']['text'] ? $options['extras']['text'] : '') . '>
			<span class="input-group-btn">
				<button class="btn btn-default audio-player-play" type="button" style="display:none;"><i class="fa fa-play"></i></button>
				<button class="btn btn-default" type="button" onclick="showAudioDialog(this, \'' . base64_encode(iserializer($options)) . '\',' . str_replace('"', '\'', json_encode($options)) . ');">选择媒体文件</button>
			</span>
		</div>
		<div class="input-group audio-player"></div>';
	    return $s;
	}
}

/**
 * @param $name 长度
 * @param $value 纯数字
 * @param $default 纯数字
 * @param $options 纯数字
 * 获取指定长度随机字符串
 */
if (!function_exists('tpl_form_field_image2')) {
	function tpl_form_field_image2($name, $value = '', $default = '', $options = array())
	{
		if (empty($default)) {
			$default = '/public/static/images/nopic.png';
		}

		$val = $default;

		if (!empty($value)) {
			$val = tomedia($value);
		}
		else {
			$val = '/public/static/images/default-pic.jpg';
		}

		if (!empty($options['global'])) {
			$options['global'] = true;
		}
		else {
			$options['global'] = false;
		}

		if (empty($options['class_extra'])) {
			$options['class_extra'] = '';
		}

		if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
			if (!preg_match('/^\\w+([\\/]\\w+)?$/i', $options['dest_dir'])) {
				exit('图片上传目录错误,只能指定最多两级目录,如: "public","public/images"');
			}
		}

		$options['direct'] = true;
		$options['multiple'] = false;

		if (isset($options['thumb'])) {
			$options['thumb'] = !empty($options['thumb']);
		}

		$options['fileSizeLimit'] = 3 * 1024;
		$s = '';

		if (!defined('TPL_INIT_IMAGE')) {
			$s = "\r\n\t\t<script type=\"text/javascript\">\r\n\t\t\tfunction showImageDialog(elm, opts, options) {\r\n\t\t\t\trequire([\"util\"], function(util){\r\n\t\t\t\t\tvar btn = \$(elm);\r\n\t\t\t\t\tvar ipt = btn.parent().prev();\r\n\t\t\t\t\tvar val = ipt.val();\r\n\t\t\t\t\tvar img = ipt.parent().next().children();\r\n\t\t\t\t\toptions = " . str_replace('"', '\'', json_encode($options)) . ";\r\n\t\t\t\t\tutil.image(val, function(url){\r\n\t\t\t\t\t\tif(url.url){\r\n\t\t\t\t\t\t\tif(img.length > 0){\r\n\t\t\t\t\t\t\t\timg.get(0).src = url.url;\r\n\t\t\t\t\t\t\t\timg.closest(\".input-group\").show();\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\tipt.val(url.attachment);\r\n\t\t\t\t\t\t\tipt.attr(\"filename\",url.filename);\r\n\t\t\t\t\t\t\tipt.attr(\"url\",url.url);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t\tif(url.media_id){\r\n\t\t\t\t\t\t\tif(img.length > 0){\r\n\t\t\t\t\t\t\t\timg.get(0).src = \"\";\r\n\t\t\t\t\t\t\t}\r\n\t\t\t\t\t\t\tipt.val(url.media_id);\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t}, options);\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t\tfunction deleteImage(elm){\r\n\t\t\t\trequire([\"jquery\"], function(\$){\r\n\t\t\t\t\t\$(elm).prev().attr(\"src\", \"/public/static/images/default-pic.jpg\");\r\n\t\t\t\t\t\$(elm).parent().prev().find(\"input\").val(\"\");\r\n\t\t\t\t});\r\n\t\t\t}\r\n\t\t</script>";
			define('TPL_INIT_IMAGE', true);
		}

		$s .= "\r\n\t\t<div class=\"input-group " . $options['class_extra'] . "\">\r\n\t\t\t<input type=\"text\" name=\"" . $name . '" value="' . $value . '"' . ($options['extras']['text'] ? $options['extras']['text'] : '') . " class=\"form-control ignore\" autocomplete=\"off\">\r\n\t\t\t<span class=\"input-group-btn\">\r\n\t\t\t\t<button class=\"btn btn-primary\" type=\"button\" onclick=\"showImageDialog(this);\">选择图片</button>\r\n\t\t\t</span>\r\n\t\t</div>";
		$s .= '<div class="input-group ' . $options['class_extra'] . '" style="margin-top:.5em;"><img src="' . $val . '" onerror="this.src=\'' . $default . '\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail" ' . ($options['extras']['image'] ? $options['extras']['image'] : '') . " width=\"150\" />\r\n                <em class=\"close\" style=\"position:absolute; top: 0px; right: -14px;\" title=\"删除这张图片\" onclick=\"deleteImage(this)\">×</em>\r\n            </div>";
		return $s;
	}
}

if (!function_exists('tpl_form_field_multi_image2')) {
	function tpl_form_field_multi_image2($name, $value = array(), $options = array())
	{
		$options['multiple'] = true;
		$options['direct'] = false;
		$options['fileSizeLimit'] = 3 * 1024;
		if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
			if (!preg_match('/^\\w+([\\/]\\w+)?$/i', $options['dest_dir'])) {
				exit('图片上传目录错误,只能指定最多两级目录,如: "we7_store","we7_store/d1"');
			}
		}

		$s = '';

		if (!defined('TPL_INIT_MULTI_IMAGE')) {
			$s = "\r\n<script type=\"text/javascript\">\r\n\tfunction uploadMultiImage(elm) {\r\n\t\tvar name = \$(elm).next().val();\r\n\t\tutil.image( \"\", function(urls){\r\n\t\t\t\$.each(urls, function(idx, url){\r\n\t\t\t\t\$(elm).parent().parent().next().append('<div class=\"multi-item\"><img onerror=\"this.src=\\'/public/static/images/nopic.png\\'; this.title=\\'图片未找到.\\'\" src=\"'+url.url+'\" class=\"img-responsive img-thumbnail\"><input type=\"hidden\" name=\"'+name+'[]\" value=\"'+url.attachment+'\"><em class=\"close\" title=\"删除这张图片\" onclick=\"deleteMultiImage(this)\">×</em></div>');\r\n\t\t\t});\r\n\t\t}, " . json_encode($options) . ");\r\n\t}\r\n\tfunction deleteMultiImage(elm){\r\n\t\trequire([\"jquery\"], function(\$){\r\n\t\t\t\$(elm).parent().remove();\r\n\t\t});\r\n\t}\r\n</script>";
			define('TPL_INIT_MULTI_IMAGE', true);
		}

		$s .= "<div class=\"input-group\">\r\n\t<input type=\"text\" class=\"form-control\" readonly=\"readonly\" value=\"\" placeholder=\"批量上传图片\" autocomplete=\"off\">\r\n\t<span class=\"input-group-btn\">\r\n\t\t<button class=\"btn btn-primary\" type=\"button\" onclick=\"uploadMultiImage(this);\">选择图片</button>\r\n\t\t<input type=\"hidden\" value=\"" . $name . "\" />\r\n\t</span>\r\n</div>\r\n<div class=\"input-group multi-img-details\">";
		if (is_array($value) && (0 < count($value))) {
			foreach ($value as $row) {
				$s .= "\r\n<div class=\"multi-item\">\r\n\t<img src=\"" . tomedia($row) . "\" onerror=\"this.src='/public/static/images/nopic.png'; this.title='图片未找到.'\" class=\"img-responsive img-thumbnail\">\r\n\t<input type=\"hidden\" name=\"" . $name . '[]" value="' . $row . "\" >\r\n\t<em class=\"close\" title=\"删除这张图片\" onclick=\"deleteMultiImage(this)\">×</em>\r\n</div>";
			}
		}

		$s .= '</div>';
		return $s;
	}
}

if (!(function_exists('tpl_form_field_video2'))) {
	function tpl_form_field_video2($name, $value = '', $options = array())
	{
		$options['btntext'] = ((!(empty($options['btntext'])) ? $options['btntext'] : '选择视频'));

		if ($options['disabled']) {
			$options['readonly'] = true;
		}


		$html = '';
		$html .= '<div class="input-group"';

		if ($options['disabled']) {
			$html .= ' style="width: 100%;"';
		}


		$html .= '><input class="form-control" id="select-video-' . $name . '" name="' . $name . '" value="' . $value . '" placeholder="' . $options['placeholder'] . '"';

		if ($options['readonly']) {
			$html .= ' readonly="readonly"';
		}


		$html .= '/>';

		if (!($options['disabled'])) {
			$html .= '<span class="input-group-addon btn btn-primary" data-toggle="selectVideo" data-input="#select-video-' . $name . '" data-network="' . $options['network'] . '">' . $options['btntext'] . '</span>';
		}


		$html .= '</div>';
		$html .= '<div class="input-group"><div class="multi-item" style="display: block" title="预览视频" data-toggle="previewVideo" data-input="#select-video-' . $name . '"><div class="img-responsive img-thumbnail img-video" style="width: 100px; height: 100px; position: relative; text-align: center; cursor: pointer;" src=""><i class="fa fa-play-circle" style="font-size: 60px; line-height: 90px; color: #999;"></i></div>';

		if (!($options['disabled'])) {
			$html .= '<em class="close" title="移除视频" data-toggle="previewVideoDel" data-element="#select-video-' . $name . '">×</em>';
		}


		$html .= '</div></div>';
		return $html;
	}
}

if (!(function_exists('tpl_form_field_daterange'))) {
	function tpl_form_field_daterange($name, $value = array(), $time = false)
	{
		$s = '';
		if (empty($time) && !(defined('TPL_INIT_DATERANGE_DATE'))) {
			$s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'myrequire(["daterangepicker"], function(){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-date").each(function(){' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\t" . 'startDate: $(elm).prev().prev().val(),' . "\r\n\t\t\t\t\t" . 'endDate: $(elm).prev().val(),' . "\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD"' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateStr() + " 至 " + end.toDateStr());' . "\r\n\t\t\t\t\t" . '$(elm).prev().prev().val(start.toDateStr());' . "\r\n\t\t\t\t\t" . '$(elm).prev().val(end.toDateStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '</script>' . "\r\n";
			define('TPL_INIT_DATERANGE_DATE', true);
		}


		if (!(empty($time)) && !(defined('TPL_INIT_DATERANGE_TIME'))) {
			$s = "\r\n" . '<script type="text/javascript">' . "\r\n\t" . 'myrequire(["daterangepicker"], function(){' . "\r\n\t\t" . '$(function(){' . "\r\n\t\t\t" . '$(".daterange.daterange-time").each(function(){' . "\r\n\t\t\t\t" . 'var elm = this;' . "\r\n\t\t\t\t" . '$(this).daterangepicker({' . "\r\n\t\t\t\t\t" . 'startDate: $(elm).prev().prev().val(),' . "\r\n\t\t\t\t\t" . 'endDate: $(elm).prev().val(),' . "\r\n\t\t\t\t\t" . 'format: "YYYY-MM-DD HH:mm",' . "\r\n\t\t\t\t\t" . 'timePicker: true,' . "\r\n\t\t\t\t\t" . 'timePicker12Hour : false,' . "\r\n\t\t\t\t\t" . 'timePickerIncrement: 1,' . "\r\n\t\t\t\t\t" . 'minuteStep: 1' . "\r\n\t\t\t\t" . '}, function(start, end){' . "\r\n\t\t\t\t\t" . '$(elm).find(".date-title").html(start.toDateTimeStr() + " 至 " + end.toDateTimeStr());' . "\r\n\t\t\t\t\t" . '$(elm).prev().prev().val(start.toDateTimeStr());' . "\r\n\t\t\t\t\t" . '$(elm).prev().val(end.toDateTimeStr());' . "\r\n\t\t\t\t" . '});' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '});' . "\r\n\t" . '});' . "\r\n" . '</script>' . "\r\n";
			define('TPL_INIT_DATERANGE_TIME', true);
		}


		if (($value['starttime'] !== false) && ($value['start'] !== false)) {
			if ($value['start']) {
				$value['starttime'] = ((empty($time) ? date('Y-m-d', strtotime($value['start'])) : date('Y-m-d H:i', strtotime($value['start']))));
			}


			$value['starttime'] = ((empty($value['starttime']) ? ((empty($time) ? date('Y-m-d') : date('Y-m-d H:i'))) : $value['starttime']));
		}
		 else {
			$value['starttime'] = '请选择';
		}

		if (($value['endtime'] !== false) && ($value['end'] !== false)) {
			if ($value['end']) {
				$value['endtime'] = ((empty($time) ? date('Y-m-d', strtotime($value['end'])) : date('Y-m-d H:i', strtotime($value['end']))));
			}


			$value['endtime'] = ((empty($value['endtime']) ? $value['starttime'] : $value['endtime']));
		}
		 else {
			$value['endtime'] = '请选择';
		}

		$s .= "\r\n\t" . '<input name="' . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . '" />' . "\r\n\t" . '<input name="' . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . '" />' . "\r\n\t" . '<button class="btn btn-default daterange ' . ((!(empty($time)) ? 'daterange-time' : 'daterange-date')) . '" type="button"><span class="date-title">' . $value['starttime'] . ' 至 ' . $value['endtime'] . '</span> <i class="fa fa-calendar"></i></button>' . "\r\n\t";
		return $s;
	}
}

if (!function_exists('tpl_daterange')) {
	function tpl_daterange($name, $value = array(), $time = false)
	{
		$placeholder = (isset($value['placeholder']) ? $value['placeholder'] : '');
		$s = '';
		if (empty($time) && !defined('TPL_INIT_DATERANGE_DATE')) {
			$s = "\r\n<script type=\"text/javascript\">\r\n\trequire([\"daterangepicker\"], function(){\r\n\t\t\$(function(){\r\n\t\t\t\$(\".daterange.daterange-date\").each(function(){\r\n\t\t\t\tvar elm = this;\r\n                var container =\$(elm).parent().prev();\r\n\t\t\t\t\$(this).daterangepicker({\r\n\t\t\t\t\tformat: \"YYYY-MM-DD\"\r\n\t\t\t\t}, function(start, end){\r\n\t\t\t\t\t\$(elm).find(\".date-title\").html(start.toDateStr() + \" 至 \" + end.toDateStr());\r\n\t\t\t\t\tcontainer.find(\":input:first\").val(start.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:last\").val(end.toDateTimeStr());\r\n\t\t\t\t});\r\n\t\t\t});\r\n\t\t});\r\n\t});\r\n</script> \r\n";
			define('TPL_INIT_DATERANGE_DATE', true);
		}

		if (!empty($time) && !defined('TPL_INIT_DATERANGE_TIME')) {
			$s = "\r\n<script type=\"text/javascript\">\r\n\trequire([\"daterangepicker\"], function(){\r\n\t\t\$(function(){\r\n\t\t\t\$(\".daterange.daterange-time\").each(function(){\r\n\t\t\t\tvar elm = this;\r\n                 var container =\$(elm).parent().prev();\r\n\t\t\t\t\$(this).daterangepicker({\r\n\t\t\t\t\tformat: \"YYYY-MM-DD HH:mm\",\r\n\t\t\t\t\ttimePicker: true,\r\n\t\t\t\t\ttimePicker12Hour : false,\r\n\t\t\t\t\ttimePickerIncrement: 1,\r\n\t\t\t\t\tminuteStep: 1\r\n\t\t\t\t}, function(start, end){\r\n\t\t\t\t\t\$(elm).find(\".date-title\").html(start.toDateTimeStr() + \" 至 \" + end.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:first\").val(start.toDateTimeStr());\r\n\t\t\t\t\tcontainer.find(\":input:last\").val(end.toDateTimeStr());\r\n\t\t\t\t});\r\n\t\t\t});\r\n\t\t});\r\n\t});\r\n     function clearTime(obj){\r\n              \$(obj).prev().html(\"<span class=date-title>\" + \$(obj).attr(\"placeholder\") + \"</span>\");\r\n              \$(obj).parent().prev().find(\"input\").val(\"\");\r\n    }\r\n</script>\r\n";
			define('TPL_INIT_DATERANGE_TIME', true);
		}

		$str = $placeholder;
		$small = (isset($value['sm']) ? $value['sm'] : true);
		$value['starttime'] = isset($value['starttime']) ? $value['starttime'] : ($_GET[$name]['start'] ? $_GET[$name]['start'] : '');
		$value['endtime'] = isset($value['endtime']) ? $value['endtime'] : ($_GET[$name]['end'] ? $_GET[$name]['end'] : '');
		if ($value['starttime'] && $value['endtime']) {
			if (empty($time)) {
				$str = date('Y-m-d', strtotime($value['starttime'])) . '至 ' . date('Y-m-d', strtotime($value['endtime']));
			}
			else {
				$str = date('Y-m-d H:i', strtotime($value['starttime'])) . ' 至 ' . date('Y-m-d  H:i', strtotime($value['endtime']));
			}
		}

		$s .= "<div style=\"float:left\">\r\n\t<input name=\"" . $name . '[start]' . '" type="hidden" value="' . $value['starttime'] . "\" />\r\n\t<input name=\"" . $name . '[end]' . '" type="hidden" value="' . $value['endtime'] . "\" />\r\n           </div>\r\n          <div class=\"btn-group " . ($small ? 'btn-group-sm' : '') . '" style="' . $value['style'] . "padding-right:0;\"  >\r\n          \r\n\t<button style=\"width:240px\" class=\"btn btn-default daterange " . (!empty($time) ? 'daterange-time' : 'daterange-date') . '"  type="button"><span class="date-title">' . $str . "</span></button>\r\n        <button class=\"btn btn-default " . ($small ? 'btn-sm' : '') . '" " type="button" onclick="clearTime(this)" placeholder="' . $placeholder . "\"><i class=\"fa fa-remove\"></i></button>\r\n         </div>\r\n\t";
		return $s;
	}
}

if (!(function_exists('tpl_form_field_date'))) {
	function tpl_form_field_date($name, $value = '', $withtime = false)
	{
		$s = '';
		$withtime = ((empty($withtime) ? false : true));

		if (!(empty($value))) {
			$value = ((strexists($value, '-') ? strtotime($value) : $value));
		}
		 else {
			$value = TIMESTAMP;
		}

		$value = (($withtime ? date('Y-m-d H:i:s', $value) : date('Y-m-d', $value)));
		$s .= '<input type="text" name="' . $name . '"  value="' . $value . '" placeholder="请选择日期时间" readonly="readonly" class="datetimepicker form-control" style="padding-left:12px;" />';
		$s .= "\r\n\t\t" . '<script type="text/javascript">' . "\r\n\t\t\t" . 'myrequire(["datetimepicker"], function(){' . "\r\n\t\t\t\t\t" . 'var option = {' . "\r\n\t\t\t\t\t\t" . 'lang : "zh",' . "\r\n\t\t\t\t\t\t" . 'step : 5,' . "\r\n\t\t\t\t\t\t" . 'timepicker : ' . ((!(empty($withtime)) ? 'true' : 'false')) . ',' . "\r\n\t\t\t\t\t\t" . 'closeOnDateSelect : true,' . "\r\n\t\t\t\t\t\t" . 'format : "Y-m-d' . ((!(empty($withtime)) ? ' H:i"' : '"')) . "\r\n\t\t\t\t\t" . '};' . "\r\n\t\t\t\t" . '$(".datetimepicker[name = \'' . $name . '\']").datetimepicker(option);' . "\r\n\t\t\t" . '});' . "\r\n\t\t" . '</script>';
		return $s;
	}
}

if (!function_exists('tpl_ueditor')) {
	function tpl_ueditor($id, $value = '', $options = array()) {
		$s = '';
		$options['height'] = empty($options['height']) ? 200 : $options['height'];
		$options['allow_upload_video'] = isset($options['allow_upload_video']) ? $options['allow_upload_video'] : true;
		$s .= !empty($id) ? "<textarea id=\"{$id}\" name=\"{$id}\" type=\"text/plain\" style=\"height:{$options['height']}px;\">{$value}</textarea>" : '';
		$s .= "
		<script type=\"text/javascript\">
			require(['util'], function(util){
				util.editor('" . ($id ? $id : "") . "', {
				height : {$options['height']}, 
				dest_dir : '" .($options['dest_dir'] ? $options['dest_dir'] : "") . "',
				image_limit : " . (3 * 1024) . ",
				allow_upload_video : " . ($options['allow_upload_video'] ? 'true' : 'false') . ",
				audio_limit : " . (5 * 1024) . ",
				callback : ''
				});
			});
		</script>";
		return $s;
	}
}

function tpl_form_field_coordinate($field, $value = array()) {
	$s = '';
	if(!defined('TPL_INIT_COORDINATE')) {
		$s .= '<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=F51571495f717ff1194de02366bb8da9&s=1"></script><script type="text/javascript">
				function showCoordinate(elm) {
					require(["util"], function(util){
						var val = {};
						val.lng = parseFloat($(elm).parent().prev().prev().find(":text").val());
						val.lat = parseFloat($(elm).parent().prev().find(":text").val());
						util.map(val, function(r){
							$(elm).parent().prev().prev().find(":text").val(r.lng);
							$(elm).parent().prev().find(":text").val(r.lat);
						});

					});
				}

			</script>';
		define('TPL_INIT_COORDINATE', true);
	}
	$s .= '
		<div class="row row-fix">
			<div class="col-xs-4 col-sm-4">
				<input type="text" name="' . $field . '[lng]" value="'.$value['lng'].'" placeholder="地理经度"  class="form-control" />
			</div>
			<div class="col-xs-4 col-sm-4">
				<input type="text" name="' . $field . '[lat]" value="'.$value['lat'].'" placeholder="地理纬度"  class="form-control" />
			</div>
			<div class="col-xs-4 col-sm-4">
				<button onclick="showCoordinate(this);" class="btn btn-default" type="button">选择坐标</button>
			</div>
		</div>';
	return $s;
}

if (!function_exists('tpl_form_field_image')) {
	function tpl_form_field_image($name, $value = '', $default = '', $options = array()) {
		if (empty($default)) {
			$default = '/public/static/images/nopic.jpg';
		}
		$val = $default;
		if (!empty($value)) {
			$val = tomedia($value);
		}
		if (!empty($options['global'])) {
			$options['global'] = true;
		} else {
			$options['global'] = false;
		}
		if (empty($options['class_extra'])) {
			$options['class_extra'] = '';
		}
		if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
			if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
				exit('图片上传目录错误,只能指定最多两级目录,如: "public","public/static"');
			}
		}
		$options['direct'] = true;
		$options['multiple'] = false;
		if (isset($options['thumb'])) {
			$options['thumb'] = !empty($options['thumb']);
		}
		$options['fileSizeLimit'] = intval($GLOBALS['_W']['setting']['upload']['image']['limit']) * 1024;
		$s = '';
		if (!defined('TPL_INIT_IMAGE')) {
			$s = '
			<script type="text/javascript">
				function showImageDialog(elm, opts, options) {
					require(["util"], function(util){
						var btn = $(elm);
						var ipt = btn.parent().prev();
						var val = ipt.val();
						var img = ipt.parent().next().children();
						options = '.str_replace('"', '\'', json_encode($options)).';
						util.image(val, function(url){
							if(url.url){
								if(img.length > 0){
									img.get(0).src = url.url;
								}
								ipt.val(url.attachment);
								ipt.attr("filename",url.filename);
								ipt.attr("url",url.url);
							}
							if(url.media_id){
								if(img.length > 0){
									img.get(0).src = "";
								}
								ipt.val(url.media_id);
							}
						}, options);
					});
				}
				function deleteImage(elm){
					$(elm).prev().attr("src", "/public/static/images/nopic.jpg");
					$(elm).parent().prev().find("input").val("");
				}
			</script>';
			define('TPL_INIT_IMAGE', true);
		}

		$s .= '
			<div class="input-group ' . $options['class_extra'] . '">
				<input type="text" name="' . $name . '" value="' . $value . '"' . ($options['extras']['text'] ? $options['extras']['text'] : '') . ' class="form-control" autocomplete="off">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="showImageDialog(this);">选择图片</button>
				</span>
			</div>
			<div class="input-group ' . $options['class_extra'] . '" style="margin-top:.5em;">
				<img src="' . $val . '" onerror="this.src=\'' . $default . '\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail" ' . ($options['extras']['image'] ? $options['extras']['image'] : '') . ' width="150" />
				<em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="deleteImage(this)">×</em>
			</div>';
		return $s;
	}
}

function tpl_form_field_multi_image($name, $value = array(), $options = array()) {
	$options['multiple'] = true;
	$options['direct'] = false;
	$options['fileSizeLimit'] = intval($GLOBALS['_W']['setting']['upload']['image']['limit']) * 1024;
	if (isset($options['dest_dir']) && !empty($options['dest_dir'])) {
		if (!preg_match('/^\w+([\/]\w+)?$/i', $options['dest_dir'])) {
			exit('图片上传目录错误,只能指定最多两级目录,如: "public","public/d1"');
		}
	}
	$s = '';
	if (!defined('TPL_INIT_MULTI_IMAGE')) {
		$s = '
<script type="text/javascript">
	function uploadMultiImage(elm) {
		var name = $(elm).next().val();
		util.image( "", function(urls){
			$.each(urls, function(idx, url){
				$(elm).parent().parent().next().append(\'<div class="multi-item"><img onerror="this.src=\\\'/public/static/images/nopic.jpg\\\'; this.title=\\\'图片未找到.\\\'" src="\'+url.url+\'" class="img-responsive img-thumbnail"><input type="hidden" name="\'+name+\'[]" value="\'+url.attachment+\'"><em class="close" title="删除这张图片" onclick="deleteMultiImage(this)">×</em></div>\');
			});
		}, ' . json_encode($options) . ');
	}
	function deleteMultiImage(elm){
		$(elm).parent().remove();
	}
</script>';
		define('TPL_INIT_MULTI_IMAGE', true);
	}

	$s .= <<<EOF
<div class="input-group">
	<input type="text" class="form-control" readonly="readonly" value="" placeholder="批量上传图片" autocomplete="off">
	<span class="input-group-btn">
		<button class="btn btn-default" type="button" onclick="uploadMultiImage(this);">选择图片</button>
		<input type="hidden" value="{$name}" />
	</span>
</div>
<div class="input-group multi-img-details">
EOF;
	if (is_array($value) && count($value) > 0) {
		foreach ($value as $row) {
			$s .= '
<div class="multi-item">
	<img src="' . tomedia($row) . '" onerror="this.src=\'/public/static/images/nopic.jpg\'; this.title=\'图片未找到.\'" class="img-responsive img-thumbnail">
	<input type="hidden" name="' . $name . '[]" value="' . $row . '" >
	<em class="close" title="删除这张图片" onclick="deleteMultiImage(this)">×</em>
</div>';
		}
	}
	$s .= '</div>';

	return $s;
}

if (!function_exists('tpl_selector')) {
	function tpl_selector($name, $options = array())
	{
		$options['multi'] = intval($options['multi']);
		$options['buttontext'] = isset($options['buttontext']) ? $options['buttontext'] : '请选择';
		$options['items'] = isset($options['items']) && $options['items'] ? $options['items'] : array();
		$options['readonly'] = isset($options['readonly']) ? $options['readonly'] : true;
		$options['callback'] = isset($options['callback']) ? $options['callback'] : '';
		$options['key'] = isset($options['key']) ? $options['key'] : 'id';
		$options['text'] = isset($options['text']) ? $options['text'] : 'title';
		$options['thumb'] = isset($options['thumb']) ? $options['thumb'] : 'thumb';
		$options['preview'] = isset($options['preview']) ? $options['preview'] : true;
		$options['type'] = isset($options['type']) ? $options['type'] : 'image';
		$options['input'] = isset($options['input']) ? $options['input'] : true;
		$options['required'] = isset($options['required']) ? $options['required'] : false;
		$options['nokeywords'] = isset($options['nokeywords']) ? $options['nokeywords'] : 0;
		$options['placeholder'] = isset($options['placeholder']) ? $options['placeholder'] : '请输入关键词';
		$options['autosearch'] = isset($options['autosearch']) ? $options['autosearch'] : 0;

		if (empty($options['items'])) {
			$options['items'] = array();
		}
		else {
			if (!is_array2($options['items'])) {
				$options['items'] = array($options['items']);
			}
		}

		$options['name'] = $name;
		$titles = '';

		foreach ($options['items'] as $item) {
			$titles .= $item[$options['text']];

			if (1 < count($options['items'])) {
				$titles .= '; ';
			}
		}

		$options['value'] = isset($options['value']) ? $options['value'] : $titles;
		$readonly = ($options['readonly'] ? 'readonly' : '');
		$required = ($options['required'] ? ' data-rule-required="true"' : '');
		$callback = (!empty($options['callback']) ? ', ' . $options['callback'] : '');
		$id = ($options['multi'] ? $name . '[]' : $name);
		$html = '<div id=\'' . $name . "_selector' class='selector'\r\n                     data-type=\"" . $options['type'] . "\"\r\n                     data-key=\"" . $options['key'] . "\"\r\n                     data-text=\"" . $options['text'] . "\"\r\n                     data-thumb=\"" . $options['thumb'] . "\"\r\n                     data-multi=\"" . $options['multi'] . "\"\r\n                     data-callback=\"" . $options['callback'] . "\"\r\n                     data-url=\"" . $options['url'] . "\"\r\n                     data-nokeywords=\"" . $options['nokeywords'] . "\"\r\n                  data-autosearch=\"" . $options['autosearch'] . "\"\r\n\r\n                 >";

		if ($options['input']) {
			$html .= '<div class=\'input-group\'>' . '<input type=\'text\' id=\'' . $name . '_text\' name=\'' . $name . '_text\'  value=\'' . $options['value'] . '\' class=\'form-control text\'  ' . $readonly . '  ' . $required . '/>' . '<div class=\'input-group-btn\'>';
		}

		$html .= '<button class=\'btn btn-primary\' type=\'button\' onclick=\'biz.selector.select(' . json_encode($options) . ');\'>' . $options['buttontext'] . '</button>';

		if ($options['input']) {
			$html .= '</div>';
			$html .= '</div>';
		}

		$show = ($options['preview'] ? '' : ' style=\'display:none\'');

		if ($options['type'] == 'image') {
			$html .= '<div class=\'input-group multi-img-details container\' ' . $show . '>';
		}
		else if ($options['type'] == 'coupon') {
			$html .= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th>优惠券总数</th>\r\n                                <th>每人限领数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody class='ui-sortable container'>";
		}
		else if ($options['type'] == 'coupon_cp') {
			$html .= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th></th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
		}
		else if ($options['type'] == 'coupon_share') {
			$html .= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th>每人领取数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
		}
		else if ($options['type'] == 'coupon_shares') {
			$html .= '<div class=\'input-group multi-audio-details\' ' . $show . ">\r\n                        <table class='table'>\r\n                            <thead>\r\n                            <tr>\r\n                                <th style='width:100px;'>优惠券名称</th>\r\n                                <th style='width:200px;'></th>\r\n                                <th></th>\r\n                                <th>每人领取数量</th>\r\n                                <th style='width:80px;'>操作</th>\r\n                            </tr>\r\n                            </thead>\r\n                            <tbody id='param-items' class='ui-sortable container'>";
		}
		else {
			$html .= '<div class=\'input-group multi-audio-details container\' ' . $show . '>';
		}

		foreach ($options['items'] as $item) {
			if ($options['type'] == 'image') {
				$html .= '<div class=\'multi-item\' data-' . $options['key'] . '=\'' . $item[$options['key']] . '\' data-name=\'' . $name . "'>\r\n                                      <img class='img-responsive img-thumbnail' src='" . tomedia($item[$options['thumb']]) . "' onerror='this.src=\"/public/static/images/nopic.png\"'>\r\n                                      <div class='img-nickname'>" . $item[$options['text']] . "</div>\r\n                                     <input type='hidden' value='" . $item[$options['key']] . '\' name=\'' . $id . "'>\r\n                                     <em onclick='biz.selector.remove(this,\"" . $name . "\")'  class='close'>×</em>\r\n                            <div style='clear:both;'></div>\r\n                         </div>";
			}
			else if ($options['type'] == 'coupon') {
				$html .= "\r\n                <tr class='multi-product-item' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                    <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                    <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                    <td style='width:80px;'>\r\n                        <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                    </td>\r\n                    <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                    <td>\r\n                        <input class='form-control valid' type='text' value='" . $item['coupontotal'] . '\' name=\'coupontotal' . $item[$options['key']] . "'>\r\n                    </td>\r\n                    <td>\r\n                        <input class='form-control valid' type='text' value='" . $item['couponlimit'] . '\' name=\'couponlimit' . $item[$options['key']] . "'>\r\n                    </td>\r\n                    <td>\r\n                        <button class='btn btn-default' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                    </td>\r\n                </tr>\r\n                ";
			}
			else if ($options['type'] == 'coupon_cp') {
				$html .= "\r\n                    <tr class='multi-product-item setticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-default' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
			}
			else if ($options['type'] == 'coupon_share') {
				$html .= "\r\n                    <tr class='multi-product-item shareticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponid[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <input class='form-control valid' type='text' value='" . $item['couponnum' . $item['id']] . '\' name=\'couponnum' . $item[$options['key']] . "'>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-default' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
			}
			else if ($options['type'] == 'coupon_shares') {
				$html .= "\r\n                    <tr class='multi-product-item sharesticket' data-" . $options['key'] . '=\'' . $item[$options['key']] . "'>\r\n                        <input type='hidden' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                        <input type='hidden' value='" . $item[$options['key']] . "' name='couponids[]'>\r\n                        <td style='width:80px;'>\r\n                            <img src='" . tomedia($item[$options['thumb']]) . "' style='width:70px;border:1px solid #ccc;padding:1px'>\r\n                        </td>\r\n                        <td style='width:220px;'>" . $item[$options['text']] . "</td>\r\n                        <td>\r\n                        </td>\r\n                        <td>\r\n                            <input class='form-control valid' type='text' value='" . $item['couponsnum' . $item['id']] . '\' name=\'couponsnum' . $item[$options['key']] . "'>\r\n                        </td>\r\n                        <td>\r\n                            <button class='btn btn-default' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                        </td>\r\n                    </tr>\r\n                    ";
			}
			else {
				$html .= '<div class=\'multi-audio-item \' data-' . $options['key'] . '=\'' . $item[$options['key']] . "' >\r\n                       <div class='input-group'>\r\n                       <input type='text' class='form-control img-textname' readonly='' value='" . $item[$options['text']] . "'>\r\n                       <input type='hidden'  value='" . $item[$options['key']] . '\' name=\'' . $id . "'>\r\n                       <div class='input-group-btn'><button class='btn btn-default' onclick='biz.selector.remove(this,\"" . $name . "\")' type='button'><i class='fa fa-remove'></i></button>\r\n                       </div></div></div>";
			}
		}

		if ($options['type'] == 'coupon') {
			$html .= '</tbody></table>';
		}
		else if ($options['type'] == 'coupon_cp') {
			$html .= '</tbody></table>';
		}
		else if ($options['type'] == 'coupon_share') {
			$html .= '</tbody></table>';
		}
		else if ($options['type'] == 'coupon_shares') {
			$html .= '</tbody></table>';
		}
		else {
			if ($options['type'] == 'coupon_sync') {
				$html .= '</tbody></table>';
			}
		}

		$html .= '</div></div>';
		return $html;
	}
}

if (!function_exists('tpl_form_field_position')) {
	function tpl_form_field_position($field, $value = array())
	{
		$s = '';

		if (!defined('TPL_INIT_COORDINATE')) {
			$s .= "<script type=\"text/javascript\">\r\n                    function showCoordinate(elm) {\r\n                        \r\n                            var val = {};\r\n                            val.lng = parseFloat(\$(elm).parent().prev().prev().find(\":text\").val());\r\n                            val.lat = parseFloat(\$(elm).parent().prev().find(\":text\").val());\r\n                            val = biz.BdMapToTxMap(val.lat,val.lng);\r\n                            biz.map(val, function(r){\r\n                                var address_label = \$(\"#address_label\");\r\n                                if (address_label.length>0)\r\n                                {\r\n                                    address_label.val(r.label);\r\n                                }\r\n                                r = biz.TxMapToBdMap(r.lat,r.lng);\r\n                                \$(elm).parent().prev().prev().find(\":text\").val(r.lng);\r\n                                \$(elm).parent().prev().find(\":text\").val(r.lat);\r\n                            },\"" . '/admin/utility/map' . "\");\r\n    }\r\n    \r\n                </script>";
			define('TPL_INIT_COORDINATE', true);
		}

		$s .= "\r\n            <div class=\"row row-fix\">\r\n                <div class=\"col-xs-4 col-sm-4\">\r\n                    <input type=\"text\" name=\"" . $field . '[lng]" value="' . $value['lng'] . "\" placeholder=\"地理经度\"  class=\"form-control\" />\r\n                </div>\r\n                <div class=\"col-xs-4 col-sm-4\">\r\n                    <input type=\"text\" name=\"" . $field . '[lat]" value="' . $value['lat'] . "\" placeholder=\"地理纬度\"  class=\"form-control\" />\r\n                </div>\r\n                <div class=\"col-xs-4 col-sm-4\">\r\n                    <button onclick=\"showCoordinate(this);\" class=\"btn btn-default\" type=\"button\">选择坐标</button>\r\n                </div>\r\n            </div>";
		return $s;
	}
}

if (!function_exists('tpl_form_field_color')) {
	function tpl_form_field_color($name, $value = '') {
		$s = '';
		if (!defined('TPL_INIT_COLOR')) {
			$s = '
			<script type="text/javascript">
				$(function(){
					$(".colorpicker").each(function(){
						var elm = this;
						util.colorpicker(elm, function(color){
							$(elm).parent().prev().prev().val(color.toHexString());
							$(elm).parent().prev().css("background-color", color.toHexString());
						});
					});
					$(".colorclean").click(function(){
						$(this).parent().prev().prev().val("");
						$(this).parent().prev().css("background-color", "#FFF");
					});
				});
			</script>';
			define('TPL_INIT_COLOR', true);
		}
		$s .= '
			<div class="row row-fix">
				<div class="col-xs-8 col-sm-8" style="padding-right:0;">
					<div class="input-group">
						<input class="form-control" type="text" name="'.$name.'" placeholder="请选择颜色" value="'.$value.'">
						<span class="input-group-addon" style="width:35px;border-left:none;background-color:'.$value.'"></span>
						<span class="input-group-btn">
							<button class="btn btn-default colorpicker" type="button">选择颜色 <i class="fa fa-caret-down"></i></button>
							<button class="btn btn-default colorclean" type="button"><span><i class="fa fa-remove"></i></span></button>
						</span>
					</div>
				</div>
			</div>
			';
		return $s;
	}
}

if (!function_exists('tpl_form_field_editor')) {
	function tpl_form_field_editor($params = array(), $callback = NULL)
	{
		$html = '<span class="form-editor-group">';
		$html .= '<span class="form-control-static form-editor-show">';
		$html .= '<a class="form-editor-text">' . $params['value'] . '</a>';
		$html .= '<a class="text-primary form-editor-btn">修改</a>';
		$html .= '</span>';
		$html .= '<span class="input-group form-editor-edit">';
		$html .= '<input class="form-control form-editor-input" value="' . $params['value'] . '" name="' . $params['name'] . '"';

		if (!empty($params['placeholder'])) {
			$html .= 'placeholder="' . $params['placeholder'] . '"';
		}

		if (!empty($params['id'])) {
			$html .= 'id="' . $params['id'] . '"';
		}

		if (!empty($params['data-rule-required']) || !empty($params['required'])) {
			$html .= ' data-rule-required="true"';
		}

		if (!empty($params['data-msg-required'])) {
			$html .= ' data-msg-required="' . $params['data-msg-required'] . '"';
		}

		$html .= ' /><span class="input-group-btn">';
		$html .= '<span class="btn btn-default form-editor-finish"';

		if ($callback) {
			$html .= 'data-callback="' . $callback . '"';
		}

		$html .= '><i class="icow icow-wancheng"></i></span>';
		$html .= '</span>';
		$html .= '</span>';
		return $html;
	}
}

function pagination($total, $pageIndex, $pageSize = 15, $url = '', $context = array('before' => 5, 'after' => 4, 'ajaxcallback' => '', 'callbackfuncname' => '')) {
	$pdata = array(
		'tcount' => 0,
		'tpage' => 0,
		'cindex' => 0,
		'findex' => 0,
		'pindex' => 0,
		'nindex' => 0,
		'lindex' => 0,
		'options' => ''
	);
	if ($context['ajaxcallback']) {
		$context['isajax'] = true;
	}

	if ($context['callbackfuncname']) {
		$callbackfunc = $context['callbackfuncname'];
	}

	$pdata['tcount'] = $total;
	$pdata['tpage'] = (empty($pageSize) || $pageSize < 0) ? 1 : ceil($total / $pageSize);
	if ($pdata['tpage'] <= 1) {
		return '';
	}
	$cindex = $pageIndex;
	$cindex = min($cindex, $pdata['tpage']);
	$cindex = max($cindex, 1);
	$pdata['cindex'] = $cindex;
	$pdata['findex'] = 1;
	$pdata['pindex'] = $cindex > 1 ? $cindex - 1 : 1;
	$pdata['nindex'] = $cindex < $pdata['tpage'] ? $cindex + 1 : $pdata['tpage'];
	$pdata['lindex'] = $pdata['tpage'];

	if ($context['isajax']) {
		if (empty($url)) {
			$url = $_GET['script_name'] . '?' . http_build_query($_GET);
		}
		$pdata['faa'] = 'href="javascript:;" page="' . $pdata['findex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $url . '\', \'' . $pdata['findex'] . '\', this);return false;"' : '');
		$pdata['paa'] = 'href="javascript:;" page="' . $pdata['pindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $url . '\', \'' . $pdata['pindex'] . '\', this);return false;"' : '');
		$pdata['naa'] = 'href="javascript:;" page="' . $pdata['nindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $url . '\', \'' . $pdata['nindex'] . '\', this);return false;"' : '');
		$pdata['laa'] = 'href="javascript:;" page="' . $pdata['lindex'] . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $url . '\', \'' . $pdata['lindex'] . '\', this);return false;"' : '');
	} else {
		if ($url) {
			$pdata['faa'] = 'href="?' . str_replace('*', $pdata['findex'], $url) . '"';
			$pdata['paa'] = 'href="?' . str_replace('*', $pdata['pindex'], $url) . '"';
			$pdata['naa'] = 'href="?' . str_replace('*', $pdata['nindex'], $url) . '"';
			$pdata['laa'] = 'href="?' . str_replace('*', $pdata['lindex'], $url) . '"';
		} else {
			$_GET['page'] = $pdata['findex'];
			$pdata['faa'] = 'href="' . $_GET['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['pindex'];
			$pdata['paa'] = 'href="' . $_GET['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['nindex'];
			$pdata['naa'] = 'href="' . $_GET['script_name'] . '?' . http_build_query($_GET) . '"';
			$_GET['page'] = $pdata['lindex'];
			$pdata['laa'] = 'href="' . $_GET['script_name'] . '?' . http_build_query($_GET) . '"';
		}
	}

	$html = '<div><ul class="pagination pagination-centered">';
	if ($pdata['cindex'] > 1) {
		$html .= "<li><a {$pdata['faa']} class=\"pager-nav\">首页</a></li>";
		$html .= "<li><a {$pdata['paa']} class=\"pager-nav\">&laquo;上一页</a></li>";
	}
		if (!$context['before'] && $context['before'] != 0) {
		$context['before'] = 5;
	}
	if (!$context['after'] && $context['after'] != 0) {
		$context['after'] = 4;
	}

	if ($context['after'] != 0 && $context['before'] != 0) {
		$range = array();
		$range['start'] = max(1, $pdata['cindex'] - $context['before']);
		$range['end'] = min($pdata['tpage'], $pdata['cindex'] + $context['after']);
		if ($range['end'] - $range['start'] < $context['before'] + $context['after']) {
			$range['end'] = min($pdata['tpage'], $range['start'] + $context['before'] + $context['after']);
			$range['start'] = max(1, $range['end'] - $context['before'] - $context['after']);
		}
		for ($i = $range['start']; $i <= $range['end']; $i++) {
			if ($context['isajax']) {
				$aa = 'href="javascript:;" page="' . $i . '" '. ($callbackfunc ? 'onclick="'.$callbackfunc.'(\'' . $url . '\', \'' . $i . '\', this);return false;"' : '');
			} else {
				if ($url) {
					$aa = 'href="?' . str_replace('*', $i, $url) . '"';
				} else {
					$_GET['page'] = $i;
					$aa = 'href="?' . http_build_query($_GET) . '"';
				}
			}
			$html .= ($i == $pdata['cindex'] ? '<li class="active"><a href="javascript:;">' . $i . '</a></li>' : "<li><a {$aa}>" . $i . '</a></li>');
		}
	}

	if ($pdata['cindex'] < $pdata['tpage']) {
		$html .= "<li><a {$pdata['naa']} class=\"pager-nav\">下一页&raquo;</a></li>";
		$html .= "<li><a {$pdata['laa']} class=\"pager-nav\">尾页</a></li>";
	}
	$html .= '</ul></div>';
	return $html;
}

if (!function_exists('http_build_query')) {
	function http_build_query($formdata, $numeric_prefix = null, $arg_separator = null) {
		if (!is_array($formdata))
			return false;
		if ($arg_separator == null)
			$arg_separator = '&';
		return http_build_recursive($formdata, $arg_separator);
	}
	function http_build_recursive($formdata, $separator, $key = '', $prefix = '') {
		$rlt = '';
		foreach ($formdata as $k => $v) {
			if (is_array($v)) {
				if ($key)
					$rlt .= http_build_recursive($v, $separator, $key . '[' . $k . ']', $prefix);
				else
					$rlt .= http_build_recursive($v, $separator, $k, $prefix);
			} else {
				if ($key)
					$rlt .= $prefix . $key . '[' . urlencode($k) . ']=' . urldecode($v) . '&';
				else
					$rlt .= $prefix . urldecode($k) . '=' . urldecode($v) . '&';
			}
		}
		return $rlt;
	}
}

function array_elements($keys, $src, $default = FALSE) {
	$return = array();
	if(!is_array($keys)) {
		$keys = array($keys);
	}
	foreach($keys as $key) {
		if(isset($src[$key])) {
			$return[$key] = $src[$key];
		} else {
			$return[$key] = $default;
		}
	}
	return $return;
}

// 文字截取
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false)
{
    if(function_exists("mb_substr")){
            if ($suffix && strlen($str)>$length)
                return mb_substr($str, $start, $length, $charset)."...";
        else
                 return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
            if ($suffix && strlen($str)>$length)
                return iconv_substr($str,$start,$length,$charset)."...";
        else
                return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

/**
 * 二维数组根据首字母分组排序
 * @param  array  $data      二维数组
 * @param  string $targetKey 首字母的键名
 * @return array             根据首字母关联的二维数组
 */
function groupByInitials(array $data, $targetKey = 'name')
{
    $data = array_map(function ($item) use ($targetKey) {
        return array_merge($item, [
            'initials' => getInitials($item[$targetKey]),
        ]);
    }, $data);
    $data = sortInitials($data);
    return $data;
}

/**
 * 按字母排序
 * @param  array  $data
 * @return array
 */
function sortInitials(array $data)
{
    $sortData = [];
    foreach ($data as $key => $value) {
        $sortData[$value['initials']][] = $value;
    }
    ksort($sortData);
    return $sortData;
}

/**
 * 获取首字母
 * @param  string $str 汉字字符串
 * @return string 首字母
 */
function getInitials($str)
{
    if (empty($str)) {return '';}
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) {
        return strtoupper($str{0});
    }
    $s1  = iconv('UTF-8', 'gb2312', $str);
    $s2  = iconv('gb2312', 'UTF-8', $s1);
    $s   = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) {
        return 'A';
    }
    if ($asc >= -20283 && $asc <= -19776) {
        return 'B';
    }
    if ($asc >= -19775 && $asc <= -19219) {
        return 'C';
    }
    if ($asc >= -19218 && $asc <= -18711) {
        return 'D';
    }
    if ($asc >= -18710 && $asc <= -18527) {
        return 'E';
    }
    if ($asc >= -18526 && $asc <= -18240) {
        return 'F';
    }
    if ($asc >= -18239 && $asc <= -17923) {
        return 'G';
    }
    if ($asc >= -17922 && $asc <= -17418) {
        return 'H';
    }
    if ($asc >= -17417 && $asc <= -16475) {
        return 'J';
    }
    if ($asc >= -16474 && $asc <= -16213) {
        return 'K';
    }
    if ($asc >= -16212 && $asc <= -15641) {
        return 'L';
    }
    if ($asc >= -15640 && $asc <= -15166) {
        return 'M';
    }
    if ($asc >= -15165 && $asc <= -14923) {
        return 'N';
    }
    if ($asc >= -14922 && $asc <= -14915) {
        return 'O';
    }
    if ($asc >= -14914 && $asc <= -14631) {
        return 'P';
    }
    if ($asc >= -14630 && $asc <= -14150) {
        return 'Q';
    }
    if ($asc >= -14149 && $asc <= -14091) {
        return 'R';
    }
    if ($asc >= -14090 && $asc <= -13319) {
        return 'S';
    }
    if ($asc >= -13318 && $asc <= -12839) {
        return 'T';
    }
    if ($asc >= -12838 && $asc <= -12557) {
        return 'W';
    }
    if ($asc >= -12556 && $asc <= -11848) {
        return 'X';
    }
    if ($asc >= -11847 && $asc <= -11056) {
        return 'Y';
    }
    if ($asc >= -11055 && $asc <= -10247) {
        return 'Z';
    }
    return '#';
}

function lazy($html = '')
{
	$html = preg_replace_callback('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', function($matches) use($_SERVER) {
		$images = $matches[0];
		if (strexists($images, 'http://') || strexists($images, 'https://')) {
			return preg_replace('/src=/', 'src=', $images);
		}

		$attachurl = str_replace(array('https://', 'http://'), '', $_SERVER['HTTP_HOST']);

		if (strexists($images, $attachurl)) {
			$image = $matches[1];
			$image = str_replace(array('https://', 'http://'), '', $image);
			$image = str_replace($attachurl, '', $image);
			$images = str_replace(array('https://', 'http://'), '', $images);
			$images = str_replace($attachurl, '', $images);
			$images = str_replace($image, tomedia($image), $images);
		}

		return preg_replace('/src=/', 'src=', $images);
	}, $html);
	return $html;
}

function ihttp_request($url, $post = '', $extra = array(), $timeout = 60) {
	if (function_exists('curl_init') && function_exists('curl_exec') && $timeout > 0) {
		$ch = ihttp_build_curl($url, $post, $extra, $timeout);
		if (is_error($ch)) {
			return $ch;
		}
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);
		if ($errno || empty($data)) {
			return errormsg($errno, $error);
		} else {
			return ihttp_response_parse($data);
		}
	}
	$urlset = ihttp_parse_url($url, true);
	if (!empty($urlset['ip'])) {
		$urlset['host'] = $urlset['ip'];
	}
	
	$body = ihttp_build_httpbody($url, $post, $extra);
	
	if ($urlset['scheme'] == 'https') {
		$fp = ihttp_socketopen('ssl://' . $urlset['host'], $urlset['port'], $errno, $error);
	} else {
		$fp = ihttp_socketopen($urlset['host'], $urlset['port'], $errno, $error);
	}
	stream_set_blocking($fp, $timeout > 0 ? true : false);
	stream_set_timeout($fp, ini_get('default_socket_timeout'));
	if (!$fp) {
		return errormsg(1, $error);
	} else {
		fwrite($fp, $body);
		$content = '';
		if($timeout > 0) {
			while (!feof($fp)) {
				$content .= fgets($fp, 512);
			}
		}
		fclose($fp);
		return ihttp_response_parse($content, true);
	}
}


function ihttp_get($url) {
	return ihttp_request($url);
}


function ihttp_post($url, $data) {
	$headers = array('Content-Type' => 'application/x-www-form-urlencoded');
	return ihttp_request($url, $data, $headers);
}


function ihttp_multi_request($urls, $posts = array(), $extra = array(), $timeout = 60) {
	if (!is_array($urls)) {
		return errormsg(1, '请使用ihttp_request函数');
	}
	$curl_multi = curl_multi_init();
	$curl_client = $response = array();

	foreach ($urls as $i => $url) {
		if (isset($posts[$i]) && is_array($posts[$i])) {
			$post = $posts[$i];
		} else {
			$post = $posts;
		}
		if (!empty($url)) {
			$curl = ihttp_build_curl($url, $post, $extra, $timeout);
			if (is_error($curl)) {
				continue;
			}
			if (curl_multi_add_handle($curl_multi, $curl) === CURLM_OK) {
								$curl_client[] = $curl;
			}
		}
	}
	if (!empty($curl_client)) {
		$active = null;
		do {
			$mrc = curl_multi_exec($curl_multi, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			do {
				$mrc = curl_multi_exec($curl_multi, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
	}
	
	foreach ($curl_client as $i => $curl) {
		$response[$i] = curl_multi_getcontent($curl);
		curl_multi_remove_handle($curl_multi, $curl);
	}
	curl_multi_close($curl_multi);
	return $response;
}

function ihttp_socketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}


function ihttp_response_parse($data, $chunked = false) {
	$rlt = array();
	$headermeta = explode('HTTP/', $data);
	if (count($headermeta) > 2) {
		$data = 'HTTP/' . array_pop($headermeta);
	}
	$pos = strpos($data, "\r\n\r\n");
	$split1[0] = substr($data, 0, $pos);
	$split1[1] = substr($data, $pos + 4, strlen($data));
	
	$split2 = explode("\r\n", $split1[0], 2);
	preg_match('/^(\S+) (\S+) (.*)$/', $split2[0], $matches);
	$rlt['code'] = $matches[2];
	$rlt['status'] = $matches[3];
	$rlt['responseline'] = $split2[0];
	$header = explode("\r\n", $split2[1]);
	$isgzip = false;
	$ischunk = false;
	foreach ($header as $v) {
		$pos = strpos($v, ':');
		$key = substr($v, 0, $pos);
		$value = trim(substr($v, $pos + 1));
		if (is_array($rlt['headers'][$key])) {
			$rlt['headers'][$key][] = $value;
		} elseif (!empty($rlt['headers'][$key])) {
			$temp = $rlt['headers'][$key];
			unset($rlt['headers'][$key]);
			$rlt['headers'][$key][] = $temp;
			$rlt['headers'][$key][] = $value;
		} else {
			$rlt['headers'][$key] = $value;
		}
		if(!$isgzip && strtolower($key) == 'content-encoding' && strtolower($value) == 'gzip') {
			$isgzip = true;
		}
		if(!$ischunk && strtolower($key) == 'transfer-encoding' && strtolower($value) == 'chunked') {
			$ischunk = true;
		}
	}
	if($chunked && $ischunk) {
		$rlt['content'] = ihttp_response_parse_unchunk($split1[1]);
	} else {
		$rlt['content'] = $split1[1];
	}
	if($isgzip && function_exists('gzdecode')) {
		$rlt['content'] = gzdecode($rlt['content']);
	}

	$rlt['meta'] = $data;
	if($rlt['code'] == '100') {
		return ihttp_response_parse($rlt['content']);
	}
	return $rlt;
}

function ihttp_response_parse_unchunk($str = null) {
	if(!is_string($str) or strlen($str) < 1) {
		return false; 
	}
	$eol = "\r\n";
	$add = strlen($eol);
	$tmp = $str;
	$str = '';
	do {
		$tmp = ltrim($tmp);
		$pos = strpos($tmp, $eol);
		if($pos === false) {
			return false;
		}
		$len = hexdec(substr($tmp, 0, $pos));
		if(!is_numeric($len) or $len < 0) {
			return false;
		}
		$str .= substr($tmp, ($pos + $add), $len);
		$tmp  = substr($tmp, ($len + $pos + $add));
		$check = trim($tmp);
	} while(!empty($check));
	unset($tmp);
	return $str;
}


function ihttp_parse_url($url, $set_default_port = false) {
	if (empty($url)) {
		return errormsg(1);
	}
	$urlset = parse_url($url);
	if (!empty($urlset['scheme']) && !in_array($urlset['scheme'], array('http', 'https'))) {
		return errormsg(1, '只能使用 http 及 https 协议');
	}
	if (empty($urlset['path'])) {
		$urlset['path'] = '/';
	}
	if (!empty($urlset['query'])) {
		$urlset['query'] = "?{$urlset['query']}";
	}
	if (strexists($url, 'https://') && !extension_loaded('openssl')) {
		if (!extension_loaded("openssl")) {
			return errormsg(1,'请开启您PHP环境的openssl', '');
		}
	}
	if (empty($urlset['host'])) {
		$current_url = parse_url($GLOBALS['_W']['siteroot']);
		$urlset['host'] = $current_url['host'];
		$urlset['scheme'] = $current_url['scheme'];
		$urlset['path'] = $current_url['path'] . 'web/' . str_replace('./', '', $urlset['path']);
		$urlset['ip'] = '127.0.0.1';
	} else if (! ihttp_allow_host($urlset['host'])){
		return errormsg(1, 'host 非法');
	}
	
	if ($set_default_port && empty($urlset['port'])) {
		$urlset['port'] = $urlset['scheme'] == 'https' ? '443' : '80';
	}
	return $urlset;
}


function ihttp_allow_host($host) {
	if (strexists($host, '@')) {
		return false;
	}
	$pattern = "/^(10|172|192|127)/";
	if (preg_match($pattern, $host) && isset($_W['setting']['ip_white_list'])) {
		$ip_white_list = $_W['setting']['ip_white_list'];
		if ($ip_white_list && isset($ip_white_list[$host]) && !$ip_white_list[$host]['status']) {
			return false;
		}
	}
	return true;
}

/**
 * GET 请求
 * @param string $url
 */
function http_get($url){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);//目标URL
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );//设定是否显示头信息,1为显示
    curl_setopt($oCurl, CURLOPT_BINARYTRANSFER, true) ;//在启用CURLOPT_RETURNTRANSFER时候将获取数据返回
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);//获取页面各种信息
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}


function ihttp_build_curl($url, $post, $extra, $timeout) {
	if (!function_exists('curl_init') || !function_exists('curl_exec')) {
		return errormsg(1, 'curl扩展未开启');
	}
	
	$urlset = ihttp_parse_url($url);
	if (is_error($urlset)) {
		return $urlset;
	}
	
	if (!empty($urlset['ip'])) {
		$extra['ip'] = $urlset['ip'];
	}
	
	$ch = curl_init();
	if (!empty($extra['ip'])) {
		$extra['Host'] = $urlset['host'];
		$urlset['host'] = $extra['ip'];
		unset($extra['ip']);
	}
	curl_setopt($ch, CURLOPT_URL, $urlset['scheme'] . '://' . $urlset['host'] . ($urlset['port'] == '80' || empty($urlset['port']) ? '' : ':' . $urlset['port']) . $urlset['path'] . $urlset['query']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	@curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	if ($post) {
		if (is_array($post)) {
			$filepost = false;
						foreach ($post as $name => &$value) {
				if (version_compare(phpversion(), '5.5') >= 0 && is_string($value) && substr($value, 0, 1) == '@') {
					$post[$name] = new CURLFile(ltrim($value, '@'));
				}
				if ((is_string($value) && substr($value, 0, 1) == '@') || (class_exists('CURLFile') && $value instanceof CURLFile)) {
					$filepost = true;
				}
			}
			if (!$filepost) {
				$post = http_build_query($post);
			}
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if (!empty($GLOBALS['_W']['config']['setting']['proxy'])) {
		$urls = parse_url($GLOBALS['_W']['config']['setting']['proxy']['host']);
		if (!empty($urls['host'])) {
			curl_setopt($ch, CURLOPT_PROXY, "{$urls['host']}:{$urls['port']}");
			$proxytype = 'CURLPROXY_' . strtoupper($urls['scheme']);
			if (!empty($urls['scheme']) && defined($proxytype)) {
				curl_setopt($ch, CURLOPT_PROXYTYPE, constant($proxytype));
			} else {
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
			}
			if (!empty($GLOBALS['_W']['config']['setting']['proxy']['auth'])) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['_W']['config']['setting']['proxy']['auth']);
			}
		}
	}
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	if (defined('CURL_SSLVERSION_TLSv1')) {
		curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1');
	if (!empty($extra) && is_array($extra)) {
		$headers = array();
		foreach ($extra as $opt => $value) {
			if (strexists($opt, 'CURLOPT_')) {
				curl_setopt($ch, constant($opt), $value);
			} elseif (is_numeric($opt)) {
				curl_setopt($ch, $opt, $value);
			} else {
				$headers[] = "{$opt}: {$value}";
			}
		}
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}
	}
	return $ch;
}

function ihttp_build_httpbody($url, $post, $extra) {
	$urlset = ihttp_parse_url($url, true);
	if (is_error($urlset)) {
		return $urlset;
	}
	
	if (!empty($urlset['ip'])) {
		$extra['ip'] = $urlset['ip'];
	}
	
	$body = '';
	if (!empty($post) && is_array($post)) {
		$filepost = false;
		$boundary = random(40);
		foreach ($post as $name => &$value) {
			if ((is_string($value) && substr($value, 0, 1) == '@') && file_exists(ltrim($value, '@'))) {
				$filepost = true;
				$file = ltrim($value, '@');
	
				$body .= "--$boundary\r\n";
				$body .= 'Content-Disposition: form-data; name="'.$name.'"; filename="'.basename($file).'"; Content-Type: application/octet-stream'."\r\n\r\n";
				$body .= file_get_contents($file)."\r\n";
			} else {
				$body .= "--$boundary\r\n";
				$body .= 'Content-Disposition: form-data; name="'.$name.'"'."\r\n\r\n";
				$body .= $value."\r\n";
			}
		}
		if (!$filepost) {
			$body = http_build_query($post, '', '&');
		} else {
			$body .= "--$boundary\r\n";
		}
	}
	
	$method = empty($post) ? 'GET' : 'POST';
	$fdata = "{$method} {$urlset['path']}{$urlset['query']} HTTP/1.1\r\n";
	$fdata .= "Accept: */*\r\n";
	$fdata .= "Accept-Language: zh-cn\r\n";
	if ($method == 'POST') {
		$fdata .= empty($filepost) ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data; boundary=$boundary\r\n";
	}
	$fdata .= "Host: {$urlset['host']}\r\n";
	$fdata .= "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1\r\n";
	if (function_exists('gzdecode')) {
		$fdata .= "Accept-Encoding: gzip, deflate\r\n";
	}
	$fdata .= "Connection: close\r\n";
	if (!empty($extra) && is_array($extra)) {
		foreach ($extra as $opt => $value) {
			if (!strexists($opt, 'CURLOPT_')) {
				$fdata .= "{$opt}: {$value}\r\n";
			}
		}
	}
	if ($body) {
		$fdata .= 'Content-Length: ' . strlen($body) . "\r\n\r\n{$body}";
	} else {
		$fdata .= "\r\n";
	}
	return $fdata;
}

if (!function_exists('get_last_day')) {
	function get_last_day($year, $month)
	{
		return date('t', strtotime($year . '-' . $month . ' -1'));
	}
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
if (!function_exists('delete_dir_file')) {
	function delete_dir_file($dir_name) {
	    $result = false;
	    if(is_dir($dir_name)){
	        if ($handle = opendir($dir_name)) {
	            while (false !== ($item = readdir($handle))) {
	                if ($item != '.' && $item != '..') {
	                    if (is_dir($dir_name . DS . $item)) {
	                        delete_dir_file($dir_name . DS . $item);
	                    } else {
	                        unlink($dir_name . DS . $item);
	                    }
	                }
	            }
	            closedir($handle);
	            if (rmdir($dir_name)) {
	                $result = true;
	            }
	        }
	    }

	    return $result;
	}
}

if (!function_exists('sizecount')) {
	function sizecount($size) {
		if($size >= 1073741824) {
			$size = round($size / 1073741824 * 100) / 100 . ' GB';
		} elseif($size >= 1048576) {
			$size = round($size / 1048576 * 100) / 100 . ' MB';
		} elseif($size >= 1024) {
			$size = round($size / 1024 * 100) / 100 . ' KB';
		} else {
			$size = $size . ' Bytes';
		}
		return $size;
	}
}

if (!function_exists('mkdirs')) {
	function mkdirs($path) {
		if (!is_dir($path)) {
			mkdirs(dirname($path));
			mkdir($path);
		}

		return is_dir($path);
	}
}

if (!function_exists('rmdirs')) {
	function rmdirs($path, $clean = false) {
		if (!is_dir($path)) {
			return false;
		}
		$files = glob($path . '/*');
		if ($files) {
			foreach ($files as $file) {
				is_dir($file) ? rmdirs($file) : @unlink($file);
			}
		}

		return $clean ? true : @rmdir($path);
	}
}

/**
 * 获取表完整名称
 * @param string $table
 * @return $tablename
 */
if (!function_exists('tablename')) {
	function tablename($table) {
		$database = config('database');
		$tablepre = $database['prefix'];
		return (strpos($table, $tablepre) === 0 || strpos($table, 'suliss_') === 0) ? $table : "`{$tablepre}{$table}`";
	}
}