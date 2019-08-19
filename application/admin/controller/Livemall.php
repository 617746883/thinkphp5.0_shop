<?php

namespace app\admin\controller;

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
    public function index()
    {
        return $this->fetch('livemall/index');
    }

    public function apply0() 
    {
        $data = $this->apply(-1);
        return $data;
    }

    public function apply1() 
    {
        $data = $this->apply(0);
        return $data;
    }

    private function apply($status = '')
    {
        $pindex = max(1, intval($_GET["page"]));
        $psize = 20;
        $condition = "";
        $keyword = trim($_GET["keyword"]);
        if (!empty($keyword)) {
            $condition .= " and ( reg.slogans like \"%" . $keyword . "%\" or g.title like \"%" . $keyword . "%\" or m.nickname like \"%" . $keyword . "%\"  or m.mobile like \"%" . $keyword . "%\" )";
        }
        if ($status !== "") {
            $condition .= " and reg.status=" . intval($status);
        }
        $sql = "select  reg.*,g.title,g.thumb,m.nickname,m.mobile from " . tablename("livemall_reg") . " reg " . " left join " . tablename("shop_goods") . " g on reg.goodsid = g.id" . " left join " . tablename("member") . " m on reg.mid = m.id" . " where 1 " . $condition . " ORDER BY applytime desc";
        if (empty($_GET["export"])) {
            $sql .= " limit " . ($pindex - 1) * $psize . "," . $psize;
        }
        $list = fetchall($sql);
        $total = fetchcolumn("select count(*) from"  . tablename("livemall_reg") . " reg " . " left join " . tablename("shop_goods") . " g on reg.goodsid = g.id" . " left join " . tablename("member") . " m on reg.mid = m.id" . "  where 1 " . $condition);
        if ($_GET["export"] == "1") {
            model('shop')->plog("livemall.user.export", "导出星店申请数据");
            foreach ($list as &$row) {
                $row["goodstitle"] = empty($row["goods"]) ? "-" : $row["goods"]["title"];
                $row["applytime"] = empty($row["applytime"]) ? "-" : date("Y-m-d H:i", $row["applytime"]);
                $row["statusstr"] = empty($row["status"]) ? "待审核" : ($row["status"] == 1 ? "已允许" : "驳回");
            }
            unset($row);
            model("excel")->export($list, array("title" => "星店数据-" . date("Y-m-d-H-i", time()), "columns" => array(array("title" => "ID", "field" => "id", "width" => 12), array("title" => "商品ID", "field" => "goodsid", "width" => 12), array("title" => "商品名称", "field" => "goodstitle", "width" => 24), array("title" => "主播推荐语", "field" => "slogans", "width" => 24), array("title" => "申请时间", "field" => "applytime", "width" => 12), array("title" => "状态", "field" => "statusstr", "width" => 12))));
        }
        $pager = pagination2($total, $pindex, $psize);
        $this->assign(['list'=>$list,'pager'=>$pager]);
        return $this->fetch('livemall/apply/index');
    }

    public function regdetail()
    {
        $id = intval($_GET["id"]);
        $item = fetch("select * from " . tablename("livemall_reg") . " where id=" . $id . " limit 1");
        if (empty($item)) {
            if (Request::instance()->isPost()) {
                show_json(0, "未找到商户代售申请!");
            }
            $this->message("未找到商户代售申请!", url("admin/livemall/apply", array("status" => 0)), "error");
        }
        $member = model("member")->getMember($item["mid"]);
        $goods = fetch("select * from " . tablename('shop_goods') . " where id = " . intval($item['goodsid']) . " limit 1");
        if (Request::instance()->isPost()) {
            $status = intval($_POST["status"]);
            $reason = trim($_POST["reason"]);
            if ($status == -1) {
                if (empty($reason)) {
                    show_json(0, "请填写驳回理由.");
                }
            }
            $item["status"] = $status;
            $item["reason"] = $reason;
            $item["slogans"] = trim($_POST["slogans"]);
            update("livemall_reg", $item, array("id" => $item["id"]));
            if ($status == 1) {
                $usercount = fetch("select * from " . tablename("livemall_goods_agent") . " where regid = " . $item["id"] . " limit 1");
                if (empty($usercount)) {
                    $user = $item;
                    unset($user["id"]);
                    unset($user["reason"]);
                    $user["regid"] = $item["id"];
                    $user["status"] = 0;
                    
                    $userid = insert("livemall_goods_agent", $user, true);
                    update("livemall_reg", $item, array("id" => $item["id"]));
                    show_json(1, array("message" => "允许代售成功，请编辑代售资料!", "url" => url("admin/livemall/agentedit", array("id" => $userid))));
                } else {
                    $user = $item;
                    unset($user["id"]);
                    unset($user["reason"]);
                    $user["status"] = 0;
                    update("livemall_goods_agent", $user, array("regid" => $item["id"]));
                    update("livemall_reg", $item, array("id" => $item["id"]));
                    show_json(1, array("message" => "允许代售成功，请编辑代售资料!", "url" => url("admin/livemall/agentedit", array("id" => $usercount["id"]))));
                }
            } else {
                if ($status == -1) {
                    update("livemall_reg", $item, array("id" => $item["id"]));
                }
            }
            show_json(1);
        }
        $this->assign(['item'=>$item,'member'=>$member,'goods'=>$goods]);
        return $this->fetch('livemall/apply/detail');
    }
    public function regdelete()
    {
        $id = intval($_GET["id"]);
        if (empty($id)) {
            $id = is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0;
        }
        $regs = fetchall("SELECT id FROM " . tablename("livemall_reg") . " WHERE id in( " . $id . " ) " );
        foreach ($regs as $reg) {
            delete("livemall_reg", array("id" => $reg["id"]));
            model('shop')->plog("livemall.reg.delete", "删除代售申请 ID:  " . $reg["id"]);
        }
        show_json(1, array("url" => referer()));
    }

    private function agent($status = '')
    {
        $pindex = max(1, intval($_GET["page"]));
        $psize = 20;
        $condition = "";
        $keyword = trim($_GET["keyword"]);
        if (!empty($keyword)) {
            $condition .= " and ( a.merchname like \"%" . $keyword . "%\" or a.realname like \"%" . $keyword . "%\" or a.mobile like \"%" . $keyword . "%\")";
        }
        if ($status !== "") {
            $status = intval($status);
            $condition .= " and a.status=" . $status;
        }
        $sortfield = "a.createtime";
        $sql = "select  a.*,g.title,g.thumb,m.realname,m.mobileverify,m.isblack,m.avatar,m.nickname,m.mobile  from " . tablename("livemall_goods_agent") . "  a " . " left join  " . tablename("shop_goods") . " g on a.goodsid = g.id " . " left join " . tablename('member') . " m on a.mid = m.id " . " where 1 " . $condition . " ORDER BY " . $sortfield . " desc";
        if (empty($_GET["export"])) {
            $sql .= " limit " . ($pindex - 1) * $psize . "," . $psize;
        }
        $list = fetchall($sql);
        $total = fetchcolumn("select count(*) from" . tablename("livemall_goods_agent") . " a  " . " left join  " . tablename("shop_goods") . " g on a.goodsid = g.id " . " left join " . tablename('member') . " m on a.mid = m.id " . " where 1 " . $condition);
        if ($_GET["export"] == "1") {
            model('shop')->plog("merch.user.export", "导出商户数据");
            foreach ($list as &$row) {
                $row["applytime"] = empty($row["applytime"]) ? "-" : date("Y-m-d H:i", $row["applytime"]);
                $row["checktime"] = empty($row["checktime"]) ? "-" : date("Y-m-d H:i", $row["checktime"]);
                $row["groupname"] = empty($row["groupid"]) ? "无分组" : $row["groupname"];
                $row["statusstr"] = empty($row["status"]) ? "待审核" : ($row["status"] == 1 ? "通过" : "未通过");
                $row["accounttime"] = date("Y-m-d H:i", $row["accounttime"]);
            }
            unset($row);
            model("excel")->export($list, array("title" => "商户数据-" . date("Y-m-d-H-i", time()), "columns" => array(array("title" => "ID", "field" => "id", "width" => 12), array("title" => "商户名", "field" => "merchname", "width" => 24), array("title" => "主营项目", "field" => "salecate", "width" => 12), array("title" => "联系人", "field" => "realname", "width" => 12), array("title" => "手机号", "field" => "moible", "width" => 12), array("title" => "子帐号数", "field" => "accounttotal", "width" => 12), array("title" => "可提现金额", "field" => "status0", "width" => 12), array("title" => "已结算金额", "field" => "status3", "width" => 12), array("title" => "到期时间", "field" => "accounttime", "width" => 12), array("title" => "申请时间", "field" => "applytime", "width" => 12), array("title" => "审核时间", "field" => "checktime", "width" => 12), array("title" => "状态", "field" => "createtime", "width" => 12))));
        }
        $pager = pagination2($total, $pindex, $psize);
        $this->assign(['list'=>$list,'pager'=>$pager]);
        return $this->fetch('livemall/agent/index');
    }

    public function agent0()
    {
        $data = $this->agent(0);
        return $data;
    }
    public function agent1()
    {
        $data = $this->agent(1);
        return $data;
    }
    public function agent2()
    {
        $data = $this->agent(2);
        return $data;
    }

    public function agentadd()
    {
        $data = $this->agentpost();
        return $data;
    }
    public function agentedit()
    {
        $data = $this->agentpost();
        return $data;
    }
    protected function agentpost()
    {
        $id = intval($_GET["id"]);
        $item = fetch("select * from " . tablename("livemall_goods_agent") . " where id=" . $id . " limit 1");
        $member = $goods = array();
        if (!empty($item["mid"])) {
            $member = model("member")->getMember($item["mid"]);
        }
        if (!empty($item["goodsid"])) {
            $goods = fetch('select * from' . tablename('shop_goods') . ' where id = ' . intval($item['goodsid']) . ' limit 1');
        }
        if (Request::instance()->isPost()) {
            $status = intval($_POST["status"]);
            $data = array("slogans" => trim($_POST["slogans"]), "hascommission" => intval($_POST["hascommission"]), "commission1_rate" => floatval($_POST["commission1_rate"]), "commission1_pay" => floatval($_POST["commission1_pay"]), "status" => $status);
            if (empty($item["createtime"]) && $status == 1) {
                $data["createtime"] = time();
            }
            $item = fetch("select * from " . tablename("livemall_goods_agent") . " where id=" . $id . " limit 1");
            if (empty($item)) {
                $item["createtime"] = time();
                $id = insert("livemall_goods_agent", $data, true);
                model('shop')->plog("merch.user.add", "添加主播代理商品 ID: " . $data["id"]);
            } else {
                update("livemall_goods_agent", $data, array("id" => $id));
                model('shop')->plog("merch.user.edit", "编辑主播代理商品 ID: " . $data["id"]);
            }
            show_json(1, array("url" => url("admin/livemall/agent" . $status)));
        }
        $this->assign(['item'=>$item,'member'=>$member,'goods'=>$goods]);
        return $this->fetch('livemall/agent/post');
    }
    public function get_show_money()
    {
        $id = intval($_GET["id"]);
        if (!empty($id)) {
            $tmoney = model('livemall')->getLiveOrderTotalPrice($id);
            show_json(1, array("status0" => $tmoney["status0"], "status3" => $tmoney["status3"]));
        }
    }
    public function agentstatus()
    {
        $id = intval($_GET["id"]);
        if (empty($id)) {
            $id = is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0;
        }
        $items = fetchall("SELECT id FROM " . tablename("livemall_goods_agent") . " WHERE id in( " . $id . " ) ");
        foreach ($items as $item) {
            update("livemall_goods_agent", array("status" => intval($_GET["status"])), array("id" => $item["id"]));
            model('shop')->plog("merch.group.edit", "修改商户分组账户状态<br/>ID: " . $item["id"] . "<br/>商户名称: " . $item["merchname"] . "<br/>状态: " . $_GET["status"] == 1 ? "启用" : "禁用");
        }
        show_json(1);
    }
    public function agentdelete()
    {
        $id = intval($_GET["id"]);
        if (empty($id)) {
            $id = is_array($_POST["ids"]) ? implode(",", $_POST["ids"]) : 0;
        }
        $change_data = array();
        $change_data["merchid"] = 0;
        $change_data["status"] = 0;
        $items = fetchall("SELECT * FROM " . tablename("livemall_goods_agent") . " WHERE id in( " . $id . " ) AND ");
        foreach ($items as $item) {
            delete("livemall_reg", array("id" => $item["regid"]));
            delete("livemall_goods_agent", array("id" => $item["id"]));
            model('shop')->plog("merch.user.delete", "删除`商户 <br/>商户:  ID: " . $item["id"] . " / 名称:   " . $item["merchname"]);
        }
        show_json(1);
    }
    public function agentquery()
    {
        $kwd = trim($_GET["keyword"]);
        $condition = " status=1 ";
        if (!empty($kwd)) {
            $condition .= " AND `merchname` LIKE \"%" . $kwd . "%\"";
        }
        $ds = fetchall("SELECT id,merchname FROM " . tablename("livemall_goods_agent") . " WHERE " . $condition . " order by id asc");
        $this->assign(['list'=>$list,'pager'=>$pager]);
        return $this->fetch('livemall/agent/query');
        exit;
    }
    public function queryagents()
    {
        $kwd = trim($_GET["keyword"]);
        $condition = " and status =1";
        if (!empty($kwd)) {
            $condition .= " AND `merchname` LIKE \"%" . $kwd . "%\"";
        }
        $ds = fetchall("SELECT id,merchname as title ,logo as thumb FROM " . tablename("livemall_goods_agent") . " WHERE 1 " . $condition . " order by id desc");
        $ds = set_medias($ds, array("thumb", "share_icon"));
        if ($_GET["suggest"]) {
            exit(json_encode(array("value" => $ds)));
        }
        return $this->fetch('livemall/agent/queryagents');
    }

    public function statisticsorder()
    {
        $pindex = max(1, intval($_GET['page']));
        $psize = 20;

        //多商户
        $merch_plugin = m('merch');
        $merch_data = model('common')->getPluginset('merch');
        if ($merch_plugin && $merch_data['is_openmerch']) {
            $is_openmerch = 1;
        } else {
            $is_openmerch = 0;
        }

        if ($st == "main") {
            $st = '';
        } else {
            $st = ".".$st;
        }

        $sendtype = !isset($_GET['sendtype']) ? 0 : $_GET['sendtype'];
        $condition = " o.ismr=0 and o.deleted=0 and o.liveid<>0 ";

        $ccard_plugin = m('ccard');
        if ($ccard_plugin) {
            $condition .= " and o.ccard=0 ";
        }

        if (empty($starttime) || empty($endtime)) {
            $starttime = strtotime('-1 month');
            $endtime = time();
        }

        $searchtime = trim($_GET['searchtime']);
        if (!empty($searchtime) && is_array($_GET['time']) && in_array($searchtime, array('create', 'pay', 'send', 'finish'))) {
            $starttime = strtotime($_GET['time']['start']);
            $endtime = strtotime($_GET['time']['end']);
            $condition .= " AND o.{$searchtime}time >= " . $starttime . " AND o.{$searchtime}time <= " . $endtime;
        }

        if ($_GET['paytype'] != '') {
            if ($_GET['paytype'] == '2') {
                $condition .= " AND ( o.paytype =21 or o.paytype=22 or o.paytype=23 )";
            } else {
                $condition .= " AND o.paytype =" . intval($_GET['paytype']);
            }
        }

        if (!empty($_GET['searchfield']) && !empty($_GET['keyword'])) {
            $searchfield = trim(strtolower($_GET['searchfield']));
            $_GET['keyword'] = trim($_GET['keyword']);
            $keyword = htmlspecialchars_decode($_GET['keyword'],ENT_QUOTES);

            $sqlcondition = '';
            if ($searchfield == 'ordersn') {
                $condition .= " AND locate(\"" . $keyword . "\",o.ordersn)>0";
            } else if ($searchfield == 'member') {
                $condition .= " AND o.mid in (select id from " . tablename('member') . " where realname like \"" . $keyword . "\" or mobile=\"" . $keyword . "\" or nickname like \"" . $keyword . "\")";
            } else if ($searchfield == 'address') {
                $condition .= " AND ( locate(\"" . $keyword . "\",a.realname)>0 or locate(\"" . $keyword . "\",a.mobile)>0 or locate(\"" . $keyword . "\",o.carrier)>0)";
            } else if ($searchfield == 'location') {
                $condition .= " AND ( locate(\"" . $keyword . "\",o.address)>0 or locate(\"" . $keyword . "\",o.address_send)>0 )";
            } else if ($searchfield == 'expresssn') {
                $condition .= " AND locate(\"" . $keyword . "\",o.expresssn)>0";
            } else if ($searchfield == 'saler') {
                $condition .= " AND verifyoperatorid in (select id from " . tablename('member') . " where realname like \"" . $keyword . "\" or mobile=\"" . $keyword . "\" or nickname like \"" . $keyword . "\")";
            } else if ($searchfield == 'store') {
                $condition .= " AND (locate(\"" . $keyword . "\",store.storename)>0)";
                $sqlcondition = " left join " . tablename('shop_store') . " store on store.id = o.verifystoreid ";
            } else if ($searchfield == 'goodstitle') {
                $sqlcondition =  " inner join ( select  og.orderid from " . tablename('shop_order_goods') . " og left join " . tablename('shop_goods') . " g on g.id=og.goodsid where (locate(\"" . $keyword . "\",g.title)>0)) gs on gs.orderid=o.id";
            } else if ($searchfield == 'goodssn') {
                $sqlcondition =  " inner join ( select og.orderid from " . tablename('shop_order_goods') . " og left join " . tablename('shop_goods') . " g on g.id=og.goodsid where (((locate(\"" . $keyword . "\",g.goodssn)>0)) or (locate(\"" . $keyword . "\",og.goodssn)>0))) gs on gs.orderid=o.id";
            }else if ($searchfield == 'merch') {
                if ($merch_plugin) {
                    $condition .= " AND (locate(\"" . $keyword . "\",merch.merchname)>0)";
                    $sqlcondition = " left join " . tablename('shop_merch') . " merch on merch.id = o.merchid ";
                }
           }
        }

        $statuscondition = '';
        // if ($status !== '') {
        //     if ($status == '-1') {
        //         $statuscondition = " AND o.status=-1 and o.refundtime=0";
        //     } else if ($status == '4') {
        //         $statuscondition = " AND o.refundstate>0 and o.refundid<>0";
        //     } else if ($status == '5') {
        //         $statuscondition = " AND o.refundtime<>0";
        //     } else if ($status=='1'){
        //         $statuscondition = " AND ( o.status = 1 or (o.status=0 and o.paytype=3) )";
        //     } else if($status=='0'){
        //         $statuscondition = " AND o.status = 0 and o.paytype<>3";
        //     } else {
        //         $statuscondition = " AND o.status = ".intval($status);
        //     }
        // }
        $agentid = intval($_GET['agentid']);
        $agentid = 0;

        $p = m('commission');
        $level = 0;
        if ($p) {
            $cset = model('commission')->getSet();
            $level = intval($cset['level']);
        }
        $olevel = intval($_GET['olevel']);
        if (!empty($agentid) && $level > 0) {
            //显示三级订单
            $agent = model('commission')->getInfo($agentid, array());
            if (!empty($agent)) {
                $agentLevel = model('commission')->getLevel($agentid);
            }
            if (empty($olevel)) {
                if ($level >= 1) {
                    $condition.=' and  ( o.agentid=' . intval($_GET['agentid']);
                }
                if ($level >= 2 && $agent['level2'] > 0) {
                    $condition.= " or o.agentid in( " . implode(',', array_keys($agent['level1_agentids'])) . ")";
                }
                if ($level >= 3 && $agent['level3'] > 0) {
                    $condition.= " or o.agentid in( " . implode(',', array_keys($agent['level2_agentids'])) . ")";
                }
                if ($level >= 1) {
                    $condition.=")";
                }
            } else {
                if ($olevel == 1) {
                    $condition.=' and  o.agentid=' . intval($_GET['agentid']);
                } else if ($olevel == 2) {
                    if ($agent['level2'] > 0) {
                        $condition.= " and o.agentid in( " . implode(',', array_keys($agent['level1_agentids'])) . ")";
                    } else {
                        $condition.= " and o.agentid in( 0 )";
                    }
                } else if ($olevel == 3) {
                    if ($agent['level3'] > 0) {
                        $condition.= " and o.agentid in( " . implode(',', array_keys($agent['level2_agentids'])) . ")";
                    } else {
                        $condition.= " and o.agentid in( 0 )";
                    }
                }
            }
        }
        if ($condition != ' o.ismr=0 and o.deleted=0 and o.isparent=0 and o.agentid>0' || !empty($sqlcondition)){
            $sql = "select o.*  from " . tablename('shop_order') . " o"
                . " " . $sqlcondition . " where " . $condition . " " . $statuscondition . " GROUP BY o.id ORDER BY o.createtime DESC  ";
            if (empty($_GET['export'])) {
                $sql.="LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            }
            $list = Db::query($sql);
            if (!empty($list)){
                $refundid = '';
                $mid = '';
                $addressid = '';
                $dispatchid = '';
                $verifyoperatorid = '';
                foreach ($list as $key=>$value){
                    $refundid .= ",'{$value['refundid']}'";
                    $mid .= ",'{$value['mid']}'";
                    $addressid .= ",'{$value['addressid']}'";
                    $dispatchid .= ",'{$value['dispatchid']}'";
                    $verifyoperatorid .= ",'{$value['verifyoperatorid']}'";
                }
                $refundid = ltrim($refundid,',');
                $mid = ltrim($mid,',');
                $addressid = ltrim($addressid,',');
                $dispatchid = ltrim($dispatchid,',');
                $verifyoperatorid = ltrim($verifyoperatorid,',');
                $refundid_array = fetchall("SELECT id,rtype,status as rstatus FROM " .tablename('shop_order_refund')." WHERE id IN ({$refundid})",'id');
                if (!empty($refundid_array)) {
                    foreach ($refundid_array as $key => &$row) {
                        if (isset($row['id'])) {
                            $refundid_array[$row['id']] = $row;
                        } else {
                            $refundid_array[] = $row;
                        }
                    }
                }
                $openid_array = fetchall("SELECT nickname,id as mid,realname as mrealname,mobile as mmobile FROM " .tablename('member')." WHERE id IN ({$mid}) ",'mid');
                if (!empty($openid_array)) {
                    foreach ($openid_array as $key => &$row) {
                        if (isset($row['mid'])) {
                            $openid_array[$row['mid']] = $row;
                        } else {
                            $openid_array[] = $row;
                        }
                    }
                }
                $addressid_array = fetchall("SELECT id,realname as arealname,mobile as amobile,province as aprovince ,city as acity , area as aarea,address as aaddress FROM " .tablename('shop_member_address')." WHERE id IN ({$addressid})",'id');
                if (!empty($addressid_array)) {
                    foreach ($addressid_array as $key => &$row) {
                        if (isset($row['id'])) {
                            $addressid_array[$row['id']] = $row;
                        } else {
                            $addressid_array[] = $row;
                        }
                    }
                }
                $dispatchid_array = fetchall("SELECT id,dispatchname FROM " .tablename('shop_dispatch')." WHERE id IN ({$dispatchid})",'id');
                if (!empty($dispatchid_array)) {
                    foreach ($dispatchid_array as $key => &$row) {
                        if (isset($row['id'])) {
                            $dispatchid_array[$row['id']] = $row;
                        } else {
                            $dispatchid_array[] = $row;
                        }
                    }
                }
                $verifyoperatorid_array = fetchall("SELECT sm.id as salerid,sm.nickname as salernickname,sm.id as mid,s.salername FROM " .tablename('shop_saler')." s LEFT JOIN ".tablename('member')." sm ON sm.id = s.mid WHERE s.mid IN ({$verifyoperatorid})",array(),'mid');
                if (!empty($verifyoperatorid_array)) {
                    foreach ($verifyoperatorid_array as $key => &$row) {
                        if (isset($row['mid'])) {
                            $verifyoperatorid_array[$row['mid']] = $row;
                        } else {
                            $verifyoperatorid_array[] = $row;
                        }
                    }
                }
                foreach ($list as $key=>&$value){
                    $list[$key]['rtype'] = $refundid_array[$value['refundid']]['rtype'];
                    $list[$key]['rstatus'] = $refundid_array[$value['refundid']]['rstatus'];
                    $list[$key]['nickname'] = $openid_array[$value['mid']]['nickname'];
                    $list[$key]['mid'] = $openid_array[$value['mid']]['mid'];
                    $list[$key]['mrealname'] = $openid_array[$value['mid']]['mrealname'];
                    $list[$key]['mmobile'] = $openid_array[$value['mid']]['mmobile'];
                    $list[$key]['arealname'] = $addressid_array[$value['addressid']]['arealname'];
                    $list[$key]['amobile'] = $addressid_array[$value['addressid']]['amobile'];
                    $list[$key]['aprovince'] = $addressid_array[$value['addressid']]['aprovince'];
                    $list[$key]['acity'] = $addressid_array[$value['addressid']]['acity'];
                    $list[$key]['aarea'] = $addressid_array[$value['addressid']]['aarea'];
                    $list[$key]['astreet'] = $addressid_array[$value['addressid']]['astreet'];
                    $list[$key]['aaddress'] = $addressid_array[$value['addressid']]['aaddress'];
                    $list[$key]['dispatchname'] = $dispatchid_array[$value['dispatchid']]['dispatchname'];
                    $list[$key]['salerid'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salerid'];
                    $list[$key]['salernickname'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salernickname'];
                    $list[$key]['salername'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salername'];
                }
                unset($value);
            }
        }else{
            $status_condition = str_replace('o.','',$statuscondition);
            $sql = "select * from " . tablename('shop_order') . " where ismr=0 and deleted=0 and isparent=0 and agentid > 0 {$status_condition} GROUP BY id ORDER BY createtime DESC  ";
            if (empty($_GET['export'])) {
                $sql.="LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            }
            $list = Db::query($sql);
            if (!empty($list)){
                $refundid = '';
                $mid = '';
                $addressid = '';
                $dispatchid = '';
                $verifyoperatorid = '';
                foreach ($list as $key=>$value){
                    $refundid .= ",'{$value['refundid']}'";
                    $mid .= ",'{$value['mid']}'";
                    $addressid .= ",'{$value['addressid']}'";
                    $dispatchid .= ",'{$value['dispatchid']}'";
                    $verifyoperatorid .= ",'{$value['verifyoperatorid']}'";
                }
                $refundid = ltrim($refundid,',');
                $mid = ltrim($mid,',');
                $addressid = ltrim($addressid,',');
                $dispatchid = ltrim($dispatchid,',');
                $verifyoperatorid = ltrim($verifyoperatorid,',');
                $refundid_array = fetchall("SELECT id,rtype,status as rstatus FROM " .tablename('shop_order_refund')." WHERE id IN ({$refundid})",'id');
                $openid_array = fetchall("SELECT id,nickname,id as mid,realname as mrealname,mobile as mmobile FROM " .tablename('member')." WHERE id IN ({$mid}) ",'mid');
                $addressid_array = fetchall("SELECT id,realname as arealname,mobile as amobile,province as aprovince ,city as acity , area as aarea,address as aaddress FROM " .tablename('shop_member_address')." WHERE id IN ({$addressid})",'id');
                $dispatchid_array = fetchall("SELECT id,dispatchname FROM " .tablename('shop_dispatch')." WHERE id IN ({$dispatchid})",array(),'id');
                $verifyoperatorid_array = fetchall("SELECT sm.id as salerid,sm.nickname as salernickname,sm.id,s.salername FROM " .tablename('shop_saler')." s LEFT JOIN ".tablename('member')." sm ON sm.id = s.mid WHERE s.mid IN ({$verifyoperatorid})",'id');
                foreach ($list as $key=>&$value){
                    $list[$key]['rtype'] = $refundid_array[$value['refundid']]['rtype'];
                    $list[$key]['rstatus'] = $refundid_array[$value['refundid']]['rstatus'];
                    $list[$key]['nickname'] = $openid_array[$value['mid']]['nickname'];
                    $list[$key]['mid'] = $openid_array[$value['mid']]['mid'];
                    $list[$key]['mrealname'] = $openid_array[$value['mid']]['mrealname'];
                    $list[$key]['mmobile'] = $openid_array[$value['mid']]['mmobile'];
                    $list[$key]['arealname'] = $addressid_array[$value['addressid']]['arealname'];
                    $list[$key]['amobile'] = $addressid_array[$value['addressid']]['amobile'];
                    $list[$key]['aprovince'] = $addressid_array[$value['addressid']]['aprovince'];
                    $list[$key]['acity'] = $addressid_array[$value['addressid']]['acity'];
                    $list[$key]['aarea'] = $addressid_array[$value['addressid']]['aarea'];
                    $list[$key]['aaddress'] = $addressid_array[$value['addressid']]['aaddress'];
                    $list[$key]['dispatchname'] = $dispatchid_array[$value['dispatchid']]['dispatchname'];
                    $list[$key]['salerid'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salerid'];
                    $list[$key]['salernickname'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salernickname'];
                    $list[$key]['salername'] = $verifyoperatorid_array[$value['verifyoperatorid']]['salername'];
                }
                unset($value);
            }
        }

        $paytype = array(
            '0' => array('css' => 'default', 'name' => '未支付'),
            '1' => array('css' => 'danger', 'name' => '余额支付'),
            '11' => array('css' => 'default', 'name' => '后台付款'),
            '2' => array('css' => 'danger', 'name' => '在线支付'),
            '21' => array('css' => 'success', 'name' => '微信支付'),
            '22' => array('css' => 'warning', 'name' => '支付宝支付'),
            '23' => array('css' => 'warning', 'name' => '银联支付'),
            '3' => array('css' => 'primary', 'name' => '货到付款'),
        );
        $orderstatus = array(
            '-1' => array('css' => 'default', 'name' => '已关闭'),
            '0' => array('css' => 'danger', 'name' => '待付款'),
            '1' => array('css' => 'info', 'name' => '待发货'),
            '2' => array('css' => 'warning', 'name' => '待收货'),
            '3' => array('css' => 'success', 'name' => '已完成')
        );
        $is_merch = array();

        $is_merchname = 0;
        if ($merch_plugin) {
            $merch_user = model('merch')->getListUser($list,'merch_user');
            if (!empty($merch_user)) {
                $is_merchname = 1;
            }
        }

        if (!empty($list)) {
            foreach ($list as &$value) {
                if ($is_merchname == 1) {
                    $value['merchname'] = $merch_user[$value['merchid']]['merchname'] ? $merch_user[$value['merchid']]['merchname'] : $_W['shopset']['shop']['name'];
                }
                $s = $value['status'];
                $pt = $value['paytype'];

                $value['statusvalue'] = $s;
                $value['statuscss'] = $orderstatus[$value['status']]['css'];
                $value['status'] = $orderstatus[$value['status']]['name'];
                if ($pt == 3 && empty($value['statusvalue'])) {
                    $value['statuscss'] = $orderstatus[1]['css'];
                    $value['status'] = $orderstatus[1]['name'];
                }
                if ($s == 1) {
                    if ($value['isverify'] == 1) {
                        $value['status'] = "待使用";
                    } else if (empty($value['addressid'])) {
                        $value['status'] = "待取货";
                    }
                }

                if ($s == -1) {
                    if (!empty($value['refundtime'])) {
                        $value['status'] = '已退款';
                    }
                }

                $value['paytypevalue'] = $pt;
                $value['css'] = $paytype[$pt]['css'];
                $value['paytype'] = $paytype[$pt]['name'];
                $value['dispatchname'] = empty($value['addressid']) ? '自提' : $value['dispatchname'];
                if (empty($value['dispatchname'])) {
                    $value['dispatchname'] = '快递';
                }
                if ($pt == 3) {
                    $value['dispatchname'] = "货到付款";
                } else if ($value['isverify'] == 1) {
                    $value['dispatchname'] = "线下核销";
                } else if ($value['isvirtual'] == 1) {
                    $value['dispatchname'] = "虚拟物品";
                } else if (!empty($value['virtual'])) {
                    $value['dispatchname'] = "虚拟物品(卡密)<br/>自动发货";
                }

                if ($value['dispatchtype'] == 1 || !empty($value['isverify']) || !empty($value['virtual']) || !empty($value['isvirtual'])) {
                    $value['address'] = '';
                    $carrier = iunserializer($value['carrier']);
                    if (is_array($carrier)) {
                        $value['addressdata']['realname'] = $value['realname'] = $carrier['carrier_realname'];
                        $value['addressdata']['mobile'] = $value['mobile'] = $carrier['carrier_mobile'];
                    }
                } else {
                    $address = iunserializer($value['address']);
                    $isarray = is_array($address);
                    $value['realname'] = $isarray ? $address['realname'] : $value['arealname'];
                    $value['mobile'] = $isarray ? $address['mobile'] : $value['amobile'];
                    $value['province'] = $isarray ? $address['province'] : $value['aprovince'];
                    $value['city'] = $isarray ? $address['city'] : $value['acity'];
                    $value['area'] = $isarray ? $address['area'] : $value['aarea'];
                    $value['address'] = $isarray ? $address['address'] : $value['aaddress'];

                    $value['address_province'] = $value['province'];
                    $value['address_city'] = $value['city'];
                    $value['address_area'] = $value['area'];
                    $value['address_address'] = $value['address'];

                    $value['address'] = $value['province'] . " " . $value['city'] . " " . $value['area'] . " " . $value['address'];
                    $value['addressdata'] = array(
                        'realname' => $value['realname'],
                        'mobile' => $value['mobile'],
                        'address' => $value['address'],
                    );
                }
                $commission1 = -1;
                $commission2 = -1;
                $commission3 = -1;
                $m1 = false;
                $m2 = false;
                $m3 = false;
                if (!empty($level) && empty($agentid)) {

                    if (!empty($value['agentid'])) {
                        $m1 = model('member')->getMember($value['agentid']);
                        $commission1 = 0;
                        if (!empty($m1['agentid'])) {
                            $m2 = model('member')->getMember($m1['agentid']);
                            $commission2 = 0;
                            if (!empty($m2['agentid'])) {
                                $m3 = model('member')->getMember($m2['agentid']);
                                $commission3 = 0;
                            }
                        }
                    }
                }

                if (!empty($agentid)) {
                    $magent = model('member')->getMember($agentid);
                }

                //订单商品
                $order_goods = Db::query('select g.id,g.title,g.thumb,g.goodssn,og.goodssn as option_goodssn, g.productsn,og.productsn as option_productsn, og.total,og.price,og.optionname as optiontitle, og.realprice,og.changeprice,og.oldprice,og.commission1,og.commission2,og.commission3,og.commissions,op.specs,g.merchid,og.nocommission from ' . tablename('shop_order_goods') . ' og '
                    . ' left join ' . tablename('shop_goods') . ' g on g.id=og.goodsid '
                    . ' left join ' . tablename('shop_goods_option') . ' op on og.optionid = op.id '
                    . ' where og.orderid= ' . $value['id']);
                $goods = '';
                foreach ($order_goods as &$og) {
                    //读取规格的图片
                    if (!empty($og['specs'])) {
                        $thumb = model('goods')->getSpecThumb($og['specs']);
                        if (!empty($thumb)) {
                            $og['thumb'] = $thumb;
                        }
                    }
                    if (!empty($level) && empty($agentid) && empty($og['nocommission'])) {
                        $commissions = iunserializer($og['commissions']);
                        if (!empty($m1)) {
                            $value['m1'] = $m1;
                            if (is_array($commissions)) {
                                $commission1+= isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
                            } else {
                                $c1 = iunserializer($og['commission1']);
                                $l1 = model('commission')->getLevel($m1['id']);
                                $commission1+= isset($c1['level' . $l1['id']]) ? $c1['level' . $l1['id']] : $c1['default'];
                            }
                        }
                        if (!empty($m2)) {
                            if (is_array($commissions)) {
                                $commission2+= isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
                            } else {
                                $c2 = iunserializer($og['commission2']);
                                $l2 = model('commission')->getLevel($m2['id']);
                                $commission2+= isset($c2['level' . $l2['id']]) ? $c2['level' . $l2['id']] : $c2['default'];
                            }
                        }
                        if (!empty($m3)) {
                            if (is_array($commissions)) {
                                $commission3+= isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
                            } else {
                                $c3 = iunserializer($og['commission3']);
                                $l3 = model('commission')->getLevel($m3['id']);
                                $commission3+= isset($c3['level' . $l3['id']]) ? $c3['level' . $l3['id']] : $c3['default'];
                            }
                        }
                    }
                    $goods.="" . $og['title'] . "\r\n";

                    if (!empty($og['optiontitle'])) {
                        $goods.=" 规格: " . $og['optiontitle'];
                    }
                    if (!empty($og['option_goodssn'])) {
                        $og['goodssn'] = $og['option_goodssn'];
                    }
                    if (!empty($og['option_productsn'])) {
                        $og['productsn'] = $og['option_productsn'];
                    }

                    if (!empty($og['goodssn'])) {
                        $goods.=' 商品编号: ' . $og['goodssn'];
                    }
                    if (!empty($og['productsn'])) {
                        $goods.=' 商品条码: ' . $og['productsn'];
                    }
                    $goods.=' 单价: ' . ($og['price'] / $og['total']) . ' 折扣后: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['price'] . " 折扣后: " . $og['realprice'] . " ";
                }
                unset($og);
                if (!empty($level) && empty($agentid)) {
                    $value['commission1'] = $commission1;
                    $value['commission2'] = $commission2;
                    $value['commission3'] = $commission3;
                }
                $value['goods'] = set_medias($order_goods, 'thumb');
                $value['goods_str'] = $goods;


                if (!empty($agentid) && $level > 0) {
                    //计算几级订单
                    $commission_level = 0;
                    if ($value['agentid'] == $agentid) {
                        $value['level'] = 1;
                        $level1_commissions = Db::query('select commission1,commissions  from ' . tablename('shop_order_goods') . ' og '
                            . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid '
                            . ' where og.orderid=' . $value['id'] . ' and o.agentid= ' . $agentid . " ");
                        foreach ($level1_commissions as $c) {
                            $commission = iunserializer($c['commission1']);
                            $commissions = iunserializer($c['commissions']);
                            if (empty($commissions)) {
                                $commission_level+= isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                            } else {
                                $commission_level+= isset($commissions['level1']) ? floatval($commissions['level1']) : 0;
                            }
                        }
                    } else if (in_array($value['agentid'], array_keys($agent['level1_agentids']))) {
                        $value['level'] = 2;
                        if ($agent['level2'] > 0) {
                            $level2_commissions = Db::query('select commission2,commissions  from ' . tablename('shop_order_goods') . ' og '
                                . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid '
                                . ' where og.orderid=' . $value['id'] . ' and  o.agentid in ( ' . implode(',', array_keys($agent['level1_agentids'])) . ")");
                            foreach ($level2_commissions as $c) {
                                $commission = iunserializer($c['commission2']);
                                $commissions = iunserializer($c['commissions']);
                                if (empty($commissions)) {
                                    $commission_level+= isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                                } else {
                                    $commission_level+= isset($commissions['level2']) ? floatval($commissions['level2']) : 0;
                                }
                            }
                        }
                    } else if (in_array($value['agentid'], array_keys($agent['level2_agentids']))) {
                        $value['level'] = 3;
                        if ($agent['level3'] > 0) {
                            $level3_commissions = Db::query('select commission3,commissions from ' . tablename('shop_order_goods') . ' og '
                                . ' left join  ' . tablename('shop_order') . ' o on o.id = og.orderid '
                                . ' where og.orderid=' . $value['id'] . ' and  o.agentid in ( ' . implode(',', array_keys($agent['level2_agentids'])) . ")");
                            foreach ($level3_commissions as $c) {
                                $commission = iunserializer($c['commission3']);
                                $commissions = iunserializer($c['commissions']);
                                if (empty($commissions)) {
                                    $commission_level+= isset($commission['level' . $agentLevel['id']]) ? $commission['level' . $agentLevel['id']] : $commission['default'];
                                } else {
                                    $commission_level+= isset($commissions['level3']) ? floatval($commissions['level3']) : 0;
                                }
                            }
                        }
                    }
                    $value['commission'] = $commission_level;
                }
            }
        }
        unset($value);
        //导出Excel

        if ($_GET['export'] == 1) {
            model('shop')->plog('order.op.export', "导出订单");
            $columns = array(
                array('title' => '订单编号', 'field' => 'ordersn', 'width' => 24),
                array('title' => '分销商id', 'field' => 'agent_id', 'width' => 12),
                array('title' => '分销商昵称', 'field' => 'agent_nickname', 'width' => 12),
                array('title' => '分销商姓名', 'field' => 'agent_realname', 'width' => 12),
                array('title' => '分销商手机号', 'field' => 'agent_mobile', 'width' => 12),
                array('title' => '粉丝昵称', 'field' => 'nickname', 'width' => 12),
                array('title' => '会员姓名', 'field' => 'mrealname', 'width' => 12),
                array('title' => 'mid', 'field' => 'mid', 'width' => 24),
                array('title' => '会员手机手机号', 'field' => 'mmobile', 'width' => 12),
                array('title' => '收货姓名(或自提人)', 'field' => 'realname', 'width' => 12),
                array('title' => '联系电话', 'field' => 'mobile', 'width' => 12),
                array('title' => '收货地址', 'field' => 'address_province', 'width' => 12),
                array('title' => '', 'field' => 'address_city', 'width' => 12),
                array('title' => '', 'field' => 'address_area', 'width' => 12),
                array('title' => '', 'field' => 'address_address', 'width' => 12),
                array('title' => '商品名称', 'field' => 'goods_title', 'width' => 24),
                array('title' => '商品编码', 'field' => 'goods_goodssn', 'width' => 12),
                array('title' => '商品规格', 'field' => 'goods_optiontitle', 'width' => 12),
                array('title' => '商品数量', 'field' => 'goods_total', 'width' => 12),
                array('title' => '商品单价(折扣前)', 'field' => 'goods_price1', 'width' => 12),
                array('title' => '商品单价(折扣后)', 'field' => 'goods_price2', 'width' => 12),
                array('title' => '商品价格(折扣后)', 'field' => 'goods_rprice1', 'width' => 12),
                array('title' => '商品价格(折扣后)', 'field' => 'goods_rprice2', 'width' => 12),
                array('title' => '支付方式', 'field' => 'paytype', 'width' => 12),
                array('title' => '配送方式', 'field' => 'dispatchname', 'width' => 12),
                array('title' => '商品小计', 'field' => 'goodsprice', 'width' => 12),
                array('title' => '运费', 'field' => 'dispatchprice', 'width' => 12),
                array('title' => '积分抵扣', 'field' => 'deductprice', 'width' => 12),
                array('title' => '余额抵扣', 'field' => 'deductcredit2', 'width' => 12),
                array('title' => '满额立减', 'field' => 'deductenough', 'width' => 12),
                array('title' => '优惠券优惠', 'field' => 'couponprice', 'width' => 12),
                array('title' => '订单改价', 'field' => 'changeprice', 'width' => 12),
                array('title' => '运费改价', 'field' => 'changedispatchprice', 'width' => 12),
                array('title' => '应收款', 'field' => 'price', 'width' => 12),
                array('title' => '状态', 'field' => 'status', 'width' => 12),
                array('title' => '下单时间', 'field' => 'createtime', 'width' => 24),
                array('title' => '付款时间', 'field' => 'paytime', 'width' => 24),
                array('title' => '发货时间', 'field' => 'sendtime', 'width' => 24),
                array('title' => '完成时间', 'field' => 'finishtime', 'width' => 24),
                array('title' => '快递公司', 'field' => 'expresscom', 'width' => 24),
                array('title' => '快递单号', 'field' => 'expresssn', 'width' => 24),
                array('title' => '订单备注', 'field' => 'remark', 'width' => 36),
                array('title' => '核销员', 'field' => 'salerinfo', 'width' => 24),
                array('title' => '核销门店', 'field' => 'storeinfo', 'width' => 36)
            );
            if (!empty($agentid) && $level > 0) {
                $columns[] = array('title' => '分销级别', 'field' => 'level', 'width' => 24);
                $columns[] = array('title' => '分销佣金', 'field' => 'commission', 'width' => 24);
            }
            foreach ($list as &$row) {
                $row['ordersn'] = $row['ordersn'] . " ";
                if ($row['deductprice'] > 0) {
                    $row['deductprice'] = "-" . $row['deductprice'];
                }
                if ($row['deductcredit2'] > 0) {
                    $row['deductcredit2'] = "-" . $row['deductcredit2'];
                }
                if ($row['deductenough'] > 0) {
                    $row['deductenough'] = "-" . $row['deductenough'];
                }
                if ($row['changeprice'] < 0) {
                    $row['changeprice'] = "-" . $row['changeprice'];
                } else if ($row['changeprice'] > 0) {
                    $row['changeprice'] = "+" . $row['changeprice'];
                }
                if ($row['changedispatchprice'] < 0) {
                    $row['changedispatchprice'] = "-" . $row['changedispatchprice'];
                } else if ($row['changedispatchprice'] > 0) {
                    $row['changedispatchprice'] = "+" . $row['changedispatchprice'];
                }
                if ($row['couponprice'] > 0) {
                    $row['couponprice'] = "-" . $row['couponprice'];
                }
                $row['nickname'] = strexists($row['nickname'],'^') ? "'".$row['nickname'] : $row['nickname'];
                $row['expresssn'] = $row['expresssn'] . " ";
                $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
                $row['paytime'] = !empty($row['paytime']) ? date('Y-m-d H:i:s', $row['paytime']) : '';
                $row['sendtime'] = !empty($row['sendtime']) ? date('Y-m-d H:i:s', $row['sendtime']) : '';
                $row['finishtime'] = !empty($row['finishtime']) ? date('Y-m-d H:i:s', $row['finishtime']) : '';
                $row['salerinfo'] = "";
                $row['storeinfo'] = "";
                if (!empty($row['verifyoperatorid'])) {
                    $row['salerinfo'] = "[" . $row['salerid'] . "]" . $row['salername'] . "(" . $row['salernickname'] . ")";
                }
                if (!empty($row['verifystoreid'])) {
                    $row['storeinfo'] = fetchcolumn('select storename from ' . tablename('shop_store') . ' where id=:storeid limit 1 ', array(':storeid' => $row['verifystoreid']));
                }
                $row['agent_id'] = $row['m1']['id'];
                // $row['agent_nickname'] = $row['m1']['nickname'];
                $row['agent_nickname'] =str_replace('=', "", $row['m1']['nickname']);
                //$row['agent_realname'] = $row['m1']['realname'];
                $row['agent_realname'] = str_replace('=', "", $row['m1']['realname']);
                $row['agent_mobile'] = $row['m1']['mobile'];
            }
            unset($row);
            $exportlist = array();
            foreach ($list as &$r) {
                $ogoods = $r['goods'];
                unset($r['goods']);
                foreach ($ogoods as $k => $g) {
                    if ($k > 0) {
                        $r['ordersn'] = '';
                        $r['realname'] = '';
                        $r['mobile'] = '';
                        $r['mid'] = '';
                        $r['nickname'] = '';
                        $r['mrealname'] = '';
                        $r['mmobile'] = '';
                        $r['address'] = '';
                        $r['address_province'] = '';
                        $r['address_city'] = '';
                        $r['address_area'] = '';
                        $r['address_address'] = '';
                        $r['paytype'] = '';
                        $r['dispatchname'] = '';
                        $r['dispatchprice'] = '';
                        $r['goodsprice'] = '';
                        $r['status'] = '';
                        $r['createtime'] = '';
                        $r['sendtime'] = '';
                        $r['finishtime'] = '';
                        $r['expresscom'] = '';
                        $r['expresssn'] = '';
                        $r['deductprice'] = '';
                        $r['deductcredit2'] = '';
                        $r['deductenough'] = '';
                        $r['changeprice'] = '';
                        $r['changedispatchprice'] = '';
                        $r['price'] = '';
                    }
                    $r['goods_title'] = $g['title'];
                    $r['goods_goodssn'] = $g['goodssn'];
                    $r['goods_optiontitle'] = $g['optiontitle'];
                    $r['goods_total'] = $g['total'];
                    $r['goods_price1'] = $g['price'] / $g['total'];
                    $r['goods_price2'] = $g['realprice'] / $g['total'];
                    $r['goods_rprice1'] = $g['price'];
                    $r['goods_rprice2'] = $g['realprice'];
                    $exportlist[] = $r;
                }
            }
            unset($r);
            model('excel')->export($exportlist, array(
                "title" => "订单数据-" . date('Y-m-d-H-i', time()),
                "columns" => $columns
            ));
        }
        if ($condition != ' o.ismr=0 and o.deleted=0 and o.isparent=0' || !empty($sqlcondition)){
            $t = Db::name('shop_order')->alias('o')
                ->join('shop_order_refund r','r.id =o.refundid','left')
                ->join('member m','m.id=o.mid','left')
                ->join('shop_member_address a','o.addressid = a.id','left')
                ->join('shop_saler s','s.mid = o.verifyoperatorid','left')
                ->join('member sm','sm.id = s.mid','left')
                ->field('COUNT(*) as count, ifnull(sum(o.price),0) as sumprice')
                ->find();
        }else{
            $t = Db::name('shop_order')->where('ismr=0 and deleted=0 and isparent=0 {$status_condition}')->field('COUNT(*) as count, ifnull(sum(price),0)')->find();
        }


        $total = $t['count'];
        $totalmoney = $t['sumprice'];
        $pager = pagination2($total, $pindex, $psize);
        $stores = Db::query('select id,storename from ' . tablename('shop_store') . ' where 1');
        $r_type = array( '0' => '退款', '1' => '退货退款', '2' => '换货');
        $this->assign(['paytype'=>$paytype,'totalmoney'=>$totalmoney,'magent'=>$magent,'agentid'=>$agentid,'refund'=>$refund,'magent'=>$magent,'list'=>$list,'pager'=>$pager,'searchfield'=>$_GET['searchfield'],'r_type'=>$r_type,'starttime'=>$starttime,'endtime'=>$endtime,'merch_plugin'=>$merch_plugin,'is_openmerch'=>$is_openmerch,'keyword'=>$keyword,'searchtime'=>$searchtime,'paytype'=>$_GET['paytype'],'act'=>strtolower(Request::instance()->action())]);
        return $this->fetch('livemall/statistics/order');
    }

    public function set()
    {
        $data = model('common')->getPluginset('livemall');
        if (Request::instance()->isPost()) {
            $data = (is_array($_POST['data']) ? $_POST['data'] : array());

            $data['goodsid'] = !empty($_POST['goodsid']) ? implode(',', $_POST['goodsid']) : 0;
            $data['livemall_description'] = model('common')->html_images($_POST['data']['livemall_description']);
            $data['rules'] = model('common')->html_images($_POST['data']['rules']);
            model('common')->updatePluginset(array('livemall' => $data));
            model('shop')->plog('livemall.set.edit', '修改星店基本设置');
            show_json(1, array('url' => url('admin/livemall/set', array('tab' => str_replace('#tab_', '', $_GET['tab'])))));
        }
        if ($data['goodsid']) {
            $goods = Db::name('shop_groups_goods')->where('id','in',$data['goodsid'])->field('id,title,thumb')->select();
        }
        $this->assign(['data'=>$data,'goods'=>$goods]);
        return $this->fetch('livemall/set');
    }

}
