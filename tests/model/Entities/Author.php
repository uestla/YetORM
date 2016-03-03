<?php

namespace Model\Entities;

use YetORM;


/** @property-read string $web Author's personal website          */
class Author extends Person
{

	/** @return YetORM\EntityCollection */
	public function getBooks()
	{
		return new YetORM\EntityCollection($this->record->related('book', 'book_id'), 'Model\Entities\Book');
	}

}
