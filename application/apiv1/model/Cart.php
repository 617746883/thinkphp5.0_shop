<?php
namespace app\apiv1\model;
use think\Db;
class Cart extends \think\Model
{
	/**
     * @param int $selected|是否被用户勾选中的 0 为全部 1为选中  一般没有查询不选中的商品情况
     * 获取用户的购物车列表
     * @return list|array
     */
    public static function getCartList($mid, $selected = 0)
    {
        $condition = ' mid = ' . $mid . ' and deleted = 0 ';
        if($selected != 0){
        	$condition .= ' and selected = 1 ';
        }
        $cartList = Db::name('shop_member_cart')
        	->where($condition)
        	->field('createtime,selectedadd,deleted',true)
        	->order('createtime', 'desc')
        	->select();  //获取购物车商品
    	foreach ($cartList as &$row) {
    		$goods = self::getCartGoods($row['goodsid'], $row['optionid']);
    		$row['goods'] = $goods;
    	}
    	unset($row);
        $list = self::checkCartList($cartList);
        
        return $list;
    }

    public function calculate($mid)
    {
        $condition = ' f.mid=' . $mid . ' and f.deleted=0';
        $list = Db::name('shop_member_cart')
            ->alias('f')
            ->join('shop_goods g','f.goodsid = g.id','left')
            ->join('shop_goods_option o','f.optionid = o.id','left')
            ->where($condition)
            ->field('f.id,f.total,f.optionid,o.specs,f.merchid,f.selected,g.id as goodsid,g.total as stock,g.status as goodsstatus,g.deleted as goodsdeleted,g.preselltimeend,g.presellprice as gpprice,g.hasoption,g.presellprice,g.ispresell, g.maxbuy,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,g.productprice,g.minbuy,g.maxbuy,g.unit,g.type,g.intervalfloor,g.intervalprice,o.stock as optionstock,o.title as optiontitle,o.presellprice,o.stock as optionstock')
            ->order('f.createtime','desc')
            ->select();
        $list = self::checkCartList($list);
        $totalcart = count($list);
        $totalgoods = array_sum(array_map(function($val){return $val['total'];}, $list));//购物车购买的商品总数
        $totalprice = self::getCartPrice($list);
        return array('totalcart'=>$totalcart,'totalgoods'=>$totalgoods,'totalprice'=>$totalprice);
    }

    /**
     * 过滤掉无效的购物车商品
     * @param $cartList
     */
    public static function checkCartList($cartList)
    {
        foreach($cartList as $cartKey => $cart) {
            //商品不存在
            if(empty($cart['goodsid'])) {
                Db::name('shop_member_cart')->where('id',$cart['id'])->delete();
                unset($cartList[$cartKey]);
                continue;
            }
            //商品已下架或库存不足
            if(($cart['goodsstatus'] != 1) || ($cart['goodsdeleted'] != 0) || (empty($cart['optionstock']) && $cart['stock'] < $cart['total']) || (!empty($cart['optionstock']) && $cart['optionstock'] < $cart['total'])) {
                Db::name('shop_member_cart')->where('id',$cart['id'])->setField('selected',0);
                continue;
            }           
        }
        return $cartList;
    }

    /**
     * 计算购物车选中商品总价
     * @param $cartList
     */
    public static function getCartPrice($cartList = null)
    {
        if(empty($cartList))
        {
            return 0;
        }

    	$total_price = 0;
        foreach($cartList as $cart){
        	if($cart['selected'] == 1) {
        		$total_price += $cart['total'] * $cart['marketprice']; 
                continue;
        	}                   
        }

        return round($total_price, 2);
    }

    /**
     * 获取购物车商品详细信息
     * @param $cartList
     */
    public static function getCartGoods($goodsid, $optionid)
    {
    	$goods = Db::name('shop_goods')
			->where('id',$goodsid)
			->field("id,title,marketprice,total,weight,thumb,status,type,deleted")
			->find();
		if(!empty($goods)) {
			$goods['thumb'] = tomedia($goods['thumb']);
			$goodsoption = array();
    		if(!empty($optionid)) {
    			$option = Db::name('shop_goods_option')
    				->where('goodsid',$goodsid)
    				->where('id',$optionid)
    				->find();
    			if(!empty($option)) {
    				$goods['marketprice'] = $option['marketprice'];
    				$goods['total'] = $option['stock'];
    				$goods['weight'] = $option['weight'];
    				$goodsoption = array('id'=>$option['id'],'title'=>$option['title']);
    			} else {
                    $goods['status'] = 0;
                }
    		}
    		$goods['option'] = $goodsoption;
		}
		return $goods;
    }
}