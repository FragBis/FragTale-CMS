<?php
namespace FragTale\Controller;
use FragTale\Db\Table;
use FragTale\Controller;
use FragTale\Db\Adapter;
use FragTale\CMS\User;
use FragTale\CMS\Parameters;
use FragTale\CMS\Article;
use FragTale\CMS\Article_History;
use FragTale\CMS\Article_Category;


/**
 * Each admin controllers must inherit from this class
 * IMPORTANT!: You must consider the rules defined into the $rules array. It must contain all restricted admin pages
 * @author fabrice
 *
 */
class Admin extends Controller{
	
	/**
	 * Bind roles and view names that are strictly reserved
	 * @var array
	 */
	protected $rules = array(
		#Only superadmin
		1	=>array('system'),
		# Admin
		2	=>array('article_categories', 'article_category', 'forms/article_category'),
		# Front-end user
		//3	=>array('users', 'articles', 'article', 'forms/article', 'elements/article_history', 'file_manager'),
		# The user must be at least registered
		//4	=>array('index', 'header', 'user', 'forms/user', 'elements/user_info', 'messages', 'user/validate'),
	);
	
	/**
	 * Custom run for admin pages
	 * @see \FragTale\Controller::run()
	 */
	function run(){
		if (!in_array($this->_view->getViewName(), array('admin/forms/login', 'admin/login'))){
			if (!$this->userIsLogged() || !$this->checkRules())
				$this->redirect(WEB_ROOT.'/login');
		}
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
	}
	/**
	 * Check if the current logged user is allowed to access the defined admin page.
	 * @param string	$page	Admin page name. If empty, the function will check the current page
	 * @return boolean
	 */
	function checkRules($page=null){
		#Check from DB if a page has been authorized
		$viewRole = $this->getRole($page);
		if (empty($viewRole)){
			return true;
		}
		$userRole = $this->getUser()->getStrongestRole();
		if ($rid = !empty($userRole['rid']) ? $userRole['rid'] : null){
			# In any case, if rid > 4, return false;
			if ($rid > 4) return false;
			return $rid <= $viewRole;
		}
		$this->addUserEndMsg('ERRORS', _('You are not allowed to access this page or this content.'));
		return false;
	}
	/**
	 * Read out the parameters to find if rules has been stored
	 */
	function getRole($page=null){
		static $admin_rules, $loaded;
		if (empty($admin_rules) && empty($loaded)){
			$params = new Parameters();
			$params->load("param_key='ADMIN_PAGE_RULES'");
			if (!empty($params->param_value)){
				$admin_rules = unserialize($params->param_value);
			}
			$loaded = true;
		}
		if ($role = $this->getViewRole($admin_rules, $page))
			return $role;
		return $this->getViewRole();
	}
	/**
	 * 
	 * @return int|NULL
	 */
	function getViewRole($rules=null, $page=null){
		if (empty($rules)){
			$rules = $this->rules;
		}
		$view2Match = $page ? $page : trim(str_replace('admin', '', $this->getViewName()), '/');
		if (empty($view2Match)){
			$view2Match = 'index';
		}
		foreach ($rules as $rid=>$views){
			if (in_array($view2Match, $views)){
				return $rid;
			}
		}
		while (!empty($view2Match)){
			$view2Match = explode('/', trim($view2Match, '/'));
			array_pop($view2Match);
			$view2Match = implode('/', $view2Match);
			foreach ($rules as $rid=>$views){
				if (in_array($view2Match, $views)){
					return $rid;
				}
			}
		}
		return null;
	}
	
	/**
	 * A custom function returning the data grid results into the admin panel lists (articles, users etc.)
	 * @param \FragTale\Db\Table $object
	 * @return array
	 */
	function getGridSortedResult(\FragTale\Db\Table $dbTable, $where=null){
		$order	= !empty($_GET['order'])? $_GET['order'].(!empty($_GET['desc']) ? ' DESC' : ' ASC'): null;
		$count	= !empty($_GET['count'])? $_GET['count'] : (!empty($_COOKIE['gridcount']) ? $_COOKIE['gridcount'] : 10);
		$page	= !empty($_GET['page'])	? $_GET['page']-1	: 0;
		
		$totalCount = $dbTable->count($where);
		$this->_view->_datagrid['rows']['total']	= $totalCount;
		$this->_view->_datagrid['pages']['total']	= ceil($totalCount/$count);
		$this->_view->_datagrid['rows']['current']	= $count;
		$this->_view->_datagrid['pages']['current']	= $page;
		
		$conditions = ($order ? ' '.$order : '1');
		if ($page*$count < $totalCount){
			$conditions .= (' LIMIT '.($page*$count).', '.$count);
		}
		return $dbTable->selectDistinct($where, null, $conditions);
	}
}

