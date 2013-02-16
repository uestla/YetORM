<?php

require_once __DIR__ . '/model/ServiceLocator.php';


class BookRepositoryTest extends PHPUnit_Framework_TestCase
{

	function testEntity()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);

		$this->assertTrue($book instanceof Book);

		$expected = array(
			'id' => 1,
			'book_title' => '1001 tipu a triku pro PHP',
			'author' => 'Jakub Vrana',
			'written' => '2010',
			'available' => TRUE,
			'tags' => array('PHP', 'MySQL'),
		);

		$this->assertEquals($expected, $book->toArray());
	}



	function testOneToOne()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);
		$author = $book->getAuthor();
		$this->assertEquals('Jakub Vrana', $author->getName());
	}



	function testOneToMany()
	{
		$book = ServiceLocator::getBookRepository()->findById(1);
		$tags = array();
		foreach ($book->getTags() as $tag) {
			$tags[] = $tag->getName();
		}

		$this->assertEquals(array('PHP', 'MySQL'), $tags);
	}



	function testSearch()
	{
		$books = array();
		foreach (ServiceLocator::getBookRepository()->findByTag('PHP') as $book) {
			$books[] = $book->getBookTitle();
		}

		$this->assertEquals(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
	}



	function testCount()
	{
		$allBooks = ServiceLocator::getBookRepository()->findAll();
		$bookTags = ServiceLocator::getBookRepository()->findById(3)->getTags();

		$this->assertEquals(4, count($allBooks->limit(2))); // data not received yet -> count as non-limited
		$this->assertEquals(2, count($allBooks->limit(2)->getData())); // data received
		$this->assertEquals(1, count($bookTags));
	}



	/** @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit */
	function testPresenterFlow()
	{
		// load data
		$books = ServiceLocator::getBookRepository()->findAll();

		// paginate result
		$paginator = new Nette\Utils\Paginator;
		$paginator->itemsPerPage = 2;
		$paginator->itemCount = count($books);
		$paginator->page = 2;

		$books->limit($paginator->length, $paginator->offset);

		// render them ordered in template
		$array = array();
		foreach ($books->orderBy('book_title') as $book) {
			$array[] = $book->getBookTitle();
		}

		$this->assertEquals(array('JUSH', 'Nette'), $array);
	}



	function testQueries()
	{
		$connection = getConnection();
		$connection->onQuery['queryDump'] = function (Nette\Database\Statement $st) {
			echo $st->queryString . "\n";
		};


		ob_start();

			foreach ($connection->table('book') as $book) {
				foreach ($book->related('book_tag')->order('tag.name DESC') as $book_tag) {
					echo $book_tag->tag->name, ', ';
				}
			}

		$native = ob_get_clean();


		ob_start();

			foreach (ServiceLocator::getBookRepository()->findAll() as $book) {
				foreach ($book->getTags()->orderBy('tag.name', TRUE) as $tag) {
					echo $tag->getName(), ', ';
				}
			}

		$repository = ob_get_clean();

		$this->assertEquals($native, $repository);


		unset($connection->onQuery['queryDump']);
	}



	function testCreate()
	{
		$book = ServiceLocator::createTestingBook();

		$this->assertTrue($book instanceof Book);
		$this->assertEquals(array(
			'id' => 5,
			'book_title' => 'Texy 2',
			'author' => 'David Grudl',
			'written' => '2008',
			'available' => TRUE,
			'tags' => array('PHP'),

		), $book->toArray());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());
	}



	function testEdit()
	{
		$repo = ServiceLocator::getBookRepository();

		// change title
		$book = $repo->findById(5);
		$book->setBookTitle('New title');
		$this->assertEquals('New title', $book->getBookTitle());

		$rows = $repo->persist($book);
		$this->assertEquals(1, $rows);
		$this->assertEquals('New title', $book->getBookTitle());


		// change author
		$author = ServiceLocator::getAuthorRepository()->findById(13);
		$this->assertEquals('Geek', $author->getName());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());
		$book->setAuthor($author);
		$rows = $repo->persist($book);

		$this->assertEquals(1, $rows);
		$this->assertEquals('Geek', $book->getAuthor()->getName());


		// change availability
		$book->setAvailable(FALSE);
		$repo->persist($book);
		$this->assertFalse($book->getAvailable());

		$this->assertEquals(array(
			'id' => 5,
			'book_title' => 'New title',
			'author' => 'Geek',
			'written' => '2008',
			'available' => FALSE,
			'tags' => array('PHP'),

		), $book->toArray());
	}



	function testDelete()
	{
		$repo = ServiceLocator::getBookRepository();
		$this->assertEquals(5, count($repo->findAll()));

		$rows = $repo->delete($repo->findById(5));
		$this->assertEquals(1, $rows);

		$this->assertEquals(4, count($repo->findAll()));
	}

}
