<?php

require_once __DIR__ . '/bootstrap.php';

use Tester\Assert;
use YetORM\EntityCollection as EC;
use Nette\Database\ResultSet as NResultSet;
use Nette\Database\Connection as NConnection;


// entity loading
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	Assert::true($book instanceof Model\Entities\Book);

	Assert::equal(array(
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

	), $book->toArray());
});


// many to one
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);
	$author = $book->getAuthor();
	Assert::equal('Jakub Vrana', $author->getName());
});


// many to many
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);

	$tags = array();
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->name;
	}

	Assert::equal(array('PHP', 'MySQL'), $tags);
});


// backjoin filter
test(function () {
	$books = array();
	foreach (ServiceLocator::getBookRepository()->getByTag('PHP') as $book) {
		$books[] = $book->bookTitle;
	}

	Assert::equal(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
});


// count
test(function () {
	$repo = ServiceLocator::getBookRepository();
	$allbooks = $repo->getAll();
	$booktags = $repo->getByID(3)->getTags();

	Assert::equal(4, count($allbooks->limit(2))); // data not received yet -> count as non-limited
	Assert::equal(2, count($allbooks->limit(2)->toArray())); // data received
	Assert::equal(1, count($booktags));
});


/**
 * presenter flow
 * @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit
 */
test(function () {
	// prepare data
	$books = ServiceLocator::getBookRepository()->getAll();

	// paginate result
	$paginator = new Nette\Utils\Paginator;
	$paginator->setItemsPerPage(2);
	$paginator->setItemCount(count($books));
	$paginator->setPage(2);

	$books->limit($paginator->getLength(), $paginator->getOffset());

	// render them ordered in template
	$array = array();
	foreach ($books->orderBy('book_title') as $book) {
		$array[] = $book->bookTitle;
	}

	Assert::equal(array('JUSH', 'Nette'), $array);
});


// equality of queries using native & YetORM data access
test(function () {
	$context = ServiceLocator::getDbContext();
	$context->getConnection()->onQuery['queryDump'] = function (NConnection $c, NResultSet $r) {
		echo $r->getQueryString(), "\n";
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

		$yetorm = ob_get_clean();

	unset($context->getConnection()->onQuery['queryDump']);
	Assert::equal($native, $yetorm);
});


// create & update
test(function () {
	$repo = ServiceLocator::getBookRepository();

	// creation
	$book = $repo->createBook();
	$book->bookTitle = 'Texy 2';
	$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(12));
	$book->written = new Nette\Utils\DateTime('2008-01-01');
	$repo->persist($book);

	Assert::equal(array(
		'id' => 5,
		'bookTitle' => 'Texy 2',
		'author' => array(
			'id' => 12,
			'name' => 'David Grudl',
			'web' => 'http://davidgrudl.com/',
			'born' => NULL,
		),
		'written' => new Nette\Utils\DateTime('2008-01-01'),
		'available' => TRUE,
		'tags' => array(),

	), $book->toArray());

	Assert::equal('David Grudl', $book->getAuthor()->getName());

	// update
	$book = $repo->getByID(5);
	$book->bookTitle = 'New title';
	Assert::equal('New title', $book->bookTitle);
	Assert::equal(TRUE, $repo->persist($book));
	Assert::equal('New title', $book->bookTitle);
	$author = ServiceLocator::getAuthorRepository()->getByID(13);
	Assert::equal('Geek', $author->getName());
	Assert::equal('David Grudl', $book->getAuthor()->getName());
	$book->setAuthor($author);
	Assert::equal(TRUE, $repo->persist($book));
	Assert::equal('Geek', $book->getAuthor()->getName());
	$book->available = FALSE;
	$repo->persist($book);
	Assert::false($book->available);
	Assert::equal(array(
		'id' => 5,
		'bookTitle' => 'New title',
		'author' => array(
			'id' => 13,
			'name' => 'Geek',
			'web' => 'http://example.com',
			'born' => NULL,
		),
		'written' => new Nette\Utils\DateTime('2008-01-01'),
		'available' => FALSE,
		'tags' => array(),

	), $book->toArray());
});


// delete
test(function () {
	$repo = ServiceLocator::getBookRepository();
	Assert::equal(5, count($repo->getAll()));
	Assert::equal(TRUE, $repo->delete($repo->getByID(5)));
	Assert::equal(4, count($repo->getAll()));
});


// events
test(function () {
	$fired = FALSE;

	$repo = ServiceLocator::getBookRepository();
	$book = $repo->getByID(1);
	$book->onPersist[] = function () use (& $fired) {
		$fired = TRUE;
	};

	$repo->persist($book);
	Assert::true($fired);
});
