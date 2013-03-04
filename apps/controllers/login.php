<?php
namespace Bonz\Controller;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Login extends Controller{
	function main(){
		if ($this->userIsLogged())
			$this->redirect(WEB_ROOT);
		$this->setTitle(_('Please, login'));
		$this->_view->form = $this->_view->getBlock('admin/forms/login');
	}
}