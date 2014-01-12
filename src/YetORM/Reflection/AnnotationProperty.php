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

use YetORM;


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
	 */
	function __construct($reflection, $name, $readonly, $type, $column, $nullable)
	{
		parent::__construct($reflection, $name, $readonly, $type);

		$this->column = (string) $column;
		$this->nullable = (bool) $nullable;
	}



	/** @return string */
	function getColumn()
	{
		return $this->column;
	}



	/**
	 * @param  mixed $value
	 * @param  bool $need
	 * @return mixed
	 */
	function checkType($value, $need = TRUE)
	{
		if ($value === NULL) {
			if (!$this->nullable) {
				$entity = $this->getEntityReflectioin()->getName();
				throw new YetORM\Exception\InvalidArgumentException("Property '{$entity}::\${$this->getName()}' cannot be NULL.");
			}

		} elseif (!$this->isOfNativeType()) {
			$class = $this->getType();
			if (!($value instanceof $class)) {
				throw new YetORM\Exception\InvalidArgumentException("Instance of '{$class}' expected, '"
						. get_class($value) . "' given.");
			}

		} elseif ($need && ($type = gettype($value)) !== $this->getType()) {
			throw new YetORM\Exception\InvalidArgumentException("Invalid type - '{$this->getType()}' expected, '$type' given.");

		} else {
			return FALSE;
		}

		return TRUE;
	}



	/**
	 * @param  mixed $value
	 * @return mixed
	 */
	function setType($value)
	{
		if (!$this->checkType($value, FALSE) && @settype($value, $this->getType()) === FALSE) { // intentionally @
			throw new YetORM\Exception\InvalidArgumentException("Unable to set type '{$this->getType()}' from '"
				. gettype($value) . "'.");
		}

		return $value;
	}

}
