<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Treasure extends \think\Model
{
	/**********计算新的一期商品夺宝码或者创建新的商品********/
	public function create_newgoods($goodsid = ''){
		if($goodsid != ''){
			//判断是否已经有正在进行的期数
			$result_check = Db::name('shop_treasure_goods_period')->where('goodsid = ' . intval($goodsid) . ' and status = 1')->field('id,status')->select();
			if(!empty($result_check)){
				return false;
			}
			//创建新的一期商品
			$result_goodslist = Db::name('shop_treasure_goods')->where('id = ' . intval($goodsid))->field('canyurenshu,id,maxperiods,periods,price,jiexiaotime,couponid,init_money,next_init_money,sort')->find();
			if($result_goodslist['maxperiods'] <= $result_goodslist['periods']){
				//判断是否期数已满
				Db::name('shop_treasure_goods')->where('id = ' . $result_goodslist['id'])->setField('status',0);
			}
	
			if($result_goodslist['next_init_money'] != 0){
				//判定是否修改了商品专区价格
				$result_goodslist['init_money'] = $result_goodslist['next_init_money'];
				Db::name('shop_treasure_goods')->where('id = ' . $result_goodslist['id'])->update(array('init_money'=>$result_goodslist['next_init_money'],'next_init_money'=>0));
			}

			$codes_num = intval($result_goodslist['price'])/$result_goodslist['init_money'];	//夺宝码数量
			$allcodes = self::create_codes_group($codes_num);		//获取压缩夺宝码段
			$new_period['canyurenshu'] = 0;
			$new_period['scale'] = 0;
			$new_period['goodsid'] = $goodsid;
			$new_period['periods'] = intval($result_goodslist['periods']) + 1;
			$new_period['jiexiaotime'] = $result_goodslist['jiexiaotime'];
			$new_period['shengyucodes'] = $codes_num;
			$new_period['zongcodes'] = $codes_num;
			$new_period['status'] = 1;
			$new_period['scale'] = 0;
			$new_period['createtime'] = time();
			$new_period['sort'] = $result_goodslist['sort'];
			$new_period['allcodes'] = $allcodes;
			$new_period['period_number'] = date('Ymd').substr(time(), -5).substr(microtime(), 2, 5).sprintf('%02d', rand(0, 99));

			$result_insert = Db::name('shop_treasure_goods_period')->insertGetId($new_period);		//新数据生成插入
			Db::name('shop_treasure_goods')->where('id = ' . $goodsid)->update(array('periods'=>$new_period['periods']));
			self::create_code($new_period['period_number']);
			return $new_period['period_number'];
		}
	}

	/**********计算新的压缩字段********/
	public function create_codes_group($codes_number = 0){
		global $_W;
		$codes_ervery = 5;		//设置每组大小
		$codes_group = intval($codes_number/$codes_ervery);		//夺宝码组数
		$codes_group_last = intval($codes_number%$codes_ervery);	//夺宝码最后一组个数
		if($codes_group_last != 0){
			$codes_group++;		//有余数组数加1
		}

		$codes_group_new = array();
		for($i = 0;$i < $codes_group;$i++){
			if($codes_group_last != 0 && $i == $codes_group-1){
				$codes_group_new[$i] = $i*$codes_ervery.':'.($i*$codes_ervery+$codes_group_last); //最后一个区段
			}else{
				$codes_group_new[$i] = $i*$codes_ervery.':'.($i+1)*$codes_ervery;		//创建区段
			}
		}
		shuffle($codes_group_new);			//打乱数组
		$allcodes = serialize($codes_group_new);		//压缩数组
		return $allcodes;
	}

	/***************计算夺宝码****************/
	public function create_code($period_number = '',$flag = 0){
		$result_period = Db::name('shop_treasure_goods_period')->where('period_number = ' . $period_number)->field('id,period_number,codes,shengyucodes,allcodes,canyurenshu')->find();
		$group_number  = 40;		//	取得区间组数
		$codes_ervery = 5;		//夺宝码区间个数
		$allcodes = unserialize($result_period['allcodes']);		//解压所有码段
		$needcodes = array();			//设置夺宝码数组
		if($result_period['shengyucodes'] < ($group_number * $codes_ervery)){
			$need_groupnum = sizeof($allcodes);
			$left_codes = '';
		}else{
			$need_groupnum = $group_number;
			$left_codes = array_slice($allcodes,$need_groupnum,sizeof($allcodes)-$need_groupnum);
			if(!is_array($left_codes)){
				if($flag == 1){
					return 'false';
				}else{
					self::create_code($period_number,1);
				}						//检测剩余码
			}
			$left_codes = serialize($left_codes);		//压缩剩余夺宝码段
		}
		//剩余码小于单次取得数
		for($i = 0;$i < $need_groupnum ; $i++){
			$codes_ervery_group = array_slice($allcodes,$i,1);		//从第0个取值取一个
			$arr = explode(':', $codes_ervery_group[0]);		//分隔字符串
			for($j = intval($arr[0]) ; $j < intval($arr[1]) ; $j++){
				//合成夺宝码
				$num = $j;		//次序
				$needcodes[$num] = 1000001+$num;	//夺宝码合成
			}
		}
		shuffle($needcodes);			//打乱夺宝码
		if(!is_array($needcodes)){		//检测生成码
				if($flag == 1){
					return 'false';
				}else{
					self::create_code($period_number,1);
				}					
			}
		$needcodes = serialize($needcodes);
		Db::name('shop_treasure_goods_period')->where('period_number = ' . $period_number)->update(array('codes'=>$needcodes,'allcodes'=>$left_codes));
	}

}