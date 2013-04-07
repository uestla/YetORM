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


/**
 * @property-read string $type
 * @property-read bool $readonly
 * @property-read string $column
 */
class EntityProperty extends Nette\Object
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $column;

	/** @var string */
	protected $type;

	/** @var bool */
	protected $nullable;

	/** @var bool */
	protected $readonly;



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 * @param  bool
	 */
	function __construct($name, $column, $type, $nullable, $readonly)
	{
		$this->name = (string) $name;
		$this->column = $column === NULL ? $this->name : (string) $column;
		$this->type = (string) $type;
		$this->nullable = (bool) $nullable;
		$this->readonly = (bool) $readonly;
	}



	/** @return string */
	function getType()
	{
		return $this->type;
	}



	/** @return bool */
	function isReadonly()
	{
		return $this->readonly;
	}



	/** @return string */
	function getColumn()
	{
		return $this->column;
	}



	/**
	 * @param  mixed
	 * @return mixed
	 */
	function fixType($value)
	{
		$type = gettype($value);
		if ($type === 'NULL') {
			if (!$this->nullable) {
				throw new Nette\InvalidArgumentException("Property '{$this->name}' cannot be NULL.");
			}

		} elseif ($type === 'object') {
			if (!($value instanceof $this->type)) {
				throw new Nette\InvalidArgumentException("Instance of '{$this->type}' expected, '$type' given.");
			}

		} elseif ($type !== $this->type) {
			throw new Nette\InvalidArgumentException("Invalid type - '{$this->type}' expected, '$type' given.");
		}

		return $value;
	}



	/**
	 * @param  mixed
	 * @return mixed
	 */
	function setType($value)
	{
		$type = gettype($value);
		if ($type === 'NULL') {
			if (!$this->nullable) {
				throw new Nette\InvalidArgumentException("Property '{$this->name}' cannot be NULL.");
			}

		} elseif ($type === 'object') {
			if (!($value instanceof $this->type)) {
				throw new Nette\InvalidArgumentException("Invalid instance - '{$this->type}' expected, '$type' gotten.");
			}

		} elseif (@settype($value, $this->type) === FALSE) { // intentionally @
			throw new Nette\InvalidArgumentException("Unable to set type '{$this->type}' from '$type'.");
		}

		return $value;
	}

}
