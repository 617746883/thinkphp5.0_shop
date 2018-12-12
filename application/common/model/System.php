<?php
namespace app\common\model;
use think\Db;
use think\Request;
class System extends \think\Model
{
	public static function init($merch = 0)
    {
    	$request = Request::instance();
		$route = strtolower($request->module() . '/' . $request->controller() . '/' . $request->action());
    	$routes = explode('/', $route);
        $arr = array(
			'merch'       => $merch,
			'order1'      => 1,
			'order4'      => 0,
			'notice'      => array(),
			'comment'     => 0,
			'foldnav'     => 0,
			'foldpanel'   => 0,
			'routes'      => $routes,
			'right_menu'  => self::initRightMenu($routes,$merch)
		);
		$arr['order1'] = self::getOrderTotal(1,$merch);
		$arr['order4'] = self::getOrderTotal(4,$merch);
		$arr['comment'] = Db::name('shop_order_comment')->alias('c')->join('shop_goods g','g.id=c.goodsid','left')->where('(c.checked=1 OR c.replychecked=1) AND c.deleted=0 AND g.merchid=0')->count();
		return $arr;
    }

    protected function getOrderTotal($status = 0,$merch = 0)
	{
		$total = 0;

		if ($status == 1) {
			$condition = ' merchshow=1 and ismr=0 and ( status=1 or ( status=0 and paytype=3) ) and deleted=0';

			if ($merch) {
				$condition .= ' and merchid= ' . $merch;
			} else {
				$condition .= ' and merchid= 0';
			} 

			$total = Db::name('shop_order')->where($condition)->count();
		} else {
			if ($status == 4) {
				$condition = ' merchshow=1 and ismr=0 and refundstate>0 and refundid<>0 and deleted=0';

				if ($merch) {
					$condition .= ' and merchid= ' . $merch;
				} else {
					$condition .= ' and merchid= 0';
				}

				$total = Db::name('shop_order')->where($condition)->count();
			}
		}

		return $total;
	}

    /**
     * 初始化右侧顶部菜单
     */
	protected static function initRightMenu($routes, $merch = false)
	{
		$shopset = model('common')->getSysset();
		$return_arr = array(
			'system'     => 0,
			'menu_title' => '',
			'menu_items' => array(),
			'logout'     => ''
		);
		
		if ($merch) {
			$return_arr['menu_title'] = session('?account') ? session('account')['username'] : '商户管理后台';
			$return_arr['menu_items'][] = array('text' => '修改密码', 'href' => url('merch/user/updatepwd'));
			$return_arr['logout'] = url('merch/system/loginout');
		} else {
			$return_arr['menu_title'] = session('?admin') ? session('admin')['username'] : '商城管理后台';
			if ($routes[1] != 'system') {
				$return_arr['system'] = 1;
			}

			if ($routes[1] == 'system') {
				$return_arr['menu_items'][] = array('text' => '返回商城', 'href' => url('admin/index/index'), 'blank' => true);
				$return_arr['logout'] = url('admin/system/loginout');
			} else {
				$return_arr['menu_items'][] = 'line';
				$return_arr['menu_items'][] = array('text' => '我的信息', 'href' => url('admin/user/index'), 'blank' => true);
				$return_arr['menu_items'][] = array('text' => '修改密码', 'href' => url('admin/user/index'), 'blank' => true);
				$return_arr['menu_items'][] = 'line';
				$return_arr['menu_items'][] = array('text' => '权限管理', 'href' => url('admin/system/perm'), 'blank' => true);
				$return_arr['menu_items'][] = array('text' => '返回系统', 'href' => url('/'), 'blank' => true);
				$return_arr['logout'] = url('admin/system/loginout');
			}
		}
		return $return_arr;
	}

