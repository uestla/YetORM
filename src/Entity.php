<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use Nette\Database\Table\ActiveRow as NActiveRow;


class Entity extends Nette\Object
{

	/** @var NActiveRow */
	protected $row;



	/** @param  NActiveRow */
	function __construct(NActiveRow $row)
	{
		$this->row = $row;
	}



	/** @return NActiveRow */
	function getActiveRow()
	{
		return $this->row;
	}



	/**
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return EntityCollection
	 */
	protected function getMany($relTable, $entityTable, $throughColumn, $entity)
	{
		return new EntityCollection($this->row->related($relTable), $entity, $entityTable, $throughColumn);
	}



	/**
	 * Looks for all public get* methods and returns
	 * associative array with corresponding values
	 *
	 * @return array
	 */
	function toArray()
	{
		$values = array();
		$class = $this->reflection->name;
		foreach ($this->reflection->methods as $method) {
			if ($method->declaringClass->name === $class && $method->public
					&& substr($method->name, 0, 3) === 'get' && strlen($method->name) > 3) {

				$value = $method->invoke($this);
				if (!($value instanceof EntityCollection) && !($value instanceof Entity)) {
					$values[lcfirst(substr($method->name, 3))] = $value;
				}

			}
		}

		return $values;
	}

}
