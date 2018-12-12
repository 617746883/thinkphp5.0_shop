<?php
/**
 * apiv1 商户
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Merch extends Base
{
	protected static $token;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        self::$token = $this->request->header('token','');

        if(!empty(self::$token))
        {
            $this->mid = Db::name('member')->where('token', self::$token)->value('id');
        }
    }

    /**
	 * 商户店铺信息
	 * @param 
	 * @return  [array]    $list  []
	 **/
	public function merch()
	{
		$id = input('id/d');
		if (!($id)) {
            $shopset = $this->shopset;
            $merch = array("id"=>0,"merchname"=>$shopset['shop']['name'],"mobile"=>$shopset['contact']['phone'],"logo"=>$shopset['shop']['logo'],"banner"=>$shopset['shop']['img'],"tel"=>$shopset['contact']['phone'],"score"=>"5.0","iscollect"=>0);
		} else {
            $merch = Db::name('shop_merch')->where('id',$id)->field('id,merchname,mobile,logo,banner,tel,score')->find();
        }
		$mid = 0;
        if(!empty($this->mid))
        {           
            $mid = $this->mid;
        }        
        if(empty($merch))
        {
        	$this->result(0,'没有找到此商户');
        }
        $merch['logo'] = tomedia($merch['logo']);
        $merch['banner'] = tomedia($merch['banner']);
        $merch['iscollect'] = 0;
        if(!empty($mid))
        {
            $collect_count = Db::name('shop_merch_collect')->where('merchid',$merch['id'])->where('mid',$mid)->field('id,deleted')->find();
            if(empty($collect_count))
            {
                $merch['iscollect'] = 0;
            }
            else
            {
                if($collect_count['deleted'] == 1)
                {
                    $merch['iscollect'] = 0;
                }
                else
                {
                    $merch['iscollect'] = 1;
                }
            }
        }
        $this->result(1,'success',array('merch'=>$merch));
	}

    /**
     * 商户-商品分类
     * @param [int] 
     * @return  [array]    $data  []
     **/
    public function category()
    {
        $category = array();
        $this->result(1,'success',array('category'=>$category));
    }

    /**
     * 商户-收藏
     * @param [int] 
     * @return  [array]    $data  []
     **/
    public function collect()
    {
        $merchid = input('merchid/d');
        $mid = $this->getMemberId();
        if(empty($merchid))
        {
            $store = 1;
        } else {
            $store = Db::name('shop_merch')->where('id',$merchid)->find();
        }
        
        if (empty($store)) {
            $this->result(1,'商户未找到');
        }
        $iscollect = 1;
        $data = Db::name('shop_merch_collect')->where('merchid',$merchid)->where('mid',$mid)->find();
        if (empty($data)) {
            $data = array('merchid' => $merchid, 'mid' => $mid, 'createtime' => time());
            Db::name('shop_merch_collect')->insert($data);
        } else {
            if($data['deleted'] == 0)
            {
                $deleted = 1;
                $iscollect = 0;
            } else {
                $deleted = 0;
                $iscollect = 1;
            }
            Db::name('shop_merch_collect')->where('id',$data['id'])->setField('deleted',$deleted);
        }
        if($iscollect) {
            Db::name('shop_merch')->where('id',$merchid)->setInc('collectcount');
        } else {
            if($store['collectcount'] >= 1) {
                Db::name('shop_merch')->where('id',$merchid)->setDec('collectcount');
            }
        }
        $this->result(1,'success',array('iscollect'=>$iscollect));
    }

    /**
     * 商户-入住申请
     * @param [int] 
     * @return  [array]    $data  []
     **/
    public function regconfirm()
    {
        $shopset = $this->shopset;
        $set = model('common')->getPluginset('merch');
        $mid = $this->getMemberId();
        if (empty($set['apply_openmobile'])) {
            $this->result(0,'未开启商户入驻申请');
        }
        $reg = Db::name('shop_store_reg')->where('mid',$mid)->find();
        $user = false;
        if (!(empty($reg['status']))) 
        {
            $user = Db::name('shop_merch')->where('mid',$mid)->find();
        }
        if (!(empty($user)) && (1 <= $user['status'])) 
        {
            $this->result(0,'您已经申请，无需重复申请!');
        }
        $apply_set = array();
        $apply_set['applycontent'] = $set['applycontent'];
        if (empty($set['applytitle'])) 
        {
            $apply_set['applytitle'] = '入驻申请协议';
        } else {
            $apply_set['applytitle'] = $set['applytitle'];
        }
        $apply_set['regbg'] = $set['regbg'];
        $this->result(1,'success',$apply_set);
    }

	public function register()
	{
		$mid = $this->getMemberId();
		$set = model('common')->getPluginset('merch');

		if (empty($set['apply_openmobile'])) {
			$this->result(0, '未开启商户入驻申请!');
		}

		$user = false;
		$reg = Db::name('shop_merch_reg')->where('mid',$mid)->find();
		if (!empty($reg['status'])) {
			$user = Db::name('shop_merch')->where('mid',$mid)->find();
		}

		if (!empty($user) && 1 <= $user['status']) {
			$this->result(0, '您已经申请，无需重复申请!');
		}

		$uname = trim(input('uname'));
		$upass = input('upass');

		if (empty($uname)) {
			$this->result(0, '请填写帐号!');
		}

		if (empty($upass)) {
			$this->result(0, '请填写密码!');
		}

		$where1 = ' uname= "' . $uname . '"';

		if (!empty($reg)) {
			$where1 .= ' and id<> ' . $reg['id'];
		}

		$usercount1 = Db::name('shop_merch_reg')->where($where1)->count();
		$where2 = ' username= "' . $uname . '"';
		$usercount2 = Db::name('shop_merch_account')->where($where2)->count();
		if (0 < $usercount1 || 0 < $usercount2) {
			$this->result(0, '帐号 ' . $uname . ' 已经存在,请更改!');
		}

		$upass = model('util')->pwd_encrypt($upass, 'E');
		$data = array('mid' => $mid, 'status' => 0, 'realname' => trim(input('realname')), 'mobile' => trim(input('mobile')), 'uname' => $uname, 'upass' => $upass, 'merchname' => trim(input('merchname')), 'salecate' => trim(input('salecate')), 'desc' => trim(input('desc')));
		if (empty($data['realname'])) {
			$this->result(0, '请填写姓名!');
		}
		if (empty($data['mobile'])) {
			$this->result(0, '请填写手机号!');
		}
		if (empty($data['merchname'])) {
			$this->result(0, '请填写商户名称!');
		}

		if (empty($reg)) {
			$data['applytime'] = time();
			$regid = Db::name('shop_merch_reg')->insertGetId($data);
			$user = array('merchname' => trim($data['merchname']), 'salecate' => trim($data['salecate']), 'realname' => $data['realname'], 'mobile' => trim($data['mobile']), 'status' => -1, 'regid' => $regid, 'upass' => $data['upass'], 'mid' => $mid, 'uname' => $uname);
			Db::name('shop_merch')->insert($user);
		} else {
			Db::name('shop_merch_reg')->where('id',$reg['id'])->update($data);
			$user = array('merchname' => trim($data['merchname']), 'salecate' => trim($data['salecate']), 'realname' => $data['realname'], 'mobile' => trim($data['mobile']), 'status' => -1, 'regid' => $reg['id'], 'uname' => $uname, 'mid' => $mid, 'upass' => $data['upass']);
			$regid = $reg['id'];
			Db::name('shop_merch')->where('regid = ' . $reg['id'])->update($user);
		}

		model('notice')->sendMerchMessage(array('merchname' => $data['merchname'], 'salecate' => $data['salecate'], 'realname' => $data['realname'], 'mobile' => $data['mobile'], 'applytime' => time()), 'merch_apply');
		$this->result(1,'申请成功，请耐心等待审核',array('regid'=> $regid));
	}

}