<?php
namespace Bonz;
/**
 * Getting the application params
 * @author fragbis
 *
 */
class Application{
	
	static protected $_ini;
	static protected $_errors;
	
	static function loadIniParams(){
		$base_ini_filename = strtolower(str_replace('www.', '', $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'/ini'));
		while ($base_ini_filename = substr($base_ini_filename, 0, strrpos($base_ini_filename, '/'))){
			if ($base_ini_filename==str_replace(array(HTTP_PROTOCOLE, '://', 'www.'), '', ADMIN_WEB_ROOT)){
				$ini_file = DOC_ROOT.'/settings/application/locales/'.str_replace('/', '.', $base_ini_filename).'.ini';
				if (!file_exists($ini_file))
					$ini_file = DOC_ROOT.'/settings/application/admin.ini';
				break;
			}
			$ini_file = DOC_ROOT.'/settings/application/locales/'.str_replace('/', '.', $base_ini_filename).'.ini';
			if (file_exists($ini_file))
				break;
			$ini_file = DOC_ROOT.'/settings/application/'.str_replace('/', '.', $base_ini_filename).'.ini';
			if (file_exists($ini_file))
				break;
			if ($base_ini_filename==strtolower(str_replace('www.', '', $_SERVER['HTTP_HOST'])) || $base_ini_filename=='.')
				break;
		}
		if (!file_exists($ini_file)){
			$ini_file = DOC_ROOT.'/settings/application/default.ini';
			if (!file_exists($ini_file)) die('Missing required "'.$ini_file.'" file.');
			unset($explodedUri);
		}
		self::$_ini = parse_ini_file($ini_file);
		
		### Set the parameters into constants
		foreach (self::$_ini as $key=>$value){
			if (!is_array($value))
				define(strtoupper($key), $value);
		}
		
		### Defining development environment
		if (defined('DEVEL')){
			define('ENV', 'devel');
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		}
		
		### Any declared php.ini values to override
		if (!empty(self::$_ini['php.ini'])){
			foreach (self::$_ini['php.ini'] as $key=>$value)
				ini_set($key, $value);
		}
		
		### Define default database connector instance name
		if (!defined('DEFAULT_DATABASE_CONNECTOR_NAME'))
			define('DEFAULT_DATABASE_CONNECTOR_NAME', 'default');
		
		### Define default landing page
		if (!defined('LANDING_PAGE'))
			define('LANDING_PAGE', 'home');
		
		### Set locale
		if (!defined('LOCALE'))
			define('LOCALE', 'fr_FR');
		
		### Locale PO files for translations with GETTEXT, assuming gettext PHP extension is installed
		$locale = LOCALE.'.utf8';
		if (isset($_SESSION['LOCALE']))
			$locale = $_SESSION['LOCALE'].'.utf8';
		putenv("LC_ALL=$locale");
		setlocale(LC_ALL, $locale);
		bindtextdomain('messages', DOC_ROOT.'/locale');
		textdomain('messages');
	}
	/**
	 * Get the application ini params
	 */
	static function getIniParams(){
		return self::$_ini;
	}
	
	/**
	 * Manage
	 * @param string	$msg
	 * @param string	$class
	 * @param string	$function
	 * @param int		$line
	 */
	static function catchError($msg, $class, $function, $line){
		$completeMsg = date('Y-m-d H:i:s').' ** '.$class.'::'.$function.'() in line '.$line.' ** '.$msg;
		$logFile = DOC_ROOT.'/logs/error'.date('Ym').'.log';
		self::$_errors[] = $completeMsg;
		fputs(fopen($logFile, 'a+'), $completeMsg."\n");
	}
	
	/**
	 * @return array
	 */
	static function getErrors(){
		return self::$_errors;
	}
	
	/**
	 * Scan a folder (and subs if in recursive mode) and include once all defined required PHP files.
	 * @param string	$folder
	 * @param bool		$recursively
	 */
	static function requireFolder($folder, $recursively=true){
		$dir = $folder;
		if (!file_exists($dir))
			$dir = DOC_ROOT.'/'.$folder;
		if (!file_exists($dir))
			$dir = APP_ROOT.'/'.$folder;
		if (!file_exists($dir)){
			self::catchError('Folder "'.$folder.'" not found.', __CLASS__, __FUNCTION__, __LINE__);
			return false;
		}
		$handle = opendir($dir);
		$dirs = array();
		while ($content = readdir($handle)) {
			if (!in_array($content, array('.', '..', '.svn'))){
				$path = $folder.'/'.$content;
				if (is_dir($path) && $recursively)
					$dirs[] = $path;
				elseif (file_exists($path) && substr($path, -4) == '.php')
					require_once $path;
			}
		}
		closedir($handle);
		
		if ($recursively)
		foreach ($dirs as $dir)
			self::requireFolder($dir);
	}
	
	/**
	 * Include the controller called by its view and all its possible parent controllers
	 * @param string $viewName
	 */
	static function requireControllers($viewName){
		$subDirs= explode('/', $viewName);
		$path	= APP_ROOT.'/controllers';
		foreach ($subDirs as $f){
			$path .= '/'.$f;
			if (file_exists($path.'.php')){
				require_once $path.'.php';
			}
		}
	}
}
