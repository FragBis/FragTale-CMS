<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class Press_Review extends Controller{
	function main(){
		## Get all article form this category
		$catid	= $this->getArticle()->getCategory()->catid;
		$range	= !empty($_REQUEST['range'])? (int)$_REQUEST['range']			: 4;
		$from	= !empty($_REQUEST['from'])	? ((int)$_REQUEST['from']-1)*$range	: 0;
		$to		= $from + $range;
		$where	= 'catid=\''.$catid.'\' AND aid<>\''.$this->getArticle()->aid.'\' AND publish = 1';
		$this->_view->articles	= $this->getArticle()->select($where, null, "position DESC LIMIT $from, $to");
		$this->_view->nbArticles= $this->getArticle()->count($where);
		$this->setTitle($this->_article->title);
	}
}