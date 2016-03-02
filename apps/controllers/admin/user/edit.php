<?php
namespace FragTale\Controller\Admin\User;
use FragTale\Controller\Admin;
use FragTale\CMS\User;
use FragTale\CMS\User_Roles;
use FragTale\CMS\Parameters;
use FragTale\CMS\Article;
use FragTale\CMS\User_Files;
use FragTale\CMS\Files;

class Edit extends Admin{
	
	var $uid;
	var $user;
	var $userIsOwner;
	
	function initialize(){
		$this->_view->setTitle(_('User account edition'));
		$this->uid = isset($_GET['uid']) ? $_GET['uid'] : $this->getUser()->uid;
		if (empty($this->uid))
			$this->redirect(ADMIN_WEB_ROOT.'/users');
		$this->user = new User();
		$this->user->load("uid='$this->uid'");
		$this->userIsOwner = ($this->uid==$this->getUser()->uid);
	}
	
	function doPostBack(){
		if (!isset($_POST['login']) || empty($this->uid))
			return false;
		if (isset($_POST['delete'])){
			if ($this->userdel()){
				if ($this->userIsOwner){
					$this->unsetUserSession();
					$this->redirect(WEB_ROOT);
				}
				else{
					$this->redirect(ADMIN_WEB_ROOT.'/users');
				}
			}
			$this->redirectToSelf();
		}
		$values = $_POST;
		$values['active'] = !empty($_POST['active']) ? 1 : 0;
		if (!empty($_POST['password']))
			$values['password'] = md5($_POST['password']);
		else
			unset($values['password']);
		$values['upd_uid'] = $this->getUser()->uid;
		unset($values['role']);
		
		if ($this->checkDuplicates($values)){
			$this->user->update("uid='$this->uid'", $values);
			if ($this->userIsAdmin()){
				$UserRoles = new User_Roles();
				$roles = $UserRoles->getUserRoles($this->uid);
				if (!empty($_POST['role']))
				foreach ($_POST['role'] as $rid=>$on){
					if (!in_array($rid, $roles)){
						$UserRoles->insert(array('uid'=>$this->uid, 'rid'=>$rid));
					}
				}
				foreach ($roles as $rid){
					if (empty($_POST['role'][$rid])){
						$UserRoles->delete("uid='$this->uid' AND rid='$rid'");
					}
				}
			}
			/* User profile image */
			if (!empty($_FILES)){
				$File		= new Files();
				$UserFiles	= new User_Files();
				$files		= $File->store($_FILES);//In fact, this array must return just 1 row
				if (!empty($files)){
					## Check if there is already a profile picture
					$profile_fid = $UserFiles->selectValue('is_profile=1 AND uid='.$this->uid, 'fid');
					foreach ($files as $fid=>$filename){
						if ($profile_fid==$fid) continue;
						$UserFiles->update('uid='.$this->uid, array('is_profile'=>0));
						## Check if this fid is already stored
						if (!$UserFiles->load('fid='.$fid.' AND uid='.$this->uid)){
							$UserFiles->insert(array('fid'=>$fid, 'uid'=>$this->uid, 'is_profile'=>1));
						}
						else{
							$UserFiles->update('fid='.$fid.' AND uid='.$this->uid, array('is_profile'=>1));
						}
					}
				}
				elseif (!empty($_POST['user_files']['selected_fid'])){
					$UserFiles	= new User_Files();
					$fid = (int)$_POST['user_files']['selected_fid'];
					$UserFiles->update('uid='.$this->uid, array('is_profile'=>0));
					## Check if this fid is already stored
					if (!$UserFiles->load('fid='.$fid.' AND uid='.$this->uid)){
						$UserFiles->insert(array('fid'=>$fid, 'uid'=>$this->uid, 'is_profile'=>1));
					}
					else{
						$UserFiles->update('fid='.$fid.' AND uid='.$this->uid, array('is_profile'=>1));
					}
				}
			}
			
			$this->addUserEndMsg('SUCCESS', $_POST['login'].' '._('has been successfully updated.'));
			//$this->redirectToSelf();
		}
	}
	
	function main(){
		$this->_view->user = $this->user;
		$this->_view->userIsOwner = $this->userIsOwner;
		$this->_view->profile_fid = null;
		if (!empty($this->user->uid)){
			$UserRoles = new User_Roles();
			$this->_view->user_roles = $UserRoles->getUserRoles($this->uid);
			$thisUserIsSuper = in_array('1', $this->_view->user_roles);
			$this->_view->noWriting = ($thisUserIsSuper && !$this->userIsSuperAdmin()) ||
				(!$this->userIsAdmin() && $this->uid != $this->getUser()->uid);
			$UserFiles = new User_Files();
			$this->_view->profile_fid = $UserFiles->selectValue('uid='.$this->user->uid.' AND is_profile=1', 'fid', '1 DESC LIMIT 0,1');
		}
		else
			$this->addUserEndMsg('ERRORS', _('Invalid user ID.'));
	}
	
	/**
	 * Before updating: check if login and email are free.
	 * @param array $values
	 * @return boolean
	 */
	function checkDuplicates($values){
		$initialLogin = $this->user->login;
		$initialEmail = $this->user->email;
		$login = $values['login'];
		if ($login!=$initialLogin){
			if ($this->user->load("login='$login'")){
				$this->addUserEndMsg('ERRORS', sprintf(_('The nickname "%s" is already taken.'), $login));
				return false;
			}
		}
		$email = $values['email'];
		if ($email!=$initialEmail){
			if ($this->user->load("email='$email'")){
				$this->addUserEndMsg('ERRORS', sprintf(_('The e-mail "%s" is already registered.'), $email));
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Account deletion
	 * @return boolean
	 */
	function userdel(){
		##Step 1: find his/her articles (if this user has written articles, you can't delete him/her --> delete its articles first)
		##! Attention: if any relationship exists between user and other tables into the database, you must manage the cases. By default,
		##  the core tables linked with user apply "on delete cascade"
		$Art = new Article();
		$nbArt = $Art->count("owner_id='$this->uid'");
		if ((int)$nbArt){
			if ($this->userIsOwner)
				$this->addUserEndMsg('ERRORS', sprintf(_('You own "%s" articles(s). You or an administrator must delete all these articles first.'), $nbArt));
			else
				$this->addUserEndMsg('ERRORS', sprintf(_('This user owns "%s" article(s). He/she or an administrator must delete all these articles first.'), $nbArt));
			return false;
		}
		##Step 2: delete user and "on cascade" lines in other tables and send a message in its mailbox.
		if ($this->user->delete("uid=$this->uid")){
			$message = sprintf(_('Hello %s,'), $this->user->firstname)."\n\n";
			$message.= _('This is a confirmation of your account deletion.')."\n";
			if (!$this->userIsOwner)
				$message.= _('Your account was deleted by an administrator. If you are not agree about this decision, you can use our "Contact Us" form.')."\n";
			$this->sendMail($this->user->email, null, _('User account deletion').' - Divorce Pour Tous', $message, 'mail/userdel');
			$this->addUserEndMsg('SUCCESS', sprintf(_('The user "%s" has been successfully deleted.'), $this->user->login));
			return true;
		}
		else{
			$this->addUserEndMsg('ERRORS', _('An error occured from the database.'));
			return false;
		}
	}
}