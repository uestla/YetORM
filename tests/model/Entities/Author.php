<?php


class Author extends YetORM\Entity
{

	/** @return string */
	function getName()
	{
		return $this->row->name;
	}



	/** @return YetORM\EntityCollection */
	function getBooks()
	{
		return new YetORM\EntityCollection($this->row->related('book'), 'Book');
	}

}
