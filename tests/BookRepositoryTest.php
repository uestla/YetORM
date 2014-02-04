<?php

use YetORM\EntityCollection as EC;
use Nette\Database\ResultSet as NResultSet;
use Nette\Database\Connection as NConnection;


class BookRepositoryTest extends PHPUnit_Framework_TestCase
{

	function testEntity()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);

		$this->assertInstanceOf('Model\Entities\Book', $book);

		$expected = array(
			'id' => 1,
			'bookTitle' => '1001 tipu a triku pro PHP',
			'author' => array(
				'id' => 11,
				'name' => 'Jakub Vrana',
				'web' => 'http://www.vrana.cz/',
				'born' => NULL,
			),
			'written' => new Nette\DateTime('2010-01-01'),
			'available' => TRUE,
			'tags' => array('PHP', 'MySQL'),
		);

		$this->assertEquals($expected, $book->toArray());
	}



	function testManyToOne()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);
		$author = $book->getAuthor();
		$this->assertEquals('Jakub Vrana', $author->getName());
	}



	function testManyToMany()
	{
		$book = ServiceLocator::getBookRepository()->getByID(1);
		$tags = array();
		foreach ($book->getTags() as $tag) {
			$tags[] = $tag->name;
		}

		$this->assertEquals(array('PHP', 'MySQL'), $tags);
	}



	function testSearch()
	{
		$books = array();
		foreach (ServiceLocator::getBookRepository()->getByTag('PHP') as $book) {
			$books[] = $book->bookTitle;
		}

		$this->assertEquals(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
	}



	function testCount()
	{
		$allBooks = ServiceLocator::getBookRepository()->getAll();
		$bookTags = ServiceLocator::getBookRepository()->getByID(3)->getTags();

		$this->assertEquals(4, count($allBooks->limit(2))); // data not received yet -> count as non-limited
		$this->assertEquals(2, count($allBooks->limit(2)->toArray())); // data received
		$this->assertEquals(1, count($bookTags));
	}



	/** @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit */
	function testPresenterFlow()
	{
		// load data
		$books = ServiceLocator::getBookRepository()->getAll();

		// paginate result
		$paginator = new Nette\Utils\Paginator;
		$paginator->itemsPerPage = 2;
		$paginator->itemCount = count($books);
		$paginator->page = 2;

		$books->limit($paginator->length, $paginator->offset);

		// render them ordered in template
		$array = array();
		foreach ($books->orderBy('book_title') as $book) {
			$array[] = $book->bookTitle;
		}

		$this->assertEquals(array('JUSH', 'Nette'), $array);
	}



	/** Tests equality of queries using native & YetORM data access */
	function testQueries()
	{
		$context = ServiceLocator::getDbContext();

		$context->getConnection()->onQuery['queryDump'] = function (NConnection $c, NResultSet $r) {
			echo $r->getQueryString() . "\n";
		};


		ob_start();

			foreach ($context->table('book') as $book) {
				foreach ($book->related('book_tag')->order('tag.name DESC') as $book_tag) {
					echo $book_tag->tag->name, ', ';
				}
			}

		$native = ob_get_clean();


		ob_start();

			foreach (ServiceLocator::getBookRepository()->getAll() as $book) {
				foreach ($book->getTags()->orderBy('tag.name', EC::DESC) as $tag) {
					echo $tag->name, ', ';
				}
			}

		$repository = ob_get_clean();

		unset($context->getConnection()->onQuery['queryDump']);
		$this->assertEquals($native, $repository);
	}



	function testCreateAndUpdate()
	{
		// === CREATION

		$repo = ServiceLocator::getBookRepository();

		$book = $repo->createBook();
		$book->bookTitle = 'Texy 2';
		$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(12));
		$book->written = new Nette\DateTime('2008-01-01');
		$repo->persist($book);

		$this->assertEquals(array(
			'id' => 5,
			'bookTitle' => 'Texy 2',
			'author' => array(
				'id' => 12,
				'name' => 'David Grudl',
				'web' => 'http://davidgrudl.com/',
				'born' => NULL,
			),
			'written' => new Nette\DateTime('2008-01-01'),
			'available' => TRUE,
			'tags' => array(),

		), $book->toArray());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());


		// === UPDATE

		$repo = ServiceLocator::getBookRepository();

		// change title
		$book = $repo->getByID(5);
		$book->bookTitle = 'New title';
		$this->assertEquals('New title', $book->bookTitle);

		$rows = $repo->persist($book);
		$this->assertEquals(1, $rows);
		$this->assertEquals('New title', $book->bookTitle);


		// change author
		$author = ServiceLocator::getAuthorRepository()->getByID(13);
		$this->assertEquals('Geek', $author->getName());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());
		$book->setAuthor($author);
		$rows = $repo->persist($book);

		$this->assertEquals(1, $rows);
		$this->assertEquals('Geek', $book->getAuthor()->getName());


		// change availability
		$book->available = FALSE;
		$repo->persist($book);
		$this->assertFalse($book->available);

		$this->assertEquals(array(
			'id' => 5,
			'bookTitle' => 'New title',
			'author' => array(
				'id' => 13,
				'name' => 'Geek',
				'web' => 'http://example.com',
				'born' => NULL,
			),
			'written' => new Nette\DateTime('2008-01-01'),
			'available' => FALSE,
			'tags' => array(),

		), $book->toArray());
	}



	function testDelete()
	{
		$repo = ServiceLocator::getBookRepository();
		$this->assertEquals(5, count($repo->getAll()));

		$rows = $repo->delete($repo->getByID(5));
		$this->assertEquals(1, $rows);

		$this->assertEquals(4, count($repo->getAll()));
	}

}
