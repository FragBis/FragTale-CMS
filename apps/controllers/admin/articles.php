<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller\Admin;
use FragTale\CMS\Article;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Articles extends Admin{
	function initialize(){
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->setTitle(_('List of articles'));
	}
	function main(){
		$this->_view->articles = $this->getGridSortedResult(new Article());
	}
}

