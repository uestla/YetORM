<?php

namespace Model\Entities;

use YetORM;


/** @property-read string $web */
class Author extends Person
{

	/** @return YetORM\EntityCollection */
	function getBooks()
	{
		return new YetORM\EntityCollection($this->record->related('book'), 'Model\Entities\Book');
	}

}
