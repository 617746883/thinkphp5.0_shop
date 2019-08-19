<?php
/**
 * apiv1 购物车
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Sign extends Base
{
	public function index()
	{
		$mid = $this->getMemberId();
		$set = model('sign')->getSet();
		if (!(empty($set['sign_rule']))) 
		{
			$set['sign_rule'] = iunserializer($set['sign_rule']);
			$set['sign_rule'] = htmlspecialchars_decode($set['sign_rule']);
		}
		if (empty($set['isopen'])) 
		{
			$this->result(0,$set['textsign'] . '未开启!');
		}
		$month = model('sign')->getMonth();
		$member = model('member')->getMember($mid);
		if (empty($member)) 
		{
			$this->result(0,'获取用户信息失败!');
		}
		$calendar = model('sign')->getCalendar();
		$signinfo = model('sign')->getSign($mid);
		$advaward = model('sign')->getAdvAward();
		$json_arr = array('calendar' => $calendar, 'signinfo' => $signinfo, 'advaward' => $advaward, 'year' => date('Y', time()), 'month' => date('m', time()), 'today' => date('d', time()), 'signed' => $signinfo['signed'], 'signold' => $set['signold'], 'signoldprice' => $set['signold_price'], 'signoldtype' => (empty($set['signold_type']) ? $set['textmoney'] : $set['textcredit']), 'textsign' => $set['textsign'], 'textsigned' => $set['textsigned'], 'textsignold' => $set['textsignold'], 'textsignforget' => $set['textsignforget']);
		$json = json_encode($json_arr);
		$texts = array('sign' => $set['textsign'], 'signed' => $set['textsigned'], 'signold' => $set['textsignold'], 'credit' => $set['textcredit'], 'color' => $set['maincolor']);
		$credit = 0;
		if (!(empty($set['reward_default_day'])) && (0 < $set['reward_default_day'])) 
		{
			$credit = $set['reward_default_day'];
			$message = ((empty($date) ? '日常' . $set['textsign'] . '+' : $set['textsignold'] . '+'));
			$message .= $set['reward_default_day'] . $set['textcredit'];
		}
		if (!(empty($set['reward_default_first'])) && (0 < $set['reward_default_first']) && empty($signinfo['sum']) && empty($date)) 
		{
			$credit = $set['reward_default_first'];
			$message = '首次' . $set['textsign'] . '+' . $set['reward_default_first'] . $set['textcredit'];
		}
		if (!(empty($reword_special)) && empty($date)) 
		{
			foreach ($reword_special as $item ) 
			{
				$day = date('Y-m-d', $item['date']);
				$today = date('Y-m-d', time());
				if (($day === $today) && !(empty($item['credit']))) 
				{
					$credit = $credit + $item['credit'];
					if (!(empty($message))) 
					{
						$message .= "\r\n";
					}
					$message .= ((empty($item['title']) ? $today : $item['title']));
					$message .= $set['textsign'] . '+' . $item['credit'] . $set['textcredit'];
					break;
				}
			}
		}
		$this->result(1,'success',array('signed'=>$signinfo['signed'],'credit'=>$credit));
	}

	public function dosign() 
	{
		$mid = $this->getMemberId();
		$set = model('sign')->getSet();
		if (empty($set['isopen'])) 
		{
			$this->result(0,$set['textcredit'] . $set['textsign'] . '未开启!');
		}
		$date = trim(input('date'));
		(($date == 'null' ? '' : $date));
		if (!(empty($date))) 
		{
			$dates = date('Y-m-d', strtotime($date));
			$date_verify = date('Y-m-d', strtotime($date));
			if ($date_verify != $dates) 
			{
				$this->result(0,'日期传入错误');
			}
		}
		$signinfo = model('sign')->getSign($mid,$date);
		if (!(empty($date))) 
		{
			$datemonth = date('m', strtotime($date));
			$thismonth = date('m', time());
			if ($datemonth < $thismonth) 
			{
				$this->result(0, $set['textsign'] . '月份小于当前月份!');
			}
		}
		if (!(empty($signinfo['signed']))) 
		{
			$this->result(0, '已经' . $set['textsign'] . '，不要重复' . $set['textsign'] . '哦~');
		}
		if (!(empty($date)) && (time() < strtotime($date))) 
		{
			$this->result(0, $set['textsign'] . '日期大于当前日期!');
		}
		$member = model('member')->getMember($mid);
		$reword_special = iunserializer($set['reword_special']);
		$credit = 0;
		if (!(empty($set['reward_default_day'])) && (0 < $set['reward_default_day'])) 
		{
			$credit = $set['reward_default_day'];
			$message = ((empty($date) ? '日常' . $set['textsign'] . '+' : $set['textsignold'] . '+'));
			$message .= $set['reward_default_day'] . $set['textcredit'];
		}
		if (!(empty($set['reward_default_first'])) && (0 < $set['reward_default_first']) && empty($signinfo['sum']) && empty($date)) 
		{
			$credit = $set['reward_default_first'];
			$message = '首次' . $set['textsign'] . '+' . $set['reward_default_first'] . $set['textcredit'];
		}
		if (!(empty($reword_special)) && empty($date)) 
		{
			foreach ($reword_special as $item ) 
			{
				$day = date('Y-m-d', $item['date']);
				$today = date('Y-m-d', time());
				if (($day === $today) && !(empty($item['credit']))) 
				{
					$credit = $credit + $item['credit'];
					if (!(empty($message))) 
					{
						$message .= "\r\n";
					}
					$message .= ((empty($item['title']) ? $today : $item['title']));
					$message .= $set['textsign'] . '+' . $item['credit'] . $set['textcredit'];
					break;
				}
			}
		}
		if (!(empty($date)) && !(empty($set['signold'])) && (0 < $set['signold_price'])) 
		{
			if (empty($set['signold_type'])) 
			{
				if ($member['credit2'] < $set['signold_price']) 
				{
					$this->result(0, $set['textsignold'] . '失败! 您的' . $set['textmoney'] . '不足, 无法' . $set['textsignold']);
				}
				model('member')->setCredit($mid, 'credit2', -$set['signold_price'], $set['textcredit'] . $set['textsign'] . ': ' . $set['textsignold'] . '扣除' . $set['signold_price'] . $set['textmoney']);
			}
			else 
			{
				if ($member['credit1'] < $set['signold_price']) 
				{
					$this->result(0, $set['textsignold'] . '失败! 您的' . $set['textcredit'] . '不足, 无法' . $set['textsignold']);
				}
				model('member')->setCredit($mid, 'credit1', -$set['signold_price'], $set['textcredit'] . $set['textsign'] . ': ' . $set['textsignold'] . '扣除' . $set['signold_price'] . $set['textcredit']);
			}
		}
		if(empty($message)) {
			$message = '日常' . $set['textsign'];
		}
		if (!(empty($credit)) && (0 < $credit)) 
		{
			model('member')->setCredit($mid, 'credit1', +$credit, $set['textcredit'] . $set['textsign'] . ': ' . $message);
		}
		$arr = array('time' => (empty($date) ? time() : strtotime($date)), 'mid' => $mid, 'credit' => $credit, 'log' => $message);
		$id = Db::name('shop_sign_records')->insertGetId($arr);
		$signinfo = model('sign')->getSign($mid);
		$member = model('member')->getMember($mid);
		$result = array('message' => $set['textsign'] . '成功!' . $message, 'signorder' => $signinfo['orderday'], 'signsum' => $signinfo['sum'], 'addcredit' => $credit, 'credit' => intval($member['credit1']));
		model('sign')->updateSign($mid,$signinfo);
		$this->result(1,'success', $result);
	}
}