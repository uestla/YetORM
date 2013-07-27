<?php

namespace Model\Repositories;

use Model;
use YetORM;


/** @entity Model\Entities\Author */
class AuthorRepository extends YetORM\Repository
{

	/**
	 * @param  int
	 * @return Model\Entities\Author
	 */
	function findById($id)
	{
		return new Model\Entities\Author($this->getTable()->get($id));
	}

}
