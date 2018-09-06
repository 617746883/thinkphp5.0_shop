<?php
/**
 * apiv1 商城商品
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Message extends Base
{
	/**
	 * 消息中心
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function index()
    {
    	$mid = $this->getMemberId();
    	$list = array();
    	$list = Db::name('member_message')->where('mid',$mid)->distinct(true)->field('messagetype')->select();
    	foreach ($list as &$val) {
    		$message = Db::name('member_message')->where('mid',$mid)->where('messagetype',$val['messagetype'])->order('createtime','desc')->field('id,title,createtime,messagethumb')->find();
            switch ($val['messagetype']) {
                case 'order':
                    $messagetype = '订单消息';
                    break;

                case 'secondgoods':
                    $messagetype = '二手市场';
                    break;
                
                case 'communitynotice':
                    $messagetype = '小区公告';
                    break;
                
                case 'secondgoods':
                    $messagetype = '二手市场';
                    break;
                
                case 'repair':
                    $messagetype = '小区报修';
                    break;
                
                case 'water':
                    $messagetype = '缴水费';
                    break;
                
                case 'electricity':
                    $messagetype = '缴电费';
                    break;
                
                case 'property':
                    $messagetype = '缴物业费';
                    break;
                
                case 'groups':
                    $messagetype = '团购';
                    break;
                
                case 'auction':
                    $messagetype = '拍卖';
                    break;
                
                case 'notice':
                    $messagetype = '公告';
                    break;
                
                case 'treasure':
                    $messagetype = '夺宝';
                    break;
                
                case 'shopnotice':
                    $messagetype = '商城公告';
                    break;
                
                case 'seckill':
                    $messagetype = '秒杀';
                    break;

                case 'system':
                    $messagetype = '系统消息';
                    break;
                
                default:
                    $messagetype = '订单消息';
                    break;
            }
            $val['messageid'] = $message['id'];
    		$val['message'] = $message['title'];
            $val['title'] = $messagetype;
    		$val['createtime'] = $message['createtime'];
    		$val['messagethumb'] = $message['messagethumb'];
    	}
    	unset($val);
    	$list = set_medias($list,'messagethumb');
    	$this->result(1,'success',$list);
    }

    /**
     * 二手市场消息
     * @param 
     * @return  [array]    $list  []
     **/
    public function secondgoods()
    {
        $mid = $this->getMemberId();
        $list = array();
        $page = input('page/d',1);
        $pagesize = input('pagesize/d',10);

        $list = Db::name('member_message')->where('mid',$mid)->where('messagetype','secondgoods')->field('id,title,remark,createtime,messagetid,businessid')->order('createtime','desc')->page($page,$pagesize)->select();

        $this->result(1,'success',array('list' => $list, 'page' => $page, 'pagesize' => $pagesize));
    }

    /**
     * 系统消息
     * @param 
     * @return  [array]    $list  []
     **/
    public function system()
    {
        $mid = $this->getMemberId();
        $list = array();
        $page = input('page/d',1);
        $pagesize = input('pagesize/d',10);

        $list = Db::name('member_message')->where('mid',$mid)->where('messagetype','system')->field('id,title,remark,createtime,messagetid,businessid,datas')->order('createtime','desc')->page($page,$pagesize)->select();

        $this->result(1,'success',array('list' => $list, 'page' => $page, 'pagesize' => $pagesize));
    }

    /**
     * 报修消息
     * @param 
     * @return  [array]    $list  []
     **/
    public function repair()
    {
        $mid = $this->getMemberId();
        $list = array();
        $page = input('page/d',1);
        $pagesize = input('pagesize/d',10);
        $list = Db::name('community_apply_repair')->alias('r')
                ->join('community_house h','h.id = r.houseid','left')
                ->join('community c','c.id = h.communityid','left')
                ->join('community_building b','b.id = h.buildingid','left')
                ->where('r.mid',$mid)
                ->field('r.*,h.housesn,h.housename,c.communityname,b.buildingname')
                ->order('r.createtime','desc')->page($page,$pagesize)->select();
        foreach ($list as &$val) {
            $val['thumb_url'] = iunserializer($val['thumb_url']);
            $val['thumb_url'] = set_medias($val['thumb_url']);
            $log = array();
            if($val['status'] > 1) {
                $log[] = array('type' => 1, 'time' => $val['createtime'], 'remark' => '提交维修申请');
                if(!empty($val['maketime']) && $val['status'] >= 2) {
                    $log[] = array('type' => 2, 'time' => $val['maketime'], 'remark' => '正在处理中');
                }
                if(!empty($val['finishtime']) && $val['status'] >= 3) {
                    $log[] = array('type' => 40, 'time' => $val['finishtime'], 'remark' => '已完成维修');
                }
            } elseif($val['status'] == 1) {
                $log[0] = array('type' => 1, 'time' => $val['createtime'], 'remark' => '提交维修申请');
            } elseif($val['status'] == -1) {
                $log[0] = array('type' => -1, 'time' => $val['canceltime'], 'remark' => '维修申请已关闭');
            }

            $val['log'] = $log;
        }
        unset($val);
        $this->result(1,'success',array('list' => $list, 'page' => $page, 'pagesize' => $pagesize));
    }

    /**
	 * 测试
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function test()
    {
    	$mid = $this->getMemberId();
    	$list = array();
    	$page = input('page/d',1);
    	$pagesize = input('pagesize/d',10);

        $list = Db::name('member_message')->where('mid',$mid)->distinct(true)->field('messagetid')->order('createtime','desc')->page($page,$pagesize)->select();
    	foreach ($list as &$val) {
            $message = Db::name('member_message')->where('mid',$mid)->where('messagetid',$val['messagetid'])->order('createtime','desc')->find();
    		$datas = iunserializer($message['datas']);
            $val['datas'] = $datas;
            $omessages = Db::name('member_message')->where('mid',$mid)->where('messagetid',$val['messagetid'])->field('id,title,remark,createtime')->order('createtime','desc')->select();
            $log = array();
            foreach ($omessages as &$row) {
                $log[] = array('time' => $row['createtime'], 'remark' => $row['title']);
            }
            $val['log'] = $log;
            unset($row);
    	}
        unset($val);
    	$this->result(1,'success',array('list' => $list, 'page' => $page, 'pagesize' => $pagesize));
    }

}