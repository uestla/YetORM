<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2016 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Reflection;

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

		$this->column = (string) $column;
		$this->nullable = (bool) $nullable;
	}


	/** @return string */
	public function getColumn()
	{
		return $this->column;
	}


	/** @return bool */
	public function isNullable()
	{
		return $this->nullable;
	}


	/**
	 * @param  mixed $value
	 * @param  bool $need
	 * @return bool
	 */
	public function checkType($value, $need = TRUE)
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

		} elseif ($need && ($type = gettype($value)) !== $this->getType()) {
			throw new Exception\InvalidArgumentException("Invalid type - '{$this->getType()}' expected, '$type' given.");

		} else {
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * @param  mixed $value
	 * @return mixed
	 */
	public function setType($value)
	{
		if (!$this->checkType($value, FALSE)) { // type casting needed
			settype($value, $this->getType());
		}

		return $value;
	}

}
