<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Reflection;

use YetORM\Entity;


class MethodProperty extends EntityProperty
{

	/** @inheritdoc */
	public function getValue(Entity $entity)
	{
		return $entity->{'get' . ucfirst($this->getName())}();
	}


	/** @inheritdoc */
	public function setValue(Entity $entity, $value): void
	{
		$entity->{'set' . ucfirst($this->getName())}($value);
	}

}
