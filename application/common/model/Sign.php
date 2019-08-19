<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Sign extends \think\Model
{
	public function getSet() 
	{
		$set = Db::name('shop_sign_set')->where('id is not null')->order('id asc')->find();
		if( empty($set) ) 
		{
			return "";
		}
		$shopset = model('common')->getSysset();
		if( empty($set["textsign"]) ) 
		{
			$set["textsign"] = "签到";
		}
		if( empty($set["textsigned"]) ) 
		{
			$set["textsigned"] = "已签";
		}
		if( empty($set["textsignold"]) ) 
		{
			$set["textsignold"] = "补签";
		}
		if( empty($set["textsignforget"]) ) 
		{
			$set["textsignforget"] = "漏签";
		}
		if( empty($shopset["trade"]["credittext"]) ) 
		{
			$set["textcredit"] = "积分";
		} else {
			$set["textcredit"] = $shopset["trade"]["credittext"];
		}
		if( empty($shopset["trade"]["credittext"]) ) {
			$set["textmoney"] = "余额";
		} else {
			$set["textmoney"] = $shopset["trade"]["moneytext"];
		}
		return $set;
	}

	public function getDate($date = array( )) 
	{
		if( empty($date) ) 
		{
			$date = array( "year" => date("y", time()), "month" => date("m", time()), "day" => date("d", time()) );
		}
		$lasttime = strtotime($date["year"] . "-" . ($date["month"] + 1) . "-1") - 1;
		if( $date["month"] == 12 ) 
		{
			$lasttime_year = $date["year"] + 1;
			$lasttime = strtotime($lasttime_year . "-1-1") - 1;
		}
		$days = date("t", strtotime($date["year"] . "-" . $date["month"]));
		$result = array( "firstday" => 1, "lastday" => $days, "firsttime" => strtotime($date["year"] . "-" . $date["month"] . "-1"), "lasttime" => $lasttime, "year" => $date["year"], "thisyear" => date("Y", time()), "month" => $date["month"], "thismonth" => date("m", time()), "day" => $date["day"], "doday" => date("d", time()), "days" => $days );
		return $result;
	}

	public function getMonth() 
	{
		$month = array( );
		$start_year = "2016";
		$start_month = "8";
		$this_year = date("Y", time());
		$this_month = date("m", time());
		for( $i = $start_year; $i <= $this_year; $i++ ) 
		{
			if( 0 < $this_year - $i ) 
			{
				$ii_month = 12;
			}
			else 
			{
				$ii_month = $this_month;
			}
			if( $start_year < $i ) 
			{
				$start_month = 1;
			}
			for( $ii = $start_month; $ii <= $ii_month; $ii++ ) 
			{
				$month[] = array( "year" => $i, "month" => ($ii < 10 ? "0" . $ii : $ii) );
			}
		}
		return $month;
	}

	public function getCalendar($mid = 0, $year = NULL, $month = NULL, $week = true) 
	{
		if( empty($year) ) 
		{
			$year = date("Y", time());
		}
		if( empty($month) ) 
		{
			$month = date("m", time());
		}
		$set = self::getSet();
		$date = self::getDate(array( "year" => $year, "month" => $month ));
		$array = array( );
		$maxday = 28;
		if( 28 < $date["days"] ) 
		{
			$maxday = 35;
		}
		for( $i = 1; $i <= $maxday; $i++ ) 
		{
			$day = 0;
			if( $i <= $date["days"] ) 
			{
				$day = $i;
			}
			$today = 0;
			if( $date["thisyear"] == $year && $date["thismonth"] == $month && $date["doday"] == $i ) 
			{
				$today = 1;
			}
			$array[$i] = array( "year" => $date["year"], "month" => $date["month"], "day" => $day, "date" => $date["year"] . "-" . $date["month"] . "-" . $day, "signed" => 0, "signold" => 1, "title" => "", "today" => $today );
		}
		$records = Db::name('shop_sign_records')->where('mid = ' . $mid . ' and `type`=0 and `time` between ' . $date["firsttime"] . ' and ' . $date["lasttime"])->select();
		if( !empty($records) ) 
		{
			foreach( $records as $item ) 
			{
				$sign_date = array( "year" => date("Y", $item["time"]), "month" => date("m", $item["time"]), "day" => date("d", $item["time"]) );
				foreach( $array as $day => &$row ) 
				{
					if( $day == $sign_date["day"] ) 
					{
						$row["signed"] = 1;
					}
				}
				unset($row);
			}
		}
		$reword_special = iunserializer($set["reword_special"]);
		if( !empty($reword_special) ) 
		{
			foreach( $reword_special as $item ) 
			{
				$sign_date = array( "year" => date("Y", $item["date"]), "month" => date("m", $item["date"]), "day" => date("d", $item["date"]) );
				foreach( $array as $day => &$row ) 
				{
					if( $row["day"] == $sign_date["day"] && $row["month"] == $sign_date["month"] && $row["year"] == $sign_date["year"] ) 
					{
						$row["title"] = $item["title"];
						$row["color"] = $item["color"];
					}
				}
				unset($row);
			}
		}
		if( $week ) 
		{
			$calendar = array( );
			foreach( $array as $index => $row ) 
			{
				if( 1 <= $index && $index <= 7 ) 
				{
					$cindex = 0;
				}
				else 
				{
					if( 8 <= $index && $index <= 14 ) 
					{
						$cindex = 1;
					}
					else 
					{
						if( 15 <= $index && $index <= 21 ) 
						{
							$cindex = 2;
						}
						else 
						{
							if( 22 <= $index && $index <= 28 ) 
							{
								$cindex = 3;
							}
							else 
							{
								if( 29 <= $index && $index <= 35 ) 
								{
									$cindex = 4;
								}
							}
						}
					}
				}
				$calendar[$cindex][] = $row;
			}
		}
		else 
		{
			$calendar = $array;
		}
		return $calendar;
	}

	public function getSign($mid = 0,$date = NULL) 
	{
		$set = self::getSet();
		$condition = "";
		if( !empty($set["cycle"]) ) 
		{
			$month_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$month_end = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
			$condition .= " and `time` between " . $month_start . " and " . $month_end . " ";
		}
		$records = Db::name('shop_sign_records')->where('mid = ' . $mid . ' and `type`=0 ' . $condition)->order('time desc')->select();
		$signed = 0;
		$orderindex = 0;
		$order = array( );
		$orderday = 0;
		if( !empty($records) ) 
		{
			foreach( $records as $key => $item ) 
			{
				$day = date("Y-m-d", $item["time"]);
				$today = date("Y-m-d", time());
				if( empty($date) && $day == $today ) 
				{
					$signed = 1;
				}
				if( !empty($date) && $day == $date ) 
				{
					$signed = 1;
				}
				if( 1 < count($records) && $key == 0 && date("Y-m-d", $records[$key + 1]["time"]) == date("Y-m-d", strtotime("-1 day")) ) 
				{
					$order[$orderindex]++;
				}
				$dday = date("d", $item["time"]);
				$pday = date("d", (isset($records[$key + 1]["time"]) ? $records[$key + 1]["time"] : 0));
				if( $dday - $pday == 1 ) 
				{
					$order[$orderindex]++;
				}
				else 
				{
					if( $dday == 1 && date("d", (isset($records[$key + 1]["time"]) ? $records[$key + 1]["time"] : 0)) == date("t", strtotime("-1 month", $item["time"])) ) 
					{
						$order[$orderindex]++;
					}
					else 
					{
						$orderindex++;
						$order[$orderindex]++;
					}
				}
				if( self::dateplus($day, $orderday) == self::dateminus($today, 1) ) 
				{
					$orderday++;
				}
			}
		}
		$data = array( "order" => (empty($order) ? 0 : max($order)), "orderday" => (empty($signed) ? $orderday : $orderday + 1), "sum" => count($records), "signed" => $signed );
		return $data;
	}

	public function dateplus($date, $day) 
	{
		$time = strtotime($date);
		$time = $time + 3600 * 24 * $day;
		$date = date("Y-m-d", $time);
		return $date;
	}

	public function dateminus($date, $day) 
	{
		$time = strtotime($date);
		$time = $time - 3600 * 24 * $day;
		$date = date("Y-m-d", $time);
		return $date;
	}

	public function getAdvAward($mid = 0) 
	{
		$set = self::getSet();
		$date = self::getDate();
		$signinfo = self::getSign($mid);
		$reword_sum = iunserializer($set["reword_sum"]);
		$reword_order = iunserializer($set["reword_order"]);
		$condition = "";
		if( !empty($set["cycle"]) ) 
		{
			$month_start = mktime(0, 0, 0, date("m"), 1, date("Y"));
			$month_end = mktime(23, 59, 59, date("m"), date("t"), date("Y"));
			$condition .= " and `time` between " . $month_start . " and " . $month_end . " ";
		}
		$records = Db::name('shop_sign_records')->where('mid = ' . intval($mid) . $condition)->order('time asc')->select();
		if( !empty($records) ) 
		{
			foreach( $records as $item ) 
			{
				if( !empty($reword_order) ) 
				{
					foreach( $reword_order as $i => &$order ) 
					{
						if( !empty($set["cycle"]) && $date["days"] < $order["day"] ) 
						{
							unset($reword_order[$i]);
						}
						if( $item["day"] == $order["day"] && $item["type"] == 1 ) 
						{
							$order["drawed"] = 1;
						}
						else 
						{
							if( $order["day"] <= $signinfo["order"] ) 
							{
								$order["candraw"] = 1;
							}
						}
					}
					unset($order);
				}
				if( !empty($reword_sum) ) 
				{
					foreach( $reword_sum as $i => &$sum ) 
					{
						if( !empty($set["cycle"]) && $date["days"] < $sum["day"] ) 
						{
							unset($reword_sum[$i]);
						}
						if( $item["day"] == $sum["day"] && $item["type"] == 2 ) 
						{
							$sum["drawed"] = 1;
						}
						else 
						{
							if( $sum["day"] <= $signinfo["sum"] ) 
							{
								$sum["candraw"] = 1;
							}
						}
					}
					unset($sum);
				}
			}
		}
		$data = array( "order" => $reword_order, "sum" => $reword_sum );
		return $data;
	}

	public function updateSign($mid = 0, $signinfo) 
	{
		if( empty($signinfo) ) 
		{
			$signinfo = self::getSign($mid);
		}
		$info = Db::name('shop_sign_user')->where('mid = ' . $mid)->field('id')->find();
		$data = array( "mid" => $mid, "order" => $signinfo["order"], "orderday" => $signinfo["orderday"], "sum" => $signinfo["sum"], "signdate" => date("Y-m") );
		if( empty($info) ) {
			Db::name('shop_sign_user')->insert($data);
		} else {
			Db::name('shop_sign_user')->where('id = ' . $info['id'])->update($data);
		}
	}
}