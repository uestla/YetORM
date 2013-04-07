<?php


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
				'written' => new Nette\DateTime('2010-01-01'),
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),
			array(
				'id' => 2,
				'bookTitle' => 'JUSH',
				'author' => 'Jakub Vrana',
				'written' => new Nette\DateTime('2007-01-01'),
				'available' => TRUE,
				'tags' => array('JavaScript'),
			),
			array(
				'id' => 4,
				'bookTitle' => 'Dibi',
				'author' => 'David Grudl',
				'written' => new Nette\DateTime('2005-01-01'),
				'available' => TRUE,
				'tags' => array('PHP', 'MySQL'),
			),

		), $books);
	}

}
