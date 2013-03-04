<?php
namespace Bonz\Controller\Admin\User;
use Bonz\Controller\Admin;
use Bonz\CMS\User;
use Bonz\CMS\User_Roles;

class Create extends Admin{
	
	function initialize(){
		$this->_view->user = new User();
	}
	
	function doPostBack(){
		if (!isset($_POST['login'])) return false;
		if (!$this->check_email($_POST['email'])){
			$this->addUserEndMsg('ERRORS', _('Invalid e-mail address.'));
			return false;
		}
		$values = $_POST;
		unset($values['role']);
		$values['active'] = isset($_POST['active']) ? 1 : 0;
		if (!empty($_POST['password']))
			$values['password'] = md5($_POST['password']);
		else
			unset($values['password']);
		
		if(!$this->checkDuplicates($this->_view->user, $values))
			return false;
		$values['upd_uid'] = $_SESSION['REG_USER']['uid'];
		$values['cre_uid'] = $_SESSION['REG_USER']['uid'];
		$values['cre_date'] = date('Y-m-d H:i:s');
		## Insert new user
		$login = $this->_view->user->escape($values['login']);
		if (!$this->_view->user->insert($values) || !$this->_view->user->load("login='$login'")){
			$this->addUserEndMsg('ERRORS', _('Registration failed.'));
			return false;
		}
		
		## Inserting its rules
		$UserRoles = new User_Roles();
		foreach ($_POST['role'] as $rid=>$on){
			$UserRoles->insert(array('uid'=>$this->_view->user->uid, 'rid'=>$rid));
		}
		$this->addUserEndMsg('SUCCESS', $_POST['login'].' '._('has been successfully added.'));
		$this->redirect(ADMIN_WEB_ROOT.'/user/edit?uid='.$this->view->user->uid);
	}
	
	function main(){
		#Include Wysiwyg
		$this->addJS(WEB_ROOT.'/js/wysiwyg.js');
		$this->setTitle(_('Add a new user account'));
	}
	
	/**
	 * Before inserting: check if login and email are free.
	 * @param User $User
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates(User $User, $values){
		$login = $values['login'];
		if ($User->load("login='$login'")){
			$this->addUserEndMsg('ERRORS', '"'.$login.'"'._(': login already used.'));
			return false;
		}
		$email = $values['email'];
		if ($User->load("email='$email'")){
			$this->addUserEndMsg('ERRORS', '"'.$email.'"'._(': E-mail already registered.'));
			return false;
		}
		return true;
	}
}