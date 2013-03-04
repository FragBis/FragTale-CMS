<?php
namespace Bonz\Db;
use Bonz\Application as APP;
use Bonz\Db\Adapter;
/**
 * @desc This class let you declare a DB structure. All public vars must be the table fields.
 * @author fragbis
 */
abstract class Table{
	
	/**
	 * Instance of Bonz\Db\Adapter
	 * @var Bonz\Db\Adapter
	 */
	protected $_db;
	
	/**
	 * The DB Table name
	 * @var string
	 */
	protected $_tablename;
	
	/**
	 * The table prefix set into the DB ini file
	 * @var string
	 */
	protected $_tableprefix;
	
	/**
	 * Pass the connection name as declared in ini file.
	 * @param string||\Database\Adapter $connection
	 */
	function __construct($connection=''){
		if (empty($connection) || is_string($connection))
			$this->_db = Adapter::getInstanceOf($connection);
		elseif (is_a($connection, 'Adapter'))
			$this->_db = $connection;
		$this->_tableprefix = $this->_db->getTablePrefix();
	}
	
	function getFullTableName(){
		return $this->_tableprefix.$this->_tablename;
	}
	
	function escape($string){return trim(str_replace("'", "''", $string));}
	
	/**
	 * @desc Insert new row(s) into DB table.
	 * @param array		$values		array('fieldname' => value, ...)
	 * @return Boolean or nb of affected rows
	 */
	function insert($values){
		if (empty($values)||!is_array($values))
			return false;
		foreach ($values as $key=>$value){
			if (!property_exists($this, $key)){
				APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
				unset($values[$key]);
			}
			else $values[$key] = $this->escape($value);
		}
		if (empty($values)) return false;
		$fields = array_keys($values);
		$query = 'INSERT INTO '.$this->getFullTableName().' ('.implode(',', $fields).') VALUES (\''.implode('\',\'', $values).'\');';
		return $this->_db->exec($query);
	}
	
	/**
	 * @desc Update row into DB table.
	 * @param string	$where			Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$values			array('fieldname' => value, ...)
	 * @return boolean
	 */
	function update($where, $values){
		if (is_array($values)){
			foreach ($values as $key=>$value){
				if (!property_exists($this, $key)){
					APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
					unset($values[$key]);
				}
				else $values[$key] = $this->escape($value);
			}
		}
		else $values = null;
		if (empty($values)) return false;
		$fields = array_keys($values);
		$set = array();
		foreach ($values as $key=>$value){
			$set[] = $key.' = \''.$value.'\'';
		}
		$query = 'UPDATE '.$this->getFullTableName().' SET '.implode(', ', $set).' WHERE '.$where.';';
		return $this->_db->exec($query);
	}
	
	/**
	 * @desc Delete row(s) into DB table. Warning: we don't check (un)escaped strings.
	 * @param string	$where			Explicite the condition(s) into a string. Example: "id=1"
	 * @return Sql Statement
	 */
	function delete($where){
		return $this->_db->exec('DELETE FROM '.$this->getFullTableName().' WHERE '.$where.';');
	}
	
	/**
	 * @desc For a single select query (no "group by": pass it into $where).
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 * @param bool		$distinct	if true "Select Distinct"
	 * @return SQL Statement
	 */
	function select($where='', $fields=array(), $order='', $distinct=false){
		$select = array();
		if (!empty($fields)){
			if (is_array($fields)){
				foreach ($fields as $alias=>$fieldname){
					if (!property_exists($this, $fieldname)){
						APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
						unset($fields[$alias]);
					}
					else $select[] = $fieldname.(is_numeric($alias) ? '' : ' AS '.$alias);
				}
			}
			else{
				if (!property_exists($this, $fields)){
					APP::catchError('Invalid use of field "'.$fields.'"', get_class($this), __FUNCTION__, __LINE__);
				}
				else $select[] = $fields;
			}
		}
		if (empty($select))	$select = '*';
		else				$select = implode(',', $select);
		if (!empty($where)) $where = 'WHERE '.$where;
		if (!empty($order)) $order = 'ORDER BY '.$order;
		$query = 'SELECT '.($distinct?'DISTINCT ':'').$select.' FROM '.$this->getFullTableName().' '.$where.' '.$order;
		return $this->_db->getTable($query);
	}
	
	/**
	 * Same as select, but distinct.
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 */
	function selectDistinct($where='', $fields=array(), $order=''){
		return self::select($where, $fields, $order, true);
	}
	
	/**
	 * Same as select, but fetch first (and only one) row.
	 * @param string	$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param array		$fields		array('alias1'=>'field1','alias2'=>'field2',...)
	 * @param string	$order		Explicite the columns order into a string (with ASC & DESC).
	 */
	function selectRow($where, $fields=array(), $order=''){
		if ($data = self::select($where, $fields, $order, true))
			return reset($data);
		return array();
	}
	
	/**
	 * Same as select, but fetch first (and only one) value.
	 * @param string			$where		Explicite the condition(s) into a string. Example: "id=1"
	 * @param string||array		$field		'fieldname' || array('alias'=>'fieldname')
	 */
	function selectValue($where, $field){
		$select = '';
		if (is_string($field)){
			if (!property_exists($this, $field)) return null;
			$select = $field;
		}
		elseif (is_array($field)){
			foreach ($field as $alias=>$fieldname){
				if (!property_exists($this, $fieldname)){
					APP::catchError('Invalid use of field "'.$key.'"', get_class($this), __FUNCTION__, __LINE__);
					unset($field[$alias]);
				}
				else $select = $fieldname.(!is_numeric($alias) ? ' AS '.$alias : '');
			}
			if (empty($field)) return null;
		}
		else return null;
		return $this->_db->getScalar("SELECT $select FROM ".$this->getFullTableName()." WHERE $where;");
	}
	
	/**
	 * Fill in the database property values of this database object
	 * @param string $where
	 * @param string $order
	 * @return boolean true on success
	 */
	function load($where, $order=''){
		$row = $this->selectRow($where);
		if (empty($row))
			return false;
		foreach ($row as $key=>$value)
			if (property_exists($this, $key))
				$this->$key = $value;
		return true;
	}
	
	/**
	 * Total number of rows
	 * @return scalar
	 */
	function count(){
		return $this->_db->getScalar('SELECT COUNT(*) FROM '.$this->getFullTableName());
	}
}