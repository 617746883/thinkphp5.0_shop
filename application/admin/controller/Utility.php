<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\File;
use think\Image;
use think\Request;
class Utility extends Controller
{
    public function file()
    {
        $do = input('do/s','');
        if (!in_array($do, array('upload', 'fetch', 'browser', 'delete', 'image' ,'module' ,'video', 'voice', 'news', 'keyword',
			'networktowechat', 'networktolocal', 'tolocal', 'group_list', 'add_group', 'change_group', 'del_group', 'move_to_group'))) {
			exit('Access Denied');
		}
		$result = array(
			'error' => 1,
			'message' => '',
			'data' => ''
		);
		$type  = input('upload_type/s',''); 
		$type = in_array($type, array('image','audio','video')) ? $type : 'image';
		$islocal = input('local') == 'local';
		$option = array();
		$option = array_elements(array('uploadtype', 'global', 'dest_dir'), $_POST);
		$option['width'] = intval($option['width']);
		$option['global'] = input('global');
		$dest_dir = input('dest_dir');
		if (preg_match('/^[a-zA-Z0-9_\/]{0,50}$/', $dest_dir, $out)) {
			$dest_dir = trim($dest_dir, '/');
			$pieces = explode('/', $dest_dir);
			if(count($pieces) > 3){
				$dest_dir = '';
			}
		} else {
			$dest_dir = '';
		}
		$module_upload_dir = '';
		if($dest_dir != '') {
			$module_upload_dir = sha1($dest_dir);
		}
		if (!empty($option['global'])) {
			$setting['folder'] = "{$type}s/global/";
			if (! empty($dest_dir)) {
				$setting['folder'] .= '' . $dest_dir . '/';
			}
		} else {
			$setting['folder'] = "{$type}s/";
			if (empty($dest_dir)) {
				$setting['folder'] .= '/' . date('Y/m/');
			} else {
				$setting['folder'] .= '/' . $dest_dir . '/';
			}
		}
		if($do == 'image') {
			$year = input('year');
			$month = input('month');
			$page = intval(input('page/d',1));
			$groupid = intval(input('groupid'));
			$page_size = 25;
			$is_local_image = $islocal == 'local' ? true : false;
			$attachment_table = Db::name('core_attachment');
			if ($groupid >=0) {
				$attachment_table = $attachment_table->where('group_id',$groupid);
			}

			if ($year || $month) {
				$start_time = strtotime("{$year}-{$month}-01");
				$end_time = strtotime('+1 month', $start_time);
				$attachment_table = $attachment_table->whereTime('createtime', 'between', [$start_time, $end_time]);
			}
			if ($islocal) {
				$attachment_table = $attachment_table->where('type',1);
			} else {
				$attachment_table = $attachment_table->where('type',0);
			}
			$attachment_table = $attachment_table->page($page, $page_size);
			$list = $attachment_table->order('createtime','desc')->select();
			$total = $attachment_table->count();
			if (!empty($list)) {
				foreach ($list as &$meterial) {
					if ($islocal) {
						$meterial['url'] = tomedia($meterial['attachment']);
					} else {
						$meterial['attach'] = tomedia($meterial['attachment'], true);
						$meterial['url'] = $meterial['attach'];
					}
				}
			}

			$pager = pagination($total, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => 1));
			$result = array('items' => $list, 'pager' => $pager);
			return json(array('message'=>array('errno'=>0,'message'=>$result)));
		}
		if ($do == 'video' || $do == 'voice') {
			$server = $islocal;
			$page = intval(input('page/d',1));
			$page_size = 10;
			$material_news_list = $this->material_list($do, $server, array('page_index' => $page_index, 'page_size' => $page_size));
			$material_list = $material_news_list['material_list'];
			$pager = $material_news_list['page'];
			foreach ($material_list as &$item) {
				$item['url'] = tomedia($item['attachment']);
				unset($item['uid']);
			}
			$result = array('items' => $material_list, 'pager' => $pager);
			return json(array('message'=>array('errno'=>0,'message'=>$result)));
		}
		$is_local_image = $islocal == 'local' ? true : false;
		if($do == 'group_list') {
			$list = Db::name('attachment_group')->where('type',$is_local_image ? 0 : 1)->select();
			return json(array('message'=>array('errno'=>0,'message'=>$list)));
		}
		if($do == 'upload') {
			$file = request()->file('file');                    
	        if(empty($file)) {
	            $result['message'] = '请选择要上传的文件！';
				die(json_encode($result));
	        }
			// 获取配置文件信息
	        $config = config('UploadFile');
	        switch ($type) {
	            case 'image':
	                $path = $config['imageSavePath'];
	                $size = $config['imageFileSize'];
	                $ext = $config['imageExts'];
	                break;
	            case 'audio':
	                $path = $config['audioSavePath'];
	                $size = $config['audioFileSize'];
	                $ext = $config['audioExts'];
	                break;
	            case 'video':
	                $path = $config['vedioSavePath'];
	                $size = $config['videoFileSize'];
	                $ext = $config['vedioExts'];
	                break;            
	            default:
	                $path = $config['imageSavePath'];
	                $size = $config['imageFileSize'];
	                $ext = $config['imageExts'];
	                break;
	        }

	        // 移动到框架根目录/public/attachment/ 目录下
	        $uploadpath = ROOT_PATH . '/public/attachment' . DS . $path;
	        $info = $file->validate(['size'=>$size,'ext'=>$ext])->move($uploadpath, true, false);
	        if(!$info)
	        {
	        	$result['message'] = '上传失败.'.$file->getError();
				die(json_encode($result));
	        }
	        $originname = $info->getSaveName();
	        $ext = $info->getExtension();
	        $pathname = "/public/attachment/" . $path . $originname;
	        $fullname = tomedia($pathname);
	        $info = array(
				'name' => $originname,
				'ext' => $ext,
				'filename' => $pathname,
				'attachment' => $pathname,
				'url' => tomedia($pathname),
				'is_image' => $type == 'image' ? 1 : 0,
				'filesize' => filesize($fullname),
				'group_id' => intval(input('group_id'))
			);
			if ($type == 'image') {
				$shop_data = model('common')->getSysset('shop');
				if(!empty($shop_data['iswater']) && !empty($shop_data['imgwater']))
				{
					$imgwater = $shop_data['imgwater'];
				}
            	$image = \think\Image::open(ROOT_PATH . DS . $pathname);  
	            if(!empty($imgwater))
	            {
	            	$image->water($imgwater,\think\Image::WATER_SOUTHEAST,50)->save(ROOT_PATH . DS . $pathname);  // 给原图左上角添加透明度为50的水印并保存
	            }
	            $info['width'] = $image->width();
				$info['height'] = $image->height();      
			} else {
				$size = filesize($fullname);
				$info['size'] = sizecount($size);
			}
			Db::name('core_attachment')->insert(
				array(
				'uid' => session('admin') ? session('admin')['id'] : 0,
				'filename' => $originname,
				'attachment' => $pathname,
				'type' => $type == 'image' ? 1 : ($type == 'video'||$type == 'voice' ? 2 : 3),
				'createtime' => time(),
				'module_upload_dir' => $module_upload_dir,
				'group_id' => intval(input('group_id'))
			));
			$info['state'] = 'SUCCESS';	die(json_encode($info));
		}
		if($do == 'delete') {
			$material_id = intval(input('material_id'));
			$id = input('id/a');
			$server = input('server') == 'local' ? 'local' : 'wechat';
			$type = trim(input('type'));
			$attachments = Db::name('core_attachment')->where('id','in',$id)->select();
			if (empty($attachments)){
				return json(array('message'=>array('errno'=>-1,'message'=>'文件不存在或已删除')));
			}
			$delete_ids = array();
			foreach ($attachments as $media) {
				$file = ROOT_PATH.$media['attachment'];
				if(!is_file($file))
				{
					return json(array('message'=>array('errno'=>-1,'message'=>'文件不存在或已删除')));
				}
				if(!unlink($file))
	            {                
	                return json(array('message'=>array('errno'=>-1,'message'=>'删除文件操作发生错误')));
	            }
				$delete_ids[] = $media['id'];
			}
			
            Db::name('core_attachment')->where('id','in',$delete_ids)->delete();
            return json(array('message'=>array('errno'=>0,'message'=>'删除素材成功')));
		}
		if($do == 'add_group') {
			$result = Db::name('attachment_group')->insertGetId(array('uid'=>session('?uid') ? session('uid') : 0,'name'=>input('name/s','未命名'),'type'=>$is_local_image ? 0 : 1));
			if (!$result) {
				return json(array('message'=>array('errno'=>-1,'message'=>'添加分组失败')));
			}
			return json(array('message'=>array('errno'=>0,'message'=>$result)));
		}
		if($do == 'del_group') {
			$type = $is_local_image ? 0 : 1;
			$id = intval(input('id'));
			$deleted = Db::name('attachment_group')->where('type', $type)->where('id',$id)->delete();
			if(!$deleted)
			{
				return json(array('message'=>array('errno'=>1,'message'=>'删除失败')));
			}
			return json(array('message'=>array('errno'=>0,'message'=>'删除成功')));
		}
		if($do == 'change_group') {
			$type = $is_local_image ? 0 : 1;
			$id = intval(input('id'));
			$name = trim(input('name/s','未命名'));
			$updated = Db::name('attachment_group')->where('type', $type)->where('id', $id)->setField('name',$name);
			if(!$updated)
			{
				return json(array('message'=>array('errno'=>1,'message'=>'更新失败')));
			}
			return json(array('message'=>array('errno'=>0,'message'=>'更新成功')));
		}
		if($do == 'move_to_group') {
			$group_id = intval(input('id'));
			$ids = input('keys/a');
			$updated = Db::name('core_attachment')->where('id','in', $ids)->setField('group_id',$group_id);
			if(!$updated)
			{
				return json(array('message'=>array('errno'=>1,'message'=>'更新失败')));
			}
			return json(array('message'=>array('errno'=>0,'message'=>'更新成功')));
		}
		if($do == 'networktolocal') {
			$type = input('type');
			if (!in_array($type,array('image','video'))) {
				$type = 'image';
			}
			$url = input('url');
			$material = get_headers($url,true);
			if(!$material){
				return json(array('message'=>array('errno'=>1,'message'=>'获取失败:未找到这个资源!')));
	        }
			$data = array('uid' => session('?uid') ? session('uid') : 0,
				'filename' => $url,
				'attachment' => $url,
				'type' => $type,
				'url' => tomedia($url),
				'createtime'=>time
			);
			return json(array('message'=>array('errno'=>0,'message'=>$data)));
		}
    }

    protected function material_list($type = '', $server = '', $page = array('page_index' => 1, 'page_size' => 24)) {
		switch ($type) {
			case 'voice' :
				$conditions['type'] = 3;
				break;
			case 'video' :
				$conditions['type'] = 2;
				break;
			default :
				$conditions['type'] = 3;
				break;
		}
		if ($server == 'local') {
			$material_list = Db::name('core_attachment')->where($conditions)->order('createtime','desc')->page($page['page_index'], $page['page_size'])->select();
			$total = Db::name('core_attachment')->where($conditions)->count();
		} else {
			$conditions['model'] = MATERIAL_WEXIN;
			$material_list = pdo_getslice($table, $conditions, array($page['page_index'], $page['page_size']), $total, array(), '', 'createtime DESC');
			if ($type == 'video'){
				foreach ($material_list as &$row) {
					$row['tag'] = $row['tag'] == '' ? array() : iunserializer($row['tag']);
				}
				unset($row);
			}
		}
		$pager = pagination($total, $page['page_index'], $page['page_size'],'',$context = array('before' => 5, 'after' => 4, 'isajax' => $_W['isajax']));
		$material_news = array('material_list' => $material_list, 'page' => $pager);
		return $material_news;
	}

    public function map()
    {
    	return $this->fetch('util/area/map');
    }

    public function express()
    {
    	$express = trim(input('express'));
		$expresssn = trim(input('expresssn'));
		$result = model('util')->getExpressList($express, $expresssn);
		$this->assign(['list'=>$result['list']]);
		echo $this->fetch('util/express');
    }

    public function days()
	{
		$year = input('year/d');
		$month = input('month/d');
		exit(get_last_day($year, $month));
	}

    public function selecturl()
    {
    	$allUrls = array(
			array(
				'name' => '商城页面',
				'list' => array(
					array('name' => '商城首页', 'url' => 'shop'),
					array('name' => '全部商品', 'url' => 'goodslist'),
					array('name' => '公告页面', 'url' => 'shopnotice'),
					array('name' => '购物车', 'url' => 'shopcart')
					)
				),
			array(
				'name' => '商品属性',
				'list' => array(
					array('name' => '推荐商品', 'url' => 'goodslist?isrecommand=1'),
					array('name' => '新品上市', 'url' => 'goodslist?isnew=1'),
					array('name' => '热卖商品', 'url' => 'goodslist?ishot=1'),
					array('name' => '促销商品', 'url' => 'goodslist?isdiscount=1'),
					array('name' => '卖家包邮', 'url' => 'goodslist?issendfree=1'),
					array('name' => '限时抢购', 'url' => 'goodslist?istime=1')
					)
				),
			array(
				'name' => '会员中心',
				'list' => array(
					0  => array('name' => '会员中心', 'url' => 'member'),
					1  => array('name' => '我的订单(全部)', 'url' => 'order'),
					2  => array('name' => '待付款订单', 'url' => 'order?status=0'),
					3  => array('name' => '待发货订单', 'url' => 'order?status=0'),
					4  => array('name' => '待收货订单', 'url' => 'order?status=0'),
					5  => array('name' => '退换货订单', 'url' => 'order?status=0'),
					6  => array('name' => '已完成订单', 'url' => 'order?status=0'),
					7  => array('name' => '我的收藏', 'url' => 'goodsfavorite'),
					8  => array('name' => '我的足迹', 'url' => 'memberhistory')
					)
				)
			);

    	$set = Db::name('shop_groups_set')->limit(1)->find();    		
        if($set['opengroups'] != 1) {
        	$allUrls[] = array(
				'name' => '团购',
				'list' => array(
					array('name' => '拼团首页', 'url' => 'groups'),
					array('name' => '活动列表', 'url' => 'groupslist'),
					array('name' => '我的订单', 'url' => 'groupsorders'),
					array('name' => '我的团', 'url' => 'groupsteam')
					)
				);
        }   	

    	$syscate = model('common')->getSysset('category');
		if (0 < $syscate['level']) {
			$categorys = Db::name('shop_goods_category')->where('enabled',1)->field('id,name,parentid')->select();
		}
		$controller = input('controller');
    	$this->assign(['allUrls'=>$allUrls,'categorys'=>$categorys,'controller'=>$controller]);
    	echo $this->fetch('util/selecturl');
    }

    public function selecturlquery()
    {
    	$type = trim(input('type'));
		$kw = trim(input('kw'));
		$full = intval(input('full'));
		$platform = trim(input('platform'));
		$list = array();
		if (!(empty($kw)) && !(empty($type))) {
			if ($type == 'good') {
				$list = Db::name('shop_goods')->where('status',1)->where('isgroups',0)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id,title,productprice,marketprice,thumb,sales,unit,minprice')->select();
				$list = set_medias($list, 'thumb');
			} else if ($type == 'groups') {
				$list = Db::name('shop_goods')->where('status',1)->where('isgroups',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id,title,productprice,marketprice,thumb,sales,unit,minprice')->select();
			} else if ($type == 'creditshop') {
				$list = Db::name('shop_creditshop_goods')->where('status',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id, thumb, title, price, credit, money')->select();
			} else if ($type == 'article') {
				$list = Db::name('shop_article')->where('article_state',1)->where('article_title','like','%' . $kw . '%')->field('id, resp_img, article_title')->select();
			} else if ($type == 'housing') {
				$list = Db::name('community_housing')->where('status',1)->where('deleted',0)->where('title','like','%' . $kw . '%')->field('id, thumb, title')->select();
			} else if ($type == 'lifestore') {
				$list = Db::name('citywide_life_store')->where('status',1)->where('deleted',0)->where('storename','like','%' . $kw . '%')->field('id, storename, logo')->select();
			}
		}
		$this->assign(['type'=>$type,'kw'=>$kw,'full'=>$full,'platform'=>$platform,'list'=>$list]);
		echo $this->fetch('util/selecturl_tpl');
    }


}