<?php
namespace Bonz\Controller\Admin;
use \Bonz\Controller\Admin;
use \Bonz\CMS\Article_Category;

/**
 * @author fabrice
 */
class Article_Categories extends Admin{
	function initialize(){
		$this->setTitle(_('List of categories'));
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
	}
	function main(){
		$order	= !empty($_GET['order'])? 'C.'.$_GET['order'].(!empty($_GET['desc']) ? ' DESC' : ' ASC'): null;
		$count	= !empty($_GET['count'])? $_GET['count']	: 10;
		$page	= !empty($_GET['page'])	? $_GET['page']-1	: 0;
		$conditions = ($order ? ' ORDER BY '.$order : '').(' LIMIT '.($page*$count).', '.$count);
		$Category = new Article_Category();
		$this->_view->article_categories = $Category->getGrid($conditions);
		$this->_view->rowCount = $Category->count();
	}
}