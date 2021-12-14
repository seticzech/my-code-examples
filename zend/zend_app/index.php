<?php

// Nastavení cest
define('ROOT_PATH', realpath(dirname(__FILE__)));

define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('LIBRARY_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('CONFIG_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'config');

define('DATA_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'data');
define('CACHE_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'cache');
define('LOGS_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'log');
define('TEMP_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'tmp');
define('SESS_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'sess');
define('UPLOAD_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'upload');
define('LUCENE_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'lucene');
define('LOCALE_PATH', DATA_PATH . DIRECTORY_SEPARATOR . 'locale');

define('LAYOUTS_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'layouts');
define('MODULES_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'modules');
//define('VFS_BASE_PATH', ROOT_PATH . 'storage' . DIRECTORY_SEPARATOR);

define('TMBS_URL_PATH', DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, 
	array('application', 'data', 'tmp')));

// Auxiliary libraries
require_once LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Auxlib' . DIRECTORY_SEPARATOR . 'auxlib.php';
require_once LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Auxlib' . DIRECTORY_SEPARATOR . 'auxdb.php';

// PHP include
set_include_path(LIBRARY_PATH
	. PATH_SEPARATOR . APP_PATH
	. PATH_SEPARATOR . ROOT_PATH . 'public'
	. PATH_SEPARATOR . get_include_path());

// PHP error logging
$phpErrorLogEnable = getenv('PHP_ERROR_LOG_ENABLE') ? getenv('PHP_ERROR_LOG_ENABLE') : '0';
$phpErrorLogFileName = getenv('PHP_ERROR_LOG_FILENAME') ? getenv('PHP_ERROR_LOG_FILENAME') : 'php_errors.log';
	
ini_set('log_errors', $phpErrorLogEnable != '0');
ini_set('error_log', createPath(array(LOGS_PATH, $phpErrorLogFileName)));

// Class autoloader
require_once createPath(array('Eal', 'Loader.php'), false);
require_once createPath(array('Zend', 'Loader', 'Autoloader.php'), false);
$autoloader = Zend_Loader_Autoloader::getInstance();
//$autoloader->setFallbackAutoloader(true);

// Define application environment
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// hack kvůli SWFUPLOAD, phpsessid v URL jako parametr 'hash'
if (isset($_GET['hash'])) {
	Zend_Session::setId($_GET['hash']);
}
Zend_Session::start();

// nastavení multibyte interního kódování na UTF-8
mb_internal_encoding('UTF-8');

// Bootstrap and run application
try {
	require_once 'Bootstrap.php';
	require_once createPath(array('Zend', 'Application.php'), false);
	require_once createPath(array('Zend', 'Config.php'), false);
	$application = new Zend_Application(APPLICATION_ENV, createPath(array(CONFIG_PATH, 'application.ini')));
	$application->bootstrap();
	$application->run();
} catch (Exception $e) {
	echo "<html><body>An exception occured while bootstrapping the application.";
	echo "<br /><br /><b>Exception message:</b> " . $e->getMessage();
	echo "<br /><br /><b>Stack trace:</b><br /><pre>" . $e->getTraceAsString() . "</pre>";
	echo "</body></html>";
}