	/**
     * 获取 全部菜单带路由
     * @param bool $full 是否返回长URL
     * @return array
     */
	public function getMenu($full = false, $merch = 0)
	{
		$return_menu = array();
		$return_submenu = array();
		$request = Request::instance();
		$route = strtolower($request->module() . '/' . $request->controller() . '/' . $request->action());
		$routes = explode('/', $route);
		$top = strtolower($request->controller());

		if(empty($merch)) {
			$allmenus = $this->shopMenu();
			$mod = 'admin';
		} else {
			$allmenus = $this->merchMenu();
			$mod = 'merch';
		}		

		if (!empty($allmenus)) {
			$submenu = $allmenus[$top];
			if (empty($submenu)) {
				$othermenu = $this->otherMenu();
				if (!empty($othermenu[$top])) {
					$submenu = $othermenu[$top];
				}
			}
			if (empty($submenu)) {
				$submenu = $this->pluginMenu($top);
				$isplugin = true;
			}
			foreach ($allmenus as $key => $val) {
				$menu_item = array('route' => empty($val['route']) ? $key : $val['route'], 'text' => $val['title']);
				if (!empty($val['index'])) {
					$menu_item['index'] = $val['index'];
				}

				if (!empty($val['param'])) {
					$menu_item['param'] = $val['param'];
				}

				if (!empty($val['icon'])) {
					$menu_item['icon'] = $val['icon'];

					if (!empty($val['iconcolor'])) {
						$menu_item['iconcolor'] = $val['iconcolor'];
					}
				}

				if (($top == $menu_item['route']) || ($menu_item['route'] == $route) || (('{$mod}/system/' . $top) == $menu_item['route'])) {
					$menu_item['active'] = 1;
				}

				if ($full) {
					$menu_item['url'] = url("{$mod}/{$menu_item['route']}/index");
				}
				$return_menu[] = $menu_item;
			}
			unset($key);
			unset($val);
			if (!empty($submenu)) {
				$return_submenu['subtitle'] = $submenu['subtitle'];

				if ($submenu['main']) {
					$return_submenu['route'] = $top;
					if (is_string($submenu['main'])) {
						$return_submenu['route'] .= '.' . $submenu['main'];
					}
				}

				if (!empty($submenu['index'])) {
					$return_submenu['route'] = $top . '.' . $submenu['index'];
				}
				if (!empty($submenu['items'])) {
					foreach ($submenu['items'] as $i => $child) {
						if (!empty($child['top'])) {
							$top = '';
						}
						if (empty($child['items'])) {
							$return_submenu_default = $top . '';
							if (!empty($child['route'])) {
								if (!empty($top)) {
									$route_second .= '';
								}
								$route_second .= $child['route'];
							}
							$return_menu_child = array('title' => $child['title'], 'route' => empty($child['route']) ? $return_submenu_default : $route_second);
							if (!empty($child['param'])) {
								$return_menu_child['param'] = $child['param'];
							}

							if (!empty($child['perm'])) {
								$return_menu_child['perm'] = $child['perm'];
							}

							if (!empty($child['permmust'])) {
								$return_menu_child['permmust'] = $child['permmust'];
							}

							if ($routes[1] == 'system') {
								$return_menu_child['route'] = 'admin/system/' . $return_menu_child['route'];
							}

							$addedit = false;

							if (!$child['route_must']) {
								if ((($return_menu_child['route'] . '/add') == $route) || (($return_menu_child['route'] . '/edit') == $route)) {
									$addedit = true;
								}
							}

							if ($child['route_in'] || strexists($route, $return_menu_child['route'])) {
								$return_menu_child['active'] = 1;
							}

							if ($full) {
								$return_menu_child['url'] = url("{$mod}/{$top}/{$return_menu_child['route']}");
							}

							$return_submenu['items'][] = $return_menu_child;
							unset($return_submenu_default);
							unset($route_second);
						}
						else
						{
							$return_menu_child = array(
								'title' => $child['title'],
								'items' => array()
							);
							foreach ($child['items'] as $ii => $three) {
								$return_submenu_default = $top . '';
								$route_second = 'main';
								if (!empty($child['route'])) {
									$return_submenu_default = $top . '/' . $child['route'];
									$route_second = $child['route'];
								}
								$return_submenu_three = array('title' => $three['title']);
								if (!empty($three['route'])) {
									if (!empty($child['route'])) {
										if (!empty($three['route_ns'])) {
											$return_submenu_three['route'] = $top . '/' . $three['route'];
										}
										else {
											$return_submenu_three['route'] = $top . '/' . $child['route'] . '/' . $three['route'];
										}
									}
									else {
										if (!empty($three['top'])) {
											$return_submenu_three['route'] = $three['route'];
										}
										else {
											$return_submenu_three['route'] = $top . '/' . $three['route'];
										}

										$route_second = $three['route'];
									}
								}
								else {
									$return_submenu_three['route'] = $return_submenu_default;
								}
								if (!empty($three['param'])) {
									$return_submenu_three['param'] = $three['param'];
								}

								if (!empty($three['perm'])) {
									$return_submenu_three['perm'] = $three['perm'];
								}

								if (!empty($three['permmust'])) {
									$return_submenu_three['permmust'] = $three['permmust'];
								}

								// if ($routes[1] == 'system') {
								// 	$return_submenu_three['route'] = '{$mod}/system/' . $return_submenu_three['route'];
								// }
								$addedit = false;

								if (!$three['route_must']) {
									if ((($return_submenu_three['route'] . '/add') == $route) || (($return_submenu_three['route'] . '/edit') == $route)) {
										$addedit = true;
									}
								}
								if ($three['route_in'] || strexists($route, $return_submenu_three['route'])) {
									$return_menu_child['active'] = 1;
									$return_submenu_three['active'] = 1;
								}
								if (!empty($child['extend'])) {
									if ($child['extend'] == $route) {
										$return_menu_child['active'] = 1;
									}
								} else {
									if (is_array($child['extends'])) {
										if (in_array($route, $child['extends'])) {
											$return_menu_child['active'] = 1;
										}
									}
								}

								if ($full) {
									$return_submenu_three['url'] = url("{$mod}/{$return_submenu_three['route']}");
								}
								$return_menu_child['items'][] = $return_submenu_three;
							}
							if (!empty($child['items']) && empty($return_menu_child['items'])) {
								continue;
							}

							$return_submenu['items'][] = $return_menu_child;
							unset($ii);
							unset($three);
							unset($route_second);
						}
					}
				}
			}
		}
		
		return array('menu' => $return_menu, 'submenu' => $return_submenu, 'shopmenu' => self::getShopMenu($merch));
	}

	/**
     * 获取 主商城菜单
     * @return array
     */
	public function getShopMenu($merch = 0)
	{
		$return_menu = array();

		if (!$merch) {
			$menus = $this->shopMenu();
		} else {
			$menus = $this->pluginMenu('merch');
		}

		foreach ($menus as $key => $val) {
			$menu_item = array(
				'title' => $val['subtitle'],
				'items' => array()
			);

			if (empty($val['items'])) {
				continue;
			}

			foreach ($val['items'] as $child) {
				$child_route_default = $key;

				if (!empty($child['route'])) {
					$child_route_default = $key . '/' . $child['route'];

					if (!empty($child['top'])) {
						$child_route_default = $child['route'];
					}
				}

				if (empty($child['items'])) {
					$menu_item_child = array('title' => $child['title'], 'route' => $child_route_default);

					if (!empty($child['param'])) {
					}

					$menu_item_child['url'] = url("admin/{$menu_item_child['route']}");
					$menu_item['items'][] = $menu_item_child;
				} else {
					foreach ($child['items'] as $three) {
						$menu_item_three = array('title' => $three['title'], 'route' => empty($three['route']) ? $child_route_default : $child_route_default . '/' . $three['route']);

						if (!empty($three['param'])) {
						}

						$menu_item_three['url'] = url("admin/{$menu_item_three['route']}");
						$menu_item['items'][] = $menu_item_three;
					}
				}
			}

			$return_menu[] = $menu_item;
		}

		return $return_menu;
	}

