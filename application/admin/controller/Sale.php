<?php
/**
 * 营销
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Db;
use think\Request;
class Sale extends Base
{
	public function index()
    {
    	header('location: ' . url('admin/sale/enoughred'));exit;
    }

    public function enoughred()
    {
    	if(Request::instance()->isPost()) 
		{
			$data = (is_array($_POST["data"]) ? $_POST["data"] : array( ));
			$data["enoughmoney"] = round(floatval($data["enoughmoney"]), 2);
			$data["enoughdeduct"] = round(floatval($data["enoughdeduct"]), 2);
			$enoughs = array( );
			$postenoughs = (is_array($_POST["enough"]) ? $_POST["enough"] : array( ));
			foreach( $postenoughs as $key => $value ) 
			{
				$enough = floatval($value);
				if( 0 < $enough ) 
				{
					$enoughs[] = array( "enough" => floatval($_POST["enough"][$key]), "give" => floatval($_POST["give"][$key]) );
				}
			}
			$data["enoughs"] = $enoughs;
			model('shop')->plog("sale.enough", "修改满额立减优惠");
			model("common")->updatePluginset(array( "sale" => $data ));
			show_json(1);
		}
		$areas = model("common")->getAreas();
		$data = model("common")->getPluginset("sale");
		$this->assign(['areas'=>$areas,'data'=>$data]);
		return $this->fetch('sale/enough');
    }

    public function enoughfree()
    {
    	if( Request::instance()->isPost() ) 
		{
			$data = (is_array($_POST["data"]) ? $_POST["data"] : array( ));
			$data["enoughfree"] = intval($data["enoughfree"]);
			$data["enoughorder"] = round(floatval($data["enoughorder"]), 2);
			$data["goodsids"] = $_POST["goodsid"];
			model('shop')->plog("sale.enough", "修改满额包邮优惠");
			model("common")->updatePluginset(array( "sale" => $data ));
			show_json(1);
		}
		$data = model("common")->getPluginset("sale");
		if( !empty($data["goodsids"]) ) 
		{
			$goods = Db::name('shop_goods')->where("id IN (" . implode(",", $data["goodsids"]) . ")")->field('id,title,thumb')->select();
		}
		$area_set = model("util")->get_area_config_set();
		$new_area = intval($area_set["new_area"]);
		$address_street = intval($area_set["address_street"]);
		$areas = model("common")->getAreas();
		$this->assign(['areas'=>$areas,'data'=>$data,'goods'=>$goods,'new_area'=>$new_area,'address_street'=>$address_street]);
    	return $this->fetch('sale/enoughfree');
    }

    public function credit1()
    {
    	if( Request::instance()->isPost() ) 
		{
			$enough1 = array( );
			$postenough1 = (is_array($_POST["enough1_1"]) ? $_POST["enough1_1"] : array( ));
			foreach( $postenough1 as $key => $value ) 
			{
				$enough = floatval($value);
				if( 0 < $enough ) 
				{
					$enough1[] = array( "enough1_1" => floatval($_POST["enough1_1"][$key]), "enough1_2" => floatval($_POST["enough1_2"][$key]), "give1" => intval($_POST["give1"][$key]) );
				}
			}
			$data["isgoodspoint"] = intval($_POST["isgoodspoint"]);
			$data["enough1"] = $enough1;
			$enough2 = array( );
			$postenough2 = (is_array($_POST["enough2_1"]) ? $_POST["enough2_1"] : array( ));
			foreach( $postenough2 as $key => $value ) 
			{
				$enough = floatval($value);
				if( 0 < $enough ) 
				{
					$enough2[] = array( "enough2_1" => floatval($_POST["enough2_1"][$key]), "enough2_2" => floatval($_POST["enough2_2"][$key]), "give2" => intval($_POST["give2"][$key]) );
				}
			}
			if( !empty($enough2) ) 
			{
				model("common")->updateSysset(array( "trade" => array( "credit" => 0 ) ));
			}
			$data["enough1"] = $enough1;
			$data["enough2"] = $enough2;
			$data["paytype"] = (is_array($_POST["paytype"]) ? $_POST["paytype"] : array( ));
			model("common")->updatePluginset(array( "sale" => array( "credit1" => iserializer($data) ) ));
			model('shop')->plog("sale.credit1.edit", "修改基本积分活动配置");
			show_json(1);
		}
		$data = model("common")->getPluginset("sale");
		$credit1 = iunserializer($data["credit1"]);
		$enough1 = (empty($credit1["enough1"]) ? array( ) : $credit1["enough1"]);
		$enough2 = (empty($credit1["enough2"]) ? array( ) : $credit1["enough2"]);
		$this->assign(['data'=>$data,'credit1'=>$credit1,'enough1'=>$enough1,'enough2'=>$enough2]);
    	return $this->fetch('sale/credit1');
    }

    public function package()
    {
    	$psize = 20;
		$condition = ' 1 ';
		$type = trim($_GET['type']);
		if ($type == 'ing') {
			$condition .= ' and starttime <= ' . time() . ' and endtime >= ' . time() . ' and deleted = 0 ';
		} else if ($type == 'none') {
			$condition .= ' and starttime > ' . time() . ' and deleted = 0 ';
		} else {
			if ($type == 'end') {
				$condition .= ' and endtime < ' . time() . ' or deleted = 1 ';
			}
		}

		if (!empty($_GET['keyword'])) {
			$keyword = trim($_GET['keyword']);
			$condition .= ' AND title LIKE "%' . trim($keyword) . '%"';
		}

		$packages = Db::name('shop_package')->where($condition)->order('displayorder DESC,id DESC')->paginate($psize);
		$pager = $packages->render();
		$this->assign(['packages'=>$packages,'pager'=>$pager,'type'=>$type,'keyword'=>$keyword]);
    	return $this->fetch('sale/package/index');
    }

    public function packageadd()
	{
		$data = $this->packagepost();
		return $data;
	}

	public function packageedit()
	{
		$data = $this->packagepost();
		return $data;
	}

	protected function packagepost()
	{
		$type = trim($_GET['type']);
		$id = intval($_GET['id']);
		if ($_W['ispost']) {

			$data = array('uniacid' => $uniacid, 'displayorder' => intval($_GPC['displayorder']), 'title' => trim($_GPC['title']), 'thumb' => trim($_GPC['thumb']), 'price' => floatval($_GPC['price']), 'goodsid' => $_GPC['goodsid'], 'cash' => intval($_GPC['cash']), 'dispatchtype' => intval($_GPC['dispatchtype']), 'freight' => floatval($_GPC['freight']), 'starttime' => strtotime($_GPC['starttime']), 'endtime' => strtotime($_GPC['endtime']), 'status' => intval($_GPC['status']), 'share_title' => trim($_GPC['share_title']), 'share_icon' => trim($_GPC['share_icon']), 'share_desc' => trim($_GPC['share_desc']));

			if ($data['thumb'] || $data['share_icon']) {

				$data['thumb'] = trim($data['thumb']);

				$data['share_icon'] = trim($data['share_icon']);

			}



			if (empty($_GPC['goodsid'])) {

				show_json(0, '套餐商品不能为空！');

			}

			else {

				$goodsid = $data['goodsid'];

				$data['goodsid'] = is_array($_GPC['goodsid']) ? implode(',', $_GPC['goodsid']) : 0;

			}



			$option = $_GPC['packagegoods'];



			foreach ($goodsid as $key => $value) {

				$good_data = pdo_fetch("select title,thumb,marketprice,goodssn,productsn,hasoption,merchid\r\n                            from " . tablename('shop_goods') . ' where id = ' . $value . ' and uniacid = ' . $uniacid . ' ');

				if (0 < $good_data['merchid'] && $data['dispatchtype'] == 0) {

					show_json(0, '套餐中包含多商户商品，请在“运费设置”中选择运费模板！');

				}



				if (empty($data['thumb'])) {

					$data['thumb'] = trim($good_data['thumb']);

				}



				$good_data['option'] = $option[$value] ? $option[$value] : '';

				if ($good_data['hasoption'] && empty($good_data['option'])) {

					show_json(0, '请选择商品规格！');

				}

			}



			if (!empty($id)) {

				foreach ($goodsid as $key => $value) {

					$packagenum = pdo_fetchcolumn('select count(1) from ' . tablename('shop_package_goods') . ' where goodsid = :goodsid and uniacid = :uniacid', array(':goodsid' => $value, ':uniacid' => $value));

					$thisgoods = pdo_fetch('select id from ' . tablename('shop_package_goods') . ' where pid = :pid and goodsid = :goodsid and uniacid = :uniacid ', array(':pid' => $id, 'goodsid' => $value, ':uniacid' => $uniacid));

					if (!$thisgoods && 3 <= $packagenum) {

						show_json(0, '同一件商品最多参与三个套餐活动!');

					}

				}



				$package_update = pdo_update('shop_package', $data, array('id' => $id, 'uniacid' => $uniacid));

				$package_goods_del = pdo_delete('shop_package_goods', array('pid' => $id, 'uniacid' => $uniacid));

				$package_goods_option_del = pdo_delete('shop_package_goods_option', array('pid' => $id, 'goodsid' => $value, 'uniacid' => $uniacid));



				foreach ($goodsid as $key => $value) {

					$good_data = pdo_fetch("select title,thumb,marketprice,goodssn,productsn,hasoption\r\n                            from " . tablename('shop_goods') . ' where id = ' . $value . ' and uniacid = ' . $uniacid . ' ');

					$good_data['uniacid'] = $uniacid;

					$good_data['goodsid'] = $value;

					$good_data['pid'] = $id;

					$good_data['option'] = $option[$value] ? $option[$value] : '';

					if (empty($good_data['option']) && !$good_data['hasoption']) {

						$packgoodStr = $_GPC['packgoods' . $value . ''];

						$packgoodArray = explode(',', $packgoodStr);

						$good_data['packageprice'] = $packgoodArray[0];

						$good_data['commission1'] = $packgoodArray[1];

						$good_data['commission2'] = $packgoodArray[2];

						$good_data['commission3'] = $packgoodArray[3];

					}



					$package_goods_insert = pdo_insert('shop_package_goods', $good_data);



					if (!empty($good_data['option'])) {

						$packageGoodsOption = array_filter(explode(',', $good_data['option']));



						foreach ($packageGoodsOption as $k => $val) {

							$op = pdo_fetch('SELECT id,title,marketprice,goodssn,productsn FROM ' . tablename('shop_goods_option') . "\r\n                                WHERE uniacid = " . $uniacid . ' and id = ' . $val . ' ');

							$optionStr = $_GPC['packagegoodsoption' . $val . ''];

							$optionArray = explode(',', $optionStr);

							$optionData = array('uniacid' => $uniacid, 'goodsid' => $value, 'pid' => $id, 'title' => $op['title'], 'optionid' => $val, 'marketprice' => $op['marketprice'], 'packageprice' => $optionArray[0], 'commission1' => $optionArray[1], 'commission2' => $optionArray[2], 'commission3' => $optionArray[3]);

							$package_goods_option_insert = pdo_insert('shop_package_goods_option', $optionData);



							if (!$package_goods_option_insert) {

								show_json(0, '套餐商品规格添加失败！');

							}

						}

					}



					if (!$package_goods_insert) {

						show_json(0, '套餐商品编辑失败！');

					}

				}



				plog('sale.package.edit', '编辑套餐 ID: ' . $id . ' <br/>套餐名称: ' . $data['title']);

			}

			else {

				foreach ($goodsid as $key => $value) {

					$packagenum = pdo_fetchcolumn('select count(1) from ' . tablename('shop_package_goods') . ' where uniacid = ' . $uniacid . ' and goodsid = ' . $value . ' ');



					if (3 <= $packagenum) {

						show_json(0, '同一件商品最多参与三个套餐活动!');

					}

				}



				$package_insert = pdo_insert('shop_package', $data);



				if (!$package_insert) {

					show_json(0, '套餐添加失败！');

				}



				$id = pdo_insertid();



				foreach ($goodsid as $key => $value) {

					$good_data = pdo_fetch("select title,thumb,marketprice,goodssn,productsn,hasoption\r\n                            from " . tablename('shop_goods') . ' where id = ' . $value . ' and uniacid = ' . $uniacid . ' ');



					if (empty($data['thumb'])) {

						$data['thumb'] = trim($good_data['thumb']);

					}



					$good_data['uniacid'] = $uniacid;

					$good_data['goodsid'] = $value;

					$good_data['pid'] = $id;

					$good_data['option'] = $option[$value] ? $option[$value] : '';

					if (empty($good_data['option']) && !$good_data['hasoption']) {

						$packgoodStr = $_GPC['packgoods' . $value . ''];

						$packgoodArray = explode(',', $packgoodStr);

						$good_data['packageprice'] = $packgoodArray[0];

						$good_data['commission1'] = $packgoodArray[1];

						$good_data['commission2'] = $packgoodArray[2];

						$good_data['commission3'] = $packgoodArray[3];

					}



					$package_goods_insert = pdo_insert('shop_package_goods', $good_data);

					$gid = pdo_insertid();



					if (!empty($good_data['option'])) {

						$packageGoodsOption = array_filter(explode(',', $good_data['option']));



						foreach ($packageGoodsOption as $k => $val) {

							$op = pdo_fetch('SELECT id,title,marketprice,goodssn,productsn FROM ' . tablename('shop_goods_option') . "\r\n                                WHERE uniacid = " . $uniacid . ' and id = ' . $val . ' ');

							$optionStr = $_GPC['packagegoodsoption' . $val . ''];

							$optionArray = explode(',', $optionStr);

							$optionData = array('uniacid' => $uniacid, 'goodsid' => $value, 'pid' => $id, 'title' => $op['title'], 'optionid' => $val, 'marketprice' => $op['marketprice'], 'packageprice' => $optionArray[0], 'commission1' => $optionArray[1], 'commission2' => $optionArray[2], 'commission3' => $optionArray[3]);

							$package_goods_option_insert = pdo_insert('shop_package_goods_option', $optionData);



							if (!$package_goods_option_insert) {

								show_json(0, '套餐商品规格添加失败！');

							}

						}

					}



					if (!$package_goods_insert) {

						show_json(0, '套餐商品添加失败！');

					}

				}



				plog('sale.package.add', '添加套餐 ID: ' . $id . '  <br/>套餐名称: ' . $data['title']);

			}



			show_json(1, array('url' => webUrl('sale/package/edit', array('type' => $type, 'id' => $id))));

		}



		$item = pdo_fetch('SELECT * FROM ' . tablename('shop_package') . ' WHERE uniacid = ' . $uniacid . (' and id = ' . $id . ' '));



		if ($item) {

			$item = set_medias($item, array('thumb'));

			$package_goods = array();

			$package_goods = pdo_fetchall("select id,pid,title,thumb,packageprice,hasoption,goodsid,`option`,commission1,commission2,commission3\r\n                            from " . tablename('shop_package_goods') . ' where pid = ' . $id . ' and uniacid = ' . $uniacid . ' ');



			foreach ($package_goods as $key => $value) {

				if ($value['hasoption']) {

					$package_goods[$key]['optiontitle'] = pdo_fetchall("select id,goodsid,optionid,pid,packageprice,title,marketprice,commission1,commission2,commission3\r\n                            from " . tablename('shop_package_goods_option') . ' where pid = ' . $id . ' and goodsid = ' . $value['goodsid'] . ' and uniacid = ' . $uniacid . ' ');

				}

			}

		}
		return $this->fetch('sale/package/post');
	}

	public function packagedelete()
	{
		$id = intval(input('id'));



		if (empty($id)) {

			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;

		}



		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('shop_package') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);



		foreach ($items as $item) {

			pdo_update('shop_package', array('deleted' => 1, 'status' => 0), array('id' => $item['id']));

			plog('sale.package.delete', '删除套餐 ID: ' . $item['id'] . ' 套餐名称: ' . $item['title'] . ' ');

		}



		show_json(1, array('url' => referer()));

	}



	public function packagestatus()
	{
		$id = intval($_GPC['id']);



		if (empty($id)) {

			$id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;

		}



		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('shop_package') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);



		foreach ($items as $item) {

			pdo_update('shop_package', array('status' => intval($_GPC['status'])), array('id' => $item['id']));

			plog('sale.package.edit', '修改套餐状态<br/>ID: ' . $item['id'] . '<br/>套餐名称: ' . $item['title'] . '<br/>状态: ' . $_GPC['status'] == 1 ? '上架' : '下架');

		}



		show_json(1, array('url' => referer()));

	}



	public function packagedelete1()
	{
		$id = intval($_GPC['id']);



		if (empty($id)) {

			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;

		}



		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('shop_package') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);



		foreach ($items as $item) {

			pdo_delete('shop_package', array('id' => $item['id']));

			pdo_delete('shop_package_goods', array('pid' => $item['id']));

			pdo_delete('shop_package_goods_option', array('pid' => $item['id']));

			plog('sale.package.edit', '彻底删除套餐<br/>ID: ' . $item['id'] . '<br/>套餐名称: ' . $item['title']);

		}



		show_json(1, array('url' => referer()));

	}



	public function packagerestore()
	{
		$id = intval($_GPC['id']);



		if (empty($id)) {

			$id = is_array($_GPC['ids']) ? implode(',', $_GPC['ids']) : 0;

		}



		$items = pdo_fetchall('SELECT id,title FROM ' . tablename('shop_package') . (' WHERE id in( ' . $id . ' ) AND uniacid=') . $_W['uniacid']);



		foreach ($items as $item) {

			pdo_update('shop_package', array('deleted' => 0), array('id' => $item['id']));

			plog('sale.package.edit', '恢复套餐<br/>ID: ' . $item['id'] . '<br/>套餐名称: ' . $item['title']);

		}



		show_json(1, array('url' => referer()));

	}



	public function packagechange()
	{
		$id = intval($_GPC['id']);



		if (empty($id)) {

			show_json(0, array('message' => '参数错误'));

		}



		$type = trim($_GPC['typechange']);

		$value = trim($_GPC['value']);



		if (!in_array($type, array('title', 'displayorder', 'price'))) {

			show_json(0, array('message' => '参数错误'));

		}



		$package = pdo_fetch('select id from ' . tablename('shop_package') . ' where id=:id and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));



		if (empty($package)) {

			show_json(0, array('message' => '参数错误'));

		}



		pdo_update('shop_package', array($type => $value), array('id' => $id));

		show_json(1);

	}



	public function packagequery()
	{
		$kwd = trim($_GPC['keyword']);

		$pindex = max(1, intval($_GPC['page']));

		$psize = 8;

		$params = array();

		$params[':uniacid'] = $uniacid;

		$condition = ' and status=1 and deleted=0 and uniacid=:uniacid ';



		if (!empty($kwd)) {

			$condition .= ' AND (`title` LIKE :keywords OR `keywords` LIKE :keywords)';

			$params[':keywords'] = '%' . $kwd . '%';

		}



		$ds = pdo_fetchall("SELECT id,title,thumb,marketprice,total,goodssn,productsn,`type`,isdiscount,istime,isverify,share_title,share_icon,description,hasoption,nocommission,groupstype,merchid\r\n            FROM " . tablename('shop_goods') . ("\r\n            WHERE 1 " . $condition . ' ORDER BY displayorder DESC,id DESC LIMIT ') . ($pindex - 1) * $psize . ',' . $psize, $params);



		foreach ($ds as $key => $row) {

			if (0 < $row['merchid']) {

				$merch = pdo_fetch('select merchname from ' . tablename('shop_merch_user') . ' where id = :merchid and uniacid = :uniacid ', array(':merchid' => $row['merchid'], ':uniacid' => $uniacid));

				$ds[$key]['merchname'] = $merch['merchname'];

			}

		}



		$total = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename('shop_goods') . ' WHERE 1 ' . $condition . ' ', $params);

		$pager = pagination2($total, $pindex, $psize, '', array('before' => 5, 'after' => 4, 'ajaxcallback' => 'select_page', 'callbackfuncname' => 'select_page'));

		$ds = set_medias($ds, array('thumb'));

		return $this->fetch('sale/package/query');
	}



	public function packagehasoption()
	{
		$goodsid = intval($_GPC['goodsid']);

		$pid = intval($_GPC['pid']);

		$hasoption = 0;

		$params = array(':uniacid' => $uniacid, ':goodsid' => $goodsid);

		$commission_level = 0;



		if (p('commission')) {

			$data = m('common')->getPluginset('commission');

			$commission_level = $data['level'];

		}



		$goods = pdo_fetch('select id,title,marketprice,hasoption,nocommission from ' . tablename('shop_goods') . ' where uniacid = :uniacid and id = :goodsid ', $params);



		if (!empty($pid)) {

			$packgoods = pdo_fetch('select id,title,packageprice,commission1,commission2,commission3,`option`,goodsid from ' . tablename('shop_package_goods') . "\r\n                        where pid = " . $pid . ' and uniacid = :uniacid and goodsid = :goodsid ', $params);

		}

		else {

			$packgoods = array('title' => $goods['title'], 'marketprice' => $goods['marketprice'], 'packageprice' => 0, 'commission1' => 0, 'commission2' => 0, 'commission3' => 0);

		}



		if ($goods['hasoption']) {

			$hasoption = 1;

			$option = array();

			$option = pdo_fetchall('SELECT id,title,marketprice,specs,displayorder FROM ' . tablename('shop_goods_option') . "\r\n            WHERE uniacid = :uniacid and goodsid = :goodsid  ORDER BY displayorder DESC,id DESC ", $params);

			$package_option = pdo_fetchall('SELECT id,uniacid,goodsid,optionid,pid,title,marketprice,packageprice,commission1,commission2,commission3 FROM ' . tablename('shop_package_goods_option') . "\r\n            WHERE uniacid = :uniacid and goodsid = :goodsid  and pid = " . $pid . ' ', $params);



			foreach ($option as $key => $value) {

				foreach ($package_option as $k => $val) {

					if ($value['id'] == $val['optionid']) {

						$option[$key]['packageprice'] = $val['packageprice'];

						$option[$key]['commission1'] = $val['commission1'];

						$option[$key]['commission2'] = $val['commission2'];

						$option[$key]['commission3'] = $val['commission3'];

						continue;

					}

				}



				if (strpos($packgoods['option'], $value['id']) !== false) {

					$option[$key]['isoption'] = 1;

				}

			}

		}

		else {

			$packgoods['marketprice'] = $goods['marketprice'];

		}



		return $this->fetch('sale/package/hasoption');

	}

	public function packageoption()
	{
		$options = is_array($_GPC['option']) ? implode(',', array_filter($_GPC['option'])) : 0;
		$options = intval($options);
		$option = pdo_fetch('SELECT id,title FROM ' . tablename('shop_goods_option') . "\r\n            WHERE uniacid = " . $uniacid . ' and id = ' . $options . '  ORDER BY displayorder DESC,id DESC LIMIT 1');
		show_json(1, $option);
	}

	public function coupon()
	{
		$psize = 20;
		$condition = " merchid=0 ";
		if( !empty($_GET["keyword"]) ) 
		{
			$keyword = trim($_GET["keyword"]);
			$condition .= " AND couponname LIKE \"%" . trim($keyword) . "%\"";
		}
		if( !empty($_GET["catid"]) ) 
		{
			$catid = trim($_GET["catid"]);
			$condition .= " AND catid = " . (int) $catid;
		}
		if( empty($starttime) || empty($endtime) ) 
		{
			$starttime = strtotime("-1 month");
			$endtime = time();
		}
		if( !empty($_GET["time"]["start"]) && !empty($_GET["time"]["end"]) ) 
		{
			$starttime = strtotime($_GET["time"]["start"]);
			$endtime = strtotime($_GET["time"]["end"]);
			if( !empty($starttime) ) 
			{
				$condition .= " AND createtime >= " . $starttime;
			}
			if( !empty($endtime) ) 
			{
				$condition .= " AND createtime <= " . $endtime;
			}
		}
		if( $_GET["gettype"] != "" ) 
		{
			$condition .= " AND gettype = " . intval($_GET["gettype"]);
		}
		if( $_GET["type"] != "" ) 
		{
			$condition .= " AND coupontype = " . intval($_GET["type"]);
		}

		$list = Db::name('shop_coupon')->where($condition)->order('displayorder DESC,id DESC')->paginate($psize);
		$pager = $list->render();
		foreach( $list as $k => $row ) 
		{
			$row["gettotal"] = Db::name('shop_coupon_data')->where('couponid = ' . $row['id'])->count();
			$row["usetotal"] = Db::name('shop_coupon_data')->where('used = 1 and couponid=' . $row['id'])->count();
			$row["pwdjoins"] = Db::name('shop_coupon_guess')->where('couponid = ' . $row['id'])->count();
			$row["pwdoks"] = Db::name('shop_coupon_guess')->where('couponid = ' . $row['id'] . ' and ok = 1')->count();
			$data = array();
    		$data = $row;
    		$list->offsetSet($k,$data);
		}
		unset($row);
		$category = Db::name('shop_coupon_category')->where('merchid = 0 ')->order('id desc')->select();
		$this->assign(['list'=>$list,'pager'=>$pager,'keyword'=>$keyword,'catid'=>$catid,'starttime'=>$starttime,'endtime'=>$endtime,'gettype'=>intval($_GET["gettype"]),'type'=>intval($_GET["type"]),'category'=>$category]);
		return $this->fetch('sale/coupon/index');
	}

	public function couponadd() 
	{
		$data = $this->couponpost();
		return $data;
	}
	public function couponedit() 
	{
		$data = $this->couponpost();
		return $data;
	}
	protected function couponpost() 
	{
		$id = intval(input('id'));
		$type = intval(input('type'));
		$tab = trim(input('tab'));
		if( Request::instance()->isPost() ) 
		{
			$data = array( "couponname" => trim($_POST["couponname"]), "coupontype" => intval($_POST["coupontype"]), "catid" => intval($_POST["catid"]), "timelimit" => intval($_POST["timelimit"]), "usetype" => intval($_POST["usetype"]), "returntype" => 0, "enough" => trim($_POST["enough"]), "timedays" => intval($_POST["timedays"]), "timestart" => strtotime($_POST["time"]["start"]), "timeend" => strtotime($_POST["time"]["end"]) + 86399, "backtype" => intval($_POST["backtype"]), "deduct" => trim($_POST["deduct"]), "discount" => trim($_POST["discount"]), "backmoney" => trim($_POST["backmoney"]), "backcredit" => trim($_POST["backcredit"]), "backredpack" => trim($_POST["backredpack"]), "backwhen" => intval($_POST["backwhen"]), "gettype" => intval($_POST["gettype"]), "getmax" => intval($_POST["getmax"]), "credit" => intval($_POST["credit"]), "money" => trim($_POST["money"]), "usecredit2" => intval($_POST["usecredit2"]), "total" => intval($_POST["total"]), "bgcolor" => trim($_POST["bgcolor"]), "thumb" => trim($_POST["thumb"]), "remark" => trim($_POST["remark"]), "desc" => trim($_POST["desc"]), "descnoset" => intval($_POST["descnoset"]), "status" => intval($_POST["status"]), "resptitle" => trim($_POST["resptitle"]), "respthumb" => trim($_POST["respthumb"]), "respdesc" => trim($_POST["respdesc"]), "respurl" => trim($_POST["respurl"]), "pwdkey2" => trim($_POST["pwdkey2"]), "pwdwords" => trim($_POST["pwdwords"]), "pwdask" => trim($_POST["pwdask"]), "pwdsuc" => trim($_POST["pwdsuc"]), "pwdfail" => trim($_POST["pwdfail"]), "pwdfull" => trim($_POST["pwdfull"]), "pwdurl" => trim($_POST["pwdurl"]), "pwdtimes" => intval($_POST["pwdtimes"]), "pwdopen" => intval($_POST["pwdopen"]), "pwdown" => trim($_POST["pwdown"]), "pwdexit" => trim($_POST["pwdexit"]), "pwdexitstr" => trim($_POST["pwdexitstr"]), "displayorder" => intval($_POST["displayorder"]), "tagtitle" => $_POST["tagtitle"], "settitlecolor" => intval($_POST["settitlecolor"]), "titlecolor" => $_POST["titlecolor"], "limitdiscounttype" => intval($_POST["limitdiscounttype"]), "quickget" => intval($_POST["quickget"]) );
			$limitgoodcatetype = intval($_POST["limitgoodcatetype"]);
			$limitgoodtype = intval($_POST["limitgoodtype"]);
			$data["limitgoodcatetype"] = $limitgoodcatetype;
			$data["limitgoodtype"] = $limitgoodtype;
			if( $limitgoodcatetype == 1 || $limitgoodcatetype == 2 ) 
			{
				$data["limitgoodcateids"] = "";
				$cates = array( );
				if( is_array($_POST["cates"]) ) 
				{
					$cates = $_POST["cates"];
					$data["limitgoodcateids"] = implode(",", $cates);
				}
			} else {
				$data["limitgoodcateids"] = "";
			}
			if( $limitgoodtype == 1 || $limitgoodtype == 2 ) 
			{
				$data["limitgoodids"] = "";
				$goodids = array( );
				if( is_array($_POST["goodsid"]) ) 
				{
					$goodids = $_POST["goodsid"];
					$data["limitgoodids"] = implode(",", $goodids);
				}
			} else {
				$data["limitgoodids"] = "";
			}
			$islimitlevel = intval($_POST["islimitlevel"]);
			$data["islimitlevel"] = $islimitlevel;
			if( $islimitlevel == 1 ) 
			{
				if( is_array($_POST["limitmemberlevels"]) ) 
				{
					$data["limitmemberlevels"] = implode(",", $_POST["limitmemberlevels"]);
				}
				else 
				{
					$data["limitmemberlevels"] = "";
				}
				if( is_array($_POST["limitagentlevels"]) ) 
				{
					$data["limitagentlevels"] = implode(",", $_POST["limitagentlevels"]);
				}
				else 
				{
					$data["limitagentlevels"] = "";
				}
				if( is_array($_POST["limitpartnerlevels"]) ) 
				{
					$data["limitpartnerlevels"] = implode(",", $_POST["limitpartnerlevels"]);
				}
				else 
				{
					$data["limitpartnerlevels"] = "";
				}
				if( is_array($_POST["limitaagentlevels"]) ) 
				{
					$data["limitaagentlevels"] = implode(",", $_POST["limitaagentlevels"]);
				}
				else 
				{
					$data["limitaagentlevels"] = "";
				}
			}
			else 
			{
				$data["limitmemberlevels"] = "";
				$data["limitagentlevels"] = "";
				$data["limitpartnerlevels"] = "";
				$data["limitaagentlevels"] = "";
			}
			if( 10 < $data["discount"] || $data["discount"] < 0 ) 
			{
				show_json(0, "您好,您输入的折扣范围不对! 请输入 0.1 ~ 10 之间数");
			}
			if( !empty($id) ) {
				if( !empty($data["pwdkey2"]) ) 
				{
					$pwdkey2 = Db::name('shop_coupon')->where('id = ' . $id)->field('pwdkey2')->find();
					if( $pwdkey2["pwdkey2"] != $data["pwdkey2"] ) 
					{
						
					}
				}
				Db::name('shop_coupon')->where('id = ' . $id)->update($data);
				model('shop')->plog("sale.coupon.edit", "编辑优惠券 ID: " . $id . " <br/>优惠券名称: " . $data["couponname"]);
			} else {
				if( !empty($data["pwdkey2"]) ) {
					if( !empty($keyword) ) 
					{
						
					}
				}
				$data["createtime"] = time();
				$id = Db::name('shop_coupon')->insertGetId($data);
				model('shop')->plog("sale.coupon.add", "添加优惠券 ID: " . $id . "  <br/>优惠券名称: " . $data["couponname"]);
			}
			show_json(1, array( "url" => url("admin/sale/couponedit", array( "id" => $id, "tab" => str_replace("#tab_", "", $_GET["tab"]) )) ));
		}
		$goods = array();
		$item = Db::name('shop_coupon')->where('id = ' . $id . ' and merchid = 0')->find();
		if( empty($item) ) {
			$starttime = time();
			$endtime = strtotime(date("Y-m-d H:i:s", $starttime) . "+7 days");
		} else {
			$type = $item["coupontype"];
			$starttime = $item["timestart"];
			$endtime = $item["timeend"];
			if( $item["limitgoodcatetype"] == 1 || $item["limitgoodcatetype"] == 2 ) 
			{
				$cates = array( );
				$cates = explode(",", $item["limitgoodcateids"]);
			}
			if( ($item["limitgoodtype"] == 1 || $item["limitgoodtype"] == 2) && $item["limitgoodids"] ) 
			{
				$goods = Db::name('shop_goods')->where("id in (" . $item["limitgoodids"] . ") ")->field('id,title,thumb')->select();
			}
			$limitmemberlevels = explode(",", $item["limitmemberlevels"]);
			$limitagentlevels = explode(",", $item["limitagentlevels"]);
			$limitpartnerlevels = explode(",", $item["limitpartnerlevels"]);
			$limitaagentlevels = explode(",", $item["limitaagentlevels"]);			
			$item["desc"] = trim($item["desc"]);
		}
		$category = Db::name('shop_coupon_category')->where('merchid = 0')->order('id desc')->select();
		$goodcategorys = model("shop")->getFullCategory(true, true);
		$shop = $this->shopset;
		$shop = $shop['shop'];
		$levels = model("member")->getLevels();
		$this->assign(['item'=>$item,'tab'=>$tab,'starttime'=>$starttime,'endtime'=>$endtime,'type'=>$type,'category'=>$category,'goodcategorys'=>$goodcategorys,'shop'=>$shop,'levels'=>$levels,'limitmemberlevels'=>$limitmemberlevels,'limitagentlevels'=>$limitagentlevels,'limitpartnerlevels'=>$limitpartnerlevels,'limitaagentlevels'=>$limitaagentlevels]);
		return $this->fetch('sale/coupon/post');
	}

	public function coupondelete() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$items = Db::name('shop_coupon')->where("id in( " . $id . " ) and merchid=0")->field('id,couponname')->select();
		foreach( $items as $item ) {
			Db::name('shop_coupon')->where('id = ' . $item['id'])->delete();
			Db::name('shop_coupon_data')->where('couponid = ' . $item['id'])->delete();
			model('shop')->plog("sale.coupon.delete", "删除优惠券 ID: " . $id . "  <br/>优惠券名称: " . $item["couponname"] . " ");
		}
		show_json(1, array( "url" => url("admin/sale/coupon") ));
	}

	public function coupondisplayorder() 
	{
		$id = intval(input('id'));
		if( empty($id) ) 
		{
			$id = (is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0);
		}
		$displayorder = intval(input('value'));
		$items = Db::name('shop_coupon')->where("id in( " . $id . " ) and merchid=0")->field('id,couponname')->select();
		foreach( $items as $item ) 
		{
			Db::name('shop_coupon')->where('id = ' . $item['id'])->update(array( "displayorder" => $displayorder ));
			model('shop')->plog("sale.coupon.displayorder", "修改优惠券排序 ID: " . $item["id"] . " 名称: " . $item["couponname"] . " 排序: " . $displayorder . " ");
		}
		show_json(1);
	}

	public function query() 
	{
		$kwd = trim(input('keyword'));
		$diy = intval(input('diy'));
		$live = intval(input('live'));
		$merch = $_GET["merch"];
		if( $merch ) 
		{
			$condition = " 1 ";
			if( $diy ) 
			{
				$condition .= " and c.gettype = 1 ";
			}
		}
		else 
		{
			$condition = " 1 and merchid=0";
			if( $diy ) 
			{
				$condition .= " and gettype = 1 ";
			}
		}
		if( !empty($kwd) ) 
		{
			$condition .= " AND couponname like \"%" . $kwd . "%\"";
		}
		$time = time();
		if( $merch ) 
		{
			$ds = Db::name('shop_coupon')->alias('c')->join('shop_merch m','m.id = c.merchid','left')->where($condition)->order('c.id asc')->field('c.*,m.merchname')->select();
		}
		else 
		{
			$ds = Db::name('shop_coupon')->where($condition)->order('id asc')->select();
		}
		if( !empty($ds) ) 
		{
			foreach( $ds as &$d ) 
			{
				$d = model("coupon")->setCoupon($d, $time, false);
				$d["last"] = model("coupon")->get_last_count($d["id"]);
				if( $d["last"] == -1 ) 
				{
					$d["last"] = "不限";
				}
				if( $diy ) 
				{
					if( $d["coupontype"] == 0 ) 
					{
						if( 0 < $d["enough"] ) 
						{
							$d["uselimit"] = "满" . (double) $d["enough"] . "元可用";
						}
						else 
						{
							$d["uselimit"] = "无门槛使用";
						}
					}
					else 
					{
						if( $d["coupontype"] == 1 ) 
						{
							if( 0 < $d["enough"] ) 
							{
								$d["uselimit"] = "充值满" . (double) $d["enough"] . "元可用";
							}
							else 
							{
								$d["uselimit"] = "充值任意金额";
							}
						}
					}
					if( $d["backtype"] == 0 ) 
					{
						$d["values"] = "￥" . (double) $d["deduct"];
					}
					else 
					{
						if( $d["backtype"] == 1 ) 
						{
							$d["values"] = (double) $d["discount"] . "折 ";
						}
						else 
						{
							if( $d["backtype"] == 2 ) 
							{
								$values = 0;
								if( !empty($d["backmoney"]) && 0 < $d["backmoney"] ) 
								{
									$values = $values + $d["backmoney"];
								}
								if( !empty($d["backcredit"]) && 0 < $d["backcredit"] ) 
								{
									$values = $values + $d["backcredit"];
								}
								if( !empty($d["backredpack"]) && 0 < $d["backredpack"] ) 
								{
									$values = $values + $d["backredpack"];
								}
								$d["values"] = "￥" . $values;
							}
						}
					}
				}
				else 
				{
					if( $live ) 
					{
						if( $d["backtype"] == 0 ) 
						{
							$d["value_text"] = "0";
							$d["value_total"] = price_format($d["deduct"]);
						}
						else 
						{
							if( $d["backtype"] == 1 ) 
							{
								$d["value_text"] = "折";
								$d["value_total"] = $d["discount"];
							}
							else 
							{
								if( $d["backtype"] == 2 ) 
								{
									if( !empty($d["backmoney"]) && 0 < $d["backmoney"] ) 
									{
										$d["value_text"] = "0";
										$d["value_total"] = price_format($d["backmoney"]);
									}
									else 
									{
										if( !empty($d["backcredit"]) && 0 < $d["backcredit"] ) 
										{
											$d["value_text"] = "积分";
											$d["value_total"] = $d["backcredit"];
										}
										else 
										{
											if( !empty($d["backredpack"]) && 0 < $d["backredpack"] ) 
											{
												$d["value_text"] = "0";
												$d["value_total"] = price_format($d["backredpack"]);
											}
											else 
											{
												$d["value_text"] = "0";
												$d["value_total"] = "0";
											}
										}
									}
								}
							}
						}
					}
				}
				unset($d["respdesc"]);
				unset($d["respthumb"]);
				unset($d["resptitle"]);
			}
			unset($d);
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('sale/coupon/query');
	}

	public function querycplist() 
	{
		$kwd = trim(input('keyword'));
		$diy = intval(input('diy'));
		$condition = " merchid=0";
		if( !empty($kwd) ) 
		{
			$condition .= " AND couponname like \"%" . $kwd . "%\"";
		}
		$time = time();
		$ds = Db::name('shop_coupon')->where($condition)->order('id asc')->select();
		if( !empty($ds) ) 
		{
			foreach( $ds as &$d ) 
			{
				$d = model("coupon")->setCoupon($d, $time, false);
				$d["last"] = model("coupon")->get_last_count($d["id"]);
				if( $d["last"] == -1 ) 
				{
					$d["last"] = "不限";
				}
				if( $diy ) 
				{
					if( $d["coupontype"] == 0 ) 
					{
						if( 0 < $d["enough"] ) 
						{
							$d["uselimit"] = "满" . (double) $d["enough"] . "元可用";
						}
						else 
						{
							$d["uselimit"] = "无门槛使用";
						}
					}
					else 
					{
						if( $d["coupontype"] == 1 ) 
						{
							if( 0 < $d["enough"] ) 
							{
								$d["uselimit"] = "充值满" . (double) $d["enough"] . "元可用";
							}
							else 
							{
								$d["uselimit"] = "充值任意金额";
							}
						}
					}
					if( $d["backtype"] == 0 ) 
					{
						$d["values"] = "￥" . (double) $d["deduct"];
					}
					else 
					{
						if( $d["backtype"] == 1 ) 
						{
							$d["values"] = (double) $d["discount"] . "折 ";
						}
						else 
						{
							if( $d["backtype"] == 2 ) 
							{
								$values = 0;
								if( !empty($d["backmoney"]) && 0 < $d["backmoney"] ) 
								{
									$values = $values + $d["backmoney"];
								}
								if( !empty($d["backcredit"]) && 0 < $d["backcredit"] ) 
								{
									$values = $values + $d["backcredit"];
								}
								if( !empty($d["backredpack"]) && 0 < $d["backredpack"] ) 
								{
									$values = $values + $d["backredpack"];
								}
								$d["values"] = "￥" . $values;
							}
						}
					}
				}
			}
			unset($d);
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('sale/coupon/querycplist');
	}

	public function couponquerygoods() 
	{
		$kwd = trim(input('keyword'));
		$condition = " deleted = 0 and (bargain =0 or bargain is null) and status =1";
		if( !empty($kwd) ) 
		{
			$condition .= " AND `title` LIKE \"%" . $kwd . "%\"";
		}
		$ds = Db::name('shop_goods')->where($condition)->field('id,title,thumb')->order('createtime desc')->select();
		$ds = set_medias($ds, array( "thumb", "share_icon" ));
		if( $_GET["suggest"] ) 
		{
			exit( json_encode(array( "value" => $ds )) );
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('sale/coupon/couponquerygoods');
	}

	public function querycoupons() 
	{
		$kwd = trim(input('keyword'));
		$condition = " 1 ";
		if( !empty($kwd) ) 
		{
			$condition .= " AND `couponname` LIKE \"%" . $kwd . "%\"";
		}
		$ds = Db::name('shop_coupon')->where($condition)->field('id,couponname as title,thumb')->order('createtime desc')->select();
		$ds = set_medias($ds, "thumb");
		if( $_GET["suggest"] ) 
		{
			exit( json_encode(array( "value" => $ds )) );
		}
		$this->assign(['ds'=>$ds]);
		return $this->fetch('sale/coupon/querycoupons');
	}

	public function sendcoupon()
	{
		$couponid = intval(input('couponid'));
		$coupon = Db::name('shop_coupon')->where('id = ' . $couponid)->find();
		$list = Db::name('member_level')->order('level asc')->select();
		$list2 = Db::name('member_group')->order('id asc')->select();
		$coupons = Db::name('shop_coupon')->where('merchid=0')->order('id asc')->select();
		$hascommission = false;
		// $plugin_com = p('commission');
		// if ($plugin_com) {
		// 	$plugin_com_set = $plugin_com->getSet();
		// 	$hascommission = !empty($plugin_com_set['level']);
		// }
		// if ($hascommission) {
		// 	$list3 = $plugin_com->getLevels();
		// }

		$data = model('common')->getPluginset('coupon');

		model('common')->updatePluginset(array('coupon' => $data));
		$this->assign(['coupon'=>$coupon,'list'=>$list,'list2'=>$list2,'coupons'=>$coupons,'hascommission'=>$hascommission]);
		return $this->fetch('sale/coupon/send');
	}

	public function couponfetch()
	{
		$couponid = intval(input('couponid'));
		$class1 = input('send1');
		$coupon = Db::name('shop_coupon')->where('id = ' . $couponid . ' and merchid=0')->find();
		if (empty($coupon)) {
			show_json(0, '未找到优惠券!');
		}
		$send_total = intval(input('send_total'));
		if (empty($send_total)) {
			show_json(0, '发送数量最小为1!');
		}

		if ($class1 == 1) {
			$send_openid = $_POST['send_openid'];
			$openids = explode(',', $send_openid);
			$plog = '发放优惠券 ID: ' . $couponid . ' 方式: 指定 ID 人数: ' . count($openids);
		} else if ($class1 == 2) {
			$where = '';
			if (!empty($_POST['send_level'])) {
				$where .= ' and level =' . intval($_POST['send_level']);
			}
			$members = Db::name('member')->where($where)->field('id')->select();
			if (!empty($_POST['send_level'])) {
				$levelname = Db::name('member_level')->where('id = ' . $_POST['send_level'])->value('levelname');
			} else {
				$levelname = '全部';
			}
			$openids = array_keys($members);
			$plog = '发放优惠券 ID: ' . $couponid . ' 方式: 等级-' . $levelname . ' 人数: ' . count($members);
		} else if ($class1 == 3) {
			$where = '';
			if (!empty($_POST['send_group'])) {
				$where .= ' and groupid =' . intval($_POST['send_group']);
			}
			$members = Db::name('member')->where($where)->field('id')->select();
			if (!empty($_POST['send_group'])) {
				$groupname = Db::name('member_group')->where('id = ' . $_POST['send_group'])->value('groupname');
			} else {
				$groupname = '全部分组';
			}
			$openids = array_keys($members);
			$plog = '发放优惠券 ID: ' . $couponid . '  方式: 分组-' . $groupname . ' 人数: ' . count($members);
		} else if ($class1 == 4) {
			$where = '';
			$members = Db::name('member')->where($where)->field('id')->select();
			$openids = array_keys($members);
			$plog = '发放优惠券 ID: ' . $couponid . '  方式: 全部会员 人数: ' . count($members);
		} else if ($class1 == 5) {
			$where = '';
			if (!empty($_POST['send_agentlevel']) || $_POST['send_partnerlevels'] === '0') {
				$where .= ' and agentlevel =' . intval($_POST['send_agentlevel']);
			}
			$members = Db::name('member')->where('isagent=1 and status=1 ')->field('id')->select();
			if ($_POST['send_agentlevel'] != '') {
				$levelname = Db::name('shop_commission_level')->where('id = ' . $_POST['send_agentlevel'])->value('levelname');
			} else {
				$levelname = '全部';
			}
			$openids = array_keys($members);
			$plog = '发放优惠券 ID: ' . $couponid . '  方式: 分销商-' . $levelname . ' 人数: ' . count($members);
		}

		$mopenids = array();
		foreach ($openids as $openid) {
			$mopenids[] = '\'' . str_replace('\'', '\'\'', $openid) . '\'';
		}
		return $mopenids;
		if (empty($mopenids)) {
			show_json(0, '未找到发送的会员!');
		}
		$members = pdo_fetchall('select id,openid,nickname from ' . tablename('shop_member') . ' where openid in (' . implode(',', $mopenids) . (') and uniacid=' . $_W['uniacid']));



		if (empty($members)) {

			show_json(0, '未找到发送的会员!');

		}



		if ($coupon['total'] != -1) {

			$last = com('coupon')->get_last_count($couponid);



			if ($last <= 0) {

				show_json(0, '优惠券数量不足,无法发放!');

			}



			$need = count($members) - $last;



			if (0 < $need) {

				show_json(0, '优惠券数量不足,请补充 ' . $need . ' 张优惠券才能发放!');

			}

		}



		$data = array('sendtemplateid' => $_POST['sendtemplateid'], 'frist' => $_POST['frist'], 'fristcolor' => $_POST['fristcolor'], 'keyword1' => $_POST['keyword1'], 'keyword1color' => $_POST['keyword1color'], 'keyword2' => $_POST['keyword2'], 'keyword2color' => $_POST['keyword2color'], 'remark' => $_POST['remark'], 'remarkcolor' => $_POST['remarkcolor'], 'templateurl' => $_POST['templateurl'], 'custitle' => $_POST['custitle'], 'custhumb' => $_POST['custhumb'], 'cusdesc' => $_POST['cusdesc'], 'cusurl' => $_POST['cusurl']);

		m('common')->updatePluginset(array('coupon' => $data));

		$time = time();



		foreach ($members as $m) {

			$i = 1;



			while ($i <= $send_total) {

				$log = array('uniacid' => $_W['uniacid'], 'merchid' => $coupon['merchid'], 'openid' => $m['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $couponid, 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => $time, 'getfrom' => 0);

				pdo_insert('shop_coupon_log', $log);

				$logid = pdo_insertid();

				$data = array('uniacid' => $_W['uniacid'], 'merchid' => $coupon['merchid'], 'openid' => $m['openid'], 'couponid' => $couponid, 'gettype' => 0, 'gettime' => $time, 'senduid' => $_W['uid']);

				pdo_insert('shop_coupon_data', $data);

				++$i;

			}

		}
		show_json(1, array('openids' => $openids));
	}

	public function category()
	{
		if (!empty($_POST['catid'])) {
			foreach ($_POST['catid'] as $k => $v) {
				$data = array('name' => trim($_POST['catname'][$k]), 'displayorder' => $k, 'status' => intval($_POST['status'][$k]));
				if (empty($v)) {
					$insert_id = Db::name('shop_coupon_category')->insertGetId($data);
					model('shop')->plog('sale.coupon.category.add', '添加分类 ID: ' . $insert_id);
				} else {
					Db::name('shop_coupon_category')->where('id = ' . $v)->update($data);
					model('shop')->plog('sale.coupon.category.edit', '修改分类 ID: ' . $v);
				}
			}

			model('shop')->plog('sale.coupon.category.edit', '批量修改分类');
			show_json(1);
		}

		$list = Db::name('shop_coupon_category')->where('merchid = 0')->order('displayorder asc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('sale/coupon/category');
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_coupon_category')->where('id = ' . $id . ' and merchid=0')->field('id,name')->find();
		if (!empty($item)) {
			Db::name('shop_coupon_category')->where('id = ' . $id)->delete();
			model('shop')->plog('sale.coupon.category.delete', '删除分类 ID: ' . $id . ' 标题: ' . $item['name'] . ' ');
		}
		show_json(1);
	}

}