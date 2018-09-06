<?php
/**
 * apiv1 会员中心
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
class Member extends Base
{
	/**
	 * 个人中心
	 * @param 
	 * @return  [array]    $data  [首页数据-幻灯、周边门店推荐]
	 **/
	public function index()
	{
		$mid = $this->getMemberId();
		$member = Db::name('member')->where('id',$mid)->field('createtime,isblack,salt,password,mobileverify,token,expirestime,diymaxcredit,maxcredit',true)->find();
		$level = model('member')->getLevel($mid);
		$level['memberno'] = $mid;
		$member['level'] = $level;
		$this->result(1,'success',$member);
	}

	/**
	 * 会员等级
	 * @param 
	 * @return  [array]    $data  [首页数据-幻灯、周边门店推荐]
	 **/
	public function level()
	{
		$mid = $this->getMemberId();
		$level = model('member')->getLevel($mid);
		$level['memberid'] = $mid;
		$this->result(1,'success',$level);
	}

	/**
	 * 极光推送设备ID绑定
	 * @param  【string】 $regId
	 * @return  [array]    $data  []
	 **/
	public function jpushregId()
	{
		$regId = input('regId/s','');
		$mid = $this->getMemberId();
		if(empty($regId)) {
			$this->result(0,'error');
		}
		$record = Db::name('member')->where('regId',$regId)->where('id','<>',$mid)->find();
		if($record) {
			Db::name('member')->where('id',$record['id'])->setField('regId','');
		}
		Db::name('member')->where('id',$mid)->setField('regId',$regId);
		$this->result(1,'success');
	}

	/**
	 * 退出登录
	 * @param  【int】 $mid
	 * @return  [array]    $data  []
	 **/
	public function loginout()
	{
		$mid = $this->getMemberId();
		if(empty($mid)) {
			$this->result(1,'success');
		}
		Db::name('member')->where('id',$mid)->update(array('regId'=>'','token'=>''));
		$this->result(1,'success');
	}

	/**
	 * 我的地址
	 * @param 
	 * @return  [array]    $list  [我的地址列表]
	 **/
	public function addresslist()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$condition = ' mid = ' . $mid . ' and deleted=0 ';

		$list = Db::name('shop_member_address')->where($condition)->order('id','desc')->field('mid,zipcode,deleted',true)->page($page,$pagesize)->select();
		$this->result(1,'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 编辑地址
	 * @param 
	 * @return
	 **/
	public function addresspost()
	{
		$mid = $this->getMemberId(); 
		$id = input('id/d');
		$data = array('mid' => $mid, 'realname' => input('realname/s',''), 'mobile' => input('mobile'), 'province' => input('province/s'), 'city' => input('city/s'), 'area' => input('area/s'), 'address' => input('address/s'), 'street' => input('street',''));
		$validate = validate('address');
		if(!$validate->check($data)){
		    $this->result(0,$validate->getError());
		}
		$isdefault = 0;

		if (empty($id)) {
			$addresscount = Db::name('shop_member_address')->where('mid',$mid)->where('deleted',0)->count();

			if ($addresscount <= 0) {
				$data['isdefault'] = 1;
				$isdefault = 1;
			}

			$id = Db::name('shop_member_address')->insertGetId($data);
		}
		else {
			Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->update($data);
		}

		if (!empty($isdefault)) {			
			Db::name('shop_member_address')->where('mid',$mid)->setField('isdefault',0);
			Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->setField('isdefault',1);
		}
		$this->result(1,'success',array('id'=>$id));
	}

	/**
	 * 编辑地址
	 * @param 
	 * @return
	 **/
	public function addressdel()
	{
		$mid = $this->getMemberId(); 
		$id = input('id/d');
		$data = Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->where('deleted',0)->field('id,isdefault')->find();

		if (empty($data)) {
			$this->result(0, '地址未找到');
		}

		Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->setField('deleted',1);

		if ($data['isdefault'] == 1) {
			$data = Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->setField('isdefault',0);
			$data2 = Db::name('shop_member_address')->where('deleted',0)->where('mid',$mid)->order('id','desc')->field('id')->find();

			if (!empty($data2)) {
				Db::name('shop_member_address')->where('id',$data2['id'])->where('mid',$mid)->setField('isdefault',1);
				$this->result(1, 'success',array('defaultid' => $data2['id']));
			}
		}
		$this->result(1, 'success',array('defaultid' => 0));
	}

	/**
	 * 设置默认地址
	 * @param 
	 * @return
	 **/
	public function addresssetdefault()
	{
		$mid = $this->getMemberId(); 
		$id = input('id/d');
		$data = Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->where('deleted',0)->field('id')->find();

		if (empty($data)) {
			$this->result(0, '地址未找到');
		}
		Db::name('shop_member_address')->where('mid',$mid)->setField('isdefault',0);
		Db::name('shop_member_address')->where('id',$id)->where('mid',$mid)->setField('isdefault',1);
		$this->result(1, 'success',array('defaultid' => $id));
	}

	/**
	 * 修改密码
	 * @param 
	 * @return
	 **/
	public function changepwd()
	{
		$mid = $this->getMemberId(); 
		$mobile = trim(input('mobile'));
		$smscode = trim(input('smscode'));
		$pwd = trim(input('pwd'));

		$check = model('common')->sms_captcha_verify($mobile,$smscode,'changepwd');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}
		if(!check_password($pwd))
		{
			$this->result(0,'密码由6-16位数或字母组成！');
		}
		$member = Db::name('member')->where('id',$mid)->where('mobile',$mobile)->where('mobileverify',1)->field('id,mobile,password,salt')->find();
		if(empty($member))
		{
			$this->result(0, '操作失败');
		}
		$salt = empty($member) ? '' : $member['salt'];

		if (empty($salt)) {
			$salt = model('member')->getSalt();
		}
		Db::startTrans();
		try{
		    Db::name('member')->where('id',$member['id'])->update(array('mobile' => $mobile, 'password' => md5($salt.$pwd.config('authkey')), 'salt' => $salt, 'mobileverify' => 1));
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1, 'success');
	}

	/**
	 * 修改手机号
	 * @param 
	 * @return
	 **/
	public function changemobile()
	{
		$mid = $this->getMemberId(); 
		$mobile = trim(input('mobile'));
		$smscode = trim(input('smscode'));
		$pwd = trim(input('password'));

		$check = model('common')->sms_captcha_verify($mobile,$smscode,'changemobile');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}

		$member = Db::name('member')->where('id',$mid)->where('mobileverify',1)->field('id,mobile,password,salt')->find();
		if(empty($member))
		{
			$this->result(0, '操作失败');
		}

		$password = md5($member['salt'].$pwd.config('authkey'));
        if ($password != $member['password']) {
        	$this->result(0, '密码错误');
        }

		$mobilecount = Db::name('member')->where('mobile', $mobile)->count();
		if($mobilecount > 0)
		{
			$this->result(0,'手机号已被注册！');
		}

		Db::startTrans();
		try{
		    Db::name('member')->where('id',$mid)->update(array('mobile' => $mobile, 'mobileverify' => 1));
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1, 'success');
	}

	/**
	 * 消费记录
	 * @param 
	 * @return
	 **/
	public function paylog()
	{
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
		$mid = $this->getMemberId();
		$list = Db::name('shop_core_paylog')->where('status = 1 and mid = ' . $mid)->field('plid,type,tid,fee,module,createtime')->order('createtime','desc')->page($page,$pagesize)->select();
		foreach ($list as &$val) {
			switch ($val['module']) {
				case 'shop':
					$module = '商城';
					break;

				case 'groups':
					$module = '拍卖';
					break;

				case 'auction':
					$module = '拍卖';
					break;

				case 'secKill':
					$module = '秒杀';
					break;

				case 'community':
					$module = '小区缴费';
					break;
				
				default:
					$module = '商城';
					break;
			}
			if($val['fee'] > 0) {
				$module .= '消费';
			} else {
				$module .= '退款';
			}
			$val['module'] = $module;
		}
		$this->result(1, 'success',array('list'=>$list,'page'=>$page,'pagesize'=>$pagesize));
	}

	/**
	 * 修改信息
	 * @param 
	 * @return
	 **/
	public function changeinfo()
	{
		$mid = $this->getMemberId();
		$member = model('member')->getMember($mid); 
		$realname = trim(input('realname/s',''));
		$nickname = trim(input('nickname/s',''));
		$avatar = trim(input('avatar/s',''));
		$province = trim(input('province/s',''));
		$city = trim(input('city/s',''));
		$area = trim(input('area/s',''));
		$birthyear = trim(input('birthyear/s','2018'));
		$birthmonth = trim(input('birthmonth/s','01'));
		$birthday = trim(input('birthday/s','01'));
		$gender = trim(input('gender/d',0));
		if(!empty($realname)) {
			$member['realname'] = $realname;
		}
		if(!empty($nickname)) {
			$member['nickname'] = $nickname;
		}
		if(!empty($avatar)) {
			$member['avatar'] = tomedia($avatar);
		}
		if(!empty($province)) {
			$member['province'] = $province;
		}
		if(!empty($city)) {
			$member['city'] = $city;
		}
		if(!empty($area)) {
			$member['area'] = $area;
		}
		if(!empty($birthyear)) {
			$member['birthyear'] = $birthyear;
		}
		if(!empty($birthmonth)) {
			$member['birthmonth'] = $birthmonth;
		}
		if(!empty($birthday)) {
			$member['birthday'] = $birthday;
		}
		if(!empty($gender)) {
			$member['gender'] = $gender;
		}
		Db::startTrans();
		try{
		    Db::name('member')->where('id',$mid)->update($member);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1, 'success');
	}

	/**
	 * 意见反馈
	 * @param 
	 * @return
	 **/
	public function feedback()
	{
		$mid = $this->getMemberId();
		$desc = input('desc/s','');
		$thumbs = input('thumbs');
		$thumb_url = '';
		if(!empty($thumbs))
		{
			$thumbs = json_decode($thumbs,true);
			$thumb_url = iserializer($thumbs);
		}
		if(empty($desc))
		{
			$this->result(0,'请提出您的宝贵意见');
		}
		$data = array('mid'=>$mid,'desc'=>$desc,'thumbs_url'=>$thumb_url,'createtime'=>time());
		Db::startTrans();
		try{
		    Db::name('system_feedback')->insert($data);
		    // 提交事务
		    Db::commit();    
		} catch (\Exception $e) {
		    // 回滚事务
		    Db::rollback();
		    $this->result(0, '操作失败');
		}
		
		$this->result(1, 'success');
	}

}