<?php
/**
 * 商品助手
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Taobao extends Base
{
	public function index()
	{
		$category = model("shop")->getFullCategory(true, true);
		$shopset = model('common')->getSysset();
		$this->assign(['category'=>$category,'shopset'=>$shopset]);
		return $this->fetch('');
	}

	public function jingdong()
	{
		$category = model("shop")->getFullCategory(true, true);
		$shopset = model('common')->getSysset();
		$this->assign(['category'=>$category,'shopset'=>$shopset]);
		return $this->fetch('');
	}

	public function one688()
	{
		$category = model("shop")->getFullCategory(true, true);
		$shopset = model('common')->getSysset();
		$this->assign(['category'=>$category,'shopset'=>$shopset]);
		return $this->fetch('');
	}

	public function taobaocsv()
	{
		$uploadStart = '0';
		$uploadnum = '0';
		$excelurl = ROOT_PATH . 'static/plugin/taobao/data/test.xlsx';
		$zipurl = ROOT_PATH . 'static/plugin/taobao/data/test.zip';

		if (Request::instance()->isPost()) {
			$rows = model('excel')->import('excelfile');
			$num = count($rows);
			$i = 0;
			$colsIndex = array();

			foreach ($rows[1] as $cols => $col) {
				if ($col == 'title') {
					$colsIndex['title'] = $i;
				}

				if ($col == 'price') {
					$colsIndex['price'] = $i;
				}

				if ($col == 'num') {
					$colsIndex['num'] = $i;
				}

				if ($col == 'description') {
					$colsIndex['description'] = $i;
				}

				if ($col == 'skuProps') {
					$colsIndex['skuProps'] = $i;
				}

				if ($col == 'picture') {
					$colsIndex['picture'] = $i;
				}

				if ($col == 'propAlias') {
					$colsIndex['propAlias'] = $i;
				}

				++$i;
			}

			$filename = $_FILES['excelfile']['name'];
			$filename = substr($filename, 0, strpos($filename, '.'));
			$rows = array_slice($rows, 3, count($rows) - 3);
			$items = array();
			$this->get_zip_originalsize($_FILES['zipfile']['tmp_name'], '../attachment/images/' . $_W['uniacid'] . '/' . date('Y') . '/' . date('m') . '/');
			$num = 0;

			foreach ($rows as $rownu => $col) {
				$item = array();
				$item['title'] = $col[$colsIndex[title]];
				$item['marketprice'] = $col[$colsIndex[price]];
				$item['total'] = $col[$colsIndex[num]];
				$item['content'] = $col[$colsIndex[description]];
				$picContents = $col[$colsIndex[picture]];
				$allpics = explode(';', $picContents);
				$pics = array();
				$optionpics = array();

				foreach ($allpics as $imgurl) {
					if (empty($imgurl)) {
						continue;
					}

					$picDetail = explode('|', $imgurl);
					$picDetail = explode(':', $picDetail[0]);
					$imgurl = 'attachment/images/' . $_W['uniacid'] . '/' . date('Y') . '/' . date('m') . '/' . $picDetail[0] . '.png';

					if (@fopen($imgurl, 'r')) {
						if ($picDetail[1] == 1) {
							$pics[] = $imgurl;
						}

						if ($picDetail[1] == 2) {
							$optionpics[$picDetail[0]] = $imgurl;
						}
					}
				}

				$item['pics'] = $pics;
				$items[] = $item;
				++$num;
			}

			session_start();
			$_SESSION['taobaoCSV'] = $items;
			m('cache')->set('taobaoCSV', $items, $_W['uniacid']);
			$uploadStart = '1';
			$uploadnum = $num;
		}

		return $this->fetch('');
	}

	public function get_zip_originalsize($filename, $path)
	{
		if (!file_exists($filename)) {
			exit('文件 ' . $filename . ' 不存在！');
		}

		$filename = iconv('utf-8', 'gb2312', $filename);
		$path = iconv('utf-8', 'gb2312', $path);
		$resource = zip_open($filename);
		$i = 1;

		while ($dir_resource = zip_read($resource)) {
			if (zip_entry_open($resource, $dir_resource)) {
				$file_name = $path . zip_entry_name($dir_resource);
				$file_path = substr($file_name, 0, strrpos($file_name, '/'));

				if (!is_dir($file_path)) {
					mkdir($file_path, 511, true);
				}

				if (!is_dir($file_name)) {
					$file_size = zip_entry_filesize($dir_resource);

					if ($file_size < 1024 * 1024 * 10) {
						$file_content = zip_entry_read($dir_resource, $file_size);
						$ext = strrchr($file_name, '.');

						if ($ext == '.png') {
							file_put_contents($file_name, $file_content);
						}
						else {
							if ($ext == '.tbi') {
								$file_name = substr($file_name, 0, strlen($file_name) - 4);
								file_put_contents($file_name . '.png', $file_content);
							}
						}
					}
				}

				zip_entry_close($dir_resource);
			}
		}

		zip_close($resource);
	}

	public function taobaofetch() 
	{
		set_time_limit(0);
		$ret = array( );
		$url = $_POST["url"];
		$cates = $_POST["cate"];
		$from = $_POST["from"];
		if( is_numeric($url) ) {
			$itemid = $url;
		} else {
			preg_match("/id\\=(\\d+)/i", $url, $matches);
			if( isset($matches[1]) ) {
				$itemid = $matches[1];
			}
		}
		if( empty($itemid) ) {
			exit( json_encode(array( "result" => 0, "error" => "未获取到 itemid!" )) );
		}
		if( $from == "all" ) {
			$ret = model('taobao')->get_item_taobao($itemid, $_POST["url"], $cates);
		} else {
			if( $from == "tmall" ) {
				$ret = model('taobao')->get_item_tmall_bypage($itemid, $_POST["url"], $cates);
			} else {
				if( $from == "taobao" ) {
					$ret = model('taobao')->get_item_taobao($itemid, $_POST["url"], $cates);
				}
			}
		}
		model('shop')->plog("taobao.main", "淘宝抓取宝贝 淘宝id:" . $itemid);
		exit( json_encode($ret) );
	}

	public function jingdongfetch()
	{
		set_time_limit(0);
		$ret = array();
		$url = $_POST['url'];
		$cates = $_POST['cate'];

		if (is_numeric($url)) {
			$itemid = $url;
		}
		else {
			preg_match('/(\\d+).html/i', $url, $matches);

			if (isset($matches[1])) {
				$itemid = $matches[1];
			}
		}

		if (empty($itemid)) {
			exit(json_encode(array('result' => 0, 'error' => '未获取到 itemid!')));
		}

		$ret = model('taobao')->get_item_jingdong($itemid, $_POST['url'], $cates);
		model('shop')->plog('jingdong.main', '京东抓取宝贝 京东id:' . $itemid);
		exit(json_encode($ret));
	}

	public function one688fetch()
	{
		set_time_limit(0);
		$ret = array();
		$url = $_POST['url'];
		$cates = $_POST['cate'];

		if (is_numeric($url)) {
			$itemid = $url;
		}
		else {
			preg_match('/(\\d+).html/i', $url, $matches);

			if (isset($matches[1])) {
				$itemid = $matches[1];
			}
		}

		if (empty($itemid)) {
			exit(json_encode(array('result' => 0, 'error' => '未获取到 itemid!')));
		}

		$ret = model('taobao')->get_item_one688($itemid, $_POST['url'], $cates);
		model('shop')->plog('1688.main', '1688抓取宝贝 1688id:' . $itemid);
		exit(json_encode($ret));
	}

}