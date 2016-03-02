<?php
namespace FragTale\Controller\Admin;
use FragTale\Controller\Admin;
use FragTale\CMS\User;
use FragTale\CMS\Message;
use FragTale\CMS\Parameters;

/**
 * @author fabrice
 */
class Messages extends Admin{
	/**
	 * @var Message
	 */
	var $Msg;
	/**
	 * @var User
	 */
	var $Usr;
	function initialize(){
		$this->Msg	= new Message();
		$this->Usr		= new User();
		$this->addJS(WEB_ROOT.'/js/admin/datagrid.js');
		$this->setTitle(_('Letter Box'));
	}
	function main(){
		$uid	= $this->getUser()->uid;
		$users	= array();
		foreach ($this->Usr->selectDistinct('uid<>'.$uid, null, 'login ASC') as $usr){
			$users[$usr['uid']] = $usr;
		}
		$this->_view->allUsers	= $users;
		$this->_view->allMsgs	= array();
		$allMessages = $this->Msg->selectDistinct('sender_id='.$uid.' OR recipient_id='.$uid, null, 'send_date DESC');
		foreach ($allMessages as $msg){
			$altUser= $msg['sender_id']==$uid ? $users[$msg['recipient_id']] : $users[$msg['sender_id']];
			$altUid	= $altUser['uid'];
			if (empty($this->_view->allMsgs[$altUid]['user'])){
				$this->_view->allMsgs[$altUid]['user']	= $altUser;
				$this->_view->allMsgs[$altUid]['unread']= 0;
			}
			$this->_view->allMsgs[$altUid]['messages'][$msg['mid']]	= $msg;
			if (empty($msg['opened']) && $msg['sender_id']!=$uid){
				$this->_view->allMsgs[$altUid]['unread']++;
			}
		}
	}
	function doPostBack(){
		if (isset($_POST['new_msg'])){
			if (empty($_POST['recipient_id'])){
				$this->addUserEndMsg('ERRORS', _('Choose a recipient'));
				return false;
			}
			$body2match = empty($_POST['body']) ? null : trim(strip_tags($_POST['body']));
			if (empty($body2match)){
				$this->addUserEndMsg('ERRORS', _('Please type a message'));
				return false;
			}
			if (!$this->Msg->insert(array(
				'send_date'		=>date('Y-m-d H:i:s'),
				'sender_id'		=>$this->getuser()->uid,
				'recipient_id'	=>$_POST['recipient_id'],
				'body'			=>$_POST['body']
			)))
				$this->addUserEndMsg('ERRORS', _('An error occured'));
			else{
				$recipient = $this->Usr->selectRow('uid='.$_POST['recipient_id']);
				$Param	= new Parameters();
				$noreply= $Param->selectValue("param_key='NOREPLY_EMAIL'", 'param_value');
				$from	= $this->getUser()->login.' - '.($noreply ? $noreply : '<'.$this->getUser()->email.'>');
				$this->sendMail(
					$recipient['login'].' <'.$recipient['email'].'>',
					$from,
					_('New message from').' '.$this->getUser()->login,
					$_POST['body'],
					//'mail/messages'
					'mail/default'
				);
			}
			$this->redirectToSelf();
		}
	}
}