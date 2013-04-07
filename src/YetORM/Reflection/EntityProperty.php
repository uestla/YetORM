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
	protected $readonly;



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  bool
	 */
	function __construct($name, $column, $type, $readonly)
	{
		$this->name = (string) $name;
		$this->column = $column === NULL ? $this->name : (string) $column;
		$this->type = (string) $type;
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

}
