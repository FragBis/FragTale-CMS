<?php
namespace FragTale\Controller\Admin\Elements;
use FragTale\CMS\Article;
use FragTale\Controller\Admin;

/**
 * @author fabrice
 */
class User_Info extends Admin{
	
	function main(){
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->_view->articles = $this->getGridSortedResult(new Article(), 'owner_id='.$this->getUser()->uid);;
	}
}