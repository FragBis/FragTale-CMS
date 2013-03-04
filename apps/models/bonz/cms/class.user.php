<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class User extends Table{
	protected $_tablename = 'user';
	/**
	 * Primary key
	 * @var int
	 */
	var $uid;
	/**
	 * Is user active ?
	 * @var bit
	 */
	var $active;
	/**
	 * User's pseudonym
	 * @var string
	 */
	var $login;
	/**
	 * His e-mail address
	 * @var string
	 */
	var $email;
	/**
	 * MD5 encoding
	 * @var string
	 */
	var $password;
	/**
	 * User's first name
	 * @var string
	 */
	var $firstname;
	/**
	 * User's last name
	 * @var string
	 */
	var $lastname;
	/**
	 * User's birthday
	 * @var Date
	 */
	var $bir_date;
	/**
	 * Reference to User that created this User (not only himself)
	 * @var int
	 */
	var $cre_uid;
	/**
	 * Reference to User that last edited user profile
	 * @var int
	 */
	var $upd_uid;
	/**
	 * Creation date
	 * @var Date
	 */
	var $cre_date;
	/**
	 * Last edit date
	 * @var Timestamp
	 */
	var $upd_date;
}