<?php 
namespace FragTale\Controller\Ajax;
use FragTale\Controller;

/**
 * 
 * @author fragbis
 *
 */
class Json extends Controller{
	
	/**
	 * In any case, superadmin and the owner of a row has any rights
	 * @var array
	 */
	protected $writeClassRules = array(
			//'class'=> 'rights for admins'.'rights for editors'.'rights for registered users', (where 7 is rw, 5 is ro and <5 nothing)
			'ads'=>755,
			'article_category'=>755,
			'article_comments'=>775,
			'article_files_user_reactions'=>755,
			'article'=>755,
			'files'=>755,
			'message'=>775,
			'parameters'=>555,
			'role'=>555,
			'user_files'=>555,
			'user_roles'=>755,
			'user'=>755
	);
	/**
	 * User ID field name as owner of loaded object
	 * @var array
	 */
	protected $classOwnerField = array(
			'ads'=>'author_id',
			'article_category'=>'cre_uid',
			'article_comments'=>'uid',
			'article_files_user_reactions'=>'uid',
			'article'=>'owner_id',
			'message'=>'recipient_id',
			'user_files'=>'uid',
			'user_roles'=>'uid',
			'user'=>'uid'
	);
	
	/**
	 * For the following classes, the uid (user id) must be automatically set by
	 * the uid of logged user
	 * @var array
	 */
	protected $classUpdaterField = array(
			'chatbox'=>'uid',
			'article_comments'=>'uid',
			'article_files_user_reactions'=>'uid',
			'article'=>'uid',
			'message'=>'recipient_id',
			'user'=>'upd_uid',
	);
	
	/**
	 * Object to load by class and args given by http request
	 * @var Object
	 */
	protected $object;
	
	function initialize(){
		#Since JSON data don't need any layout, we set the "clean" one.
		$this->_view->setLayoutScript(TPL_ROOT.'/pages/clean.layout.phtml');
	}
	function main(){
		#Calling precisely object & method to execute
		if (isset($_REQUEST['model']) && isset($_REQUEST['class']) && isset($_REQUEST['method'])){
			$fullClassName = '\\'.$_REQUEST['model'].'\\'.$_REQUEST['class'];
			if (!class_exists($fullClassName))
				$fullClassName = '\\FragTale\\'.$_REQUEST['model'].'\\'.$_REQUEST['class'];
			if (class_exists($fullClassName)){
				$obj = new $fullClassName();
				$method = $_REQUEST['method'];
				if (method_exists($obj, $method)){
					$this->_view->results = $_REQUEST;
					if (in_array($method, array('delete', 'put', 'insert', 'update', 'set')) && !$this->userIsLogged()){
						return $this->throwError(_('User is not logged in'));
					}
					if ($this->userIsLogged() && in_array($method, array('delete', 'update', 'set'))){
						if (strtolower($_REQUEST['model'])=='cms' && !$this->checkClassAccess($_REQUEST['class']))
							return $this->throwError(_('Action not allowed'));
					}
					$args = array();
					if ($method=='delete'){
						foreach ($_REQUEST as $key=>$value){
							if (!in_array($key, array('my_current_view', 'model', 'class', 'method'))){
								$value = str_replace("'", "''", $value);
								$args[] = "$key='$value'";
							}
						}
						$args = implode(' AND ', $args);
					}
					else{
						foreach ($_REQUEST as $key=>$value){
							if (!in_array($key, array('my_current_view', 'model', 'class', 'method'))){
								$args[$key] = $value;
							}
						}
					}
					if (in_array($method, array('put', 'insert', 'update', 'set'))){
						$classname = strtolower($_REQUEST['class']);
						if (isset($this->classUpdaterField[$classname])){
							$uidfield = $this->classUpdaterField[$classname];
							$args[$uidfield] = $this->getUser()->uid;
						}
					}
					$result = $obj->$method($args);
					$this->_view->results['result'] = $result;
					return;
				}
			}
			return $this->throwError(_('Error occured'));
		}
		#Else, send error
		$this->throwError(_('Bad Request'));
	}
	/**
	 * Only for update and delete
	 * @param string $class	Class name
	 * @return boolean
	 */
	function checkClassAccess($class){
		/*Super Admin can do naything*/
		if ($this->userIsSuperAdmin()) return true;
		$class=strtolower($class);
		/*If class is not declare: forbidden*/
		if (!isset($this->writeClassRules[$class])) return false;
		## Check if the logged user is the owner of the object
		if ($userfieldname = (isset($this->classOwnerField[$class]) ? $this->classOwnerField[$class] : null)){
			if (isset($_REQUEST[$userfieldname]))
				$fieldval = str_replace("'", "''", $_REQUEST[$userfieldname]);
			elseif ($class=='message')
				$fieldval = $this->getUser()->uid;
			else return false;
			$arg = $userfieldname."='$fieldval'";
			$fullclassname = "\\FragTale\\CMS\\".$class;
			$obj = new $fullclassname();
			if (!$obj->load($arg)) return false;
			## The owner of the object is the logged user, so he/she can write in any case
			if ($obj->$userfieldname!=$this->getUser()->uid) return true;
		}
		## Check rights
		switch ($this->writeClassRules[$class]){
			case 777: return $this->userIsLogged();
			case 775: return $this->userCanEditArticles();
			case 755: return $this->userIsAdmin();
			default: return false;
		}
		return false;
	}
	function throwError($message){
		header('HTTP/1.0 404 Not Found');
		$this->_view->results['message']= $message;
		$this->_view->results['result']	= 0;
		return false;
	}
}