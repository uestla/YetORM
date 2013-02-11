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
	 * Looks for all public get* methods and @property[-read] annotations
	 * and returns associative array with corresponding values
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
				if (!($value instanceof EntityCollection || $value instanceof Entity)) {
					$values[lcfirst(substr($method->name, 3))] = $value;
				}

			}
		}

		// @property and @property-read annotations
		foreach ($this->getProperties() as $name => $type) {
			if (!isset($values[$name])) {
				$value = $this->row->$name;
				if (settype($value, $type) === FALSE) {
					throw new Nette\InvalidArgumentException("Invalid property type.");
				}

				$values[$name] = $value;
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
		try {
			return parent::__call($name, $args);

		} catch (Nette\MemberAccessException $e) {
			if (strlen($name) > 3) {
				$prefix = substr($name, 0, 3);
				$prop = strtolower(NStrings::replace(substr($name, 3), '#(.)(?=[A-Z])#', '$1_')); // propName => prop_name

				if ($prefix === 'set') { // set<Property>
					$this->__set($prop, reset($args));
					return $this;

				} elseif ($prefix === 'get') { // get<Property>
					return $this->__get($prop);
				}
			}

			throw $e;
		}
	}



	/**
	 * @param  string
	 * @return mixed
	 */
	function & __get($name)
	{
		try {
			return parent::__get($name);

		} catch (Nette\MemberAccessException $e) {
			if ($this->hasProperty($name, FALSE, $type)) {
				$value = $this->row->$name;
				if (settype($value, $type) === FALSE) {
					throw new Nette\InvalidArgumentException("Invalid property type.");
				}

				return $value;
			}

			throw $e;
		}
	}



	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	function __set($name, $value)
	{
		try {
			return parent::__set($name, $value);

		} catch (Nette\MemberAccessException $e) {
			if ($this->hasProperty($name, TRUE, $type)) {
				if (settype($value, $type) === FALSE) {
					throw new Nette\InvalidArgumentException("Invalid property type.");
				}

				$this->row->$name = $value;
				return ;
			}

			throw $e;
		}
	}



	/**
	 * @param  string
	 * @param  bool
	 * @param  string
	 * @return bool
	 */
	private function hasProperty($name, $writeAccess, & $type)
	{
		$anns = static::getReflection()->annotations;
		foreach ($anns as $key => $values) {
			if ($key === 'property' || (!$writeAccess && $key === 'property-read')) {
				foreach ($values as $tmp) {
					$split = NStrings::split($tmp, '#\s+#');
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



	/** @return array */
	private function getProperties()
	{
		$props = array();
		foreach (static::getReflection()->annotations as $name => $values) {
			if ($name === 'property' || $name === 'property-read') {
				foreach ($values as $tmp) {
					$split = NStrings::split($tmp, '#\s+#');
					if (count($split) >= 2) {
						list($type, $var) = $split;
						$props[substr($var, 1)] = $type;
					}
				}
			}
		}

		return $props;
	}

}
