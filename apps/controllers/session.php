<?php
namespace FragTale\Controller;
use FragTale\Controller;

/**
 * 
 * When this controller is called, it will just return if user is still logged.
 * If the check is done before session timeout, this will refresh session
 * 
 * @author fabrice
 * 
 */
class Session extends Controller{
	function main(){
		if (!$this->userIsLogged())
			die('0');
		else
			die('1');
	}
}