<?php
namespace FragTale\Controller;
use FragTale\CMS\User_Roles;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;
use FragTale\Controller;

/**
 * 
 * @author fragbis
 *
 */
class Password_Recovery extends Controller{
	function initialize(){
		$this->setTitle(_('Password recovery'));
	}
	function main(){
	}
	
	function doPostBack(){
		if (!isset($_POST['user'])) return false;
		$userinfo = trim($_POST['user']);
		if (empty($userinfo)){
			$this->addUserEndMsg('ERROR', _('The username or email address you entered is empty.'));
			return false;
		}
		$User = new User();
		if (!$User->load("email='$userinfo' OR login='$userinfo'")){
			$this->addUserEndMsg('ERROR', _('The username or email address you entered is not registered.'));
			return false;
		}
		
		## Create a temp code for validation
		$auth_key = md5(md5($userinfo . rand(0, 10000)));
		$Param = new Parameters();
		$Param->insert ( array (
				'param_key'		=> 'PWDREC_'.$auth_key,
				'param_value'	=> $User->uid
		) );
		
		## Send the validation mail
		$noreply = $Param->selectValue ("param_key='NOREPLY_EMAIL'", 'param_value');
		$this->sendMail ( $User->email, $noreply, _( 'Password recovery'), $auth_key, 'mail/password_recovery' );
		
		## Throw check Mail message
		$this->addUserEndMsg('SUCCESS', _('An email has been sent to your mailbox. Please, check in. The mail might be placed in your spam box.'));
		$this->redirect(WEB_ROOT);
	}
}