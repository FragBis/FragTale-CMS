<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;

/**
 * @author fabrice
 */
class List_Of_Articles extends Controller{
	function main(){
		$this->_view->articles = array();
		if (empty($this->_meta_article->catid))
			return;
		$articles = $this->_meta_article->getCategory()->getArticles(true);
		foreach ($articles as $article){
			if ($article['aid']!=$this->_meta_article->aid){
				$this->_view->articles[$article['aid']] = $article;
			}
		}
	}	
}
