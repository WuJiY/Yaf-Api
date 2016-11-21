<?php
defined('APP_ENV') or define('APP_ENV', 'dev'); //'dev', 'product'
define('APPLICATION_PATH', dirname(__FILE__) . '/../');
define('LIBRARY_PATH', APPLICATION_PATH . '/application/library/');

//这里视常量数量，可将配置写入文件中，加载此文件
define('CACHE_KEY_PREFIX', 'mwx:api:');
define('CACHE_KEY_EXPIRE', 3600);

define('TIMESTAMP', time());

require(LIBRARY_PATH . '/ErrorHandler.php');
require(LIBRARY_PATH . '/helpers/autoload.php');

(new app\library\ErrorHandler())->register();
Yaf\Registry::set('response', new app\library\Response([
    'format' => app\library\Response::FORMAT_JSON,
    'charset' => 'UTF-8'
]));

$application = new Yaf\Application(APPLICATION_PATH . '/conf/application_' . APP_ENV . '.ini');

$application->bootstrap()->run();
?>
