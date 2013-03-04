<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class Article_Comments extends Table{
	protected $_tablename = 'article_comments';
	/**
	 * Primary key
	 * @var int
	 */
	var $acid;
	/**
	 * Reference to Article
	 * @var int
	 */
	var $aid;
	/**
	 * Reference to User
	 * @var int
	 */
	var $uid;
	/**
	 * Comment
	 * @var string
	 */
	var $message;
	/**
	 * Date of post
	 * @var date
	 */
	var $edit_date;
	
	
	function getComments($aid, $asc=true){
		$User = new User();
		$query = 'SELECT C.acid, C.aid, C.message, C.edit_date, U.uid, U.login AS user_login FROM '.$this->getFullTableName().
			' AS C INNER JOIN '.$User->getFullTableName().' AS U ON U.uid = C.uid '.
			'WHERE C.aid = '.$aid.' ORDER BY C.edit_date '.($asc?'ASC':'DESC');
		unset($User);
		return $this->_db->getTable($query);
	}
}