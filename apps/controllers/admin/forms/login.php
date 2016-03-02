<?php
namespace FragTale\Controller\Admin\Forms;
use FragTale\Controller\Admin;

/**
 * @author fabrice
 */
class Login extends Admin{
	/**
	 * @var \FragTale\CMS\User
	 */
	var $user;
	
	function initialize(){
		$this->unsetUserSession();
		$this->user = new \FragTale\CMS\User();
	}
	
	function doPostBack(){
		if (!isset($_POST['login']))
			return false;
		if (empty($_POST['login']) || empty($_POST['pwd'])){
			$this->addUserEndMsg('ERRORS', _('Please, enter your login (nickname) and password.'));
			return false;
		}
		$login = $this->user->escape($_REQUEST['login']);
		$this->user->load("(login='$login' OR email='$login') AND password=MD5('".$this->user->escape($_REQUEST['pwd'])."')");
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
			$toGo = $_SESSION['PREVIOUS_PAGE'];
			$exps = explode('/', $toGo);
			if (!empty($_SESSION['PREVIOUS_PAGE']) &&
					!in_array(end($exps), array('login', 'register', 'password_recovery'))){
				unset($_SESSION['PREVIOUS_PAGE']);
				$this->redirect($toGo);
			}
			elseif (strpos($this->_meta_view->getViewName(), 'admin')===0)
				$this->redirect(ADMIN_WEB_ROOT);
			else
				$this->redirect(WEB_ROOT);
			return true;
		}
		else
			$this->addUserEndMsg('ERRORS',_('Authentication failed.'));
		return false;
	}
	
	function main(){
		# Store the page referer intending to go back there after login
		if (!empty($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != WEB_ROOT.$_SERVER['REQUEST_URI']){
			$_SESSION['PREVIOUS_PAGE'] = stripos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false ?
			$_SERVER['HTTP_REFERER'] : null;
		}
	}
	
	/**
	 * Bypass this check since user try to connect
	 * @see FragTale\Controller.Admin::checkRoles()
	 */
	function checkRoles(){
		return true;
	}
}

