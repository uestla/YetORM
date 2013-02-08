<?php


class BookRepository extends YetORM\Repository
{

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
