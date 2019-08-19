<?php
/**
 * 拼团
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\mobile\controller;
use think\Db;
use think\Request;
class Groups extends Base
{
	public function index()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$banners = array();
		$category = array();
		$goods = array();
		$banners = Db::name('shop_groups_banner')->where('enabled',1)->order('displayorder','desc')->field('enabled,displayorder',true)->select();
		$banners = set_medias($banners, 'thumb');
		$category = Db::name('shop_groups_goods_category')->where('enabled=1')->order('displayorder desc')->field('id,name,thumb')->select();
		$category = set_medias($category, 'thumb');	

		$goods = Db::name('shop_groups_goods')->field('id,title,is_ladder,thumb,price,groupnum,groupsprice,teamnum,single,singleprice,isindex,goodsnum,units,description')->where('status=1 and deleted=0')->order('displayorder desc,id DESC')->page($page,$pagesize)->select();
		foreach ($goods as &$row) {
			$row['thumb'] = tomedia($row['thumb']);
			$row["fightnum"] = Db::name('shop_groups_order')->where('goodsid = ' . $row['id'] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
			$row["fightnum"] = $row["teamnum"] + $goods["fightnum"];
			$team = Db::name('shop_groups_order')->where('paytime > 0 and heads = 1 and is_team = 1 and goodsid = ' . $row['id'])->field('id,mid,teamid')->order('createtime','desc')->find();
			$teams = Db::name('shop_groups_order')->alias('o')->join('member m','o.mid = m.id','left')->where('o.teamid',$team['teamid'])->field('o.id,m.avatar')->select();
			$row['teams'] = $teams;
		}
		unset($row);

		$this->assign(['banners'=>$banners,'category'=>$category,'goods'=>$goods]);
		return $this->fetch('');
	}

	public function goods()
	{
		$id = input('id');
		$groupsset = Db::name('shop_groups_set')->field('description,groups_description,discount,headstype,headsmoney,headsdiscount')->find();
		$groupsset["groups_description"] = lazy($groupsset["groups_description"]);
		$goods = Db::name('shop_groups_goods')->where('id = ' . $id . ' and status = 1 and deleted = 0')->find();
		if( empty($id) || empty($goods) ) 
		{
			$this->error("你访问的商品不存在或已删除!");
		}
		if( !empty($goods["thumb_url"]) ) 
		{
			$goods["thumb_url"] = array_merge(iunserializer($goods["thumb_url"]));
		}
		$goods = set_medias($goods, "thumb");
		$goods["fightnum"] = Db::name('shop_groups_order')->where('goodsid = ' . $goods['id'] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
		$goods["fightnum"] = $goods["teamnum"] + $goods["fightnum"];
		$goods["content"] = lazy($goods["content"]);
		if( empty($goods) ) 
		{
			$this->error("商品已下架或被删除!");
		}
		if( !empty($groupsset["discount"]) ) 
		{
			if( empty($goods["discount"]) ) 
			{
				$goods["headstype"] = $groupsset["headstype"];
				$goods["headsmoney"] = $groupsset["headsmoney"];
				$goods["headsdiscount"] = $groupsset["headsdiscount"];
			}
			if( $goods["groupsprice"] < $goods["headsmoney"] ) 
			{
				$goods["headsmoney"] = $goods["groupsprice"];
			}
		}
		$specArr = array( );
		if( $goods["more_spec"] == 1 ) 
		{
			$group_goods = Db::name('shop_groups_goods')->where('id = ' . $id)->find();
			if( empty($group_goods["gid"]) ) 
			{
				$this->error("缺少商品");
			}
			$specArr = Db::name('shop_goods_spec')->where('goodsid = ' . $group_goods["gid"])->field('id,title')->select();
			foreach( $specArr as $k => $v ) 
			{
				$specArr[$k]["item"] = Db::name('shop_goods_spec_item')->where('specid = ' . $v["id"])->field('id,title,specid,thumb')->select();
			}
		}
		$ladder = array( );
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $id)->select();
		}
		$this->assign(['goods'=>$goods,'groupsset'=>$groupsset,'ladder'=>$ladder,'specArr'=>$specArr]);
		return $this->fetch('groups/goods/detail');
	}

	public function openGroups()
	{
		$id = intval(input('id'));
		$mid = 0;
		if( empty($id) ) 
		{
			$this->error("你访问的商品不存在或已删除!");
		}
		$is_ladder = intval(input('is_ladder'));
		$goods = Db::name('shop_groups_goods')->where('id = ' . $id . ' and status = 1 and deleted = 0 ')->find();
		$goods["fightnum"] = Db::name('shop_groups_order')->where('goodsid = ' . $goods['id'] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
		$goods["fightnum"] = $goods["teamnum"] + $goods["fightnum"];
		$goods = set_medias($goods, "thumb");
		$ladder = array( );
		if( $goods["is_ladder"] == 1 && $is_ladder == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $id)->select();
			$info = Db::name('shop_groups_order')->where('mid <> ' . $mid . ' and success = 0 and is_team =1 and status>0 and ladder_id >0 and goodsid = ' . $id)->select();
			if( $info[0]["order_num"] == 0 ) 
			{
				$order_num = 0;
			}
			else 
			{
				$order_num = 1;
			}
		}
		if( $is_ladder != $goods["is_ladder"] ) 
		{
			$this->error("拼团类型错误,请重新选择!");
		}
		$specArr = array( );
		if( $goods["more_spec"] == 1 && $_GET["more_spec"] == 1 ) 
		{
			$group_goods = Db::name('shop_groups_goods')->where('id = ' . $id)->select();
			if( empty($group_goods["gid"]) ) 
			{
				$this->error("缺少商品");
			}
			$specArr = Db::name('shop_goods_spec')->where('goodsid = ' . $group_goods["gid"])->field('id,title')->select();
			foreach( $specArr as $k => $v ) 
			{
				$specArr[$k]["item"] = Db::name('shop_goods_spec_item')->where('specid = ' . $v["id"])->field('id,title,specid,thumb')->select();
			}
			$order_num = Db::name('shop_groups_order')->where('mid <> 0 and success = 0 and status>0 and more_spec =1 and is_team =1 and `goodsid`= ' . $id)->count();
		}
		if( $goods["is_ladder"] == 0 && $goods["more_spec"] == 0 ) 
		{
			$order_num = Db::name('shop_groups_order')->where('mid <> 0 and success = 0 and is_team =1 and status>0 and `goodsid`= ' . $id)->count();
		}
		$teams = Db::name('shop_groups_goods')->where('deleted = 0 and status = 1')->limit(4)->select();
		foreach( $teams as $key => $value ) 
		{
			$value["fightnum"] = Db::name('shop_groups_order')->where(' goodsid = ' . $value["id"] . ' and deleted = 0 and is_team = 1 and status > 0 ')->count();
			$value["fightnum"] = $value["teamnum"] + $value["fightnum"];
			$value = set_medias($value, "thumb");
			$teams[$key] = $value;
		}
		if( empty($goods) ) 
		{
			$this->error("商品已下架或被删除!");
		}
		$this->assign(['goods'=>$goods,'ladder'=>$ladder,'specArr'=>$specArr,'teams'=>$teams,'order_num'=>$order_num]);
		return $this->fetch('groups/goods/openGroups');
	}

	public function goodsCheck() 
	{
		$id = intval(input('id'));
		$type = input('type');
		$mid = 0;
		if( empty($id) ) 
		{
			show_json(0, array( "message" => "商品不存在！" ));
		}
		$goods = Db::name('shop_groups_goods')->where('id = ' . $id . ' and status = 1 and deleted = 0')->find();
		if( empty($goods) ) 
		{
			show_json(0, array( "message" => "商品不存在！" ));
		}
		if( $goods["stock"] <= 0 ) 
		{
			show_json(0, array( "message" => "您选择的商品库存不足，请浏览其他商品或联系商家！" ));
		}
		if( empty($goods["status"]) ) 
		{
			show_json(0, array( "message" => "您选择的商品已经下架，请浏览其他商品或联系商家！" ));
		}
		$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $id)->count();
		if( !empty($goods["purchaselimit"]) && $goods["purchaselimit"] <= $ordernum ) 
		{
			show_json(0, array( "message" => "您已到达此商品购买上限，请浏览其他商品或联系商家！" ));
		}
		$order = Db::name('shop_groups_order')->where('goodsid = ' . $id . ' and status >= 0  and mid = ' . $mid . ' and success = 0  and is_team = 1 and deleted = 0 ')->select();
		if( $order && $order["status"] == 0 ) 
		{
			show_json(0, array( "message" => "您的订单已存在，请尽快完成支付！" ));
		}
		if( $order && $order["status"] == 1 && $type == "groups" ) 
		{
			show_json(0, array( "message" => "您已经参与了该团，请等待拼团结束后再进行购买！" ));
		}
		if( $type == "single" && empty($goods["single"]) ) 
		{
			show_json(0, array( "message" => "商品不允许单购，请重新选择！" ));
		}
		$ladder = array( );
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $id)->select();
			if( $_GET["fightgroups"] == 1 ) 
			{
				$info = Db::name('shop_groups_order')->where('mid != ' . $mid . ' and success = 0 and status>0 and ladder_id >0 and goodsid = ' . $id)->field('ladder_id,count(ladder_id) as order_num')->group('ladder_id')->select();
				if( !empty($info) && !empty($ladder) ) 
				{
					foreach( $ladder as $key => $value ) 
					{
						foreach( $info as $k => $v ) 
						{
							if( $value["id"] == $v["ladder_id"] ) 
							{
								$ladder[$key]["order_num"] = $v["order_num"];
							}
						}
					}
				}
			}
		}
		if( empty($goods["stock"]) ) 
		{
			show_json(0, array( "message" => "商品库存为0，暂时无法购买，请浏览其他商品！" ));
		}
		$specArr = array( );
		if( $goods["more_spec"] == 1 ) 
		{
			$group_goods = Db::name('shop_groups_goods')->where('id = ' . $id)->find();
			if( empty($group_goods["gid"]) ) 
			{
				show_json(0,"缺少商品");
			}
			$specArr = Db::name('shop_goods_spec')->where('goodsid = ' . $group_goods["gid"])->field('id,title')->select();
			foreach( $specArr as $k => $v ) 
			{
				$specArr[$k]["item"] = Db::name('shop_goods_spec_item')->where('specid = ' . $v["id"])->field('id,title,specid,thumb')->select();
			}
		}
		show_json(1, array( "ladder" => $ladder, "specArr" => $specArr ));
	}

	public function get_option() 
	{
		$specArr = $_POST['spec_id'];
		asort($specArr);
		if( !empty($specArr) ) 
		{
			$spec_id = implode("_", $specArr);
			$goods_option = Db::name('shop_groups_goods_option')->where('specs = ' . $spec_id)->find();
			show_json(1, array( "data" => $goods_option ));
		}
	}

	public function confirm() 
	{
		$mid = 0;
		$isverify = false;
		$goodsid = intval(input('id'));
		$type = input('type');
		$heads = intval(input('heads'));
		$teamid = intval(input('teamid'));
		$member = model("member")->getMember($mid);
		$credit = array( );
		$goods = Db::name('shop_groups_goods')->where('id = ' . $goodsid . ' and deleted = 0')->find();
		if( $goods["stock"] <= 0 ) 
		{
			$this->error("您选择的商品库存不足，请浏览其他商品或联系商家！");
		}
		if( empty($goods["status"]) ) 
		{
			$this->error("您选择的商品已经下架，请浏览其他商品或联系商家！");
		}
		$ladder = array();
		if( $goods["is_ladder"] == 1 ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('goods_id = ' . $goodsid)->find();
		}
		if( 0 < $_GET["ladder_id"] ) 
		{
			$ladder = Db::name('shop_groups_ladder')->where('id = ' . $_GET["ladder_id"])->find();
		}
		if( $goods["more_spec"] == 1 ) 
		{
			$option_id = $_GET["options_id"];
			if( !empty($option_id) ) 
			{
				$option = Db::name('shop_groups_goods_option')->where('goods_option_id=' . $option_id . ' and groups_goods_id=' . $goods["id"])->find();
			}
		}
		if( $type == "groups" ) 
		{
			$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 1')->count();
		}
		else 
		{
			if( $type == "single" ) 
			{
				$ordernum = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and is_team = 0')->count();
			}
		}
		if( !empty($goods["purchaselimit"]) && $goods["purchaselimit"] <= $ordernum ) 
		{
			$this->error("您已到达此商品购买上限，请浏览其他商品或联系商家！");
		}
		if( $type == "groups" ) 
		{
			$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 1')->find();
		}
		else 
		{
			if( $type == "single" ) 
			{
				$order = Db::name('shop_groups_order')->where('mid = ' . $mid . ' and status >= 0 and goodsid = ' . $goodsid . ' and success = 0 and deleted = 0 and is_team = 0')->find();
			}
		}
		if( $order && $order["status"] == 0 ) 
		{
			$this->error("您的订单已存在，请尽快完成支付！");
		}
		if( $order && $order["status"] == 1 && $type == "groups" ) 
		{
			$this->error("您已经参与了该团，请等待拼团结束后再进行购买1！");
		}
		if( !empty($teamid) ) 
		{
			$orders = Db::name('shop_groups_order')->where('teamid = ' . $teamid)->select();
			foreach( $orders as $key => $value ) 
			{
				if( $orders && $value["success"] == -1 ) 
				{
					$this->error("该活动已过期，请浏览其他商品或联系商家！");
				}
				if( $orders && $value["success"] == 1 ) 
				{
					$this->error("该活动已结束，请浏览其他商品或联系商家！");
				}
			}
			$num = Db::name('shop_groups_order')->where('teamid = ' . $teamid . ' and status > 0 and goodsid = ' . $goods["id"])->count();
			if( !empty($ladder) ) 
			{
				if( $ladder["ladder_num"] <= $num ) 
				{
					$this->error("该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
			else 
			{
				if( $goods["groupnum"] <= $num ) 
				{
					$this->error("该活动已成功组团，请浏览其他商品或联系商家！");
				}
			}
		}
		if( $type == "groups" && $goods["more_spec"] == 0 && $goods["is_ladder"] == 0 ) 
		{
			$goodsprice = $goods["groupsprice"];
			$price = $goods["groupsprice"];
			$groupnum = intval($goods["groupnum"]);
			$is_team = 1;
		}
		else 
		{
			if( $type == "single" && $goods["more_spec"] == 1 ) 
			{
				$goodsprice = $option["single_price"];
				$price = $option["single_price"];
				$goods["singleprice"] = $option["single_price"];
				$groupnum = 1;
				$is_team = 0;
				$teamid = 0;
			}
			else 
			{
				if( $type == "groups" && !empty($ladder) && $goods["is_ladder"] == 1 ) 
				{
					$goodsprice = $ladder["ladder_price"];
					$price = $ladder["ladder_price"];
					$groupnum = $ladder["ladder_num"];
					$is_team = 1;
					$goods["groupsprice"] = $ladder["ladder_price"];
				}
				else 
				{
					if( $type == "groups" && !empty($option) && $goods["more_spec"] == 1 ) 
					{
						$goodsprice = $option["price"];
						$price = $option["price"];
						$groupnum = intval($goods["groupnum"]);
						$is_team = 1;
						$goods["groupsprice"] = $option["price"];
					}
					else 
					{
						if( $type == "single" ) 
						{
							$goodsprice = $goods["singleprice"];
							$price = $goods["singleprice"];
							$groupnum = 1;
							$is_team = 0;
							$teamid = 0;
						}
					}
				}
			}
		}
		$set = Db::name('shop_groups_set')->field('discount,headstype,headsmoney,headsdiscount')->find();
		if( !empty($set["discount"]) && $heads == 1 ) 
		{
			if( !empty($goods["discount"]) ) 
			{
				if( empty($goods["headstype"]) ) 
				{
				}
				else 
				{
					if( 0 < $goods["headsdiscount"] ) 
					{
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $goods["headsdiscount"]) / 100, 2);
					}
					else 
					{
						if( $goods["headsdiscount"] == 0 ) 
						{
							$goods["headsmoney"] = 0;
						}
					}
				}
			}
			else 
			{
				if( empty($set["headstype"]) ) 
				{
					$goods["headsmoney"] = $set["headsmoney"];
				}
				else 
				{
					if( 0 < $set["headsdiscount"] ) 
					{
						$goods["headsmoney"] = $goods["groupsprice"] - price_format(($goods["groupsprice"] * $set["headsdiscount"]) / 100, 2);
					}
				}
				$goods["headstype"] = $set["headstype"];
				$goods["headsdiscount"] = $set["headsdiscount"];
			}
			if( $goods["groupsprice"] < $goods["headsmoney"] ) 
			{
				$goods["headsmoney"] = $goods["groupsprice"];
			}
			$price = $price - $goods["headsmoney"];
			if( $price < 0 ) 
			{
				$price = 0;
			}
		}
		else 
		{
			$goods["headsmoney"] = 0;
		}
		if( !empty($goods["isverify"]) ) 
		{
			$isverify = true;
			$goods["freight"] = 0;
			$storeids = array( );
			$merchid = 0;
			if( !empty($goods["storeids"]) ) 
			{
				$merchid = $goods["merchid"];
				$storeids = array_merge(explode(",", $goods["storeids"]), $storeids);
			}
			if( empty($storeids) ) 
			{
				if( 0 < $merchid ) 
				{
					$stores = Db::name('shop_store')->where('merchid=' . $merchid . ' and status=1 and type in(2,3)')->select();
				}
				else 
				{
					$stores = Db::name('shop_store')->where('status=1 and type in(2,3)')->select();
				}
			}
			else 
			{
				if( 0 < $merchid ) 
				{
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and merchid=" . $merchid . " and status=1 and type in(2,3)")->select();
				}
				else 
				{
					$stores = Db::name('shop_store')->where("id in (" . implode(",", $storeids) . ") and status=1 and type in(2,3)")->select();
				}
			}
			$verifycode = "PT" . random(8, true);
			while( 1 ) 
			{
				$count = Db::name('shop_groups_order')->where("verifycode=" . $verifycode)->count();
				if( $count <= 0 ) 
				{
					break;
				}
				$verifycode = "PT" . random(8, true);
			}
			$verifynum = (!empty($goods["verifytype"]) ? ($verifynum = $goods["verifynum"]) : 1);
		}
		else 
		{
			$address = Db::name('shop_member_address')->where("mid=" . $mid . ' and deleted=0 and isdefault=1 ')->find();
		}
		$creditdeduct =  Db::name('shop_groups_set')->field('creditdeduct,groupsdeduct,credit,groupsmoney')->find();
		if( intval($creditdeduct["creditdeduct"]) ) 
		{
			if( intval($creditdeduct["groupsdeduct"]) ) 
			{
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $creditdeduct["groupsmoney"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $creditdeduct["groupsmoney"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $creditdeduct["groupsmoney"];
				}
				else 
				{
					$credit["deductprice"] = 0;
				}
			} else {
				$sys_data = model("common")->getPluginset("sale");
				if( 0 < $goods["deduct"] ) 
				{
					$credit["deductprice"] = round(intval($member["credit1"]) * $sys_data["money"], 2);
					if( $price <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $price;
					}
					if( $goods["deduct"] <= $credit["deductprice"] ) 
					{
						$credit["deductprice"] = $goods["deduct"];
					}
					$credit["credit"] = floor($credit["deductprice"] / $sys_data["money"]);
					if( $credit["credit"] < 1 ) 
					{
						$credit["credit"] = 0;
						$credit["deductprice"] = 0;
					}
					$credit["deductprice"] = $credit["credit"] * $sys_data["money"];
				}
				else 
				{
					$credit["deductprice"] = 0;
				}
			}
		}
		$template_flag = 0;
		$ordersn = model("common")->createNO("shop_groups_order", "orderno", "PT");
		if( Request::instance()->isPost() ) 
		{
			if( empty($_POST["aid"]) && !$isverify ) 
			{
				$this->error("请选择地址");
				exit();
			}
			if( $isverify && (empty($_POST["realname"]) || empty($_POST["mobile"])) ) 
			{
				$this->error("联系人或联系电话不能为空！");
			}
			if( 0 < intval($_POST["aid"]) && !$isverify ) 
			{
				$order_address = Db::name('shop_member_address')->where('id = ' . intval($_POST['aid']) . ' and mid = ' . $mid)->find();
				if( empty($order_address) ) 
				{
					$this->error("未找到地址");
					header("location: " . url("mobile/groups/addresspost"));
					exit();
				}
				if( empty($order_address["province"]) || empty($order_address["city"]) ) 
				{
					$this->error("地址请选择省市信息");
					header("location: " . url("mobile/groups/addresspost"));
					exit();
				}
			}
			$data = array("groupnum" => $groupnum, "mid" => $mid, "paytime" => "", "orderno" => $ordersn, "credit" => (intval($_POST["isdeduct"]) ? $_POST["credit"] : 0), "creditmoney" => (intval($_POST["isdeduct"]) ? $_POST["creditmoney"] : 0), "price" => $price, "freight" => $goods["freight"], "status" => 0, "goodsid" => $goodsid, "teamid" => $teamid, "is_team" => $is_team, "more_spec" => $goods["more_spec"], "heads" => $heads, "discount" => (!empty($heads) ? $goods["headsmoney"] : 0), "addressid" => intval($_POST["aid"]), "address" => iserializer($order_address), "message" => trim($_POST["message"]), "realname" => ($isverify ? trim($_POST["realname"]) : ""), "mobile" => ($isverify ? trim($_POST["mobile"]) : ""), "endtime" => $goods["endtime"], "isverify" => intval($goods["isverify"]), "verifytype" => intval($goods["verifytype"]), "verifycode" => (!empty($verifycode) ? $verifycode : 0), "verifynum" => (!empty($verifynum) ? $verifynum : 1), "createtime" => time() );
			if( $goods["is_ladder"] == 1 && 0 < $_POST["ladder_id"] ) 
			{
				$data["is_ladder"] = 1;
				$data["ladder_id"] = $_POST["ladder_id"];
			}
			if( $goods["more_spec"] == 1 && 0 < $_POST["options_id"] ) 
			{
				$data["specs"] = $option["specs"];
			}
			$order_insert = Db::name('shop_groups_order')->insertGetId($data);
			if( !$order_insert ) 
			{
				$this->error("生成订单失败！");
			}
			$orderid = $order_insert;
			if( empty($teamid) && $type == "groups" ) 
			{
				Db::name('shop_groups_order')->where('id = ' . intval($orderid))->update(array( "teamid" => $orderid ));
			}
			if( !empty($orderid) && $goods["more_spec"] == 1 ) 
			{
				$_data = array( "goods_id" => $goods["gid"], "groups_goods_id" => $goods["id"], "groups_goods_option_id" => $_POST["options_id"], "option_name" => $option["title"], "groups_order_id" => $orderid, "price" => $price, "create_time" => time() );
				Db::name('shop_groups_order_goods')->insert($_data);
			}
			$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid))->find();
			header("location: " . url("mobile/groups/pay", array( "teamid" => (empty($teamid) ? $order["teamid"] : $teamid), "orderid" => $orderid )));
		}
		$this->assign(['carrier_list'=>$carrier_list,'isverify'=>$isverify,'address'=>$address,'member'=>$member,'goods'=>$goods,'option'=>$option,'creditdeduct'=>$creditdeduct,'stores'=>$stores,'price'=>$price,'is_team'=>$is_team,'set'=>$set,'isdiscountprice'=>$isdiscountprice,'type'=>$type,'couponcount'=>$couponcount,'credit'=>$credit]);
		return $this->fetch('groups/order/confirm');
	}

	public function pay()
	{
		$mid = 0;
		$member = model("member")->getMember($mid);
		$orderid = intval(input('orderid'));
		$teamid = intval(input('teamid'));
		$order = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->where('o.id = ' . intval($orderid))->field('o.*,g.title,g.status as gstatus,g.deleted as gdeleted,g.stock')->find();
		if( empty($order) ) 
		{
			$this->error("订单未找到！", url("mobile/groups/index"));
		}
		if( !empty($isteam) && $order["success"] == -1 ) 
		{
			$this->error("该活动已失效，请浏览其他商品或联系商家！", url("mobile/groups/index"));
		}
		if( empty($order["gstatus"]) || !empty($order["gdeleted"]) ) 
		{
			$this->error($order["title"] . "<br/> 已下架!", url("mobile/groups/index"));
		}
		if( $order["stock"] <= 0 ) 
		{
			$this->error($order["title"] . "<br/>库存不足!", url("mobile/groups/index"));
		}
		if( !empty($teamid) ) 
		{
			$team_orders = Db::name('shop_groups_order')->where('teamid = ' . intval($teamid))->select();
			foreach( $team_orders as $key => $value ) 
			{
				if( $team_orders && $value["success"] == -1 ) 
				{
					$this->error("该活动已过期，请浏览其他商品或联系商家！", url("mobile/groups/index"));
				}
				if( $team_orders && $value["success"] == 1 ) 
				{
					$this->error("该活动已结束，请浏览其他商品或联系商家！", url("mobile/groups/index"));
				}
			}
			$num = Db::name('shop_groups_order')->where('teamid = ' . intval($teamid) . ' and status > 0 ')->count();
			if( $order["groupnum"] <= $num ) 
			{
				$this->error("该活动已成功组团，请浏览其他商品或联系商家！", url("mobile/groups/index"));
			}
		}
		if( empty($order) ) 
		{
			header("location: " . url("mobile/groups"));
			exit();
		}
		if( $order["status"] == -1 ) 
		{
			header("location: " . url("mobile/groups/goods", array( "id" => $order["goodsid"] )));
			exit();
		}
		if( 1 <= $order["status"] ) 
		{
			header("location: " . url("mobile/groups/goods", array( "id" => $order["goodsid"] )));
			exit();
		}
		$log = Db::name('shop_core_paylog')->where('module','groups')->where('tid',$order['orderno'])->find();
		if( !empty($log) && $log["status"] != "0" ) 
		{
			header("location: " . url("mobile/groups/order", array( "id" => $order["id"] )));
			exit();
		}
		if( empty($log) ) 
		{
			$log = array( "mid" => $mid, "module" => "groups", "tid" => $order["orderno"], "credit" => $order["credit"], "creditmoney" => $order["creditmoney"], "fee" => $order["price"] - $order["creditmoney"] + $order["freight"], "status" => 0 );
			$plid = Db::name('shop_core_paylog')->insertGetId($log);
		}
		$set = model("common")->getSysset(array( "shop", "pay" ));
		$sec = model("common")->getSec();
		$sec = iunserializer($sec["sec"]);
		$param_title = $set["shop"]["name"] . "订单";
		$credit = array( "success" => false );
		if( isset($set["pay"]) && $set["pay"]["credit"] == 1 && $order["deductcredit2"] <= 0 ) 
		{
			$credit = array( "success" => true, "current" => $member["credit2"] );
		}
		$wechat = array( "success" => false );
		$alipay = array( "success" => false );
		$shopset = $set['shop'];
		$params = array( );
		$params["tid"] = $log["tid"];
		$params["user"] = $openid;
		$params["fee"] = $log["fee"];
		$params["title"] = $param_title;
		$params['product_id'] = $orderid;
		$paytype = input('paytype/d') ? input('paytype/d') : $order['pay_type'];
		if ($paytype == 1) {
			if( is_weixin() ) 
			{
				if( isset($set["pay"]) && $set["pay"]["wx_wechat"] == 1 ) 
				{
					$wechat = model("payment")->wechat_build($params, 'wechat', 3, 'wechat');
					if( !is_array($wechat) ) 
					{
						$this->result(0,$wechat);
					}
					$wechat = json_encode($wechat);
				}
			} else {
				if(is_mobile()) {
					if( isset($set["pay"]) && $set["pay"]["web_wechat"] == 1 ) 
					{
						$wechat = model("payment")->wechat_build($params, 'web', 3, 'web');
						if( !is_array($wechat) ) 
						{
							$this->result(0,$wechat);
						}
					}
				}
			}
		} else {
			if($paytype == 2) {
				$alipay = model('payment')->alipay_build($params, 'iOS', 3, getHttpHost() . '/public/dist/order','web');
				if (empty($alipay)) {
					$this->result(0,'参数错误');
				}
			}
		}
		
		$this->assign(["orderid" => $orderid, "teamid" => $teamid, "paytype" => $paytype, "credit" => $credit, "wechat" => $wechat, "alipay" => $alipay, "params" => $params, "shopset" => $shopset]);
		return $this->fetch('groups/pay');
	}

	public function orders()
	{
		return $this->fetch('groups/orders/index');
	}

	public function getorderlist()
	{
		$list = array( );
		$mid = 0;
		$page = intval(input('page'));
		$psize = 5;
		$status = input('status');
		if( $status == 0 ) 
		{
			$tab_all = true;
			$condition = " o.mid= " . $mid . "  and o.deleted = 0 ";
		}
		else 
		{
			$condition = " o.mid= " . $mid . "  and o.deleted = 0 ";
			if( $status == 1 ) 
			{
				$tab0 = true;
				$condition .= " and o.status= 0 ";
			}
			else 
			{
				if( $status == 2 ) 
				{
					$tab1 = true;
					$condition .= " and o.status= 1 and (o.is_team = 0 or o.success = 1) ";
				}
				else 
				{
					if( $status == 3 ) 
					{
						$tab2 = true;
						$condition .= "and ( o.status = 2  or( o.status = 1 and o.isverify = 1)) ";
					}
					else 
					{
						if( $status == 4 ) 
						{
							$tab3 = true;
							$condition .= " and o.status= 3 ";
						}
					}
				}
			}
		}
		$orders = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('shop_groups_ladder l','l.id = o.ladder_id','left')->join('shop_groups_order_goods p','p.groups_order_id = o.id','left')->join('shop_groups_goods_option op','op.specs = o.specs','left')->where($condition)->order('o.createtime desc')->field('o.id,o.orderno,o.createtime,o.price,o.more_spec,o.is_ladder,o.freight,o.ladder_id,o.specs,o.creditmoney,o.goodsid,o.teamid,o.status,o.is_team,o.success,o.teamid,o.mid,g.title,g.thumb,g.units,g.goodsnum,g.groupsprice,g.singleprice,o.verifynum,o.verifytype,o.isverify,o.verifycode,g.thumb_url,l.ladder_price,l.ladder_num,p.option_name,op.title as optiontitle')->page($page,$psize)->select();
		$total = Db::name('shop_groups_order')->alias('o')->where($condition)->count();
		foreach( $orders as $key => $value ) 
		{
			$verifytotal = Db::name('shop_groups_verify')->where('orderid = ' . $value['id'] . ' and mid = ' . $mid . ' and verifycode = ' . $value["verifycode"])->count();
			if( !$verifytotal ) 
			{
				$verifytotal = 0;
			}
			$orders[$key]["vnum"] = $value["verifynum"] - intval($verifytotal);
			$orders[$key]["amount"] = ($value["price"] + $value["freight"]) - $value["creditmoney"];
			$statuscss = "text-cancel";
			switch( $value["status"] ) 
			{
				case "-1": $status = "已取消";
				break;
				case "0": $status = "待付款";
				$statuscss = "text-cancel";
				break;
				case "1": if( $value["is_team"] == 0 || $value["success"] == 1 ) 
				{
					$status = "待发货";
					$statuscss = "text-warning";
				}
				else 
				{
					if( $value["success"] == -1 ) 
					{
						$status = "已过期";
						$statuscss = "text-warning";
					}
					else 
					{
						$status = "已付款";
						$statuscss = "text-success";
					}
				}
				break;
				case "2": $status = "待收货";
				$statuscss = "text-danger";
				break;
				case "3": $status = "已完成";
				$statuscss = "text-success";
				break;
			}
			$orders[$key]["statusstr"] = $status;
			$orders[$key]["statuscss"] = $statuscss;
		}
		$orders = set_medias($orders, "thumb");
		show_json(1, array( "list" => $orders, "pagesize" => $psize, "total" => $total ));
	}

	public function team()
	{
		return $this->fetch('groups/team/index');
	}

	public function getteamlist()
	{
		$mid = 0;
		$page = input('page/d',1);
		$psize = 5;
		$success = intval(input('success'));
		$condition = " o.mid= " . $mid . " and o.is_team = 1 and o.paytime > 0 and o.deleted = 0 ";
		if( $success == 0 ) 
		{
			$tab0 = true;
			$condition .= " and o.success = " . $success;
		}
		else 
		{
			if( $success == 1 ) 
			{
				$tab1 = true;
				$condition .= " and o.success = " . $success;
			}
			else 
			{
				if( $success == -1 ) 
				{
					$tab2 = true;
					$condition .= " and o.success = " . $success;
				}
			}
		}
		$orders = Db::name('shop_groups_order')->alias('o')->join('shop_groups_goods g','g.id = o.goodsid','left')->join('shop_groups_ladder l','l.id = o.ladder_id','left')->join('shop_groups_order_goods p','p.groups_order_id = o.id','left')->where($condition)->field('o.*,g.title,g.price as gprice,g.groupsprice,l.ladder_price,l.ladder_num,p.option_name,g.thumb,g.thumb_url,g.units,g.goodsnum')->order('o.createtime')->page($page,$psize)->select();
		$total = Db::name('shop_groups_order')->alias('o')->where($condition)->count();
		foreach( $orders as $key => $order ) 
		{
			$orders[$key]["amount"] = ($order["price"] + $order["freight"]) - $order["creditmoney"];
			$goods = Db::name('shop_groups_goods')->where('id = ' . $order['goodsid'])->find();
			$alltuan = Db::name('shop_groups_order')->where('teamid = ' . $order['teamid'] . ' and success = 1')->select();
			$item = array( );
			foreach( $alltuan as $num => $all ) 
			{
				$item[$num] = $all["id"];
			}
			$orders[$key]["itemnum"] = count($item);
			$tuan_first_order = Db::name('shop_groups_order')->where('teamid = ' . $order["teamid"] . ' and paytime > 0 and heads = 1')->find();
			$hours = $tuan_first_order["endtime"];
			$time = time();
			$date = date("Y-m-d H:i:s", $tuan_first_order["starttime"]);
			$endtime = date("Y-m-d H:i:s", strtotime(" " . $date . " + " . $hours . " hour"));
			$date1 = date("Y-m-d H:i:s", $time);
			$orders[$key]["lasttime"] = strtotime($endtime) - strtotime($date1);
			$orders[$key]["starttime"] = date("Y-m-d H:i:s", $orders[$key]["starttime"]);
		}
		$orders = set_medias($orders, "thumb");
		show_json(1, array( "list" => $orders, "pagesize" => $psize, "total" => $total ));
	}

	public function orderfinish() 
	{
		$orderid = intval(input('id'));
		$mid = 0;
		$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->find();
		if( empty($order) ) 
		{
			show_json(0, "订单未找到");
		}
		if( $order["status"] != 2 ) 
		{
			show_json(0, "订单不能确认收货");
		}
		if( 0 < $order["refundstate"] && !empty($order["refundid"]) ) 
		{
			$change_refund = array( );
			$change_refund["refundstatus"] = -2;
			$change_refund["refundtime"] = time();
			Db::name('shop_groups_order_refund')->where('id',$order['refundid'])->update($change_refund);
		}
		Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "status" => 3, "finishtime" => time(), "refundstate" => 0 ));
		model("groups")->sendTeamMessage($orderid);
		show_json(1);
	}

	public function ordercancel() 
	{
		try 
		{
			$orderid = intval(input('id'));
			$mid = 0;
			$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->field('id,orderno,mid,status,credit,teamid,groupnum,creditmoney,price,freight,pay_type,discount,success')->find();
			$total = Db::name('shop_groups_order')->where('teamid = ' . $order["teamid"])->count();
			if( empty($order) ) 
			{
				show_json(0, "订单未找到");
			}
			if( $order["status"] != 0 ) 
			{
				show_json(0, "订单不能取消");
			}

			Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "status" => -1, "canceltime" => time() ));
			model("groups")->sendTeamMessage($orderid);
			show_json(1);
		}
		catch( Exception $e ) 
		{
			show_json(0, "操作失败");
		}
	}

	public function orderdelete() 
	{
		$orderid = intval(input('id'));
		$mid = 0;
		$order = Db::name('shop_groups_order')->where('id = ' . intval($orderid) . ' and mid = ' . $mid )->field('id,status')->find();
		if( empty($order) ) 
		{
			show_json(0, "订单未找到!");
		}
		if( $order["status"] != 3 && $order["status"] != -1 ) 
		{
			show_json(0, "无法删除");
		}
		Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "deleted" => 1 ));
		show_json(1);
	}

	public function rules()
	{
		$set = Db::name('shop_groups_set')->find();
		$this->assign(['set'=>$set]);
		return $this->fetch('groups/rules/index');
	}

}