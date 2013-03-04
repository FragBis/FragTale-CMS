<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class Role extends Table{
	protected $_tablename = 'role';
	/**
	 * Primary key
	 * @var int
	 */
	var $rid;
	/**
	 * Role name
	 * @var string
	 */
	var $name;
	/**
	 * Short explaination
	 * @var string
	 */
	var $summary;
}