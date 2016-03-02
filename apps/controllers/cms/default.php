<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class _Default extends Controller{
	function main(){
		if (empty($this->_article->aid)){
			$request_uri = $this->_article->escape(trim($_GET['my_current_view'], '/'));
			$this->_article->load("request_uri='$request_uri'");
		}
		$this->setTitle($this->_article->title);
		$this->_view->restricted = !$this->checkRules();
		if (!empty($_REQUEST['clean'])) $this->setLayout('clean');
	}
}