	/**
     * 定义 商城 菜单
     * @return array
     */
	protected function shopMenu()
	{
		$shopmenu = array(
			'community'      => array(
				'title'    => '小区',
				'subtitle' => '小区服务',
				'icon'     => 'wangzhan',
				'items'    => array(					
					array('title' => '小区', 'route' => 'index', 'desc' => '小区管理'),
					array('title' => '小区楼栋', 'route' => 'building', 'desc' => '小区楼栋管理'),
					array('title' => '小区业主', 'route' => 'house', 'desc' => '小区房屋管理'),
					array('title' => '幻灯片', 'route' => 'banner', 'desc' => '首页幻灯片管理'),
					array('title' => '通知公告', 'route' => 'notice', 'desc' => '通知公告管理'),
					array('title' => '租房房源', 'route' => 'housing', 'desc' => '租房房源管理'),
					array('title' => '缴费申请', 'route' => 'payment', 'desc' => '水电物业缴费申请管理'),
					array('title' => '报修申请', 'route' => 'repair', 'desc' => '小区报修申请管理'),
					array('title' => '设置', 'route' => 'set', 'desc' => '小区设置管理'),
					)
				),
			'citywide'      => array(
				'title'    => '同城',
				'subtitle' => '同城管理',
				'icon'     => 'star',
				'items'    => array(
					array('title' => '幻灯片', 'route' => 'banner', 'desc' => '首页幻灯片管理'),
					array(
						'title' => '生活服务',
						'route' => '',
						'items' => array(
							array('title' => '幻灯片', 'route' => 'lifebanner', 'desc' => '生活服务首页幻灯片管理'),
							array('title' => '门店分类', 'route' => 'lifecategory', 'desc' => '门店分类管理'),
							array('title' => '门店管理', 'route' => 'lifestore', 'desc' => '门店管理'),
							)
						),
					array(
						'title' => '二手市场',
						'items' => array(
							array('title' => '宝贝分类', 'route' => 'secondcategory', 'desc' => '宝贝分类管理'),
							array('title' => '宝贝管理', 'route' => 'secondgoods', 'desc' => '宝贝管理'),
							)
						),
					array('title' => '设置', 'route' => 'set', 'desc' => '同城设置管理'),
					)
				),
			'shop'       => array(
				'title'    => '商城',
				'subtitle' => '商城首页',
				'icon'     => 'store',
				'items'    => array(
					array(
						'title' => '首页',
						'route' => '',
						'items' => array(
							array('title' => '幻灯片', 'route' => 'banner', 'desc' => '商城首页幻灯片管理'),
							array('title' => '导航图标', 'route' => 'nav', 'desc' => '商城首页导航图标管理'),
							array('title' => '广告', 'route' => 'adv', 'desc' => '商城首页广告管理'),
							array('title' => '魔方推荐', 'route' => 'cube', 'desc' => '商城首页魔方推荐管理'),
							array('title' => '商品推荐', 'route' => 'recommand', 'desc' => '商城首页商品推荐管理'),
							array('title' => '排版设置', 'route' => 'composition', 'desc' => '商城首页排版设置')
							)
						),
					array(
						'title' => '商城',
						'items' => array(
							array('title' => '公告管理', 'route' => 'notice', 'desc' => '商城公告管理'),
							array('title' => '评价管理', 'route' => 'comment', 'desc' => '商城商品评价管理'),
							array('title' => '退货地址', 'route' => 'refundaddress', 'desc' => '退换货地址管理'),
							)
						),
					array(
						'title' => '配送方式',
						'items' => array(
							array('title' => '普通配送', 'route' => 'dispatch', 'desc' => '普通配送方式管理'),
							array('title' => '同城配送', 'route' => 'cityexpress', 'desc' => '同城配送管理'),
							)
						),
					)
				),
			'goods'      => array(
				'title'    => '商品',
				'subtitle' => '商品管理',
				'icon'     => 'goods',
				'items'    => array(
					array('title' => '出售中', 'route' => 'sale', 'desc' => '出售中商品管理', 'extend' => 'goods/sale', 'perm' => 'goods.main'),
					array('title' => '已售罄', 'route' => 'out', 'desc' => '已售罄/无库存商品管理', 'perm' => 'goods.main'),
					array('title' => '仓库中', 'route' => 'stock', 'desc' => '仓库中商品管理', 'perm' => 'goods.main'),
					array('title' => '回收站', 'route' => 'cycle', 'desc' => '回收站/已删除商品管理', 'perm' => 'goods.main'),
					array('title' => '待审核', 'route' => 'verify', 'desc' => '多商户待审核商品管理', 'perm' => 'goods.main'),
					array('title' => '商品分类', 'route' => 'category'),
					array('title' => '商品组', 'route' => 'group'),
					array('title' => '标签管理', 'route' => 'label', 'extend' => 'goods.label.style'),
					)
				),	
			'order'      => array(
				'title'    => '订单',
				'subtitle' => '订单管理',
				'icon'     => 'order',
				'items'    => array(
					array('title' => '订单概述', 'route' => 'index', 'desc' => '订单概述'),
					array('title' => '待发货', 'route' => 'olist1', 'desc' => '待发货订单管理'),
					array('title' => '待收货', 'route' => 'olist2', 'desc' => '待收货订单管理'),
					array('title' => '待付款', 'route' => 'olist0', 'desc' => '待付款订单管理'),
					array('title' => '已完成', 'route' => 'olist3', 'desc' => '已完成订单管理'),
					array('title' => '已关闭', 'route' => 'olist_1', 'desc' => '已关闭订单管理'),
					array('title' => '核销订单', 'route' => 'olist6', 'desc' => '核销订单管理'),
					array('title' => '全部订单', 'route' => 'olist_all', 'desc' => '全部订单列表'),
					array(
						'title' => '售后',
						'items' => array(
							array('title' => '售后申请', 'route' => 'refund4', 'desc' => '维权申请管理'),
							array('title' => '售后完成', 'route' => 'refund5', 'desc' => '维权完成管理')
							)
						),
					// array(
					// 	'title' => '工具',
					// 	'items' => array(
					// 		array('title' => '自定义导出', 'route' => 'export', 'desc' => '订单自定义导出'),
					// 		array('title' => '批量发货', 'route' => 'batchsend', 'desc' => '订单批量发货')
					// 		)
					// 	)
					)
				),
			'merch'      => array(
				'title'    => '商户',
				'subtitle' => '商户',
				'icon'     => 'mendianguanli',
				'items'    => array(
					array(
						'title' => '入驻申请',
						'items' => array(
							array('title' => '申请中', 'route' => 'reg1'),
							array('title' => '驳回', 'route' => 'reg0')
							)
						),
					array(
						'title' => '商户管理',
						'items' => array(
							array('title' => '待入驻', 'route' => 'user0'),
							array('title' => '入驻中', 'route' => 'user1'),
							array('title' => '暂停中', 'route' => 'user2'),
							array('title' => '即将到期', 'route' => 'user3'),
							array('title' => '商户分组', 'route' => 'group'),
							array('title' => '商户分类', 'route' => 'category')
							)
						),
					array(
						'title' => '数据统计',
						'items' => array(
							array('title' => '订单统计', 'route' => 'order'),
							array('title' => '商户统计', 'route' => 'merch')
							)
						),
					array(
						'title' => '提现申请',
						'items' => array(
							array('title' => '待确认申请', 'route' => 'check1'),
							array('title' => '待打款申请', 'route' => 'check2'),
							array('title' => '已打款申请', 'route' => 'check3'),
							array('title' => '无效申请', 'route' => 'check_1')
							)
						),
					array(
						'title' => '线下门店',
						'items' => array(
							array('title' => '门店管理', 'route' => 'store'),
							array('title' => '店员管理', 'route' => 'saler')
							)
						),
					array('title' => '基础设置', 'route' => 'set', 'desc' => '商户基础设置管理'),
					)					
				),	
			'member'     => array(
				'title'    => '会员',
				'subtitle' => '会员管理',
				'icon'     => 'member',
				'items'    => array(
					array('title' => '会员概述', 'route' => 'index'),
					array('title' => '会员列表', 'route' => 'mlist'),
					array('title' => '会员等级', 'route' => 'level'),
					array('title' => '会员分组', 'route' => 'group'),
					array('title' => '设置', 'route' => 'set'),
					)
				),
			// 'sale'       => array(
			// 	'title'    => '营销',
			// 	'subtitle' => '营销设置',
			// 	'icon'     => 'yingxiao',
			// 	'items'    => array(
			// 		array(
			// 			'title' => '基本功能',
			// 			'items' => array(
			// 				array('title' => '满额立减', 'route' => 'enough', 'desc' => '满额立减设置', 'keywords' => '营销'),
			// 				array('title' => '满额包邮', 'route' => 'enoughfree', 'desc' => '满额包邮设置', 'keywords' => '营销'),
			// 				array('title' => '抵扣设置', 'route' => 'deduct', 'desc' => '抵扣设置', 'keywords' => '营销'),
			// 				array('title' => '充值优惠', 'route' => 'recharge', 'desc' => '充值优惠设置', 'keywords' => '营销'),
			// 				array('title' => '积分优惠', 'route' => 'credit1', 'desc' => '积分优惠设置', 'keywords' => '营销'),
			// 				array('title' => '套餐管理', 'route' => 'package', 'keywords' => '营销'),
			// 				array('title' => '赠品管理', 'route' => 'gift', 'keywords' => '营销'),
			// 				array('title' => '全返管理', 'route' => 'fullback', 'keywords' => '营销'),
			// 				array('title' => '找人代付', 'route' => 'peerpay', 'keywords' => '营销'),
			// 				array('title' => '绑定送积分', 'route' => 'bindmobile', 'keywords' => '营销')
			// 				)
			// 			),
			// 		array(
			// 			'title' => '优惠券',
			// 			'route' => 'coupon',
			// 			'iscom' => 'coupon',
			// 			'items' => array(
			// 				array('title' => '全部优惠券'),
			// 				array('title' => '手动发送', 'route' => 'sendcoupon', 'desc' => '手动发送优惠券'),
			// 				array(
			// 					'title'   => '购物送券',
			// 					'route'   => 'shareticket',
			// 					'extends' => array('sale/coupon/goodssend', 'sale/coupon/usesendtask', 'sale/coupon/goodssend/add', 'sale/coupon/usesendtask/add')
			// 					),
			// 				array('title' => '发放记录', 'route' => 'log', 'desc' => '优惠券发放记录'),
			// 				array('title' => '分类管理', 'route' => 'category', 'desc' => '优惠券分类管理'),
			// 				array('title' => '其他设置', 'route' => 'set', 'desc' => '优惠券设置')
			// 				)
			// 			)
			// 		)
			// 	),
			'finance'    => array(
				'title'    => '财务',
				'subtitle' => '财务管理',
				'icon'     => '31',
				'items'    => array(
					array(
						'title' => '财务',
						'items' => array(
							// array('title' => '充值记录', 'route' => 'recharge'),
							array('title' => '提现申请', 'route' => 'withdraw')
							)
						),
					array(
						'title' => '明细',
						'items' => array(
							array('title' => '交易明细', 'route' => 'transaction'),
							array('title' => '积分明细', 'route' => 'credit1'),
							array('title' => '余额明细', 'route' => 'credit2')
							)
						),
					// array(
					// 	'title' => '对账单',
					// 	'items' => array(
					// 		array('title' => '下载对账单', 'route' => 'downloadbill')
					// 		)
					// 	)
					)
				),
			'statistics' => array(
				'title'    => '数据',
				'subtitle' => '数据统计',
				'icon'     => 'statistics',
				'items'    => array(
					array(
						'title' => '销售统计',
						'items' => array(
							array('title' => '销售统计', 'route' => 'sale_census'),
							// array('title' => '销售指标', 'route' => 'sale_analysis'),
							)
						),
					array(
						'title' => '商品统计',
						'items' => array(
							array('title' => '销售明细', 'route' => 'goods_detailed'),
							array('title' => '销售排行', 'route' => 'goods_rank'),
							array('title' => '销售转化率', 'route' => 'goods_trans')
							)
						),
					array(
						'title' => '会员统计',
						'items' => array(
							array('title' => '消费排行', 'route' => 'member_cost'),
							array('title' => '增长趋势', 'route' => 'member_increase')
							)
						)
					)
				),	
			'plugins'    => array('title' => '应用', 'subtitle' => '应用管理', 'icon' => 'plugins'),
			'sysset'     => array(
				'title'    => '设置',
				'subtitle' => '商城设置',
				'icon'     => 'sysset',
				'items'    => array(
					array(
						'title' => '商城',
						'items' => array(
							array('title' => '基础设置', 'route' => 'index'),
							array('title' => '商城状态', 'route' => 'close'),
							)
						),
					array(
						'title' => '交易',
						'items' => array(
							array('title' => '交易设置', 'route' => 'trade'),
							array('title' => '支付设置', 'route' => 'payset'),
							)
						),
					array(
						'title' => '推送/短信配置',
						'items' => array(
							array('title' => '推送接口设置', 'route' => 'notice'),
							array('title' => '短信接口设置', 'route' => 'smsset')
							)
						),
					array(
						'title' => '其他',
						'items' => array(
							array('title' => '会员设置', 'route' => 'member'),
							array('title' => '分类层级', 'route' => 'category'),
							array('title' => '联系方式', 'route' => 'contact'),
							array('title' => '地址库设置', 'route' => 'area'),
							array('title' => '物流信息接口', 'route' => 'express')
							)
						),
					// array(
					// 	'title' => '工具',
					// 	'items' => array(
					// 		// array('title' => '七牛存储', 'route' => 'qiniu', 'iscom' => 'qiniu'),
					// 		// array('title' => '清理缓存', 'route' => 'goodsprice'),
					// 		// array('title' => '数据库优化', 'route' => 'funbar')
					// 		)
					// 	)
					)
				),
			'system'    => array(
				'title'    => '系统',
				'subtitle' => '系统管理',
				'icon'     => 'wangzhan',
				'items'    => array(
					array('title' => '管理员管理', 'route' => 'admin'),
					array('title' => '操作日志', 'route' => 'plog'),
					array(
						'title'    => '权限管理',
						'items'    => array(
							array('title' => '权限管理', 'route' => 'role'),
							array('title' => '权限组', 'route' => 'perm'),
							)
						),
					array(
						'title'    => '常用工具',
						'items'    => array(
							array('title' => '更新缓存', 'route' => 'updatecache'),
							array('title' => '数据库', 'route' => 'database'),
							array('title' => '消息推送', 'route' => 'push'),
							)
						),
					array(
						'title'    => '版权',
						'items'    => array(
							array('title' => '手机端', 'route' => 'copyrightweb'),
							array('title' => '管理端', 'route' => 'copyrightmanage'),
							)
						),
					array('title' => '意见反馈', 'route' => 'feedback'),
					)
				),
			);

		return $shopmenu;
	}

