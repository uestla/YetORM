<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use YetORM\Exception\MemberAccessException;
use YetORM\Exception\NotSupportedException;
use YetORM\Exception\InvalidArgumentException;
use YetORM\Exception\InvalidPropertyDefinitionException;


// setters
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	$book->bookTitle = 'New title';
	Assert::same('New title', $book->bookTitle);

	Assert::exception(function () use ($book) {
		$book->id = 123;

	}, MemberAccessException::class, 'Cannot write to a read-only property Model\Entities\Book::$id.');

	Assert::exception(function () use ($book) {
		$book->bookTitle = 123;

	}, InvalidArgumentException::class, "Invalid type - 'string' expected, 'integer' given.");

	Assert::exception(function () use ($book) {
		$book->available = 'TRUE';

	}, InvalidArgumentException::class, "Invalid type - 'bool' expected, 'string' given.");

	Assert::exception(function () use ($book) {
		$book->asdf = 'Book title';

	}, MemberAccessException::class, 'Cannot write to an undeclared property Model\Entities\Book::$asdf.');
});


// getters
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	Assert::same('1001 tipu a triku pro PHP', $book->bookTitle);
	Assert::true(is_int($book->id));
	Assert::true(is_string($book->bookTitle));
	Assert::true(is_bool($book->available));

	Assert::exception(function () use ($book) {
		$book->asdf;

	}, MemberAccessException::class, 'Cannot read an undeclared property Model\Entities\Book::$asdf.');
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

	}, NotSupportedException::class);
});


// entity to array
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(2);

	Assert::equal([
		'id' => 2,
		'bookTitle' => 'JUSH',
		'written' => new Nette\Utils\DateTime('2007-01-01'),
		'available' => TRUE,
		'author' => [
			'id' => 11,
			'name' => 'Jakub Vrana',
			'web' => 'http://www.vrana.cz/',
			'born' => NULL,
		],
		'price' => 0.0,
		'tags' => ['JavaScript'],

	], $book->toArray());
});


// inheritance
test(function () {
	$author = ServiceLocator::getAuthorRepository()->getByID(11);

	Assert::equal([
		'id' => 11,
		'name' => 'Jakub Vrana',
		'web' => 'http://www.vrana.cz/',
		'born' => NULL,

	], $author->toArray());
});


// class types
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	Assert::true($book->written instanceof DateTime);

	Assert::exception(function () use ($book) {
		$book->written = new \stdClass;

	}, InvalidArgumentException::class, "Instance of 'Nette\Utils\DateTime' expected, 'stdClass' given.");

	Assert::exception(function () use ($book) {
		$book->written = '';

	}, InvalidArgumentException::class, "Instance of 'Nette\Utils\DateTime' expected, 'string' given.");
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
	Assert::true(Model\Entities\Book::getReflection()->getEntityProperty('written')->isNullable());

	Assert::exception(function () use ($book) {
		$book->bookTitle = NULL;

	}, InvalidArgumentException::class, "Property 'Model\Entities\Book::\$bookTitle' cannot be NULL.");
});


// double NULL annotation fail
test(function () {
	Assert::exception(function () {
		Model\Entities\BadDoubleNullEntity::getReflection()->getEntityProperties();

	}, InvalidPropertyDefinitionException::class, 'Only one NULL is allowed, "NULL|NULL" given.');
});


// multiple non-NULL property type fail
test(function () {
	Assert::exception(function () {
		Model\Entities\BadMultipleTypeEntity::getReflection()->getEntityProperties();

	}, InvalidPropertyDefinitionException::class, 'Multiple non-NULL types detected.');
});


// missing $ in property definition
test(function () {
	Assert::exception(function () {
		Model\Entities\MissingDollarEntity::getReflection()->getEntityProperties();

	}, InvalidPropertyDefinitionException::class,
			'Missing "$" in property name in "@property string nodollar"');
});


// invalid property definition (general)
test(function () {
	Assert::exception(function () {
		Model\Entities\MissingTypeEntity::getReflection()->getEntityProperties();

	}, InvalidPropertyDefinitionException::class,
		'"@property[-read] <type> $<property> [-> <column>][ <description>]" expected, "@property $missingType" given.');
});


// uninitialized column value
test(function () {
	Assert::exception(function () {
		$book = ServiceLocator::getBookRepository()->createEntity();
		$book->toRecord()->book_title;

	}, MemberAccessException::class, "The value of column 'book_title' not set.");
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
	Assert::same("Returns author of the book.\n\nWhat a useful method!\nLove it <3", Nette\Utils\Strings::normalizeNewLines($authorprop->getDescription()));

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
