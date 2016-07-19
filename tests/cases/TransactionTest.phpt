<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use YetORM\Exception\InvalidStateException;


test(function () {
	Assert::exception(function () {
		$transaction = new YetORM\Transaction(ServiceLocator::getDbContext()->getConnection());
		$transaction->commit();

	}, InvalidStateException::class, 'No transaction started.');
});
