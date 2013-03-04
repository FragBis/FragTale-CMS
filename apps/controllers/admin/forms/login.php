<?php
namespace Bonz\Controller\Admin\Forms;
use Bonz\Controller\Admin;

/**
 * @author fabrice
 */
class Login extends Admin{
	/**
	 * @var \Bonz\CMS\User
	 */
	var $user;
	
	function initialize(){
		unset($_SESSION['REG_USER']);
		$this->user = new \Bonz\CMS\User();
	}
	
	function doPostBack(){
		if (!isset($_POST['login']))
			return false;
		if (empty($_POST['login']) || empty($_POST['pwd'])){
			$this->addUserEndMsg('ERRORS', _('Please, enter your login and password.'));
			return false;
		}
		$this->user->load("login='".$this->user->escape($_REQUEST['login'])."' AND password=MD5('".$this->user->escape($_REQUEST['pwd'])."')");
		unset($this->user->password);
		if (!empty($this->user->uid)){
			if (!$this->user->active){
				$this->addUserEndMsg('ERRORS',_('Your account has been deactivated.'));
				return false;
			}
			## Set into session
			$_SESSION['REG_USER'] = array();
			foreach (get_object_vars($this->user) as $key=>$value){
				$_SESSION['REG_USER'][$key] = $value;
			}
			$this->userRoles = new \Bonz\CMS\User_Roles();
			$_SESSION['REG_USER']['ROLES'] = $this->userRoles->getUserRoles($this->user->uid);
			return true;
		}
		else
			$this->addUserEndMsg('ERRORS',_('Authentication failed.'));
		return false;
	}
	
	/**
	 * Bypass this check since user try to connect
	 * @see Bonz\Controller.Admin::checkRoles()
	 */
	function checkRoles(){
		return true;
	}
}

