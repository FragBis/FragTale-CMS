<?php
namespace Bonz\Controller;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Home extends Controller{
	function main(){
		$this->_article->load("request_uri='home'");
		$this->setTitle($this->_article->title);
	}
}