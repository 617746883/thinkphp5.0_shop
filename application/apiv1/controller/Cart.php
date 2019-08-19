<?php
/**
 * apiv1 购物车
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Cart extends Base
{
	/**
	 * 购物车列表
	 * @param $mid
	 * @return  [array]    $list  []
	 **/
	public function getlist()
	{
		$mid = $this->getMemberId();
		$shopset = $this->shopset;
		$list = array();
		$totalprice = 0;
		$ischeckall = true;
		$level = model('member')->getLevel($mid);
		$condition = ' f.mid=' . $mid . ' and f.deleted=0';
		$list = Db::name('shop_member_cart')
			->alias('f')
			->join('shop_goods g','f.goodsid = g.id','left')
			->join('shop_goods_option o','f.optionid = o.id','left')
			->where($condition)
			->field('f.id,f.total,f.optionid,ifnull(o.specs,"") as specs,f.merchid,f.selected,g.id as goodsid,g.total as stock,g.status as goodsstatus,g.deleted as goodsdeleted,g.preselltimeend,g.presellprice as gpprice,g.hasoption,g.presellprice,g.ispresell, g.maxbuy,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,g.productprice,g.minbuy,g.maxbuy,g.unit,g.type,g.intervalfloor,g.intervalprice,ifnull(o.title,"") as optiontitle,ifnull(o.presellprice,0.00) as optionpresellprice,ifnull(o.stock,0) as optionstock')
			->order('f.createtime','desc')
			->select();
		$list = model('cart')->checkCartList($list);
		$totalcart = count($list);
        $totalgoods = array_sum(array_map(function($val){return $val['total'];}, $list));//购物车购买的商品总数
        $totalprice = model('cart')->getCartPrice($list);

		$arr = array();
		foreach ($list as &$val) {
			$val['status'] = 1;
			if($val['goodsstatus'] != 1 || $val['goodsdeleted'] != 0 || (empty($val['optionstock']) && $val['stock'] < $val['total']) || (!empty($val['optionstock']) && $val['optionstock'] < $val['total'])) {
                $val['status'] = 0;
            }  
			$val['thumb'] = tomedia($val['thumb']);
			$arr[$val['merchid']][] = $val;
		}
		unset($val);
		$cartlist = array();
		foreach ($arr as $k => $v){
            if(empty($k))
            {
            	$merch = array('id'=>0,'merchname'=>$shopset['shop']['name'],'logo'=>$shopset['shop']['logo']);
            }
            else
            {
            	$merch = Db::name('shop_merch')->where('id',$k)->field('id,logo,merchname')->find();
            }
            $merch['logo'] = tomedia($merch['logo']);
            $merch['carts'] = $v;
            $cartlist[] = $merch;
        }

		$this->result(1,'success',array('list'=>$cartlist ? $cartlist : array(),'totalcart'=>$totalcart,'totalgoods'=>$totalgoods,'totalprice'=>$totalprice));
	}

	/**
	 * 购物车选中状态
	 * @param $id [商品id]
	 * @param $mid
	 * @return  [array]    $data  []
	 **/
	public function select()
	{
		$id = input('id/d');
		$select = input('select/d') ? 1 : 0;
		$mid = $this->getMemberId();
		$data = Db::name('shop_member_cart')->where('id',$id)->where('mid',$mid)->field('id,goodsid,optionid,total')->find();
		if (empty($data)) {
			$this->result(0,'无购物车记录');
		}
		Db::startTrans();
		try{
		    if (!empty($id)) {
				Db::name('shop_member_cart')->where('id',$id)->setField('selected',$select);
			}
			else {
				Db::name('shop_member_cart')->where('mid',$mid)->setField('selected',$select);
			}
		    Db::commit();    
		} catch (\Exception $e) {
		    Db::rollback();
		    $this->result(0,'操作失败');
		}		
		$cartinfo = model('cart')->calculate($mid);
		$this->result(1,'success',$cartinfo);
	}

	/**
	 * 更改购物车数量
	 * @param $mid
	 * @param $id [商品id]
	 * @param $optionid [商品规格id]
	 * @return  [array]    $data  [返回成功失败]
	 **/
	public function update()
	{
		$id = input('id/d');
		$goodstotal = input('total/d');
		$optionid = input('optionid/d');
		$mid = $this->getMemberId();
		empty($goodstotal) && ($goodstotal = 1);
		$condition = ' id = ' . $id . ' and mid = ' . $mid;
		if(!empty($optionid))
		{
			$condition .= ' and optionid = ' . $optionid;
		}
		$data = Db::name('shop_member_cart')->where($condition)->field('id,goodsid,optionid,total')->find();

		if (empty($data)) {
			$this->result(0,'无购物车记录');
		}
		
		$goods = Db::name('shop_goods')->where('id',$data['goodsid'])->where('status',1)->where('deleted',0)->field('id,maxbuy,minbuy,total,unit,hasoption')->find();
		
		if (empty($goods)) {
			$this->result(0,'商品未找到');
		}
		$goods['unit'] = $goods['unit'] ? $goods['unit'] : '件';
		if(!empty($goods['hasoption']) && empty($optionid)) {
			$this->result(0,'请选择规格');
		}
		if(!empty($optionid)) {
			$option = Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goods['id'])->find();
			if(empty($option))
			{
				$this->result(0,'商品规格不存在');
			}
			if(($option['stock'] !== -1) && ($option['stock'] < $goodstotal)) {
				$this->result(0,'商品库存不足');
			}
		} else if(($goods['total'] !== -1) && ($goods['total'] < $goodstotal)) {
			$this->result(0,'商品库存不足');
		}
		if(!empty($goods['maxbuy']) && ($goods['maxbuy'] < $goodstotal)) {
			$this->result(0,'最多购买'.$goods['maxbuy'].$goods['unit']);
		}
		if(!empty($goods['minbuy']) && ($goods['minbuy'] > $goodstotal)) {
			$this->result(0,'最少购买'.$goods['minbuy'].$goods['unit']);
		}

		Db::startTrans();
		try{
		    Db::name('shop_member_cart')->where('id',$id)->where('mid',$mid)->update(array('total' => $goodstotal));
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0,'操作失败');
		}
		
		$cartinfo = model('cart')->calculate($mid);
		$this->result(1,'success',$cartinfo);
	}

	/**
	 * 商品加入购物车
	 * @param $mid
	 * @param $id [商品id]
	 * @param $optionid [商品规格id]
	 * @return  [array]    $data  [返回成功失败]
	 **/
	public function add()
	{
		$id = input('id/d');
		$total = input('total/d');
		($total <= 0) && ($total = 1);
		$mid = $this->getMemberId();
		$optionid = input('optionid/d',0);
		$goods = Db::name('shop_goods')->where('id',$id)->where('status',1)->where('deleted',0)->field('id,marketprice,isverify,`type`,merchid,cannotrefund,maxbuy,minbuy,total,unit,hasoption')->find();
		if (empty($goods)) {
			$this->result(0,'商品未找到');
		}

		if (($goods['isverify'] == 2) || ($goods['type'] == 2) || ($goods['type'] == 3) || !empty($goods['cannotrefund'])) {
			$this->result(0,'此商品不可加入购物车,请直接点击立刻购买');
		}
		if(!empty($goods['hasoption']) && empty($optionid)) {
			$this->result(0,'请选择规格');
		}
		if(!empty($optionid)) {
			$option = Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$id)->find();
			if(empty($option)) {
				$this->result(0,'商品规格不存在');
			}
			if(($option['stock'] !== -1) && ($option['stock'] < $total)) {
				$this->result(0,'商品库存不足');
			}
		} else if(($goods['total'] !== -1) && ($goods['total'] < $total)) {
			$this->result(0,'商品库存不足');
		}
		$goods['unit'] = $goods['unit'] ? $goods['unit'] : '件';
		if(!empty($goods['maxbuy']) && ($goods['maxbuy'] < $total)) {
			$this->result(0,'最多购买'.$goods['maxbuy'].$goods['unit']);
		}
		if(!empty($goods['minbuy']) && ($goods['minbuy'] > $total)) {
			$this->result(0,'最少购买'.$goods['minbuy'].$goods['unit']);
		}

		Db::startTrans();
		try{
		    $data = Db::name('shop_member_cart')->where('goodsid',$id)->where('mid',$mid)->where('optionid',$optionid)->where('deleted',0)->field('id,total')->find();
			if (empty($data)) {
				$data = array('merchid' => $goods['merchid'], 'mid' => $mid, 'goodsid' => $id, 'optionid' => $optionid, 'marketprice' => $goods['marketprice'], 'total' => $total, 'selected' => 1, 'createtime' => time());
				Db::name('shop_member_cart')->insertGetId($data);
			}
			else {
				$data['total'] += $total;
				$data['selected'] += 1;
				Db::name('shop_member_cart')->where('id',$data['id'])->update($data);
			}
		    Db::commit();    
		} catch (\Exception $e) {
		    Db::rollback();
		    $this->result(0,'操作失败');
		}		

		$cartinfo = model('cart')->calculate($mid);
		$this->result(1,'success',$cartinfo);
	}

	/**
	 * 移除购物车
	 * @param $ids [购物车id]
	 * @return  [array]    $data  [返回成功失败]
	 **/
	public function remove()
	{
		$ids = input('ids/s');        
        $ids = array_filter(explode(',', $ids));
		$mid = $this->getMemberId();
		if (empty($ids) || !is_array($ids)) {
			$this->result(0,'参数错误');
		}
		Db::startTrans();
		try{
		    Db::name('shop_member_cart')->where('mid',$mid)->where('id','in',$ids)->setField('deleted',1);
		    Db::commit();    
		} catch (\Exception $e) {
		    Db::rollback();
		    $this->result(0,'操作失败');
		}
		$cartinfo = model('cart')->calculate($mid);
		$this->result(1,'success',$cartinfo);
	}

}