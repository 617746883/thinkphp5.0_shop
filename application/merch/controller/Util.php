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
class Util extends Base
{
	public function selecturl()
    {
    	$allUrls = array(
			array(
				'name' => '商城页面',
				'list' => array(
					array('name' => '商城首页', 'url' => 'shop'),
					array('name' => '全部商品', 'url' => 'goodslist'),
					array('name' => '公告页面', 'url' => 'shopnotice'),
					array('name' => '购物车', 'url' => 'shopcart')
					)
				),
			array(
				'name' => '商品属性',
				'list' => array(
					array('name' => '推荐商品', 'url' => 'goodslist?isrecommand=1'),
					array('name' => '新品上市', 'url' => 'goodslist?isnew=1'),
					array('name' => '热卖商品', 'url' => 'goodslist?ishot=1'),
					array('name' => '促销商品', 'url' => 'goodslist?isdiscount=1'),
					array('name' => '卖家包邮', 'url' => 'goodslist?issendfree=1'),
					array('name' => '限时抢购', 'url' => 'goodslist?istime=1')
					)
				),
			array(
				'name' => '会员中心',
				'list' => array(
					0  => array('name' => '会员中心', 'url' => 'member'),
					1  => array('name' => '我的订单(全部)', 'url' => 'order'),
					2  => array('name' => '待付款订单', 'url' => 'order?status=0'),
					3  => array('name' => '待发货订单', 'url' => 'order?status=0'),
					4  => array('name' => '待收货订单', 'url' => 'order?status=0'),
					5  => array('name' => '退换货订单', 'url' => 'order?status=0'),
					6  => array('name' => '已完成订单', 'url' => 'order?status=0'),
					7  => array('name' => '我的收藏', 'url' => 'goodsfavorite'),
					8  => array('name' => '我的足迹', 'url' => 'memberhistory')
					)
				)
			);

    	$set = Db::name('shop_groups_set')->limit(1)->find();    		
        if($set['opengroups'] != 1) {
        	$allUrls[] = array(
				'name' => '团购',
				'list' => array(
					array('name' => '拼团首页', 'url' => 'groups'),
					array('name' => '活动列表', 'url' => 'groupslist'),
					array('name' => '我的订单', 'url' => 'groupsorders'),
					array('name' => '我的团', 'url' => 'groupsteam')
					)
				);
        }   	

    	$syscate = model('common')->getSysset('category');
		if (0 < $syscate['level']) {
			$categorys = Db::name('shop_goods_category')->where('enabled',1)->field('id,name,parentid')->select();
		}
		$controller = input('controller');
    	$this->assign(['allUrls'=>$allUrls,'categorys'=>$categorys,'controller'=>$controller]);
    	echo $this->fetch('util/selecturl');
    }

    public function selecturlquery()
    {
    	$type = trim(input('type'));
		$kw = trim(input('kw'));
		$full = intval(input('full'));
		$platform = trim(input('platform'));
		$list = array();
		if (!(empty($kw)) && !(empty($type))) {
			if ($type == 'good') {
				$list = Db::name('shop_goods')->where('status',1)->where('isgroups',0)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id,title,productprice,marketprice,thumb,sales,unit,minprice')->select();
				$list = set_medias($list, 'thumb');
			} else if ($type == 'groups') {
				$list = Db::name('shop_goods')->where('status',1)->where('isgroups',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id,title,productprice,marketprice,thumb,sales,unit,minprice')->select();
			} else if ($type == 'creditshop') {
				$list = Db::name('shop_creditshop_goods')->where('status',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id, thumb, title, price, credit, money')->select();
			} else if ($type == 'article') {
				$list = Db::name('shop_article')->where('article_state',1)->where('article_title','like','%' . $kw . '%')->field('id, resp_img, article_title')->select();
			} else if ($type == 'housing') {
				$list = Db::name('community_housing')->where('status',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id, thumb, title')->select();
			} else if ($type == 'lifestore') {
				$list = Db::name('citywide_life_store')->where('status',1)->where('deleted',0)->where('storename','like','%' . $kw . '%')->field('id, storename, logo')->select();
			}
		}
		$this->assign(['type'=>$type,'kw'=>$kw,'full'=>$full,'platform'=>$platform,'list'=>$list]);
		echo $this->fetch('util/selecturl_tpl');
    }

    public function map()
    {
    	return $this->fetch('util/area/map');
    }

    public function express()
    {
    	$express = trim(input('express'));
		$expresssn = trim(input('expresssn'));
		$result = model('util')->getExpressList($express, $expresssn);
		$this->assign(['list'=>$result['list']]);
		echo $this->fetch('util/express');
    }

}