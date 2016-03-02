<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class Fiches_Pratiques extends Controller{
	function main(){
		$art = new Article();
		$this->_view->listOfArticles = $art->select('catid='.$this->getArticle()->catid.' AND aid<>'.$this->getArticle()->aid.' AND publish=1', null, 'position');
		$this->setTitle($this->_article->title);
	}
} 