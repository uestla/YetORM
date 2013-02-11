<?php


/**
 * @property string $title
 * @property string $written
 * @property-read int $id
 */
class Book extends YetORM\Entity
{

	/** @return int */
	function getId()
	{
		return $this->row->id;
	}



	/** @return Author */
	function getAuthor()
	{
		return new Author($this->row->author);
	}



	/**
	 * @param  Author
	 * @return Book
	 */
	function setAuthor(Author $author)
	{
		$this->row->author_id = $author->getId();
		return $this;
	}



	/** @return YetORM\EntityCollection */
	function getTags()
	{
		return $this->getMany('Tag', 'book_tag', 'tag');
	}

}
