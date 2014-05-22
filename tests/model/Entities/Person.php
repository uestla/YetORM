<?php

namespace Model\Entities;

use Nette;


/**
 * @property-read int $id
 * @property-read Nette\Utils\DateTime|NULL $born
 */
abstract class Person extends BaseEntity
{

	/** @return string */
	function getName()
	{
		return Nette\Utils\Strings::capitalize($this->row->name);
	}

}
