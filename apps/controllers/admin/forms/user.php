<?php
namespace FragTale\Controller\Admin\Forms;
use FragTale\Controller\Admin;
use FragTale\CMS\Role;

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

