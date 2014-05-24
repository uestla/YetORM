<?php

namespace Model\Repositories;

use Model;
use YetORM;


/**
 * @table  author
 * @entity Model\Entities\Author
 */
class AuthorRepository extends YetORM\Repository
{

	/**
	 * @param  int $id
	 * @return Model\Entities\Author
	 */
	function getByID($id)
	{
		return new Model\Entities\Author($this->getTable()->get($id));
	}

}
