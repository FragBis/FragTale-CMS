<?php
namespace Bonz;
use Bonz\CMS\Article;

/**
 * 
 * @author fabrice
 *
 */
class View{
	/**
	 * @var string
	 */
	protected $_my_current_view;
	/**
	 * @var string
	 */
	protected $_current_script;
	/**
	 * @var string
	 */
	protected static $_layout_script;
	/**
	 * @var string
	 */
	protected static $_title;
	/**
	 * @var array of strings
	 */
	static protected $_css= array();
	/**
	 * @var array of strings
	 */
	static protected $_js = array();
	/**
	 * @var boolean
	 */
	protected $_is404	 = false;
	/**
	 * @var boolean
	 */
	protected $_isMeta	 = false;
	/**
	 * The very first instance of View, globally scoped, instanciated in index.php
	 * @var \Bonz\View
	 */
	protected static $_metaView;
	/**
	 * If this View is instanciated by another View (method "getBlock"), then the calling View is the parent
	 * @var \Bonz\View
	 */
	protected $_parentView;
	/**
	 * Instance of Article (for CMS page)
	 * @var \Bonz\CMS\Article
	 */
	protected $_article;
	
	/**
	 * HTML output to render
	 * @var string
	 */
	protected $_render;
	
	/**
	 * @desc This object stores all the usefull informations to render the Web page.
	 * The controller can set new properties intending to display specific informations.
	 * @param string $view_name
	 */
	function __construct($view_name=null){
		if (!is_a(self::$_metaView, __CLASS__)){
			## Default meta view settings:
			$this->_isMeta = true;
			self::$_metaView = $this;
			
			# Loading application params & set default view & layout params
			Application::loadIniParams();
			$ini_params = Application::getIniParams();
			
			$myCurrentView = !empty($_GET['my_current_view']) ? trim(trim($_GET['my_current_view']), '/') : '';
			$view_name = $myCurrentView ? $myCurrentView : (defined('LANDING_PAGE') ? LANDING_PAGE : 'home');
			if (defined('DEFAULT_PAGE_LAYOUT')){
				$this->setLayoutScript(TPL_ROOT.'/pages/'.DEFAULT_PAGE_LAYOUT.'.layout.phtml');
			}
			if (defined('DEFAULT_PAGE_TITLE')){
				$this->setTitle(DEFAULT_PAGE_TITLE);
			}
			if (!empty($ini_params['default_css_files'])){
				foreach ($ini_params['default_css_files'] as $script){
					$this->addCSS(WEB_ROOT.'/css/'.$script);
				}
			}
			if (!empty($ini_params['default_js_files'])){
				foreach ($ini_params['default_js_files'] as $script){
					$this->addJS(WEB_ROOT.'/js/'.$script);
				}
			}
		}
		# Instanciate the article associated to the view
		$this->_article = new Article();
		if (is_string($view_name)){
			$this->setViewName($view_name);
			$this->setCurrentScript(TPL_ROOT.'/views/'.$view_name.'.phtml');
		}
	}
	
	/**
	 * Run its controller if exists
	 */
	protected function runController(){
		if (file_exists(APP_ROOT.'/controllers/'.$this->getViewName().'.php')){
			Application::requireControllers($this->getViewName());
			$viewName = $this->getViewName()=='cms/default' ? 'cms/_default' : $this->getViewName();
			$className = '\\Bonz\\Controller\\'.str_replace(array(' ', '-'), '_', str_replace('/', '\\', $viewName));
			$controller = new $className($this);
			$controller->run();
		}
	}
	
	/**
	 * Render the web page
	 */
	function render(){
		ob_start();
		$view = $this;
		$this->runController();
		include $this->getCurrentScript();
		$this->_render = ob_get_clean();
		if ($this->_isMeta){
			# If this view is the "meta" view (the very first instance of View declared in index.php)
			# we will also load the layout
			if ($view->is404()){
				header('HTTP/1.0 404 Not Found');
				header('Status: 404 Not Found');
			}
			require_once $this->getLayoutScript();
		}
		else
			# For any else cases, this view is a block, so we return the output as string value
			return $this->_render;
	}
	
