<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class Chatbox extends Table{
	protected $_tablename = 'chatbox';
	/**
	 * Primary key
	 * @var int
	 */
	var $tid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * @var string
	 */
	var $message;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $edit_date;
	
	##### Like REST API system (put/get, no need to update here)
	/**
	 * @desc Fetch a list of Chat (usually called by AJAX or REST API systems).
	 * By default, the method returns the last 5 posts.
	 * @param array $values Any array containing the interesting key(s)
	 * @return array
	 */
	function get($values=array()){
		$User = new User();
		#Base query
		$sql = 'SELECT DISTINCT CB.*, '.
				'U.login AS user_login, '.
				'U.email AS user_email, '.
				'U.firstname AS user_firstname, '.
				'U.lastname AS user_lastname, '.
				'U.active AS user_is_active '.
			'FROM '.$this->getFullTableName().' AS CB INNER JOIN '.$User->getFullTableName().' AS U ON CB.uid = U.uid ';
		#Order direction (asc/desc)
		$direction = isset($values['direction']) && in_array($values['direction'], array('desc', 'DESC')) ? 'DESC' : 'ASC';
		#Apply conditions
		if (!empty($values['tid'])){
			if (empty($values['previous']))
				$sql .= 'WHERE CB.tid > '.$values['tid'].' ORDER BY CB.tid '.$direction;
			else
				$sql .= 'WHERE CB.tid < '.$values['tid'].' ORDER BY CB.edit_date DESC LIMIT 0, 10';
		}
		elseif (!empty($values['limit']))
			$sql .= 'ORDER BY CB.tid '.$direction.' LIMIT 0, '.$values['limit'];
		else
			$sql .= 'WHERE CB.tid > (SELECT MAX(tid) - 10 FROM '.$this->getFullTableName().') ORDER BY CB.tid ASC LIMIT 0, 10';
		#Parse date
		$data = $this->_db->getTable($sql);
		foreach ($data as $i=>$row){
			$data[$i]['edit_date']	= date('d/m/Y - H:i:s', strtotime($row['edit_date']));
			$data[$i]['message']	= str_ireplace(array('<script', '</script>'), '', trim($data[$i]['message']));
		}
		return $data;
	}
	
	/**
	 * @desc Insert new chat.
	 * Note: the UID is only from the Session User. Cannot insert without authentication.
	 * @param array $values
	 */
	function put($values){
		if (!isset($_SESSION['REG_USER']['uid'])) return false;
		if (!empty($values['message'])){
			$fields['uid'] = (int)$_SESSION['REG_USER']['uid'];
			$fields['message'] = $values['message'];
			$this->insert($fields);
			return true;
		}
		return false;
	}
}