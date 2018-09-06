<?php
namespace app\merch\model;
use think\Db;
use think\Request;
class Shop extends \think\Model
{
	public static function plog($type = '', $op = '')
    {
    	
    }

    /**
	 * 获取商品分类
	 * @global type $_W
	 * @return type
	 */
	public static function getCategory()
	{
		$parents = array();
		$children = array();
		$category = Db::name('shop_goods_category')->where('enabled',1)->order('parentid','asc')->order('displayorder','desc')->select();

		foreach ($category as $index => $row) {
			if (!empty($row['parentid'])) {
				if ($row[$row['parentid']]['parentid'] == 0) {
					$row[$row['parentid']]['level'] = 2;
				}
				else {
					$row[$row['parentid']]['level'] = 3;
				}

				$children[$row['parentid']][] = $row;
				unset($category[$index]);
			}
			else {
				$row['level'] = 1;
				$parents[] = $row;
			}
		}

		$allcategory = array('parent' => $parents, 'children' => $children);
		return $allcategory;
	}

	public static function getAllCategory()
	{
		$allcategory = Db::name('shop_goods_category')->field('id,parentid,name,thumb')->select();
		return $allcategory;
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
	public static function getFullCategory2($fullname = false, $enabled = false)
	{
		$allcategory = array();
		$sql = ' 1 ';

		if ($enabled) {
			$sql .= ' AND enabled=1';
		}
		$category = Db::name('shop_store_goods_category')->where($sql)->order('parentid','asc')->order('displayorder','desc')->select();

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

	/**
     * 获取快递列表
     */
	public function getExpressList()
	{
		$data = Db::name('shop_express')->where('status=1')->order('displayorder','desc')->select();
		return $data;
	}

}