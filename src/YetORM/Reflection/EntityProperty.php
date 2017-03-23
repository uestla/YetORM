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


abstract class EntityProperty
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
		$this->name = $name;
		$this->type = $type;
		$this->readonly = $readonly;
		$this->reflection = $reflection;
		$this->description = $description === NULL ? NULL : (string) $description;
	}


	/**
	 * @param  Entity $entity
	 * @return mixed
	 */
	abstract public function getValue(Entity $entity);


	/**
	 * @param  Entity $entity
	 * @param  mixed $value
	 * @return void
	 */
	abstract public function setValue(Entity $entity, $value);


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