	/**
	 * Returns the HTML output of a controller/view include as a block into a parent controller/view
	 * @param string $block_name	The block name placed in the "views" folder
	 * @return string
	 */
	function getBlock($block_name){
		$block_name = trim($block_name, '/');
		$view = new View($block_name);
		$view->_parentView = $this;
		return $view->render();
	}
	
	### GETTERS ###
	/**
	 * @return \Bonz\View
	 */
	function getMetaView(){
		return self::$_metaView;
	}
	/**
	 * 
	 * @return \Bonz\View
	 */
	function getParentView(){
		return $this->_parentView;
	}
	/**
	 * @return string: The current view name
	 */
	function getViewName(){
		return $this->_my_current_view;
	}
	/**
	 * @return string: The full path of the current "phtml" view script
	 */
	function getCurrentScript(){
		if (!empty($this->_current_script) && file_exists($this->_current_script))
			return $this->_current_script;
		else{
			$this->_is404 = true;
			return PAGE_404;
		}
	}
	/**
	 * @return string: The full path of the selected "phtml" page layout file
	 */
	function getLayoutScript(){
		if (empty(self::$_layout_script))
			if (defined('DEFAULT_PAGE_LAYOUT'))
				return TPL_ROOT.'/pages/'.DEFAULT_PAGE_LAYOUT.'.layout.phtml';
			else
				return DEFAULT_LAYOUT;
		return self::$_layout_script;
	}
	/**
	 * @return string: HTML tag output giving CSS source files to include
	 */
	function getCssTags(){
		$tags = '';
		foreach (self::$_css as $script){
			$tags .= '<link rel="stylesheet" type="text/css" href="'.$script.'" />';
		}
		return $tags;
	}
	/**
	 * @return string: HTML tag output giving JS source files to include
	 */
	function getJsTags(){
		$tags = '';
		foreach (self::$_js as $script){
			$tags .= '<script type="text/javascript" src="'.$script.'"></script>';
		}
		return $tags;
	}
	/**
	 * @return string: The Web page title
	 */
	function getTitle(){
		return self::$_title;
	}
	/**
	 * @return \Bonz\CMS\Article
	 */
	function getArticle(){
		return $this->_article;
	}
	/**
	 * @return false: page not found
	 */
	function is404(){
		return $this->_is404;
	}
	
	### SETTERS ###
	/**
	 * Set the current view name
	 * @param string $view_name
	 */
	function setViewName($view_name){
		$this->_my_current_view = $view_name;
	}
	/**
	 * Set the full path of the current "phtml" view script. If the script doesn't exist, quote that is a 404 page.
	 * @param string $fullpath
	 */
	function setCurrentScript($fullpath){
		if (file_exists($fullpath))
			$this->_current_script = $fullpath;
		elseif (!$this->isCmsPage())
			$this->_is404 = true;
	}
	/**
	 * Set the full path of the selected "phtml" page layout file
	 * @param string $fullpath
	 */
	function setLayoutScript($fullpath){
		self::$_layout_script = $fullpath;
	}
	/**
	 * Set new article object
	 * @param \Bonz\CMS\Article $article
	 */
	function setArticle(Article $article){
		$this->_article = $article;
	}
	/**
	 * Add a CSS source file
	 * @param string $fullpath
	 */
	function addCSS($fullpath){
		if (!in_array($fullpath, self::$_css))
			self::$_css[] = $fullpath;
	}
	/**
	 * Add a JS source file
	 * @param string $fullpath
	 */
	function addJS($fullpath){
		if (!in_array($fullpath, self::$_js))
			self::$_js[] = $fullpath;
	}
	/**
	 * Set the Web page title
	 * @param string $title
	 */
	function setTitle($title){
		self::$_title = $title;
	}
	
