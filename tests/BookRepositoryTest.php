<?php

require_once __DIR__ . '/db/connection.php';


class BookRepositoryTest extends PHPUnit_Framework_TestCase
{

	function testEntity()
	{
		$book = static::getRepository()->findById(1);

		$this->assertTrue($book instanceof Book);

		$expected = array(
			'id' => 1,
			'title' => '1001 tipu a triku pro PHP',
			'year' => '2010',
		);

		$this->assertEquals($expected, $book->toArray());
	}



	function testOneToOne()
	{
		$book = static::getRepository()->findById(1);
		$author = $book->getAuthor();
		$this->assertEquals('Jakub Vrana', $author->getName());
	}



	function testOneToMany()
	{
		$book = static::getRepository()->findById(1);
		$tags = array();
		foreach ($book->getTags() as $tag) {
			$tags[] = $tag->getName();
		}

		$this->assertEquals(array('PHP', 'MySQL'), $tags);
	}



	function testSearch()
	{
		$books = array();
		foreach (static::getRepository()->findByTag('PHP') as $book) {
			$books[] = $book->getTitle();
		}

		$this->assertEquals(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
	}



	function testCount()
	{
		$allBooks = static::getRepository()->findAll();
		$bookTags = static::getRepository()->findById(3)->getTags();

		$this->assertEquals(4, count($allBooks->limit(2))); // data not received yet -> count as non-limited
		$this->assertEquals(2, count($allBooks->limit(2)->getData())); // data received
		$this->assertEquals(1, count($bookTags));
	}



	/** @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit */
	function testPresenterFlow()
	{
		// load data
		$books = static::getRepository()->findAll();

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

			foreach (static::getRepository()->findAll() as $book) {
				foreach ($book->getTags()->orderBy('tag.name', TRUE) as $tag) {
					echo $tag->getName(), ', ';
				}
			}

		$repository = ob_get_clean();

		$this->assertEquals($native, $repository);


		unset($connection->onQuery['queryDump']);
	}



	// =========================================

	protected static function getRepository()
	{
		static $repo;

		if ($repo === NULL) {
			$repo = new BookRepository(getConnection());
		}

		return $repo;
	}

}
