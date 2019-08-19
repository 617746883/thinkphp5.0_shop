<?php
/**
 * apiv1 Index
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
class Index extends Base
{
	/**
	 * 商城首页
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function index()
	{
		$shopset = $this->shopset;

		$categorys = model('goods')->getFullCategory(true);
		$category = array();

		foreach ($categorys as $val) {
			$category[$val['id']] = $val;
		}
		unset($val);

		$banner = $sale = $notice = $groups = array();

		$banner = Db::name('shop_banner')->where('enabled = 1')->order('displayorder','desc')->field('displayorder,enabled',true)->select();
		$banner = set_medias($banner,'thumb');

		$sale = Db::name('shop_goods')->where('status = 1 and deleted = 0 and checked =0')->field('id,title,pcate,ccate,tcate,thumb')->limit(2)->select();
		$sale = set_medias($sale,'thumb');
		foreach ($sale as &$row) {
			$catename = '';
			if(!empty($row['pcate'])) {
				$catename = $category[$row['pcate']]['name'];
			}
			if(!empty($row['ccate'])) {
				$catename = $category[$row['ccate']]['name'];
			}
			if(!empty($row['tcate']) && intval($shopset['catlevel'])==3) {
				$catename = $category[$row['tcate']]['name'];
			}
			$row['catename'] = $catename;
		}
		unset($row);

		$notice = Db::name('shop_notice')->where('status = 1')->order('createtime,displayorder desc')->field('id,title')->limit(2)->select();

		$groups = Db::name('shop_goods_group')->where('enabled = 1 and merchid = 0')->order('displayorder','desc')->field('enabled,displayorder,merchid',true)->select();		
		foreach ($groups as &$row) {
			$goodsids = explode(',',$row['goodsids']);
			$goods = Db::name('shop_goods')->where('id','in',$goodsids)->field('id,title,pcate,ccate,tcate,thumb,thumb_url')->select();
			foreach ($goods as &$val) {
				$val['thumb'] = tomedia($val['thumb']);
				$thumbs = array_values(iunserializer($val['thumb_url']));

				if (empty($thumbs)) {
					$thumbs = array($val['thumb']);
				}
				$thumbs = set_medias($thumbs);
				$val['thumbs'] = $thumbs;

				$catename = '';
				if(!empty($val['pcate'])) {
					$catename = $category[$val['pcate']]['name'];
				}
				if(!empty($val['ccate'])) {
					$catename = $category[$val['ccate']]['name'];
				}
				if(!empty($val['tcate']) && intval($shopset['catlevel'])==3) {
					$catename = $category[$val['tcate']]['name'];
				}
				$val['catename'] = $catename;
				unset($val['thumb_url']);
			}		
			unset($val);
			$row['goods'] = $goods;
		}
		unset($row);
		$this->result(1,'success',array('banner'=>$banner,'sale'=>$sale,'notice'=>$notice,'groups'=>$groups));
	}

	/**
	 * 店铺首页
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function merch()
	{
		$banner = $sale = $category = $recom = array();
		$shopset = $this->shopset;
		$imgs = tomedia($shopset['shop']['img']);
		$arr = array('thumb'=>$imgs,'outlink'=>array('id'=>0,'type'=>0,'url'=>''));
		$banner[] = $arr;

		$timegoods = Db::name('shop_goods')->where('status = 1 and deleted = 0 and merchid = 0 and istime = 1 and isrecommand = 1')->field('id,title,thumb,marketprice')->order('displayorder','desc')->limit(2)->select();
		$timegoods = set_medias($timegoods,'thumb');
		$time = array('title'=>'限时购','subtitle'=>'每日精品商品限时抢购','goods'=>$timegoods);

		$groupsgoods = Db::name('shop_goods')->where('status = 1 and deleted = 0 and merchid = 0 and ishot = 1 and isrecommand = 1')->field('id,title,thumb,marketprice')->order('displayorder','desc')->limit(2)->select();
		$groupsgoods = set_medias($groupsgoods,'thumb');
		$groups = array('title'=>'热卖','subtitle'=>'每日为您精选热卖','goods'=>$groupsgoods);
		
		$discountgoods = Db::name('shop_goods')->where('status = 1 and deleted = 0 and merchid = 0 and isdiscount = 1 and isrecommand = 1')->field('id,title,thumb,marketprice')->order('displayorder','desc')->limit(2)->select();
		$discountgoods = set_medias($discountgoods,'thumb');
		$discount = array('title'=>'福利社','subtitle'=>'每日精品商品促销价','goods'=>$discountgoods);
		$sale = array($time,$groups,$discount);

		$category = Db::name('shop_goods_category')->where('isrecommand = 1 and enabled	= 1 and ishome = 1 and advimg<>""')->field('id,name,description,advimg')->order('displayorder','desc')->limit(8)->select();
		$category = set_medias($category,'advimg');

		$num = input('num/d',6);
		$shopset = model('common')->getSysset('shop');
		$condition = 't1.deleted = 0 and t1.status = 1 and t1.isrecommand = 1 and t1.merchid = 0 and t1.istime = 0 and t1.ishot = 0 and t1.isdiscount = 0';

		$list = Db::query("SELECT t1.id,t1.title,t1.subtitle,t1.thumb,t1.marketprice,t1.productprice,t1.minprice,t1.maxprice,t1.isdiscount,t1.isdiscount_time,t1.sales,t1.salesreal,t1.total,t1.description,t1.`type`,t1.ispresell,t1.merchid,t1.labelname,t1.quality,t1.seven,t1.repair FROM " . tablename('shop_goods') . " AS t1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM " . tablename('shop_goods') . ")-(SELECT MIN(id) FROM " . tablename('shop_goods') . "))) AS id) AS t2 WHERE t1.id >= t2.id AND " . $condition . " ORDER BY t1.id desc LIMIT " . $num
		);
		foreach ($list as $lk => $lv ) {
			if ($lv['type'] == 3) { 
				$vData = Db::name('shop_virtual_type')->where('id',intval($lv['virtual']))->find();

				if ($vData['recycled'] == 1) {
					array_splice($list, $lk, 1);
				}
			}
		}
		foreach ($list as &$val) {
			$val['labelname'] = iunserializer($val['labelname']);
			if(empty($val['labelname']))
			{
				$val['labelname'] = array();
			}
			$merchinfo = array('id'=>0,'logo'=>$shopset['logo'],'merchname'=>$shopset['name']);
			if(!empty($val['merchid']))
			{
				$merchinfo = Db::name('shop_store')->where('id',$val['merchid'])->field('id,logo,merchname')->find();
			}
			$merchinfo['logo'] = tomedia($merchinfo['logo']);
			$val['merchinfo'] = $merchinfo;
			$quality = $seven = $repair = '';
			if(!empty($val['quality'])) {
				$quality = '正品保证';
				array_unshift($val['labelname'], $quality);
			}
			if(!empty($val['seven'])) {
				$seven = '7天无理由退换';
				array_unshift($val['labelname'], $seven);
			}
			if(!empty($val['repair'])) {
				$repair = '报修';
				array_unshift($val['labelname'], $repair);
			}
			array_unshift($val['labelname'], $merchinfo['merchname']);
			unset($val['quality'],$val['seven'],$val['repair']);
		}
		unset($val);
		$recom = set_medias($list, 'thumb');
		$this->result(1,'success',array('banner'=>$banner,'sale'=>$sale,'category'=>$category,'recom'=>$recom));
	}

	/**
	 * 公告列表
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function notice()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$list = Db::name('shop_notice')->where('status = 1 and merchid = 0')->order('createtime,displayorder desc')->field('id,title,thumb,cate as catename')->page($page,$pagesize)->select();
		$list = set_medias($list,'thumb');
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	public function test()
	{
		return model('notice')->sendOrderMessage(6);
	}


}