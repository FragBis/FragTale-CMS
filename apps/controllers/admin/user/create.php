<?php
namespace FragTale\Controller\Admin\User;
use FragTale\Controller\Admin;
use FragTale\CMS\User;
use FragTale\CMS\User_Roles;

class Create extends Admin{
	
	function initialize(){
		$this->setTitle(_('Add a new user account'));
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
		$values['upd_uid'] = $this->getUser()->uid;
		$values['cre_uid'] = $this->getUser()->uid;
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
	
	function main(){}
	
	/**
	 * Before inserting: check if login and email are free.
	 * @param User $User
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates(User $User, $values){
		$login = $values['login'];
		if ($User->load("login='$login'")){
			$this->addUserEndMsg('ERRORS', sprintf(_('The nickname "%s" is already taken.'), $login));
			return false;
		}
		$email = $values['email'];
		if ($User->load("email='$email'")){
			$this->addUserEndMsg('ERRORS', sprintf(_('The e-mail "%s" is already registered.'), $email));
			return false;
		}
		return true;
	}
}