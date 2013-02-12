<?php

require_once __DIR__ . '/db/connection.php';
require_once __DIR__ . '/model/ServiceLocator.php';


class PropertiesTest extends PHPUnit_Framework_TestCase
{

	function testSetters()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		// set as property
		$book->book_title = 'New title';
		$this->assertEquals('New title', $book->getBookTitle());

		// use setter
		$book->setBookTitle('Another title');
		$this->assertEquals('Another title', $book->getBookTitle());

		// setting read-only property
		try {
			$book->setId(123);
			$this->fail();

		} catch (Nette\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Book::$id.') {
				throw $e;
			}
		}

		// setting invalid type
		try {
			$book->setBookTitle(123);
			$this->fail();

		} catch (Nette\InvalidArgumentException $e) {
			if ($e->getMessage() !== 'Invalid type - string expected, integer given.') {
				throw $e;
			}
		}

		try {
			$book->setAvailable('TRUE');
			$this->fail();

		} catch (Nette\InvalidArgumentException $e) {
			if ($e->getMessage() !== 'Invalid type - boolean expected, string given.') {
				throw $e;
			}
		}

		// setting undeclared property
		try {
			$book->setAsdf('Book title');
			$this->fail();

		} catch (Nette\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Book::$asdf.') {
				throw $e;
			}
		}
	}



	function testGetters()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		// get as property
		$this->assertEquals('1001 tipu a triku pro PHP', $book->book_title);

		// use getter
		$this->assertEquals('1001 tipu a triku pro PHP', $book->getBookTitle());

		// test type
		$this->assertTrue(is_int($book->getId()));
		$this->assertTrue(is_string($book->getBookTitle()));
		$this->assertTrue(is_bool($book->getAvailable()));

		// getting undeclared property
		try {
			$book->getAsdf();
			$this->fail();

		} catch (Nette\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot read an undeclared property Book::$asdf.') {
				throw $e;
			}
		}
	}



	function test()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);
		$book->book_title = 'New title';
		$this->assertEquals('New title', $book->book_title);

		$this->assertEquals('2010', $book->written);

		try {
			$book->id = 125;
			$this->fail();

		} catch (Nette\MemberAccessException $e) {}

		$this->assertEquals(1, $book->id);

		$this->assertTrue($book->author instanceof Author);
		$this->assertTrue($book->tags instanceof YetORM\EntityCollection);


		$author = ServiceLocator::getAuthorRepository()->findById(11);
		$this->assertEquals(array(
			'id' => 11,
			'name' => 'Jakub Vrana',

		), $author->toArray());
	}

}
