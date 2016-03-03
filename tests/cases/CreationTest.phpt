<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;


// creation
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->createEntity();
	$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
	$book->bookTitle = 'Brand new book';

	Assert::equal(TRUE, $repo->persist($book));

	// default values
	Assert::equal(TRUE, $book->available);
	Assert::equal(NULL, $book->written);
});


// add tags
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->createEntity();
	$book->bookTitle = 'Testing book';
	$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
	$book->addTag('PHP');
	$book->addTag('New tag');
	$repo->persist($book);

	$tags = array();
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->toArray();
	}

	Assert::equal(array(
		array(
			'id' => 21,
			'name' => 'PHP',
		),
		array(
			'id' => 25,
			'name' => 'New tag',
		),

	), $tags);
});


// remove tags
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->getByID(6);
	$book->removeTag('New tag');
	$repo->persist($book);

	$tags = array();
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->toArray();
	}

	Assert::equal(array(
		array(
			'id' => 21,
			'name' => 'PHP',
		),

	), $tags);
});
