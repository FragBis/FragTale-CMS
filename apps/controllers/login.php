<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Login extends Controller{
	/**
	 * @var \FragTale\CMS\User
	 */
	var $user;
	
	function initialize(){
		//$this->unsetUserSession();
		if ($this->userIsLogged())
			$this->checkRoles();
		$this->user = new \FragTale\CMS\User();
	}
	
	function doPostBack(){
		if (!isset($_POST['email']))
			return false;
		if (empty($_POST['email']) || empty($_POST['pwd'])){
			$this->addUserEndMsg('ERRORS', _('Please, enter your email and password.'));
			return false;
		}
		$this->user->load("email='".$this->user->escape($_POST['email'])."' AND password=MD5('".$this->user->escape($_POST['pwd'])."')");
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
			$this->userRoles = new \FragTale\CMS\User_Roles();
			$_SESSION['REG_USER']['ROLES'] = $this->userRoles->getUserRoles($this->user->uid);
			return $this->checkRoles();
		}
		else
			$this->addUserEndMsg('ERRORS',_('Authentication failed.'));
		return false;
	}
	
	function main(){
		$this->setTitle('Écran de connexion');
	}
	
	/**
	 * Bypass this check since user try to connect
	 */
	function checkRoles(){
		$role = $this->getUser()->getStrongestRole();
		$rid = !empty($role['rid']) ? $role['rid'] : null;
		if (!$rid){
			$this->addUserEndMsg('ERRORS',_('Authentication failed.'));
			return false;
		}
		switch ($rid){
			case 1:
			case 2:
				$this->redirect(ADMIN_WEB_ROOT);
				break;
			case 3:
			case 4:
			case 5:
				$this->redirect(WEB_ROOT.'/manager_account');
				break;
			case 6:
				$_SESSION['fresh_login'] = true;
				$this->redirect(WEB_ROOT.'/user_account');
				break;
			default:
				$this->redirect(WEB_ROOT);
				break;
		}
		return true;
	}
}