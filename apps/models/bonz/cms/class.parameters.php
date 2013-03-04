<?php
namespace Bonz\CMS;
use \Bonz\Db\Table;

class Parameter extends Table{
	protected $_tablename = 'parameters';
	/**
	 * Primary key
	 * @var string
	 */
	var $param_key;
	/**
	 * Any value
	 * @var string
	 */
	var $param_value;
}