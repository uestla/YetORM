<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use YetORM\Exception\InvalidStateException;


test(function () {
	// missing table
	Assert::exception(function () {
		ServiceLocator::getInvalidRepository()->testNoTable();

	}, InvalidStateException::class, 'Table name not set. Use either annotation @table or class member Model\Repositories\InvalidRepository::$table');

	// missing entity
	Assert::exception(function () {
		ServiceLocator::getInvalidRepository()->testNoEntity();

	}, InvalidStateException::class, 'Entity class not set. Use either annotation @entity or class member Model\Repositories\InvalidRepository::$entity');
});
