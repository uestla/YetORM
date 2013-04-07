<?php

require_once __DIR__ . '/model/ServiceLocator.php';


class BookFacadeTest extends PHPUnit_Framework_TestCase
{

	function testLatest()
	{
		$books = array();
		foreach (ServiceLocator::getBookFacade()->getLatest() as $book) {
			$books[] = $book->toArray();
		}

		$this->assertEquals(array(
			array(
				'id' => 1,
				'bookTitle' => '1001 tipu a triku pro PHP',
				'author' => 'Jakub Vrana',
				'written' => '2010',
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),
			array(
				'id' => 2,
				'bookTitle' => 'JUSH',
				'author' => 'Jakub Vrana',
				'written' => '2007',
				'available' => TRUE,
				'tags' => array('JavaScript'),
			),
			array(
				'id' => 4,
				'bookTitle' => 'Dibi',
				'author' => 'David Grudl',
				'written' => '2005',
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),

		), $books);
	}

}
