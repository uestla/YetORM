<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;


// creation
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->createEntity();
	$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
	$book->bookTitle = 'Brand new book';

	Assert::true($repo->persist($book));

	// default values
	Assert::true($book->available);
	Assert::null($book->written);

	// invalid constructor
	Assert::exception(function () {
		new Model\Entities\Author(new \DateTime);

	}, 'YetORM\Exception\InvalidArgumentException', "Instance of 'Nette\Database\Table\ActiveRow' or 'YetORM\Record' expected, 'DateTime' given.");

	// not persisted check
	Assert::exception(function () {
		$author = new Model\Entities\Author;
		$author->getBooks();

	}, 'YetORM\Exception\InvalidStateException', 'Row not set yet.');
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

	$tags = [];
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->toArray();
	}

	Assert::same([
		[
			'id' => 21,
			'name' => 'PHP',
		],
		[
			'id' => 25,
			'name' => 'New tag',
		],

	], $tags);
});


// remove tags
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->getByID(6);
	$book->removeTag('New tag');
	$repo->persist($book);

	$tags = [];
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->toArray();
	}

	Assert::same([
		[
			'id' => 21,
			'name' => 'PHP',
		],

	], $tags);
});


// not persisted entity
test(function () {
	$repo = ServiceLocator::getBookRepository();
	$book = $repo->createEntity();

	Assert::true($repo->delete($book));
});


// undefined method
test(function () {
	Assert::exception(function () {
		ServiceLocator::getBookRepository()->createEntity()->asdf();

	}, 'YetORM\Exception\MemberAccessException', 'Call to undefined method Model\Entities\Book::asdf().');
});
