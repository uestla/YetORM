<?php


/**
 * @property-read int $id
 * @property-read string $name
 */
class Author extends YetORM\Entity
{

	/** @return YetORM\EntityCollection */
	function getBooks()
	{
		return new YetORM\EntityCollection($this->row->related('book'), 'Book');
	}

}
