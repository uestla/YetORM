<?php


/** @property-read string $web */
class Author extends Person
{

	/** @return YetORM\EntityCollection */
	function getBooks()
	{
		return new YetORM\EntityCollection($this->row->related('book'), 'Book');
	}

}
