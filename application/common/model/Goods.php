<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Goods extends \think\Model
{
	/**
     * 获取商品规格
     * @param type $goodsid
     * @param type $optionid
     * @return type
     */
	public function getOption($goodsid = 0, $optionid = 0)
	{
		return Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goodsid)->find();
	}

	/**
     * 获取商品规格图片
     * @param type $specs
     * @return type
     */
	public function getSpecThumb($specs)
	{
		$thumb = '';
		$cartspecs = explode('_', $specs);
		$specid = $cartspecs[0];

		if (!(empty($specid))) {
			$spec = Db::name('shop_goods_spec_item')->where('id',$specid)->field('thumb')->find();

			if (!(empty($spec))) {
				if (!(empty($spec['thumb']))) {
					$thumb = $spec['thumb'];
				}
			}
		}
		return $thumb;
	}

	/**
     * 获取商品规格图片
     * @param type $specs
     * @return type
     */
	public function getOptionThumb($goodsid = 0, $optionid = 0)
	{
		$thumb = '';
		$option = self::getOption($goodsid, $optionid);

		if (!(empty($option))) {
			$specs = $option['specs'];
			$thumb = self::getSpecThumb($specs);
		}

		return $thumb;
	}

	public function wholesaleprice($goods)
	{
		$goods2 = array();

		foreach ($goods as $good ) {
			if ($good['type'] == 4) {
				if (empty($goods2[$good['goodsid']])) {
					$intervalprices = array();

					if (0 < $good['intervalfloor']) {
						$intervalprices[] = array('intervalnum' => intval($good['intervalnum1']), 'intervalprice' => floatval($good['intervalprice1']));
					}

					if (1 < $good['intervalfloor']) {
						$intervalprices[] = array('intervalnum' => intval($good['intervalnum2']), 'intervalprice' => floatval($good['intervalprice2']));
					}

					if (2 < $good['intervalfloor']) {
						$intervalprices[] = array('intervalnum' => intval($good['intervalnum3']), 'intervalprice' => floatval($good['intervalprice3']));
					}

					$goods2[$good['goodsid']] = array('goodsid' => $good['goodsid'], 'total' => $good['total'], 'intervalfloor' => $good['intervalfloor'], 'intervalprice' => $intervalprices);
				}
				 else {
					$goods2[$good['goodsid']]['total'] += $good['total'];
				}
			}

		}

		foreach ($goods2 as $good2 ) {
			$intervalprices2 = iunserializer($good2['intervalprice']);
			$price = 0;

			foreach ($intervalprices2 as $intervalprice ) {
				if ($intervalprice['intervalnum'] <= $good2['total']) {
					$price = $intervalprice['intervalprice'];
				}
			}

			foreach ($goods as &$good ) {
				if ($good['goodsid'] == $good2['goodsid']) {
					$good['wholesaleprice'] = $price;
					$good['goodsalltotal'] = $good2['total'];
				}
			}

			unset($good);
		}

		return $goods;
	}

	/**
     * 获取商品规格的价格
     * @param type $goodsid
     * @param type $optionid
     * @return type
     */
	public function getOptionPirce($goodsid = 0, $optionid = 0)
	{
		return Db::name('shop_goods_option')->where('id',$optionid)->where('goodsid',$goodsid)->value('marketprice');
	}

	public static function getOneMinPrice($goods)
	{
		$goods = array($goods);
		$res = self::getAllMinPrice($goods);
		return $res[0];
	}

	public static function getAllMinPrice($goods)
	{
		if (is_array($goods)) {
			foreach ($goods as &$value ) {
				$minprice = $value['minprice'];
				$maxprice = $value['maxprice'];
				if ($value['isdiscount'] && (time() <= $value['isdiscount_time'])) {
					$value['oldmaxprice'] = $maxprice;
					$isdiscount_discounts = json_decode($value['isdiscount_discounts'], true);
					$prices = array();
					if (!(isset($isdiscount_discounts['type'])) || empty($isdiscount_discounts['type'])) {
						$prices_array = model('order')->getGoodsDiscountPrice($value, array(), 1);
						$prices[] = $prices_array['price'];
					}
					 else {
						$goods_discounts = model('order')->getGoodsDiscounts($value, $isdiscount_discounts, 0);
						$prices = $goods_discounts['prices'];
					}

					$minprice = min($prices);
					$maxprice = max($prices);
				}


				$value['minprice'] = $minprice;
				$value['maxprice'] = $maxprice;
			}

			unset($value);
		}
		 else {
			$goods = array();
		}

		return $goods;
	}

	/**
     *
     * 是否已经有重复购买的商品
     * @param $goods
     * @return bool
     */
	public static function canBuyAgain($goods)
	{
		$condition = '';
		$id = $goods['id'];

		if (isset($goods['goodsid'])) {
			$id = $goods['goodsid'];
		}

		if (empty($goods['buyagain_islong'])) {
			$condition = ' AND canbuyagain = 1';
		}

		$order_goods = Db::name('shop_order_goods')->where('goodsid',$id)->where($condition)->field('id,orderid')->select();

		if (empty($order_goods)) {
			return false;
		}
		$order = Db::name('shop_order')->where('id','in',implode(',', array_keys($order_goods)))->where('status','egt',(empty($goods['buyagain_condition']) ? '1' : '3'))->count();
		return !(empty($order));
	}

	/**
     * 获取商品分类
     * @param $level
     * @param $merchid
     * @return $list
     */
	public function getCategory($level, $merchid = 0)
	{
		$level = intval($level);
		$category = model('shop')->getCategory();
		$category_parent = array();
		$category_children = array();
		$category_grandchildren = array();

		if (0 < $merchid) {
			$merch_data = model('common')->getPluginset('merch');
			if ($merch_data['is_openmerch']) {
				$is_openmerch = 1;
			}
			else {
				$is_openmerch = 0;
			}

			if ($is_openmerch) {
				$merch_category = model('merch')->getSet('merch_category', $merchid);

				if (!empty($merch_category)) {
					if (!empty($category['parent'])) {
						foreach ($category['parent'] as $key => $value) {
							if (array_key_exists($value['id'], $merch_category)) {
								$category['parent'][$key]['enabled'] = $merch_category[$value['id']];
							}
						}
					}

					if (!empty($category['children'])) {
						foreach ($category['children'] as $key => $value) {
							if (!empty($value)) {
								foreach ($value as $k => $v) {
									if (array_key_exists($v['id'], $merch_category)) {
										$category['children'][$key][$k]['enabled'] = $merch_category[$v['id']];
									}
								}
							}
						}
					}
				}
			}
		}

		foreach ($category['parent'] as $value) {
			if ($value['enabled'] == 1) {
				$value['thumb'] = tomedia($value['thumb']);
				$value['advimg'] = tomedia($value['advimg']);
				$category_parent[$value['parentid']][] = $value;
				if (!empty($category['children'][$value['id']]) && (2 <= $level)) {
					foreach ($category['children'][$value['id']] as $val) {
						if ($val['enabled'] == 1) {
							$val['thumb'] = tomedia($val['thumb']);
							$val['advimg'] = tomedia($val['advimg']);
							$category_children[$val['parentid']][] = $val;
							if (!empty($category['children'][$val['id']]) && (3 <= $level)) {
								foreach ($category['children'][$val['id']] as $v) {
									if ($v['enabled'] == 1) {
										$v['thumb'] = tomedia($v['thumb']);
										$v['advimg'] = tomedia($v['advimg']);
										$category_grandchildren[$v['parentid']][] = $v;
									}
								}
							}
						}
					}
				}
			}
		}

		return array('parent' => $category_parent, 'children' => $category_children, 'grandchildren' => $category_grandchildren);
	}

	/**
     * 获取商品一二级分类
     * 可扩展三级
     * @return $result
     */
    public static function getCategoryTree($catelist = array(), $level = 2)
    {
        $child = $arr = $result = array();        
        if($catelist){
            foreach ($catelist as $val){
            	$val['thumb'] = tomedia($val['thumb']);
				$val['advimg'] = tomedia($val['advimg']);
            	if($level >= 2) {
            		if($val['level'] == 2){
	                    $arr[$val['parentid']][] = $val;
	                }
            	}
	            if($level == 3) {
	            	if($val['level'] == 3){
	                    $crr[$val['parentid']][] = $val;
	                }
	            }
                
                if($val['level'] == 1){
                    $child[] = $val;
                }
            }

            foreach ($arr as $k=>$v){
                foreach ($v as $kk=>$vv){
                    $arr[$k][$kk]['children'] = empty($crr[$vv['id']]) ? array() : $crr[$vv['id']];
                }
            }

            foreach ($child as $val){
                $val['children'] = empty($arr[$val['id']]) ? array() : $arr[$val['id']];
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * 获取商品列表
     * @param @args
     * @return $list
     */
    public static function getList($args = array())
    {
    	$mid = $args['mid'];
    	$shopset = model('common')->getSysset('shop');
		$page = ((!(empty($args['page'])) ? intval($args['page']) : 1));
		$pagesize = ((!(empty($args['pagesize'])) ? intval($args['pagesize']) : 10));
		$random = ((!(empty($args['random'])) ? $args['random'] : 0));
		$displayorder = 'displayorder';
		$merchid = ((!(empty($args['merchid'])) ? intval($args['merchid']) : 0));

		if (!(empty($merchid))) {
			$displayorder = 'merchdisplayorder';
		}
		$order = ((!(empty($args['order'])) ? $args['order'] . ' ' . ((empty($args['order']) ? '' : ((!(empty($args['by'])) ? $args['by'] : 'desc')))) : ' ' . $displayorder . ' desc,createtime desc '));
		$orderby = ((empty($args['order']) ? '' : ((!(empty($args['by'])) ? $args['by'] : 'desc'))));
		$merch_data = model('common')->getPluginset('merch');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		$condition = ' `deleted` = 0 and status = 1 and checked = 0 ';

		if (!(empty($merchid)) && !(empty($is_openmerch))) {
			$condition .= ' and merchid = ' . $merchid . ' and checked = 0 ';
		} else if ($is_openmerch == 0) {
			$condition .= ' and `merchid` = 0';
		}
		if(!empty($args['nolive'])) {
			$condition .= ' and nolive =1 ';
		}
 		if (empty($args['type'])) {
			$condition .= ' and type !=10 ';
		}

		$isnew = ((!(empty($args['isnew'])) ? 1 : 0));

		if (!(empty($isnew))) {
			$condition .= ' and isnew=1';
		}

		$ishot = ((!(empty($args['ishot'])) ? 1 : 0));

		if (!(empty($ishot))) {
			$condition .= ' and ishot=1';
		}


		$isrecommand = ((!(empty($args['isrecommand'])) ? 1 : 0));

		if (!(empty($isrecommand))) {
			$condition .= ' and isrecommand=1';
		}


		$isdiscount = ((!(empty($args['isdiscount'])) ? 1 : 0));

		if (!(empty($isdiscount))) {
			$condition .= ' and isdiscount=1';
		}


		$issendfree = ((!(empty($args['issendfree'])) ? 1 : 0));

		if (!(empty($issendfree))) {
			$condition .= ' and issendfree=1';
		}


		$istime = ((!(empty($args['istime'])) ? 1 : 0));

		if (!(empty($istime))) {
			$condition .= ' and istime=1 ';
		}

		$keyword = ((!(empty($args['keyword'])) ? $args['keyword'] : ''));

		if (!(empty($keyword))) {
			$condition .= ' AND (`title` LIKE "%' . $keyword . '%" OR `keywords` LIKE "%' . $keyword . '%")';

			if (empty($merchid)) {
				$condition .= ' AND nosearch=0';
			}

		}

		if (!(empty($args['cate']))) {
			if (empty($merchid)) {
				$category = Db::name('shop_goods_category')->field('id,parentid,name,thumb')->select();
				$catearr = array($args['cate']);

				foreach ($category as $index => $row ) {
					if ($row['parentid'] == $args['cate']) {
						$catearr[] = $row['id'];

						foreach ($category as $ind => $ro ) {
							if ($ro['parentid'] == $row['id']) {
								$catearr[] = $ro['id'];
							}

						}
					}

				}

				$catearr = array_unique($catearr);
				$condition .= ' AND ( ';

				foreach ($catearr as $key => $value ) {
					if ($key == 0) {
						$condition .= 'FIND_IN_SET(' . $value . ',cates)';
					}
					 else {
						$condition .= ' || FIND_IN_SET(' . $value . ',cates)';
					}
				}

				$condition .= ' <>0 )';
			} else {
				$category = Db::name('shop_merch_goods_category')->where('merchid',$merchid)->field('id,parentid,name,thumb')->select();
				$catearr = array($args['cate']);

				foreach ($category as $index => $row ) {
					if ($row['parentid'] == $args['cate']) {
						$catearr[] = $row['id'];

						foreach ($category as $ind => $ro ) {
							if ($ro['parentid'] == $row['id']) {
								$catearr[] = $ro['id'];
							}
						}
					}
				}

				$catearr = array_unique($catearr);
				$condition .= ' AND ( ';

				foreach ($catearr as $key => $value ) {
					if ($key == 0) {
						$condition .= 'FIND_IN_SET(' . $value . ',merchcates)';
					}
					 else {
						$condition .= ' || FIND_IN_SET(' . $value . ',merchcates)';
					}
				}

				$condition .= ' <>0 )';
			}			
		}

		if(!empty($args['startprice'])) {
			$condition .= ' and marketprice >= ' . $args['startprice'];
		}

		if(!empty($args['endprice'])) {
			$condition .= ' and marketprice <= ' . $args['endprice'];
		}

		$member = model('member')->getMember($mid);

		if (!(empty($member))) {
			$levelid = intval($member['level']);
			$groupid = intval($member['groupid']);
			$condition .= ' and ( ifnull(showlevels,\'\')=\'\' or FIND_IN_SET( ' . $levelid . ',showlevels)<>0 ) ';
			$condition .= ' and ( ifnull(showgroups,\'\')=\'\' or FIND_IN_SET( ' . $groupid . ',showgroups)<>0 ) ';
		}
		else {
			$condition .= ' and ifnull(showlevels,\'\')=\'\' ';
			$condition .= ' and   ifnull(showgroups,\'\')=\'\' ';
		}

		$condition .= ' and type <> 99 ';
		if(!empty($args['agentgoodsids'])) {
			$agentgoodsids = is_array($args['agentgoodsids']) ? implode(',', $args['agentgoodsids']) : 0;
			$condition .= ' and id in( ' . $agentgoodsids . ' ) ';
		}
		if (!($random)) {
			$list = Db::name('shop_goods')->where($condition)->order($order)->field('id,title,subtitle,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,sales,salesreal,total,description,`type`,ispresell,merchid,labelname,quality,seven,repair')->page($page,$pagesize)->select();
		}
		else {
			$list = Db::name('shop_goods')->where($condition)->field('id,title,thumb,marketprice,productprice,minprice,maxprice,isdiscount,isdiscount_time,sales,salesreal,total,description,`type`,ispresell,merchid,labelname,quality,seven,repair')->orderRaw('rand()')->limit($pagesize)->select();
		}
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
				$merchinfo = Db::name('shop_merch')->where('id',$val['merchid'])->field('id,logo,merchname')->find();
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
		$list = set_medias($list, 'thumb');
		return $list;

    }

    /**
     * 商品访问权限
     * @param array $goods
     * @param array $member
     * @return int
     */
	public function visit($goods = array(), $member = array())
	{
		if (empty($goods)) {
			return 1;
		}

		if (empty($member)) {
			return 1;
		}


		$showlevels = (($goods['showlevels'] != '' ? explode(',', $goods['showlevels']) : array()));
		$showgroups = (($goods['showgroups'] != '' ? explode(',', $goods['showgroups']) : array()));
		$showgoods = 0;

		if (!(empty($member))) {
			if ((!(empty($showlevels)) && in_array($member['level'], $showlevels)) || (!(empty($showgroups)) && in_array($member['groupid'], $showgroups)) || (empty($showlevels) && empty($showgroups))) {
				$showgoods = 1;
			}

		} else if (empty($showlevels) && empty($showgroups)) {
			$showgoods = 1;
		}


		return $showgoods;
	}

	public function getTaskGoods($mid, $goodsid, $rank, $log_id = 0, $join_id = 0, $optionid = 0, $total = 0)
	{
		$is_task_goods = 0;
		$is_task_goods_option = 0;

		if (!(empty($join_id))) {
			$flag = 1;
		}
		 else if (!(empty($log_id))) {
			$flag = 2;
		}


		$param = array();
		$param['mid'] = $mid;
		$param['goods_id'] = $goodsid;
		$param['rank'] = $rank;
		$param['join_id'] = $join_id;
		$param['log_id'] = $log_id;
		$param['goods_spec'] = $optionid;
		$param['goods_num'] = $total;

		if (!(empty($task_goods)) && empty($total) && (!(empty($join_id)) || !(empty($log_id)))) {
			if (!(empty($task_goods['spec']))) {
				foreach ($task_goods['spec'] as $k => $v ) {
					if (empty($v['total'])) {
						unset($task_goods['spec'][$k]);
						continue;
					}

					if (!(empty($optionid))) {
						if ($k == $optionid) {
							$task_goods['marketprice'] = $v['marketprice'];
							$task_goods['total'] = $v['total'];
						}
						 else {
							unset($task_goods['spec'][$k]);
						}
					}

					if (!(empty($optionid)) && ($k != $optionid)) {
						unset($task_goods['spec'][$k]);
					}
					 else if (!(empty($optionid)) && ($k != $optionid)) {
						$task_goods['marketprice'] = $v['marketprice'];
						$task_goods['total'] = $v['total'];
					}
				}

				if (!(empty($task_goods['spec']))) {
					$is_task_goods = $flag;
					$is_task_goods_option = 1;
				}

			}
			 else if (!(empty($task_goods['total']))) {
				$is_task_goods = $flag;
			}

		}


		$data = array();
		$data['is_task_goods'] = $is_task_goods;
		$data['is_task_goods_option'] = $is_task_goods_option;
		$data['task_goods'] = $task_goods;
		return $data;
	}

	/**
     * 使用掉重复购买的变量
     * @param $goods
     */
	public static function useBuyAgain($mid, $orderid)
	{
		$order_goods = Db::name('shop_order_goods')->where('orderid','neq',$orderid)->where('canbuyagain',1)->where('mid',$mid)->field('id,goodsid')->select();

		if (empty($order_goods)) {
			return false;
		}
		Db::name('shop_order_goods')->where('goodsid','in',implode(',', array_keys($order_goods)))->setField('canbuyagain',0);
	}

	public static function getFullCategory($fullname = false, $enabled = false)
	{
		$allcategory = array();
		$sql = ' 1 ';

		if ($enabled) {
			$sql .= ' AND enabled=1';
		}
		$category = Db::name('shop_goods_category')->where($sql)->order('parentid','asc')->order('displayorder','desc')->select();

		if (empty($category)) {
			return array();
		}

		foreach ($category as &$c) {
			if (empty($c['parentid'])) {
				$allcategory[] = $c;

				foreach ($category as &$c1) {
					if ($c1['parentid'] != $c['id']) {
						continue;
					}

					if ($fullname) {
						$c1['name'] = $c['name'] . '-' . $c1['name'];
					}

					$allcategory[] = $c1;

					foreach ($category as &$c2) {
						if ($c2['parentid'] != $c1['id']) {
							continue;
						}

						if ($fullname) {
							$c2['name'] = $c1['name'] . '-' . $c2['name'];
						}

						$allcategory[] = $c2;

						foreach ($category as &$c3) {
							if ($c3['parentid'] != $c2['id']) {
								continue;
							}

							if ($fullname) {
								$c3['name'] = $c2['name'] . '-' . $c3['name'];
							}

							$allcategory[] = $c3;
						}

						unset($c3);
					}

					unset($c2);
				}

				unset($c1);
			}

			unset($c);
		}

		return $allcategory;
	}

	public static function addHistory($goodsid = 0, $mid = 0)
	{
		Db::name('shop_goods')->where('id',$goodsid)->setInc('viewcount');
		if(empty($mid)) {
			return;
		}
		$history = Db::name('shop_member_history')->where('goodsid',$goodsid)->where('mid',$mid)->field('id,times')->find();

		if (empty($history)) {
			$history = array('mid' => $mid, 'goodsid' => $goodsid, 'deleted' => 0, 'createtime' => time(), 'times' => 1);
			Db::name('shop_member_history')->insert($history);
		}
		 else {
			Db::name('shop_member_history')->where('id',$history['id'])->update(array('deleted' => 0, 'times' => $history['times'] + 1));
		}
	}

	public static function getTotals($merch = 0) 
	{
		$condition = ' 1 ';
		if(!empty($merch)) {
			$condition .= ' and merchid = ' . $merch;
		}
		return array(
			'sale' => Db::name('shop_goods')->where($condition . ' and status > 0 and checked=0 and deleted=0 and total>0 and type != 30')->count(), 
			'out' => Db::name('shop_goods')->where($condition . ' and status > 0 and deleted=0 and total=0 and type !=30')->count(), 
			'stock' => Db::name('shop_goods')->where($condition . ' and (status=0 or checked=1) and deleted=0 and type !=30')->count(), 
			'cycle' => Db::name('shop_goods')->where($condition . ' and deleted=1 and type !=30')->count(), 
			'verify' => Db::name('shop_goods')->where($condition . ' and deleted=0  and type !=30 and merchid>0 and checked=1')->count(),
		);
	}

	public static function getMemberPrice($goods, $level)
	{
		if (!(empty($goods['isnodiscount']))) {
			return (double) 0;
		}


		$liveid = intval($level['id']);


		if (!(empty($level['id']))) {
			$level = Db::name('member_level')->where('id = ' . $level['id'] . ' and enabled = 1')->find();
			$level = ((empty($level) ? array() : $level));
		}

		$discounts = json_decode($goods['discounts'], true);
		if (is_array($discounts)) {
			$key = ((!(empty($level['id'])) ? 'level' . $level['id'] : 'default'));
			if (!(isset($discounts['type'])) || empty($discounts['type'])) {
				$memberprice = $goods['minprice'];

				if (!(empty($discounts[$key]))) {
					$dd = floatval($discounts[$key]);

					if ((0 < $dd) && ($dd < 10)) {
						$memberprice = round(($dd / 10) * $goods['minprice'], 2);
					}
				} else {
					$dd = floatval($discounts[$key . '_pay']);
					$md = floatval($level['discount']);
					
					if (!(empty($dd))) {
						$memberprice = round($dd, 2);
					} else if ((0 < $md) && ($md < 10)) {
						$memberprice = round(($md / 10) * $goods['minprice'], 2);
					}

				}

				if ($memberprice == $goods['minprice']) {
					return (double) 0;
				}


				return $memberprice;
			}


			$options = model('goods')->getOptions($goods);
			$marketprice = array();

			foreach ($options as $option ) {
				$discount = trim($discounts[$key]['option' . $option['id']]);

				if ($discount == '') {
					$discount = round(floatval($level['discount']) * 10, 2) . '%';
				}


				if (!(empty($liveid)) && !(empty($option['islive']))) {
					if ((0 < $option['liveprice']) && ($option['liveprice'] < $option['marketprice'])) {
						$option['marketprice'] = $option['liveprice'];
					}

				}


				$optionprice = model('order')->getFormartDiscountPrice($discount, $option['marketprice']);
				$marketprice[] = $optionprice;
			}

			$minprice = min($marketprice);
			$maxprice = max($marketprice);
			$memberprice = array('minprice' => (double) $minprice, 'maxprice' => (double) $maxprice);

			if ($memberprice['minprice'] < $memberprice['maxprice']) {
				$memberprice = $memberprice['minprice'] . '~' . $memberprice['maxprice'];
			}
			 else {
				$memberprice = $memberprice['minprice'];
			}

			if ($memberprice == $goods['minprice']) {
				return (double) 0;
			}


			return $memberprice;
		}

	}

	public static function getOptions($goods)
	{
		$id = $goods['id'];
		$specs = false;
		$options = false;

		if (!(empty($goods)) && $goods['hasoption']) {
			$specs = Db::name('shop_goods_spec')->where('goodsid = ' . $id)->order('displayorder','asc')->select();

			foreach ($specs as &$spec ) {
				$spec['items'] = Db::name('shop_goods_spec_item')->where('specid',$spec['id'])->order('displayorder','asc')->select();
			}

			unset($spec);
			$options = Db::name('shop_goods_option')->where('goodsid',$id)->order('displayorder','asc')->select();
		}


		if ((0 < $goods['ispresell']) && (($goods['preselltimeend'] == 0) || (time() < $goods['preselltimeend']))) {
			$options['marketprice'] = $options['presellprice'];
		}


		return $options;
	}

}