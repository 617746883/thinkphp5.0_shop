<?php
/**
 * 个人中心
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\mobile\controller;
use think\Db;
use think\Request;
class Member extends Base
{
	public function address()
	{
		$mid = 0;
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		$pindex = intval(input('page'));
		$psize = 20;
		$condition = " mid=" . $mid . " and deleted=0 ";
		$list = Db::name('shop_member_address')->where($condition)->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('member/address/index');
	}

	public function addresspost() 
	{
		$mid = 0;
		$id = intval(input('id'));
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		if( !empty($id) ) 
		{
			$address = Db::name('shop_member_address')->where('id = ' . $id . ' and mid = ' . $mid)->find();
			if( empty($address["datavalue"]) ) 
			{
				$provinceName = $address["province"];
				$citysName = $address["city"];
				$countyName = $address["area"];
				$province_code = 0;
				$citys_code = 0;
				$county_code = 0;
				$path = "/public/static/js/dist/area/AreaNew.xml";
				$xml = file_get_contents($path);
				$array = xml2array($xml);
				$newArr = array( );
				if( is_array($array["province"]) ) 
				{
					foreach( $array["province"] as $i => $v ) 
					{
						if( 0 < $i && $v["@attributes"]["name"] == $provinceName && !is_null($provinceName) && $provinceName != "" ) 
						{
							$province_code = $v["@attributes"]["code"];
							if( is_array($v["city"]) ) 
							{
								if( !isset($v["city"][0]) ) 
								{
									$v["city"] = array( $v["city"] );
								}
								foreach( $v["city"] as $ii => $vv ) 
								{
									if( $vv["@attributes"]["name"] == $citysName && !is_null($citysName) && $citysName != "" ) 
									{
										$citys_code = $vv["@attributes"]["code"];
										if( is_array($vv["county"]) ) 
										{
											if( !isset($vv["county"][0]) ) 
											{
												$vv["county"] = array( $vv["county"] );
											}
											foreach( $vv["county"] as $iii => $vvv ) 
											{
												if( $vvv["@attributes"]["name"] == $countyName && !is_null($countyName) && $countyName != "" ) 
												{
													$county_code = $vvv["@attributes"]["code"];
												}
											}
										}
									}
								}
							}
						}
					}
				}
				if( $province_code != 0 && $citys_code != 0 && $county_code != 0 ) 
				{
					$address["datavalue"] = $province_code . " " . $citys_code . " " . $county_code;
					Db::name('shop_member_address')->where('id = ' . $id . ' and mid = ' . $mid)->update($address);
				}
			}
			$show_data = 1;
			if( !empty($new_area) && empty($address["datavalue"]) || empty($new_area) && !empty($address["datavalue"]) ) 
			{
				$show_data = 0;
			}
		}
		$this->assign(['new_area'=>$new_area,'address_street'=>$address_street,'address'=>$address]);
		return $this->fetch('member/address/post');
	}

	public function addresssetdefault() 
	{
		$mid = 0;
		$id = intval(input('id'));
		$data = Db::name('shop_member_address')->where(' id=' . $id . ' and deleted=0 ')->value('id');
		if( empty($data) ) 
		{
			show_json(0, "地址未找到");
		}
		Db::name('shop_member_address')->where(' mid=' . $mid)->update(array( "isdefault" => 0 ));
		Db::name('shop_member_address')->where(' mid=' . $mid . ' and id = ' . $id)->update(array( "isdefault" => 1 ));
		show_json(1);
	}

	private function extractNumber($string) 
	{
		$string = preg_replace("# #", "", $string);
		preg_match("/\\d{11}/", $string, $result);
		return (string) $result[0];
	}

	public function addresssubmit() 
	{
		$mid = 0;
		$id = intval(input('id'));
		$data = $_POST['addressdata'];
		$data["mobile"] = $this->extractNumber($data["mobile"]);
		$areas = explode(" ", $data["areas"]);
		list($data["province"], $data["city"], $data["area"]) = $areas;
		$data["street"] = trim($data["street"]);
		$data["datavalue"] = trim($data["datavalue"]);
		$data["streetdatavalue"] = trim($data["streetdatavalue"]);
		$isdefault = intval($data["isdefault"]);
		unset($data["isdefault"]);
		unset($data["areas"]);
		$data["mid"] = $mid;
		if( empty($id) ) 
		{
			$addresscount = Db::name('shop_member_address')->where('mid = ' . $mid . ' and deleted=0')->count();
			if( $addresscount <= 0 ) 
			{
				$data["isdefault"] = 1;
			}
			$id = Db::name('shop_member_address')->insertGetId($data);
		}
		else 
		{
			$data["lng"] = "";
			$data["lat"] = "";
			Db::name('shop_member_address')->where('id = ' . $id . ' and mid = ' . $mid)->update($data);
		}
		if( !empty($isdefault) ) 
		{
			Db::name('shop_member_address')->where(' mid=' . $mid)->update(array( "isdefault" => 0 ));
			Db::name('shop_member_address')->where(' mid=' . $mid . ' and id = ' . $id)->update(array( "isdefault" => 1 ));
		}
		show_json(1, array( "addressid" => $id ));
	}

	public function addressdelete() 
	{
		$mid = 0;
		$id = intval(input('id'));
		$data = Db::name('shop_member_address')->where(' id=' . $id . ' and deleted=0 and mid = ' . $mid)->field('id,isdefault')->find();
		if( empty($data) ) 
		{
			show_json(0, "地址未找到");
		}
		Db::name('shop_member_address')->where('id = ' . $id)->update(array( "deleted" => 1 ));
		if( $data["isdefault"] == 1 ) 
		{
			Db::name('shop_member_address')->where('id = ' . $id . ' and mid = ' . $mid)->update(array( "isdefault" => 0 ));
			$data2 = Db::name('shop_member_address')->where('mid = ' . $mid . ' and deleted=0')->order('id desc')->field('id')->find();
			if( !empty($data2) ) 
			{
				Db::name('shop_member_address')->where(' mid=' . $mid . ' and id = ' . $data2["id"])->update(array( "isdefault" => 1 ));
				show_json(1, array( "defaultid" => $data2["id"] ));
			}
		}
		show_json(1);
	}

	public function addressselector() 
	{
		$mid = 0;
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		$condition = " mid=" . $mid . " and deleted=0 ";
		$list = Db::name('shop_member_address')->where($condition)->order('isdefault desc, id DESC')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('member/address/selector');
		exit();
	}

	public function addressgetselector() 
	{
		$mid = 0;
		$condition = " mid=" . $mid . " and deleted=0 ";
		$keywords = input('keywords');
		if( !empty($keywords) ) 
		{
			$condition .= " AND (`realname` LIKE '%" . trim($keywords) . "%' OR `mobile` LIKE '%" . trim($keywords) . "%' OR `province` LIKE '%" . trim($keywords) . "%' OR `city` LIKE '%" . trim($keywords) . "%' OR `area` LIKE '%" . trim($keywords) . "%' OR `address` LIKE '%" . trim($keywords) . "%' OR `street` LIKE '%" . trim($keywords) . "%')";
		}
		$list = Db::name('shop_member_address')->where($condition)->order('isdefault desc, id DESC')->select();
		foreach( $list as &$item ) 
		{
			$item["editurl"] = url("member/addresspost", array( "id" => $item["id"] ));
		}
		unset($item);
		if( 0 < count($list) ) 
		{
			show_json(1, array( "list" => $list ));
		}
		else 
		{
			show_json(0);
		}
	}

}