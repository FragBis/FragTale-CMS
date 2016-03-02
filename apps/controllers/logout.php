<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * @author fabrice
 */
class Logout extends Controller{
	function main(){
		session_destroy();
		$this->redirect(WEB_ROOT);
	}	
}

