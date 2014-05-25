<?php


class PropertiesTest extends PHPUnit_Framework_TestCase
{

	function testSetters()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);

		// set as property
		$book->bookTitle = 'New title';
		$this->assertEquals('New title', $book->bookTitle);

		// setting read-only property
		try {
			$book->id = 123;
			$this->fail();

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Model\Entities\Book::$id.') {
				throw $e;
			}
		}

		// setting invalid type
		try {
			$book->bookTitle = 123;
			$this->fail();

		} catch (YetORM\Exception\InvalidArgumentException $e) {
			if ($e->getMessage() !== "Invalid type - 'string' expected, 'integer' given.") {
				throw $e;
			}
		}

		try {
			$book->available = 'TRUE';
			$this->fail();

		} catch (YetORM\Exception\InvalidArgumentException $e) {
			if ($e->getMessage() !== "Invalid type - 'boolean' expected, 'string' given.") {
				throw $e;
			}
		}

		// setting undeclared property
		try {
			$book->asdf = 'Book title';
			$this->fail();

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Model\Entities\Book::$asdf.') {
				throw $e;
			}
		}
	}


	function testGetters()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);

		// get as property
		$this->assertEquals('1001 tipu a triku pro PHP', $book->bookTitle);

		// test type
		$this->assertTrue(is_int($book->id));
		$this->assertTrue(is_string($book->bookTitle));
		$this->assertTrue(is_bool($book->available));

		// getting undeclared property
		try {
			$book->asdf;
			$this->fail();

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot read an undeclared property Model\Entities\Book::$asdf.') {
				throw $e;
			}
		}
	}


	function testIsSet()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);

		// properties
		$this->assertTrue(isset($book->id));
		$this->assertTrue(isset($book->bookTitle));
		$this->assertFalse(isset($book->foo));

		// methods (should fail)
		$this->assertFalse(isset($book->author));
		$this->assertFalse(isset($book->tags));

		// NULL values
		$book->written = NULL;
		$this->assertFalse(isset($book->written));

		$book->written = new Nette\Utils\DateTime;
		$this->assertTrue(isset($book->written));
	}


	function testUnset()
	{
		try {
			$book = ServiceLocator::getBookRepository()->getByID(1);
			unset($book->author);
			$this->fail();

		} catch (YetORM\Exception\NotSupportedException $e) {}
	}


	function testToArray()
	{
		$book = ServiceLocator::getBookRepository()->getByID(2);

		$expected = array(
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
		);

		$this->assertEquals($expected, $book->toArray());
	}


	function testInheritance()
	{
		$author = ServiceLocator::getAuthorRepository()->getByID(11);

		$this->assertEquals(array(
			'id' => 11,
			'name' => 'Jakub Vrana',
			'web' => 'http://www.vrana.cz/',
			'born' => NULL,

		), $author->toArray());
	}


	function testClassTypes()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);
		$this->assertInstanceOf('DateTime', $book->written);
	}


	function testNullable()
	{
		$repo = ServiceLocator::getBookRepository();

		$book = $repo->getByID(1);
		$book->written = NULL;
		$this->assertNull($book->written);

		$repo->persist($book);
		$this->assertNull($book->written);

		$book->written = new Nette\Utils\DateTime('1990-01-01');
		$this->assertEquals(new Nette\Utils\DateTime('1990-01-01'), $book->written);

		$repo->persist($book);
		$this->assertEquals(new Nette\Utils\DateTime('1990-01-01'), $book->written);

		$book->written = NULL;
		$this->assertNull($book->written);

		$repo->persist($book);
		$this->assertNull($book->written);

		try {
			$book->bookTitle = NULL;
			$this->fail();

		} catch (YetORM\Exception\InvalidArgumentException $e) {
			if ($e->getMessage() !== "Property 'Model\Entities\Book::\$bookTitle' cannot be NULL.") {
				throw $e;
			}
		}
	}


	function testAnnotationFail()
	{
		try {
			$bad = new Model\Entities\BadEntity;
			$bad->toArray();
			$this->fail();

		} catch (YetORM\Exception\InvalidStateException $e) {
			if ($e->getMessage() !== 'Invalid property type (double NULL).') {
				throw $e;
			}
		}
	}


	function testUninitializedColumnValue()
	{
		try {
			$book = ServiceLocator::getBookRepository()->createBook();
			$book->toRecord()->book_title;
			$this->fail();

		} catch (\YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== "The value of column 'book_title' not set.") {
				throw $e;
			}
		}
	}

}
