<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Seckill extends \think\Model
{
	public static function get_prefix() 
	{
		$key = random(16);
		return "sul1ss_shop_" . $key . "_seckill_";
	}

	public static function setTaskCache($id) 
	{
		if( is_error(redis()) ) 
		{
			return NULL;
		}
		$redis_prefix = self::get_prefix();
		$task = pdo_fetch("select * from " . tablename("ewei_shop_seckill_task") . " where id=:id limit 1", array( ":id" => $id ));
		redis()->delete((string) $redis_prefix . "info_" . $id);
		redis()->hMset((string) $redis_prefix . "info_" . $id, $task);
		$allrooms = pdo_fetchall("select * from " . tablename("ewei_shop_seckill_task_room") . " where taskid=:taskid and enabled=1 and uniacid=:uniacid order by `displayorder` desc", array( ":taskid" => $id, ":uniacid" => $_W["uniacid"] ));
		redis()->delete((string) $redis_prefix . "rooms_" . $id);
		foreach( $allrooms as $room ) 
		{
			redis()->rPush((string) $redis_prefix . "rooms_" . $id, json_encode($room));
		}
		redis()->delete((string) $redis_prefix . "times_" . $id);
		$alltimes = pdo_fetchall("select * from " . tablename("ewei_shop_seckill_task_time") . " where taskid=:taskid and uniacid=:uniacid order by `time` asc", array( ":taskid" => $id, ":uniacid" => $_W["uniacid"] ));
		$redisgoods = array( );
		foreach( $alltimes as &$time ) 
		{
			$goods = pdo_fetchall("select * from " . tablename("ewei_shop_seckill_task_goods") . " where taskid=:taskid and timeid=:timeid and uniacid=:uniacid order by displayorder asc", array( ":taskid" => $id, ":timeid" => $time["id"], ":uniacid" => $_W["uniacid"] ));
			foreach( $goods as $key => $val ) 
			{
				$goodskey = (string) $_W["uniacid"] . "_seckill_stock_" . $val["taskid"] . "_" . $val["roomid"] . "_" . $val["timeid"] . "_" . $val["goodsid"] . "_" . $val["optionid"];
				redis()->set($goodskey, $val["total"], 86400);
			}
			if( !empty($goods) ) 
			{
				if( !isset($redisgoods[$time["time"]]) || !is_array($redisgoods[$time["time"]]) ) 
				{
					$redisgoods["time-" . $time["time"]] = array( );
				}
				redis()->rPush((string) $redis_prefix . "times_" . $id, json_encode($time));
				$redisgoods["time-" . $time["time"]] = json_encode($goods);
			}
		}
		redis()->delete((string) $redis_prefix . "goods_" . $id);
		if( !empty($redisgoods) ) 
		{
			redis()->hMset((string) $redis_prefix . "goods_" . $id, $redisgoods);
		}
	}

	public static function usedDate($taskid) 
	{
		if( is_error(redis()) ) {
			return false;
		}

		$redis_prefix = self::get_prefix();
		$calendar = redis()->hGetAll((string) $redis_prefix . "calendar");
		if( !is_array($calendar) || empty($calendar) ) 
		{
			return false;
		}
		foreach( $calendar as $k => $v ) 
		{
			if( !empty($v) && is_array($v) && $v["taskid"] == $taskid ) 
			{
				return $k;
			}
		}
		return false;
	}
}