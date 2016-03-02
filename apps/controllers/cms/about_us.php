<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class About_Us extends Controller{
	function main(){
		$art = new Article();
		$this->_view->listOfArticles = $art->select('catid='.$this->getArticle()->catid.' AND aid<>'.$this->getArticle()->aid.' AND publish=1');
		$this->setTitle($this->_article->title);
	}
} 