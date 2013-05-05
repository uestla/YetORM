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
	protected $entity;

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
	 * @param  string
	 * @param  bool
	 * @param  bool
	 */
	function __construct($entity, $name, $column, $type, $nullable, $readonly)
	{
		$this->entity = (string) $entity;
		$this->name = (string) $name;
		$this->column = (string) $column;
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
	 * @param  bool
	 * @return mixed
	 */
	function checkType($value, $need = TRUE)
	{
		if ($value === NULL) {
			if (!$this->nullable) {
				throw new Nette\InvalidArgumentException("Property '{$this->entity}::\${$this->name}' cannot be NULL.");
			}

		} elseif (is_object($value)) {
			if (!($value instanceof $this->type)) {
				throw new Nette\InvalidArgumentException("Instance of '{$this->type}' expected, '"
					. get_class($value) . "' given.");
			}

		} elseif (($type = gettype($value)) !== $this->type) {
			if ($need) {
				throw new Nette\InvalidArgumentException("Invalid type - '{$this->type}' expected, '$type' given.");
			}

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
			throw new Nette\InvalidArgumentException("Unable to set type '{$this->type}' from '"
				. gettype($value) . "'.");
		}

		return $value;
	}

}
