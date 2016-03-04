<?php

namespace Model\Entities;

use Nette;


/**
 * @property-read int $id
 * @property-read Nette\Utils\DateTime|NULL $born   person's birthday
 */
abstract class Person extends BaseEntity
{

	/** @return string */
	public function getName()
	{
		return Nette\Utils\Strings::capitalize($this->record->name);
	}


	/** @return bool */
	public function hasName()
	{
		return isset($this->record->name) && strlen($this->record->name);
	}

}
