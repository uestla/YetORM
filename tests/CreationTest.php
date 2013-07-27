<?php


class CreationTest extends PHPUnit_Framework_TestCase
{

	function testCreation()
	{
		$book = new Model\Entities\Book;
		$book->author = ServiceLocator::getAuthorRepository()->findById(11);
		$book->bookTitle = 'Brand new book';

		$rows = ServiceLocator::getBookRepository()->persist($book);
		$this->assertEquals(1, $rows);

		// default values
		$this->assertEquals($book->available, TRUE);
		$this->assertEquals($book->written, NULL);


		// multiple refreshing
		try {
			$book->refresh($book->toRow()->getNative());
			$this->fail();

		} catch (YetORM\Exception\InvalidStateException $e) {
			if ($e->getMessage() !== 'Cannot refresh already refreshed entity.') {
				throw $e;
			}
		}
	}



	function testAddTags()
	{
		$book = new Model\Entities\Book;
		$book->bookTitle = 'Testing book';
		$book->author = ServiceLocator::getAuthorRepository()->findById(11);
		$book->addTag('PHP');
		$book->addTag('Nový tag');

		ServiceLocator::getBookRepository()->persist($book);

		$expected = array(
			array(
				'id' => 21,
				'name' => 'PHP',
			),
			array(
				'id' => 25,
				'name' => 'Nový tag',
			),
		);

		$actual = array();
		foreach ($book->getTags() as $tag) {
			$actual[] = $tag->toArray();
		}

		$this->assertEquals($expected, $actual);
	}



	function testRemoveTags()
	{
		$repo = ServiceLocator::getBookRepository();
		$book = $repo->findById(7);

		$book->removeTag('Nový tag');
		$repo->persist($book);

		$book->removeTag('Nový tag');
		$repo->persist($book);


		$expected = array(
			array(
				'id' => 21,
				'name' => 'PHP',
			),
		);

		$actual = array();
		foreach ($book->getTags() as $tag) {
			$actual[] = $tag->toArray();
		}

		$this->assertEquals($expected, $actual);
	}

}
