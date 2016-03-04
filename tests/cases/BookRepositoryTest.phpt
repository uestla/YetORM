<?php

require_once __DIR__ . '/../bootstrap.php';

use Tester\Assert;
use YetORM\EntityCollection as EC;
use Nette\Database\ResultSet as NResultSet;
use Nette\Database\Connection as NConnection;


// entity loading
test(function () {
	$repo = ServiceLocator::getBookRepository();

	$book = $repo->getByID(1);
	Assert::true($book instanceof Model\Entities\Book);

	$book2 = $repo->getBy([
		'id' => 1,
	]);

	Assert::true($book2 instanceof Model\Entities\Book);
	Assert::equal($book->toArray(), $book2->toArray());

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
	Assert::same('Jakub Vrana', $author->getName());
});


// many to many
test(function () {
	$book = ServiceLocator::getBookRepository()->getByID(1);

	$tags = array();
	foreach ($book->getTags() as $tag) {
		$tags[] = $tag->name;
	}

	Assert::same(array('PHP', 'MySQL'), $tags);
});


// backjoin filter
test(function () {
	$books = array();
	foreach (ServiceLocator::getBookRepository()->findByTag('PHP') as $book) {
		$books[] = $book->bookTitle;
	}

	Assert::same(array('1001 tipu a triku pro PHP', 'Nette', 'Dibi'), $books);
});


// count
test(function () {
	$repo = ServiceLocator::getBookRepository();
	$allbooks = $repo->findAll();
	$booktags = $repo->getByID(3)->getTags();

	Assert::same(4, count($allbooks->limit(2))); // data not received yet -> count as non-limited
	Assert::same(2, count($allbooks->limit(2)->toArray())); // data received
	Assert::same(2, count($allbooks)); // count collection after data receival
	Assert::same(1, count($booktags));
});


/**
 * presenter flow
 * @see http://phpfashion.com/mvc-paradox-a-jak-jej-resit
 */
test(function () {
	// prepare data
	$books = ServiceLocator::getBookRepository()->findAll();

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

	Assert::same(array('JUSH', 'Nette'), $array);
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
		foreach (ServiceLocator::getBookRepository()->findAll() as $book) {
			foreach ($book->getTags()->orderBy('tag.name', EC::DESC) as $tag) {
				echo $tag->name, ', ';
			}
		}

		$yetorm = ob_get_clean();

	unset($context->getConnection()->onQuery['queryDump']);
	Assert::same($native, $yetorm);
});


// create & update
test(function () {
	$repo = ServiceLocator::getBookRepository();

	// creation
	$book = $repo->createEntity();
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

	Assert::same('David Grudl', $book->getAuthor()->getName());

	// update
	$book = $repo->getByID(5);
	$book->bookTitle = 'New title';
	Assert::same('New title', $book->bookTitle);
	Assert::true($repo->persist($book));
	Assert::same('New title', $book->bookTitle);
	$author = ServiceLocator::getAuthorRepository()->getByID(13);
	Assert::same('Geek', $author->getName());
	Assert::same('David Grudl', $book->getAuthor()->getName());
	$book->setAuthor($author);
	Assert::true($repo->persist($book));
	Assert::same('Geek', $book->getAuthor()->getName());
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
	Assert::same(5, count($repo->findAll()));
	Assert::true($repo->delete($repo->getByID(5)));
	Assert::same(4, count($repo->findAll()));
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


// custom exception
test(function () {
	Assert::exception(function () {
		$repo = ServiceLocator::getBookRepository();
		$book = $repo->createEntity();
		$book->bookTitle = 'Nette'; // duplicate title
		$book->setAuthor(ServiceLocator::getAuthorRepository()->getByID(11));
		$book->written = new Nette\Utils\DateTime;
		$book->available = TRUE;
		$repo->persist($book);

	}, 'Model\Repositories\DuplicateEntryException');
});


// magic findBy() method
test(function () {
	Assert::same(4, count(ServiceLocator::getBookRepository()->findByAvailable(TRUE)));

	// nonexisting property
	Assert::exception(function () {
		ServiceLocator::getBookRepository()->findByFoo('bar');

	}, 'YetORM\Exception\InvalidArgumentException', "Property '\$foo' not found in entity 'Model\Entities\Book'.");

	// wrong number of arguments
	Assert::exception(function () {
		ServiceLocator::getBookRepository()->findByAvailable(TRUE, FALSE);

	}, 'YetORM\Exception\InvalidArgumentException', 'Wrong number of argument passed to findByAvailable method - 1 expected, 2 given.');
});


// magic getBy() method
test(function () {
	Assert::true(ServiceLocator::getBookRepository()->getByBookTitle('Nette') instanceof \Model\Entities\Book);
	Assert::null(ServiceLocator::getBookRepository()->getByBookTitle('as567tfa6sd54f6'));

	// nonexisting property
	Assert::exception(function () {
		ServiceLocator::getBookRepository()->getByFoo('bar');

	}, 'YetORM\Exception\InvalidArgumentException', "Property '\$foo' not found in entity 'Model\Entities\Book'.");

	// wrong number of arguments
	Assert::exception(function () {
		ServiceLocator::getBookRepository()->getByBookTitle('Nette', FALSE);

	}, 'YetORM\Exception\InvalidArgumentException', 'Wrong number of argument passed to getByBookTitle method - 1 expected, 2 given.');
});


// entity check on perist
test(function () {
	Assert::exception(function () {
		ServiceLocator::getAuthorRepository()->persist(ServiceLocator::getBookRepository()->createEntity());

	}, 'YetORM\Exception\InvalidArgumentException', "Instance of 'Model\Entities\Author' expected, 'Model\Entities\Book' given.");
});
