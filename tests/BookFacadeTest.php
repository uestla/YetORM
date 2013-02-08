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
				'title' => '1001 tipu a triku pro PHP',
				'year' => '2010',
			),
			array(
				'id' => 2,
				'title' => 'JUSH',
				'year' => '2007',
			),
			array(
				'id' => 4,
				'title' => 'Dibi',
				'year' => '2005',
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
