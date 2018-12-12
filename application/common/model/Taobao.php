<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Taobao extends \think\Model
{
	private $num = 0;
	public static function get_item_taobao($itemid = "", $taobaourl = "", $cates = "", $merchid = 0) 
	{
		error_reporting(0);
		$g = Db::name('shop_goods')->where('merchid=' . $merchid . ' and catch_id=' . $itemid . ' and catch_source="taobao"')->find();
		$item = array( );
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) {
			if( empty($_W["merch_user"]["goodschecked"]) ) {
				$item["checked"] = 1;
			} else {
				$item["checked"] = 0;
			}
		}
		$url = self::get_tmall_page_url($itemid);
		$response = ihttp_get($url);
		$length = strval($response["headers"]["Content-Length"]);
		if( $length != NULL ) {
			return array( "result" => "0", "error" => "未从淘宝获取到商品信息!" );
		}
		$content = $response["content"];
		if( function_exists("mb_convert_encoding") ) {
			$content = mb_convert_encoding($content, "UTF-8", "UTF-8,GBK,GB2312,BIG5");
		}
		if( strexists($response["content"], "ERRCODE_QUERY_DETAIL_FAIL") ) {
			return array( "result" => "0", "error" => "宝贝不存在!" );
		}
		$dom = new \DOMDocument();
		$dom->loadHTML("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>" . $content);
		$xml = simplexml_import_dom($dom);
		preg_match("/var g_config\\s*=(.*);/isU", $content, $match);
		$matchOne = str_replace(array( " ", "\r", "\n", "\t" ), array( "" ), $match[1]);
		$erdr = substr($matchOne, stripos($matchOne, "sibUrl"));
		$erdr2 = substr($erdr, 0, stripos($erdr, "descUrl"));
		$asd = explode(":", $erdr2);
		$two = substr($asd[1], 1);
		$threeUrl = substr($two, 0, -2);
		$detailskip = ihttp_request("https:" . $threeUrl, "", array( "referer" => "https://item.taobao.com?id=" . $itemid, "accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8", "accept-encoding" => "", "accept-language" => "zh-CN,zh;q=0.9,en;q=0.8", "user-agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36", "CURLOPT_USERAGENT" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36" ));
		$detailskip = json_decode($detailskip["content"], true);
		$stockArray = array( );
		if( $detailskip["code"]["code"] == 0 && $detailskip["code"]["message"] == "SUCCESS" ) 
		{
			$stockArray = $detailskip["data"]["dynStock"]["sku"];
		}
		$specifications = $xml->xpath("//*[@id=\"J_isku\"]/div/dl/dd/ul");
		$specificationsArray = array( );
		$guigeArr = array( );
		foreach( $specifications as $key => $specificationsInfo ) 
		{
			$sizeArray = (array) $specificationsInfo;
			$sizeAttributesArray = explode(":", $sizeArray["@attributes"]["data-property"]);
			$specificationsArray[$key]["title"] = $sizeAttributesArray[0];
			$sizeLiArray = $sizeArray["li"];
			if( !is_object($sizeLiArray) ) 
			{
				$specificationsArray[$key]["itemsCount"] = count($sizeLiArray);
				foreach( $sizeLiArray as $j => $sizeLiInfo ) 
				{
					$sizeLiInfoArray = (array) $sizeLiInfo;
					$guigeArr[$key][$j][] = ";" . $sizeLiInfoArray["@attributes"]["data-value"];
					$sizeLiInfoAttributesArray = explode(":", $sizeLiInfoArray["@attributes"]["data-value"]);
					list($specificationsArray[$key]["propId"], $specificationsArray[$key]["items"][$j]["valueId"]) = $sizeLiInfoAttributesArray;
					$sizeLiInfoA = (array) $sizeLiInfoArray["a"];
					$specificationsTitle = (array) $sizeLiInfoA["span"];
					$specificationsArray[$key]["items"][$j]["title"] = $specificationsTitle[0];
					$guigeArr[$key][$j][] = $specificationsTitle[0];
					$sizeLiInfoAttr = $sizeLiInfoA["@attributes"];
					if( !empty($sizeLiInfoAttr["style"]) ) 
					{
						$sizeLiInfoAttrStyle = substr($sizeLiInfoAttr["style"], stripos($sizeLiInfoAttr["style"], "//"));
						$sizeLiInfoAttrStyleUrl = substr($sizeLiInfoAttrStyle, 0, stripos($sizeLiInfoAttrStyle, ")"));
						$thumb = mb_substr($sizeLiInfoAttrStyleUrl, 0, strpos($sizeLiInfoAttrStyleUrl, "_30x30.jpg"));
						$specificationsArray[$key]["items"][$j]["thumb"] = "http:" . $thumb;
					}
					else 
					{
						$specificationsArray[$key]["items"][$j]["thumb"] = "";
					}
				}
			}
			else 
			{
				$objsctArr = (array) $sizeLiArray;
				$specificationsArray[$key]["itemsCount"] = 1;
				$objsctArrAttributes = explode(":", $objsctArr["@attributes"]["data-value"]);
				list($specificationsArray[$key]["propId"], $specificationsArray[$key]["items"][0]["valueId"]) = $objsctArrAttributes;
				$sizeLiInfoA = (array) $objsctArr["a"];
				$specificationsTitle = (array) $sizeLiInfoA["span"];
				$specificationsArray[$key]["items"][0]["title"] = $specificationsTitle[0];
				$guigeArr[$key][0][] = ";" . $objsctArr["@attributes"]["data-value"];
				$guigeArr[$key][0][] = $specificationsTitle[0];
				$sizeLiInfoAttr = $sizeLiInfoA["@attributes"];
				if( !empty($sizeLiInfoAttr["style"]) ) 
				{
					$sizeLiInfoAttrStyle = substr($sizeLiInfoAttr["style"], stripos($sizeLiInfoAttr["style"], "//"));
					$sizeLiInfoAttrStyleUrl = substr($sizeLiInfoAttrStyle, 0, stripos($sizeLiInfoAttrStyle, ")"));
					$thumb = mb_substr($sizeLiInfoAttrStyleUrl, 0, strpos($sizeLiInfoAttrStyleUrl, "_30x30.jpg"));
					$specificationsArray[$key]["items"][0]["thumb"] = "http:" . $thumb;
				}
				else 
				{
					$specificationsArray[$key]["items"][0]["thumb"] = "";
				}
			}
		}
		$item["specs"] = self::my_sort($specificationsArray, "itemsCount", SORT_ASC, SORT_STRING);
		$count = count($guigeArr);
		if( $count == 1 ) 
		{
			for( $i = 0; $i < count($guigeArr[0]);
			$i++ ) 
			{
				$value = $guigeArr[0][$i][0];
				$title = $guigeArr[0][$i][1];
				$arr[] = $value . ";|" . $title;
			}
		}
		else 
		{
			if( $count == 2 ) 
			{
				for( $i = 0; $i < count($guigeArr[0]);
				$i++ ) 
				{
					$value = $guigeArr[0][$i][0];
					$title = $guigeArr[0][$i][1];
					for( $j = 0; $j < count($guigeArr[1]);
					$j++ ) 
					{
						$valueTwo = $value . $guigeArr[1][$j][0];
						$titleTwo = $title . "+" . $guigeArr[1][$j][1];
						$arr[] = $valueTwo . ";|" . $titleTwo;
					}
				}
			}
			else 
			{
				if( $count == 3 ) 
				{
					for( $i = 0; $i < count($guigeArr[0]);
					$i++ ) 
					{
						$value = $guigeArr[0][$i][0];
						$title = $guigeArr[0][$i][1];
						for( $j = 0; $j < count($guigeArr[1]);
						$j++ ) 
						{
							$valueTwo = $value . $guigeArr[1][$j][0];
							$titleTwo = $title . "+" . $guigeArr[1][$j][1];
							for( $g = 0; $g < count($guigeArr[2]);
							$g++ ) 
							{
								$valueThree = $valueTwo . $guigeArr[2][$g][0];
								$titleThree = $titleTwo . "+" . $guigeArr[2][$g][1];
								$arr[] = $valueThree . ";|" . $titleThree;
							}
						}
					}
				}
				else 
				{
					if( $count == 4 ) 
					{
						for( $i = 0; $i < count($guigeArr[0]);
						$i++ ) 
						{
							$value = $guigeArr[0][$i][0];
							$title = $guigeArr[0][$i][1];
							for( $j = 0; $j < count($guigeArr[1]);
							$j++ ) 
							{
								$valueTwo = $value . $guigeArr[1][$j][0];
								$titleTwo = $title . "+" . $guigeArr[1][$j][1];
								for( $g = 0; $g < count($guigeArr[2]);
								$g++ ) 
								{
									$valueThree = $valueTwo . $guigeArr[2][$g][0];
									$titleThree = $titleTwo . "+" . $guigeArr[2][$g][1];
									for( $r = 0; $r < count($guigeArr[3]);
									$r++ ) 
									{
										$valueFour = $valueThree . $guigeArr[3][$r][0];
										$titleFour = $titleThree . "+" . $guigeArr[3][$r][1];
										$arr[] = $valueFour . ";|" . $titleFour;
									}
								}
							}
						}
					}
					else 
					{
						if( $count == 5 ) 
						{
							for( $i = 0; $i < count($guigeArr[0]);
							$i++ ) 
							{
								$value = $guigeArr[0][$i][0];
								$title = $guigeArr[0][$i][1];
								for( $j = 0; $j < count($guigeArr[1]);
								$j++ ) 
								{
									$valueTwo = $value . $guigeArr[1][$j][0];
									$titleTwo = $title . "+" . $guigeArr[1][$j][1];
									for( $g = 0; $g < count($guigeArr[2]);
									$g++ ) 
									{
										$valueThree = $valueTwo . $guigeArr[2][$g][0];
										$titleThree = $titleTwo . "+" . $guigeArr[2][$g][1];
										for( $r = 0; $r < count($guigeArr[3]);
										$r++ ) 
										{
											$valueFour = $valueThree . $guigeArr[3][$g][0];
											$titleFour = $titleThree . "+" . $guigeArr[3][$g][1];
											for( $t = 0; $t < count($guigeArr[4]);
											$t++ ) 
											{
												$valueFive = $valueFour . $guigeArr[4][$t][0];
												$titleFive = $titleFour . "+" . $guigeArr[4][$t][1];
												$arr[] = $valueFive . ";|" . $titleFive;
											}
										}
									}
								}
							}
						}
						else 
						{
							if( $count == 6 ) 
							{
								for( $i = 0; $i < count($guigeArr[0]);
								$i++ ) 
								{
									$value = $guigeArr[0][$i][0];
									$title = $guigeArr[0][$i][1];
									for( $j = 0; $j < count($guigeArr[1]);
									$j++ ) 
									{
										$valueTwo = $value . $guigeArr[1][$j][0];
										$titleTwo = $title . "+" . $guigeArr[1][$j][1];
										for( $g = 0; $g < count($guigeArr[2]);
										$g++ ) 
										{
											$valueThree = $valueTwo . $guigeArr[2][$g][0];
											$titleThree = $titleTwo . "+" . $guigeArr[2][$g][1];
											for( $r = 0; $r < count($guigeArr[3]);
											$r++ ) 
											{
												$valueFour = $valueThree . $guigeArr[3][$g][0];
												$titleFour = $titleThree . "+" . $guigeArr[3][$g][1];
												for( $t = 0; $t < count($guigeArr[4]);
												$t++ ) 
												{
													$valueFive = $valueFour . $guigeArr[4][$t][0];
													$titleFive = $titleFour . "+" . $guigeArr[4][$t][1];
													for( $k = 0; $k < count($guigeArr[5]);
													$k++ ) 
													{
														$valueSix = $valueFive . $guigeArr[5][$k][0];
														$titleSix = $titleFive . "+" . $guigeArr[5][$k][1];
														$arr[] = $valueSix . ";|" . $titleSix;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		$item["options"] = array( );
		$item["total"] = 0;
		foreach( $arr as $key => $asdInfo ) 
		{
			$asdInfoArrAs = explode("|", $asdInfo);
			$asdInfoArr = explode(";", $asdInfoArrAs[0]);
			$asdInfoArr = array_filter($asdInfoArr);
			$j = 0;
			foreach( $asdInfoArr as $asdInfoArrInfo ) 
			{
				$asdInfoArrInfoArr = explode(":", $asdInfoArrInfo);
				list($item["options"][$key]["option_specs"][$j]["propId"], $item["options"][$key]["option_specs"][$j]["valueId"]) = $asdInfoArrInfoArr;
				$j++;
			}
			if( !empty($stockArray[$asdInfoArrAs[0]]) ) 
			{
				$item["options"][$key]["stock"] = $stockArray[$asdInfoArrAs[0]]["stock"];
				$item["total"] = $item["total"] + $stockArray[$asdInfoArrAs[0]]["stock"];
			}
			else 
			{
				$item["options"][$key]["stock"] = 0;
			}
			$item["options"][$key]["title"] = explode("+", $asdInfoArrAs[1]);
			$item["options"][$key]["marketprice"] = $detailskip["data"]["price"];
		}
		$prodectNameContent = $xml->xpath("//*[@id=\"J_Title\"]");
		$titleArr = (array) $prodectNameContent[0];
		$item["title"] = trim(strval($titleArr["h3"]));
		$prodectDescContent = $xml->xpath("//div/div/div/div/div/div/div/div/div/div/div[1]");
		$item["subTitle"] = trim(strval($prodectDescContent[1]->p));
		$prodectPrice = $xml->xpath("//*[@id=\"J_StrPrice\"]");
		$prodectPriceArr = (array) $prodectPrice[0];
		$taoBaoPrice = trim(strval($prodectPriceArr["em"][1]));
		$taoBaoPriceArr = explode("-", $taoBaoPrice);
		$item["productPrice"] = $taoBaoPriceArr[0];
		$imgs = array( );
		for( $i = 1; $i < 6; $i++ ) 
		{
			$img = $xml->xpath("//*[@id=\"J_UlThumb\"]/li[" . $i . "]");
			if( !empty($img) ) 
			{
				$img = strval($img[0]->div->a->img["data-src"]);
				$img = mb_substr($img, 0, strpos($img, "_50x50.jpg"));
				$imgArr = explode(":", $img);
				if( count($imgArr) == 2 ) 
				{
					$img = "http:" . $imgArr[1];
				}
				else 
				{
					$img = "http:" . $imgArr[0];
				}
				$imgs[] = $img;
			}
		}
		$item["pics"] = $imgs;
		$paramsContent = $xml->xpath("//*[@id=\"attributes\"]");
		$paramsContent = $paramsContent[0]->ul->li;
		$paramsContent = (array) $paramsContent;
		if( !empty($paramsContent["@attributes"]) ) 
		{
			unset($paramsContent["@attributes"]);
		}
		$params = array( );
		foreach( $paramsContent as $paramitem ) 
		{
			$paramitem = strval($paramitem);
			if( !empty($paramitem) ) 
			{
				$paramitem = trim(str_replace("：", ":", $paramitem));
				$p1 = mb_strpos($paramitem, ":");
				$ptitle = mb_substr($paramitem, 0, $p1);
				$pvalue = mb_substr($paramitem, $p1 + 1, mb_strlen($paramitem));
				$param = array( "title" => $ptitle, "value" => $pvalue );
				$params[] = $param;
			}
		}
		$item["params"] = $params;
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($cates) ) 
		{
			foreach( $cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$item["pcate"] = $pcateid;
		$item["ccate"] = $ccateid;
		$item["tcate"] = $tcateid;
		if( !empty($cates) ) 
		{
			$item["cates"] = implode(",", $cates);
		}
		$item["pcates"] = implode(",", $pcates);
		$item["ccates"] = implode(",", $ccates);
		$item["tcates"] = implode(",", $tcates);
		$url = self::get_taobao_detail_url($itemid);
		$response = ihttp_get($url);
		$response = self::contentpasswh($response);
		$item["content"] = $response;
		return self::save_taobao_goods($item, $taobaourl);
	}

	public static function get_item_taobao_old($itemid = "", $taobaourl = "", $cates = "", $merchid = 0) 
	{
		$g = Db::name('shop_goods')->where('merchid=' . $merchid . ' and catch_id=' . $itemid . ' and catch_source="taobao"')->find();
		$url = self::get_tmall_page_url($itemid);
		$response = ihttp_get($url);
		$length = strval($response["headers"]["Content-Length"]);
		if( $length != NULL ) 
		{
			return array( "result" => "0", "error" => "未从淘宝获取到商品信息!" );
		}
		$content = $response["content"];
		$content = mb_convert_encoding($content, "UTF-8", "UTF-8,GBK,GB2312,BIG5");
		$dom = new \DOMDocument();
		$dom->loadHTML("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>" . $content);
		$xml = simplexml_import_dom($dom);
		$item = array( );
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$item["checked"] = 1;
			}
			else 
			{
				$item["checked"] = 0;
			}
		}
		$prodectNameContent = $xml->xpath("//*[@id=\"J_DetailMeta\"]/div[1]");
		$prodectName = trim(strval($prodectNameContent[0]->h1));
		if( empty($prodectName) ) 
		{
			$prodectName = trim(strval($prodectNameContent[0]->h1->a));
		}
		$item["title"] = $prodectName;
		$url = self::get_taobao_info_url($itemid);
		$response = ihttp_get($url);
		if( !isset($response["content"]) ) 
		{
			return array( "result" => "0", "error" => "未从淘宝获取到商品信息!" );
		}
		$content = $response["content"];
		if( strexists($response["content"], "ERRCODE_QUERY_DETAIL_FAIL") ) 
		{
			return array( "result" => "0", "error" => "宝贝不存在!" );
		}
		$arr = json_decode($content, true);
		$data = $arr["data"];
		if( empty($data["apiStack"][0]["value"]) ) 
		{
			if( 2 <= self::num ) 
			{
				return array( "result" => "0", "error" => "规格库存详情不存在,请重新抓取" );
			}
			$this->num += 1;
			return self::get_item_taobao($itemid, $taobaourl, $cates, $merchid);
		}
		$itemInfoModel = $data["itemInfoModel"];
		$item = array( );
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$item["checked"] = 1;
			}
			else 
			{
				$item["checked"] = 0;
			}
		}
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($cates) ) 
		{
			foreach( $cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$item["pcate"] = $pcateid;
		$item["ccate"] = $ccateid;
		$item["tcate"] = $tcateid;
		if( !empty($cates) ) 
		{
			$item["cates"] = implode(",", $cates);
		}
		$item["pcates"] = implode(",", $pcates);
		$item["ccates"] = implode(",", $ccates);
		$item["tcates"] = implode(",", $tcates);
		$item["itemId"] = $itemInfoModel["itemId"];
		$item["title"] = $itemInfoModel["title"];
		$item["pics"] = $itemInfoModel["picsPath"];
		$params = array( );
		if( isset($data["props"]) ) 
		{
			$props = $data["props"];
			foreach( $props as $pp ) 
			{
				$params[] = array( "title" => $pp["name"], "value" => $pp["value"] );
			}
		}
		$item["params"] = $params;
		$specs = array( );
		$options = array( );
		if( isset($data["skuModel"]) ) 
		{
			$skuModel = $data["skuModel"];
			if( isset($skuModel["skuProps"]) ) 
			{
				$skuProps = $skuModel["skuProps"];
				foreach( $skuProps as $prop ) 
				{
					$spec_items = array( );
					foreach( $prop["values"] as $spec_item ) 
					{
						$spec_items[] = array( "valueId" => $spec_item["valueId"], "title" => $spec_item["name"], "thumb" => $spec_item["imgUrl"] );
					}
					$spec = array( "propId" => $prop["propId"], "title" => $prop["propName"], "items" => $spec_items );
					$specs[] = $spec;
				}
			}
			if( isset($skuModel["ppathIdmap"]) ) 
			{
				$ppathIdmap = $skuModel["ppathIdmap"];
				foreach( $ppathIdmap as $key => $skuId ) 
				{
					$option_specs = array( );
					$m = explode(";", $key);
					foreach( $m as $v ) 
					{
						$mm = explode(":", $v);
						$option_specs[] = array( "propId" => $mm[0], "valueId" => $mm[1] );
					}
					$options[] = array( "option_specs" => $option_specs, "skuId" => $skuId, "stock" => 0, "marketprice" => 0, "specs" => "" );
				}
			}
		}
		$item["specs"] = $specs;
		$stack = $data["apiStack"][0]["value"];
		$value = json_decode($stack, true);
		$item1 = array( );
		$data1 = $value["data"];
		$itemInfoModel1 = $data1["itemInfoModel"];
		$item["total"] = $itemInfoModel1["quantity"];
		$item["sales"] = $itemInfoModel1["totalSoldQuantity"];
		if( isset($data1["skuModel"]) ) 
		{
			$skuModel1 = $data1["skuModel"];
			if( isset($skuModel1["skus"]) ) 
			{
				$skus = $skuModel1["skus"];
				foreach( $skus as $key => $val ) 
				{
					$sku_id = $key;
					foreach( $options as &$o ) 
					{
						if( $o["skuId"] == $sku_id ) 
						{
							$o["stock"] = $val["quantity"];
							foreach( $val["priceUnits"] as $p ) 
							{
								$o["marketprice"] = $p["price"];
							}
							$titles = array( );
							foreach( $o["option_specs"] as $osp ) 
							{
								foreach( $specs as $sp ) 
								{
									if( $sp["propId"] == $osp["propId"] ) 
									{
										foreach( $sp["items"] as $spitem ) 
										{
											if( $spitem["valueId"] == $osp["valueId"] ) 
											{
												$titles[] = $spitem["title"];
											}
										}
									}
								}
							}
							$o["title"] = $titles;
						}
					}
					unset($o);
				}
			}
			else 
			{
				$mprice = 0;
				foreach( $itemInfoModel1["priceUnits"] as $p ) 
				{
					$mprice = $p["price"];
				}
				$item["marketprice"] = $mprice;
			}
		}
		else 
		{
			$mprice = 0;
			foreach( $itemInfoModel1["priceUnits"] as $p ) 
			{
				$mprice = $p["price"];
			}
			$item["marketprice"] = $mprice;
		}
		$item["options"] = $options;
		$item["content"] = array( );
		$url = self::get_taobao_detail_url($itemid);
		$response = ihttp_get($url);
		$response = self::contentpasswh($response);
		$item["content"] = $response;
		return self::save_taobao_goods($item, $taobaourl);
	}
	public static function get_item_tmall_bypage($itemid = "", $taobaourl = "", $cates = "", $merchid = 0) 
	{
		error_reporting(0);
		$g = Db::name('shop_goods')->where('merchid=' . $merchid . ' and catch_id=' . $itemid . ' and catch_source="taobao"')->find();
		$url = self::get_tmall_page_url($itemid);
		$response = ihttp_get($url);
		$length = strval($response["headers"]["Content-Length"]);
		if( $length != NULL ) 
		{
			return array( "result" => "0", "error" => "未从淘宝获取到商品信息!" );
		}
		$content = $response["content"];
		if( function_exists("mb_convert_encoding") ) 
		{
			$content = mb_convert_encoding($content, "UTF-8", "UTF-8,GBK,GB2312,BIG5");
		}
		$item = array( );
		$arr = array( );
		preg_match("/TShop\\.Setup\\(([\\s\\S]*)\\s+\\);/", $content, $arr);
		$arr = json_decode(trim($arr[1]), true);
		$item["marketprice"] = $arr["detail"]["defaultItemPrice"];
		$dom = new \DOMDocument();
		$dom->loadHTML("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>" . $content);
		$xml = simplexml_import_dom($dom);
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$item["checked"] = 1;
			}
			else 
			{
				$item["checked"] = 0;
			}
		}
		$prodectNameContent = $xml->xpath("//*[@id=\"J_DetailMeta\"]/div[1]/div[1]/div/div[1]");
		$prodectName = trim(strval($prodectNameContent[0]->h1));
		if( empty($prodectName) ) 
		{
			$prodectName = trim(strval($prodectNameContent[0]->h1->a));
		}
		$item["title"] = $prodectName;
		$item["total"] = 10;
		$imgs = array( );
		for( $i = 1; $i < 6; $i++ ) 
		{
			$img = $xml->xpath("//*[@id=\"J_UlThumb\"]/li[" . $i . "]/a/img");
			if( !empty($img) ) 
			{
				$img = strval($img[0]->attributes()->src);
				$img = mb_substr($img, 0, strpos($img, "_60x60q90.jpg"));
				$img = "http:" . $img;
				$imgs[] = $img;
			}
		}
		$item["pics"] = $imgs;
		$paramsContent = $xml->xpath("//*[@id=\"J_AttrList\"]");
		$paramsContent = $paramsContent[0]->ul->li;
		$paramsContent = (array) $paramsContent;
		if( !empty($paramsContent["@attributes"]) ) 
		{
			unset($paramsContent["@attributes"]);
		}
		$params = array( );
		foreach( $paramsContent as $paramitem ) 
		{
			$paramitem = strval($paramitem);
			if( !empty($paramitem) ) 
			{
				$paramitem = trim(str_replace("：", ":", $paramitem));
				$p1 = mb_strpos($paramitem, ":");
				$ptitle = mb_substr($paramitem, 0, $p1);
				$pvalue = mb_substr($paramitem, $p1 + 1, mb_strlen($paramitem));
				$param = array( "title" => $ptitle, "value" => $pvalue );
				$params[] = $param;
			}
		}
		$item["params"] = $params;
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($cates) ) 
		{
			foreach( $cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$item["pcate"] = $pcateid;
		$item["ccate"] = $ccateid;
		$item["tcate"] = $tcateid;
		if( !empty($cates) ) 
		{
			$item["cates"] = implode(",", $cates);
		}
		$item["pcates"] = implode(",", $pcates);
		$item["ccates"] = implode(",", $ccates);
		$item["tcates"] = implode(",", $tcates);
		$url = self::get_tmall_detail_url($itemid);
		$response = ihttp_get($url);
		preg_match_all("/data\\-ks\\-lazyload=\"(http.+?)\"/i", $response["content"], $matches);
		$item["content"] = $matches;
		return self::save_tmall_goods($item, $taobaourl);
	}
	public static function get_item_jingdong($itemid = "", $jingdongurl = "", $cates = "", $merchid = 0) 
	{
		error_reporting(0);
		$g = Db::name('shop_goods')->where('merchid=' . $merchid . ' and catch_id=' . $itemid . ' and catch_source="jingdong"');
		$item = array( );
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$item["checked"] = 1;
			}
			else 
			{
				$item["checked"] = 0;
			}
		}
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($cates) ) 
		{
			foreach( $cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$item["pcate"] = $pcateid;
		$item["ccate"] = $ccateid;
		$item["tcate"] = $tcateid;
		if( !empty($cates) ) 
		{
			$item["cates"] = implode(",", $cates);
		}
		$item["pcates"] = implode(",", $pcates);
		$item["ccates"] = implode(",", $ccates);
		$item["tcates"] = implode(",", $tcates);
		$item["itemId"] = $itemid;
		$item["total"] = 10;
		$item["sales"] = 0;
		$priceurl = self::get_jingdong_price_url($itemid);
		$responsePrice = ihttp_get($priceurl);
		$contentePrice = $responsePrice["content"];
		if( empty($contentePrice) ) 
		{
			return array( "result" => "0", "error" => "未从京东获取到商品信息!" );
		}
		$price = json_decode($contentePrice, true);
		$item["marketprice"] = $price[0]["p"];
		$url = self::get_jingdong_detail_url($itemid);
		$responseDetail = ihttp_get($url);
		$contenteDetail = $responseDetail["content"];
		$details = json_decode($contenteDetail, true);
		$item["title"] = $details["ware"]["wname"];
		$pics = array( );
		$imgurls = $details["ware"]["images"];
		foreach( $imgurls as $imgurl ) 
		{
			if( count($pics) < 4 ) 
			{
				if( count($pics) == 0 ) 
				{
					$iurl = $imgurl["bigpath"];
					if( stripos($iurl, "//") == 0 ) 
					{
						$iurl .= "http:" . $iurl;
					}
					$pics[] = $iurl;
				}
				else 
				{
					$iurl = $imgurl["bigpath"];
					if( stripos($iurl, "//") == 0 ) 
					{
						$iurl .= "http:" . $iurl;
					}
					$pics[] = $iurl;
				}
			}
		}
		$item["pics"] = $pics;
		$specs = array( );
		$prodectContent = $details["wdis"];
		$prodectContent = strval($prodectContent);
		$prodectContent = self::contentpasswh($prodectContent);
		$item["content"] = $prodectContent;
		$params = array( );
		$pr = $details["ware"]["wi"]["code"];
		$pr = json_decode($pr, 1);
		foreach( $pr as $value ) 
		{
			foreach( $value as $key => $val ) 
			{
				if( is_array($val) ) 
				{
					$paramsValue = "";
					foreach( $val as $v ) 
					{
						foreach( $v as $k1 => $v1 ) 
						{
							if( !empty($v1) ) 
							{
								$params[] = array( "title" => $k1, "value" => $v1 );
							}
						}
					}
				}
				else 
				{
					if( !empty($val) ) 
					{
						$params[] = array( "title" => $key, "value" => $val );
					}
				}
			}
		}
		$item["params"] = $params;
		return self::save_jingdong_goods($item, $jingdongurl);
	}
	public static function get_item_jdHK($itemid = "", $jingdongurl = "", $cates = "", $merchid = 0) 
	{
		error_reporting(0);
		$g = Db::name('shop_goods')->where('merchid=' . $merchid . ' and catch_id= ' . $itemid . ' and catch_source="jingdong"')->find();
		$url = "http://item.jd.hk/" . $itemid . ".html";
		$response = ihttp_get($url);
		$length = strval($response["headers"]["Content-Length"]);
		if( empty($length) ) 
		{
			return array( "result" => "0", "error" => "未从京东获取到商品信息!" );
		}
		$content = iconv("GBK", "UTF-8", $response["content"]);
		preg_match("/<div class=\"sku-name\">\\n{1}[\\s\\S\\n]*<\\/span>\\n(.+)<\\/div>\\n\\s*<div class=\"news\">/", $content, $prodectName);
		$prodectName = trim($prodectName[1]);
		if( $prodectName == NULL ) 
		{
			return array( "result" => "0", "error" => "宝贝不存在!" );
		}
		$item = array( );
		$item["id"] = $g["id"];
		$item["merchid"] = $merchid;
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$item["checked"] = 1;
			}
			else 
			{
				$item["checked"] = 0;
			}
		}
		$pcates = array( );
		$ccates = array( );
		$tcates = array( );
		$pcateid = 0;
		$ccateid = 0;
		$tcateid = 0;
		if( is_array($cates) ) 
		{
			foreach( $cates as $key => $cid ) 
			{
				$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
				if( $c["level"] == 1 ) 
				{
					$pcates[] = $cid;
				}
				else 
				{
					if( $c["level"] == 2 ) 
					{
						$ccates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 3 ) 
						{
							$tcates[] = $cid;
						}
					}
				}
				if( $key == 0 ) 
				{
					if( $c["level"] == 1 ) 
					{
						$pcateid = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$crow = Db::name('shop_goods_category')->where('id',$cid)->field('parentid')->find();
							$pcateid = $crow["parentid"];
							$ccateid = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcateid = $cid;
								$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
								$ccateid = $tcate["parentid"];
								$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
								$pcateid = $ccate["parentid"];
							}
						}
					}
				}
			}
		}
		$item["pcate"] = $pcateid;
		$item["ccate"] = $ccateid;
		$item["tcate"] = $tcateid;
		if( !empty($cates) ) 
		{
			$item["cates"] = implode(",", $cates);
		}
		$item["pcates"] = implode(",", $pcates);
		$item["ccates"] = implode(",", $ccates);
		$item["tcates"] = implode(",", $tcates);
		$item["itemId"] = $itemid;
		$item["title"] = $prodectName;
		$pics = array( );
		preg_match_all("/<img.+src='(.+)' data-url.+data-img='1' width='75' height='75'>/", $content, $picRet);
		if( empty($picRet[1]) ) 
		{
			return array( "result" => "0", "error" => "不能抓取到图片" );
		}
		foreach( $picRet[1] as $pic ) 
		{
			$pics[] = "https:" . str_replace("s75x75", "s450x450", $pic);
		}
		$item["pics"] = $pics;
		$specs = array( );
		$item["total"] = 10;
		$item["sales"] = 0;
		$priceContent = ihttp_get("https://p.3.cn/prices/mgets?skuIds=J_" . $itemid);
		$prodectPrices = json_decode($priceContent["content"], 1);
		$item["marketprice"] = $prodectPrices[0]["p"];
		$url = self::get_jingdong_detail_url($itemid);
		$responseDetail = ihttp_get($url);
		$contenteDetail = $responseDetail["content"];
		$details = json_decode($contenteDetail, true);
		$prodectContent = $details["wdis"];
		$prodectContent = strval($prodectContent);
		$prodectContent = self::contentpasswh($prodectContent);
		$item["content"] = $prodectContent;
		$params = array( );
		$pr = $details["ware"]["wi"]["code"];
		preg_match_all("/<td class=\"tdTitle\">(.*?)<\\/td>/i", $pr, $params1);
		preg_match_all("/<td>(.*?)<\\/td>/i", $pr, $params2);
		$paramsTitle = $params1[1];
		$paramsValue = $params2[1];
		if( count($paramsTitle) == count($paramsValue) ) 
		{
			for( $i = 0; $i < count($paramsTitle);
			$i++ ) 
			{
				$params[] = array( "title" => $paramsTitle[$i], "value" => $paramsValue[$i] );
			}
		}
		$item["params"] = $params;
		return self::save_jingdong_goods($item, $jingdongurl);
	}
	public static function save_taobao_goods($item = array( ), $catch_url = "") 
	{
		$data = array("subtitle" => $item["subTitle"], "merchid" => intval($item["merchid"]), "checked" => $item["checked"], "catch_source" => "taobao", "catch_id" => intval($item["itemId"]), "catch_url" => $catch_url, "title" => $item["title"], "total" => $item["total"], "marketprice" => $item["marketprice"], "productprice" => $item["productPrice"], "pcate" => $item["pcate"], "ccate" => $item["ccate"], "tcate" => $item["tcate"], "cates" => $item["cates"], "sales" => $item["sales"], "createtime" => time(), "updatetime" => time(), "hasoption" => (0 < count($item["options"]) ? 1 : 0), "status" => 0, "deleted" => 0, "buylevels" => "", "showlevels" => "", "buygroups" => "", "showgroups" => "", "noticeopenid" => "", "storeids" => "", "merchsale" => ($item["merchid"] == 0 ? 0 : 1), "newgoods" => 1 );
		if( empty($item["merchid"]) ) 
		{
			$data["discounts"] = "{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}";
		}
		$thumb_url = array( );
		$pics = $item["pics"];
		$piclen = count($pics);
		if( 0 < $piclen ) 
		{
			$img = self::save_image($pics[0], false);
			if( empty($img) ) 
			{
				$img = $pics[0];
			}
			$info = getimagesize("../attachment/" . $img);
			$srcFileExtImg = $info["mime"];
			if( $srcFileExtImg == "image/x-ms-bmp" ) 
			{
				$mig = self::changeBMPtoJPG("../attachment/" . $img);
			}
			else 
			{
				$mig = $img;
			}
			$data["thumb"] = $mig;
			if( 1 < $piclen ) 
			{
				for( $i = 1; $i < $piclen; $i++ ) 
				{
					$img = self::save_image($pics[$i], false);
					if( empty($img) ) 
					{
						$img = $pics[$i];
					}
					$thumb_url[] = $img;
				}
			}
		}
		$mi = array( );
		foreach( $thumb_url as $thumb_Info ) 
		{
			$info = getimagesize("../attachment/" . $thumb_Info);
			$srcFileExt = $info["mime"];
			if( $srcFileExt == "image/x-ms-bmp" ) 
			{
				$mi[] = self::changeBMPtoJPG("../attachment/" . $thumb_Info);
			}
			else 
			{
				$mi[] = $thumb_Info;
			}
		}
		$data["thumb_url"] = serialize($mi);
		$goods = Db::name('shop_goods')->where('catch_id=' . intval($item["itemId"]) . ' and catch_source="taobao" and merchid=' . intval($item["merchid"]))->find();
		if( empty($goods) ) 
		{
			$goodsid = Db::name('shop_goods')->insertGetId($data);
		}
		else 
		{
			$goodsid = $goods["id"];
			unset($data["createtime"]);
			Db::name('shop_goods')->where("id = " . $goodsid)->update($data);
		}
		$goods_params = Db::name('shop_goods_param')->where("goodsid = " . $goodsid)->select();
		$params = $item["params"];
		$paramids = array( );
		$displayorder = 0;
		foreach( $params as $p ) 
		{
			$oldp = Db::name('shop_goods_param')->where('goodsid = ' . $goodsid . ' and title = "' . $p["title"] . '"')->find();
			$paramid = 0;
			$d = array( "goodsid" => $goodsid, "title" => $p["title"], "value" => $p["value"], "displayorder" => $displayorder );
			if( empty($oldp) ) 
			{
				$paramid = Db::name('shop_goods_param')->insertGetId($d);
			}
			else 
			{
				Db::name('shop_goods_param')->where('id',$oldp["id"])->update($d);
				$paramid = $oldp["id"];
			}
			$paramids[] = $paramid;
			$displayorder++;
		}
		if( 0 < count($paramids) ) {
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $paramids) . ")");
		} else {
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid = " . $goodsid);
		}
		$specs = $item["specs"];
		$specids = array( );
		$displayorder = 0;
		$newspecs = array( );
		foreach( $specs as $spec ) 
		{
			$oldspec = Db::name('shop_goods_spec')->where('goodsid=' . $goodsid . ' and propId= ' . $spec["propId"])->find();
			$specid = 0;
			$d_spec = array( "goodsid" => $goodsid, "title" => $spec["title"], "displayorder" => $displayorder, "propId" => $spec["propId"] );
			if( empty($oldspec) ) 
			{
				$specid = Db::name('shop_goods_spec')->insertGetId($d_spec);
			}
			else 
			{
				Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update($d_spec);
				$specid = $oldspec["id"];
			}
			$d_spec["id"] = $specid;
			$specids[] = $specid;
			$displayorder++;
			$spec_items = $spec["items"];
			$spec_itemids = array( );
			$displayorder_item = 0;
			$newspecitems = array( );
			foreach( $spec_items as $spec_item ) 
			{
				$d = array( "specid" => $specid, "title" => $spec_item["title"], "thumb" => self::save_image($spec_item["thumb"], false), "valueId" => $spec_item["valueId"], "show" => 1, "displayorder" => $displayorder_item );
				$oldspecitem = Db::name('shop_goods_spec_item')->where('specid=' . $specid . ' and valueId= ' .$spec_item["valueId"])->find();
				$spec_item_id = 0;
				if( empty($oldspecitem) ) 
				{
					$spec_item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
				}
				else 
				{
					Db::name('shop_goods_spec_item')->where('id',$oldspecitem["id"])->update($d);
					$spec_item_id = $oldspecitem["id"];
				}
				$displayorder_item++;
				$spec_itemids[] = $spec_item_id;
				$d["id"] = $spec_item_id;
				$newspecitems[] = $d;
			}
			$d_spec["items"] = $newspecitems;
			$newspecs[] = $d_spec;
			if( 0 < count($spec_itemids) ) 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid=" . $specid . " and id not in (" . implode(",", $spec_itemids) . ")");
			}
			else 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid= " . $specid);
			}
			Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update(array( "content" => serialize($spec_itemids) ));
		}
		if( 0 < count($specids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $specids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid= " . $goodsid);
		}
		$minprice = 0;
		$options = $item["options"];
		if( 0 < count($options) ) 
		{
			$minprice = $options[0]["marketprice"];
		}
		$optionids = array( );
		$displayorder = 0;
		foreach( $options as $o ) 
		{
			$option_specs = $o["option_specs"];
			$ids = array( );
			$valueIds = array( );
			foreach( $option_specs as $os ) 
			{
				foreach( $newspecs as $nsp ) 
				{
					foreach( $nsp["items"] as $nspitem ) 
					{
						if( $nspitem["valueId"] == $os["valueId"] ) 
						{
							$ids[] = $nspitem["id"];
							$valueIds[] = $nspitem["valueId"];
						}
					}
				}
			}
			asort($ids);
			$ids = implode("_", $ids);
			$valueIds = implode("_", $valueIds);
			$do = array( "displayorder" => $displayorder, "goodsid" => $goodsid, "title" => implode("+", $o["title"]), "specs" => $ids, "stock" => $o["stock"], "marketprice" => $o["marketprice"], "skuId" => $o["skuId"] );
			if( $o["marketprice"] < $minprice ) 
			{
				$minprice = $o["marketprice"];
			}
			$oldoption = Db::name('shop_goods_option')->where('goodsid=' . $goodsid . ' and skuId= ' . $o["skuId"])->find();
			$option_id = 0;
			if( empty($oldoption) ) 
			{
				$option_id = Db::name('shop_goods_option')->insertGetId($do);
			}
			else 
			{
				Db::name('shop_goods_option')->where('id',$oldoption["id"])->update($do);
				$option_id = $oldoption["id"];
			}
			$displayorder++;
			$optionids[] = $option_id;
		}
		if( 0 < count($optionids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_option") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $optionids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_option") . " where goodsid= " . $goodsid);
		}
		$response = $item["content"];
		$content = $response["content"];
		preg_match_all("/<img.*?src=[\\\\'| \\\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\'|\\\"].*?[\\/]?>/", $content, $imgs);
		if( isset($imgs[1]) ) 
		{
			foreach( $imgs[1] as $img ) 
			{
				$catchimg = $img;
				if( substr($catchimg, 0, 2) == "//" ) 
				{
					$img = "http://" . substr($img, 2);
				}
				$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
				$images[] = $im;
			}
		}
		preg_match("/tfsContent : \\'(.*)\\'/", $content, $html);
		$html = iconv("GBK", "UTF-8", $html[1]);
		if( isset($images) ) 
		{
			foreach( $images as $img ) 
			{
				if( !empty($img["system"]) ) 
				{
					$html = str_replace($img["catchimg"], $img["system"], $html);
				}
			}
		}
		$html = model("common")->html_to_images($html);
		$hasoption = 0;
		if( 0 < count($options) ) 
		{
			$hasoption = 1;
		}
		$d = array( "content" => $html, "hasoption" => $hasoption );
		if( 0 < $minprice ) 
		{
			$d["marketprice"] = $minprice;
		}
		Db::name('shop_goods')->where('id',$goodsid)->update($d);
		if( $d["hasoption"] ) 
		{
			$sql = "update " . tablename("shop_goods") . " g set\r\n            g.minprice = (select min(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $goodsid . " and marketprice > 0),\r\n            g.maxprice = (select max(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $goodsid . ")\r\n            where g.id = " . $goodsid . " and g.hasoption=1";
		}
		else 
		{
			$sql = "update " . tablename("shop_goods") . " set minprice = marketprice,maxprice = marketprice where id = " . $goodsid . " and hasoption=0;";
		}
		Db::query($sql);
		return array( "result" => "1", "goodsid" => $goodsid );
	}

	public static function save_tmall_goods($item = array( ), $catch_url = "") 
	{
		$data = array( "subtitle" => $item["subTitle"], "merchid" => intval($item["merchid"]), "checked" => $item["checked"], "catch_source" => "taobao", "catch_id" => intval($item["itemId"]), "catch_url" => $catch_url, "title" => $item["title"], "total" => $item["total"], "marketprice" => $item["marketprice"], "productprice" => $item["productPrice"], "pcate" => $item["pcate"], "ccate" => $item["ccate"], "tcate" => $item["tcate"], "cates" => $item["cates"], "sales" => $item["sales"], "createtime" => time(), "updatetime" => time(), "hasoption" => (0 < count($item["options"]) ? 1 : 0), "status" => 0, "deleted" => 0, "buylevels" => "", "showlevels" => "", "buygroups" => "", "showgroups" => "", "noticeopenid" => "", "storeids" => "", "merchsale" => ($item["merchid"] == 0 ? 0 : 1), "newgoods" => 1 );
		if( empty($item["merchid"]) ) 
		{
			$data["discounts"] = "{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}";
		}
		$thumb_url = array( );
		$pics = $item["pics"];
		$piclen = count($pics);
		if( 0 < $piclen ) 
		{
			$img = self::save_image($pics[0], false);
			if( empty($img) ) 
			{
				$img = $pics[0];
			}
			$info = getimagesize("../attachment/" . $img);
			$srcFileExtImg = $info["mime"];
			if( $srcFileExtImg == "image/x-ms-bmp" ) 
			{
				$mig = self::changeBMPtoJPG("../attachment/" . $img);
			}
			else 
			{
				$mig = $img;
			}
			$data["thumb"] = $mig;
			if( 1 < $piclen ) 
			{
				for( $i = 1; $i < $piclen; $i++ ) 
				{
					$img = self::save_image($pics[$i], false);
					if( empty($img) ) 
					{
						$img = $pics[$i];
					}
					$thumb_url[] = $img;
				}
			}
		}
		$mi = array( );
		foreach( $thumb_url as $thumb_Info ) 
		{
			$info = getimagesize("../attachment/" . $thumb_Info);
			$srcFileExt = $info["mime"];
			if( $srcFileExt == "image/x-ms-bmp" ) 
			{
				$mi[] = self::changeBMPtoJPG("../attachment/" . $thumb_Info);
			}
			else 
			{
				$mi[] = $thumb_Info;
			}
		}
		$data["thumb_url"] = serialize($mi);
		$goods = Db::name('shop_goods')->where('catch_id=' . intval($item["itemId"]) . ' and catch_source="taobao" and merchid= ' . intval($item["merchid"]))->find();
		if( empty($goods) ) 
		{
			$goodsid = Db::name('shop_goods')->insertGetId($data);
		}
		else 
		{
			$goodsid = $goods["id"];
			unset($data["createtime"]);
			Db::name('shop_goods')->where('id',$goodsid)->update($data);
		}
		$goods_params = Db::name('shop_goods_param')->where('goodsid',$goodsid)->select();
		$params = $item["params"];
		$paramids = array( );
		$displayorder = 0;
		foreach( $params as $p ) 
		{
			$oldp = Db::name('shop_goods_param')->where('goodsid=' . $goodsid . ' and title="' . $p["title"] . '"')->find();
			$paramid = 0;
			$d = array( "goodsid" => $goodsid, "title" => $p["title"], "value" => $p["value"], "displayorder" => $displayorder );
			if( empty($oldp) ) 
			{
				$paramid = Db::name('shop_goods_param')->insertGetId($d);
			}
			else 
			{
				Db::name('shop_goods_param')->where('id',$oldp["id"])->update($d);
				$paramid = $oldp["id"];
			}
			$paramids[] = $paramid;
			$displayorder++;
		}
		if( 0 < count($paramids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $paramids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid);
		}
		$specs = $item["specs"];
		$specids = array( );
		$displayorder = 0;
		$newspecs = array( );
		foreach( $specs as $spec ) 
		{
			$oldspec = Db::name('shop_goods_spec')->where('goodsid=' . $goodsid . ' and propId=' . $spec["propId"])->find();
			$specid = 0;
			$d_spec = array( "goodsid" => $goodsid, "title" => $spec["title"], "displayorder" => $displayorder, "propId" => $spec["propId"] );
			if( empty($oldspec) ) 
			{
				$specid = Db::name('shop_goods_spec')->insertGetId($d_spec);
			}
			else 
			{
				Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update($d_spec);
				$specid = $oldspec["id"];
			}
			$d_spec["id"] = $specid;
			$specids[] = $specid;
			$displayorder++;
			$spec_items = $spec["items"];
			$spec_itemids = array( );
			$displayorder_item = 0;
			$newspecitems = array( );
			foreach( $spec_items as $spec_item ) 
			{
				$d = array( "specid" => $specid, "title" => $spec_item["title"], "thumb" => self::save_image($spec_item["thumb"], false), "valueId" => $spec_item["valueId"], "show" => 1, "displayorder" => $displayorder_item );
				$oldspecitem = Db::name('shop_goods_spec_item')->where('specid=' . $specid . ' and valueId=' . $spec_item["valueId"])->find();
				$spec_item_id = 0;
				if( empty($oldspecitem) ) 
				{
					$spec_item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
				}
				else 
				{
					Db::name('shop_goods_spec_item')->where('id',$oldspecitem["id"])->update($d);
					$spec_item_id = $oldspecitem["id"];
				}
				$displayorder_item++;
				$spec_itemids[] = $spec_item_id;
				$d["id"] = $spec_item_id;
				$newspecitems[] = $d;
			}
			$d_spec["items"] = $newspecitems;
			$newspecs[] = $d_spec;
			if( 0 < count($spec_itemids) ) 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid=" . $specid . " and id not in (" . implode(",", $spec_itemids) . ")");
			}
			else 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid= " . $specid);
			}
			Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update(array( "content" => serialize($spec_itemids) ));
		}
		if( 0 < count($specids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $specids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid= " . $goodsid);
		}
		$minprice = 0;
		$options = $item["options"];
		if( 0 < count($options) ) 
		{
			$minprice = $options[0]["marketprice"];
		}
		$optionids = array( );
		$displayorder = 0;
		foreach( $options as $o ) 
		{
			$option_specs = $o["option_specs"];
			$ids = array( );
			$valueIds = array( );
			foreach( $option_specs as $os ) 
			{
				foreach( $newspecs as $nsp ) 
				{
					foreach( $nsp["items"] as $nspitem ) 
					{
						if( $nspitem["valueId"] == $os["valueId"] ) 
						{
							$ids[] = $nspitem["id"];
							$valueIds[] = $nspitem["valueId"];
						}
					}
				}
			}
			asort($ids);
			$ids = implode("_", $ids);
			$valueIds = implode("_", $valueIds);
			$do = array( "displayorder" => $displayorder, "goodsid" => $goodsid, "title" => implode("+", $o["title"]), "specs" => $ids, "stock" => $o["stock"], "marketprice" => $o["marketprice"], "skuId" => $o["skuId"] );
			if( $o["marketprice"] < $minprice ) 
			{
				$minprice = $o["marketprice"];
			}
			$oldoption = Db::name('shop_goods_option')->where('goodsid=' . $goodsid . ' and skuId=' . $o["skuId"])->find();
			$option_id = 0;
			if( empty($oldoption) ) 
			{
				$option_id = Db::name('shop_goods_option')->insertGetId($do);
			}
			else 
			{
				Db::name('shop_goods_option')->where('id',$oldoption["id"])->update($do);
				$option_id = $oldoption["id"];
			}
			$displayorder++;
			$optionids[] = $option_id;
		}
		if( 0 < count($optionids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_option") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $optionids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_option") . " where goodsid=" . $goodsid);
		}
		$content = $item["content"];
		$imgs = $content;
		if( isset($imgs[1]) ) 
		{
			foreach( $imgs[1] as $img ) 
			{
				$catchimg = $img;
				if( substr($catchimg, 0, 2) == "//" ) 
				{
					$img = "http://" . substr($img, 2);
				}
				$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
				$images[] = $im;
			}
		}
		$str = "";
		$str .= "<div style='width: 100%;text-align: center'>";
		foreach( $images as $key => $val ) 
		{
			$src = $val["system"];
			$str .= "<img src=\"" . $src . "\">";
		}
		$str .= "</div>";
		$html = htmlspecialchars_decode($str);
		$hasoption = 0;
		if( 0 < count($options) ) 
		{
			$hasoption = 1;
		}
		$d = array( "content" => $html, "hasoption" => $hasoption );
		if( 0 < $minprice ) 
		{
			$d["marketprice"] = $minprice;
		}
		Db::name('shop_goods')->where('id',$goodsid)->update($d);
		if( $d["hasoption"] ) 
		{
			$sql = "update " . tablename("shop_goods") . " g set\r\n            g.minprice = (select min(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $goodsid . " and marketprice > 0),\r\n            g.maxprice = (select max(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $goodsid . ")\r\n            where g.id = " . $goodsid . " and g.hasoption=1";
		}
		else 
		{
			$sql = "update " . tablename("shop_goods") . " set minprice = marketprice,maxprice = marketprice where id = " . $goodsid . " and hasoption=0;";
		}
		Db::query($sql);
		return array( "result" => "1", "goodsid" => $goodsid );
	}

	public static function save_jingdong_goods($item = array( ), $catch_url = "") 
	{
		$data = array( "merchid" => intval($item["merchid"]), "checked" => $item["checked"], "catch_source" => "jingdong", "catch_id" => intval($item["itemId"]), "catch_url" => $catch_url, "title" => $item["title"], "total" => $item["total"], "marketprice" => $item["marketprice"], "pcate" => $item["pcate"], "ccate" => $item["ccate"], "tcate" => $item["tcate"], "cates" => $item["cates"], "sales" => $item["sales"], "createtime" => time(), "updatetime" => time(), "hasoption" => 0, "status" => 0, "deleted" => 0, "buylevels" => "", "showlevels" => "", "buygroups" => "", "showgroups" => "", "noticeopenid" => "", "storeids" => "", "minprice" => $item["marketprice"], "maxprice" => $item["marketprice"], "merchsale" => ($item["merchid"] == 0 ? 0 : 1), "newgoods" => 1 );
		if( empty($item["merchid"]) ) 
		{
			$data["discounts"] = "{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}";
		}
		$thumb_url = array( );
		$pics = $item["pics"];
		$piclen = count($pics);
		if( 0 < $piclen ) 
		{
			$img = self::save_image($pics[0], false);
			if( empty($img) ) 
			{
				$img = $pics[0];
			}
			$data["thumb"] = $img;
			if( 1 < $piclen ) 
			{
				for( $i = 1; $i < $piclen; $i++ ) 
				{
					$img = self::save_image($pics[$i], false);
					if( empty($img) ) 
					{
						$img = $pics[$i];
					}
					$thumb_url[] = $img;
				}
			}
		}
		$data["thumb_url"] = serialize($thumb_url);
		Db::name('shop_goods')->where('catch_id=' . intval($item["itemId"]) . ' and catch_source="jingdong" and merchid='. intval($item["merchid"]))->find();
		if( empty($goods) ) 
		{
			$goodsid = Db::name('shop_goods')->insertGetId($data);
		}
		else 
		{
			$goodsid = $goods["id"];
			unset($data["createtime"]);
			Db::name('shop_goods')->where('id',$goodsid)->update($data);
		}
		$goods_params = Db::name('shop_goods_param')->where('goodsid',$goodsid)->select();
		$params = $item["params"];
		$paramids = array( );
		$displayorder = 0;
		foreach( $params as $p ) 
		{
			Db::name('shop_goods_param')->where('goodsid=' . $goodsid . ' and title="' . $p["title"] . '"');
			$paramid = 0;
			$d = array( "goodsid" => $goodsid, "title" => $p["title"], "value" => $p["value"], "displayorder" => $displayorder );
			if( empty($oldp) ) 
			{
				$paramid = Db::name('shop_goods_param')->insertGetId($d);
			}
			else 
			{
				Db::name('shop_goods_param')->where('id',$oldp["id"])->update($d);
				$paramid = $oldp["id"];
			}
			$paramids[] = $paramid;
			$displayorder++;
		}
		if( 0 < count($paramids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $paramids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid);
		}
		$content = $item["content"];
		preg_match_all("/<img.*?src=[\\\\'| \\\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\'|\\\"].*?[\\/]?>/", $content, $imgs);
		if( isset($imgs[1]) ) 
		{
			foreach( $imgs[1] as $img ) 
			{
				$catchimg = $img;
				if( substr($catchimg, 0, 2) == "//" ) 
				{
					$img = "http://" . substr($img, 2);
				}
				$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
				$images[] = $im;
			}
		}
		$html = $content;
		if( isset($images) ) 
		{
			foreach( $images as $img ) 
			{
				if( !empty($img["system"]) ) 
				{
					$html = str_replace($img["catchimg"], $img["system"], $html);
				}
			}
		}
		$html = model("common")->html_to_images($html);
		$d = array( "content" => $html );
		Db::name('shop_goods')->where('id',$goodsid)->update($d);
		return array( "result" => "1", "goodsid" => $goodsid );
	}

	public static function save_1688_goods($item = array( ), $catch_url = "") 
	{
		$data = array( "merchid" => intval($item["merchid"]), "checked" => $item["checked"], "catch_source" => "1688", "catch_id" => intval($item["itemId"]), "catch_url" => $catch_url, "title" => $item["title"], "total" => $item["total"], "marketprice" => $item["marketprice"], "pcate" => $item["pcate"], "ccate" => $item["ccate"], "tcate" => $item["tcate"], "cates" => $item["cates"], "sales" => $item["sales"], "createtime" => time(), "updatetime" => time(), "hasoption" => 0, "status" => 0, "deleted" => 0, "buylevels" => "", "showlevels" => "", "buygroups" => "", "showgroups" => "", "noticeopenid" => "", "storeids" => "", "minprice" => $item["marketprice"], "maxprice" => $item["marketprice"], "merchsale" => ($item["merchid"] == 0 ? 0 : 1), "newgoods" => 1 );
		if( empty($item["merchid"]) ) 
		{
			$data["discounts"] = "{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}";
		}
		$thumb_url = array( );
		$pics = $item["pics"];
		$piclen = count($pics);
		if( 0 < $piclen ) 
		{
			$img = self::save_image($pics[0], false);
			if( empty($img) ) 
			{
				$img = $pics[0];
			}
			$data["thumb"] = $img;
			if( 1 < $piclen ) 
			{
				for( $i = 1; $i < $piclen; $i++ ) 
				{
					$img = self::save_image($pics[$i], false);
					if( empty($img) ) 
					{
						$img = $pics[$i];
					}
					$thumb_url[] = $img;
				}
			}
		}
		$data["thumb_url"] = serialize($thumb_url);
		$goods = Db::name('shop_goods')->where('catch_id=' . intval($item["itemId"]) . ' and catch_source="1688" and merchid='.intval($item["merchid"]))->find();
		if( empty($goods) ) 
		{
			$goodsid = Db::name('shop_goods')->insertGetId($data);
		}
		else 
		{
			$goodsid = $goods["id"];
			unset($data["createtime"]);
			Db::name('shop_goods')->where('id',$goodsid)->update($data);
		}
		$goods_params = Db::name('shop_goods_param')->where('goodsid = ' . $goodsid)->select();
		$params = $item["params"];
		$paramids = array( );
		$displayorder = 0;
		foreach( $params as $p ) 
		{
			$oldp = Db::name('shop_goods_param')->where('goodsid=' . $goodsid . ' and title="' . $p["title"] . '"');
			$paramid = 0;
			$d = array( "goodsid" => $goodsid, "title" => $p["title"], "value" => $p["value"], "displayorder" => $displayorder );
			if( empty($oldp) ) 
			{
				$paramid = Db::name('shop_goods_param')->insertGetId($d);
			}
			else 
			{
				Db::name('shop_goods_param')->where('id',$oldp["id"])->update($d);
				$paramid = $oldp["id"];
			}
			$paramids[] = $paramid;
			$displayorder++;
		}
		if( 0 < count($paramids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $paramids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_param") . " where goodsid= " . $goodsid);
		}
		$specs = $item["specs"];
		$specids = array( );
		$displayorder = 0;
		$newspecs = array( );
		foreach( $specs as $spec ) 
		{
			$oldspec = Db::name('shop_goods_spec')->where('goodsid=' . $goodsid . ' and propId=' . $spec["propId"])->find();
			$specid = 0;
			$d_spec = array( "goodsid" => $goodsid, "title" => $spec["title"], "displayorder" => $displayorder, "propId" => $spec["propId"] );
			if( empty($oldspec) ) 
			{
				$specid = Db::name('shop_goods_spec')->insertGetId($d_spec);
			}
			else 
			{
				Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update($d_spec);
				$specid = $oldspec["id"];
			}
			$d_spec["id"] = $specid;
			$specids[] = $specid;
			$displayorder++;
			$spec_items = $spec["items"];
			$spec_itemids = array( );
			$displayorder_item = 0;
			$newspecitems = array( );
			foreach( $spec_items as $spec_item ) 
			{
				$d = array( "specid" => $specid, "title" => $spec_item["title"], "thumb" => self::save_image($spec_item["thumb"], false), "valueId" => $spec_item["valueId"], "show" => 1, "displayorder" => $displayorder_item );
				$oldspecitem = Db::name('shop_goods_spec_item')->where('specid=' . $specid . ' and valueId=' . $spec_item["valueId"])->find();
				$spec_item_id = 0;
				if( empty($oldspecitem) ) 
				{
					$spec_item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
				}
				else 
				{
					Db::name('shop_goods_spec_item')->where('id',$oldspecitem["id"])->update($d);
					$spec_item_id = $oldspecitem["id"];
				}
				$displayorder_item++;
				$spec_itemids[] = $spec_item_id;
				$d["id"] = $spec_item_id;
				$newspecitems[] = $d;
			}
			$d_spec["items"] = $newspecitems;
			$newspecs[] = $d_spec;
			if( 0 < count($spec_itemids) ) 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid=" . $specid . " and id not in (" . implode(",", $spec_itemids) . ")");
			}
			else 
			{
				Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid= " . $specid);
			}
			Db::name('shop_goods_spec')->where('id',$oldspec["id"])->update(array( "content" => serialize($spec_itemids) ));
		}
		if( 0 < count($specids) ) 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=" . $goodsid . " and id not in (" . implode(",", $specids) . ")");
		}
		else 
		{
			Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=".$goodsid);
		}
		$content = $item["content"];
		preg_match_all("/<img.*?src=[\\\\'| \\\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\'|\\\"].*?[\\/]?>/", $content, $imgs);
		if( isset($imgs[1]) ) 
		{
			foreach( $imgs[1] as $img ) 
			{
				$catchimg = $img;
				if( substr($catchimg, 0, 2) == "//" ) 
				{
					$img = "http://" . substr($img, 2);
				}
				$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
				$images[] = $im;
			}
		}
		$html = $content;
		if( isset($images) ) 
		{
			foreach( $images as $img ) 
			{
				if( !empty($img["system"]) ) 
				{
					$html = str_replace($img["catchimg"], $img["system"], $html);
				}
			}
		}
		$html = model("common")->html_to_images($html);
		$d = array( "content" => $html );
		Db::name('shop_goods')->where('id',$goodsid)->update($d);
		return array( "result" => "1", "goodsid" => $goodsid );
	}

	public static function save_taobaocsv_goods($item = array( ), $merchid = 0) 
	{
		$data = array( "merchid" => $merchid, "catch_source" => "taobaocsv", "catch_id" => "", "catch_url" => "", "title" => $item["title"], "total" => $item["total"], "marketprice" => $item["marketprice"], "pcate" => "", "ccate" => "", "tcate" => "", "cates" => "", "sales" => 0, "createtime" => time(), "updatetime" => time(), "hasoption" => 0, "status" => 0, "deleted" => 0, "buylevels" => "", "showlevels" => "", "buygroups" => "", "showgroups" => "", "noticeopenid" => "", "storeids" => "", "minprice" => $item["marketprice"], "maxprice" => $item["marketprice"], "merchsale" => ($item["merchid"] == 0 ? 0 : 1) );
		if( empty($item["merchid"]) ) 
		{
			$data["discounts"] = "{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}";
		}
		if( !empty($merchid) ) 
		{
			if( empty($_W["merch_user"]["goodschecked"]) ) 
			{
				$data["checked"] = 1;
			}
			else 
			{
				$data["checked"] = 0;
			}
		}
		$thumb_url = array( );
		$pics = $item["pics"];
		$piclen = count($pics);
		if( 0 < $piclen ) 
		{
			$data["thumb"] = self::save_image($pics[0], false);
			if( 1 < $piclen ) 
			{
				for( $i = 1; $i < $piclen; $i++ ) 
				{
					$img = self::save_image($pics[$i], false);
					$thumb_url[] = $img;
				}
			}
		}
		$data["thumb_url"] = serialize($thumb_url);
		$goodsid = Db::name('shop_goods')->insertGetId($data);
		$content = $item["content"];
		preg_match_all("/<img.*?src=[\\\\'| \\\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\'|\\\"].*?[\\/]?>/", $content, $imgs);
		if( isset($imgs[1]) ) 
		{
			foreach( $imgs[1] as $img ) 
			{
				$catchimg = $img;
				if( substr($catchimg, 0, 2) == "//" ) 
				{
					$img = "http://" . substr($img, 2);
				}
				$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
				$images[] = $im;
			}
		}
		$html = $content;
		if( isset($images) ) 
		{
			foreach( $images as $img ) 
			{
				if( !empty($img["system"]) ) 
				{
					$html = str_replace($img["catchimg"], $img["system"], $html);
				}
			}
		}
		$html = model("common")->html_to_images($html);
		if( isset($images[0]) ) 
		{
			$d["thumb_url"] = serialize($images[0]);
			$d["thumb"] = $images[0]["catchimg"];
		}
		$d["content"] = $html;
		Db::name('shop_goods')->where('id',$goodsid)->update($d);
		return array( "result" => "1", "goodsid" => $goodsid );
	}

	public static function get_taobao_info_url($itemid) 
	{
		$url = "http://hws.m.taobao.com/cache/wdetail/5.0/?id=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_tmall_page_url($itemid) 
	{
		$url = "https://detail.tmall.com/item.htm?id=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_taobao_page_url($itemid) 
	{
		$url = "https://item.taobao.com/item.htm?id=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_taobao_detail_url($itemid) 
	{
		$url = "http://hws.m.taobao.com/cache/wdesc/5.0/?id=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_tmall_detail_url($itemid) 
	{
		$url = "https://detail.m.tmall.com/item.htm?id=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_jingdong_info_url($itemid) 
	{
		$url = "http://item.m.jd.com/ware/view.action?wareId=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_jingdong_detail_url($itemid) 
	{
		$url = "http://item.m.jd.com/ware/detail.json?wareId=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_jingdong_price_url($itemid) 
	{
		$url = "https://pe.3.cn/prices/mgets?skuids=" . $itemid;
		$url = self::getRealURL($url);
		return $url;
	}

	public static function get_1688_info_url($itemid) 
	{
		$url = "https://m.1688.com/offer/" . $itemid . ".html";
		return $url;
	}

	public static function save_image($url, $iscontent) 
	{
		$ext = strrchr($url, ".");
		if( $ext != ".jpeg" && $ext != ".gif" && $ext != ".jpg" && $ext != ".png" ) 
		{
			return $url;
		}
		if( trim($url) == "" ) 
		{
			return $url;
		}
		$filename = random(32) . $ext;
		$save_dir = ROOT_PATH . '/public/attachment' . DS . "images/";
		if( !file_exists($save_dir) && !mkdir($save_dir, 511, true) ) 
		{
			return $url;
		}
		$img = ihttp_get($url);
		if( is_error($img) ) 
		{
			return "";
		}
		$img = $img["content"];
		if( strlen($img) != 0 ) 
		{
			file_put_contents($save_dir . $filename, $img);
			$imgdir = "images/" . date("Y") . "/" . date("m") . "/";
			// $saveurl = save_media($imgdir . $filename, true);
			return '/public/attachment/images/' . $filename;
		}
		return "";
	}

	public static function getRealURL($url) 
	{
		if( function_exists("stream_context_set_default") ) 
		{
			stream_context_set_default(array( "http" => array( "method" => "HEAD" ) ));
		}
		$header = get_headers($url, 1);
		if( strpos($header[0], "301") || strpos($header[0], "302") ) 
		{
			if( is_array($header["Location"]) ) 
			{
				return $header["Location"][count($header["Location"]) - 1];
			}
			return $header["Location"];
		}
		return $url;
	}

	public static function check_remote_file_exists($url) 
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		$result = curl_exec($curl);
		$found = false;
		if( $result !== false ) 
		{
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if( $statusCode == 200 ) 
			{
				$found = true;
			}
		}
		curl_close($curl);
		return $found;
	}

	public static function my_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC) 
	{
		if( is_array($arrays) ) 
		{
			foreach( $arrays as $array ) 
			{
				if( is_array($array) ) 
				{
					$key_arrays[] = $array[$sort_key];
				}
				else 
				{
					return false;
				}
			}
			array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
			return $arrays;
		}
		else 
		{
			return false;
		}
	}

	public static function get_pageno_url($url = "", $pageNo = 1) 
	{
		$url .= "/search.htm?pageNo=" . $pageNo;
		return $url;
	}

	public static function get_total_page($url = "", $taobao = false) 
	{
		if( empty($url) ) 
		{
			return array( "totalpage" => 0 );
		}
		$content = self::get_page_content($url);
		$str = "";
		if( $taobao ) 
		{
			$str = "/<span class=\"page-info\">(.*)<\\/span>/";
		}
		else 
		{
			$str = "/<b class=\"ui-page-s-len\">(.*)<\\/b>/";
		}
		preg_match($str, $content, $p);
		if( is_array($p) ) 
		{
			$pages = explode("/", $p[1]);
			return array( "totalpage" => $pages[1] );
		}
		return array( "totalpage" => 0 );
	}

	public static function httpGet($url) 
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 500);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_URL, $url);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

	public static function get_page_content($url = "", $pageNo = 1) 
	{
		if( empty($url) ) 
		{
			return array( "totalpage" => 0 );
		}
		$url = self::get_pageno_url($url, $pageNo);
		$response = ihttp_get($url);
		if( !isset($response["content"]) ) 
		{
			return array( "result" => 0 );
		}
		return $response["content"];
	}

	public static function get_pag_items($pageContent = "") 
	{
		$str = "/data-id=\"(.*)\"/U";
		preg_match_all($str, $pageContent, $items);
		if( isset($items[1]) ) 
		{
			return $items[1];
		}
		return array( );
	}

	public static function contentpasswh($content) 
	{
		$content = preg_replace("/(?:width)=('|\").*?\\1/", " width=\"100%\"", $content);
		$content = preg_replace("/(?:height)=('|\").*?\\1/", " ", $content);
		$content = preg_replace("/(?:max-width:\\s*\\d*\\.?\\d*(px|rem|em))/", "", $content);
		$content = preg_replace("/(?:max-height:\\s*\\d*\\.?\\d*(px|rem|em))/", "", $content);
		$content = preg_replace("/(?:min-width:\\s*\\d*\\.?\\d*(px|rem|em))/", " ", $content);
		$content = preg_replace("/(?:min-height:\\s*\\d*\\.?\\d*(px|rem|em))/", " ", $content);
		return $content;
	}

	public static function get_item_one688($itemid = "", $one688url = "", $cates, $merchid = 0) 
	{
		self::alibaba($itemid, $cates, $merchid);
	}

	public static function alibaba($itemid, $cates, $merchid = 0) 
	{
		global $_GPC;
		error_reporting(0);
		if( true ) 
		{
			$id = $itemid;
			$itemUrl = "http://m.1688.com/offer/" . $id . ".html";
			$html = file_get_contents($itemUrl);
			if( preg_match("/https:\\/\\/www\\.taobao\\.com\\/markets\\/bx\\/deny_pc/", $html, $message) ) 
			{
				show_json(0, "访问被拒绝,请检查代理或VPN或请求次数过多");
			}
			preg_match("/window\\.wingxViewData=window\\.wingxViewData\\|\\|{};window\\.wingxViewData\\[0\\]=(.+)<\\/script>/", $html, $res);
			$json1 = $res[1];
			$json1 = json_decode($json1, true);
			if( empty($json1["detailUrl"]) ) 
			{
				show_json(0, "商品获取失败");
			}
			$detailUrl = $json1["detailUrl"];

			$detail = ihttp_get($detailUrl);
			$detail = iconv("GBK", "UTF-8", $detail["content"]);
			preg_match("/var offer_details=(.+);\$/", $detail, $detailStr);
			$detail_temp = json_decode($detailStr[1], true);
			if( empty($detail_temp) ) 
			{
				preg_match("/var desc='(.+)';\$/", $detail, $detailStr);
				unset($detail);
				$detail["content"] = $detailStr[1];
			}
			else 
			{
				$detail = $detail_temp;
			}
			$thumb_url = array( );
			foreach( $json1["imageList"] as $k => $v ) 
			{
				$thumb_url[] = self::save_image($v["originalImageURI"], 1);
			}
			$thumb = $thumb_url[0];
			unset($thumb_url[0]);
			$priceRange = explode("-", $json1["priceDisplay"]);
			$minprice = floatval($priceRange[0]);
			$maxprice = (empty($priceRange[1]) ? floatval($priceRange[0]) : floatval($priceRange[1]));
			$hasoption = (empty($json1["skuProps"]) ? 0 : 1);
			$param = $json1["productFeatureList"];
			$detail["content"] = self::contentpasswh($detail["content"]);
			preg_match_all("/<img.*?src=[\\\\'| \\\"](.*?(?:[\\.gif|\\.jpg]?))[\\\\'|\\\"].*?[\\/]?>/", $detail["content"], $imgs);
			if( isset($imgs[1]) ) 
			{
				foreach( $imgs[1] as $img ) 
				{
					$catchimg = $img;
					if( substr($catchimg, 0, 2) == "//" ) 
					{
						$img = "http://" . substr($img, 2);
					}
					$im = array( "catchimg" => $catchimg, "system" => self::save_image($img, true) );
					$images[] = $im;
				}
			}
			if( isset($images) ) 
			{
				foreach( $images as $img ) 
				{
					if( !empty($img["system"]) ) 
					{
					}
					if( !empty($img["system"]) ) 
					{
						$detail["content"] = str_replace($img["catchimg"], $img["system"], $detail["content"]);
					}
				}
			}
			$detail["content"] = model("common")->html_to_images($detail["content"]);
			$data = array( "thumb" => $thumb, "thumb_url" => serialize($thumb_url), "title" => $json1["subject"], "status" => 0, "marketprice" => $maxprice, "originalprice" => $minprice, "minprice" => $minprice, "maxprice" => $maxprice, "hasoption" => $hasoption, "createtime" => time(), "total" => $json1["canBookedAmount"], "content" => $detail["content"], "merchid" => $merchid, "cates" => $cates, "checked" => (empty($merchid) ? 0 : 1), "newgoods" => 1 );
			if( !empty($merchid) ) 
			{
				if( empty($_W["merch_user"]["goodschecked"]) ) 
				{
					$data["checked"] = 1;
				}
				else 
				{
					$data["checked"] = 0;
				}
			}
			$pcates = array( );
			$ccates = array( );
			$tcates = array( );
			$pcateid = 0;
			$ccateid = 0;
			$tcateid = 0;
			if( is_array($cates) ) 
			{
				foreach( $cates as $key => $cid ) 
				{
					$c = Db::name('shop_goods_category')->where('id',$cid)->field('level')->find();
					if( $c["level"] == 1 ) 
					{
						$pcates[] = $cid;
					}
					else 
					{
						if( $c["level"] == 2 ) 
						{
							$ccates[] = $cid;
						}
						else 
						{
							if( $c["level"] == 3 ) 
							{
								$tcates[] = $cid;
							}
						}
					}
					if( $key == 0 ) 
					{
						if( $c["level"] == 1 ) 
						{
							$pcateid = $cid;
						}
						else 
						{
							if( $c["level"] == 2 ) 
							{
								$crow = Db::name('shop_goods_category')->where('id=' . $cid)->field('parentid')->find();
								$pcateid = $crow["parentid"];
								$ccateid = $cid;
							}
							else 
							{
								if( $c["level"] == 3 ) 
								{
									$tcateid = $cid;
									$tcate = Db::name('shop_goods_category')->where('id',$cid)->field('id,parentid')->find();
									$ccateid = $tcate["parentid"];
									$ccate = Db::name('shop_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
									$pcateid = $ccate["parentid"];
								}
							}
						}
					}
				}
			}
			$data["pcate"] = $pcateid;
			$data["ccate"] = $ccateid;
			$data["tcate"] = $tcateid;
			if( !empty($cates) ) 
			{
				$data["cates"] = implode(",", $cates);
			}
			$data["pcates"] = implode(",", $pcates);
			$data["ccates"] = implode(",", $ccates);
			$data["tcates"] = implode(",", $tcates);
			$goodsid = Db::name('shop_goods')->insertGetId($data);
			if( empty($goodsid) ) 
			{
				show_json(0, "抓取失败");
			}
			$param = self::paramFormat($param, $goodsid);
			if( $hasoption ) 
			{
				foreach( $json1["skuProps"] as $k => $v ) 
				{
					$spec = $v["prop"];
					$specId = Db::name('shop_goods_spec')->insertGetId(array( "goodsid" => $goodsid, "title" => $spec ));
					foreach( $v["value"] as $key => $val ) 
					{
						$thumb = $val["imageUrl"];
						$title = $val["name"];
						$specs = Db::name('shop_goods_spec_item')->insertGetId(array( "specid" => $specId, "title" => $val["name"], "thumb" => (empty($thumb) ? "" : $thumb), "show" => 1 ));
						$specsid[$k][$key][$title] = $specs;
					}
				}
				$map = $json1["skuMap"];
				foreach( $map as $k => $v ) 
				{
					$specArr = explode("&gt;", $k);
					foreach( $specsid as $key => $item ) 
					{
						foreach( $item as $v1 ) 
						{
							if( !empty($v1[$specArr[$key]]) ) 
							{
								$sss[] = $v1[$specArr[$key]];
							}
						}
					}
					$option["specs"] = implode("_", $sss);
					unset($sss);
					$option["title"] = str_replace("&gt;", "+", $k);
					if( !empty($v["price"]) ) 
					{
						$option["marketprice"] = $v["price"];
					}
					else 
					{
						if( !empty($v["discountPrice"]) ) 
						{
							$option["marketprice"] = $v["discountPrice"];
						}
						else 
						{
							if( !empty($json1["discountPriceRanges"]) ) 
							{
								$option["marketprice"] = $json1["discountPriceRanges"][0]["price"];
							}
							else 
							{
								$option["marketprice"] = 0;
							}
						}
					}
					$option["stock"] = $v["canBookCount"];
					$option["goodsid"] = $goodsid;
					if( !empty($v["price"]) ) 
					{
						$option["productprice"] = $v["price"];
					}
					else 
					{
						if( !empty($v["discountPrice"]) ) 
						{
							$option["productprice"] = $v["discountPrice"];
						}
						else 
						{
							if( !empty($json1["discountPriceRanges"]) ) 
							{
								$option["productprice"] = $json1["discountPriceRanges"][0]["price"];
							}
							else 
							{
								$option["productprice"] = 0;
							}
						}
					}
					Db::name('shop_goods_option')->insert($option);
				}
			}
			show_json(1, "抓取成功");
		}
	}

	private function paramFormat($param, $id) 
	{
		$value = array( );
		foreach( $param as $k => $v ) 
		{
			if( !empty($v["name"]) && !empty($v["value"]) ) 
			{
				$unit = (empty($v["unit"]) ? "" : $v["unit"]);
				$value[$v["name"]] = $v["value"] . $unit;
			}
		}
		foreach( $value as $key => $val ) 
		{
			Db::name('shop_goods_param')->insert(array( "goodsid" => $id, "title" => $key, "value" => $val ));
		}
	}

	public static function changeBMPtoJPG($srcPathName) 
	{
		$srcFile = $srcPathName;
		$dstFile = str_replace(".bmp", ".jpg", $srcPathName);
		$photoSize = GetImageSize($srcFile);
		list($pw, $ph) = $photoSize;
		$dstImage = ImageCreateTrueColor($pw, $ph);
		$white = imagecolorallocate($dstImage, 255, 255, 255);
		imagefill($dstImage, 0, 0, $white);
		$srcImage = self::ImageCreateFromBMP_private($srcFile);
		imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $pw, $ph, $pw, $ph);
		$judge = imagejpeg($dstImage, $dstFile, 90);
		imagedestroy($dstImage);
		if( $judge ) 
		{
			return $dstFile;
		}
		return false;
	}
	public static function ImageCreateFromBMP_private($filename) 
	{
		if( !($f1 = fopen($filename, "rb")) ) 
		{
			return false;
		}
		$FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
		if( $FILE["file_type"] != 19778 ) 
		{
			return false;
		}
		$BMP = unpack("Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel" . "/Vcompression/Vsize_bitmap/Vhoriz_resolution" . "/Vvert_resolution/Vcolors_used/Vcolors_important", fread($f1, 40));
		$BMP["colors"] = pow(2, $BMP["bits_per_pixel"]);
		if( $BMP["size_bitmap"] == 0 ) 
		{
			$BMP["size_bitmap"] = $FILE["file_size"] - $FILE["bitmap_offset"];
		}
		$BMP["bytes_per_pixel"] = $BMP["bits_per_pixel"] / 8;
		$BMP["bytes_per_pixel2"] = ceil($BMP["bytes_per_pixel"]);
		$BMP["decal"] = ($BMP["width"] * $BMP["bytes_per_pixel"]) / 4;
		$BMP["decal"] -= floor(($BMP["width"] * $BMP["bytes_per_pixel"]) / 4);
		$BMP["decal"] = 4 - 4 * $BMP["decal"];
		if( $BMP["decal"] == 4 ) 
		{
			$BMP["decal"] = 0;
		}
		$PALETTE = array( );
		if( $BMP["colors"] < 16777216 ) 
		{
			$PALETTE = unpack("V" . $BMP["colors"], fread($f1, $BMP["colors"] * 4));
		}
		$IMG = fread($f1, $BMP["size_bitmap"]);
		$VIDE = chr(0);
		$res = imagecreatetruecolor($BMP["width"], $BMP["height"]);
		$P = 0;
		$Y = $BMP["height"] - 1;
		while( 0 <= $Y ) 
		{
			$X = 0;
			while( $X < $BMP["width"] ) 
			{
				switch( $BMP["bits_per_pixel"] ) 
				{
					case 32: $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
					break;
					case 24: $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
					break;
					case 16: $COLOR = unpack("n", substr($IMG, $P, 2));
					$COLOR[1] = $PALETTE[$COLOR[1] + 1];
					break;
					case 8: $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
					$COLOR[1] = $PALETTE[$COLOR[1] + 1];
					break;
					case 4: $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
					if( ($P * 2) % 2 == 0 ) 
					{
						$COLOR[1] = $COLOR[1] >> 4;
					}
					else 
					{
						$COLOR[1] = $COLOR[1] & 15;
					}
					$COLOR[1] = $PALETTE[$COLOR[1] + 1];
					break;
					case 1: $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
					if( ($P * 8) % 8 == 0 ) 
					{
						$COLOR[1] = $COLOR[1] >> 7;
					}
					else 
					{
						if( ($P * 8) % 8 == 1 ) 
						{
							$COLOR[1] = ($COLOR[1] & 64) >> 6;
						}
						else 
						{
							if( ($P * 8) % 8 == 2 ) 
							{
								$COLOR[1] = ($COLOR[1] & 32) >> 5;
							}
							else 
							{
								if( ($P * 8) % 8 == 3 ) 
								{
									$COLOR[1] = ($COLOR[1] & 16) >> 4;
								}
								else 
								{
									if( ($P * 8) % 8 == 4 ) 
									{
										$COLOR[1] = ($COLOR[1] & 8) >> 3;
									}
									else 
									{
										if( ($P * 8) % 8 == 5 ) 
										{
											$COLOR[1] = ($COLOR[1] & 4) >> 2;
										}
										else 
										{
											if( ($P * 8) % 8 == 6 ) 
											{
												$COLOR[1] = ($COLOR[1] & 2) >> 1;
											}
											else 
											{
												if( ($P * 8) % 8 == 7 ) 
												{
													$COLOR[1] = $COLOR[1] & 1;
												}
											}
										}
									}
								}
							}
						}
					}
					$COLOR[1] = $PALETTE[$COLOR[1] + 1];
					break;
					default: return false;
				}
				imagesetpixel($res, $X, $Y, $COLOR[1]);
				$X++;
				$P += $BMP["bytes_per_pixel"];
			}
			$Y--;
			$P += $BMP["decal"];
		}
		fclose($f1);
		return $res;
	}
}