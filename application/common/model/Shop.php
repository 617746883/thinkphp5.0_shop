<?php
namespace app\common\model;
use think\Db;
use think\Request;
class Shop extends \think\Model
{
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

}