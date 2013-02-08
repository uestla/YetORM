<?php


class BookRepository extends YetORM\Repository
{

	/**
	 * @param  mixed
	 * @return Book
	 */
	function create($values)
	{
		return new Book($this->insert($values));
	}



	/**
	 * @param  Book
	 * @param  mixed
	 * @return int
	 */
	function edit(Book $book, $values)
	{
		return $this->update($book->getActiveRow(), $values);
	}



	/**
	 * @param  Book
	 * @param  mixed
	 * @return int
	 */
	function remove(Book $book)
	{
		return $this->delete($book->getActiveRow());
	}



	/** @return Book */
	function findById($id)
	{
		return new Book($this->getTable()->get($id));
	}



	/** @return EntityCollection */
	function findByTag($name)
	{
		return new YetORM\EntityCollection($this->getTable()->where('book_tag:tag.name', $name), 'Book');
	}



	/** @return EntityCollection */
	function findAll()
	{
		return new YetORM\EntityCollection($this->getTable(), 'Book');
	}

}
