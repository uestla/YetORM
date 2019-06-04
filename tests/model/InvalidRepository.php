<?php

namespace Model\Repositories;

use YetORM;
use Model\Entities;


/** Notice no "table" nor "entity" annotation nor class property */
class InvalidRepository extends YetORM\Repository
{

	/** @return void */
	public function testNoTable(): void
	{
		$this->getTable();
	}


	/** @return void */
	public function testNoEntity(): void
	{
		$this->getEntityClass();
	}

}