	/**
     * 定义 商城 菜单
     * @return array
     */
	protected function merchMenu()
	{
		$merchmenu = array(
			'shop'       => array(
				'title'    => '店铺',
				'subtitle' => '店铺首页',
				'icon'     => 'store',
				'items'    => array(
					// array(
					// 	'title' => '首页',
					// 	'route' => '',
					// 	'items' => array(
					// 		array('title' => '幻灯片', 'route' => 'banner', 'desc' => '商城首页幻灯片管理'),
					// 		array('title' => '导航图标', 'route' => 'nav', 'desc' => '商城首页导航图标管理'),
					// 		array('title' => '广告', 'route' => 'adv', 'desc' => '商城首页广告管理'),
					// 		array('title' => '魔方推荐', 'route' => 'cube', 'desc' => '商城首页魔方推荐管理'),
					// 		array('title' => '商品推荐', 'route' => 'recommand', 'desc' => '商城首页商品推荐管理'),
					// 		array('title' => '排版设置', 'route' => 'composition', 'desc' => '商城首页排版设置')
					// 		)
					// 	),
					array(
						'title' => '商城',
						'items' => array(
							array('title' => '公告管理', 'route' => 'notice', 'desc' => '商城公告管理'),
							array('title' => '评价管理', 'route' => 'comment', 'desc' => '商城商品评价管理'),
							array('title' => '退货地址', 'route' => 'refundaddress', 'desc' => '退换货地址管理'),
							)
						),
					array(
						'title' => '配送方式',
						'items' => array(
							array('title' => '普通配送', 'route' => 'dispatch', 'desc' => '普通配送方式管理'),
							array('title' => '同城配送', 'route' => 'cityexpress', 'desc' => '同城配送管理'),
							)
						),
					array(
						'title' => 'O2O',
						'items' => array(
							array('title' => '门店管理', 'route' => 'store', 'desc' => '门店管理'),
							array('title' => '店员管理', 'route' => 'saler', 'desc' => '店员管理'),
							)
						),
					)
				),
			'goods'      => array(
				'title'    => '商品',
				'subtitle' => '商品管理',
				'icon'     => 'goods',
				'items'    => array(
					array('title' => '出售中', 'route' => 'sale', 'desc' => '出售中商品管理', 'extend' => 'goods/sale', 'perm' => 'goods.main'),
					array('title' => '审核中', 'route' => 'check', 'desc' => '多商户待审核商品管理', 'perm' => 'goods.main'),
					array('title' => '已售罄', 'route' => 'out', 'desc' => '待上架商品', 'perm' => 'goods.main'),
					array('title' => '仓库中', 'route' => 'stock', 'desc' => '仓库中商品管理', 'perm' => 'goods.main'),
					array('title' => '回收站', 'route' => 'cycle', 'desc' => '回收站/已删除商品管理', 'perm' => 'goods.main'),
					array('title' => '商品分类', 'route' => 'category'),
					// array('title' => '标签管理', 'route' => 'label', 'extend' => 'goods.label.style'),
					)
				),	
			'order'      => array(
				'title'    => '订单',
				'subtitle' => '订单管理',
				'icon'     => 'order',
				'items'    => array(
					array('title' => '订单概述', 'route' => 'index', 'desc' => '订单概述'),
					array('title' => '待发货', 'route' => 'olist1', 'desc' => '待发货订单管理'),
					array('title' => '待收货', 'route' => 'olist2', 'desc' => '待收货订单管理'),
					array('title' => '待付款', 'route' => 'olist0', 'desc' => '待付款订单管理'),
					array('title' => '已完成', 'route' => 'olist3', 'desc' => '已完成订单管理'),
					array('title' => '已关闭', 'route' => 'olist_1', 'desc' => '已关闭订单管理'),
					array('title' => '核销订单', 'route' => 'olist6', 'desc' => '核销订单管理'),
					array('title' => '全部订单', 'route' => 'olist_all', 'desc' => '全部订单列表'),
					array(
						'title' => '售后',
						'items' => array(
							array('title' => '售后申请', 'route' => 'refund4', 'desc' => '维权申请管理'),
							array('title' => '售后完成', 'route' => 'refund5', 'desc' => '维权完成管理')
							)
						)
					)
				),
			// 'sale'       => array(
			// 	'title'    => '营销',
			// 	'subtitle' => '营销设置',
			// 	'icon'     => 'yingxiao',
			// 	'items'    => array(
			// 		array(
			// 			'title' => '基本功能',
			// 			'items' => array(
			// 				array('title' => '满额立减', 'route' => 'enough', 'desc' => '满额立减设置', 'keywords' => '营销'),
			// 				array('title' => '满额包邮', 'route' => 'enoughfree', 'desc' => '满额包邮设置', 'keywords' => '营销'),
			// 				array('title' => '抵扣设置', 'route' => 'deduct', 'desc' => '抵扣设置', 'keywords' => '营销'),
			// 				array('title' => '充值优惠', 'route' => 'recharge', 'desc' => '充值优惠设置', 'keywords' => '营销'),
			// 				array('title' => '积分优惠', 'route' => 'credit1', 'desc' => '积分优惠设置', 'keywords' => '营销'),
			// 				array('title' => '套餐管理', 'route' => 'package', 'keywords' => '营销'),
			// 				array('title' => '赠品管理', 'route' => 'gift', 'keywords' => '营销'),
			// 				array('title' => '全返管理', 'route' => 'fullback', 'keywords' => '营销'),
			// 				array('title' => '找人代付', 'route' => 'peerpay', 'keywords' => '营销'),
			// 				array('title' => '绑定送积分', 'route' => 'bindmobile', 'keywords' => '营销')
			// 				)
			// 			),
			// 		array(
			// 			'title' => '优惠券',
			// 			'route' => 'coupon',
			// 			'iscom' => 'coupon',
			// 			'items' => array(
			// 				array('title' => '全部优惠券'),
			// 				array('title' => '手动发送', 'route' => 'sendcoupon', 'desc' => '手动发送优惠券'),
			// 				array(
			// 					'title'   => '购物送券',
			// 					'route'   => 'shareticket',
			// 					'extends' => array('sale/coupon/goodssend', 'sale/coupon/usesendtask', 'sale/coupon/goodssend/add', 'sale/coupon/usesendtask/add')
			// 					),
			// 				array('title' => '发放记录', 'route' => 'log', 'desc' => '优惠券发放记录'),
			// 				array('title' => '分类管理', 'route' => 'category', 'desc' => '优惠券分类管理'),
			// 				array('title' => '其他设置', 'route' => 'set', 'desc' => '优惠券设置')
			// 				)
			// 			)
			// 		)
			// 	),
			'statistics' => array(
				'title'    => '数据',
				'subtitle' => '数据统计',
				'icon'     => 'statistics',
				'items'    => array(
					array(
						'title' => '销售统计',
						'items' => array(
							array('title' => '销售统计', 'route' => 'sale_census'),
							array('title' => '销售指标', 'route' => 'sale_analysis'),
							)
						),
					array(
						'title' => '商品统计',
						'items' => array(
							array('title' => '销售明细', 'route' => 'goods_detailed'),
							array('title' => '销售排行', 'route' => 'goods_rank'),
							array('title' => '销售转化率', 'route' => 'goods_trans')
							)
						),
					array(
						'title' => '会员统计',
						'items' => array(
							array('title' => '消费排行', 'route' => 'member_cost'),
							array('title' => '增长趋势', 'route' => 'member_increase')
							)
						)
					)
				),	
			'perm'      => array(
				'title'    => '权限',
				'subtitle' => '权限管理',
				'icon'     => 'heimingdan2',
				'items'    => array(
					array('title' => '角色', 'route' => 'role', 'desc' => '角色管理', 'extend' => 'perm/index', 'perm' => 'perm.main'),
					array('title' => '操作员', 'route' => 'user', 'desc' => '操作员管理', 'perm' => 'perm.main'),
					array('title' => '操作员日志', 'route' => 'log', 'desc' => '操作员日志管理', 'perm' => 'perm.main'),
					)
				),	
			'apply'      => array(
				'title'    => '结算',
				'subtitle' => '结算管理',
				'icon'     => '31',
				'items'    => array(
					array('title' => '结算', 'route' => 'index', 'desc' => '结算管理', 'extend' => 'apply/index', 'perm' => 'apply.main'),
					array('title' => '待审核申请', 'route' => 'status1', 'desc' => '待审核申请管理', 'perm' => 'apply.main'),
					array('title' => '待结算申请', 'route' => 'status2', 'desc' => '待结算申请管理', 'perm' => 'apply.main'),
					array('title' => '已结算申请', 'route' => 'status3', 'desc' => '已结算申请管理', 'perm' => 'apply.main'),
					array('title' => '无效结算申请', 'route' => 'status_1', 'desc' => '无效结算申请管理', 'perm' => 'apply.main'),
					array('title' => '申请结算', 'route' => 'add', 'desc' => '申请结算管理', 'perm' => 'apply.main'),
					)
				),
			'plugins'    => array('title' => '应用', 'subtitle' => '应用管理', 'icon' => 'plugins'),
			'sysset'    => array('title' => '设置', 'subtitle' => '设置管理', 'icon' => 'sysset'),
			);

		return $merchmenu;
	}

