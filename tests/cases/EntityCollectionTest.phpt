<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use Model\Entities\Author;
use YetORM\EntityCollection;


test(function () {
	$context = ServiceLocator::getDbContext();

	$selection = $context->table('author')
			->where(':book.written IS NOT NULL');

	$collection = new EntityCollection($selection, 'Model\Entities\Author');

	// count($collection)
	Assert::same(4, count($collection));

	// count($column) for number of authors
	Assert::same(2, $collection->count('DISTINCT author.id'));
});
