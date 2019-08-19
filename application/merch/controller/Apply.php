<?php
/**
 * 结算
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Apply extends Base
{
	public function index()
    {        
        return $this->fetch('apply/index');
    }

    protected function applyData($status, $st) 
	{
		empty($status) && ($status = 1);
		$merch = $this->merch;
		$merchid = $merch['id'];
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		if ($st == 'main') {
			$st = '';
		} else {
			$st = '.' . $st;
		}
		$psize = 20;
		$condition = ' b.status= ' . $status . ' and b.merchid= ' . $merchid. ' and (b.creditstatus = 2 or b.creditstatus=0)';
		$keyword = trim($_GET['keyword']);
		if (!(empty($keyword))) 
		{
			$condition .= ' and b.applyno like "%' . $keyword . '%"';
		}
		if (empty($starttime) || empty($endtime)) 
		{
			$starttime = strtotime('-1 month');
			$endtime = time();
		}
		$timetype = $_GET['timetype'];
		if (!(empty($_GET['timetype']))) 
		{
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			if (!(empty($timetype))) 
			{
				$condition .= ' AND b.' . $timetype . ' >= ' . $starttime . ' AND b.' . $timetype . '  <= ' . $endtime;
			}
		}
		if (3 <= $status) 
		{
			$orderby = 'b.paytime';
		}
		else if (2 <= $status) 
		{
			$orderby = ' b.checktime';
		}
		else 
		{
			$orderby = 'b.applytime';
		}
		$applytitle = '';
		if ($status == 1) 
		{
			$applytitle = '待审核';
		}
		else if ($status == 2) 
		{
			$applytitle = '待打款';
		}
		else if ($status == 3) 
		{
			$applytitle = '已打款';
		}
		else if ($status == -1) 
		{
			$applytitle = '已无效';
		}
		$sql = 'select b.* from ' . tablename('shop_merch_bill') . ' b ' . ' left join ' . tablename('shop_merch') . ' u on b.merchid = u.id' . ' where 1 ' . $condition . ' ORDER BY ' . $orderby . ' desc ';

		$list = Db::name('shop_merch_bill')->alias('b')->join('shop_merch u','b.merchid = u.id','left')->field('b.*')->where($condition)->order($orderby,'desc')->paginate($psize);
		$pager = $list->render();
		if ($_GET['export'] == '1') 
		{
			plog('member.list', '导出结算数据');
			foreach ($list as &$row ) 
			{
				$row['applytime'] = date('Y-m-d H:i', $row['applytime']);
				$row['paytime'] = date('Y-m-d H:i', $row['paytime']);
				$row['typestr'] = $apply_type[$row['applytype']];
			}
			unset($row);
			$columns = array();
			$columns[] = array('title' => '商城信息', 'field' => 'merchname', 'width' => 12);
			$columns[] = array('title' => '姓名', 'field' => 'realname', 'width' => 12);
			$columns[] = array('title' => '手机号', 'field' => 'mobile', 'width' => 12);
			$columns[] = array('title' => '申请金额', 'field' => 'realprice', 'width' => 12);
			$columns[] = array('title' => '申请抽成后金额', 'field' => 'realpricerate', 'width' => 12);
			$columns[] = array('title' => '申请订单个数', 'field' => 'ordernum', 'width' => 16);
			$columns[] = array('title' => '提现方式', 'field' => 'typestr', 'width' => 12);
			if (1 < $status) 
			{
				$columns[] = array('title' => '通过申请金额', 'field' => 'passrealprice', 'width' => 12);
				$columns[] = array('title' => '通过申请抽成后金额', 'field' => 'passrealpricerate', 'width' => 12);
				$columns[] = array('title' => '通过申请订单个数', 'field' => 'passordernum', 'width' => 16);
			}
			if ($status == 3) 
			{
				$columns[] = array('title' => '实际打款金额', 'field' => 'finalprice', 'width' => 12);
			}
			$columns[] = array('title' => '抽成比例%', 'field' => 'payrate', 'width' => 12);
			$columns[] = array('title' => '申请时间', 'field' => 'applytime', 'width' => 16);
			if ($status == 3) 
			{
				$columns[] = array('title' => '最终打款时间', 'field' => 'paytime', 'width' => 12);
			}
			model('excel')->export($list, array('title' => '提现申请数据', 'columns' => $columns));
		}
		$this->assign(['list'=>$list,'pager'=>$pager,'status'=>$status,'st'=>$st,'keyword'=>$keyword,'timetype'=>$timetype,'starttime'=>$starttime,'endtime'=>$endtime]);
    	return $this->fetch('apply/list');
	}

	public function status1() 
	{
		$applyData = $this->applyData(1, 'status1');
		return $applyData;
	}

	public function status2() 
	{
		$applyData = $this->applyData(2, 'status2');
		return $applyData;
	}

	public function status3() 
	{
		$applyData = $this->applyData(3, 'status3');
		return $applyData;
	}

	public function status_1() 
	{
		$applyData = $this->applyData(-1, 'status_1');
		return $applyData;
	}

	public function add() 
	{
		$data = $this->post();
		return $data;
	}

	protected function post() 
	{
		$merch = $this->merch;
		$merchid = $merch['id'];
		$item = model('merch')->getMerchPrice($merchid, 1);
		$list = model('merch')->getMerchPriceList($merchid);
		$order_num = count($list);
		$set = model('common')->getPluginset('merch');
		$last_data = $this->getLastApply($merchid);
		$type_array = array();
		if ($set['applycashweixin'] == 1) 
		{
			$type_array[0]['title'] = '提现到微信钱包';
		}
		if ($set['applycashalipay'] == 1) 
		{
			$type_array[2]['title'] = '提现到支付宝';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 2) 
				{
					$type_last = $this->getLastApply($merchid, 2);
					if (!(empty($type_last))) 
					{
						$last_data['alipay'] = $type_last['alipay'];
					}
				}
			}
		}
		if ($set['applycashcard'] == 1) 
		{
			$type_array[3]['title'] = '提现到银行卡';
			if (!(empty($last_data))) 
			{
				if ($last_data['applytype'] != 3) 
				{
					$type_last = $this->getLastApply($merchid, 3);
					if (!(empty($type_last))) 
					{
						$last_data['bankname'] = $type_last['bankname'];
						$last_data['bankcard'] = $type_last['bankcard'];
					}
				}
			}
			$condition = ' 1 ';
			$banklist = Db::name('system_bank')->where($condition)->order('displayorder DESC')->select();
		}
		if (!(empty($last_data))) 
		{
			if (array_key_exists($last_data['applytype'], $type_array)) 
			{
				$type_array[$last_data['applytype']]['checked'] = 1;
			}
		}
		if (Request::instance()->isPost()) 
		{
			if (($item['realprice'] <= 0) || empty($list)) 
			{
				show_json(0, '您没有可提现的金额');
			}
			$applytype = intval($_POST['applytype']);
			if (!(array_key_exists($applytype, $type_array))) 
			{
				show_json(0, '未选择提现方式，请您选择提现方式后重试!');
			}
			$insert = array();
			$insert['creditstatus'] = 2;
			if ($applytype == 2) 
			{
				$realname = trim($_POST['realname']);
				$alipay = trim($_POST['alipay']);
				$alipay1 = trim($_POST['alipay1']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (empty($alipay)) 
				{
					show_json(0, '请填写支付宝帐号!');
				}
				if (empty($alipay1)) 
				{
					show_json(0, '请填写确认帐号!');
				}
				if ($alipay != $alipay1) 
				{
					show_json(0, '支付宝帐号与确认帐号不一致!');
				}
				$insert['applyrealname'] = $realname;
				$insert['alipay'] = $alipay;
			}
			else if ($applytype == 3) 
			{
				$realname = trim($_POST['realname']);
				$bankname = trim($_POST['bankname']);
				$bankcard = trim($_POST['bankcard']);
				$bankcard1 = trim($_POST['bankcard1']);
				if (empty($realname)) 
				{
					show_json(0, '请填写姓名!');
				}
				if (empty($bankname)) 
				{
					show_json(0, '请选择银行!');
				}
				if (empty($bankcard)) 
				{
					show_json(0, '请填写银行卡号!');
				}
				if (empty($bankcard1)) 
				{
					show_json(0, '请填写确认卡号!');
				}
				if ($bankcard != $bankcard1) 
				{
					show_json(0, '银行卡号与确认卡号不一致!');
				}
				$insert['applyrealname'] = $realname;
				$insert['bankname'] = $bankname;
				$insert['bankcard'] = $bankcard;
			}
			$insert['merchid'] = $merchid;
			$insert['applyno'] = model('common')->createNO('shop_merch_bill', 'applyno', 'MO');
			$insert['orderids'] = iserializer($item['orderids']);
			$insert['ordernum'] = $order_num;
			$insert['price'] = $item['price'];
			$insert['credit4price'] = $item['credit4price'];
			$insert['realprice'] = $item['realprice'];
			$insert['realpricerate'] = $item['realpricerate'];
			$insert['finalprice'] = $item['finalprice'] ? $item['finalprice'] : $item['realprice'];
			$insert['orderprice'] = $item['orderprice'];
			$insert['payrateprice'] = round(($item['realpricerate'] * $item['payrate']) / 100, 2);
			$insert['payrate'] = $item['payrate'];
			$insert['applytime'] = time();
			$insert['status'] = 1;
			$insert['applytype'] = $applytype;
			$billid = Db::name('shop_merch_bill')->insertGetId($insert);
			foreach ($list as $k => $v ) 
			{
				$orderid = $v['id'];
				$insert_data = array();
				$insert_data['billid'] = $billid;
				$insert_data['orderid'] = $orderid;
				$insert_data['ordermoney'] = $v['realprice'];
				Db::name('shop_merch_billo')->insert($insert_data);
				$change_order_data = array();
				$change_order_data['merchapply'] = 1;
				Db::name('shop_order')->where('id = ' . $orderid)->update($change_order_data);
			}
			$merch_user = Db::name('shop_merch')->where('id=' . $merchid)->find();
			model('notice')->sendMerchMessage(array('merchname' => $merch_user['merchname'], 'money' => $insert['realprice'], 'realname' => $merch_user['realname'], 'mobile' => $merch_user['mobile'], 'applytime' => time()), 'merch_apply_money');
			show_json(1, array('url' => url('merch/apply/status1')));
		}
		$this->assign(['item'=>$item,'list'=>$list,'order_num'=>$order_num,'set'=>$set,'last_data'=>$last_data,'type_array'=>$type_array,'banklist'=>$banklist]);
		return $this->fetch('apply/post');
	}

	public function detail()
	{
		$id = intval(input('id'));
		$status = intval(input('status'));
		$merch = $this->merch;
		$merchid = $merch['id'];
		$apply_type = array(0 => '微信钱包', 2 => '支付宝', 3 => '银行卡');
		$item = model('merch')->getOneApply($id);
		$orderids = iunserializer($item['orderids']);
		$keyword = trim(input('keyword'));
		$list = array();
		foreach ($orderids as $key => $value ) 
		{
			if ($item['creditstatus'] == 2) 
			{
				$data = model('merch')->getMerchPriceList($item['merchid'], $value, 10, $id);
			} else if ($item['creditstatus'] == 1) {
				$data = model('merch')->getMerchCreditList($item['merchid'], $value, 10, $item['creditrate'], $item['isbillcredit']);
			}
			if (!(empty($data))) 
			{
				$flag = 1;
				if (!(empty($keyword))) 
				{
					if (strpos(trim($data['ordersn']), $keyword) !== false) 
					{
						$flag = 1;
					}
					else 
					{
						$flag = 0;
					}
				}
				if ($flag) 
				{
					$list[] = $data;
				}
			}
		}
		if ($_GET['export'] == '1') 
		{
			foreach ($list as &$row ) 
			{
				$row['finishtime'] = date('Y-m-d H:i', $row['time_finish']);
			}
			$columns = array();
			$columns[] = array('title' => '订单编号', 'field' => 'ordersn', 'width' => 24);
			$columns[] = array('title' => '可提现金额', 'field' => 'realprice', 'width' => 24);
			$columns[] = array('title' => '抽成比例', 'field' => 'payrate', 'width' => 12);
			$columns[] = array('title' => '抽成后获得金额', 'field' => 'realpricerate', 'width' => 24);
			$columns[] = array('title' => '订单完成时间', 'field' => 'finishtime', 'width' => 24);
			$columns[] = array('title' => '订单商品总额', 'field' => 'goodsprice', 'width' => 24);
			$columns[] = array('title' => '快递金额', 'field' => 'dispatchprice', 'width' => 24);
			$columns[] = array('title' => '积分抵扣金额', 'field' => 'deductprice', 'width' => 24);
			$columns[] = array('title' => '余额抵扣金额', 'field' => 'deductcredit2', 'width' => 24);
			$columns[] = array('title' => '会员折扣金额', 'field' => 'discountprice', 'width' => 24);
			$columns[] = array('title' => '促销金额', 'field' => 'isdiscountprice', 'width' => 24);
			$columns[] = array('title' => '满减金额', 'field' => 'deductenough', 'width' => 24);
			$columns[] = array('title' => '实际支付金额', 'field' => 'price', 'width' => 24);
			$columns[] = array('title' => '商户满减金额', 'field' => 'merchdeductenough', 'width' => 24);
			$columns[] = array('title' => '商户优惠券金额', 'field' => 'merchcouponprice', 'width' => 24);
			$columns[] = array('title' => '分销佣金', 'field' => 'commission', 'width' => 24);
			model('excel')->export($list, array('title' => '提现申请订单数据-' . date('Y-m-d-H-i', time()), 'columns' => $columns));
		}
		$this->assign(['id'=>$id,'status'=>$status,'apply_type'=>$apply_type,'item'=>$item,'keyword'=>$keyword,'list'=>$list]);
		if ($item['creditstatus'] == 1) {
			return $this->fetch('apply/creditdetail');
		}
		return $this->fetch('apply/detail');
	}

	public function getLastApply($merchid, $applytype = -1) 
	{
		$sql = 'select applytype,alipay,bankname,bankcard,applyrealname from ' . tablename('shop_merch_bill') . ' where merchid=' . $merchid;
		if (-1 < $applytype) 
		{
			$sql .= ' and applytype=' . $applytype;
		}
		$sql .= ' order by id desc Limit 1';
		$data = Db::query($sql);
		return $data;
	}

    public function ajaxgettotalprice()
    {
    	$merch = $this->merch;
        $merchid = $merch["id"];
        $totals = model('merch')->getMerchOrderTotalPrice($merchid);
        show_json(1, $totals);
    }

    public function ajaxgettotalcredit()
    {
        $merch = $this->merch;
        $merchid = $merch["id"];
        $totals = model('merch')->getMerchCreditTotalPrice($merchid);
        show_json(1, $totals);
    }

    public function test()
    {
    	$merch = $this->merch;
        $merchid = $merch["id"];
        $totals = model('merch')->getMerchPriceList($merchid);
        dump($totals);
    }

}