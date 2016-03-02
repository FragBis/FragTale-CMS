<?php
namespace FragTale\Controller\Admin\Blocks;
use FragTale\CMS\Article;
use FragTale\CMS\Article_Category;
use FragTale\Controller\Admin;
use FragTale\Controller\Admin\Article_Categories;

/**
 * @author fabrice
 */
class Article_Lists extends Admin{
	
	function main(){
		$Cat = new Article_Category();
		$this->_view->categories = $Cat->getTree();
		$this->_view->tree = $this->buildArticles().$this->buildTree($this->_view->categories);
	}
	
	function doPostBack(){
		if (empty($_POST['position'])) return false;
		$Art = new Article();
		foreach ($_POST['position'] as $aid=>$position){
			$Art->update('aid='.$aid, array('position'=>$position));
		}
		$this->addUserEndMsg('SUCCESS', _('Changes saved'));
		$this->redirectToSelf();
	}
	
	function buildTree($categories){
		$html= '<ul class="category_tree">';
		foreach ($categories as $category){
			$aid = $category['data']['aid'];
			$html .= '<li class="category_branch">'.
						'<input type="hidden" class="hidden_position" name="position['.$aid.']" value="'.$category['data']['position'].'" />'.
						'<div>'.
							'<a class="category_link" href="'.ADMIN_WEB_ROOT.'/article/edit?aid='.$aid.'">'.
								$category['data']['article_title'].
							'</a>'.
							'<a class="movedown">&nbsp;</a>'.
							'<a class="moveup">&nbsp;</a>'.
						'</div>';
			if (!empty($category['children'])){
				$html .= $this->buildTree($category['children']);
			}
			$html .= $this->buildArticles($category['data'], $aid);
			$html .= '</li>';
		}
		$html .= '</ul>';
		return $html;
	}
	
	function buildArticles($cat_data=null, $aid=null){
		$catid	= !empty($cat_data['catid']) ? $cat_data['catid'] : '';
		$articleCatTitle= !empty($cat_data['article_title']) ? $cat_data['article_title'] : '';
		$html	= '<ul class="article_tree">';
		$firstExp = empty($catid) ? 'catid IS NULL' : "catid='$catid'";
		foreach ($this->getArticle()->select("$firstExp AND aid <> '$aid'", null, 'position') as $article){
			$html .='<li class="article_branch">'.
						'<input type="hidden" class="hidden_position" name="position['.$article['aid'].']" value="'.$article['position'].'" />'.
						'<div>'.
							'<a class="article_link" href="'.ADMIN_WEB_ROOT.'/article/edit?aid='.$article['aid'].'">'.$article['title'].'</a>'.
							'<a class="movedown">&nbsp;</a>'.
							'<a class="moveup">&nbsp;</a>'.
						'</div>'.
					'</li>';
		}
		$html .='<li class="article_create">'.
					'<div><a class="add_new" href="'.ADMIN_WEB_ROOT.'/article/create?catid='.$catid.'">'.
						($articleCatTitle ?
							_('Create new item for category:').' "'.$articleCatTitle.'"' :
							_('Create new item without category')
						).
					'</a></div>'.
				'</li>';
		$html .= '</ul>';
		return $html;
	}
}