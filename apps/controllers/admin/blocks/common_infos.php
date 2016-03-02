<?php
namespace FragTale\Controller\Admin\Blocks;
use FragTale\Controller\Admin;
use FragTale\Db\Table;

use FragTale\Db\Adapter;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;
use FragTale\CMS\Article_History;
use FragTale\CMS\Article_Category;

/**
 * @author fabrice
 */
class Common_Infos extends Admin{
	
	function main(){
		$this->getStats();
	}
	/**
	 * Few stats & activities
	 */
	function getStats(){
		$db = Adapter::getInstanceOf();
		$user = new User();
		$this->_view->newUsers = $user->select('cre_date > DATE_SUB(now(), INTERVAL 10 DAY)');//Last users created from 10 days
		
		$category = new Article_Category();
		$this->_view->newCategories = $category->select('cre_date > DATE_SUB(now(), INTERVAL 10 DAY)');
		
		$articleH = new Article_History();
		$this->_view->newArticles = $db->getTable('SELECT * FROM '.$articleH->getFullTableName().' GROUP BY aid HAVING MIN(edit_date) > DATE_SUB(now(), INTERVAL 10 DAY)');
		
		$param = new Parameters();
		if ($param->load("param_key='FILES_NOT_IN_DB'"))
			$this->_view->files['not_in_db'] = @unserialize($param->param_value);
		if ($param->load("param_key='FILES_NOT_IN_DIR'"))
			$this->_view->files['not_in_dir'] = @unserialize($param->param_value);
	}
}

