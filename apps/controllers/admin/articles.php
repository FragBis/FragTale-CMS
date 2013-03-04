<?php
namespace Bonz\Controller\Admin;
use Bonz\Controller\Admin;
use Bonz\CMS\Article;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Articles extends Admin{
	function initialize(){
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->setTitle(_('List of articles'));
	}
	function main(){
		$order	= !empty($_GET['order'])? $_GET['order'].(!empty($_GET['desc']) ? ' DESC' : ' ASC'): null;
		$count	= !empty($_GET['count'])? $_GET['count']	: 10;
		$page	= !empty($_GET['page'])	? $_GET['page']-1	: 0;
		$conditions = ($order ? ' '.$order : '1').(' LIMIT '.($page*$count).', '.$count);
		
		$Article = new Article();
		$this->_view->articles = $Article->selectDistinct(null, null, $conditions);
		$this->_view->rowCount = $Article->count();
	}
}

