<?php

use Nette\Diagnostics\Debugger;

require_once 'Nette/loader.php';
require_once 'PHPUnit/Autoload.php';
require_once __DIR__ . '/../src/yetorm.php';
require_once __DIR__ . '/model/ServiceLocator.php';


Debugger::enable(Debugger::DEVELOPMENT, FALSE);
Debugger::$strictMode = TRUE;
Debugger::$maxDepth = FALSE;
Debugger::$maxLen = FALSE;

function id($a) { return $a; }

$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage(ServiceLocator::getCacheStorage());
$loader->addDirectory(__DIR__ . '/model');
$loader->register();
