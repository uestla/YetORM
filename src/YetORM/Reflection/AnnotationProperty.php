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
use YetORM\Exception;


class AnnotationProperty extends EntityProperty
{

	/** @var string */
	private $column;

	/** @var bool */
	private $nullable;


	/**
	 * @param  EntityType $reflection
	 * @param  string $name
	 * @param  bool $readonly
	 * @param  string $type
	 * @param  string $column
	 * @param  bool $nullable
	 * @param  string $description
	 */
	public function __construct($reflection, $name, $readonly, $type, $column, $nullable, $description = NULL)
	{
		parent::__construct($reflection, $name, $readonly, $type, $description);

		$this->column = $column;
		$this->nullable = $nullable;
	}


	/** @inheritdoc */
	public function getValue(Entity $entity)
	{
		$value = $this->setType($entity->toRecord()->{$this->getColumn()});
		return $value;
	}


	/** @inheritdoc */
	public function setValue(Entity $entity, $value): void
	{
		if ($this->isReadonly()) {
			$ref = $entity::getReflection();
			throw new Exception\MemberAccessException("Cannot write to a read-only property {$ref->getName()}::\${$this->getName()}.");
		}

		$this->checkType($value);
		$entity->toRecord()->{$this->getColumn()} = $value;
	}


	public function getColumn(): string
	{
		return $this->column;
	}


	public function isNullable(): bool
	{
		return $this->nullable;
	}


	/**
	 * @param  mixed $value
	 * @param  bool $need
	 */
	public function checkType($value, $need = TRUE): bool
	{
		if ($value === NULL) {
			if (!$this->nullable) {
				$entity = $this->getEntityReflection()->getName();
				throw new Exception\InvalidArgumentException("Property '{$entity}::\${$this->getName()}' cannot be NULL.");
			}

		} elseif (!$this->isOfNativeType()) {
			$class = $this->getType();
			if (!($value instanceof $class)) {
				throw new Exception\InvalidArgumentException("Instance of '{$class}' expected, '"
						. (($valtype = gettype($value)) === 'object' ? get_class($value) : $valtype) . "' given.");
			}

		} elseif ($need && !call_user_func('is_' . $this->getType(), $value)) {
			throw new Exception\InvalidArgumentException("Invalid type - '{$this->getType()}' expected, '" . gettype($value) . "' given.");

		} else {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * @param  mixed $value
	 */
	public function setType($value)
	{
		if (!$this->checkType($value, FALSE)) { // type casting needed
			settype($value, $this->getType());
		}

		return $value;
	}

}
