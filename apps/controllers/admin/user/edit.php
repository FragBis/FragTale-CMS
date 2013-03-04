<?php
namespace Bonz\Controller\Admin\User;
use Bonz\Controller\Admin;
use Bonz\CMS\User;
use Bonz\CMS\User_Roles;

class Edit extends Admin{
	
	var $uid;
	var $user;
	
	function initialize(){
		$this->_view->setTitle(_('User account edition'));
		$this->uid = isset($_GET['uid']) ? $_GET['uid'] : ($this->_view->userIsLogged() ? $_SESSION['REG_USER']['uid'] : null);
		if (empty($this->uid))
			$this->redirect(ADMIN_WEB_ROOT.'/users');
		$this->user = new User();
	}
	
	function doPostBack(){
		if (!isset($_POST['login']) || empty($this->uid))
			return false;
		$values = $_POST;
		unset($values['role']);
		$values['active'] = isset($_POST['active']) ? 1 : 0;
		if (!empty($_POST['password']))
			$values['password'] = md5($_POST['password']);
		else
			unset($values['password']);
		$values['upd_uid'] = $_SESSION['REG_USER']['uid'];
		
		$this->user->load("uid='$this->uid'");
		if ($this->checkDuplicates($values)){
			$this->user->update("uid='$this->uid'", $values);
			if ($this->userIsAdmin()){
				$UserRoles = new User_Roles();
				$roles = $UserRoles->getUserRoles($this->uid);
				if (!empty($_POST['role']))
				foreach ($_POST['role'] as $rid=>$on){
					if (!in_array($rid, $roles)){
						$UserRoles->insert(array('uid'=>$this->uid, 'rid'=>$rid));
					}
				}
				foreach ($roles as $rid){
					if (empty($_POST['role'][$rid])){
						$UserRoles->delete("uid='$this->uid' AND rid='$rid'");
					}
				}
			}
			$this->addUserEndMsg('SUCCESS', $_POST['login'].' '._('has been successfully updated.'));
			$this->redirect($_SERVER['REQUEST_URI']);
		}
	}
	
	function main(){
		$this->_view->user = new User();
		if ($this->_view->user->load("uid='$this->uid'")){
			$UserRoles = new User_Roles();
			$this->_view->user_roles = $UserRoles->getUserRoles($this->uid);
			$thisUserIsSuper = in_array('1', $this->_view->user_roles);
			$this->_view->noWriting = ($thisUserIsSuper && !$this->userIsSuperAdmin()) ||
				(!$this->userIsAdmin() && $this->uid != $_SESSION['REG_USER']['uid']);
		}
		else
			$this->addUserEndMsg('ERRORS', _('User ID cannot be empty.'));
		
		#Include Wysiwyg
		$this->addJS(WEB_ROOT.'/js/wysiwyg.js');
	}
	
	/**
	 * Before updating: check if login and email are free.
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates($values){
		$initialLogin = $this->user->login;
		$initialEmail = $this->user->email;
		$login = $values['login'];
		if ($login!=$initialLogin){
			if ($this->user->load("login='$login'")){
				$this->addUserEndMsg('ERRORS', sprintf(_('Login "%s" already used.'), $login));
				return false;
			}
		}
		$email = $values['email'];
		if ($email!=$initialEmail){
			if ($this->user->load("email='$email'")){
				$this->addUserEndMsg('ERRORS', sprintf(_('E-mail "%s" already registered.'), $email));
				return false;
			}
		}
		return true;
	}
		
	function checkRoles(){
		return $this->userIsLogged();
	}
}