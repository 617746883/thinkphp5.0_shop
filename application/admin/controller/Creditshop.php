<?php
/**
 * 积分商城
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Creditshop extends Base
{
	public function index()
    {
    	header('location: ' . url('admin/creditshop/goodslist'));exit;
    }

    public function goodslist()
    {
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}

		$psize = 15;
		$condition = ' 1 ';
		$tab = (!empty(input('tab')) ? trim(input('tab')) : 'sell');
		if (empty($tab) || ($tab == 'sell')) {
			$condition .= ' and status = 1 and total > 0 and deleted = 0 ';
		} else if ($tab == 'sellout') {
			$condition .= ' and status = 1 and total <= 0 and deleted = 0 ';
		} else if ($tab == 'warehouse') {
			$condition .= ' and status = 0 and deleted = 0 ';
		} else {
			if ($tab == 'recycle') {
				$condition .= ' and deleted = 1 ';
			}
		}
		$keyword = input('keyword/s','');
		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' AND title LIKE "%' . trim($keyword) . '%"';
		}
		$cate = input('cate');
		if ($cate != '') {
			$condition .= ' AND cate = ' . intval($cate);
		}

		$list = Db::name('shop_creditshop_goods')->where($condition)->order('displayorder','desc')->paginate($psize);
		$categorys = Db::name('shop_creditshop_goods_category')->field('id,`name`,thumb')->order('displayorder','desc')->group('id')->select();
		foreach ($categorys as $key => &$row) {
			if (isset($row['id'])) {
				$category[$row['id']] = $row;
			} else {
				$category[] = $row;
			}
		}
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'cate'=>$cate,'keyword'=>$keyword,'category'=>$category,'tab'=>$tab]);
    	return $this->fetch('creditshop/goods/index');
    }

    public function goodsadd()
    {
    	$goodData = $this->goodspost();
    	return $goodData;
    }

    public function goodsedit()
    {
    	$goodData = $this->goodspost();
    	return $goodData;
    }

    protected function goodspost()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_creditshop_goods')->where('id',$id)->find();
		$shopset = model('common')->getSysset();
		if ($item['showlevels'] != '') {
			$item['showlevels'] = explode(',', $item['showlevels']);
		}

		if ($item['buylevels'] != '') {
			$item['buylevels'] = explode(',', $item['buylevels']);
		}

		if ($item['showgroups'] != '') {
			$item['showgroups'] = explode(',', $item['showgroups']);
		}

		if ($item['buygroups'] != '') {
			$item['buygroups'] = explode(',', $item['buygroups']);
		}
		$endtime = (empty($item['endtime']) ? date('Y-m-d H:i', time()) : date('Y-m-d H:i', $item['endtime']));
		$groups = model('member')->getGroups();
		$category = Db::name('shop_creditshop_goods_category')->field('id,name,thumb')->order('displayorder','desc')->select();
		$levels = model('member')->getLevels();

		foreach ($levels as &$l) {
			$l['key'] = 'level' . $l['id'];
		}

		unset($l);
		$levels = array_merge(array(array('id' => 0, 'key' => 'default', 'levelname' => empty($shopset['shop']['levelname']) ? '默认会员' : $shopset['shop']['levelname'])), $levels);

		$allspecs = Db::name('shop_creditshop_goods_spec')->where('goodsid',$id)->order('displayorder','asc')->select();

		foreach ($allspecs as &$s) {
			$s['items'] = Db::name('shop_creditshop_goods_spec_item')->alias('a')->join('shop_virtual_type b','b.id=a.virtual','left')->where('a.specid',$s['id'])->order('a.displayorder','asc')->field('a.id,a.specid,a.title,a.thumb,a.show,a.displayorder,a.valueId,a.virtual,b.title as title2')->select();
		}
		unset($s);

		$html = '';
		$discounts_html = '';
		$isdiscount_discounts_html = '';
		$options = Db::name('shop_creditshop_goods_option')->where('goodsid',$id)->select();
		$specs = array();

		if (0 < count($options)) {
			$specitemids = explode('_', $options[0]['specs']);

			foreach ($specitemids as $itemid) {
				foreach ($allspecs as $ss) {
					$items = $ss['items'];
					foreach ($items as $it) {
						if ($it['id'] == $itemid) {
							$specs[] = $ss;
							break;
						}
					}
				}
			}

			$html = '';
			$html .= '<table class="table table-bordered table-condensed">';
			$html .= '<thead>';
			$html .= '<tr class="active">';
			$discounts_html .= '<table class="table table-bordered table-condensed">';
			$discounts_html .= '<thead>';
			$discounts_html .= '<tr class="active">';
			$isdiscount_discounts_html .= '<table class="table table-bordered table-condensed">';
			$isdiscount_discounts_html .= '<thead>';
			$isdiscount_discounts_html .= '<tr class="active">';
			$len = count($specs);
			$newlen = 1;
			$h = array();
			$rowspans = array();
			$i = 0;

			while ($i < $len) {
				$html .= '<th>' . $specs[$i]['title'] . '</th>';
				$discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$isdiscount_discounts_html .= '<th>' . $specs[$i]['title'] . '</th>';
				$itemlen = count($specs[$i]['items']);

				if ($itemlen <= 0) {
					$itemlen = 1;
				}

				$newlen *= $itemlen;
				$h = array();
				$j = 0;

				while ($j < $newlen) {
					$h[$i][$j] = array();
					++$j;
				}

				$l = count($specs[$i]['items']);
				$rowspans[$i] = 1;
				$j = $i + 1;

				while ($j < $len) {
					$rowspans[$i] *= count($specs[$j]['items']);
					++$j;
				}

				++$i;
			}

			$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">库存</div><div class="input-group"><input type="text" class="form-control input-sm option_total_all"  VALUE=""/><span class="input-group-addon" ><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_total\');"></a></span></div></div></th>';
			$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">积分</div>\r\n<div class=\"input-group\"><input type=\"text\" class=\"form-control  input-sm option_credit_all\"  VALUE=\"\"/><span class=\"input-group-addon\">\r\n<a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_credit');\"></a></span></div></div></th>";
			$html .= "<th><div class=\"\"><div style=\"padding-bottom:10px;text-align:center;\">金额</div>\r\n<div class=\"input-group\"><input type=\"text\" class=\"form-control input-sm option_money_all\"  VALUE=\"\"/><span class=\"input-group-addon\">\r\n<a href=\"javascript:;\" class=\"fa fa-angle-double-down\" title=\"批量设置\" onclick=\"setCol('option_money');\"></a></span></div></div></th>";
			$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">编码</div><div class="input-group"><input type="text" class="form-control input-sm option_goodssn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_goodssn\');"></a></span></div></div></th>';
			$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">条码</div><div class="input-group"><input type="text" class="form-control input-sm option_productsn_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_productsn\');"></a></span></div></div></th>';
			$html .= '<th><div class=""><div style="padding-bottom:10px;text-align:center;">重量（克）</div><div class="input-group"><input type="text" class="form-control input-sm option_weight_all"  VALUE=""/><span class="input-group-addon"><a href="javascript:;" class="fa fa-angle-double-down" title="批量设置" onclick="setCol(\'option_weight\');"></a></span></div></div></th>';

			$html .= '</tr></thead>';
			$discounts_html .= '</tr></thead>';
			$isdiscount_discounts_html .= '</tr></thead>';
			$m = 0;

			while ($m < $len) {
				$k = 0;
				$kid = 0;
				$n = 0;
				$j = 0;

				while ($j < $newlen) {
					$rowspan = $rowspans[$m];
					if (($j % $rowspan) == 0) {
						$h[$m][$j] = array('html' => '<td class=\'full\' rowspan=\'' . $rowspan . '\'>' . $specs[$m]['items'][$kid]['title'] . '</td>', 'id' => $specs[$m]['items'][$kid]['id']);
					} else {
						$h[$m][$j] = array('html' => '', 'id' => $specs[$m]['items'][$kid]['id']);
					}
					++$n;
					if ($n == $rowspan) {
						++$kid;
						if ((count($specs[$m]['items']) - 1) < $kid) {
							$kid = 0;
						}
						$n = 0;
					}
					++$j;
				}
				++$m;
			}

			$hh = '';
			$dd = '';
			$isdd = '';
			$cc = '';
			$i = 0;

			while ($i < $newlen) {
				$hh .= '<tr>';
				$dd .= '<tr>';
				$isdd .= '<tr>';
				$cc .= '<tr>';
				$ids = array();
				$j = 0;

				while ($j < $len) {
					$hh .= $h[$j][$i]['html'];
					$dd .= $h[$j][$i]['html'];
					$isdd .= $h[$j][$i]['html'];
					$cc .= $h[$j][$i]['html'];
					$ids[] = $h[$j][$i]['id'];
					++$j;
				}

				$ids = implode('_', $ids);
				$val = array('id' => '', 'title' => '', 'total' => '', 'credit' => '', 'money' => '', 'weight' => '', 'virtual' => '');

				foreach ($options as $o) {
					if ($ids === $o['specs']) {
						$val = array('id' => $o['id'], 'title' => $o['title'], 'total' => $o['total'], 'credit' => $o['credit'], 'money' => $o['money'], 'goodssn' => $o['goodssn'], 'productsn' => $o['productsn'], 'weight' => $o['weight'], 'virtual' => $o['virtual']);
						$discount_val = array('id' => $o['id']);
						break;
					}
				}

				foreach ($levels as $level) {
					$dd .= '<td>';
					$isdd .= '<td>';

					if ($level['key'] == 'default') {
						$dd .= '<input data-name="discount_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control discount_' . $level['key'] . ' discount_' . $level['key'] . '_' . $ids . '" value="' . $discounts_val[$level['key']] . '"/> ';
						$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['key'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_' . $level['key'] . ' isdiscount_discounts_' . $level['key'] . '_' . $ids . '" value="' . $isdiscounts_val[$level['key']] . '"/> ';
					}
					else {
						$dd .= '<input data-name="discount_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control discount_level' . $level['id'] . ' discount_level' . $level['id'] . '_' . $ids . '" value="' . $discounts_val['level' . $level['id']] . '"/> ';
						$isdd .= '<input data-name="isdiscount_discounts_level_' . $level['id'] . '_' . $ids . '"  type="text" class="form-control isdiscount_discounts_level' . $level['id'] . ' isdiscount_discounts_level' . $level['id'] . '_' . $ids . '" value="' . $isdiscounts_val['level' . $level['id']] . '"/> ';
					}

					$dd .= '</td>';
					$isdd .= '</td>';
				}

				$dd .= '<input data-name="discount_id_' . $ids . '"  type="hidden" class="form-control discount_id discount_id_' . $ids . '" value="' . $discounts_val['id'] . '"/>';
				$dd .= '<input data-name="discount_ids"  type="hidden" class="form-control discount_ids discount_ids_' . $ids . '" value="' . $ids . '"/>';
				$dd .= '<input data-name="discount_title_' . $ids . '"  type="hidden" class="form-control discount_title discount_title_' . $ids . '" value="' . $discounts_val['title'] . '"/>';
				$dd .= '<input data-name="discount_virtual_' . $ids . '"  type="hidden" class="form-control discount_title discount_virtual_' . $ids . '" value="' . $discounts_val['virtual'] . '"/>';
				$dd .= '</tr>';
				$isdd .= '<input data-name="isdiscount_discounts_id_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_id isdiscount_discounts_id_' . $ids . '" value="' . $isdiscounts_val['id'] . '"/>';
				$isdd .= '<input data-name="isdiscount_discounts_ids"  type="hidden" class="form-control isdiscount_discounts_ids isdiscount_discounts_ids_' . $ids . '" value="' . $ids . '"/>';
				$isdd .= '<input data-name="isdiscount_discounts_title_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_title_' . $ids . '" value="' . $isdiscounts_val['title'] . '"/>';
				$isdd .= '<input data-name="isdiscount_discounts_virtual_' . $ids . '"  type="hidden" class="form-control isdiscount_discounts_title isdiscount_discounts_virtual_' . $ids . '" value="' . $isdiscounts_val['virtual'] . '"/>';
				$isdd .= '</tr>';
				$hh .= '<td>';
				$hh .= '<input data-name="option_total_' . $ids . '"  type="text" class="form-control option_total option_total_' . $ids . '" value="' . $val['total'] . '"/>';
				$hh .= '</td>';
				$hh .= '<input data-name="option_id_' . $ids . '"  type="hidden" class="form-control option_id option_id_' . $ids . '" value="' . $val['id'] . '"/>';
				$hh .= '<input data-name="option_ids"  type="hidden" class="form-control option_ids option_ids_' . $ids . '" value="' . $ids . '"/>';
				$hh .= '<input data-name="option_title_' . $ids . '"  type="hidden" class="form-control option_title option_title_' . $ids . '" value="' . $val['title'] . '"/>';
				$hh .= '<input data-name="option_virtual_' . $ids . '"  type="hidden" class="form-control option_virtual option_virtual_' . $ids . '" value="' . $val['virtual'] . '"/>';
				$hh .= '<td><input data-name="option_credit_' . $ids . '" type="text" class="form-control option_credit option_credit_' . $ids . '" value="' . $val['credit'] . '"/></td>';
				$hh .= '<td><input data-name="option_money_' . $ids . '" type="text" class="form-control option_money option_money_' . $ids . '" " value="' . $val['money'] . '"/></td>';
				$hh .= '<td><input data-name="option_goodssn_' . $ids . '" type="text" class="form-control option_goodssn option_goodssn_' . $ids . '" " value="' . $val['goodssn'] . '"/></td>';
				$hh .= '<td><input data-name="option_productsn_' . $ids . '" type="text" class="form-control option_productsn option_productsn_' . $ids . '" " value="' . $val['productsn'] . '"/></td>';
				$hh .= '<td><input data-name="option_weight_' . $ids . '" type="text" class="form-control option_weight option_weight_' . $ids . '" " value="' . $val['weight'] . '"/></td>';
				$hh .= '</tr>';

				++$i;
			}

			$discounts_html .= $dd;
			$discounts_html .= '</table>';
			$isdiscount_discounts_html .= $isdd;
			$isdiscount_discounts_html .= '</table>';
			$html .= $hh;
			$html .= '</table>';
		}
		$dispatch_data = Db::name('shop_dispatch')->where('merchid = 0 and enabled = 1 ')->order('displayorder','desc')->select();

		if (Request::instance()->isPost()) {
			$data = array('displayorder' => intval($_POST['displayorder']), 'goodstype' => intval($_POST['goodstype']), 'goodsid' => intval($_POST['goodsid']), 'couponid' => intval($_POST['couponid']), 'grant1' => floatval($_POST['grant1']), 'grant2' => floatval($_POST['grant2']), 'packetmoney' => floatval($_POST['packetmoney']), 'packetlimit' => floatval($_POST['packetlimit']), 'packettotal' => intval($_POST['packettotal']), 'packettype' => intval($_POST['packettype']), 'minpacketmoney' => floatval($_POST['minpacketmoney']), 'title' => trim($_POST['title']), 'cate' => intval($_POST['cate']), 'thumb' => trim($_POST['thumb']), 'price' => floatval($_POST['price']), 'productprice' => floatval($_POST['productprice']), 'credit' => intval($_POST['credit']), 'money' => trim($_POST['money']), 'dispatchtype' => intval($_POST['dispatchtype']), 'dispatchid' => intval($_POST['dispatchid']), 'dispatch' => floatval($_POST['dispatch']), 'istop' => intval($_POST['istop']), 'isrecommand' => intval($_POST['isrecommand']), 'istime' => intval($_POST['istime']), 'timestart' => strtotime($_POST['timestart']), 'timeend' => strtotime($_POST['timeend']), 'goodsdetail' => model('common')->html_images($_POST['goodsdetail']), 'goodssn' => trim($_POST['goodssn']), 'productsn' => trim($_POST['productsn']), 'weight' => intval($_POST['weight']), 'total' => intval($_POST['total']), 'showtotal' => intval($_POST['showtotal']), 'totalcnf' => intval($_POST['totalcnf']), 'hasoption' => intval($_POST['hasoption']), 'status' => intval($_POST['status']), 'type' => intval($_POST['type']), 'area' => trim($_POST['area']), 'chanceday' => intval($_POST['chanceday']), 'chance' => intval($_POST['chance']), 'totalday' => intval($_POST['totalday']), 'rate1' => trim($_POST['rate1']), 'rate2' => trim($_POST['rate2']), 'isendtime' => intval($_POST['isendtime']), 'usetime' => 0 <= intval($_POST['usetime']) ? intval($_POST['usetime']) : 0, 'endtime' => strtotime($_POST['endtime']), 'detailshow' => intval($_POST['detailshow']), 'noticedetailshow' => intval($_POST['noticedetailshow']), 'detail' => model('common')->html_images($_POST['detail']), 'noticedetail' => model('common')->html_images($_POST['noticedetail']), 'showlevels' => is_array($_POST['showlevels']) ? implode(',', $_POST['showlevels']) : '', 'buylevels' => is_array($_POST['buylevels']) ? implode(',', $_POST['buylevels']) : '', 'showgroups' => is_array($_POST['showgroups']) ? implode(',', $_POST['showgroups']) : '', 'buygroups' => is_array($_POST['buygroups']) ? implode(',', $_POST['buygroups']) : '', 'subtitle' => trim($_POST['subtitle']), 'subdetail' => model('common')->html_images($_POST['subdetail']), 'isverify' => intval($_POST['isverify']), 'verifytype' => intval($_POST['verifytype']), 'verifynum' => intval($_POST['verifynum']), 'storeids' => is_array($_POST['storeids']) ? implode(',', $_POST['storeids']) : '', 'noticeopenid' => trim($_POST['noticeopenid']), 'followneed' => intval($_POST['followneed']), 'followtext' => trim($_POST['followtext']), 'share_title' => trim($_POST['share_title']), 'share_icon' => trim($_POST['share_icon']), 'share_desc' => trim($_POST['share_desc']));

			if ($isverify) {
				$data['dispatch'] = 0;
				if (($data['verifytype'] == 1) && ($data['verifynum'] < 1)) {
					$data['verifynum'] = 1;
				}

				if ($data['verifytype'] == 0) {
					$data['verifynum'] = 1;
				}
			}

			if ($data['credit'] <= 0) {
				show_json(0, '请正确填写积分！');
			}

			if ($data['money'] < 0) {
				show_json(0, '请正确填写金额！');
			}

			if (($data['goodstype'] == 2) && ($data['grant1'] <= 0)) {
				show_json(0, '请正确填写余额！');
			}

			$data['vip'] = !empty($data['showlevels']) || !empty($data['showgroups']) ? 1 : 0;

			if (!empty($id)) {
				$data['goodstype'] = $goodstype;
				$data['type'] = $type;
				$data['isverify'] = $isverify;
				$data['packetmoney'] = $item['packetmoney'];
				$data['surplusmoney'] = $item['surplusmoney'];
				$data['packettotal'] = $item['packettotal'];
				$data['packetsurplus'] = $item['packetsurplus'];
				$data['grant2'] = $item['grant2'];
				$data['packettype'] = $item['packettype'];
				$data['minpacketmoney'] = $item['minpacketmoney'];
				Db::name('shop_creditshop_goods')->where('id',$id)->update($data);
				model('shop')->plog('creditshop.goods.edit', '编辑积分商城商品 ID: ' . $id . ' <br/>商品名称: ' . $data['title']);
			} else {
				if (($data['goodstype'] == 3) || (0 < $data['packetmoney']) || (0 < $data['packettotal'])) {
					if ($data['packettype'] == 0) {
						if ($data['grant2'] < 1) {
							show_json(0, '红包最少为1元，请正确填写！');
						}

						if ($data['packetmoney'] != ($data['grant2'] * $data['packettotal'])) {
							show_json(0, '请正确填写红包金额和数量！');
						}
					} else {
						if ($data['minpacketmoney'] < 1) {
							show_json(0, '红包随机金额最少为1元，请正确填写！');
						}

						if ($data['packetmoney'] < ($data['minpacketmoney'] * $data['packettotal'])) {
							show_json(0, '请正确填写红包金额和数量！');
						}
					}
				}

				$data['surplusmoney'] = $data['packetmoney'];
				$data['packetsurplus'] = $data['packettotal'];
				$id = Db::name('shop_creditshop_goods')->insertGetId($data);
				model('shop')->plog('creditshop.goods.add', '添加积分商城商品 ID: ' . $id . '  <br/>商品名称: ' . $data['title']);
			}

			if ($data['hasoption']) {
				$totalstocks = 0;
				$files = $_FILES;
				$spec_ids = $_POST['spec_id'];
				$spec_titles = $_POST['spec_title'];
				$specids = array();
				$len = count($spec_ids);
				$specids = array();
				$spec_items = array();
				$k = 0;

				while ($k < $len) {
					$spec_id = '';
					$get_spec_id = $spec_ids[$k];
					$a = array('goodsid' => $id, 'displayorder' => $k, 'title' => $spec_titles[$get_spec_id]);

					if (is_numeric($get_spec_id)) {
						Db::name('shop_creditshop_goods_spec')->where('id',$get_spec_id)->update($a);
						$spec_id = $get_spec_id;
					} else {
						$spec_id = Db::name('shop_creditshop_goods_spec')->insertGetId($a);
					}

					$spec_item_ids = $_POST['spec_item_id_' . $get_spec_id];
					$spec_item_titles = $_POST['spec_item_title_' . $get_spec_id];
					$spec_item_shows = $_POST['spec_item_show_' . $get_spec_id];
					$spec_item_thumbs = $_POST['spec_item_thumb_' . $get_spec_id];
					$spec_item_oldthumbs = $_POST['spec_item_oldthumb_' . $get_spec_id];
					$spec_item_virtuals = $_POST['spec_item_virtual_' . $get_spec_id];
					$itemlen = count($spec_item_ids);
					$itemids = array();
					$n = 0;

					while ($n < $itemlen) {
						$item_id = '';
						$get_item_id = $spec_item_ids[$n];
						$d = array('specid' => $spec_id, 'displayorder' => $n, 'title' => $spec_item_titles[$n], 'show' => $spec_item_shows[$n], 'thumb' => trim($spec_item_thumbs[$n]), 'virtual' => $data['type'] == 3 ? $spec_item_virtuals[$n] : 0);
						$f = 'spec_item_thumb_' . $get_item_id;

						if (is_numeric($get_item_id)) {
							Db::name('shop_creditshop_goods_spec_item')->where('id',$get_item_id)->update($d);
							$item_id = $get_item_id;
						} else {
							$item_id = Db::name('shop_creditshop_goods_spec_item')->insertGetId($d);
						}

						$itemids[] = $item_id;
						$d['get_id'] = $get_item_id;
						$d['id'] = $item_id;
						$spec_items[] = $d;
						++$n;
					}

					if (0 < count($itemids)) {
						Db::name('shop_creditshop_goods_spec_item')->where('specid=' . $spec_id . ' and id not in (' . implode(',', $itemids) . ')')->delete();
					} else {
						Db::name('shop_creditshop_goods_spec_item')->where('specid=' . $spec_id)->delete();
					}

					Db::name('shop_creditshop_goods_spec')->where('id',$spec_id)->update(array('content' => serialize($itemids)));
					$specids[] = $spec_id;
					++$k;
				}

				if (0 < count($specids)) {
					Db::name('shop_creditshop_goods_spec')->where('goodsid=' . $id . ' and id not in (' . implode(',', $specids) . ')')->delete();
				} else {
					Db::name('shop_creditshop_goods_spec')->where('goodsid=' . $id)->delete();
				}

				$optionArray = json_decode($_POST['optionArray'], true);
				$option_idss = $optionArray['option_ids'];
				$len = count($option_idss);
				$optionids = array();
				$levelArray = array();
				$k = 0;

				while ($k < $len) {
					$option_id = '';
					$ids = $option_idss[$k];
					$get_option_id = $optionArray['option_id'][$k];
					$idsarr = explode('_', $ids);
					$newids = array();

					foreach ($idsarr as $key => $ida) {
						foreach ($spec_items as $it) {
							if ($it['get_id'] == $ida) {
								$newids[] = $it['id'];
								break;
							}
						}
					}

					$newids = implode('_', $newids);
					$a = array('title' => $optionArray['option_title'][$k], 'credit' => $optionArray['option_credit'][$k], 'money' => $optionArray['option_money'][$k], 'total' => $optionArray['option_total'][$k], 'weight' => $optionArray['option_weight'][$k], 'goodssn' => $optionArray['option_goodssn'][$k], 'productsn' => $optionArray['option_productsn'][$k], 'goodsid' => $id, 'specs' => $newids, 'virtual' => $data['type'] == 3 ? $optionArray['option_virtual'][$k] : 0);
					$totalstocks += $a['total'];

					if (empty($get_option_id)) {
						$option_id = Db::name('shop_creditshop_goods_option')->insertGetId($a);
					} else {
						Db::name('shop_creditshop_goods_option')->where('id',$get_option_id)->update($a);
						$option_id = $get_option_id;
					}

					$optionids[] = $option_id;
					++$k;
				}

				if ((0 < count($optionids)) && ($data['hasoption'] !== 0)) {
					Db::name('shop_creditshop_goods_option')->where('goodsid=' . $id . ' and id not in ( ' . implode(',', $optionids) . ')')->delete();
					Db::query('update ' . tablename('shop_creditshop_goods') . " g set\r\n\t\t\t\t\tg.mincredit = (select min(credit) from " . tablename('shop_creditshop_goods_option') . ' where goodsid = ' . $id . "),\r\n\t\t\t\t\tg.minmoney = (select min(money) from " . tablename('shop_creditshop_goods_option') . ' where goodsid = ' . $id . "),\r\n\t\t\t\t\tg.maxcredit = (select max(credit) from " . tablename('shop_creditshop_goods_option') . ' where goodsid = ' . $id . "),\r\n\t\t\t\t\tg.maxmoney = (select max(money) from " . tablename('shop_creditshop_goods_option') . ' where goodsid = ' . $id . ")\r\n\t\t\t\t\twhere g.id = " . $id . ' and g.hasoption=1');
				} else {
					Db::name('shop_creditshop_goods_option')->where('goodsid',$id)->delete();
					Db::query('update ' . tablename('shop_creditshop_goods') . ' set minmoney = money,maxmoney = money,mincredit = credit,maxcredit = credit where id = ' . $id . ' and hasoption=0;');
				}

				if ($data['hasoption'] !== 0) {
					Db::name('shop_creditshop_goods')->where('id',$id)->update(array('total' => $totalstocks));
				}
			}
			show_json(1, array('url' => url('admin/creditshop/goodsedit', array('id' => $id, 'tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}

		$this->assign(['item'=>$item,'endtime'=>$endtime,'groups'=>$groups,'category'=>$category,'levels'=>$levels,'allspecs'=>$allspecs,'html'=>$html,'discounts_html'=>$discounts_html,'isdiscount_discounts_html'=>$isdiscount_discounts_html,'dispatch_data'=>$dispatch_data]);
		return $this->fetch('creditshop/goods/post');
	}

	public function goodsquery()
	{
		$kwd = trim(input('keyword'));
		$type = intval(input('type'));
		$params = array();
		$condition = " 1 and status=1 and deleted=0 and type = 1 and groupstype = 0 and isdiscount = 0 and istime = 0 and  ifnull(bargain,0)=0 and ispresell = 0 ";

		if (!empty($kwd)) {
			$condition .= ' AND (`title` LIKE "%' . $keyword . '%" OR `keywords` LIKE "%' . $keyword . '%")';
		}

		if (empty($type)) {
			$condition .= ' AND `type` != 10 ';
		} else {
			$condition .= ' AND `type` = ' . $type;
		}

		$list = array();
		$list = Db::name('shop_goods')->where($condition)->field('id,title,thumb,marketprice,productprice,share_title,share_icon,description,minprice,costprice,total,content,hasoption')->select();
		$list = set_medias($list, array('thumb', 'share_icon'));

		foreach ($list as $key => $value) {
			if (intval($value['hasoption']) == 1) {
				$allspecs = Db::name('shop_goods_spec')->where('goodsid',$value['id'])->order('displayorder','asc')->select();

				foreach ($allspecs as &$s) {
					$s['items'] = Db::name('shop_goods_spec_item')->alias('a')->join('shop_virtual_type b','b.id=a.virtual','left')->where('a.specid',$s['id'])->order('a.displayorder','asc')->field('a.id,a.specid,a.title,a.thumb,a.show,a.displayorder,a.valueId,a.virtual,b.title as title2')->select();
				}

				unset($s);
				$options = Db::name('shop_goods_option')->where('goodsid',$value['id'])->order('id','asc')->select();
			}

			$list[$key]['allspecs'] = $allspecs;
			$list[$key]['options'] = $options;
		}

		if (input('suggest')) {
			exit(json_encode(array('value' => $list)));
		}
		$this->assign(['list'=>$list]);
		return $this->fetch('creditshop/goods/query');
	}

	public function goodsdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_creditshop_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_creditshop_goods')->where('id',$item['id'])->setField('deleted',1);
			model('shop')->plog('creditshop.goods.delete', '删除积分商城商品 ID: ' . $item['id'] . '  <br/>商品名称: ' . $item['title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsstatus()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$status = intval(input('status'));
		$items = Db::name('shop_creditshop_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_creditshop_goods')->where('id',$item['id'])->setField('status',$status);
			model('shop')->plog('creditshop.goods.edit', '修改积分商城商品 ' . $item['id'] . ' <br /> 状态: ' . ($status == 0 ? '下架' : '上架'));
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsdeleted()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_creditshop_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_creditshop_goods')->where('id',$item['id'])->delete();
			model('shop')->plog('creditshop.goods.deleted', '从回收站彻底删除商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsrecycle()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_creditshop_goods')->where('id','in',$id)->field('id,title')->select();

		foreach ($items as $item) {
			Db::name('shop_creditshop_goods')->where('id',$item['id'])->update(array('status' => 0, 'deleted' => 0));
			model('shop')->plog('creditshop.goods.edit', '从回收站恢复商品<br/>ID: ' . $item['id'] . '<br/>商品名称: ' . $item['title']);
		}

		show_json(1, array('url' => referer()));
	}

	public function goodsproperty()
	{
		$id = intval(input('id'));
		$type = trim(input('type'));
		$value = intval(input('value'));

		if (in_array($type, array('istop', 'isrecommand', 'status', 'displayorder', 'title'))) {
			Db::name('shop_creditshop_goods')->where('id',$id)->update(array($type => $value));
			$statusstr = '';

			if ($type == 'istop') {
				$typestr = '置顶';
				$statusstr = ($value == 1 ? '置顶' : '取消置顶');
			} else if ($type == 'isrecommand') {
				$typestr = '推荐';
				$statusstr = ($value == 1 ? '推荐' : '取消推荐');
			} else if ($type == 'status') {
				$typestr = '上下架';
				$statusstr = ($value == 1 ? '上架' : '下架');
			} else {
				if ($type == 'displayorder') {
					$typestr = '排序';
					$statusstr = '序号 ' . $value;
				}
			}
			model('shop')->plog('creditshop.goods.edit', '修改积分商城商品' . $typestr . '状态   ID: ' . $id . ' ' . $statusstr . ' ');
		}

		show_json(1);
	}

	public function category()
	{
		$list = Db::name('shop_creditshop_goods_category')->order('displayorder','desc')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('creditshop/category/index');
	}

	public function categoryadd()
	{
		$data = $this->categorypost();
		return $data;
	}

	public function categoryedit()
	{
		$data = $this->categorypost();
		return $data;
	}

	protected function categorypost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('name' => trim($_POST['catename']), 'enabled' => intval($_POST['enabled']), 'isrecommand' => intval($_POST['isrecommand']), 'displayorder' => intval($_POST['displayorder']), 'thumb' => trim($_POST['thumb']));

			if (!empty($id)) {
				Db::name('shop_creditshop_goods_category')->where('id',$id)->update($data);
				model('shop')->plog('creditshop.category.edit', '修改积分商城分类 ID: ' . $id);
			} else {
				$id = Db::name('shop_creditshop_goods_category')->insertGetId($data);
				model('shop')->plog('creditshop.category.add', '添加积分商城分类 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/creditshop/category', array('op' => 'display'))));
		}

		$item = Db::name('shop_creditshop_goods_category')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('creditshop/category/post');
	}

	public function categorydisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_creditshop_goods_category')->where('id',$id)->field('id,name')->find();
		if (!empty($item)) {
			Db::name('shop_creditshop_goods_category')->where('id',$id)->update(array('displayorder' => $displayorder));
			model('shop')->plog('creditshop.category.edit', '修改分类排序 ID: ' . $item['id'] . ' 标题: ' . $item['name'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_creditshop_goods_category')->where('id',$id)->field('id,name')->find();

		if (empty($item)) {
			show_json(0,'抱歉，分类不存在或是已经被删除！');
		}

		Db::name('shop_creditshop_goods_category')->where('id',$id)->delete();
		model('shop')->plog('creditshop.category.delete', '删除积分商城分类 ID: ' . $id . ' 标题: ' . $item['name'] . ' ');
		show_json(1);
	}

	public function categoryenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$enabled = input('enabled');
		$items = Db::name('shop_creditshop_goods_category')->where('id','in',$id)->field('id,name')->select();
		foreach ($items as $item) {
			Db::name('shop_creditshop_goods_category')->where('id',$item['id'])->update(array('enabled' => intval($enabled)));
			model('shop')->plog('creditshop.category.edit', ('修改商品分类<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['name'] . '<br/>状态: ' . $enabled) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function categoryquery()
	{
		$keyword = trim(input('keyword'));
		$condition = ' 1 and enabled=1 ';

		if (!empty($keyword)) {
			$condition .= ' AND `name` LIKE "%' . $keyword . '%"';
		}

		$list = Db::name('shop_creditshop_goods_category')->where($condition)->order('displayorder','desc')->select();

		if (!empty($list)) {
			$list = set_medias($list, array('thumb', 'bannerimg'));
		}
		$this->assign(['lsit'=>$list]);
		return $this->fetch('creditshop/category/query');
	}

	public function banner()
	{
		$psize = 20;
		$condition = ' 1 ';
		$enabled = input('enabled');
		$keyword = input('keyword');
		if ($enabled != '') {
			$condition .= ' and enabled=' . intval($enabled);
		}

		if (!empty($keyword)) {
			$keyword = trim($keyword);
			$condition .= ' and bannername  like "%' . $keyword . '%"';
		}

		$list = Db::name('shop_creditshop_banner')->where($condition)->order('displayorder','decs')->paginate($psize);
		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'enabled'=>$enabled,'keyword'=>$keyword]);
		return $this->fetch('creditshop/banner/index');
	}

	public function banneradd()
	{
		$data = $this->bannerpost();
		return $data;
	}

	public function banneredit()
	{
		$data = $this->bannerpost();
		return $data;
	}

	protected function bannerpost()
	{
		$id = intval(input('id'));

		if (Request::instance()->isPost()) {
			$data = array('bannername' => trim($_POST['bannername']), 'link' => trim($_POST['link']), 'enabled' => intval($_POST['enabled']), 'displayorder' => intval($_POST['displayorder']), 'thumb' => trim($_POST['thumb']));

			if (!empty($id)) {
				Db::name('shop_creditshop_banner')->where('id',$id)->update($data);
				model('shop')->plog('creditshop.banner.edit', '修改幻灯片 ID: ' . $id);
			} else {
				$id = Db::name('shop_creditshop_banner')->insertGetId($data);
				model('shop')->plog('creditshop.banner.add', '添加幻灯片 ID: ' . $id);
			}

			show_json(1, array('url' => url('admin/creditshop/banner')));
		}

		$item = Db::name('shop_creditshop_banner')->where('id',$id)->find();
		$this->assign(['item'=>$item]);
		return $this->fetch('creditshop/banner/post');
	}

	public function bannerdelete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_creditshop_banner')->where('id','in',$id)->field('id,bannername')->select();

		foreach ($items as $item) {
			Db::name('shop_creditshop_banner')->where('id',$item['id'])->delete();
			model('shop')->plog('creditshop.banner.delete', '删除幻灯片 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function bannerdisplayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_creditshop_banner')->where('id',$id)->field('id,bannername')->find();

		if (!empty($item)) {
			Db::name('shop_creditshop_banner')->where('id',$id)->update(array('displayorder' => $displayorder));
			model('shop')->plog('creditshop.banner.edit', '修改幻灯片排序 ID: ' . $item['id'] . ' 标题: ' . $item['bannername'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function bannerenabled()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_creditshop_banner')->where('id','in',$id)->field('id,bannername')->select();
		$enabled = input('enabled');
		foreach ($items as $item) {
			Db::name('shop_creditshop_banner')->where('id',$id)->update(array('enabled' => intval($enabled)));
			model('shop')->plog('creditshop.banner.edit', ('修改幻灯片状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['bannername'] . '<br/>状态: ' . $_GPC['enabled']) == 1 ? '显示' : '隐藏');
		}

		show_json(1, array('url' => referer()));
	}

	public function exchange()
	{
		$data = $this->getLogData(0);
		return $data;
	}

	public function draw()
	{
		$data = $this->getLogData(1);
		return $data;
	}

	public function order()
	{
		$data = $this->getLogData(2);
		return $data;
	}

	public function convey()
	{
		$data = $this->getLogData(3);
		return $data;
	}

	public function finish()
	{
		$data = $this->getLogData(4);
		return $data;
	}

	public function verifying()
	{
		$data = $this->getLogData(5);
		return $data;
	}

	public function verifyover()
	{
		$data = $this->getLogData(6);
		return $data;
	}

	public function verify()
	{
		$data = $this->getLogData(7);
		return $data;
	}

	protected function getLogData($type)
	{
		$type = intval($type);
		$psize = 20;
		$condition = ' 1 ';
		if (empty($type) || ($type == 1)) {
			$condition .= ' and log.status>0 and g.type=' . $type;
		} else if ($type == 2) {
			$condition .= ' and log.status=2 and log.time_send=0 and g.isverify = 0 ';
		} else if ($type == 3) {
			$condition .= ' and log.status=3 and log.time_send<>0 and log.time_finish = 0 and g.isverify = 0 ';
		} else if ($type == 4) {
			$condition .= ' and log.time_finish>0 and log.time_send>0 and log.status > 0 and g.isverify = 0 ';
		} else if ($type == 5) {
			$condition .= ' and g.isverify > 0 and log.status=2 ';
		} else if ($type == 6) {
			$condition .= ' and g.isverify > 0 and log.status=3 ';
		} else {
			if ($type == 7) {
				$condition .= ' and g.isverify > 0 and log.status > 0 ';
			}
		}

		$set = model('common')->getPluginset('creditshop');
		$searchfield = strtolower(trim(input('searchfield')));
		$keyword = trim(input('keyword'));
		if (!empty($searchfield) && !empty($keyword)) {
			if ($searchfield == 'member') {
				$condition .= ' and ( m.realname like "%' . $keyword . '%" or m.nickname like "%' . $keyword . '%"  or m.mobile like "%' . $keyword . '%") ';
			} else if ($searchfield == 'address') {
				$condition .= ' and ( log.realname like "%' . $keyword . '%"  or log.mobile like "%' . $keyword . '%"  or a.realname like "%' . $keyword . '%" or a.mobile like "%' . $keyword . '%") ';
			} else if ($searchfield == 'logno') {
				$condition .= ' and log.logno like "%' . $keyword . '%"';
			} else if ($searchfield == 'eno') {
				$condition .= ' and log.eno like "%' . $keyword . '%"';
			} else if ($searchfield == 'goods') {
				$condition .= ' and g.title like "%' . $keyword . '%"';
			} else if ($searchfield == 'store') {
				$condition .= ' and  s.merchname like "%' . $keyword . '%"';
			} else {
				if ($searchfield == 'express') {
					$condition .= ' and  log.expresssn like "%' . $keyword . '%"';
				}
			}
		}
		$status = input('status');
		if ($status != '') {
			$condition .= ' and log.status=' . intval($status);
		}

		if (empty($starttime) || empty($endtime)) {
			$starttime = strtotime('-1 month');
			$endtime = time();
		}

		if (!empty($_GET['time']['start']) && !empty($_GET['time']['end'])) {
			$starttime = strtotime($_GET['time']['start']);
			$endtime = strtotime($_GET['time']['end']);
			$condition .= ' AND log.createtime >= ' . $starttime . ' AND log.createtime <= ' . $endtime;
		}

		$list = Db::name('shop_creditshop_log')
			->alias('log')
			->join('member m','m.id = log.mid','left')
			->join('shop_member_address a','a.id = log.addressid','left')
			->join('shop_store s','s.id = log.storeid','left')
			->join('shop_creditshop_goods g','g.id = log.goodsid','left')
			->where($condition)
			->field('log.*, m.nickname,m.avatar,m.realname as mrealname,m.mobile as mmobile, g.title,g.thumb,g.thumb,g.credit,g.money,g.type as goodstype,g.isverify,g.goodstype as iscoupon,s.merchname,s.address as storeaddress,g.dispatch,g.goodstype,g.type,g.merchid as gmerchid,g.hasoption')
			->group('log.id')
			->order('log.createtime','desc')
			->paginate($psize);
		$pager = $list->render();

		foreach ($list as $key => &$row) {
			if (($row['hasoption'] == 1) && (0 < $row['optionid'])) {
				$option = Db::name('shop_creditshop_goods_option')->where('id',$row['optionid'])->where('goodsid',$row['goodsid'])->field('total,credit,money,title as optiontitle,weight')->find();
				$row['credit'] = $option['credit'];
				$row['money'] = $option['money'];
				$row['weight'] = $option['weight'];
				$row['total'] = $option['total'];
				$row['optiontitle'] = $option['optiontitle'];
			}
		}
		unset($row);

		foreach ($list as &$row) {
			$row['address'] = array();

			if (!empty($row['addressid'])) {
				$row['address'] = Db::name('shop_member_address')->where('id',$row['addressid'])->field('realname,mobile,address,province,city,area')->find();
			} else {
				if (0 < intval($row['gmerchid'])) {
					$stores = Db::name('shop_store')->where('id = ' . $row['storeid'] . 'and merchid = ' . $row['gmerchid'] . ' and status=1 and type in(2,3) ')->find();
				} else {
					$stores = Db::name('shop_store')->where('id = ' . $row['storeid'] . 'and status=1 and type in(2,3) ')->find();
				}

				$row['address'] = array('carrier_realname' => $row['realname'], 'carrier_mobile' => $row['mobile'], 'carrier_storename' => $stores['storename'], 'carrier_address' => $row['storeaddress']);
			}

			$row['address']['logid'] = $row['id'];
			$row['address']['isverify'] = $row['isverify'];
			$row['address']['storeid'] = $row['storeid'];
			$row['address']['addressid'] = $row['addressid'];

			if ($row['optionid']) {
				$option = Db::name('shop_creditshop_goods_option')->where('id',$row['optionid'])->field('title')->find();
				$row['optiontitle'] = $option['title'];
			} else {
				$row['optiontitle'] = '';
			}

			$canexchange = true;
			$verifynum = Db::name('shop_creditshop_verify')->where('logid',$row['id'])->count();

			if ($row['status'] == 2) {
				if (empty($row['paystatus'])) {
					$canexchange = false;
				}

				if (empty($row['dispatchstatus'])) {
					$canexchange = false;
				}

				if (0 < $row['merchid']) {
					$canexchange = false;
				}

				if ($row['isverify'] == 1) {
					if (empty($row['storeid'])) {
						$canexchange = false;
					}
				}
			} else {
				if (($row['status'] == 3) && (1 < $row['verifynum'])) {
					if ($row['verifynum'] <= $verifynum) {
						$canexchange = false;
					}
				} else {
					$canexchange = false;
				}
			}

			$row['canexchange'] = $canexchange;
		}
		unset($row);

		$pager = $list->render();
		$this->assign(['list'=>$list,'pager'=>$pager,'verifynum'=>$verifynum,'type'=>$type]);
		return $this->fetch('creditshop/log/index');
	}

	public function detail()
	{
		$id = intval(input('id'));
		$log = Db::name('shop_creditshop_log')->where('id',$id)->find();

		if (empty($log)) {
			$this->error('兑换记录不存在!', referer(), 'error');
		}

		$member = model('member')->getMember($log['mid']);
		$goods = model('creditshop')->getGoods($log['goodsid'], $member, $log['optionid']);

		if (empty($goods['id'])) {
			$this->message('商品记录不存在!', referer(), 'error');
		}

		$set = model('common')->getPluginset('creditshop');
		$canexchange = true;

		if ($log['status'] == 2) {
			if (empty($log['paystatus'])) {
				$canexchange = false;
			}

			if (empty($log['dispatchstatus'])) {
				$canexchange = false;
			}

			if (($goods['isverify'] == 1) && empty($log['storeid'])) {
				$canexchange = false;
			}
		}
		else {
			$canexchange = false;
		}

		$log['canexchange'] = $canexchange;

		if (!empty($goods['isverify'])) {
			if (!empty($log['storeid'])) {
				$store = Db::name('shop_store')->where('id',$log['storeid'])->field('id,storename,address')->find();
			}
		} else {
			$address = iunserializer($log['address']);

			if (!is_array($address)) {
				$address = Db::name('shop_member_address')->where('id',$log['addressid'])->field('realname,mobile,address,province,city,area')->find();
			}
		}
		$this->assign(['log'=>$log,'address'=>$address,'member'=>$member,'goods'=>$goods]);
		return $this->fetch('creditshop/log/detail');
	}

	public function comment()
	{
		return $this->fetch('creditshop/comment/index');
	}

	public function commentcheck()
	{
		return $this->fetch('creditshop/comment/check');
	}

	public function set()
	{
		$data = model('common')->getPluginset('creditshop');
		if (Request::instance()->isPost()) {
			$data = (is_array($_POST['data']) ? $_POST['data'] : array());

			$data['set_realname'] = intval($_POST['data']['set_realname']);
			$data['set_mobile'] = intval($_POST['data']['set_mobile']);
			$data['isdetail'] = intval($_POST['data']['isdetail']);
			$data['isnoticedetail'] = intval($_POST['data']['isnoticedetail']);
			$data['detail'] = model('common')->html_images($_POST['data']['detail']);
			$data['noticedetail'] = model('common')->html_images($_POST['data']['noticedetail']);
			model('common')->updatePluginset(array('creditshop' => $data));
			model('shop')->plog('creditshop.set.edit', '修改积分商城基本设置');
			show_json(1, array('url' => url('admin/creditshop/set', array('tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}

		$this->assign(['data'=>$data]);
		return $this->fetch('creditshop/set');
	}

}