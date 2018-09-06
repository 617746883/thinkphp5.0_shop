<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
class Webview extends Controller
{
	public function communitynotice()
	{
		$id = input('id/d',1);
		$data = Db::name('community_notice')->where('id',$id)->find();
		if(empty($data))
		{
			$data = array('title'=>'新闻公告','content'=>'您访问的信息不存在');
		}
		$this->assign('data',$data);
		return $this->fetch('');
	}

	public function citywidestore()
	{
		$id = input('id/d',1);
		$data = Db::name('citywide_life_store')->where('id',$id)->find();
		if(empty($data))
		{
			$data = array('storename'=>'附近门店','desc'=>'您访问的信息不存在');
		}
		$this->assign('data',$data);
		return $this->fetch('');
	}

	public function goodsdetail()
	{
		$id = input('id/d',1);
		$data = Db::name('shop_goods')->where('id',$id)->find();
		if(empty($data))
		{
			$data = array('title'=>'商品详情','content'=>'您访问的信息不存在');
		}
		$data['content'] = lazy($data['content']);
		$params = Db::name('shop_goods_param')->where('goodsid',$data['id'])->order('displayorder','asc')->select();
		$this->assign(['data'=>$data,'params'=>$params]);
		return $this->fetch('');
	}

	public function shopnotice()
	{
		$id = input('id/d',1);
		$data = Db::name('shop_notice')->where('id',$id)->find();
		if(empty($data))
		{
			$data = array('title'=>'商城公告','detail'=>'您访问的信息不存在');
		}
		$data['detail'] = lazy($data['detail']);
		$this->assign('data',$data);
		return $this->fetch('');
	}

	public function memberexplain()
	{
		$data = model('common')->getSysset('member');
		if (!(isset($data['levelname']))) {
			$shop = model('common')->getSysset('shop');
			$data['levelname'] = $shop['levelname'];
			$data['levelurl'] = $shop['levelurl'];
			$data['leveltype'] = $shop['leveltype'];
			$data['explain'] = $shop['memberexplain'];
		}
		if(!empty($data['levelurl'])) {
			$this->redirect($data['levelurl']);
		}
		$data['title'] = '会员说明';
		$this->assign(['data'=>$data]);
		return $this->fetch('');
	}

	public function article()
	{
		$id = input('aid/d',1);
		$article = Db::name('shop_article')->where('id',$id)->find();
		if(empty($article))
		{
			$article = array('article_title'=>'您访问的信息不存在','article_content'=>'您访问的信息不存在');
		}
		$article['article_content'] = lazy($article['article_content']);
		$readnum = intval($article['article_readnum'] + $article['article_readnum_v']);
		$readnum = (100000 < $readnum ? '100000+' : $readnum);
		$likenum = intval($article['article_likenum'] + $article['article_likenum_v']);
		$likenum = (100000 < $likenum ? '100000+' : $likenum);
		$this->assign(['article'=>$article,'readnum'=>$readnum,'likenum'=>$likenum]);
		return $this->fetch('');
	}

}