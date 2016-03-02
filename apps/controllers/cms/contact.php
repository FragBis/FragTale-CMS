<?php
namespace FragTale\Controller\Cms;
use FragTale\Controller;
use FragTale\CMS\Article;

/**
 * @author fabrice
 */
class Contact extends Controller{
	
	function initialize(){
		if (empty($_SESSION['randomize'])){
			$_SESSION['randomize'] = rand(0, 1000000);
		}
		/**
		 * Define the fields properties
		 */
		$this->_view->fields = array(
			'lastname'	=>array(
					'type' =>'text',
					'class'=>'text long mandatory',
					'title'=>_('Lastname*'),
					'errmsg'=>_('This field is required.'),
					'value'=>''
			),
			'firstname'	=>array(
					'type' =>'text',
					'class'=>'text long mandatory',
					'title'=>_('Firstname*'),
					'errmsg'=>_('This field is required.'),
					'value'=>''
			),
			'email'		=>array(
					'type' =>'text',
					'class'=>'text long mandatory',
					'title'=>_('Your email address*'),
					'errmsg'=>_('Invalid email address format.').' '._('This field is required.'),
					'value'=>''
			),
			'subject'	=>array(
					'type'		=>'text',
					'class'		=>'text long',
					'title'		=>_('Subject*'),
					'errmsg'=>_('This field is required.'),
					'value'=>''
			),
			'message'	=>array(
					'type' =>'textarea',
					'class'=>'mandatory',
					'title'=>_('Message*'),
					'errmsg'=>_('This field is required.'),
					'value'=>''
			)
		);
	}
	
	function main(){
		$this->setTitle($this->_article->title);
	}
	
	function doPostBack(){
		if (empty($_POST)) return false;
		if (empty($_POST['sendmail']) || $_POST['sendmail'] != $_SESSION['randomize']){
			$this->addUserEndMsg('ERRORS', _('The system seems to have found that you are trying something like spaming'));
			//TODO : log spamer
			//Idée : on pourrait faire un système de log de fichiers timés (spam_[timestamp].log << session_id)
			//Si pour une session, il y a trop de répétitions, on définit comme spam et on bloque temporairement l'IP
			$this->redirect(WEB_ROOT);
		}
		
		## Checking fields:
		
		#Check no empty value
		$errorCaught = false;
		foreach ($this->_view->fields as $fieldname=>$attr){
			if (empty($_POST[$fieldname])){
				$this->_view->fields[$fieldname]['throw_error'] = true;
				$errorCaught = true;
			}
			else{
				$this->_view->fields[$fieldname]['value'] = $_POST[$fieldname];
			}
		}
		
		# Check email format
		if (!($validEmail = $this->check_email($_POST['email']))){
			$this->_view->fields['email']['throw_error'] = $errorCaught = true;
		}
		
		# Check subject
		$subject = !empty($_POST['subject']) ? $_POST['subject'] : null;
		if (!$subject)
			$this->_view->fields['subject']['throw_error'] = $errorCaught = true;
		
		if ($errorCaught)
			return false;
		
		$fullname = $_POST['firstname'].' '.$_POST['lastname'];
		$from = $fullname.' <'.$_POST['email'].'>';
		$message = nl2br($_POST['message']);
		
		# Send the email to contact and to admin
		if ($this->sendMailToContact($from, $subject, $message, 'mail/contactus')){
			$Admin = new \FragTale\CMS\User();
			if ($Admin->load('uid=1'))
				$this->sendMail($Admin->email, $from, $subject, $message, 'mail/contactus');
			unset($_SESSION['randomize']);
			$this->addUserEndMsg('SUCCESS', _('Your message has been sent'));
			# Clear message
			foreach ($this->_view->fields as $fieldname=>$attr){
				$this->_view->fields[$fieldname]['value'] = '';
			}
			$this->redirectToSelf();
		}
		else
			$this->addUserEndMsg('ERRORS', _('An error occured while sending the email'));
	}
}