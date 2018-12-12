<?php
/**
 * 后台首页
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\merch\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Controller;
class Index extends Base
{
    public function index()
    {        
        $shop_data = model('common')->getSysset('shop');
        $merch_data = model('common')->getPluginset('merch');
        $merch = $this->merch;
        if ($merch_data['is_openmerch']) {
            $is_openmerch = 1;
        } else {
            $is_openmerch = 0;
        }

        $order = Db::name('shop_order')->where('merchid= ' . $merch['id'] . ' and isparent=0 and deleted=0 AND ( status = 1 or (status=0 and paytype=3) )')->order('createtime','asc')->limit(20)->field('id,ordersn,createtime,address,price,invoicename')->select();

        foreach ($order as &$value ) {
            $value['address'] = iunserializer($value['address']);
        }
        unset($value);
        $order_ok = $order;
        $notice = array();
        $goods_totals = Db::name('shop_goods')->where('status=1 and deleted=0 and total<=0 and total<>-1 and merchid = ' . $merch['id'])->count();
        $order_totals = model('order')->getTotals($merch['id']);
        $this->assign(['order_ok'=>$order_ok,'notice'=>$notice,'goods_totals'=>$goods_totals,'order_totals'=>$order_totals]);
        return $this->fetch('/index');
    }

    public function error()
    {
    	return $this->fetch('/error');
    }
    
}