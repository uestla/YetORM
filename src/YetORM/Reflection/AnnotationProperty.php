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

use YetORM;
use Aliaser\Container as Aliaser;


/**
 * @property-read string $type
 * @property-read string $column
 */
class AnnotationProperty extends EntityProperty
{

	/** @var string */
	protected $column;

	/** @var string */
	protected $type;

	/** @var bool */
	protected $nullable;



	/**
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  string
	 * @param  string
	 * @param  bool
	 */
	function __construct($entity, $name, $readonly, $column, $type, $nullable)
	{
		parent::__construct($entity, $name, $readonly);

		$this->column = (string) $column;
		$this->type = (string) $type;
		$this->nullable = (bool) $nullable;
	}



	/** @return string */
	function getColumn()
	{
		return $this->column;
	}



	/** @return string */
	function getType()
	{
		return $this->type;
	}



	/**
	 * @param  mixed
	 * @param  bool
	 * @return mixed
	 */
	function checkType($value, $need = TRUE)
	{
		if ($value === NULL) {
			if (!$this->nullable) {
				$entity = $this->reflection->name;
				throw new YetORM\Exception\InvalidArgumentException("Property '$entity::\${$this->name}' cannot be NULL.");
			}

		} elseif (is_object($value)) {
			$class = Aliaser::getClass($this->type, $this->reflection);
			if (!($value instanceof $class)) {
				throw new YetORM\Exception\InvalidArgumentException("Instance of '{$class}' expected, '"
					. get_class($value) . "' given.");
			}

		} elseif ($need && ($type = gettype($value)) !== $this->type) {
			throw new YetORM\Exception\InvalidArgumentException("Invalid type - '{$this->type}' expected, '$type' given.");

		} else {
			return FALSE;
		}

		return TRUE;
	}



	/**
	 * @param  mixed
	 * @return mixed
	 */
	function setType($value)
	{
		if (!$this->checkType($value, FALSE) && @settype($value, $this->type) === FALSE) { // intentionally @
			throw new YetORM\Exception\InvalidArgumentException("Unable to set type '{$this->type}' from '"
				. gettype($value) . "'.");
		}

		return $value;
	}

}
