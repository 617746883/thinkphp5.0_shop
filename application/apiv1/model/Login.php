<?php
namespace app\apiv1\model;
use think\Db;
class Login extends \think\Model
{
    public function register($username,$pwd,$mobile)
    {
        $unamecount = Db::name('member')->where('username', $username)->count();
        $mobilecount = Db::name('member')->where('mobile', $mobile)->count();
        if($unamecount > 0)
        {
            return array('code'=>0,'msg'=>'用户名已被注册！');
        }
        if($mobilecount > 0)
        {
            return array('code'=>0,'msg'=>'手机号已被注册！');
        }
        $salt = model('member')->getSalt();
        $password = self::buildPwd($salt, $pwd);
        if(empty($password))
        {
            return array('code'=>0,'msg'=>'密码错误');
        }
        $member = array('mobile'=>$mobile,'createtime'=>time(),'status'=>1,'username'=>$username,'salt'=>$salt,'password'=>$password,'mobileverify'=>1,'token'=>self::buildToken($mobile),'expirestime'=>time());
        $id = Db::name('member')->insertGetId($member);
        $member = Db::name('member')->where('id',$id)->field('status,isblack,salt,password,mobileverify,expirestime',true)->find();
        $level = model('member')->getLevel($id);
        $level['memberno'] = $id;
        $member['level'] = $level;
        return array('code'=>1,'msg'=>'success','data'=>$member);
    }

    public function login($username,$pwd)
    {
        Db::name('member_failed_login')->where('lastupdate','lt',time()-300)->delete();
        $failed = Db::name('member_failed_login')->where('username',$username)->where('ip',request()->ip())->find();
        if ($failed['count'] >= 5) {
            return array('code'=>-403,'msg'=>'输入密码错误次数超过5次，请在5分钟后再登录');
        }
        $record = Db::name('member')->where('username', $username)->where('status', 1)->where('isblack', 0)->find();
        if(empty($record))
        {
            return array('code'=>-1,'msg'=>'您的账号不存在或是已经被系统禁止，请联系管理员解决?');
        }
        $password = md5($record['salt'].$pwd.config('authkey'));
        if ($password != $record['password']) {
            self::plog_failed_login($failed, $username);
            return array('code'=>-404,'msg'=>'密码错误');
        }
        $token = self::buildToken($record['mobile']);
        $upgrade['token'] = $token;
        $upgrade['expirestime'] = time();
        Db::name('member')->where('id', $record['id'])->update($upgrade);
        Db::name('member_failed_login')->where('id', $failed['id'])->delete();
        $record['token'] = $token;
        unset($record['status'],$record['isblack'],$record['salt'],$record['password'],$record['mobileverify'],$record['expirestime']);
        $level = model('member')->getLevel($record['id']);
        $level['memberno'] = $record['id'];
        $record['level'] = $level;
        return array('code'=>1,'msg'=>'success','data'=>$record);
    }

    protected static function buildPwd($salt, $password)
    {
        $pwd = md5($salt.$password.config('authkey'));
        return $pwd;
    }

    /**
     * 记录登录失败
     * 写入记录或者增加记录条数
     */
    public static function plog_failed_login($failed, $username)
    {
        if (empty($failed)) {
            Db::name('member_failed_login')->insert(array('ip'=>request()->ip(),'username'=>$username,'count' => '1', 'lastupdate' => time()));
        } else {
            Db::name('member_failed_login')->where('id', $failed['id'])->update(array('count' => $failed['count'] + 1,'lastupdate' => time()));
        }
    }

	/**
     * 验证token
     * 检测token有效性，请求之后设置token过期时间
     */
    public static function checktoken($token)
    {
    	$res = Db::name('member')->where('token', $token)->field('id,expirestime,token,status,isblack')->find();       
        if (!empty($res))
        {
            if($res['status'] != 1 || $res['isblack'] == 1)
            {
                return array('code'=>-90004,'msg'=>'您的账号不存在或是已经被系统禁止，请联系管理员解决?');
            }
            if ((time() - $res['expirestime']) > 604800) 
            {
                return array('code'=>90003,'msg'=>'身份信息已过期请重新登录!');  //token长时间未使用而过期，需重新登陆
            }
            $new_time_out = time() + 604800;//604800是七天
            if (Db::name('member')->where('id', $res['id'])->update(['expirestime'=>$new_time_out]));
            {
                return array('code'=>90001,'msg'=>'success','data'=>$res['id']);  //token验证成功，time_out刷新成功，可以获取接口信息
            }
        }
        return array('code'=>90002,'msg'=>'token error!');  //token错误验证失败
    }

    /**
     * 生成AccessToken
     * @param string
     * @return string
     */
    public static function buildToken($client = '')
    {
    	if(empty($client)) {
    		return '';
    	}
        $str_pol = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
        $str = substr(str_shuffle($str_pol), 0, 8);
        $token = md5($client.$str.time());
        while (1) {
            $count = Db::name('member')->where('token',$token)->count();
            if ($count <= 0) {
                break;
            }
            $token = md5($client.$str.time());
        }
        return $token;
    }

    /**
     * 设置Token
     * @param $clientInfo
     * @return int
     */
    protected function setToken($clientInfo)
    {
        //生成令牌
        $accessToken = self::buildToken($clientInfo['mobile']);
        if(empty($accessToken)) {
        	return false;
        }
        $accessTokenInfo = [
            'token' => $accessToken,//访问令牌
            'expirestime' => time() + 604800,      //过期时间时间戳
        ];
        $res = Db::name('member')->where('id', $clientInfo['id'])->update($accessTokenInfo);
        if(!$res) {
        	return false;
        }
        return $accessTokenInfo;
    }

}