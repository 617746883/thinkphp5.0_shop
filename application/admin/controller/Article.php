<?php
/**
 * 文章营销
 *
 * @author SUL1SS <617746883@QQ.com>
 */
namespace app\admin\controller;
use think\Request;
use think\Db;
class Article extends Base
{
	public function index()
	{
		$select_category = (empty($_GET['category']) ? '' : ' and a.article_category=' . intval($_GET['category']) . ' ');
		$select_title = (empty($_GET['keyword']) ? '' : ' and a.article_title LIKE \'%' . $_GET['keyword'] . '%\' ');
		$psize = 20;
		$articles = array();
		$articles = Db::name('shop_article')->alias('a')->join('shop_article_category c','c.id=a.article_category','left')->field('a.id,a.displayorder, a.article_title,a.article_category,a.article_date,a.article_readnum,a.article_likenum,a.article_state,c.category_name')->where(' 1 ' . $select_title . $select_category)->order('a.displayorder','desc')->paginate($psize);
		$pager = $articles->render();

		if (!empty($articles)) {
			foreach ($articles as $k => $value) {
				$value['url'] = getHttpHost()  . url('index/webview/article',array('aid'=>$value['id']), true);
				$data = array();
	    		$data = $value;
	    		$articles->offsetSet($k,$data);
			}
			unset($value);
		}

		$articlenum = Db::name('shop_article')->count();
		$categorys = Db::name('shop_article_category')->select();
		$this->assign(['articles'=>$articles,'pager'=>$pager,'articlenum'=>$articlenum,'categorys'=>$categorys]);
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
		$aid = intval(input('aid'));
		$article = Db::name('shop_article')->where('id',$aid)->find();

		if (Request::instance()->isPost()) {
			$data = array('article_title' => trim(input('article_title')), 'article_category' => intval(input('article_category')), 'resp_desc' => trim(input('resp_desc')), 'resp_img' => trim(input('resp_img')), 'article_mp' => trim(input('article_mp')), 'article_author' => trim(input('article_author')), 'article_readnum_v' => intval(input('article_readnum_v')), 'article_likenum_v' => trim(input('article_likenum_v')), 'page_set_option_nocopy' => intval(input('page_set_option_nocopy')), 'page_set_option_noshare_tl' => intval(input('page_set_option_noshare_tl')), 'page_set_option_noshare_msg' => intval(input('page_set_option_noshare_msg')), 'article_state' => intval(input('article_state')), 'article_content' => model('common')->html_images($_POST['editor'], true));

			if (empty($article)) {
				$data['article_date'] = date('Y-m-d H:i:s');
				$aid = Db::name('shop_article')->insertGetId($data);
				model('shop')->plog('article.add', '添加文章 ID: ' . $aid . ' 标题: ' . $data['article_title']);
			} else {
				Db::name('shop_article')->where('id',$article['id'])->update($data);
				model('shop')->plog('article.edit', '编辑文章 ID: ' . $aid . ' 标题: ' . $data['article_title']);
			}
			show_json(1, array('url' => url('admin/article/edit', array('aid' => $aid, 'tab' => str_replace('#tab_', '', $_GET['tab'])))));
		}

		$categorys = Db::name('shop_article_category')->select();
		$this->assign(['categorys'=>$categorys,'article'=>$article,'aid'=>$aid,'no_left'=>1]);
		return $this->fetch('article/post');
	}

	public function delete()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}

		$items = Db::name('shop_article')->where('id','in',$id)->field('id,article_title')->select();

		foreach ($items as $item) {
			Db::name('shop_article')->where('id',$item['id'])->delete();			
			model('shop')->plog('article.delete', '删除文章 ID: ' . $item['id'] . ' 标题: ' . $item['article_title'] . ' ');
		}

		show_json(1, array('url' => referer()));
	}

	public function displayorder()
	{
		$id = intval(input('id'));
		$displayorder = intval(input('value'));
		$item = Db::name('shop_article')->where('id',$id)->field('id,article_title')->find();

		if (!empty($item)) {
			Db::name('shop_article')->where('id',$item['id'])->setField('displayorder',$displayorder);			
			model('shop')->plog('article.edit', '修改文章排序 ID: ' . $item['id'] . ' 标题: ' . $item['article_title'] . ' 排序: ' . $displayorder . ' ');
		}

		show_json(1);
	}

	public function state()
	{
		$id = intval(input('id'));

		if (empty($id)) {
			$id = (is_array($_POST['ids']) ? implode(',', $_POST['ids']) : 0);
		}
		$state = input('state');
		$items = Db::name('shop_article')->where('id','in',$id)->field('id,article_title')->select();

		foreach ($items as $item) {
			Db::name('shop_article')->where('id',$item['id'])->setField('article_state',$state);			
			model('shop')->plog('article.edit', ('修改文章状态<br/>ID: ' . $item['id'] . '<br/>标题: ' . $item['article_title'] . '<br/>状态: ' . $state) == 1 ? '开启' : '关闭');
		}

		show_json(1, array('url' => referer()));
	}

	public function source()
	{
		$sourceUrl = '/public/static/article/images';
		$this->assign(['sourceUrl'=>$sourceUrl]);
		return $this->fetch('');
	}

	public function category()
	{
		$list = Db::name('shop_article_category')->select();
		$this->assign(['list'=>$list]);
		return $this->fetch('article/category/index');
	}

	public function categorysave()
	{
		if (!empty($_POST['cate'])) {
			foreach ($_POST['cate'] as $id => $cate) {
				$data = array('category_name' => trim($cate['name']), 'displayorder' => intval($cate['displayorder']), 'isshow' => intval($cate['isshow']));
				if (!empty($id) && !empty($data['category_name'])) {
					Db::name('shop_article_category')->where('id',$id)->update($data);
					model('shop')->plog('article.category.save', '修改文章分类 ID: ' . $id . ' 名称: ' . $data['category_name']);
				}
			}
		}

		if (!empty($_POST['cate_new'])) {
			foreach ($_POST['cate_new'] as $cate_new) {
				$cate_new = trim($cate_new);

				if (empty($cate_new)) {
					continue;
				}

				$insert_id = Db::name('shop_article_category')->insertGetId(array('category_name' => $cate_new));
				model('shop')->plog('article.category.save', '添加分类 ID: ' . $insert_id . ' 名称: ' . $cate_new);
			}
		}

		model('shop')->plog('article.category.save', '批量修改分类');
		show_json(1);
	}

	public function categorydelete()
	{
		$id = intval(input('id'));
		$item = Db::name('shop_article_category')->where('id',$id)->field('id,category_name')->find();

		if (!empty($item)) {
			Db::name('shop_article_category')->where('id',$id)->delete();
			model('shop')->plog('article.category.delete', '删除分类 ID: ' . $id . ' 标题: ' . $item['category_name'] . ' ');
		}

		show_json(1);
	}

}