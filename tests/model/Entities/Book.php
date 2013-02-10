<?php


class Book extends YetORM\Entity
{

	/** @return int */
	function getId()
	{
		return $this->row->id;
	}



	/** @return string */
	function getTitle()
	{
		return $this->row->title;
	}



	/**
	 * @param  string
	 * @return Book
	 */
	function setTitle($title)
	{
		$this->row->title = (string) $title;
		return $this;
	}



	/** @return string */
	function getWritten()
	{
		return $this->row->written;
	}



	/**
	 * @param  string
	 * @return Book
	 */
	function setWritten($written)
	{
		$this->row->written = (string) $written;
		return $this;
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
