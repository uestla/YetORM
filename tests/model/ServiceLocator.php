<?php

require_once __DIR__ . '/db/connection.php';


class ServiceLocator
{

	/** @var BookRepository */
	protected static $bookRepository = NULL;

	/** @var AuthorRepository */
	protected static $authorRepository = NULL;

	/** @var BookFacade */
	protected static $bookFacade = NULL;



	static function getBookRepository()
	{
		if (static::$bookRepository === NULL) {
			static::$bookRepository = new BookRepository(getConnection());
		}

		return static::$bookRepository;
	}



	static function getAuthorRepository()
	{
		if (static::$authorRepository === NULL) {
			static::$authorRepository = new AuthorRepository(getConnection());
		}

		return static::$authorRepository;
	}



	static function getBookFacade()
	{
		if (static::$bookFacade === NULL) {
			static::$bookFacade = new BookFacade(static::getBookRepository());
		}

		return static::$bookFacade;
	}



	static function createTestingBook()
	{
		return static::getBookRepository()->create(array(
			'author_id' => 12,
			'book_title' => 'Texy 2',
			'written' => '2008',
			'available' => TRUE,
		));
	}

}
