<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use Nette\Database\IRow as NIRow;;
use Model\Entities\NoPrimaryEntity;
use YetORM\Exception\InvalidStateException;


test(function () {
	Assert::exception(function () {
		ServiceLocator::getNoPrimaryRepository()->persist(new NoPrimaryEntity);

	}, InvalidStateException::class, 'Insert did not return instance of ' . NIRow::class . '. '
			. 'Does table "no_primary_table" have primary key defined? If so, try cleaning cache.');
});
