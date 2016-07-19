<?php


class ServiceLocator
{

	/** @var Nette\Caching\Storages\FileStorage */
	private static $cacheStorage = NULL;

	/** @var Nette\Database\Context */
	private static $dbContext = NULL;

	/** @var Model\Repositories\BookRepository */
	private static $bookRepository = NULL;

	/** @var Model\Repositories\AuthorRepository */
	private static $authorRepository = NULL;

	/** @var Model\Services\BookService */
	private static $bookService = NULL;

	/** @var Model\Repositories\NoTableRepository */
	private static $invalidRepository = NULL;

	/** @var Model\Repositories\NoPrimaryRepository */
	private static $noPrimaryRepository = NULL;


	/** @return Nette\Caching\Storages\FileStorage */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			self::$cacheStorage = new Nette\Caching\Storages\FileStorage(__DIR__ . '/../temp');
		}

		return self::$cacheStorage;
	}


	/** @return Nette\Database\Context */
	public static function getDbContext()
	{
		if (self::$dbContext === NULL) {
			$connection = new Nette\Database\Connection('mysql:host=127.0.0.1;dbname=yetorm_test', 'root', '');
			Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/db.sql');

			$structure = new Nette\Database\Structure($connection, self::getCacheStorage());
			$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
			self::$dbContext = new Nette\Database\Context($connection, $structure, $conventions, self::getCacheStorage());
		}

		return self::$dbContext;
	}


	/** @return Model\Repositories\BookRepository */
	public static function getBookRepository()
	{
		if (self::$bookRepository === NULL) {
			self::$bookRepository = new Model\Repositories\BookRepository(self::getDbContext(), __DIR__ . '/books');
		}

		return self::$bookRepository;
	}


	/** @return Model\Repositories\AuthorRepository */
	public static function getAuthorRepository()
	{
		if (self::$authorRepository === NULL) {
			self::$authorRepository = new Model\Repositories\AuthorRepository(self::getDbContext());
		}

		return self::$authorRepository;
	}


	/** @return Model\Services\BookService */
	public static function getBookService()
	{
		if (self::$bookService === NULL) {
			self::$bookService = new Model\Services\BookService(self::getBookRepository());
		}

		return self::$bookService;
	}


	/** @return Model\Repositories\InvalidRepository */
	public static function getInvalidRepository()
	{
		if (self::$invalidRepository === NULL) {
			self::$invalidRepository = new Model\Repositories\InvalidRepository(self::getDbContext());
		}

		return self::$invalidRepository;
	}


	/** @return Model\Repositories\NoPrimaryRepository */
	public static function getNoPrimaryRepository()
	{
		if (self::$noPrimaryRepository === NULL) {
			self::$noPrimaryRepository = new Model\Repositories\NoPrimaryRepository(self::getDbContext());
		}

		return self::$noPrimaryRepository;
	}

}
