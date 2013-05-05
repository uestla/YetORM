<?php


/**
 * @property-read int $id
 * @property-read Nette\DateTime|NULL $born
 */
abstract class Person extends YetORM\Entity
{

	/** @return string */
	function getName()
	{
		return Nette\Utils\Strings::capitalize($this->row->name);
	}

}
