<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
error_reporting(E_ERROR | E_PARSE );

 /**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
 function check_mobile($mobile){
    if(preg_match('/1[34578]\d{9}$/',$mobile)) 
        return true;
    return false;
 }

 /** 
 * 检查用户名是否符合规定 
 * 
 * @param STRING $username 要检查的用户名 
 * @return TRUE or FALSE 
 */
function check_username($username) 
{ 
    $strlen = strlen($username); 
    if (!preg_match("/^[A-Za-z0-9_\x{4e00}-\x{9fa5}]+$/u",$username))
    { 
        return false; 
    } 
    elseif (16 < $strlen || $strlen < 2) 
    { 
        return false; 
    } 
    return true; 
}

function check_password($value,$minLen=6,$maxLen=16){ 
    $match='/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{'.$minLen.','.$maxLen.'}$/'; 
    $v = trim($value); 
    if(empty($v)) {
        return false; 
    }
    return preg_match($match,$v); 
}

 /**
 * @param $length
 * @param $numeric
 * @return string
 * 得到随机hash字符串 
 */
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

/*
参数说明：
$lng  经度
$lat   纬度
$distance   周边半径  默认是500米（0.5Km）
*/
function returnSquarePoint($lat,$lng,$distance  = 0.5)
{
    $degree = (24901*1609)/360.0;
    $dpmLat = 1/$degree;

    $radiusLat = $dpmLat*$distance*1000;
    $minLat = $lat - $radiusLat;       // 最小纬度
    $maxLat = $lat + $radiusLat;       // 最大纬度

    $mpdLng = $degree*cos($lat * (3.1415926/180));
    $dpmLng = 1 / $mpdLng;
    $radiusLng = $dpmLng*$distance*1000;
    $minLng = $lng - $radiusLng;      // 最小经度
    $maxLng = $lng + $radiusLng;      // 最大经度

    return array(
           "lat" => array(array('ELT',$maxLat),array('EGT',$minLat),'and'),
           "lng" => array(array('EGT',$minLng),array('ELT',$maxLng),'and'),
    );
}

/** 
* 根据两点间的经纬度计算距离
* @param float $lat 纬度值 
* @param float $lng 经度值
* @return [int]           [返回单位: 米] 
*/
function getDistance($lat1, $lng1, $lat2, $lng2){   
    $earthRadius = 6367000; //地球近似半径,单位:米

    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;   
    $lat2 = ($lat2 * pi() ) / 180;   
    $lng2 = ($lng2 * pi() ) / 180;

    $calcLongitude = $lng2 - $lng1;   
    $calcLatitude = $lat2 - $lat1; 

    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);   
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));

    $calculatedDistance = $earthRadius * $stepTwo;   
    return round($calculatedDistance);   
}