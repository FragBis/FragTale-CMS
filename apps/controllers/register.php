<?php
namespace FragTale\Controller;
use FragTale\CMS\User_Roles;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;
use FragTale\Controller;
use FragTale\Debug;
use FragTale\CMS\Role;
use FragTale\JPT\Case_Report;
use FragTale\JPT\Department;

/**
 * 
 * @author fragbis
 *
 */
class Register extends Controller{
	/**
	 * @var \FragTale\JPT\Case_Report
	 */
	protected $CaseReport;
	/**
	 * @var \FragTale\CMS\User
	 */
	protected $Spouse1;
	/**
	 * @var \FragTale\CMS\User
	 */
	protected $Spouse2;
	
	function initialize(){
		if ($this->userIsLogged())
			$this->redirect(WEB_ROOT);
		$this->_view->user = new User();
	}
	function main(){
		$this->setTitle(_('Create a new account'));
	}
	
	function doPostBack(){
		if (!isset($_POST['email'])) return false;
		if (!$this->check_email($_POST['email'])){
			$this->addUserEndMsg('ERRORS', _('Invalid e-mail address.'));
			return false;
		}
		try{
			$values = $this->passValuesForUSer($_POST);
			if (!$values) return false;
			## Insert new user
			$email = $this->_view->user->escape($values['email']);
			if (!$this->_view->user->insert($values) || !$this->_view->user->load("email='$email'")){
				foreach ($values as $k=>$v){
					$this->_view->user->$k = $v;
				}
				$this->addUserEndMsg('ERRORS', _('Registration failed.'));
				return false;
			}
			$this->_view->user->update('uid='.$this->_view->user->uid, array(
				'upd_uid' => $this->_view->user->uid,
				'cre_uid' => $this->_view->user->uid
			));
			
			## Inserting its rules
			$UserRoles = new User_Roles();
			if (!$UserRoles->insert(array('uid'=>$this->_view->user->uid, 'rid'=>6)))
				$this->addUserEndMsg('ERRORS', _('Error occured.').' '._('No rule matched'));
			
			if (defined('ENV') && ENV=='devel')
				$this->temp();
			else
				$this->validate($values, $this->_view->user->uid);
		}
		catch(\Exception $exc){
			$this->addUserEndMsg('ERROR', _('System error:').' '.$exc->getMessage());
		}
		return false;
	}
	
	function passValuesForUSer(array $post){
		$values = array();
		foreach(array(
			'civility',
			'firstname',
			'lastname',
			'bir_name',
			'phone',
			'email'
					) as $field){
			if (!empty($post[$field]))
				$values[$field] = $post[$field];
			else{
				$this->addUserEndMsg('ERRORS', _('All fields are required.'));
				return false;
			}
		}
		if (!empty($post['password']))
			$values['password'] = md5($post['password']);
		else{
			$this->addUserEndMsg('ERRORS', _('All fields are required.'));
			return false;
		}
		if(!$this->checkDuplicates($this->_view->user, $values))
			return false;
		$values['login']	= $this->buildLoginAsRecordNumber($values);
		$values['upd_uid']	= 1;
		$values['cre_date'] = date('Y-m-d H:i:s');
		$values['active']	= 0;
		return $values;
	}
	/**
	 * Before inserting: check if login and email are free.
	 * @param User $User
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates(User $User, array $values){
		$email = $values['email'];
		if ($User->load("email='$email'")){
			$this->addUserEndMsg('ERRORS', sprintf(_('The e-mail "%s" is already registered.'), $email));
			return false;
		}
		return true;
	}
	
	function buildLoginAsRecordNumber(array $values){
		return strtoupper(substr($values['bir_name'], 0, 3)).
			strtoupper(substr($values['firstname'], 0, 3)).
			//str_replace('-', '', $values['bir_date']).
			date('Ymd');
	}
	
	final function validate(array $values, $uid){
		try{
			## Create a temp code for validation
			$auth_key = md5(md5($values['email']) . rand(0, 10000));
			$Param = new Parameters();
			$Param->insert(array(
					'param_key'		=> 'REGVAL_'.$auth_key,
					'param_value'	=> (int)$uid
			));
			## Send the validation mail
			if ($this->sendMail($values['email'], null, _('Email account validation'), $auth_key, 'mail/user_validation')){
				## Throw check Mail message
				$this->redirect(WEB_ROOT.'/check_mail');
			}
		}
		catch(\Exception $exc){
			$this->addUserEndMsg('ERROR', _('System error:').' '.$exc->getMessage());
		}
	}
	
	function loadCaseReport(){
		if (!$this->userIsLogged()){
			$this->addUserEndMsg('ERRORS', _('You must be logged in to be able to complete the registration form.'));
			$this->redirect(WEB_ROOT.'/login');
		}
		if (!empty($this->getMetaView()->CaseReport->cid)){
			$this->CaseReport = $this->getMetaView()->CaseReport;
		}
		else{
			$this->CaseReport = new Case_Report();
			$this->CaseReport->load('spouse1='.$this->getUser()->uid.' OR spouse2='.$this->getUser()->uid, 'cre_date DESC LIMIT 0,1');
		}
		$this->_view->CaseReport = $this->CaseReport;
		
		if (!empty($this->getMetaView()->Spouse1->uid)){
			$this->Spouse1 = $this->getMetaView()->Spouse1;
		}
		elseif (!empty($this->CaseReport->spouse1) && !isset($this->Spouse1->uid)){
			$this->Spouse1 = new User();
			$this->Spouse1->load('uid='.$this->CaseReport->spouse1);
		}
		$this->_view->Spouse1 = $this->Spouse1;
		
		if (!empty($this->getMetaView()->Spouse2->uid)){
			$this->Spouse2 = $this->getMetaView()->Spouse2;
		}
		elseif (!isset($this->Spouse2->uid)){
			$this->Spouse2 = new User();
			if (!empty($this->CaseReport->spouse2)){
				$this->Spouse2->load('uid='.$this->CaseReport->spouse2);
			}
		}
		$this->_view->Spouse2 = $this->Spouse2;
	}
	
	/**
	 * Load Dpt into a meta view var
	 */
	function loadListOfDepartments(){
		if (empty($this->getMetaView()->listOfDepartments)){
			$this->getMetaView()->listOfDepartments = (new Department())->select("country_code='fr_FR'", null, 'dpt_name');//TODO get locale (maybe from cookies)
		}
		if (empty($this->_view->listOfDepartments)){
			$this->_view->listOfDepartments = $this->getMetaView()->listOfDepartments;
		}
	}
	
	/**
	 * @param int $status
	 */
	function updateCaseReportStatus($status){
		if ($this->CaseReport->csid < (int)$status)
			$this->CaseReport->update('cid='.$this->CaseReport->cid, array('csid'=>$status, 'archived'=>0));
	}
	
	final function temp(){
		$this->_view->user->update('uid='.$this->_view->user->uid, array('active'=>1));
		$this->redirect(WEB_ROOT.'/login');
	}
}