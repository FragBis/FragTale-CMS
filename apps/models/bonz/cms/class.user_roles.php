<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class User_Roles extends Table{
	protected $_tablename = 'user_roles';
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Reference to Role
	 * @var int
	 */
	var $rid;
	
	/**
	 * Fetch single array filled with a given User ID
	 * @param int $uid
	 * @return array of int
	 */
	function getUserRoles($uid){
		$roles = $this->select("uid='$uid'");
		$rids = array();
		foreach ($roles as $role)
			$rids[] = $role['rid'];
		return $rids;
	}
}