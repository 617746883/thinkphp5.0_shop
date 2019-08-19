<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Dispatch extends \think\Model
{
	public static function getAllNoDispatchAreas($areas = array(), $type = 0)
	{
		$tradeset = model('common')->getSysset('trade');

		if (empty($type)) {
			$dispatchareas = iunserializer($tradeset['nodispatchareas']);
		} else {
			$dispatchareas = iunserializer($tradeset['nodispatchareas_code']);
		}

		$set_citys = array();
		$dispatch_citys = array();

		if (!empty($dispatchareas)) {
			$set_citys = explode(';', trim($dispatchareas, ';'));
		}

		if (!empty($areas)) {
			$areas = iunserializer($areas);

			if (!empty($areas)) {
				$dispatch_citys = explode(';', trim($areas, ';'));
			}
		}

		$citys = array();

		if (!empty($set_citys)) {
			$citys = $set_citys;
		}

		if (!empty($dispatch_citys)) {
			$citys = array_merge($citys, $dispatch_citys);
			$citys = array_unique($citys);
		}

		return $citys;
	}

	/**
     * 获取默认快递信息
     */
	public static function getDefaultDispatch($merchid = 0)
	{
		$data = Db::name('shop_dispatch')->where('isdefault',1)->where('merchid',$merchid)->where('enabled',1)->find();
		return $data;
	}

	/**
     * 获取一条快递信息
     */
	public static function getOneDispatch($id)
	{
		if ($id == 0) {
			$data = Db::name('shop_dispatch')->where('isdefault',1)->where('enabled',1)->find();
		}
		else {
			$data = Db::name('shop_dispatch')->where('id',$id)->where('enabled',1)->find();
		}
		return $data;
	}

	/**
     * 获取最新的一条快递信息
     */
	public static function getNewDispatch($merchid = 0)
	{
		$data = Db::name('shop_dispatch')->where('merchid',$merchid)->where('enabled',1)->order('id','desc')->find();
		return $data;
	}

	public static function checkOnlyDispatchAreas($user_city_code, $dispatch_data)
	{
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);

		if (empty($new_area)) {
			$areas = $dispatch_data['nodispatchareas'];
		}
		else {
			$areas = $dispatch_data['nodispatchareas_code'];
		}

		$isnoarea = 1;
		if (!empty($user_city_code) && !empty($areas)) {
			$areas = iunserializer($areas);
			$citys = explode(';', trim($areas, ';'));

			if (in_array($user_city_code, $citys)) {
				$isnoarea = 0;
			}
		}

		return $isnoarea;
	}

	public static function getCityDispatchPrice($areas, $address, $param, $d)
	{
		$city = $address['city'];
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);

		if (empty($new_area)) {
			if (is_array($areas) && (0 < count($areas))) {
				foreach ($areas as $area) {
					$citys = explode(';', $area['citys']);
					if (in_array($city, $citys) && !empty($citys)) {
						return self::getDispatchPrice($param, $area, $d['calculatetype']);
					}
				}
			}
		}
		else {
			$address_datavalue = trim($address['datavalue']);
			if (is_array($areas) && (0 < count($areas))) {
				foreach ($areas as $area) {
					$citys_code = explode(';', $area['citys_code']);
					if (in_array($address_datavalue, $citys_code) && !empty($citys_code)) {
						return self::getDispatchPrice($param, $area, $d['calculatetype']);
					}
				}
			}
		}

		return self::getDispatchPrice($param, $d);
	}

	/**
     * 计算运费
     * @param type $param 重量或者是数量
     * @param type $d
     * @param type $calculatetype -1默认读取$d中的calculatetype值 1按数量计算运费 0按重量计算运费
     */
	public static function getDispatchPrice($param, $d, $calculatetype = -1)
	{
		if (empty($d)) {
			return 0;
		}

		$price = 0;

		if ($calculatetype == -1) {
			$calculatetype = $d['calculatetype'];
		}

		if ($calculatetype == 1) {
			if ($param <= $d['firstnum']) {
				$price = floatval($d['firstnumprice']);
			}
			else {
				$price = floatval($d['firstnumprice']);
				$secondweight = $param - floatval($d['firstnum']);
				$dsecondweight = (floatval($d['secondnum']) <= 0 ? 1 : floatval($d['secondnum']));
				$secondprice = 0;

				if (($secondweight % $dsecondweight) == 0) {
					$secondprice = ($secondweight / $dsecondweight) * floatval($d['secondnumprice']);
				}
				else {
					$secondprice = ((int) ($secondweight / $dsecondweight) + 1) * floatval($d['secondnumprice']);
				}

				$price += $secondprice;
			}
		}
		else if ($param <= $d['firstweight']) {
			if (0 <= $param) {
				$price = floatval($d['firstprice']);
			}
			else {
				$price = 0;
			}
		}
		else {
			$price = floatval($d['firstprice']);
			$secondweight = $param - floatval($d['firstweight']);
			$dsecondweight = (floatval($d['secondweight']) <= 0 ? 1 : floatval($d['secondweight']));
			$secondprice = 0;

			if (($secondweight % $dsecondweight) == 0) {
				$secondprice = ($secondweight / $dsecondweight) * floatval($d['secondprice']);
			}
			else {
				$secondprice = ((int) ($secondweight / $dsecondweight) + 1) * floatval($d['secondprice']);
			}

			$price += $secondprice;
		}

		return $price;
	}

}