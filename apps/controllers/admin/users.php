<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller\Admin;
use FragTale\CMS\User;

/**
 * @author fabrice
 */
class Users extends Admin{
	function initialize(){
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->setTitle(_('User accounts'));
	}
	function main(){
		$this->_view->users = $this->getGridSortedResult(new User());
	}
}