<?php
namespace Bonz\Controller\Admin\Forms;
use Bonz\Controller;

/**
 * @author fabrice
 */
class Register extends Controller{
	function main(){
		$this->setTitle(_('Create account'));
	}
}