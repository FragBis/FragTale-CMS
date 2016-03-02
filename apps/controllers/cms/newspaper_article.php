<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class Newspaper_Article extends Controller{
	function main(){
		$this->_view->article_category = $this->getArticle()->selectRow('aid=\''.$this->getArticle()->getCategory()->aid.'\' AND publish=1');
		$this->setTitle($this->_article->title);
	}
}