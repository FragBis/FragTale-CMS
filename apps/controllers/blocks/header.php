<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Header extends Controller{
	function main(){
		$this->_view->menu = array(
			'home'		=>_('Home'),
		);
		if ($this->userIsLogged())
			$this->_view->menu['logout'] = _('Logout');
		else{
			$this->_view->menu['login']		= _('Sign In');
		}
		$this->_view->current = !empty($_GET['my_current_view']) ? trim($_GET['my_current_view'], '/') : 'home';
	}	
}
