<?php
namespace Bonz\Controller\Admin;
use Bonz\Controller\Admin;
use Bonz\CMS\User;

/**
 * @author fabrice
 */
class Users extends Admin{
	function initialize(){
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->setTitle(_('User accounts'));
	}
	function main(){
		$order	= !empty($_GET['order'])? $_GET['order'].(!empty($_GET['desc']) ? ' DESC' : ' ASC'): null;
		$count	= !empty($_GET['count'])? $_GET['count']	: 10;
		$page	= !empty($_GET['page'])	? $_GET['page']-1	: 0;
		$conditions = ($order ? ' '.$order : '1').(' LIMIT '.($page*$count).', '.$count);
		
		$User = new User();
		$this->_view->users = $User->selectDistinct(null, null, $conditions);
		$this->_view->rowCount = $User->count();
	}
}

