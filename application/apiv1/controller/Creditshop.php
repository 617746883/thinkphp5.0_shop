<?php
/**
 * apiv1 购物车
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\apiv1\controller;
use think\Db;
use think\Request;
class Creditshop extends Base
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
        $set = model('Common')->getPluginset('creditshop');
        if(!empty($set) && !empty($set['opencreditshop']))
        {
        	$this->result(0, '积分商城未开启','');
        }
        $this->set = $set;
    }

    /**
	 * 积分商城首页
	 * @param 
	 * @return  [array]    $list  []
	 **/
    public function index()
    {    	
		$page = input('page/d',1);
		$pagesize = input('pagesize/d',10);
    	$merchid = intval(input('merchid'));
    	$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}
		if($page <= 1) {
			$condition = ' 1 ';
			if (0 < intval($merchid) && $is_openmerch == 1) {
				$contation .= 'and merchid = ' . intval($merchid) . ' ';
			}
	    	$banner = Db::name('shop_creditshop_banner')->where($condition . ' and enabled=1 ')->order('displayorder','desc')->field('id,bannername,link,thumb')->select();
	    	if(!empty($banner)) {
	    		$banner = set_medias($banner,'thumb');
	    	}
	    	$category = array();

			if (0 < intval($merchid) && $is_openmerch == 1) {
				$merch_category = model('store')->getSet('merch_creditshop_category', $merchid);

				if (!empty($merch_category)) {
					$i = 0;
					foreach ($merch_category as $index => $row) {
						if (0 < $row) {
							$list = Db::name('shop_creditshop_goods_category')->where('id = ' . $index . ' enabled=1')->field('id,name,thumb,isrecommand')->order('displayorder','desc')->select();
							$list = set_medias($list, 'thumb');
							$category[$i] = $list;
							++$i;
						}
					}
				}
			} else {
				$category = Db::name('shop_creditshop_goods_category')->where('enabled=1')->field('id,name,thumb,isrecommand')->order('displayorder','desc')->select();
				$category = set_medias($category, 'thumb');
			}
			array_values($category);
		}		

		$cate = input('cate/d');
		$order_key = input('order_key','');
        $order_method = input('order_method','');
		$goodscondition = 'isrecommand = 1 and status=1 and deleted=0';
		if (!empty($cate)) {
            $goodscondition .= 'cate = ' . $cate;
        }
        $keywords = trim(input('keywords'));
		if (!empty($keywords)) {
			$goodscondition .= ' AND title like \'%' . $keywords . '%\' ';
		}
		if (0 < $merchid && $is_openmerch == 1) {
			$goodscondition .= ' and merchid = ' . $merchid . ' ';
		}
        if (!empty($order_key) && !empty($order_method)) {
            $sort = $order_key. " " .$order_method;           
        } else {
            $sort = "displayorder desc";
        }
    	$goodslist = Db::name('shop_creditshop_goods')->where($goodscondition)->order($sort)->field("id,title,thumb,price,credit,total,money,type")->page($page,$pagesize)->select();
    	if(!empty($goodslist)) {
    		$goodslist = set_medias($goodslist,'thumb');
    	}
    	if($page <= 1) {
    		$this->result(1,'success',array('banner'=>$banner, 'category'=>$category, 'goods'=>array('list'=>$goodslist,'page'=>$page,'pagesize'=>$pagesize)));
    	}
    	$this->result(1,'success',array('goods'=>array('list'=>$goodslist,'page'=>$page,'pagesize'=>$pagesize)));
    }

    /**
	 * 积分商城-商品详情
	 * @param [int] $id
	 * @return  [array]    $list  []
	 **/
    public function goodsdetail()
    {
    	$mid = $this->getMemberId();
		$id = intval(input('id'));
		$merch_data = model('common')->getPluginset('store');
		if ($merch_data['is_openmerch']) {
			$is_openmerch = 1;
		} else {
			$is_openmerch = 0;
		}

		$merchid = intval(input('merchid'));
		if (!$id) {
			$this->result(0,'该商品不存在或已删除!');
		}

		$shop = model('common')->getSysset('shop');
		$member = model('member')->getMember($mid);
		$goods = model('creditshop')->getGoods($id, $member);

		if (empty($goods)) {
			$this->result(0,'该商品不存在或已删除!');
		}

		$showgoods = model('goods')->visit($goods, $member);

		if (empty($showgoods)) {
			$this->result(0,'您没有权限浏览此商品!');
		}

		$pay = model('common')->getSysset('pay');
		$set = $this->set;
		$goods['subdetail'] = lazy($goods['subdetail']);
		$goods['noticedetail'] = lazy($goods['noticedetail']);
		$goods['usedetail'] = lazy($goods['usedetail']);
		$goods['goodsdetail'] = lazy($goods['goodsdetail']);
		$credit = $member['credit1'];
		$money = $member['credit2'];

		if (!empty($goods)) {
			Db::name('shop_creditshop_goods')->where('id',$id)->setInc('views');
		} else {
			$this->result(0,'商品已下架或被删除!');
		}

		$log = array();
		$log = Db::name('shop_creditshop_log')->where('goodsid = ' . $id . ' and status > 0 ')->order('createtime','desc')->field('mid,createtime')->limit(2)->select();

		foreach ($log as $key => $value) {
			$mem = model('member')->getMember($value['mid']);
			$log[$key]['avatar'] = $mem['avatar'];
			$log[$key]['nickname'] = $mem['nickname'];
			$log[$key]['createtime_str'] = date('Y/m/d H:i', $value['createtime']);
			unset($mem);
		}

		$logtotal = 0;
		$logtotal = Db::name('shop_creditshop_log')->where('goodsid=' . $id . ' and status > 0 ')->count();
		$replys = array();
		$replys = Db::name('shop_creditshop_comment')->where('goodsid = ' . $id . ' and checked = 1 and deleted = 0')->order('time','desc')->limit(2)->select();
		$replykeywords = explode(',', $set['desckeyword']);
		$replykeystr = trim($set['replykeyword']);

		if (empty($replykeystr)) {
			$replykeystr = '**';
		}

		foreach ($replys as $key => $value) {
			foreach ($replykeywords as $k => $val) {
				if (!empty($value['content'])) {
					if (!strstr($val, $value['content'])) {
						$value['content'] = str_replace($val, $replykeystr, $value['content']);
					}
				}

				if (!empty($value['reply_content'])) {
					if (!strstr($val, $value['reply_content'])) {
						$value['reply_content'] = str_replace($val, $replykeystr, $value['reply_content']);
					}
				}

				if (!empty($value['append_content'])) {
					if (!strstr($val, $value['append_content'])) {
						$value['append_content'] = str_replace($val, $replykeystr, $value['append_content']);
					}
				}

				if (!empty($value['append_reply_content'])) {
					if (!strstr($val, $value['append_reply_content'])) {
						$value['append_reply_content'] = str_replace($val, $replykeystr, $value['append_reply_content']);
					}
				}
			}

			$replys[$key]['content'] = $value['content'];
			$replys[$key]['reply_content'] = $value['reply_content'];
			$replys[$key]['append_content'] = $value['append_content'];
			$replys[$key]['append_reply_content'] = $value['append_reply_content'];
			$replys[$key]['time_str'] = date('Y/m/d', $value['time']);
			$replys[$key]['images'] = set_medias(iunserializer($value['images']));
			$replys[$key]['reply_images'] = set_medias(iunserializer($value['reply_images']));
			$replys[$key]['append_images'] = set_medias(iunserializer($value['append_images']));
			$replys[$key]['append_reply_images'] = set_medias(iunserializer($value['append_reply_images']));
			$replys[$key]['nickname'] = cut_str($value['nickname'], 1, 0) . '**' . cut_str($value['nickname'], 1, -1);
			$replys[$key]['content'] = str_replace('=', '**', $value['content']);
		}

		$replytotal = 0;
		$replytotal = Db::name('shop_creditshop_comment')->where('goodsid = ' . $id . ' and checked = 1 and deleted = 0')->order('time','desc')->count();

		if ($goods['goodstype'] == 0) {
			$stores = array();

			if (!empty($goods['isverify'])) {
				$storeids = array();

				if (!empty($goods['storeids'])) {
					$storeids = array_merge(explode(',', $goods['storeids']), $storeids);
				}

				if (empty($storeids)) {
					if (0 < $merchid) {
						$stores = pdo_fetchall('select * from ' . tablename('shop_merch_store') . ' where  uniacid=:uniacid and merchid=:merchid and status=1 and type in(2,3)', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
					} else {
						$stores = pdo_fetchall('select * from ' . tablename('shop_store') . ' where  uniacid=:uniacid and status=1 and type in(2,3)', array(':uniacid' => $_W['uniacid']));
					}
				} else if (0 < $merchid) {
					$stores = pdo_fetchall('select * from ' . tablename('shop_merch_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and merchid=:merchid and status=1 and type in(2,3)', array(':uniacid' => $_W['uniacid'], ':merchid' => $merchid));
				} else {
					$stores = pdo_fetchall('select * from ' . tablename('shop_store') . ' where id in (' . implode(',', $storeids) . ') and uniacid=:uniacid and status=1 and type in(2,3)', array(':uniacid' => $_W['uniacid']));
				}
			}
		}

		$goodsrec = Db::name('shop_creditshop_goods')->where('goodstype = ' . $goods['goodstype'] . ' and `type` = ' . $goods['type'] . ' and status = 1 and deleted = 0')->field('id,thumb,title,credit,money,mincredit,minmoney')->orderRaw('rand()')->limit(3)->select();

		foreach ($goodsrec as $key => $value) {
			$goodsrec[$key]['credit'] = intval($value['credit']);

			if ((intval($value['money']) - $value['money']) == 0) {
				$goodsrec[$key]['money'] = intval($value['money']);
			}

			$goodsrec[$key]['mincredit'] = intval($value['mincredit']);

			if ((intval($value['minmoney']) - $value['minmoney']) == 0) {
				$goodsrec[$key]['minmoney'] = intval($value['minmoney']);
			}
		}
		$this->result(1,'success',array('goods' => $goods));
    }

}