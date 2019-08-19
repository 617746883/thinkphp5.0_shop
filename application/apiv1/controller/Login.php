<?php
/**
 * apiv1 登陆
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Controller;
use think\Db;
class Login extends Controller
{
	/**
	 * 检测用户,注册用户。以fanwe数据库为数据原型。拉取所需字段
	 */
	public function checkMember() 
	{
		$member = array();
		$mc = array();
		$uid = $_POST['uid'];

		//如果你把下面这个if注释了,你就倒霉了
		if (empty($uid)) {
			$this->result(0, '商城正在维护, 请退出后重新登陆!');
		}
        $mc = model('member')->getLiveMember($uid);
		$uid = 0;
		if ($mc) {
			$uid = $mc['id'];
		} else {
			$this->result(0, '商城正在维护, 请退出后重新登陆!');
		}
		$member = model('member')->getMemberByUid($uid);
		$token = model('login')->buildToken($mc['mobile']);
		if (empty($member) && !empty($uid)) {
			$member = array(
				'uid' => $uid,
				'realname' => !empty($mc['realname']) ? $mc['realname'] : '',
				'mobile' => !empty($mc['mobile']) ? $mc['mobile'] : '',
				'nickname' => !empty($mc['nick_name']) ? $mc['nick_name'] : '',
				'avatar' => !empty($mc['head_image']) ? $mc['head_image'] : '',
				'gender' => !empty($mc['sex']) ? $mc['sex'] : '-1',
				'province' => !empty($mc['province']) ? $mc['province'] : '',
				'city' => !empty($mc['city']) ? $mc['city'] : '',
				'createtime' => time(),
				'expirestime' => time(),
				'token' => $token,
				'status' => 1
			);			
			$member['id'] = insert('member', $member, true);
		} else {
			if ($member['isblack'] == 1) {
				//是黑名单
				$this->result("暂时无法访问，请稍后再试!");
			}
			$upgrade = array('uid'=>$uid);
			$upgrade['expirestime'] = time();
			$upgrade['token'] = $token;
            if(isset($mc['nick_name']) && empty($member['nickname'])){
                $upgrade['nickname'] = $mc['nick_name'];
            }
            if(isset($mc['head_image']) && empty($member['avatar'])){
                $upgrade['avatar'] = $mc['head_image'];
            }
			if (isset($mc['sex']) && $member['gender'] != $mc['sex']) {
				$upgrade['gender'] = $mc['sex'];
			}
			if (!empty($upgrade)) {
				update('member', $upgrade, array('id' => $member['id']));
			}
		}
		if (empty($member)) {
			return false;
		}
		$this->result(1,'success',array(
			'id' => $member['id'],
			'uid' => $member['uid'],
			'token' => $token
		));
	}

	/**
	 * 注册
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function register()
	{
		$username = trim(input('username'));
		$pwd = trim(input('pwd'));
		$repwd = trim(input('repwd'));
		$mobile = trim(input('mobile'));
		$smscode = trim(input('smscode'));
		if(!check_username($username))
		{
			$this->result(0,'用户名由2-16位数字或字母、汉字、下划线组成！');
		}
		if(empty($pwd) || empty($repwd))
		{
			$this->result(0,'请输入密码');
		}
		if($pwd !== $repwd)
		{
			$this->result(0,'两次输入密码不一致');
		}
		if(!check_password($pwd))
		{
			$this->result(0,'密码由6-16位数或字母组成！');
		}
		if(!check_mobile($mobile))
		{
			$this->result(0,'手机号格式错误！');
		}
		$check = model('common')->sms_captcha_verify($mobile,$smscode,'register');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}
		$res = model('login')->register($username,$pwd,$mobile);
		if($res['code'] !== 1)
		{
			$this->result(0,$res['msg']);
		}
		$this->result(1,'success',$res['data']);
	}

	/**
	 * 登陆
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function login()
	{
		$username = trim(input('username'));
		$pwd = trim(input('pwd'));
		
		if(empty($username))
		{
			$this->result(0,'用户名不为空');
		}
		if(empty($pwd))
		{
			$this->result(0,'密码不为空');
		}
		$res = model('login')->login($username,$pwd);
		if($res['code'] !== 1)
		{
			$this->result(0,$res['msg']);
		}
		$this->result(1,'success',$res['data']);
	}

	/**
	 * 找回密码
	 * @param 
	 * @return  [array]    $data  []
	 **/
	public function forgetpwd()
	{
		$mobile = trim(input('mobile'));
		$username = trim(input('username'));
		$smscode = trim(input('smscode'));
		$pwd = trim(input('pwd'));

		$check = model('common')->sms_captcha_verify($mobile,$smscode,'forgetpwd');
		if($check['code'] !== 1)
		{
			$this->result(0,$check['msg']);
		}
		if(!check_password($pwd))
		{
			$this->result(0,'密码由6-16位数或字母组成！');
		}
		$member = Db::name('member')->where('username',$username)->where('mobile',$mobile)->where('mobileverify',1)->field('id,mobile,salt')->find();
		if(empty($member))
		{
			$this->result(0,'您的账号不存在或手机号有误');
		}
		$salt = empty($member) ? '' : $member['salt'];

		if (empty($salt)) {
			$salt = model('member')->getSalt();
		}

		Db::startTrans();
		try{
		    Db::name('member')->where('id',$member['id'])->update(array('password' => md5($salt.$pwd.config('authkey'))));
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