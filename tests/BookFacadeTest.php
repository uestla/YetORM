<?php

require_once __DIR__ . '/db/connection.php';


class BookFacadeTest extends PHPUnit_Framework_TestCase
{

	function testLatest()
	{
		$books = array();
		foreach (static::getFacade()->getLatest() as $book) {
			$books[] = $book->toArray();
		}

		$this->assertEquals(array(
			array(
				'id' => 1,
				'book_title' => '1001 tipu a triku pro PHP',
				'written' => '2010',
				'available' => TRUE,
			),
			array(
				'id' => 2,
				'book_title' => 'JUSH',
				'written' => '2007',
				'available' => TRUE,
			),
			array(
				'id' => 4,
				'book_title' => 'Dibi',
				'written' => '2005',
				'available' => TRUE,
			),

		), $books);
	}



	// =====================================

	protected static function getFacade()
	{
		static $facade;

		if ($facade === NULL) {
			$facade = new BookFacade(new BookRepository(getConnection()));
		}

		return $facade;
	}

}
