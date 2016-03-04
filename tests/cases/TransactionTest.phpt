<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;


test(function () {
	Assert::exception(function () {
		$transaction = new YetORM\Transaction(ServiceLocator::getDbContext()->getConnection());
		$transaction->commit();

	}, 'YetORM\Exception\InvalidStateException', 'No transaction started.');
});
