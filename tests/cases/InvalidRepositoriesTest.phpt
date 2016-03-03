<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;


test(function () {
	// missing table
	Assert::exception(function () {
		ServiceLocator::getInvalidRepository()->testNoTable();

	}, 'YetORM\Exception\InvalidStateException', 'Table name not set.');

	// missing entity
	Assert::exception(function () {
		ServiceLocator::getInvalidRepository()->testNoEntity();

	}, 'YetORM\Exception\InvalidStateException', 'Entity class not set.');
});
