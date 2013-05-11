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

	/** @var string */
	protected $entity;

	/** @var string */
	protected $name;

	/** @var bool */
	protected $readonly;



	/**
	 * @param  string
	 * @param  string
	 * @param  bool
	 */
	function __construct($entity, $name, $readonly)
	{
		$this->entity = (string) $entity;
		$this->name = (string) $name;
		$this->readonly = (bool) $readonly;
	}



	/** @return bool */
	function isReadonly()
	{
		return $this->readonly;
	}

}
