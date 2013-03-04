<?php
namespace Bonz\Controller;
use Bonz\Controller;
use Bonz\Db\Adapter;
use Bonz\CMS\User;
use Bonz\CMS\Parameter;
use Bonz\CMS\Article;
use Bonz\CMS\Article_History;
use Bonz\CMS\Article_Category;


/**
 * Each admin controllers must inherit from this class
 * @author fabrice
 *
 */
class Admin extends Controller{
	
	/**
	 * Custom run for admin pages
	 * @see \Bonz\Controller::run()
	 */
	function run(){
		if (!$this->checkRoles())
			$this->redirect(ADMIN_WEB_ROOT.'/login');
		$this->initialize();
		if ($this->doPostBack()){
			if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], ADMIN_WEB_ROOT)!==false)
				$this->redirect($_SERVER['HTTP_REFERER']);
			else
				$this->redirect(ADMIN_WEB_ROOT);
		}
		$this->main();
	}
	
	function main(){
		$this->_view->setTitle(_('Dashboard'));
		$this->getStats();
	}
	
	/**
	 * Only users with roles 1 & 2 can access to the admin pages
	 * To be overrided for specific pages allowed only for super admin
	 * @return boolean
	 */
	function checkRoles(){
		#Allowed for admins
		if ($this->userIsAdmin()) return true;
		#Check from DB if a page has been authorized
		if ($this->checkDbRoles()) return true;
		$_SESSION['USER_END_MSGS']['ERRORS'][] = _('You are not allowed to access the entire administration space.');
		return false;
	}
	/**
	 * Macth permission from database
	 * @return boolean
	 */
	function checkDbRoles(){
		$Article = new Article();
		$request_uri = trim($_GET['my_current_view'], '/');
		if ($Article->load("request_uri='$request_uri'")){
			if (!empty($Article->access) && $this->view->userIsLogged()){
				foreach ($_SESSION['REG_USER']['ROLES'] as $rid){
					#For basic admin roles, the power is descendant
					if (in_array($Article->access, array(1, 2, 3)) && $rid>=$Article->access)
						return true;
					## For other roles, their role ID must match the authorization, knowing that an account can have multiple roles
					elseif ($rid==$Article->access)
						return true;
				}
			}
		}
		return false;
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
		
		$param = new Parameter();
		if ($param->load("param_key='FILES_NOT_IN_DB'"))
			$this->_view->files['not_in_db'] = @unserialize($param->param_value);
		if ($param->load("param_key='FILES_NOT_IN_DIR'"))
			$this->_view->files['not_in_dir'] = @unserialize($param->param_value);
	}
}

