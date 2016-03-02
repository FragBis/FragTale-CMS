<?php
namespace FragTale\Controller\Admin\User;
use FragTale\Controller;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;

class Validate extends Controller{
	
	var $auth_key;
	
	function initialize(){
		$this->_view->setTitle(_('Account validation'));
		$this->auth_key = isset($_GET['auth_key']) ? $_GET['auth_key'] : null;
		if (empty($this->auth_key)){
			$this->addUserEndMsg('ERROR', 'Wrong access attempt');
			$this->redirect(WEB_ROOT);
		}
	}
	
	function main(){
		$Param = new Parameters();
		$User = new User();
		if (!$Param->load("param_key='REGVAL_$this->auth_key'") || !$User->load("uid='$Param->param_value'")){
			$this->addUserEndMsg('ERROR', 'Unabled to find your account key');
			$Param->delete("param_key='REGVAL_$this->auth_key'");
			return false;
		}
		$values['active'] = 1;
		$User->update("uid='$User->uid'", $values);
		$Param->delete("param_key='REGVAL_$this->auth_key'");
		$this->addUserEndMsg('SUCCESS', _('Your account is now active.'));
		$this->redirect(WEB_ROOT.'/login');
	}
	
}