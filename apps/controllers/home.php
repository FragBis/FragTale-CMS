<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * @author fabrice
 * 
 * !!! NB: This class must probably be overrided by the class "FragTale\Controller\CMS\Home"
 * 
 */
class Home extends Controller{
	function main(){
		$this->_article->load("request_uri='home'");
		$this->setTitle($this->_article->title);
	}
}