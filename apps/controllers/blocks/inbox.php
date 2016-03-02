<?php
namespace FragTale\Controller\Blocks;
use FragTale\Controller;
use FragTale\CMS\Message;

/**
 * @author fabrice
 */
class Inbox extends Controller{
	static $inbox;
	
	function main(){
		if (!$this->userIsLogged()){
			return;
		}
		if (!isset(self::$inbox)){
			$Msg = new Message();
			self::$inbox = $Msg->count('recipient_id='.$this->getUser()->uid.' AND IFNULL(opened, 0) = 0');
		}
		$this->_view->inbox = self::$inbox;
	}	
}
