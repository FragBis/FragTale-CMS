<?php
namespace FragTale\Controller\Admin;
use \FragTale\Controller\Admin;
use \FragTale\CMS\Article_Category;

/**
 * @author fabrice
 */
class Article_Categories extends Admin{
	function initialize(){
		$this->setTitle(_('List of categories'));
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
	}
	function main(){
		$this->_view->article_categories = $this->getGridSortedResult(new Article_Category());
	}
}