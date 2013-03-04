<?php
namespace Bonz\Controller\Cms;
use Bonz\Controller;
use Bonz\CMS\Article;

/**
 * @author fabrice
 */
class _Default extends Controller{
	function main(){
		if (empty($this->_article->aid)){
			$this->_article = new Article();
			$request_uri = $this->_article->escape(trim($_GET['my_current_view'], '/'));
			$this->_article->load("request_uri='$request_uri'");
		}
		$this->setTitle($this->_article->title);
	}
}