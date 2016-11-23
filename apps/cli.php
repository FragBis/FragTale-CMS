<?php
/**
 * @copyright 2016 Fabrice Dant <fabricedant@gmail.com>
 * 
 * USAGE:
 * php cli.php -c=controller/name [any else options handled by controllers
 * 
 * Where -c required option is the relative path (from APP_ROOT) to the controller to include
 */
namespace FragTale;

# Measuring PHP process time
$chrono = microtime(true);
echo "[".date('Y-m-d H:i:s')."] START\n";

# Base constants
define('REL_DIR',		trim(getopt('c:')['c'], '/'));
if (empty(REL_DIR))
	die("Missing required option -c. Application STOPPED\n");
define('APP_ROOT',		__DIR__);
define('DOC_ROOT',		realpath(APP_ROOT.'/..'));
define('PUB_ROOT',		realpath(DOC_ROOT.'/public'));
define('CONF_ROOT',		DOC_ROOT.'/settings');
define('DB_CONF_ROOT',	CONF_ROOT.'/databases');
define('LIB_ROOT',		APP_ROOT.'/library');
define('TPL_ROOT',		APP_ROOT.'/templates');
define('DEFAULT_LAYOUT',TPL_ROOT.'/pages/default.layout.phtml');
define('PAGE_404',		TPL_ROOT.'/views/404.phtml');
define('HTTP_PROTOCOLE',null);
define('WEB_ROOT',		null);
define('ADMIN_WEB_ROOT',null);
define('IS_CLI',		true);
define('ENV',			'cli');

require_once LIB_ROOT.'/fragtale.application.php';
# Loading application params
Application::loadCliParams();

# Load all the inherited controllers
Application::requireControllers(REL_DIR);

$class_name = 'FragTale\Controller\\'.str_replace('/', '\\', REL_DIR);
if (class_exists($class_name)){
	$Controller = new $class_name();
	$Controller->run();
}
else echo "!!FATAL ERROR!! Unknown class $class_name\n";

#Displaying PHP process time on dev env
echo "[".date('Y-m-d H:i:s').'] FINISH - PHP process time: '.substr((microtime(true)-$chrono)*1000, 0, 5).'ms'.' | Allocated mem: '.round(memory_get_peak_usage()/1024/1024, 2)."Mo\n";