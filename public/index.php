<?php

date_default_timezone_set('PRC');

$appDir = '../application';
$coreDir = '../Core';

define('APPPATH', realpath($appDir).DIRECTORY_SEPARATOR);
define('COREPATH', realpath($coreDir).DIRECTORY_SEPARATOR);

require '../Core/Autoload/Autoloader.php';
require '../Core/common.php';

$autoloader = new App\Autoload\Autoloader();
$autoloader->register();
$autoloader->addNamespace('App\Core', COREPATH);
$autoloader->addNamespace('Roc', APPPATH);

use App\Core\Server;

$server = new Server();
$server->run();