	### Tools
	/**
	 * HTML output. The view returns the rendering output.
	 * @return string
	 */
	function __toString(){
		return $this->_render;
	}
	/**
	 * Round float value into formated string
	 * @param float $number		Value to round
	 * @param int	$decimal	Nb of decimals after sep
	 * @param char	$sep		Decimal separator
	 * @return string
	 */
	function round($number, $decimal=2, $sep='.'){
		return str_replace('.', $sep, number_format((float)$number, $decimal));
	}
	
	### CMS
	/**
	 * This function is launched in "$this->setCurrentScript()" function to determine if the current page is a CMS page.
	 * @return boolean
	 */
	function isCmsPage(){
		$this->_article->load("request_uri='$this->_my_current_view'");
		if (!empty($this->_article->aid)){
			$viewScript = APP_ROOT.'/templates/views/cms/'.$this->_article->view.'.phtml';
			if (file_exists($viewScript)){
				$this->setCurrentScript($viewScript);
				return true;
			}
			else
				$this->_current_script = PAGE_404;
		}
		return false;
	}
	
	/**
	 * Add a new message to throw into the user interface.
	 * @param string $type
	 * @param string $msg
	 */
	function addUserEndMsg($type, $msg){
		$type = strtoupper($type);
		if (!in_array($type, array('ERRORS', 'SUCCESS', 'WARNINGS'))){
			$msg = _('Attempt to store the following message in an unknown type').' "'.$type.'" : "'.$msg.'".';
			$_SESSION['USER_END_MSGS']['ERRORS'][] = $msg;
			return;
		}
		if (empty($_SESSION['USER_END_MSGS'][$type]) || !in_array($msg, $_SESSION['USER_END_MSGS'][$type])){
			$_SESSION['USER_END_MSGS'][$type][] = $msg;
		}
	}
	/**
	 * Display all stored user end messages.
	 * @return string
	 */
	function getUserEndMsgs(){
		$output = '';
		if (!empty($_SESSION['USER_END_MSGS']) && is_array($_SESSION['USER_END_MSGS'])){
			if (!empty($_SESSION['USER_END_MSGS']['ERRORS']) && is_array($_SESSION['USER_END_MSGS']['ERRORS'])){
				$output .= '<div class="user_end_error">';
				foreach ($_SESSION['USER_END_MSGS']['ERRORS'] as $msg){
					Application::catchError($msg, __CLASS__, __FUNCTION__, __LINE__);
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
			if (!empty($_SESSION['USER_END_MSGS']['SUCCESS']) && is_array($_SESSION['USER_END_MSGS']['SUCCESS'])){
				$output .= '<div class="user_end_success">';
				foreach ($_SESSION['USER_END_MSGS']['SUCCESS'] as $msg){
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
			if (!empty($_SESSION['USER_END_MSGS']['WARNINGS']) && is_array($_SESSION['USER_END_MSGS']['WARNINGS'])){
				$output .= '<div class="user_end_warning">';
				foreach ($_SESSION['USER_END_MSGS']['WARNINGS'] as $msg){
					$output .= '<p>'.$msg.'</p>';
				}
				$output .= '</div>';
			}
		}
		unset($_SESSION['USER_END_MSGS']);
		return $output;
	}
	
	/**
	 * Check if user is logged in
	 * @return boolean
	 */
	function userIsLogged(){
		return !empty($_SESSION['REG_USER']['uid']);
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsAdmin(){
		if (!$this->userIsLogged()) return false;
		if (empty($_SESSION['REG_USER']['ROLES'])) return false;
		return in_array(1, $_SESSION['REG_USER']['ROLES']) || in_array(2, $_SESSION['REG_USER']['ROLES']);
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsSuperAdmin(){
		if (!$this->userIsLogged()) return false;
		if (empty($_SESSION['REG_USER']['ROLES'])) return false;
		return in_array(1, $_SESSION['REG_USER']['ROLES']);
	}
}