	/**
     * 获取 系统管理 菜单
     * @return array
     */
	protected function systemMenu()
	{
		return array(
			'user'    => array(
				'title'    => '用户',
				'subtitle' => '用户管理',
				'icon'     => 'member',
				'items'    => array(
					array('title' => '管理员管理', 'route' => 'user'),
					array('title' => '操作日志', 'route' => 'plog'),
					array(
						'title'    => '权限管理',
						'isplugin' => 'grant',
						'items'    => array(
							array('title' => '权限管理', 'route' => 'perm'),
							array('title' => '权限组', 'route' => 'group'),
							)
						)
					)
				),
			'copyright' => array(
				'title'    => '版权',
				'subtitle' => '版权设置',
				'icon'     => 'banquan',
				'items'    => array(
					array('title' => '手机端', 'route' => 'web'),
					array('title' => '管理端', 'route' => 'manage'),
					)
				),
		);
	}

	/**
     * 获取 其他 菜单
     * @return array
     */
	protected function otherMenu()
	{
		return array(
			'perm' => array(
				'title'    => '权限',
				'subtitle' => '权限系统',
				'icon'     => 'store',
				'items'    => array(
					array('title' => '角色管理', 'route' => 'role'),
					array('title' => '操作员管理', 'route' => 'user'),
					array('title' => '操作日志', 'route' => 'log')
				)
			)
		);
	}

