<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2015 Petr Kessler (http://kesspess.1991.cz)
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

	/** @var string */
	private $description;


	/**
	 * @param  EntityType $reflection
	 * @param  string $name
	 * @param  bool $readonly
	 * @param  string $type
	 * @param  string $description
	 */
	public function __construct(EntityType $reflection, $name, $readonly, $type, $description = NULL)
	{
		$this->reflection = $reflection;
		$this->name = (string) $name;
		$this->readonly = (bool) $readonly;
		$this->type = (string) $type;
		$this->description = $description === NULL ? NULL : (string) $description;
	}


	/** @return EntityType */
	public function getEntityReflection()
	{
		return $this->reflection;
	}


	/** @return string */
	public function getName()
	{
		return $this->name;
	}


	/** @return bool */
	public function isReadonly()
	{
		return $this->readonly;
	}


	/** @return string */
	public function getType()
	{
		return $this->type;
	}


	/** @return string|NULL */
	public function getDescription()
	{
		return $this->description;
	}


	/** @return bool */
	public function hasDescription()
	{
		return $this->description !== NULL;
	}


	/** @return bool */
	public function isOfNativeType()
	{
		return self::isNativeType($this->type);
	}


	/**
	 * @param  string $type
	 * @return bool
	 */
	public static function isNativeType($type)
	{
		return $type !== NULL && ($type === 'integer' || $type === 'float' || $type === 'double'
				|| $type === 'boolean' ||  $type === 'string' || $type === 'array');
	}

}
