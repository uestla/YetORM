<?php


class BookServiceTest extends PHPUnit_Framework_TestCase
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
				'author' => array(
					'id' => 11,
					'name' => 'Jakub Vrana',
					'web' => 'http://www.vrana.cz/',
					'born' => NULL,
				),
				'written' => new Nette\Utils\DateTime('2010-01-01'),
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),
			array(
				'id' => 2,
				'bookTitle' => 'JUSH',
				'author' => array(
					'id' => 11,
					'name' => 'Jakub Vrana',
					'web' => 'http://www.vrana.cz/',
					'born' => NULL,
				),
				'written' => new Nette\Utils\DateTime('2007-01-01'),
				'available' => TRUE,
				'tags' => array('JavaScript'),
			),
			array(
				'id' => 4,
				'bookTitle' => 'Dibi',
				'author' => array(
					'id' => 12,
					'name' => 'David Grudl',
					'web' => 'http://davidgrudl.com/',
					'born' => NULL,
				),
				'written' => new Nette\Utils\DateTime('2005-01-01'),
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),

		), $books);
	}

}
