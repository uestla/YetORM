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



	/** @return string */
	function getWritten()
	{
		return $this->row->written;
	}



	/** @return Author */
	function getAuthor()
	{
		return new Author($this->row->author);
	}



	/** @return EntityCollection */
	function getTags()
	{
		return $this->getMany('book_tag', 'tag', 'tag_id', 'Tag');
	}

}
