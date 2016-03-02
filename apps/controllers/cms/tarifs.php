<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class Tarifs extends Controller{
	function main(){
		$this->setTitle($this->_article->title);
	}
}