<?php
/**
 * 数据统计
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Request;
use think\Db;
class Statistics extends Base
{
	public function index()
    {
    	header('location: ' . url('merch/statistics/sale'));exit;
    }

	public function sale()
	{
		$merch = $this->merch;
		$op = input('op');
    	$year = input('year');
    	$month = input('month');
    	$day = input('day');
    	$operation = (!empty($op) ? $op : 'display');
		$years = array();
		$current_year = date('Y');
		$year = (empty($year) ? $current_year : $year);
		$i = $current_year - 10;

		while ($i <= $current_year) {
			$years[] = array('data' => $i, 'selected' => $i == $year);
			++$i;
		}

		$months = array();
		$current_month = date('m');
		$month = $month;
		$i = 1;

		while ($i <= 12) {
			$months[] = array('data' => $i, 'selected' => $i == $month);
			++$i;
		}

		$day = intval($day);
		$type = intval(input('type'));
		$list = array();
		$totalcount = 0;
		$maxcount = 0;
		$maxcount_date = '';
		$maxdate = '';
		$countfield = (empty($type) ? 'sum(price)' : 'count(*)');
		$typename = (empty($type) ? '交易额' : '交易量');
		$dataname = (empty($month) ? '月份' : '日期');
		if (!empty($year) && !empty($month) && !empty($day)) {
			$hour = 0;

			while ($hour < 24) {
				$nexthour = $hour + 1;
				if(empty($type)) {
					$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and createtime >=' . strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':00:00') . ' and createtime <= ' . strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':59:59'))->sum('price');
				} else {
					$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and createtime >=' . strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':00:00') . ' and createtime <= ' . strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':59:59'))->count();
				}
				
				$dr = array('data' => $hour . '点 - ' . $nexthour . '点', 'count' => $count );
				$totalcount += $dr['count'];

				if ($maxcount < $dr['count']) {
					$maxcount = $dr['count'];
					$maxcount_date = $year . '年' . $month . '月' . $day . '日 ' . $hour . '点 - ' . $nexthour . '点';
				}

				$list[] = $dr;
				++$hour;
			}
		} else {
			if (!empty($year) && !empty($month)) {
				$lastday = get_last_day($year, $month);
				$d = 1;

				while ($d <= $lastday) {
					if(empty($type)) {
						$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and isparent=0 and createtime >=' . strtotime($year . '-' . $month . '-' . $d . ' 00:00:00') . ' and createtime <= ' . strtotime($year . '-' . $month . '-' . $d . ' 23:59:59'))->sum('price');
					} else {
						$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and isparent=0 and createtime >=' . strtotime($year . '-' . $month . '-' . $d . ' 00:00:00') . ' and createtime <= ' . strtotime($year . '-' . $month . '-' . $d . ' 23:59:59'))->count();
					}
					$dr = array('data' => $d, 'count' => $count);
					$totalcount += $dr['count'];

					if ($maxcount < $dr['count']) {
						$maxcount = $dr['count'];
						$maxcount_date = $year . '年' . $month . '月' . $d . '日';
					}

					$list[] = $dr;
					++$d;
				}
			} else {
				if (!empty($year)) {
					foreach ($months as $k => $m) {
						$lastday = get_last_day($year, $k + 1);
						if(empty($type)) {
							$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and createtime >=' . strtotime($year . '-' . $m['data'] . '-01 00:00:00') . ' and createtime <= ' . strtotime($year . '-' . $m['data'] . '-' . $lastday . ' 23:59:59'))->sum('price');
						} else {
							$count = Db::name('shop_order')->where('merchid = ' . $merch['id'] . ' and status>=1 and createtime >=' . strtotime($year . '-' . $m['data'] . '-01 00:00:00') . ' and createtime <= ' . strtotime($year . '-' . $m['data'] . '-' . $lastday . ' 23:59:59'))->count();
						}
						$dr = array('data' => $m['data'], 'count' => $count);
						$totalcount += $dr['count'];

						if ($maxcount < $dr['count']) {
							$maxcount = $dr['count'];
							$maxcount_date = $year . '年' . $m['data'] . '月';
						}

						$list[] = $dr;
					}
				}
			}
		}

		foreach ($list as $key => &$row) {
			$list[$key]['percent'] = number_format(($row['count'] / (empty($totalcount) ? 1 : $totalcount)) * 100, 2);
		}

		unset($row);
		$this->assign(['years'=>$years,'months'=>$months,'totalcount'=>$totalcount,'maxcount'=>$maxcount,'maxcount_date'=>$maxcount_date,'type'=>$type,'list'=>$list]);
		return $this->fetch('');
	}

	public function goods()
    {
    	$psize = 20;
		$merch = $this->merch;
		$condition = ' 1 and o.status>=1 and o.merchid = ' . $merch['id'];
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['datetime'])) {
			$starttime = strtotime($_GET['datetime']['start']);
			$endtime = strtotime($_GET['datetime']['end']);

			if (!empty($starttime)) {
				$condition .= ' AND o.createtime >= ' . $starttime;
			}

			if (!empty($endtime)) {
				$condition .= ' AND o.createtime <= ' . $endtime;
			}
		}

		if (!empty($_GET['title'])) {
			$_GET['title'] = trim($_GET['title']);
			$condition .= ' and g.title like "'. $_GET['title'] . '%"';
		}

		$orderby = (!isset($_GET['orderby']) ? 'og.price' : (empty($_GET['orderby']) ? 'og.price' : 'og.total'));

		$list = Db::name('shop_order_goods')->alias('og')->join('shop_order o','o.id = og.orderid','left')->join('shop_goods g','g.id = og.goodsid','left')->join('shop_goods_option op','op.id = og.optionid','left')->where($condition)->field('og.price,og.total,o.createtime,o.ordersn,g.title,g.thumb,g.goodssn,op.goodssn as optiongoodssn,op.title as optiontitle')->order($orderby,'desc')->paginate($psize);

		foreach ($list as $k => $row) {
			if (!empty($row['optiongoodssn'])) {
				$row['goodssn'] = $row['optiongoodssn'];
				$data = array();
	    		$data = $row;
	    		$list->offsetSet($k,$data);
			}
		}
		unset($row);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager]);
    	return $this->fetch('');
    }

    public function goods_rank()
    {
    	$page = input('page/d',1);
    	$psize = 20;
		$merch = $this->merch;
		$condition = ' and o.merchid = ' . $merch['id'];
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['datetime'])) {
			$starttime = strtotime($_GET['datetime']['start']);
			$endtime = strtotime($_GET['datetime']['end']);

			if (!empty($starttime)) {
				$condition .= ' AND o.createtime >= ' . $starttime;
			}

			if (!empty($endtime)) {
				$condition .= ' AND o.createtime <= ' . $endtime . ' ';
			}
		}

		$condition1 = ' 1 and g.merchid =  ' . $merch['id'];

		if (!empty($_GET['title'])) {
			$_GET['title'] = trim($_GET['title']);
			$condition1 .= ' and g.title like "%' . $_GET['title'] . '%"';
		}

		$orderby = (!isset($_GET['orderby']) ? 'money' : (empty($_GET['orderby']) ? 'money' : 'count'));

		$list = Db::name('shop_goods')->alias('g')->where($condition1)->field('g.id,g.title,g.thumb,(select ifnull(sum(og.price),0) from ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_order') . ' o on og.orderid=o.id  where o.status>=1 and og.goodsid=g.id ' . $condition . ')  as money,(select ifnull(sum(og.total),0) from ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_order') . ' o on og.orderid=o.id  where o.status>=1 and og.goodsid=g.id ' . $condition . ') as count')->order($orderby,'desc')->paginate($psize);
		$pager = $list->render();

    	$this->assign(['list'=>$list,'pager'=>$pager,'page'=>$page,'psize'=>$psize,'orderby'=>$orderby]);
    	return $this->fetch('');
    }

    public function goods_trans()
    {
		$psize = 20;
		$merch = $this->merch;

    	$condition = ' and o.merchid = ' . $merch['id'];
		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['datetime'])) {
			$starttime = strtotime($_GET['datetime']['start']);
			$endtime = strtotime($_GET['datetime']['end']);
			$condition .= ' AND o.createtime >=' . $starttime . ' AND o.createtime <= ' . $endtime . ' ';
		}

		$condition1 = ' 1 and g.merchid =  ' . $merch['id'];

		if (!empty($_GET['title'])) {
			$_GET['title'] = trim($_GET['title']);
			$condition1 .= ' and g.title like "%' . $_GET['title'] . '%"';
		}

		$orderby = (!isset($_GET['orderby']) ? 'desc' : (empty($_GET['orderby']) ? 'desc' : 'asc'));

		$list = Db::name('shop_goods')->alias('g')->where($condition1)->field('g.id,g.title,g.thumb,g.viewcount,(select sum(og.total) from  ' . tablename('shop_order_goods') . ' og left join ' . tablename('shop_order') . ' o on og.orderid=o.id  where o.status>=1 and og.goodsid=g.id ' . $condition . ')  as buycount')->order('buycount',$orderby)->paginate($psize);

		foreach ($list as $k => $row) {
			$row['percent'] = round(($row['buycount'] / (empty($row['viewcount']) ? 1 : $row['viewcount'])) * 100, 2);
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}
		unset($row);
		$pager = $list->render();

    	$this->assign(['list'=>$list,'pager'=>$pager,'page'=>$page,'orderby'=>$orderby]);
    	return $this->fetch('');
    }

}