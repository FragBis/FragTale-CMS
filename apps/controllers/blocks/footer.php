<?php
namespace Bonz\Controller\Blocks;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Footer extends Controller{
	function main(){
		$this->_view->mainArticles = $this->_article->select("(catid IS NULL OR catid='' OR catid=0) AND request_uri <> 'home'");
	}	
}
