<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Groups extends \think\Model
{
	public static function payResult($orderno, $type, $app = false) 
	{
		$log = Db::name('shop_core_paylog')->where('module','groups')->where('tid',$orderno)->find();
		$order = Db::name('shop_groups_order')->where('orderno',$orderno)->find();
		if( 0 < $order["status"] || 0 < $log["status"] ) {
			return true;
		}
		$mid = $order["mid"];
		$order_goods = Db::name('shop_groups_goods')->where('id',$order['goodsid'])->find();
		$shopset = model('common')->getSysset('shop');
		if( 0 < $order["credit"] ) 
		{
			$result = model("member")->setCredit($mid, "credit1", 0 - $order["credit"], array( $mid, $shopset["shop"]["name"] . "消费" . $order["credit"] . "积分" ));
			if( is_error($result) ) 
			{
				return $result["message"];
			}
		}
		$record = array( );
		$record["status"] = "1";
		$record["type"] = $type;
		$params = array( ":teamid" => $order["teamid"], ":uniacid" => $uniacid, ":success" => 0, ":status" => 1 );
		Db::name('shop_groups_order')->where('id',$order['id'])->update(array( "pay_type" => $type, "status" => 1, "paytime" => time(), "starttime" => time(), "apppay" => ($app ? 1 : 0) ));
		Db::name('shop_core_paylog')->where('id',$log['id'])->update(array( "status" => 1 ));
		model('notice')->sendGroupsNotice($order["id"]);
		if( !empty($order["is_team"]) ) 
		{
			$total = Db::name('shop_groups_order')->where('status',1)->where('teamid',$order["teamid"])->where('success',0)->count();
			if( $order["groupnum"] == $total ) 
			{
				Db::name('shop_groups_order')->where('teamid',$order["teamid"])->where('status',1)->update(array( "success" => 1 ));
				Db::name('shop_groups_order')->where('teamid',$order["teamid"])->where('status',0)->update(array( "success" => -1, "status" => -1, "canceltime" => time() ));
				model('notice')->sendGroupsNotice($order["id"]);
			}
		}
		if( $order_goods["more_spec"] == "1" ) {
			$order_goods_S = Db::name('shop_groups_order_goods')->where('groups_order_id',$order["id"])->find();
			$goods_option = Db::name('shop_groups_goods_option')->where('goods_option_id',$order_goods_S["groups_goods_option_id"])->find();
			Db::name('shop_groups_goods_option')->where('id',$goods_option["id"])->update(array( "stock" => $goods_option["stock"] - 1 ));
			$stock = intval($order_goods["stock"] - 1);
			$sales = intval($order_goods["sales"]) + 1;
		}
		else 
		{
			$stock = intval($order_goods["stock"] - 1);
			$sales = intval($order_goods["sales"]) + 1;
		}
		Db::name('shop_groups_goods')->where('id',$order_goods["id"])->update(array( "stock" => $stock, "sales" => $sales ));
		return true;
	}

	public static function getTotals() 
	{
		$totals["all"] = Db::name('shop_groups_order')->where('isverify',0)->count();
		$totals["status1"] = Db::name('shop_groups_order')->where('isverify = 0 and status = 1 and (success = 1 or is_team = 0)')->count();
		$totals["status2"] = Db::name('shop_groups_order')->where('isverify = 0 and status=2 ')->count();
		$totals["status3"] = Db::name('shop_groups_order')->where('isverify = 0 and status = 0')->count();
		$totals["status4"] = Db::name('shop_groups_order')->where('isverify = 0 and status = 3')->count();
		$totals["status5"] = Db::name('shop_groups_order')->where('isverify = 0 and status = -1 ')->count();
		$totals["team1"] = Db::name('shop_groups_order')->where('heads = 1 and paytime > 0 and is_team = 1 and success = 1')->count();
		$totals["team2"] = Db::name('shop_groups_order')->where('heads = 1 and paytime > 0 and is_team = 1 and success = 0')->count();
		$totals["team3"] = Db::name('shop_groups_order')->where('heads = 1 and paytime > 0 and is_team = 1 and success = -1')->count();
		$totals["allteam"] = Db::name('shop_groups_order')->where('heads = 1 and paytime > 0 and is_team = 1')->count();
		$totals["refund1"] = Db::name('shop_groups_order_refund')->alias('ore')->join('shop_groups_order o','o.id = ore.orderid','left')->join('shop_groups_goods g','g.id = o.goodsid','right')->join('member m','m.openid=o.openid','right')->join('shop_member_address a','a.id=ore.refundaddressid','right')->join('shop_groups_goods_category c','c.id = g.category','right')->where('o.refundstate > 0 and o.refundid != 0 and ore.refundstatus >= 0')->count();
		$totals["refund2"] = 
		Db::name('shop_groups_order_refund')->alias('ore')->join('shop_groups_order o','o.id = ore.orderid','left')->join('shop_groups_goods g','g.id = o.goodsid','right')->join('member m','m.openid=o.openid','right')->join('shop_member_address a','a.id=ore.refundaddressid','right')->join('shop_groups_goods_category c','c.id = g.category','right')->where('o.refundtime != 0 or ore.refundstatus < 0')->count();
		$totals["verify1"] = Db::name('shop_groups_order')->where('isverify = 1 and status =  1')->count();
		$totals["verify2"] = Db::name('shop_groups_order')->where('isverify = 1 and status = 3')->count();
		$totals["verify3"] = Db::name('shop_groups_order')->where('isverify = 1 and status <= 0')->count();
		return $totals;
	}

	public static function del_spec($goods_id) 
	{
		$spec = Db::name('shop_groups_goods_option')->where('groups_goods_id',$goods_id)->select();
		if( !empty($spec) ) 
		{
			return Db::name('shop_groups_goods_option')->where('groups_goods_id',$goods_id)->delete();
		}
		return true;
	}

	public static function dispose_spec($spec, $groups_goods_id, $goods_id = 0) 
	{
		foreach( $spec as $k => $v ) 
		{
			$specs = explode("_", $v["specs"]);
			asort($specs);
			$data = array( "goods_option_id" => $v["goods_option_id"], "title" => $v["name"], "marketprice" => $v["marketprice"], "single_price" => $v["single_price"], "price" => $v["price"], "stock" => $v["stock"], "specs" => implode("_", $specs), "groups_goods_id" => $groups_goods_id );
			if( $goods_id ) 
			{
				$data["goodsid"] = $goods_id;
			}
			if( empty($v["id"]) ) 
			{
				Db::name('shop_groups_goods_option')->insert($data);
			}
			else 
			{
				Db::name('shop_groups_goods_option')->where('id',$v["id"])->update($data);
			}
		}
		return true;
	}

}