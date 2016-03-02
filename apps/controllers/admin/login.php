<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Login extends Controller{
	function main(){
		if ($this->userIsLogged())
			$this->redirect(WEB_ROOT.'/admin');
		$this->setTitle(_('Accessing the admin panel'));
		$this->_view->form = $this->_view->getBlock('admin/forms/login');
	}
}

