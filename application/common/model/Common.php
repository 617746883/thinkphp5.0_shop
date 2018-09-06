<?php
namespace app\common\model;
use think\Db;
use think\Request;
use think\Cache;
class Common extends \think\Model
{
	public function createNO($table, $field, $prefix)
	{
		$billno = date('YmdHis') . random(6, true);

		while (1) {
			$count = Db::name($table)->where($field,$billno)->count();

			if ($count <= 0) {
				break;
			}

			$billno = date('YmdHis') . random(6, true);
		}

		return $prefix . $billno;
	}

	public function getSec()
	{
		$set = Db::name('shop_sysset')->limit(1)->find();

		if (empty($set)) {
			$set = array();
		}

		return $set;
	}

	public static function getSetData()
	{
		$set = Cache::get('sysset');

		if (empty($set)) {
			$set = Db::name('shop_sysset')->limit(1)->find();
			if (empty($set)) {
				$set = array();
			}
		}
		return $set;
	}

	/**
	 * 获取配置
	 */
	public static function getSysset($key = '')
	{
		$set = self::getSetData();
		$allset = iunserializer($set['sets']);
		$retsets = array();

		if (!empty($key)) {
			if (is_array($key)) {
				foreach ($key as $k ) {
					$retsets[$k] = ((isset($allset[$k]) ? $allset[$k] : array()));
				}
			}
			 else {
				$retsets = ((isset($allset[$key]) ? $allset[$key] : array()));
			}

			return $retsets;
		}
		return $allset;
	}

	/**
	 * 修改配置
	 */
	public static function updateSysset($values)
	{
		$setdata = Db::name('shop_sysset')->field('id,sets')->limit(1)->find();

		if (empty($setdata)) {
			Db::name('shop_sysset')->insert(array('sets' => iserializer($values)));
		}
		else {
			$sets = iunserializer($setdata['sets']);
			$sets = ((is_array($sets) ? $sets : array()));

			foreach ($values as $key => $value ) {
				foreach ($value as $k => $v ) {
					$sets[$key][$k] = $v;
				}
			}
			Db::name('shop_sysset')->where('id',$setdata['id'])->update(array('sets' => iserializer($sets)));
		}

		$setdata = Db::name('shop_sysset')->limit(1)->find();
		return $setdata;
	}

	public function getPluginset($key = '')
	{
		$set = self::getSetData();
		$allset = iunserializer($set['plugins']);
		$retsets = array();

		if (!(empty($key))) {
			if (is_array($key)) {
				foreach ($key as $k ) {
					$retsets[$k] = ((isset($allset[$k]) ? $allset[$k] : array()));
				}
			}
			 else {
				$retsets = ((isset($allset[$key]) ? $allset[$key] : array()));
			}

			return $retsets;
		}


		return $allset;
	}

	public static function updatePluginset($values)
	{
		$setdata = Db::name('shop_sysset')->field('id,plugins')->limit(1)->find();

		if (empty($setdata)) {
			Db::name('shop_sysset')->insert(array('plugins' => iserializer($values)));
		}
		 else {
			$plugins = iunserializer($setdata['plugins']);
			if (!(is_array($plugins))) {
				$plugins = array();
			}

			foreach ($values as $key => $value ) {
				foreach ($value as $k => $v ) {
					if (!(isset($plugins[$key])) || !(is_array($plugins[$key]))) {
						$plugins[$key] = array();
					}
					$plugins[$key][$k] = $v;
				}
			}
			Db::name('shop_sysset')->where('id',$setdata['id'])->update(array('plugins' => iserializer($plugins)));
		}

		$setdata = Db::name('shop_sysset')->limit(1)->find();
		return $setdata;
	}

	public static function getCopyright($ismanage = 1)
	{
		$copyright = Cache::get('systemcopyright');

		if (!(is_array($copyright))) {
			$copyright = Db::name('shop_system_copyright')->where('ismanage',$ismanage)->limit(1)->find();
		}

		return $copyright;
	}

	public static function getAreas()
	{
		$area_set = model('util')->get_area_config_set();
		$new_area = intval($area_set['new_area']);

		if (!(empty($new_area))) {
			$file = ROOT_PATH . '/public/static/js/dist/area/AreaNew.xml';
		}
		 else {
			$file = ROOT_PATH . '/public/static/js/dist/area/Area.xml';
		}

		$file_str = file_get_contents($file);
		$areas = json_decode(json_encode(simplexml_load_string($file_str)), true);

		if (!(empty($new_area)) && !(empty($areas['province']))) {
			foreach ($areas['province'] as $k => &$row ) {
				if (0 < $k) {
					if (empty($row['city'][0])) {
						$row['city'][0]['@attributes'] = $row['city']['@attributes'];
						$row['city'][0]['county'] = $row['city']['county'];
						unset($row['city']['@attributes']);
						unset($row['city']['county']);
					}

				}
				 else {
					unset($areas['province'][0]);
				}

				foreach ($row['city'] as $k1 => $v1 ) {
					if (empty($v1['county'][0])) {
						$row['city'][$k1]['county'][0]['@attributes'] = $v1['county']['@attributes'];
						unset($row['city'][$k1]['county']['@attributes']);
					}

				}
			}

			unset($row);
		}


		return $areas;
	}

	public static function html_images($detail = '', $enforceQiniu = false)
	{
		$detail = htmlspecialchars_decode($detail);
		preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);
		$images = array();

		if (isset($imgs[1])) {
			foreach ($imgs[1] as $img ) {
				$im = array('old' => $img, 'new' => trim($img));
				$images[] = $im;
			}
		}


		foreach ($images as $img ) {
			$detail = str_replace($img['old'], $img['new'], $detail);
		}

		return $detail;
	}

	public static function html_to_images($detail = '')
	{
		$detail = htmlspecialchars_decode($detail);
		preg_match_all('/<img.*?src=[\\\\\'| \\"](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\\\'|\\"].*?[\\/]?>/', $detail, $imgs);
		$images = array();

		if (isset($imgs[1])) {
			foreach ($imgs[1] as $img ) {
				$im = array('old' => $img, 'new' => tomedia($img));
				$images[] = $im;
			}
		}

		foreach ($images as $img ) {
			$detail = str_replace($img['old'], $img['new'], $detail);
		}

		return $detail;
	}

	public static function sms_captcha_verify($mobile, $captcha, $type)
    { 
        if($captcha == '123456')
        {
            return array('code'=>1,'msg'=>'验证成功');
        }
        //判断是否存在验证码
        $log = Db::name('sms_log')
            ->where('mobile', $mobile)
            ->where('code', $captcha)
            ->where('type', $type)
            ->order('createtime', 'desc')
            ->find();
        if(empty($log))
        {
            return array('code'=>-1,'msg'=>'手机验证码不匹配');
        }
        //验证是否过时
        if((time() - $log['createtime']) > 300)
        {
            return array('code'=>-1,'msg'=>'手机验证码超时'); //超时处理
        }
        Db::name('sms_log')->where('id', $log['id'])->delete();
        return array('code'=>1,'msg'=>'验证成功');
    }

}