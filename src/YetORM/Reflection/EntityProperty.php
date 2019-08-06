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
	 */
	abstract public function getValue(Entity $entity);


	/**
	 * @param  Entity $entity
	 * @param  mixed $value
	 */
	abstract public function setValue(Entity $entity, $value): void;


	public function getEntityReflection(): EntityType
	{
		return $this->reflection;
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function isReadonly(): bool
	{
		return $this->readonly;
	}


	public function getType(): string
	{
		return $this->type;
	}


	public function getDescription(): ?string
	{
		return $this->description;
	}


	public function hasDescription(): bool
	{
		return $this->description !== NULL;
	}


	public function isOfNativeType(): bool
	{
		return self::isNativeType($this->type);
	}


	/**
	 * @param  string $type
	 */
	public static function isNativeType($type): bool
	{
		return $type !== NULL && ($type === 'int' || $type === 'float' || $type === 'double'
				|| $type === 'bool' ||  $type === 'string' || $type === 'array');
	}

}
