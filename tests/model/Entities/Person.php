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

}
