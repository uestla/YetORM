<?php

namespace Model\Entities;

use YetORM\Entity;


/** @property NULL|NULL $badOne */
class BadDoubleNullEntity extends Entity
{}


/** @property \DateTime|string $evil */
class BadMultipleTypeEntity extends Entity
{}


/** @property string nodollar */
class MissingDollarEntity extends Entity
{}


/** @property $missingType */
class MissingTypeEntity extends Entity
{}


/** @property string $name */
class BothPropertyDefinitionEntity extends Entity
{
	/** @return string */
	public function getName()
	{
		return $this->record->name;
	}
}
