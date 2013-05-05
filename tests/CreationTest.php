<?php


class CreationTest extends PHPUnit_Framework_TestCase
{

	function testCreation()
	{
		$book = new Book;
		$book->author = ServiceLocator::getAuthorRepository()->findById(11);
		$book->bookTitle = 'Brand new book';

		$rows = ServiceLocator::getBookRepository()->persist($book);
		$this->assertEquals(1, $rows);

		// default values
		$this->assertEquals($book->available, TRUE);
		$this->assertEquals($book->written, NULL);
	}

}
