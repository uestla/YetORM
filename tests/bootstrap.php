<?php

use Nette\Diagnostics\Debugger;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/yetorm.php';
require_once __DIR__ . '/model/ServiceLocator.php';


Debugger::enable(Debugger::DEVELOPMENT, FALSE);
Debugger::$strictMode = TRUE;
Debugger::$maxDepth = FALSE;
Debugger::$maxLen = FALSE;

function id($a) { return $a; }

ServiceLocator::getCacheStorage()->clean(array(
	Nette\Caching\Cache::ALL => TRUE,
));

Aliaser\Container::setCacheStorage(ServiceLocator::getCacheStorage());

$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage(ServiceLocator::getCacheStorage());
$loader->addDirectory(__DIR__ . '/model');
$loader->register();
