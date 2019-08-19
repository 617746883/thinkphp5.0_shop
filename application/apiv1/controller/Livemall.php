<?php

namespace app\apiv1\controller;

use think\Controller;
use think\Request;
use think\Db;

class Livemall extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function apply()
    {
        $slogans = trim(input('slogans'));
        $goodsid = intval(input('goodsid'));
        $mid = $this->getMemberId();
        $set = model('common')->getPluginset('livemall');
        if(!empty($set) && !empty($set['salegoodcount'])) {
            $goodscount = Db::name('livemall_goods_agent')->where('mid = ' . intval($mid) . ' and status >= 0')->count();
            if($set['salegoodcount'] <= $goodscount) {
                $this->result(0, '代售商品已达最大限制!');
            }
        }
        if (empty($goodsid)) {
            $this->result(0, '请选择代售商品!');
        }
        if (empty($slogans)) {
            $this->result(0, '请填写推荐语!');
        }
        $goods = fetch('SELECT id,title,status,checked,nolive FROM ' . tablename('shop_goods') . ' WHERE id = ' . intval($goodsid) . ' limit 1');
        if(empty($goods) || $goods['status'] != 1 || $goods['checked'] != 0) {
            $this->result(0, '商品信息错误!');
        }
        if($goods['nolive'] != 0) {
            $this->result(0, '此商品不允许代售!');
        }
        $agent = false;
        $reg = Db::name('livemall_reg')->where('mid = ' . $mid . ' and goodsid = ' . $goods['id'])->find();
        if (!empty($reg['status'])) {
            $agent = Db::name('livemall_goods_agent')->where('mid = ' . intval($mid) . ' and goodsid = ' . intval($goodsid))->find();
        }

        if (!empty($agent) && 1 <= $agent['status']) {
            $this->result(0, '您已经申请此商品代售权!');
        }
        $data = array('mid' => $mid, 'status' => 0, 'slogans' => $slogans, 'goodsid' => $goodsid);
        if (empty($reg)) {
            $data['applytime'] = time();
            $regid = Db::name('livemall_reg')->insertGetId($data);
            $agent = array('status' => -1, 'regid' => $regid, 'goodsid' => $goodsid, 'mid' => $mid, 'slogans' => $slogans);
            Db::name('livemall_goods_agent')->insert($agent);
        } else {
            Db::name('livemall_reg')->where('id',$reg['id'])->update($data);
            $agent = array('status' => -1, 'goodsid' => $goodsid, 'regid' => $reg['id'], 'slogans' => $slogans, 'mid' => $mid);
            $regid = $reg['id'];
            Db::name('livemall_goods_agent')->where('regid = ' . $reg['id'])->update($agent);
        }

        // model('notice')->sendLivemallReg(array('merchname' => $data['merchname'], 'salecate' => $data['salecate'], 'realname' => $data['realname'], 'mobile' => $data['mobile'], 'applytime' => time()), 'merch_apply');
        $this->result(1,'申请成功，请耐心等待审核',array('regid'=> $regid));
    }

    public function goodslist()
    {
        $mid = $this->getMemberId();
        $agentgoodsids = Db::name('livemall_goods_agent')->where('mid = ' . $mid . ' and status = 1')->column('goodsid');
        if(empty($agentgoodsids)) {
            $this->result(0, '暂无代售商品!');
        }
        $args = array('pagesize' => isset($_GET['pagesize']) ? $_GET['pagesize'] : 10, 'page' => max(1, isset($_GET['page']) ? intval($_GET['page']) : 1), 'isnew' => trim($_GET['isnew']), 'ishot' => trim($_GET['ishot']), 'isrecommand' => trim($_GET['isrecommand']), 'isdiscount' => trim($_GET['isdiscount']), 'istime' => trim($_GET['istime']), 'keywords' => trim($_GET['keywords']), 'cate' => trim($_GET['cate']), 'order' => trim($_GET['order']), 'by' => trim($_GET['by']));
        $args['agentgoodsids'] = $agentgoodsids;
        $goods = model('goods')->getList($args);
        $this->result(1, 'success', array('list'=>$goods,'page'=>$args['page'],'pagesize'=>$args['pagesize']));
    }

}
