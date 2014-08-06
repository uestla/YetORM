<?php

require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;


// setters
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	$book->bookTitle = 'New title';
	Assert::equal('New title', $book->bookTitle);

	Assert::exception(function () use ($book) {
		$book->id = 123;
	}, 'YetORM\Exception\MemberAccessException', 'Cannot write to an undeclared property Model\Entities\Book::$id.');

	Assert::exception(function () use ($book) {
		$book->bookTitle = 123;
	}, 'YetORM\Exception\InvalidArgumentException', "Invalid type - 'string' expected, 'integer' given.");

	Assert::exception(function () use ($book) {
		$book->available = 'TRUE';
	}, 'YetORM\Exception\InvalidArgumentException', "Invalid type - 'boolean' expected, 'string' given.");

	Assert::exception(function () use ($book) {
		$book->asdf = 'Book title';
	}, 'YetORM\Exception\MemberAccessException', 'Cannot write to an undeclared property Model\Entities\Book::$asdf.');
});


// getters
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	Assert::equal('1001 tipu a triku pro PHP', $book->bookTitle);
	Assert::true(is_int($book->id));
	Assert::true(is_string($book->bookTitle));
	Assert::true(is_bool($book->available));

	Assert::exception(function () use ($book) {
		$book->asdf;
	}, 'YetORM\Exception\MemberAccessException', 'Cannot read an undeclared property Model\Entities\Book::$asdf.');
});


// isset
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);

	Assert::true(isset($book->id));
	Assert::true(isset($book->bookTitle));
	Assert::false(isset($book->foo));
	Assert::false(isset($book->author));
	Assert::false(isset($book->tags));

	$book->written = NULL;
	Assert::false(isset($book->written));

	$book->written = new Nette\Utils\DateTime;
	Assert::true(isset($book->written));
});


// unset
test(function () {
	Assert::exception(function () {
		$book = ServiceLocator::getBookRepository()->getByID(1);
		unset($book->written);
	}, 'YetORM\Exception\NotSupportedException');
});


// entity to array
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(2);

	Assert::equal(array(
		'id' => 2,
		'bookTitle' => 'JUSH',
		'written' => new Nette\Utils\DateTime('2007-01-01'),
		'available' => TRUE,
		'author' => array(
			'id' => 11,
			'name' => 'Jakub Vrana',
			'web' => 'http://www.vrana.cz/',
			'born' => NULL,
		),
		'tags' => array('JavaScript'),

	), $book->toArray());
});


// inheritance
test(function () {
	$author = ServiceLocator::getAuthorRepository()->getByID(11);
	Assert::equal(array(
		'id' => 11,
		'name' => 'Jakub Vrana',
		'web' => 'http://www.vrana.cz/',
		'born' => NULL,

	), $author->toArray());
});


// class types
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	Assert::true($book->written instanceof DateTime);
});


// nullable
test(function () {
	$repo = ServiceLocator::getBookRepository();
	$book = $repo->getByID(1);
	$book->written = NULL;
	Assert::null($book->written);
	$book->written = new \Nette\Utils\DateTime('1990-01-01');
	Assert::equal(new \Nette\Utils\DateTime('1990-01-01'), $book->written);
	$repo->persist($book);
	Assert::equal(new \Nette\Utils\DateTime('1990-01-01'), $book->written);
	$book->written = NULL;
	Assert::null($book->written);
	$repo->persist($book);
	Assert::null($book->written);

	Assert::exception(function () use ($book) {
		$book->bookTitle = NULL;
	}, 'YetORM\Exception\InvalidArgumentException', "Property 'Model\Entities\Book::\$bookTitle' cannot be NULL.");
});


// double NULL annotation fail
test(function () {
	Assert::exception(function () {
		Model\Entities\BadDoubleNullEntity::getReflection()->getEntityProperties();

	}, 'YetORM\Exception\InvalidStateException', 'Invalid property type (double NULL).');
});


// multiple non-NULL property type fail
test(function () {
	Assert::exception(function () {
		Model\Entities\BadMultipleTypeEntity::getReflection()->getEntityProperties();

	}, 'YetORM\Exception\InvalidStateException', 'Invalid property type (multiple non-NULL types detected).');
});


// uninitialized column value
test(function () {
	Assert::exception(function () {
		$book = ServiceLocator::getBookRepository()->createBook();
		$book->toRecord()->book_title;

	}, 'YetORM\Exception\MemberAccessException', "The value of column 'book_title' not set.");
});


// property description
test(function () {
	$bookref = Model\Entities\Book::getReflection();

	$titleprop = $bookref->getEntityProperty('bookTitle');
	Assert::true($titleprop->hasDescription());
	Assert::same('Title   of	the		book', $titleprop->getDescription());

	$writtenprop = $bookref->getEntityProperty('written');
	Assert::false($writtenprop->hasDescription());
	Assert::null($writtenprop->getDescription());

	$authorprop = $bookref->getEntityProperty('author');
	Assert::true($authorprop->hasDescription());
	Assert::same('Returns author of the book.

What a useful method!
Love it <3', $authorprop->getDescription());

	$tagsprop = $bookref->getEntityProperty('tags');
	Assert::false($tagsprop->hasDescription());
	Assert::null($tagsprop->getDescription());

	$authref = Model\Entities\Author::getReflection();

	$webprop = $authref->getEntityProperty('web');
	Assert::true($webprop->hasDescription());
	Assert::same('Author\'s personal website', $webprop->getDescription());

	$birthprop = $authref->getEntityProperty('born');
	Assert::true($birthprop->hasDescription());
	Assert::same('person\'s birthday', $birthprop->getDescription());
});
