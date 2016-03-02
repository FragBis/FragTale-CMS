<?php
namespace FragTale\Controller\Admin\User;
use FragTale\Controller;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;

class Password_Recovery extends Controller{
	
	var $User;
	var $auth_key;
	var $Param;
	
	function initialize(){
		$this->_view->setTitle(_('Password recovery'));
		$this->auth_key = isset($_GET['auth_key']) ? $_GET['auth_key'] : null;
		$this->User = new User();
		
		if (empty($this->auth_key)){
			$this->addUserEndMsg('ERROR', 'Wrong access attempt');
			$this->redirect(WEB_ROOT);
		}
		$this->Param = new Parameters();
		if (!$this->Param->load("param_key='PWDREC_$this->auth_key'") || !$this->User->load("uid='".$this->Param->param_value."'")){
			$this->addUserEndMsg('ERROR', 'Unabled to find your account key');
			return false;
		}
	}
	
	function main(){
	}
	
	function doPostBack(){
		if (!isset($_POST['password'])){
			return false;
		}
		
		$values['password'] = md5($_POST['password']);
		$this->User->update("uid='".$this->User->uid."'", $values);
		$this->addUserEndMsg('SUCCESS', _('Password successfully updated.'));
		$this->Param->delete("param_key='PWDREC_$this->auth_key'");
		$this->redirect(WEB_ROOT.'/login');
	}
}