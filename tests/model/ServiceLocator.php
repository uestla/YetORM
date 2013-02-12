<?php


class ServiceLocator
{

	/** @var BookRepository */
	protected static $bookRepository = NULL;

	/** @var AuthorRepository */
	protected static $authorRepository = NULL;



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
