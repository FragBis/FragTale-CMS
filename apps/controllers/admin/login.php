<?php
namespace Bonz\Controller\Admin;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Login extends Controller{
	function main(){
		if (!empty($_SESSION['REG_USER']['uid']))
			$this->redirect(WEB_ROOT.'/admin');
		$this->setTitle(_('Accessing the admin panel'));
		$this->_view->form = $this->_view->getBlock('admin/forms/login');
	}
}

