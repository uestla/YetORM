<?php


class ServiceLocator
{

	/** @var Nette\Database\Connection */
	protected static $connection = NULL;

	/** @var BookRepository */
	protected static $bookRepository = NULL;

	/** @var AuthorRepository */
	protected static $authorRepository = NULL;

	/** @var BookFacade */
	protected static $bookFacade = NULL;



	static function getConnection()
	{
		if (static::$connection === NULL) {
			static::$connection = new Nette\Database\Connection('mysql:host=localhost;dbname=yetorm_test', 'root', '');
			Nette\Database\Helpers::loadFromFile(static::$connection, __DIR__ . '/db/db.sql');
		}

		return static::$connection;
	}



	static function getBookRepository()
	{
		if (static::$bookRepository === NULL) {
			static::$bookRepository = new BookRepository(static::getConnection());
		}

		return static::$bookRepository;
	}



	static function getAuthorRepository()
	{
		if (static::$authorRepository === NULL) {
			static::$authorRepository = new AuthorRepository(static::getConnection());
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
		return static::getBookRepository()->create('Texy 2', 12, new Nette\DateTime('2008-01-01'), TRUE, array('PHP'));
	}

}
