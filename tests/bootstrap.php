<?php

use Nette\Diagnostics\Debugger;

require_once 'Nette/loader.php';
require_once 'PHPUnit/Autoload.php';


Debugger::enable(Debugger::DEVELOPMENT, FALSE);
Debugger::$strictMode = TRUE;
Debugger::$maxDepth = FALSE;
Debugger::$maxLen = FALSE;

function id($a) { return $a; }

$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage( new Nette\Caching\Storages\FileStorage(__DIR__ . '/temp') );
$loader->addDirectory(array(__DIR__ . '/../src', __DIR__ . '/model'));
$loader->register();
