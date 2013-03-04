<?php
namespace Bonz\Controller\Admin\Forms;
use Bonz\Controller\Admin;
use Bonz\CMS\Role;

/**
 * 
 * @author fabrice
 *
 */
class User extends Admin{
	function main(){
		$Roles = new Role();
		$this->_view->roles = $Roles->select();
	}
	
	function checkRoles(){
		return $this->userIsLogged();
	}
}

