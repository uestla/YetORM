<?php


class PropertiesTest extends PHPUnit_Framework_TestCase
{

	function testSetters()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		// set as property
		$book->bookTitle = 'New title';
		$this->assertEquals('New title', $book->getBookTitle());

		// use setter
		$book->setBookTitle('Another title');
		$this->assertEquals('Another title', $book->getBookTitle());

		// setting read-only property
		try {
			$book->setId(123);
			$this->fail();

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Model\Entities\Book::$id.') {
				throw $e;
			}
		}

		// setting invalid type
		try {
			$book->setBookTitle(123);
			$this->fail();

		} catch (YetORM\Exception\InvalidArgumentException $e) {
			if ($e->getMessage() !== "Invalid type - 'string' expected, 'integer' given.") {
				throw $e;
			}
		}

		try {
			$book->setAvailable('TRUE');
			$this->fail();

		} catch (YetORM\Exception\InvalidArgumentException $e) {
			if ($e->getMessage() !== "Invalid type - 'boolean' expected, 'string' given.") {
				throw $e;
			}
		}

		// setting undeclared property
		try {
			$book->setAsdf('Book title');
			$this->fail();

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot write to an undeclared property Model\Entities\Book::$asdf.') {
				throw $e;
			}
		}
	}



	function testGetters()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		// get as property
		$this->assertEquals('1001 tipu a triku pro PHP', $book->bookTitle);

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

		} catch (YetORM\Exception\MemberAccessException $e) {
			if ($e->getMessage() !== 'Cannot read an undeclared property Model\Entities\Book::$asdf.') {
				throw $e;
			}
		}
	}



	function testIsSet()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		// properties
		$this->assertTrue(isset($book->id));
		$this->assertTrue(isset($book->bookTitle));
		$this->assertFalse(isset($book->foo));

		// getters
		$this->assertTrue(isset($book->author));
		$this->assertTrue(isset($book->tags));
	}



	function testUnset()
	{
		try {
			$book = ServiceLocator::getBookRepository()->findById(1);
			unset($book->author);
			$this->fail();

		} catch (YetORM\Exception\NotSupportedException $e) {}
	}



	/** Tests default Nette\Object properties behavior */
	function testNativeGettersSetters()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);
		$author = $book->author;

		$this->assertInstanceOf('Model\Entities\Author', $author);
		$this->assertInstanceOf('YetORM\EntityCollection', $book->tags);
		$this->assertInstanceOf('YetORM\EntityCollection', $author->books);
	}



	function testToArray()
	{
		$book = ServiceLocator::getBookRepository()->findById(2);
		$expected = array(
			'id' => 2,
			'bookTitle' => 'JUSH',
			'written' => new Nette\DateTime('2007-01-01'),
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
		$author = ServiceLocator::getAuthorRepository()->findById(11);
		$this->assertEquals(array(
			'id' => 11,
			'name' => 'Jakub Vrana',
			'web' => 'http://www.vrana.cz/',
			'born' => NULL,

		), $author->toArray());
	}



	function testClassTypes()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);
		$this->assertInstanceOf('DateTime', $book->written);
	}



	function testNullable()
	{
		$repo = ServiceLocator::getBookRepository();
		$book = $repo->findById(1);
		$book->written = NULL;
		$this->assertNull($book->written);

		$repo->persist($book);
		$this->assertNull($book->getWritten());

		$book->setWritten(new Nette\DateTime('1990-01-01'));
		$this->assertEquals(new Nette\DateTime('1990-01-01'), $book->getWritten());

		$repo->persist($book);
		$this->assertEquals(new Nette\DateTime('1990-01-01'), $book->written);

		$book->setWritten(NULL);
		$this->assertNull($book->getWritten());

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

}
