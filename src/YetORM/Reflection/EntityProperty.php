<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2014 Petr Kessler (http://kesspess.1991.cz)
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
	private $reflection;

	/** @var string */
	private $name;

	/** @var bool */
	private $readonly;

	/** @var string */
	private $type;



	/**
	 * @param  EntityType $reflection
	 * @param  string $name
	 * @param  bool $readonly
	 * @param  string $type
	 */
	function __construct(EntityType $reflection, $name, $readonly, $type)
	{
		$this->reflection = $reflection;
		$this->name = (string) $name;
		$this->readonly = (bool) $readonly;
		$this->type = (string) $type;
	}



	/** @return EntityType */
	function getEntityReflectioin()
	{
		return $this->reflection;
	}



	/** @return string */
	function getName()
	{
		return $this->name;
	}



	/** @return bool */
	function isReadonly()
	{
		return $this->readonly;
	}



	/** @return string */
	function getType()
	{
		return $this->type;
	}



	/** @return bool */
	function isOfNativeType()
	{
		return self::isNativeType($this->type);
	}



	/**
	 * @param  string $type
	 * @return bool
	 */
	static function isNativeType($type)
	{
		return $type !== NULL && ($type === 'integer' || $type === 'float' || $type === 'double'
				|| $type === 'boolean' ||  $type === 'string' || $type === 'array');
	}

}
