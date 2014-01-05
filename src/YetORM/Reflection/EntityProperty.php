<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Reflection;

use Nette;


/** @property-read bool $readonly */
abstract class EntityProperty extends Nette\Object
{

	/** @var EntityType */
	protected $reflection;

	/** @var string */
	protected $name;

	/** @var bool */
	protected $readonly;



	/**
	 * @param  EntityType $reflection
	 * @param  string $name
	 * @param  bool $readonly
	 */
	function __construct(EntityType $reflection, $name, $readonly)
	{
		$this->reflection = $reflection;
		$this->name = (string) $name;
		$this->readonly = (bool) $readonly;
	}



	/** @return bool */
	function isReadonly()
	{
		return $this->readonly;
	}

}
