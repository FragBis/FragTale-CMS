<?php
namespace Bonz;
/**
 * Main class to be extended by all controllers.
 * @author fdant
 */
abstract class Controller{
	/**
	 * @var \Bonz\View
	 */
	protected $_view;
	/**
	 * @var \Bonz\View
	 */
	protected $_meta_view;
	/**
	 * @var \Bonz\CMS\Article
	 */
	protected $_article;
	/**
	 * @var \Bonz\CMS\Article
	 */
	protected $_meta_article;


	/**
	 * @desc Constructor (by default, nothing to do, no param to pass).
	 * @param View $view
	 */
	final function __construct(View $view){
		$this->_view = $view;
		$this->_article = $view->getArticle();
		$this->_meta_view = $view->getMetaView();
		$this->_meta_article = $view->getMetaView()->getArticle();
	}

	/**
	 * To be overrided in each inherited class.
	 * Write in the specific codes for each page.
	 */
	protected function main(){}
	
	/**
	 * Execute any post back procedures on form submission. By default, the end of this method will redirect the page
	 * to self if it returns true.
	 * @return boolean
	 */
	protected function doPostBack(){return false;}
	
	/**
	 * Set any wishable variables. Use this function as a preprocess method.
	 */
	protected function initialize(){}
	
	/**
	 * Executed in bootstrap to run the main controller's processes.
	 * By default, execution order is the following:
	 * 	Controller::declarations()
	 * 	Controller::doPostBack()	if returns true --> Controller::redirectToSelf()
	 * 	Controller::main()
	 * @final Not overridable.
	 */
	public function run(){
		$this->initialize();
		if (!empty($_POST) && $this->doPostBack())
			$this->redirectToSelf();
		$this->main();
	}

	/**
	 * Include one or all PHP files (recursively) from a given directory placed in the models' folder.
	 * @param string $model		File or directory placed in 'app/models'
	 */
	final public function loadModel($model=''){
		Application::requireFolder('apps/models/'.$model);
	}

	final public function catchError($msg, $class, $function, $line){
		Application::catchError($msg, $class, $function, $line);
	}
	
	########################## SETTERS ##############################
	/**
	* Change the layout file name (without .phtml extension)
	* @param string $name
	*/
	final public function setLayout($name){
		self::$viewThemeLayout = $name;
	}

	########################## GETTERS ##############################
	/**
	* The main HTML layout file name (without .phtml extension).
	* @return string
	*/
	final public function getLayout(){
		return self::$viewThemeLayout;
	}
	########################## From View's methods ##################
	/**
	* Check if user is logged in
	* @return boolean
	*/
	function userIsLogged(){
		return $this->_view->userIsLogged();
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsAdmin(){
		return $this->_view->userIsAdmin();
	}
	/**
	 * Check if session user is admin
	 * @return boolean
	 */
	function userIsSuperAdmin(){
		return $this->_view->userIsSuperAdmin();
	}
	/**
	 * Add a new message to throw into the user interface.
	 * @param string $type
	 * @param string $msg
	 */
	function addUserEndMsg($type, $msg){
		return $this->_view->addUserEndMsg($type, $msg);
	}
	/**
	 * Returns the HTML output of a controller/view include as a block into a parent controller/view
	 * @param string $block_name	The block name placed in the "views" folder
	 * @return string
	 */
	function getBlock($block_name){
		return $this->_view->getBlock($block_name);
	}

	### GETTERS ###
	/**
	 * @return \Bonz\View
	*/
	function getMetaView(){
		return $this->_view->getMetaView();
	}
	/**
	 * @return \Bonz\View
	 */
	function getParentView(){
		return $this->_view->getParentView();
	}
	/**
	 * @return string: The current view name
	 */
	function getViewName(){
		return $this->_view->getViewName();
	}
	/**
	 * @return string: The full path of the selected "phtml" page layout file
	 */
	function getLayoutScript(){
		return $this->_view->getLayoutScript();
	}
	/**
	 * @return string: The Web page title
	 */
	function getTitle(){
		return $this->_view->getTitle();
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
		return $this->_view->is404();
	}

	### SETTERS ###
	/**
	 * Set the current view name
	* @param string $view_name
	*/
	function setViewName($view_name){
		$this->_view->setViewName($view_name);
	}
	/**
	 * Set the full path of the selected "phtml" page layout file
	 * @param string $fullpath
	 */
	function setLayoutScript($fullpath){
		$this->_view->setLayoutScript($fullpath);
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
		$this->_view->addCSS($fullpath);
	}
	/**
	 * Add a JS source file
	 * @param string $fullpath
	 */
	function addJS($fullpath){
		$this->_view->addJS($fullpath);
	}
	/**
	 * Set the Web page title
	 * @param string $title
	 */
	function setTitle($title){
		$this->_view->setTitle($title);
	}

	############ Tools	############
	/**
	 * Immediately redirect the web page.
	 * @param string $url
	 */
	function redirect($url){
		header('Location:'.$url);
		exit;
	}
	/**
	 * Immediately redirect the web page.
	 * @param string $url
	 */
	function redirectToSelf(){
		$url = HTTP_PROTOCOLE.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->redirect($url);
	}
	/**
	 * Round float value into formated string
	 * @param float $number		Value to round
	 * @param int	$decimal	Nb of decimals after sep
	 * @param char	$sep		Decimal separator
	 * @return string
	 */
	function round($number, $decimal=2, $sep='.'){
		return $this->_view->round($number, $decimal, $sep);
	}
	/**
	 * Check e-mail validity
	 * @param string $email
	 * @return bool
	 */
	final public function check_email(&$email) {
		if($email = filter_var($email, FILTER_VALIDATE_EMAIL)){
			list($username,$domain)=explode('@',$email);
			return checkdnsrr($domain,'MX');
		}
		return false;
	}

}