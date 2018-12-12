<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Goods extends Base
{
	public function index()
	{
		header('location: ' . url('merch/goods/sale'));exit;
	}

	public function main($goodsfrom = "") 
	{
		$merch = $this->merch;
		$psize = 20;
		$sqlcondition = $groupcondition = "";
		$condition = " g.`merchid`=" . $merch['id'];
		$not_add = 0;
		$maxgoods = intval($merch["maxgoods"]);
		if( 0 < $maxgoods ) 
		{
			$goodstotal = Db::name('shop_goods')->where('merchid = ' . $merch['id'])->count();
			if( $maxgoods <= $goodstotal ) 
			{
				$not_add = 1;
			}
		}
		$querysql = Db::name('shop_goods')->alias('g');
		if( !empty($_GET["keyword"]) ) 
		{
			$keyword = trim($_GET["keyword"]);
			$querysql = $querysql->join('shop_goods_option op','g.id = op.goodsid','left');
			$querysql = $querysql->group('g.`id`');
			$condition .= " AND (g.`id` = '%" . $keyword . "%' or g.`title` LIKE '%" . $keyword . "%' or g.`goodssn` LIKE '%" . $keyword . "%' or g.`productsn` LIKE '%" . $keyword . "%' or op.`title` LIKE '%" . $keyword . "%' or op.`goodssn` LIKE '%" . $keyword . "%' or op.`productsn` LIKE '%" . $keyword . "%')";
		}
		if( !empty($_GET["cate"]) ) 
		{
			$cate = intval($_GET["cate"]);
			$condition .= " AND FIND_IN_SET(" . $cate . ",cates)<>0 ";
		}
		if( empty($goodsfrom) ) 
		{
			$goodsfrom = $_GET["goodsfrom"];
		}
		if( empty($goodsfrom) ) 
		{
			$goodsfrom = "sale";
		}
		if( $goodsfrom == "sale" ) 
		{
			$condition .= " AND g.`status` = 1  and g.`total`>0 and g.`deleted`=0  AND g.`checked`=0";
			$status = 1;
		}
		else 
		{
			if( $goodsfrom == "out" ) 
			{
				$condition .= " AND g.`total` <= 0 AND g.`status` <> 0 and g.`deleted`=0  AND g.`checked`=0";
				$status = 1;
			}
			else 
			{
				if( $goodsfrom == "stock" ) 
				{
					$status = 0;
					$condition .= " AND g.`status` = 0 and g.`deleted`=0 AND g.`checked`=0";
				}
				else 
				{
					if( $goodsfrom == "cycle" ) 
					{
						$status = 0;
						$condition .= " AND g.`deleted`=1";
					}
					else 
					{
						if( $goodsfrom == "check" ) 
						{
							$status = 0;
							$condition .= " AND g.`checked`=1 and g.`deleted`=0";
						}
					}
				}
			}
		}

		$list = $querysql->where($condition)->field('g.*')->order('g.status DESC, g.displayorder DESC,g.id DESC')->paginate($psize);
		foreach( $list as $key => $value ) 
		{
			$value["allcates"] = explode(",", $value["cates"]);
			$value["allcates"] = array_unique($value["allcates"]);
			$sale_cpcount = Db::query("SELECT sum(og.total)  as sale_count FROM " . tablename("shop_order_goods") . " og LEFT JOIN " . tablename("shop_order") . " o on og.orderid=o.id  WHERE og.goodsid=" . $value["id"] . " and o.`status`>=1 and o.refundid = 0 ");
			$value["sale_cpcount"] = $sale_cpcount["sale_count"];
			$data = array();
    		$data = $value;
    		$list->offsetSet($key,$data);
		}
		unset($value);
		$pager = $list->render();
		$categorys = model("merch")->getFullCategory($merch['id'],true,true);
		$category = array( );
		foreach( $categorys as $cate ) 
		{
			$category[$cate["id"]] = $cate;
		}
		$this->assign(['list'=>$list,'pager'=>$pager,'category'=>$category,'keyword'=>$keyword,'cate'=>$cate,'status'=>$status,'goodsfrom'=>$goodsfrom]);
		return $this->fetch('goods/index');
	}

	public function sale() 
	{
		$data = $this->main("sale");
		return $data;
	}

	public function out() 
	{
		$data = $this->main("out");
		return $data;
	}

	public function stock() 
	{
		$data = $this->main("stock");
		return $data;
	}

	public function cycle() 
	{
		$data = $this->main("cycle");
		return $data;
	}

	public function verify() 
	{
		$data = $this->main("verify");
		return $data;
	}

	public function check() 
	{
		$data = $this->main("check");
		return $data;
	}

	public function add() 
	{
		$data = $this->post();
		return $data;
	}

	public function edit() 
	{
		$data = $this->post();
		return $data;
	}

	protected function post()
	{
		$merch_user = $this->merch;
		$id = intval(input('id'));
		$not_add = 0;
		$maxgoods = intval($merch_user["maxgoods"]);
		if( 0 < $maxgoods ) 
		{
			$goodstotal = Db::name('shop_goods')->where('merchid = ' . $merch_user['id'])->count();
			if( $maxgoods <= $goodstotal ) 
			{
				$not_add = 1;
			}
			if( $id == 0 && $not_add == 1 ) 
			{
				$this->error("抱歉，您的商品数量已经达到最高设置,不能添加商品！");
			}
		}
		$item = Db::name('shop_goods')->where('id = ' . $id . ' and merchid = ' . $merch_user['id'])->find();
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		$category = model("merch")->getFullCategory(true, true);
		$levels = array( array( "id" => 0, "key" => "merch", "levelname" => "促销价" ) );
		if( Request::instance()->isPost() ) 
		{
			if( empty($id)) {
				$goodstype = intval($_POST["type"]);
			} else {
				$goodstype = intval($_POST["goodstype"]);
			}
			$data = array( "merchid" => intval($merch_user['id']), "merchdisplayorder" => intval($_POST["displayorder"]), "title" => trim($_POST["goodsname"]), "subtitle" => trim($_POST["subtitle"]), "thumb_first" => intval($_POST["thumb_first"]), "keywords" => trim($_POST["keywords"]), "type" => $goodstype, "isrecommand" => intval($_POST["isrecommand"]), "ishot" => intval($_POST["ishot"]), "isnew" => intval($_POST["isnew"]), "isnodiscount" => 1, "isdiscount" => intval($_POST["isdiscount"]), "isdiscount_title" => trim(mb_substr($_POST["isdiscount_title"], 0, 5, "UTF-8")), "isdiscount_time" => strtotime($_POST["isdiscount_time"]), "issendfree" => intval($_POST["issendfree"]), "istime" => intval($_POST["istime"]), "timestart" => strtotime($_POST["saletime"]["start"]), "timeend" => strtotime($_POST["saletime"]["end"]), "description" => trim($_POST["description"]), "goodssn" => trim($_POST["goodssn"]), "unit" => trim($_POST["unit"]), "createtime" => TIMESTAMP, "total" => intval($_POST["total"]), "showtotal" => intval($_POST["showtotal"]), "totalcnf" => intval($_POST["totalcnf"]), "marketprice" => $_POST["marketprice"], "weight" => $_POST["weight"], "productprice" => trim($_POST["productprice"]), "productsn" => trim($_POST["productsn"]), "maxbuy" => intval($_POST["maxbuy"]), "minbuy" => intval($_POST["minbuy"]), "usermaxbuy" => intval($_POST["usermaxbuy"]), "hasoption" => intval($_POST["hasoption"]), "sales" => intval($_POST["sales"]), "share_icon" => trim($_POST["share_icon"]), "share_title" => trim($_POST["share_title"]), "status" => intval($_POST["status"]), "virtualsend" => intval($_POST["virtualsend"]), "virtualsendcontent" => trim($_POST["virtualsendcontent"]), "isverify" => intval($_POST["isverify"]), "verifytype" => intval($_POST["verifytype"]), "storeids" => (is_array($_POST["storeids"]) ? implode(",", $_POST["storeids"]) : ""), "noticemid" => (is_array($_POST["noticemid"]) ? implode(",", $_POST["noticemid"]) : ""), "noticetype" => (is_array($_POST["noticetype"]) ? implode(",", $_POST["noticetype"]) : ""), "needfollow" => intval($_POST["needfollow"]), "followurl" => trim($_POST["followurl"]), "followtip" => trim($_POST["followtip"]), "virtual" => ($goodstype == 3 ? intval($_POST["virtual"]) : 0), "ednum" => intval($_POST["ednum"]), "edareas" => trim($_POST["edareas"]), "edareas_code" => trim($_POST["edareas_code"]), "edmoney" => trim($_POST["edmoney"]), "invoice" => intval($_POST["invoice"]), "repair" => intval($_POST["repair"]), "seven" => intval($_POST["seven"]), "province" => trim($_POST["province"]), "city" => trim($_POST["city"]), "quality" => intval($_POST["quality"]), "cashier" => intval($_POST["cashier"]), "video" => trim($_POST["video"]), "buyshow" => intval($_POST["buyshow"]) );
			// 启动事务
			Db::startTrans();
			try {			    
				if( intval($_POST["isverify"]) == 2 || $goodstype == 2 || $goodstype == 3 ) 
				{
					$data["cash"] = 0;
				}
				else 
				{
					$data["cash"] = intval($_POST["cash"]);
				}
				if( empty($item) ) 
				{
					$data["deduct2"] = -1;
					$data["merchsale"] = 1;
				}
				$cateset = model("common")->getSysset("shop");
				$pcates = array( );
				$ccates = array( );
				$tcates = array( );
				$fcates = array( );
				$cates = array( );
				$pcateid = 0;
				$ccateid = 0;
				$tcateid = 0;
				if( is_array($_POST["cates"]) ) 
				{
					$cates = $_POST["cates"];
					foreach( $cates as $key => $cid ) 
					{
						$c = Db::name('shop_merch_goods_category')->where('id',$cid)->field('level')->find();
						if( $c["level"] == 1 ) 
						{
							$pcates[] = $cid;
						} else {
							if( $c["level"] == 2 ) {
								$ccates[] = $cid;
							} else {
								if( $c["level"] == 3 ) {
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
									$crow = Db::name('shop_merch_goods_category')->where('id',$cid)->field('parentid')->find();
									$pcateid = $crow["parentid"];
									$ccateid = $cid;
								}
								else 
								{
									if( $c["level"] == 3 ) 
									{
										$tcateid = $cid;
										$tcate = Db::name('shop_merch_goods_category')->where('id',$cid)->field('id,parentid')->find();
										$ccateid = $tcate["parentid"];
										$ccate = Db::name('shop_merch_goods_category')->where('id',$ccateid)->field('id,parentid')->find();
										$pcateid = $ccate["parentid"];
									}
								}
							}
						}
					}
				}
				$data["merchpcate"] = $pcateid;
				$data["merchccate"] = $ccateid;
				$data["merchtcate"] = $tcateid;
				$data["merchcates"] = implode(",", $cates);
				$data["merchpcates"] = implode(",", $pcates);
				$data["merchccates"] = implode(",", $ccates);
				$data["merchtcates"] = implode(",", $tcates);
				$data["content"] = model("common")->html_images($_POST["content"]);
				$data["buycontent"] = model("common")->html_images($_POST["buycontent"]);
				
				$data["dispatchtype"] = intval($_POST["dispatchtype"]);
				$data["dispatchprice"] = trim($_POST["dispatchprice"]);
				$data["dispatchid"] = intval($_POST["dispatchid"]);
				if( $data["total"] === -1 ) 
				{
					$data["total"] = 0;
					$data["totalcnf"] = 2;
				}
				if( is_array($_POST["thumbs"]) ) 
				{
					$thumbs = $_POST["thumbs"];
					$thumb_url = array( );
					foreach( $thumbs as $th ) 
					{
						$thumb_url[] = trim($th);
					}
					$data["thumb"] = trim($thumb_url[0]);
					unset($thumb_url[0]);
					$data["thumb_url"] = serialize($thumb_url);
				}
				$needcheck = false;
				$keys = array( "title", "subtitle", "video", "thumb_url", "content", "followtip", "share_title", "description" );
				foreach( $data as $key => $value ) 
				{
					if( in_array($key, $keys) && isset($item[$key]) && $data[$key] != $item[$key] ) 
					{
						$needcheck = true;
					}
				}
				if( empty($merch_user["goodschecked"]) ) 
				{
					if( $needcheck ) 
					{
						$data["checked"] = 1;
					}
				}
				else 
				{
					$data["checked"] = 0;
				}
				if( empty($id) ) 
				{
					if( empty($merch_user["goodschecked"]) ) 
					{
						$data["checked"] = 1;
					}
					$id = Db::name('shop_goods')->insertGetId($data);
					model('shop')->mplog($merch_user['id'],"goods.add", "添加商品 ID: " . $id . "<br>" . ((!empty($data["nocommission"]) ? "是否参与分销 -- 否" : "是否参与分销 -- 是")));
				}
				else 
				{
					unset($data["createtime"]);
					Db::name('shop_goods')->where('id = ' . $id)->update($data);
					model('shop')->mplog($merch_user['id'],"goods.edit", "编辑商品 ID: " . $id . "<br>" . ((!empty($data["nocommission"]) ? "是否参与分销 -- 否" : "是否参与分销 -- 是")));
				}
				$param_ids = $_POST["param_id"];
				$param_titles = $_POST["param_title"];
				$param_values = $_POST["param_value"];
				$param_displayorders = $_POST["param_displayorder"];
				$len = count($param_ids);
				$paramids = array( );
				for( $k = 0; $k < $len; $k++ ) 
				{
					$param_id = "";
					$get_param_id = $param_ids[$k];
					$a = array( "title" => $param_titles[$k], "value" => $param_values[$k], "displayorder" => $k, "goodsid" => $id );
					if( !is_numeric($get_param_id) ) 
					{
						$param_id = Db::name('shop_goods_param')->insertGetId($a);
					}
					else 
					{
						Db::name('shop_goods_param')->where('id',$get_param_id)->update($a);
						$param_id = $get_param_id;
					}
					$paramids[] = $param_id;
				}
				if( 0 < count($paramids) ) 
				{
					Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $id . " and id not in ( " . implode(",", $paramids) . ")");
				}
				else 
				{
					Db::query("delete from " . tablename("shop_goods_param") . " where goodsid=" . $id);
				}
				$totalstocks = 0;
				$files = $_FILES;
				$spec_ids = $_POST["spec_id"];
				$spec_titles = $_POST["spec_title"];
				$specids = array( );
				$len = count($spec_ids);
				$specids = array( );
				$spec_items = array( );
				for( $k = 0; $k < $len; $k++ ) 
				{
					$spec_id = "";
					$get_spec_id = $spec_ids[$k];
					$a = array( "goodsid" => $id, "displayorder" => $k, "title" => $spec_titles[$get_spec_id] );
					if( is_numeric($get_spec_id) ) 
					{
						Db::name('shop_goods_spec')->where('id',$get_spec_id)->update($a);
						$spec_id = $get_spec_id;
					}
					else 
					{
						$spec_id = Db::name('shop_goods_spec')->insertGetId($a);
					}
					$spec_item_ids = $_POST["spec_item_id_" . $get_spec_id];
					$spec_item_titles = $_POST["spec_item_title_" . $get_spec_id];
					$spec_item_shows = $_POST["spec_item_show_" . $get_spec_id];
					$spec_item_thumbs = $_POST["spec_item_thumb_" . $get_spec_id];
					$spec_item_oldthumbs = $_POST["spec_item_oldthumb_" . $get_spec_id];
					$spec_item_virtuals = $_POST["spec_item_virtual_" . $get_spec_id];
					$itemlen = count($spec_item_ids);
					$itemids = array( );
					for( $n = 0; $n < $itemlen; $n++ ) 
					{
						$item_id = "";
						$get_item_id = $spec_item_ids[$n];
						$d = array("specid" => $spec_id, "displayorder" => $n, "title" => $spec_item_titles[$n], "show" => $spec_item_shows[$n], "thumb" => trim($spec_item_thumbs[$n]), "virtual" => ($data["type"] == 3 ? $spec_item_virtuals[$n] : 0) );
						$f = "spec_item_thumb_" . $get_item_id;
						if( is_numeric($get_item_id) ) 
						{
							Db::name('shop_goods_spec_item')->where('id',$get_item_id)->update($d);
							$item_id = $get_item_id;
						} else {
							$item_id = Db::name('shop_goods_spec_item')->insertGetId($d);
						}
						$itemids[] = $item_id;
						$d["get_id"] = $get_item_id;
						$d["id"] = $item_id;
						$spec_items[] = $d;
					}
					if( 0 < count($itemids) ) 
					{
						Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid=" . $spec_id . " and id not in (" . implode(",", $itemids) . ")");
					}
					else 
					{
						Db::query("delete from " . tablename("shop_goods_spec_item") . " where specid=" . $spec_id);
					}
					Db::name('shop_goods_spec')->where('id',$spec_id)->update(array( "content" => serialize($itemids) ));
					$specids[] = $spec_id;
				}
				if( 0 < count($specids) ) 
				{
					Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=" . $id . " and id not in (" . implode(",", $specids) . ")");
				}
				else 
				{
					Db::query("delete from " . tablename("shop_goods_spec") . " where goodsid=" . $id);
				}
				$optionArray = json_decode($_POST["optionArray"], true);
				$isdiscountDiscountsArray = json_decode($_POST["isdiscountDiscountsArray"], true);
				$option_idss = $optionArray["option_ids"];
				$len = count($option_idss);
				$optionids = array( );
				$levelArray = array( );
				$isDiscountsArray = array( );
				for( $k = 0; $k < $len; $k++ ) 
				{
					$option_id = "";
					$ids = $option_idss[$k];
					$get_option_id = $optionArray["option_id"][$k];
					$idsarr = explode("_", $ids);
					$newids = array( );
					foreach( $idsarr as $key => $ida ) 
					{
						foreach( $spec_items as $it ) 
						{
							if( $it["get_id"] == $ida ) 
							{
								$newids[] = $it["id"];
								break;
							}
						}
					}
					$newids = implode("_", $newids);
					$a = array( "title" => $optionArray["option_title"][$k], "productprice" => $optionArray["option_productprice"][$k], "costprice" => $optionArray["option_costprice"][$k], "marketprice" => $optionArray["option_marketprice"][$k], "stock" => $optionArray["option_stock"][$k], "weight" => $optionArray["option_weight"][$k], "goodssn" => $optionArray["option_goodssn"][$k], "productsn" => $optionArray["option_productsn"][$k], "goodsid" => $id, "specs" => $newids, "virtual" => ($data["type"] == 3 ? $optionArray["option_virtual"][$k] : 0) );
					$totalstocks += $a["stock"];
					if( empty($get_option_id) ) 
					{
						$option_id = Db::name('shop_goods_option')->insertGetId($a);
					}
					else 
					{
						Db::name('shop_goods_option')->where('id',$get_option_id)->update($a);
						$option_id = $get_option_id;
					}
					$optionids[] = $option_id;
					foreach( $levels as $level ) 
					{
						$isDiscountsArray[$level["key"]]["option" . $option_id] = $isdiscountDiscountsArray["isdiscount_discounts_" . $level["key"]][$k];
					}
				}
				$has_default = 0;
				$old_isdiscount_discounts = json_decode($item["isdiscount_discounts"], true);
				if( !empty($old_isdiscount_discounts["default"]) ) 
				{
					$has_default = 1;
				}
				if( !empty($isDiscountsArray) && $data["hasoption"] ) 
				{
					$is_discounts_arr = array_merge(array( "type" => 1 ), $isDiscountsArray);
					if( $has_default == 1 && !empty($old_isdiscount_discounts) ) 
					{
						foreach( $old_isdiscount_discounts as $k => $v ) 
						{
							if( $k != "type" && $k != "merch" ) 
							{
								$is_discounts_arr[$k] = $v;
							}
						}
					}
					$is_discounts_json = json_encode($is_discounts_arr);
				}
				else 
				{
					foreach( $levels as $level ) 
					{
						if( $level["key"] == "merch" ) 
						{
							$isDiscountsDefaultArray[$level["key"]]["option0"] = $_POST["isdiscount_discounts_level_" . $level["key"] . "_default"];
						}
					}
					$is_discounts_arr = array_merge(array( "type" => 0 ), $isDiscountsDefaultArray);
					if( $has_default == 1 && !empty($old_isdiscount_discounts) ) 
					{
						foreach( $old_isdiscount_discounts as $k => $v ) 
						{
							if( $k != "type" && $k != "merch" ) 
							{
								$is_discounts_arr[$k] = $v;
							}
						}
					}
					$is_discounts_json = (is_array($is_discounts_arr) ? json_encode($is_discounts_arr) : json_encode(array( )));
				}
				Db::name('shop_goods')->where('id',$id)->update(array( "isdiscount_discounts" => $is_discounts_json ));
				if( 0 < count($optionids) && $data["hasoption"] !== 0 ) 
				{
					Db::query("delete from " . tablename("shop_goods_option") . " where goodsid=" . $id . " and id not in ( " . implode(",", $optionids) . ")");
					$sql = "update " . tablename("shop_goods") . " g set\r\n            g.minprice = (select min(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $id . "),\r\n            g.maxprice = (select max(marketprice) from " . tablename("shop_goods_option") . " where goodsid = " . $id . ")\r\n            where g.id = " . $id . " and g.hasoption=1";
					Db::query($sql);
				}
				else 
				{
					Db::query("delete from " . tablename("shop_goods_option") . " where goodsid=" . $id);
					$sql = "update " . tablename("shop_goods") . " set minprice = marketprice,maxprice = marketprice where id = " . $id . " and hasoption=0;";
					Db::query($sql);
				}
				if( 0 < $totalstocks && $data["totalcnf"] != 2 ) 
				{
					Db::name('shop_goods')->where('id',$id)->update(array( "total" => $totalstocks ));
				}
				Db::commit();
			} catch (\Exception $e) {
			    // 回滚事务
			    show_json(0,'操作失败');
			    Db::rollback();
			}
			show_json(1, array( "url" => url("merch/goods/edit", array( "id" => $id, "tab" => str_replace("#tab_", "", $_POST["tab"]) )) ));
		}
		if( !empty($id) ) 
		{
			if( empty($item) ) 
			{
				$this->error("抱歉，商品不存在或是已经删除！", "", "error");
			}
			$noticetype = explode(",", $item["noticetype"]);
			$cates = explode(",", $item["merchcates"]);
			$isdiscount_discounts = json_decode($item["isdiscount_discounts"], true);
			$allspecs = Db::name('shop_goods_spec')->where('goodsid = ' . $id)->order('displayorder asc"')->select();
			foreach( $allspecs as &$s ) 
			{
				$s["items"] = Db::query("select a.id,a.specid,a.title,a.thumb,a.show,a.displayorder,a.valueId,a.virtual,b.title as title2 from " . tablename("shop_goods_spec_item") . " a left join " . tablename("shop_virtual_type") . " b on b.id=a.virtual  where a.specid=" . $s["id"] . " order by a.displayorder asc");
			}
			unset($s);
			$params = Db::query("select * from " . tablename("shop_goods_param") . " where goodsid=" . $id . " order by displayorder asc");
			if( !empty($item["thumb"]) ) 
			{
				$piclist = array_merge(array( $item["thumb"] ), iunserializer($item["thumb_url"]));
			}
			$item["content"] = model("common")->html_to_images($item["content"]);
			$html = "";
			$discounts_html = "";
			$commission_html = "";
			$isdiscount_discounts_html = "";
			$options = Db::query("select * from " . tablename("shop_goods_option") . " where goodsid=" . $id . " order by id asc");
			$specs = array( );
			if( 0 < count($options) ) 
			{
				$specitemids = explode("_", $options[0]["specs"]);
				foreach( $specitemids as $itemid ) 
				{
					foreach( $allspecs as $ss ) 
					{
						$items = $ss["items"];
						foreach( $items as $it ) 
						{
							if( $it["id"] == $itemid ) 
							{
								$specs[] = $ss;
								break;
							}
						}
					}
				}
				$html = "";
				$html .= "<table class=\"table table-bordered table-condensed\">";
				$html .= "<thead>";
				$html .= "<tr class=\"active\">";
				$isdiscount_discounts_html .= "<table class=\"table table-bordered table-condensed\">";
				$isdiscount_discounts_html .= "<thead>";
				$isdiscount_discounts_html .= "<tr class=\"active\">";
				$len = count($specs);
				$newlen = 1;
				$h = array( );
				$rowspans = array( );
				for( $i = 0; $i < $len; $i++ ) 
				{
					$html .= "<th>" . $specs[$i]["title"] . "</th>";
					$isdiscount_discounts_html .= "<th>" . $specs[$i]["title"] . "</th>";
					$itemlen = count($specs[$i]["items"]);
					if( $itemlen <= 0 ) 
					{
						$itemlen = 1;
					}
					$newlen *= $itemlen;
					$h = array( );
					for( $j = 0; $j < $newlen; $j++ ) 
					{
						$h[$i][$j] = array( );
					}
					$l = count($specs[$i]["items"]);
					$rowspans[$i] = 1;
					for( $j = $i + 1; $j < $len; $j++ ) 
					{
						$rowspans[$i] *= count($specs[$j]["items"]);
					}
				}
				$canedit = true;
				if( $canedit ) 
				{
					foreach( $levels as $level ) 
					{
						$isdiscount_discounts_html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">" . $level["levelname"] . "</div><div class=\"input-group\"><input type=\"text\" class=\"form-control  input-sm isdiscount_discounts_" . $level["key"] . "_all\" VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('isdiscount_discounts_" . $level["key"] . "');\"></a></span></div></div></th>";
					}
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">库存</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_stock_all\"  VALUE=\"\"/><span class=\"input-group-addon\" ><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_stock');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">现价</div><div class=\"input-group\"><input type=\"text\" class=\"form-control  input-sm option_marketprice_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_marketprice');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">原价</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_productprice_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_productprice');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">成本价</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_costprice_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_costprice');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">编码</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_goodssn_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_goodssn');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">条码</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_productsn_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_productsn');\"></a></span></div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">重量（克）</div><div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_weight_all\"  VALUE=\"\"/><span class=\"input-group-addon\"><a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_weight');\"></a></span></div></div></th>";
				}
				else 
				{
					foreach( $levels as $level ) 
					{
						$isdiscount_discounts_html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">" . $level["levelname"] . "</div></div></th>";
					}
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">库存</div></div></th>";
					$html .= "<th\"><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">销售价格</div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">市场价格</div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">成本价格</div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">商品编码</div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">商品条码</div></div></th>";
					$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">重量（克）</div></th>";
				}
				$html .= "</tr></thead>";
				$isdiscount_discounts_html .= "</tr></thead>";
				for( $m = 0; $m < $len; $m++ ) 
				{
					$k = 0;
					$kid = 0;
					$n = 0;
					for( $j = 0; $j < $newlen; $j++ ) 
					{
						$rowspan = $rowspans[$m];
						if( $j % $rowspan == 0 ) 
						{
							$h[$m][$j] = array( "html" => "<td class='full' rowspan='" . $rowspan . "'>" . $specs[$m]["items"][$kid]["title"] . "</td>", "id" => $specs[$m]["items"][$kid]["id"] );
						}
						else 
						{
							$h[$m][$j] = array( "html" => "", "id" => $specs[$m]["items"][$kid]["id"] );
						}
						$n++;
						if( $n == $rowspan ) 
						{
							$kid++;
							if( count($specs[$m]["items"]) - 1 < $kid ) 
							{
								$kid = 0;
							}
							$n = 0;
						}
					}
				}
				$hh = "";
				$dd = "";
				$isdd = "";
				$cc = "";
				for( $i = 0; $i < $newlen; $i++ ) 
				{
					$hh .= "<tr>";
					$dd .= "<tr>";
					$isdd .= "<tr>";
					$cc .= "<tr>";
					$ids = array( );
					for( $j = 0; $j < $len; $j++ ) 
					{
						$hh .= $h[$j][$i]["html"];
						$dd .= $h[$j][$i]["html"];
						$isdd .= $h[$j][$i]["html"];
						$cc .= $h[$j][$i]["html"];
						$ids[] = $h[$j][$i]["id"];
					}
					$ids = implode("_", $ids);
					$val = array( "id" => "", "title" => "", "stock" => "", "costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "", "virtual" => "" );
					$isdiscounts_val = array( "id" => "", "title" => "", "level" => "", "costprice" => "", "productprice" => "", "marketprice" => "", "weight" => "", "virtual" => "" );
					foreach( $levels as $level ) 
					{
						$isdiscounts_val[$level["key"]] = "";
					}
					foreach( $options as $o ) 
					{
						if( $ids === $o["specs"] ) 
						{
							$val = array( "id" => $o["id"], "title" => $o["title"], "stock" => $o["stock"], "costprice" => $o["costprice"], "productprice" => $o["productprice"], "marketprice" => $o["marketprice"], "goodssn" => $o["goodssn"], "productsn" => $o["productsn"], "weight" => $o["weight"], "virtual" => $o["virtual"] );
							$discount_val = array( "id" => $o["id"] );
							foreach( $levels as $level ) 
							{
								$isdiscounts_val[$level["key"]] = (is_string($isdiscount_discounts[$level["key"]]) ? "" : $isdiscount_discounts[$level["key"]]["option" . $o["id"]]);
							}
							break;
						}
					}
					if( $canedit ) 
					{
						foreach( $levels as $level ) 
						{
							$isdd .= "<td>";
							if( $level["key"] == "merch" ) 
							{
								$isdd .= "<input data-name=\"isdiscount_discounts_level_" . $level["key"] . "_" . $ids . "\"  type=\"text\" class=\"form-control isdiscount_discounts_" . $level["key"] . " isdiscount_discounts_" . $level["key"] . "_" . $ids . "\" value=\"" . $isdiscounts_val[$level["key"]] . "\"/> ";
							}
							$isdd .= "</td>";
						}
						$isdd .= "<input data-name=\"isdiscount_discounts_id_" . $ids . "\"  type=\"hidden\" class=\"form-control isdiscount_discounts_id isdiscount_discounts_id_" . $ids . "\" value=\"" . $isdiscounts_val["id"] . "\"/>";
						$isdd .= "<input data-name=\"isdiscount_discounts_ids\"  type=\"hidden\" class=\"form-control isdiscount_discounts_ids isdiscount_discounts_ids_" . $ids . "\" value=\"" . $ids . "\"/>";
						$isdd .= "<input data-name=\"isdiscount_discounts_title_" . $ids . "\"  type=\"hidden\" class=\"form-control isdiscount_discounts_title isdiscount_discounts_title_" . $ids . "\" value=\"" . $isdiscounts_val["title"] . "\"/>";
						$isdd .= "<input data-name=\"isdiscount_discounts_virtual_" . $ids . "\"  type=\"hidden\" class=\"form-control isdiscount_discounts_title isdiscount_discounts_virtual_" . $ids . "\" value=\"" . $isdiscounts_val["virtual"] . "\"/>";
						$isdd .= "</tr>";
						$hh .= "<td>";
						$hh .= "<input data-name=\"option_stock_" . $ids . "\"  type=\"text\" class=\"form-control option_stock option_stock_" . $ids . "\" value=\"" . $val["stock"] . "\"/>";
						$hh .= "</td>";
						$hh .= "<input data-name=\"option_id_" . $ids . "\"  type=\"hidden\" class=\"form-control option_id option_id_" . $ids . "\" value=\"" . $val["id"] . "\"/>";
						$hh .= "<input data-name=\"option_ids\"  type=\"hidden\" class=\"form-control option_ids option_ids_" . $ids . "\" value=\"" . $ids . "\"/>";
						$hh .= "<input data-name=\"option_title_" . $ids . "\"  type=\"hidden\" class=\"form-control option_title option_title_" . $ids . "\" value=\"" . $val["title"] . "\"/>";
						$hh .= "<input data-name=\"option_virtual_" . $ids . "\"  type=\"hidden\" class=\"form-control option_virtual option_virtual_" . $ids . "\" value=\"" . $val["virtual"] . "\"/>";
						$hh .= "<td><input data-name=\"option_marketprice_" . $ids . "\" type=\"text\" class=\"form-control option_marketprice option_marketprice_" . $ids . "\" value=\"" . $val["marketprice"] . "\"/></td>";
						$hh .= "<td><input data-name=\"option_productprice_" . $ids . "\" type=\"text\" class=\"form-control option_productprice option_productprice_" . $ids . "\" \" value=\"" . $val["productprice"] . "\"/></td>";
						$hh .= "<td><input data-name=\"option_costprice_" . $ids . "\" type=\"text\" class=\"form-control option_costprice option_costprice_" . $ids . "\" \" value=\"" . $val["costprice"] . "\"/></td>";
						$hh .= "<td><input data-name=\"option_goodssn_" . $ids . "\" type=\"text\" class=\"form-control option_goodssn option_goodssn_" . $ids . "\" \" value=\"" . $val["goodssn"] . "\"/></td>";
						$hh .= "<td><input data-name=\"option_productsn_" . $ids . "\" type=\"text\" class=\"form-control option_productsn option_productsn_" . $ids . "\" \" value=\"" . $val["productsn"] . "\"/></td>";
						$hh .= "<td><input data-name=\"option_weight_" . $ids . "\" type=\"text\" class=\"form-control option_weight option_weight_" . $ids . "\" \" value=\"" . $val["weight"] . "\"/></td>";
						$hh .= "</tr>";
					}
					else 
					{
						$hh .= "<td>" . $val["stock"] . "</td>";
						$hh .= "<td>" . $val["marketprice"] . "</td>";
						$hh .= "<td>" . $val["productprice"] . "</td>";
						$hh .= "<td>" . $val["costprice"] . "</td>";
						$hh .= "<td>" . $val["goodssn"] . "</td>";
						$hh .= "<td>" . $val["productsn"] . "</td>";
						$hh .= "<td>" . $val["weight"] . "</td>";
						$hh .= "</tr>";
					}
				}
				$discounts_html .= $dd;
				$discounts_html .= "</table>";
				$isdiscount_discounts_html .= $isdd;
				$isdiscount_discounts_html .= "</table>";
				$html .= $hh;
				$html .= "</table>";
				$commission_html .= $cc;
				$commission_html .= "</table>";
			}
			$stores = array( );
			if( !empty($item["storeids"]) ) 
			{
				$stores = Db::query("select id,storename from " . tablename("shop_store") . " where id in (" . $item["storeids"] . " ) and merchid=" . $merch_user['id']);
			}
			if( !empty($item["noticemid"]) ) 
			{
				$salers = array( );
				if( isset($item["noticemid"]) && !empty($item["noticemid"]) ) 
				{
					$openids = array( );
					$strsopenids = explode(",", $item["noticemid"]);
					foreach( $strsopenids as $openid ) 
					{
						$openids[] = "'" . $openid . "'";
					}
					$salers = Db::query("select id,nickname,avatar from " . tablename("member") . " where id in (" . implode(",", $openids) . ") ");
				}
			}
		}
		$dispatch_data = Db::query("select * from " . tablename("shop_dispatch") . " where merchid=" . $merch_user['id'] . " and enabled=1 order by displayorder desc");
		$areas = model("common")->getAreas();
		$this->assign(['item'=>$item,'category'=>$category,'merchid'=>$merchid,'dispatch_data'=>$dispatch_data,'new_area'=>$new_area,'address_street'=>$address_street,'levels'=>$levels,'groups'=>$groups,'virtual_types'=>$virtual_types,'allspecs'=>$allspecs,'cates'=>$cates,'piclist'=>$piclist,'params'=>$params,'html'=>$html,'discounts_html'=>$discounts_html,'isdiscount_discounts_html'=>$isdiscount_discounts_html,'areas'=>$areas,'labelname'=>$labelname,'intervalprices'=>$intervalprices,'details'=>$details,'discounts'=>$discounts,'stores'=>$stores,'salers'=>$salers,'endtime'=>$endtime,'noticetype'=>$noticetype]);
		return $this->fetch('goods/post');
	}

	public function delete() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_goods')->where("id in( " . $id . " )")->field('id,title')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_goods')->where("id = " . $item["id"])->update(array( "deleted" => 1 ));
			model('shop')->mplog($merch['id'],"goods.delete", "删除商品 ID: " . $item["id"] . " 商品名称: " . $item["title"] . " ");
		}
		show_json(1, array( "url" => referer() ));
	}

	public function status() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		$status = intval(input('status'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_goods')->where("id in( " . $id . " )")->field('id,title')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_goods')->where("id = " . $item["id"])->update(array( "status" => intval($status) ));
			model('shop')->mplog($merch['id'],"goods.edit", ("修改商品状态<br/>ID: " . $item["id"] . "<br/>商品名称: " . $item["title"] . "<br/>状态: " . $status == 1 ? "上架" : "下架"));
		}
		show_json(1, array( "url" => referer() ));
	}

	public function delete1() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_goods')->where("id in( " . $id . " )")->field('id,title')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_goods')->where("id = " . $item["id"])->delete();
			model('shop')->mplog($merch['id'],"goods.edit", "从回收站彻底删除商品<br/>ID: " . $item["id"] . "<br/>商品名称: " . $item["title"]);
		}
		show_json(1, array( "url" => referer() ));
	}

	public function restore() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_goods')->where("id in( " . $id . " )")->field('id,title')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_goods')->where("id = " . $item["id"])->update(array( "deleted" => 0 ));
			model('shop')->mplog($merch['id'],"goods.edit", "从回收站恢复商品<br/>ID: " . $item["id"] . "<br/>商品名称: " . $item["title"]);
		}
		show_json(1, array( "url" => referer() ));
	}

	public function property() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		$type = input('type');
		$data = intval(input('data'));
		if( in_array($type, array( "new", "hot", "recommand", "discount", "time", "sendfree" )) ) 
		{
			Db::name('shop_goods')->where("id = " . $id)->update(array( "is" . $type => $data ));
			if( $type == "new" ) 
			{
				$typestr = "新品";
			}
			else 
			{
				if( $type == "hot" ) 
				{
					$typestr = "热卖";
				}
				else 
				{
					if( $type == "recommand" ) 
					{
						$typestr = "推荐";
					}
					else 
					{
						if( $type == "discount" ) 
						{
							$typestr = "促销";
						}
						else 
						{
							if( $type == "time" ) 
							{
								$typestr = "限时卖";
							}
							else 
							{
								if( $type == "sendfree" ) 
								{
									$typestr = "包邮";
								}
							}
						}
					}
				}
			}
			model('shop')->mplog($merch['id'],"goods.edit", "修改商品" . $typestr . "状态   ID: " . $id);
		}
		if( in_array($type, array( "status" )) ) 
		{
			Db::name('shop_goods')->where("id = " . $id)->update(array( $type => $data ));
			model('shop')->mplog($merch['id'],"goods.edit", "修改商品上下架状态   ID: " . $id);
		}
		if( in_array($type, array( "type" )) ) 
		{
			Db::name('shop_goods')->where("id = " . $id)->update(array( $type => $data ));
			model('shop')->mplog($merch['id'],"goods.edit", "修改商品类型   ID: " . $id);
		}
		show_json(1);
	}

	public function change() 
	{
		$merch = $this->merch;
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			show_json(0, array( "message" => "参数错误" ));
		}
		$type = trim(input('type'));
		$value = trim(input('value'));
		if( !in_array($type, array( "title", "marketprice", "total", "goodssn", "productsn", "displayorder", "merchdisplayorder" )) ) 
		{
			show_json(0, array( "message" => "参数错误" ));
		}
		$goods = Db::name('shop_goods')->where('id = ' . $id)->field('id,hasoption')->find();
		if( empty($goods) ) 
		{
			show_json(0, array( "message" => "参数错误" ));
		}
		if( $type == "title" ) 
		{
			if( empty($merch["goodschecked"]) ) 
			{
				$checked = 1;
			}
			else 
			{
				$checked = 0;
			}
		}
		else 
		{
			$checked = 0;
		}
		Db::name('shop_goods')->where('id = ' . $id)->update(array( $type => $value, "checked" => $checked ));
		if( $goods["hasoption"] == 0 ) 
		{
			$sql = "update " . tablename("shop_goods") . " set minprice = marketprice,maxprice = marketprice where id = " . $goods["id"] . " and hasoption=0;";
			Db::query($sql);
		}
		show_json(1);
	}

	public function tpl() 
	{
		$merch = $this->merch;
		$tpl = trim(input('tpl'));
		if( $tpl == "option" ) 
		{
			$tag = random(32);
			$this->assign(['tag'=>$tag]);
			return $this->fetch('goods/tpl/option');
		}
		else 
		{
			if( $tpl == "spec" ) 
			{
				$spec = array( "id" => random(32), "title" => $_POST["title"] );
				$this->assign(['spec'=>$spec]);
				return $this->fetch('goods/tpl/spec');
			} else {
				if( $tpl == "specitem" ) {
					$spec = array( "id" => input('specid') );
					$specitem = array( "id" => random(32), "title" => $_POST["title"], "show" => 1 );
					$this->assign(['spec'=>$spec,'specitem'=>$specitem]);
					return $this->fetch('goods/tpl/spec_item');
				} else {
					if( $tpl == "param" ) {
						$tag = random(32);
						$this->assign(['tag'=>$tag]);
						return $this->fetch('goods/tpl/param');
					}
				}
			}
		}
	}

	public function query() 
	{
		$merch = $this->merch;
		$kwd = trim(input('keyword'));
		$condition = " status=1 and deleted=0 and merchid=" . $merch['id'];
		if( !empty($kwd) ) 
		{
			$condition .= " AND (`title` LIKE '%" . $kwd . "%' OR `keywords` LIKE '%" . $kwd . "%')";
		}
		$ds = Db::name('shop_goods')->where($condition)->field('id,title,thumb,marketprice,productprice,share_title,share_icon,description,minprice')->order('createtime desc')->select();
		$ds = set_medias($ds, array( "thumb", "share_icon" ));
		if( $_POST["suggest"] ) 
		{
			exit( json_encode(array( "value" => $ds )) );
		}
		$this->assign(['kwd'=>$kwd,'ds'=>$ds]);
		return $this->fetch('goods/query');
	}

	public function category()
	{
		$children = array();
		$merch = $this->merch;
		$category = Db::name('shop_merch_goods_category')->where('merchid = ' . $merch['id'])->order('parentid asc,displayorder desc')->select();
		foreach ($category as $index => $row) {
			if (!empty($row['parentid'])) {
				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
		}
		$this->assign(['children'=>$children,'category'=>$category]);
		return $this->fetch('goods/category/index');
	}

	public function categoryadd()
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	public function categoryedit()
	{
		$categorydata = $this->categorypost();
		return $categorydata;
	}

	protected function categorypost()
	{
		$merch = $this->merch;
		$parentid = input('parentid/d');
		$id = input('id/d');
		$parent = array();
		$parent1 = array();
		if (!empty($id)) {
			$item = Db::name('shop_merch_goods_category')->where('id',$id)->find();
			$parentid = $item['parentid'];
		} else {
			$item = array('displayorder' => 0);
		}

		if (!empty($parentid)) {
			$parent = Db::name('shop_merch_goods_category')->where('id',$parentid)->find();

			if (empty($parent)) {
				$this->error('抱歉，上级分类不存在或是已经被删除！', url('merch/goods/categoryadd'));
			}

			if (!empty($parent['parentid'])) {
				$parent1 = Db::name('shop_merch_goods_category')->where('id',$parent['parentid'])->find();
			}
		}

		if (empty($parent)) {
			$level = 1;
		} else if (empty($parent['parentid'])) {
			$level = 2;
		} else {
			$level = 3;
		}

		if (!empty($item)) {
			$item['url'] = url('merch/goods/list', array('cate' => $item['id']));
		}

		if (Request::instance()->isPost()) {
			$data = array('merchid' => $merch['id'], 'name' => trim(input('catename')), 'enabled' => intval(input('enabled')), 'displayorder' => intval(input('displayorder')), 'isrecommand' => intval(input('isrecommand')), 'ishome' => intval(input('ishome')), 'description' => input('description'), 'parentid' => intval($parentid), 'thumb' => trim(input('thumb')), 'advimg' => trim(input('advimg')), 'advurl' => trim(input('advurl')), 'level' => $level);

			if (!empty($id)) {
				unset($data['parentid']);
				Db::name('shop_merch_goods_category')->where('id',$id)->update($data);
				model('shop')->mplog($merch['id'],'shop.category.edit', '修改分类 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_merch_goods_category')->insertGetId($data);
				model('shop')->mplog($merch['id'],'shop.category.add', '添加分类 ID: ' . $id);
			}
			show_json(1, array('url' => url('merch/goods/category')));
		}
		$this->assign(['item'=>$item,'parentid'=>$parentid,'parent'=>$parent,'parent1'=>$parent1]);
		return $this->fetch('goods/category/post');
	}

	public function categorydelete()
	{
		$id = input('id/d');
		$merch = $this->merch;
		$item = Db::name('shop_merch_goods_category')->where('id',$id)->field('id,name,parentid')->find();
		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}
		$child = Db::name('shop_merch_goods_category')->where('parentid',$id)->count();
		if($child > 0)
		{
			show_json(0,'请先删除下级分类');
		}
		Db::name('shop_merch_goods_category')->where('id',$id)->whereOr('parentid',$id)->delete();
		model('shop')->mplog($merch['id'],'shop.category.delete', '删除分类 ID: ' . $id . ' 分类名称: ' . $item['name']);
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = input('id/d');
		$merch = $this->merch;
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_merch_goods_category')->where('id','in',$id)->select();

		foreach ($items as $item) {
			Db::name('shop_merch_goods_category')->where('id',$item['id'])->setField('enabled',input('enabled'));
			model('shop')->mplog($merch['id'],'shop.dispatch.edit', ('修改分类状态<br/>ID: ' . $item['id'] . '<br/>分类名称: ' . $item['name'] . '<br/>状态: ' . input('enabled')) == 1 ? '显示' : '隐藏');
		}
		show_json(1);
	}

	public function label()
	{
		$merch = $this->merch;
		$condition = ' merchid = ' . $merch['id'];
		$psize = 20;

		if (input('enabled') != '') {
			$enabled = intval(input('enabled'));
			$condition .= ' and status = ' . $enabled;
		}

		if (!empty(input('keyword'))) {
			$keyword = trim(input('keyword'));
			$condition .= ' and label like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_goods_label')->where($condition)->order('id','desc')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('goods/label/index');
	}

	public function labeladd()
	{
		$labeldata = $this->labelpost();
		return $labeldata;
	}

	public function labeledit()
	{
		$labeldata = $this->labelpost();
		return $labeldata;
	}

	protected function labelpost()
	{
		$id = input('id/d');
		$merch = $this->merch;
		if (!empty($id)) {
			$item = Db::name('shop_goods_label')->where('id',$id)->find();

			if (json_decode($item['labelname'], true)) {
				$labelname = json_decode($item['labelname'], true);
			}
			else {
				$labelname = unserialize($item['labelname']);
			}
		}

		if (Request::instance()->isPost()) {
			if (empty(input('labelname/a'))) {
				$labelname = array();
			}
			$labelname = input('labelname/a');
			$data = array('merchid' => $merch['id'],'displayorder' => input('displayorder/d'), 'label' => trim(input('label')), 'labelname' => serialize(array_filter($labelname)), 'status' => intval(input('status')));

			if (!empty($item)) {
				Db::name('shop_goods_label')->where('id',$item['id'])->update($data);
				model('shop')->mplog($merch['id'],'goods.label.edit', '修改标签组 ID: ' . $id);
			}
			else {
				$id = Db::name('shop_goods_label')->insertGetId($data);
				model('shop')->mplog($merch['id'],'goods.label.add', '添加标签组 ID: ' . $id);
			}

			show_json(1, array('url' => url('merch/goods/labeledit', array('id' => $id))));
		}
		$this->assign(['item'=>$item,'labelname'=>$labelname]);
		return $this->fetch('goods/label/post');
	}

	public function labeldelete()
	{
		$id = input('id/d');
		$merch = $this->merch;
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_label')->where('id','in',$id)->select();

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $item) {
			Db::name('shop_goods_label')->where('id',$item['id'])->delete();
			model('shop')->mplog($merch['id'],'goods.edit', '从回收站彻底删除标签组<br/>ID: ' . $item['id'] . '<br/>标签组名称: ' . $item['label']);
		}

		show_json(1, array('url' => referer()));
	}

	public function labelstatus()
	{
		$id = input('id/d');
		$merch = $this->merch;
		if (empty($id)) {
			$id = input('ids/a');
		}

		$items = Db::name('shop_goods_label')->where('id','in',$id)->select();

		if (empty($items)) {
			$items = array();
		}

		foreach ($items as $item) {
			Db::name('shop_goods_label')->where('id',$item['id'])->setField('status',input('status/d'));
			model('shop')->mplog($merch['id'],'goods.label.edit', ('修改标签组状态<br/>ID: ' . $item['id'] . '<br/>标签组名称: ' . $item['label'] . '<br/>状态: ' . input('status/d')) == 1 ? '上架' : '下架');
		}

		show_json(1, array('url' => referer()));
	}

	public function labelquery()
	{
		$kwd = trim(input('keyword'));
		$merch = $this->merch;
		$params = array();
		$condition = ' status = 1 and merchid = 0';

		if (!empty($kwd)) {
			$condition .= ' and label like "%' . $kwd . '%"';
		}

		$labels = Db::name('shop_goods_label')->where($condition)->select();

		if (empty($labels)) {
			$labels = array();
		}

		foreach ($labels as $key => $value) {
			if (json_decode($value['labelname'], true)) {
				$labels[$key]['labelname'] = json_decode($value['labelname'], true);
			}
			else {
				$labels[$key]['labelname'] = unserialize($value['labelname']);
			}
		}
		$this->assign(['labels'=>$labels]);
		return $this->fetch('goods/label/query');
	}

}