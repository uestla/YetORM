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
use Nette\Reflection\ClassType as NClassType;


abstract class Entity extends Nette\Object
{

	/** @var NActiveRow */
	protected $row;

	/** @var array */
	protected static $reflections = array();



	/** @param  NActiveRow */
	function __construct(NActiveRow $row)
	{
		$this->row = $row;
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



	/** @return NActiveRow */
	final function toActiveRow()
	{
		return $this->row;
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
				if ($prefix === 'set') { // set<Property>
					$this->__set(lcfirst(substr($name, 3)), reset($args));
					return $this;

				} elseif ($prefix === 'get') { // get<Property>
					return $this->__get(lcfirst(substr($name, 3)));
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
			if ($this->hasProperty($name, FALSE, $property, $type)) {
				$value = $this->row->$property;
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
			if ($this->hasProperty($name, TRUE, $property, $expected)) {
				$expected = $expected === 'bool' ? 'boolean' : $expected; // accept 'bool' as well as 'boolean'
				$actual = gettype($value);

				if ($actual !== $expected) {
					throw new Nette\InvalidArgumentException("Invalid type - '$expected' expected, '$actual' given.");
				}

				$this->row->$property = $value;
				return ;
			}

			throw $e;
		}
	}



	/**
	 * @return NClassType
	 */
	public static function getReflection()
	{
		$class = get_called_class();
		if (!array_key_exists($class, static::$reflections)) {
			static::$reflections[$class] = parent::getReflection();
		}
		return static::$reflections[$class];
	}



	/**
	 * @param  string
	 * @param  bool
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	private function hasProperty($name, $writeAccess, & $prop, & $type)
	{
		$anns = static::getReflection()->annotations;
		$prop = strtolower(NStrings::replace($name, '#(.)(?=[A-Z])#', '$1_')); // propName => prop_name

		foreach ($anns as $key => $values) {
			if ($key === 'property' || (!$writeAccess && $key === 'property-read')) {
				foreach ($values as $tmp) {
					$split = NStrings::split($tmp, '#\s+#');
					if (count($split) >= 2) {
						list($type, $var) = $split;
						if ($var === '$' . $prop) {
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
