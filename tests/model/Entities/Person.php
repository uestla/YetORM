<?php

namespace Model\Entities;

use Nette;
use YetORM;


/**
 * @property-read int $id
 * @property-read Nette\Utils\DateTime|NULL $born
 */
abstract class Person extends YetORM\Entity
{

	/** @return string */
	function getName()
	{
		return Nette\Utils\Strings::capitalize($this->row->name);
	}

}
