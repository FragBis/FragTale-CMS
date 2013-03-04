<?php
namespace Bonz\Controller\Admin;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Logout extends Controller{
	function main(){
		session_destroy();
		$this->redirect(WEB_ROOT.'/admin/login');
	}
}
