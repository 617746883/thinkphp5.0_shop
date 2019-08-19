<?php

namespace app\merch\controller;

use think\Controller;
use think\Request;
use think\Db;

class Luckbuy extends Base
{
    public function index()
    {
        $pindex = max(1, intval($_GET['page']));
        $psize = 10;
        $merch = $this->merch;
        $condition = ' and merchid=' . $merch['id'] . ' and `deleted`=0 ';

        if (!empty($_GET['keyword'])) {
            $_GET['keyword'] = trim($_GET['keyword']);
            $condition .= ' AND `lottery_title` LIKE \'%' . trim($_GET['keyword']) . '%\'';
        }

        $list = fetchall('SELECT * FROM ' . tablename('shop_lottery') . (' WHERE 1 ' . $condition . ' ORDER BY createtime desc LIMIT ') . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shop_lottery') . (' where 1 ' . $condition . ' '), $params);
        $pager = pagination2($total, $pindex, $psize);

        foreach ($list as $key => $value) {
            $member_total = fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shop_lottery_join') . ' where lottery_id= ' . $value['lottery_id']);
            $list[$key]['viewcount'] = $member_total;
        }
        $this->assign(['list'=>$list,'pager'=>$pager]);
        return $this->fetch('');
    }

    public function add()
    {
        $data = $this->post();
        return $data;
    }

    public function edit()
    {
        $data = $this->post();
        return $data;
    }

    protected function post()
    {
        $id = intval($_GET['id']);
        $merch = $this->merch;        
        if ($id) {
            $item = fetch('SELECT * FROM ' . tablename('shop_lottery') . ' WHERE lottery_id = ' . $id . ' limit 1');
            $type = intval($item['lottery_type']);
            $reward = unserialize($item['lottery_data']);
        }

        if (Request::instance()->isPost()) {
            if (empty(intval($_POST['goodsid']))) {
                show_json(0, '请选择商品');
            }
            if (empty(intval($_POST['groupcount'])) || intval($_POST['groupcount']) <= 0) {
                show_json(0, '每组人数不能小于0');
            }

            $data = array();
            $data['lottery_title'] = $_POST['lottery_title'];
            $data['lottery_icon'] = trim($_POST['lottery_icon']);
            $data['lottery_banner'] = trim($_POST['lottery_banner']);
            $data['luckbuycount'] = intval($_POST['luckbuycount']);
            $data['groupcount'] = intval($_POST['groupcount']);
            $data['goodsid'] = intval($_POST['goodsid']);
            $data['joinlimied'] = intval($_POST['joinlimied']);
            $data['lottery_cannot'] = trim(str_replace(array('', '', ''), '', $_POST['lottery_cannot']));
            $data['status'] = intval($_POST['status']); 
            $data['merchid'] = $merch['id'];
            if (intval($data['luckbuycount']) > intval($merch['luckbuytotal'])) {
                show_json(0, '您的活动次数不够');
            }           
            if (intval($data['luckbuycount']) < intval($data['groupcount'])) {
                show_json(0, '活动次数不能小于每组人数');
            }
            if ($id) {
                $res = update('shop_lottery', $data, array('lottery_id' => $id));
                if ($res) {
                    model('shop')->mplog('lottery.edit', '修改抽奖活动 ID: ' . $id . '<br>');
                } else {
                    show_json(0, '更新操作失败');
                }

                show_json(1, array('url' => url('merch/luckbuy/index')));
            } else {                
                $taskTypeIsExist = intval($this->taskTypeIsExist($_POST['goodsid']));
                if (!empty($taskTypeIsExist)) {
                    show_json(0, '该商品场景已经存在，不能给一个商品添加两个游戏');
                }
                $data['createtime'] = time();
                $data['stock'] = intval($_POST['luckbuycount']);
                $id = insert('shop_lottery', $data, true);

                if ($id) {
                    model('shop')->mplog('lottery.edit', '添加抽奖活动 ID: ' . $id . '<br>');
                } else {
                    show_json(0, '添加操作失败');
                }
                Db::name('shop_merch')->where('id = ' . $merch['id'])->setDec('luckbuytotal',$data['luckbuycount']);
                show_json(1, array('url' => url('merch/luckbuy/index')));
            }
        }
        $luckbuygoods = Db::name('shop_goods')->where('merchid = ' . $merch['id'] . ' and luckbuy = 1 and `status` = 1  and `total`>0 and `deleted`=0  AND `checked`=0')->select();
        $this->assign(['item'=>$item,'luckbuygoods'=>$luckbuygoods]);
        return $this->fetch('luckbuy/post');
    }

    public function delete()
    {
        $id = intval($_GET['id']);

        if (empty($id)) {
            $id = is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0;
        }

        $posters = fetchall('SELECT lottery_id,lottery_title FROM ' . tablename('shop_lottery') . (' WHERE lottery_id in ( ' . $id . ' ) '));

        foreach ($posters as $poster) {
            update('shop_lottery', array('deleted' => 1), array('lottery_id' => $poster['lottery_id']));
            model('shop')->mplog('lottery.delete', '删除抽奖 ID: ' . $id . ' 海报名称: ' . $poster['lottery_title']);
        }

        show_json(1, array('url' => url('merch/luckbuy/index')));
    }

    public function testlottery()
    {
        $reward = array();
        $inforeward = array();
        $temreward = array();
        $teminforeward = array();

        foreach ($_GET['testreward'] as $key => $value) {
            $temreward[$value['rank']] = $value['probability'];
            $teminforeward[$value['rank']] = $value;
        }

        ksort($temreward, 1);

        foreach ($temreward as $key => $value) {
            array_push($reward, $value);
            array_push($inforeward, $teminforeward[$key]);
        }

        $num = $this->getRand($reward);
        $info = array('status' => 1, 'num' => $num, 'info' => $inforeward[$num]);
        echo json_encode($info);
        exit();
    }

    private function getRand($proArr)
    {
        $result = '';
        $proSum = array_sum($proArr);

        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);

            if ($randNum <= $proCur) {
                $result = $key;
                break;
            }

            $proSum -= $proCur;
        }

