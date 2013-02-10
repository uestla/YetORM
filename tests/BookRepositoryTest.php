<?php

require_once __DIR__ . '/db/connection.php';


class BookRepositoryTest extends PHPUnit_Framework_TestCase
{

	function testEntity()
	{
		$book = static::getBookRepository()->findById(1);

		$this->assertTrue($book instanceof Book);

		$expected = array(
			'id' => 1,
			'title' => '1001 tipu a triku pro PHP',
			'written' => '2010',
		);

		$this->assertEquals($expected, $book->toArray());
	}



	function testOneToOne()
	{
		$book = static::getBookRepository()->findById(1);
		$author = $book->getAuthor();
		$this->assertEquals('Jakub Vrana', $author->getName());
	}



	function testOneToMany()
	{
		$book = static::getBookRepository()->findById(1);
		$tags = array();
		foreach ($book->getTags() as $tag) {
			$tags[] = $tag->getName();
		}

		$this->assertEquals(array('PHP', 'MySQL'), $tags);
	}



	function testSearch()
	{
		$books = array();
		foreach (static::getBookRepository()->findByTag('PHP') as $book) {
			$books[] = $book->getTitle();
		}

		$this->assertEquals(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
	}



	function testCount()
	{
		$allBooks = static::getBookRepository()->findAll();
		$bookTags = static::getBookRepository()->findById(3)->getTags();

		$this->assertEquals(4, count($allBooks->limit(2))); // data not received yet -> count as non-limited
		$this->assertEquals(2, count($allBooks->limit(2)->getData())); // data received
		$this->assertEquals(1, count($bookTags));
	}



	/** @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit */
	function testPresenterFlow()
	{
		// load data
		$books = static::getBookRepository()->findAll();

		// paginate result
		$paginator = new Nette\Utils\Paginator;
		$paginator->itemsPerPage = 2;
		$paginator->itemCount = count($books);
		$paginator->page = 2;

		$books->limit($paginator->length, $paginator->offset);

		// render them ordered in template
		$array = array();
		foreach ($books->orderBy('title') as $book) {
			$array[] = $book->getTitle();
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

			foreach (static::getBookRepository()->findAll() as $book) {
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
		$book = $this->createTestingBook();

		$this->assertTrue($book instanceof Book);
		$this->assertEquals(array(
			'id' => 5,
			'title' => 'Texy 2',
			'written' => '2008',

		), $book->toArray());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());
	}



	function testEdit()
	{
		$repo = static::getBookRepository();

		$book = $repo->findById(5);
		$book->setTitle('New title');
		$this->assertEquals('New title', $book->getTitle());

		$rows = $repo->persist($book);
		$this->assertEquals(1, $rows);
		$this->assertEquals('New title', $book->getTitle());


		$author = static::getAuthorRepository()->findById(13);
		$this->assertEquals('Geek', $author->getName());

		$this->assertEquals('David Grudl', $book->getAuthor()->getName());
		$book->setAuthor($author);
		$rows = $repo->persist($book);

		$this->assertEquals(1, $rows);
		$this->assertEquals('Geek', $book->getAuthor()->getName());
	}



	function testDelete()
	{
		$repo = static::getBookRepository();
		$this->assertEquals(5, count($repo->findAll()));

		$rows = $repo->delete($repo->findById(5));
		$this->assertEquals(1, $rows);

		$this->assertEquals(4, count($repo->findAll()));
	}



	// =========================================

	protected static function getBookRepository()
	{
		static $repo;

		if ($repo === NULL) {
			$repo = new BookRepository(getConnection());
		}

		return $repo;
	}



	protected static function getAuthorRepository()
	{
		static $repo;

		if ($repo === NULL) {
			$repo = new AuthorRepository(getConnection());
		}

		return $repo;
	}



	protected function createTestingBook()
	{
		return static::getBookRepository()->create(array(
			'author_id' => 12,
			'title' => 'Texy 2',
			'written' => '2008',
		));
	}

}
