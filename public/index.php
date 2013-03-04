<?php
namespace Bonz;

session_start();

# Measuring PHP process time
$chrono = microtime(true);

# Base constants
define('PUB_ROOT',		__DIR__);
define('DOC_ROOT',		realpath(PUB_ROOT.'/..'));
define('CONF_ROOT',		DOC_ROOT.'/settings');
define('DB_CONF_ROOT',	CONF_ROOT.'/databases');
define('APP_ROOT',		DOC_ROOT.'/apps');
define('LIB_ROOT',		APP_ROOT.'/library');
define('TPL_ROOT',		APP_ROOT.'/templates');
define('DEFAULT_LAYOUT',TPL_ROOT.'/pages/default.layout.phtml');
define('PAGE_404',		TPL_ROOT.'/views/404.phtml');
define('REL_DIR',		trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', PUB_ROOT), '/'));
define('HTTP_PROTOCOLE',stripos($_SERVER['SERVER_PROTOCOL'], 'https')!==false ? 'https' : 'http');
# Application base url
define('WEB_ROOT',		trim(HTTP_PROTOCOLE.'://'.$_SERVER['HTTP_HOST'].'/'.REL_DIR, '/'));
# Admin base url
define('ADMIN_WEB_ROOT',WEB_ROOT.'/admin');

require_once LIB_ROOT.'/bonz.application.php';
# Include system library
Application::requireFolder(LIB_ROOT.'/bonz');
Application::requireFolder(APP_ROOT.'/models/bonz/cms');

# Instanciate the meta View
$view = new View();
# Render
$view->render(true);

unset($view);
#Displaying PHP process time on dev env
Debug::vars('PHP process time: '.substr((microtime(true)-$chrono)*1000, 0, 5).'ms'.' | Allocated mem: '.round(memory_get_peak_usage()/(1024*1024), 2).'Mo');