        unset($proArr);
        return intval($result);
    }

    public function log()
    {
        $pindex = max(1, intval($_GET['page']));
        $psize = 10;
        $condition = ' and log.lottery_id=' . intval($_GET['id']);
        $keyword = trim($_GET['keyword']);

        if (!empty($keyword)) {
            $condition .= ' AND ( m.nickname LIKE \'%' . $keyword . '%\' or m.realname LIKE \'%' . $keyword . '%\' or m.mobile LIKE \'%' . $keyword . '%\' ) ';
        }

        if (!empty($_GET['time']['start']) && !empty($_GET['time']['end'])) {
            $starttime = strtotime($_GET['time']['start']);
            $endtime = strtotime($_GET['time']['end']);
            $condition .= ' AND log.addtime >= ' . $starttime . ' AND log.addtime <= ' . $endtime;
        }

        $list = fetchall('SELECT log.*, m.avatar,m.nickname,m.realname,m.mobile,record.lottery_data,record.is_reward as logis_reward,record.bonus,record.log_id FROM ' . tablename('shop_lottery_join') . ' log ' . ' left join ' . tablename('member') . ' m on m.id = log.join_user left join ' . tablename('shop_lottery_log') . ' record on record.join_id = log.id ' . (' WHERE 1 ' . $condition . ' ORDER BY log.addtime desc ') . '  LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, $params);
        $total = fetchcolumn('SELECT count(*)  FROM ' . tablename('shop_lottery_join') . ' log ' . ' left join ' . tablename('member') . ' m on m.id = log.join_user left join ' . tablename('shop_lottery_log') . ' record on record.join_id = log.id ' . (' where 1 ' . $condition . '  '), $params);

        $pager = pagination2($total, $pindex, $psize);
        $total = fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shop_lottery_join') . ' where lottery_id= ' . $_GET['id']);
        $this->assign(['list'=>$list,'pager'=>$pager,'total'=>$total]);
        return $this->fetch('');
    }

    public function taskTypeIsExist($goodsid)
    {
        $sql = 'SELECT COUNT(1) FROM ' . tablename('shop_lottery') . ' WHERE goodsid = ' . $goodsid . ' AND deleted = 0 and stock > 0 and status = 1';
        $res = fetchcolumn($sql);
        $res = intval($res);
        return $res;
    }

    public function goods($goodsfrom = "") 
    {
        $merch = $this->merch;
        $psize = 20;
        $sqlcondition = $groupcondition = "";
        $condition = " g.`merchid`=" . $merch['id'] . ' and luckbuy = 1 and deleted = 0';
        $not_add = 0;
        $maxgoods = intval($merch["maxgoods"]);
        if( 0 < $maxgoods ) 
        {
            $goodstotal = Db::name('shop_goods')->where('merchid = ' . $merch['id'])->count();
            if( $maxgoods <= $goodstotal ) 
            {
                $not_add = 1;
            }
        }
        $querysql = Db::name('shop_goods')->alias('g');
        if( !empty($_GET["keyword"]) ) 
        {
            $keyword = trim($_GET["keyword"]);
            $querysql = $querysql->join('shop_goods_option op','g.id = op.goodsid','left');
            $querysql = $querysql->group('g.`id`');
            $condition .= " AND (g.`id` = '%" . $keyword . "%' or g.`title` LIKE '%" . $keyword . "%' or g.`goodssn` LIKE '%" . $keyword . "%' or g.`productsn` LIKE '%" . $keyword . "%' or op.`title` LIKE '%" . $keyword . "%' or op.`goodssn` LIKE '%" . $keyword . "%' or op.`productsn` LIKE '%" . $keyword . "%')";
        }
        if( !empty($_GET["cate"]) ) 
        {
            $cate = intval($_GET["cate"]);
            $condition .= " AND FIND_IN_SET(" . $cate . ",cates)<>0 ";
        }
        // if( empty($goodsfrom) ) 
        // {
        //     $goodsfrom = $_GET["goodsfrom"];
        // }
        // if( empty($goodsfrom) ) 
        // {
        //     $goodsfrom = "sale";
        // }
        // if( $goodsfrom == "sale" ) 
        // {
        //     $condition .= " AND g.`status` = 1  and g.`total`>0 and g.`deleted`=0  AND g.`checked`=0";
        //     $status = 1;
        // }
        // else 
        // {
        //     if( $goodsfrom == "out" ) 
        //     {
        //         $condition .= " AND g.`total` <= 0 AND g.`status` <> 0 and g.`deleted`=0  AND g.`checked`=0";
        //         $status = 1;
        //     }
        //     else 
        //     {
        //         if( $goodsfrom == "stock" ) 
        //         {
        //             $status = 0;
        //             $condition .= " AND g.`status` = 0 and g.`deleted`=0 AND g.`checked`=0";
        //         }
        //         else 
        //         {
        //             if( $goodsfrom == "cycle" ) 
        //             {
        //                 $status = 0;
        //                 $condition .= " AND g.`deleted`=1";
        //             }
        //             else 
        //             {
        //                 if( $goodsfrom == "check" ) 
        //                 {
        //                     $status = 0;
        //                     $condition .= " AND g.`checked`=1 and g.`deleted`=0";
        //                 }
        //             }
        //         }
        //     }
        // }

        $list = $querysql->where($condition)->field('g.*')->order('g.status DESC, g.displayorder DESC,g.id DESC')->paginate($psize);
        foreach( $list as $key => $value ) 
        {
            $value["allcates"] = explode(",", $value["cates"]);
            $value["allcates"] = array_unique($value["allcates"]);
            $sale_cpcount = Db::query("SELECT sum(og.total)  as sale_count FROM " . tablename("shop_order_goods") . " og LEFT JOIN " . tablename("shop_order") . " o on og.orderid=o.id  WHERE og.goodsid=" . $value["id"] . " and o.`status`>=1 and o.refundid = 0 ");
            $value["sale_cpcount"] = $sale_cpcount["sale_count"];
            $data = array();
            $data = $value;
            $list->offsetSet($key,$data);
        }
        unset($value);
        $pager = $list->render();
        $categorys = model("merch")->getFullCategory($merch['id'],true,true);
        $category = array( );
        foreach( $categorys as $cate ) 
        {
            $category[$cate["id"]] = $cate;
        }
        $this->assign(['list'=>$list,'pager'=>$pager,'category'=>$category,'keyword'=>$keyword,'cate'=>$cate,'status'=>$status,'goodsfrom'=>$goodsfrom]);
        return $this->fetch('luckbuy/goods/index');
    }
}