	/**
     * 获取 插件 菜单
     * @param array $plugin 要获取的插件标识
     * @return array
     */
	protected function pluginMenu($plugin = '')
	{
		if (empty($plugin)) {
			return array();
		}
		$allmenus = $this->allPluginMenu();
		return $allmenus[$plugin];
	}

	/**
     * 获取 全部插件 菜单
     * @return array
     */
	protected function allPluginMenu()
	{
		return array(
			'groups' => array(
				'title'    => '团购',
				'subtitle' => '团购管理',
				'icon'     => 'page',
				'extend' => 'admin/plugins/index',
				'items'    => array(
					array('title' => '商品管理', 'route' => 'goods', 'extend' => 'admin/plugins/index'),
					array('title' => '分类管理', 'route' => 'category'),
					array('title' => '幻灯片管理', 'route' => 'banner'),
					array(
						'title'  => '团购管理',
						'extend' => 'groups/teamdetail',
						'items'  => array(
							array(
								'title' => '团购成功',
								'route' => 'teamsuccess'
								),
							array(
								'title' => '团购中',
								'route' => 'teaming'
								),
							array(
								'title' => '团购失败',
								'route' => 'teamerror'
								),
							array(
								'title' => '全部团购',
								'route' => 'teamall'
								)
							)
						),
					array(
						'title'  => '订单管理',
						'extend' => 'groups/orderdetail',
						'items'  => array(
							array(
								'title' => '待发货',
								'route' => 'order1'
								),
							array(
								'title' => '待收货',
								'route' => 'order2'
								),
							array(
								'title' => '待付款',
								'route' => 'order3'
								),
							array(
								'title' => '已完成',
								'route' => 'order4'
								),
							array(
								'title' => '已关闭',
								'route' => 'order5'
								),
							array(
								'title' => '全部订单',
								'route' => 'orderall'
								)
							)
						),
					array(
						'title'  => '维权设置',
						'extend' => 'groups.refund.detail',
						'items'  => array(
							array(
								'title' => '维权申请',
								'route' => 'refundapply'
								),
							array(
								'title' => '维权完成',
								'route' => 'refundover'
								)
							)
						),
					array('title' => '基础设置', 'route' => 'set'),
				)
			),
			'auction' => array(
				'title'    => '拍卖',
				'subtitle' => '拍卖管理',
				'icon'     => 'page',
				'items'    => array(
					array('title' => '拍品管理', 'route' => 'goods'),
					array('title' => '拍品分类', 'route' => 'category'),
					array('title' => '幻灯片管理', 'route' => 'banner'),
					array(
						'title'  => '竞拍管理',
						'extend' => 'auction/auctiondetail',
						'items'  => array(
							array(
								'title' => '竞拍成功',
								'route' => 'auctionsuccess'
								),
							array(
								'title' => '竞拍中',
								'route' => 'auctioning'
								),
							array(
								'title' => '竞拍失败',
								'route' => 'auctionerror'
								),
							array(
								'title' => '全部竞拍',
								'route' => 'auctionall'
								)
							)
						),
					array(
						'title'  => '订单管理',
						'extend' => 'auction/orderdetail',
						'items'  => array(
							array(
								'title' => '待发货',
								'route' => 'order1'
								),
							array(
								'title' => '待收货',
								'route' => 'order2'
								),
							array(
								'title' => '待付款',
								'route' => 'order3'
								),
							array(
								'title' => '已完成',
								'route' => 'order4'
								),
							array(
								'title' => '已关闭',
								'route' => 'order5'
								),
							array(
								'title' => '全部订单',
								'route' => 'orderall'
								)
							)
						),
					array(
						'title'  => '维权设置',
						'extend' => 'auction.refund.detail',
						'items'  => array(
							array(
								'title' => '维权申请',
								'route' => 'refundapply'
								),
							array(
								'title' => '维权完成',
								'route' => 'refundover'
								)
							)
						),
					array('title' => '基础设置', 'route' => 'set'),
				)
			),
			'creditshop'    => array(
				'title'    => '积分商城',
				'subtitle' => '积分商城管理',
				'icon'     => 'page',
				'items'     => array(
					array('title' => '商品管理', 'route' => 'goodslist'),
					array('title' => '分类管理', 'route' => 'category'),
					array('title' => '幻灯片管理', 'route' => 'banner'),
					array(
						'title' => '参与记录',
						'items' => array(
							array('title' => '兑换记录', 'route' => 'exchange', 'extend' => 'creditshop.log.detail'),
							array('title' => '抽奖记录', 'route' => 'draw')
							)
						),
					array(
						'title' => '评价管理',
						'items' => array(
							array('title' => '全部评价', 'route' => 'comment'),
							array('title' => '待审核', 'route' => 'commentcheck')
							)
						),
					array(
						'title' => '发货管理',
						'items' => array(
							array('title' => '待发货', 'route' => 'order'),
							array('title' => '待收货', 'route' => 'convey'),
							array('title' => '已完成', 'route' => 'finish')
							)
						),
					array(
						'title' => '核销管理',
						'items' => array(
							array('title' => '全部核销', 'route' => 'allverify'),
							array('title' => '待核销', 'route' => 'verifying'),
							array('title' => '已核销', 'route' => 'verifyover')
							)
						),
					array('title' => '基础设置', 'route' => 'set'),
				)
			),
			'seckill'    => array(
				'title'    => '秒杀',
				'subtitle' => '秒杀管理',
				'icon'     => 'page',
				'items'     => array(
					array('title' => '专题管理', 'route' => 'task'),
					array('title' => '会场管理', 'route' => 'room'),
					array('title' => '商品管理', 'route' => 'goods'),
					array('title' => '分类管理', 'route' => 'category'),
					array('title' => '广告管理', 'route' => 'adv'),
					array(
						'title' => '设置',
						'items' => array(
							array('title' => '任务设置', 'route' => 'calendar'),
							array('title' => '入口设置', 'route' => 'cover')
							)
						)
				)
			),
			'treasure'    => array(
				'title'    => '众筹夺宝',
				'subtitle' => '众筹夺宝管理',
				'icon'     => 'page',
				'items'     => array(
					array('title' => '商品管理', 'route' => 'goods'),
					array('title' => '商品分类', 'route' => 'category'),
					array('title' => '幻灯片管理', 'route' => 'banner'),
					array(
						'title' => '订单',
						'items' => array(
							array('title' => '待发货', 'route' => 'order'),
							array('title' => '待收货', 'route' => 'convey'),
							array('title' => '已完成', 'route' => 'finish')
						)
					)
				)
			),
			'article' => array(
				'title'    => '文章',
				'subtitle' => '文章营销',
				'icon'     => 'page',
				'items'    => array(
					array('title' => '文章管理', 'route' => 'index'),
					array('title' => '分类管理', 'route' => 'category'),
				)
			),
			'taobao' => array(
				'title'    => '商品助手',
				'subtitle' => '商品助手',
				'icon'     => 'page',
				'items'    => array(
					array('title' => '淘宝助手', 'route' => 'index'),
					array('title' => '京东助手', 'route' => 'jingdong'),
					array('title' => '1688助手', 'route' => 'one688'),
					// array('title' => '淘宝CSV上传', 'route' => 'taobaocsv'),
				)
			),
		);
	}

