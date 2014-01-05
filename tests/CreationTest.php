<?php


class CreationTest extends PHPUnit_Framework_TestCase
{

	function testCreation()
	{
		$repo = ServiceLocator::getBookRepository();

		$book = $repo->createBook();
		$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
		$book->bookTitle = 'Brand new book';

		$rows = ServiceLocator::getBookRepository()->persist($book);
		$this->assertEquals(1, $rows);

		// default values
		$this->assertEquals($book->available, TRUE);
		$this->assertEquals($book->written, NULL);
	}



	function testAddTags()
	{
		$repo = ServiceLocator::getBookRepository();

		$book = $repo->createBook();
		$book->bookTitle = 'Testing book';
		$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
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
		$book = $repo->getByID(7);

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
