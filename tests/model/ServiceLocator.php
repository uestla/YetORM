<?php


class ServiceLocator
{

	/** @var Nette\Caching\Storages\FileStorage */
	private static $cacheStorage = NULL;

	/** @var Nette\Database\Connection */
	private static $connection = NULL;

	/** @var Model\Repositories\BookRepository */
	private static $bookRepository = NULL;

	/** @var Model\Repositories\AuthorRepository */
	private static $authorRepository = NULL;

	/** @var Model\Services\BookService */
	private static $bookService = NULL;



	/** @return Nette\Caching\Storages\FileStorage */
	static function getCacheStorage()
	{
		if (static::$cacheStorage === NULL) {
			static::$cacheStorage = new Nette\Caching\Storages\FileStorage(__DIR__ . '/../temp');
		}

		return static::$cacheStorage;
	}



	/** @return Nette\Database\Connection */
	static function getConnection()
	{
		if (static::$connection === NULL) {
			static::$connection = new Nette\Database\Connection('mysql:host=localhost;dbname=yetorm_test', 'root', '');
			static::$connection->setCacheStorage(static::getCacheStorage());
			Nette\Database\Helpers::loadFromFile(static::$connection, __DIR__ . '/db/db.sql');
		}

		return static::$connection;
	}



	/** @return Model\Repositories\BookRepository */
	static function getBookRepository()
	{
		if (static::$bookRepository === NULL) {
			static::$bookRepository = new Model\Repositories\BookRepository(static::getConnection(), __DIR__ . '/books');
		}

		return static::$bookRepository;
	}



	/** @return Model\Repositories\AuthorRepository */
	static function getAuthorRepository()
	{
		if (static::$authorRepository === NULL) {
			static::$authorRepository = new Model\Repositories\AuthorRepository(static::getConnection());
		}

		return static::$authorRepository;
	}



	/** @return Model\Services\BookService */
	static function getBookFacade()
	{
		if (static::$bookService === NULL) {
			static::$bookService = new Model\Services\BookService(static::getBookRepository());
		}

		return static::$bookService;
	}

}