	/**
     * 获取 全部菜单带路由
     * @param bool $full 是否返回长URL
     * @return array
     */
	public function getSubMenus($full = false, $plugin = false)
	{
		$return_submenu = array();

		if (!$merch) {
			$systemMenu = $this->systemMenu();
			$allmenus = array_merge($this->shopMenu(), $systemMenu);

			if ($plugin) {
				$allmenus = array_merge($allmenus, $this->allPluginMenu());
			}
		}
		else {
			$allmenus = $this->pluginMenu('merch', 'manage_menu');
		}

		if (!empty($allmenus)) {
			foreach ($allmenus as $key => $item) {
				if (!$merch && is_array($systemMenu) && array_key_exists($key, $systemMenu)) {
					$key = 'system.' . $key;
				}

				if (empty($item['items'])) {
					$return_submenu_item = array('title' => $item['title'], 'top' => $key, 'toptitle' => $item['title'], 'topsubtitle' => $item['subtitle'], 'route' => empty($item['route']) ? $key : $item['route']);

					if (!empty($item['param'])) {
						$return_submenu_item = $item['param'];
					}

					if ($full) {
						$return_submenu_item['url'] = url($return_submenu_item['route'], !empty($return_submenu_item['param']) && is_array($return_submenu_item['param']) ? $return_submenu_item['param'] : array());
					}

					$return_submenu[] = $return_submenu_item;
				}
				else {
					foreach ($item['items'] as $i => $child) {
						if (empty($child['items'])) {
							$return_submenu_default = $key;
							$return_submenu_route = $key . '.' . $child['route'];
							$return_submenu_child = array('title' => $child['title'], 'top' => $key, 'toptitle' => $item['title'], 'topsubtitle' => $item['subtitle'], 'route' => empty($child['route']) ? $return_submenu_default : $return_submenu_route);

							if (!empty($child['desc'])) {
								$return_submenu_child['desc'] = $child['desc'];
							}

							if (!empty($child['keywords'])) {
								$return_submenu_child['keywords'] = $child['keywords'];
							}

							if (!empty($child['param'])) {
								$return_submenu_child['param'] = $child['param'];
							}

							if ($full) {
								$return_submenu_child['url'] = url($return_submenu_child['route'], !empty($return_submenu_child['param']) && is_array($return_submenu_child['param']) ? $return_submenu_child['param'] : array());
							}

							$return_submenu[] = $return_submenu_child;
						}
						else {
							foreach ($child['items'] as $ii => $three) {
								$return_submenu_default = $key;

								if (!empty($child['route'])) {
									$return_submenu_default = $key . '.' . $child['route'];
								}

								$return_submenu_three = array('title' => $three['title'], 'top' => $key, 'topsubtitle' => $item['subtitle']);

								if (!empty($three['desc'])) {
									$return_submenu_three['desc'] = $three['desc'];
								}

								if (!empty($three['keywords'])) {
									$return_submenu_three['keywords'] = $three['keywords'];
								}

								if (!empty($three['route'])) {
									if (!empty($child['route'])) {
										$return_submenu_three['route'] = $key . '.' . $child['route'] . '.' . $three['route'];
									}
									else {
										$return_submenu_three['route'] = $key . '.' . $three['route'];
									}
								}
								else {
									$return_submenu_three['route'] = $return_submenu_default;
								}

								if (!empty($three['param'])) {
									$return_submenu_three['param'] = $three['param'];
								}

								if ($full) {
									$return_submenu_three['url'] = url($return_submenu_three['route'], !empty($return_submenu_three['param']) && is_array($return_submenu_three['param']) ? $return_submenu_three['param'] : array());
								}

								$return_submenu[] = $return_submenu_three;
							}

							unset($return_submenu_default);
							unset($return_submenu_three);
						}
					}

					unset($return_submenu_default);
					unset($return_submenu_route);
					unset($return_submenu_child);
				}
			}
		}

		return $return_submenu;
	}

}