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
use Nette\Utils\Strings as NStrings;
use Nette\Database\Table\ActiveRow as NActiveRow;


abstract class Entity extends Nette\Object
{

	/** @var NActiveRow */
	protected $row;



	/** @param  NActiveRow */
	function __construct(NActiveRow $row)
	{
		$this->row = $row;
	}



	/** @return NActiveRow */
	final function getActiveRow()
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
	protected function getMany($entity, $relTable, $entityTable, $throughColumn = NULL)
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
		$ref = static::getReflection();
		$values = array();
		$class = $ref->name;

		// get<Property> methods
		foreach ($ref->methods as $method) {
			if ($method->declaringClass->name === $class && $method->public
					&& substr($method->name, 0, 3) === 'get' && strlen($method->name) > 3) {

				$value = $method->invoke($this);
				if (!($value instanceof EntityCollection) && !($value instanceof Entity)) {
					$values[lcfirst(substr($method->name, 3))] = $value;
				}

			}
		}

		// @property and @property-read annotations
		foreach ($ref->annotations as $n => $vals) {
			if ($n === 'property' || $n === 'property-read') {
				foreach ($vals as $val) {
					$split = NStrings::split($val, '#\s+#');
					if (count($split) >= 2) {
						list($type, $var) = $split;
						$name = substr($var, 1);
						$value = $this->row->$name;
						if (settype($value, $type) === FALSE) {
							throw new Nette\InvalidArgumentException("Invalid property type.");
						}

						if (!($value instanceof EntityCollection) && !($value instanceof Entity)) {
							$values[$name] = $value;
						}
					}
				}
			}
		}

		return $values;
	}



	/**
	 * @param  string
	 * @param  array
	 * @return mixed
	 */
	function __call($name, $args)
	{
		if (strlen($name) > 3) {
			$prefix = substr($name, 0, 3);
			if ($prefix === 'set') { // set<Property>
				return $this->__set(lcfirst(substr($name, 3)), reset($args));

			} elseif ($prefix === 'get') {
				return $this->__get(lcfirst(substr($name, 3)));
			}
		}

		return parent::__call($name, $args);
	}



	/**
	 * @param  string
	 * @return mixed
	 */
	function & __get($name)
	{
		if ($this->_getProperty($name, FALSE, $type)) {
			$value = $this->row->$name;
			if (settype($value, $type) === FALSE) {
				throw new Nette\InvalidArgumentException("Invalid property type.");
			}

			return $value;
		}

		return parent::__get($name);
	}



	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	function __set($name, $value)
	{
		if ($this->_getProperty($name, TRUE, $type)) {
			if (settype($value, $type) === FALSE) {
				throw new Nette\InvalidArgumentException("Invalid property type.");
			}

			$this->row->$name = $value;
			return $value;
		}

		return parent::__set($name, $value);
	}



	/**
	 * @param  string
	 * @param  bool
	 * @param  string
	 * @return bool
	 */
	final protected function _getProperty($name, $writeAccess, & $type)
	{
		$anns = static::getReflection()->annotations;
		foreach ($anns as $n => $vals) {
			if ($n === 'property' || (!$writeAccess && $n === 'property-read')) {
				foreach ($vals as $val) {
					$split = NStrings::split($val, '#\s+#');
					if (count($split) >= 2) {
						list($type, $var) = $split;
						if ($var === '$' . $name) {
							return TRUE;
						}
					}
				}
			}
		}

		return FALSE;
	}

}
