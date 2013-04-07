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


abstract class Entity extends Nette\Object
{

	/** @var NActiveRow */
	protected $row;

	/** @var array */
	private static $reflections = array();



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
		foreach ($ref->getProperties() as $name => $prop) {
			if (!isset($values[$name])) {
				$value = $this->row->{$prop->column};
				if (settype($value, $prop->type) === FALSE) {
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
			if ($prop = static::getReflection()->getProperty($name)) {
				$value = $this->row->{$prop->column};
				if (settype($value, $prop->type) === FALSE) {
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
			$prop = static::getReflection()->getProperty($name);
			if ($prop && !$prop->readonly) {
				$type = gettype($value);
				if ($type !== $prop->type) {
					throw new Nette\InvalidArgumentException("Invalid type - '{$prop->type}' expected, '$type' given.");
				}

				$this->row->{$prop->column} = $value;
				return ;
			}

			throw $e;
		}
	}



	/**
	 * @param  string
	 * @return bool
	 */
	function __isset($name)
	{
		return parent::__isset($name) || static::getReflection()->hasProperty($name);
	}



	/**
	 * @param  string
	 * @return void
	 * @throws Nette\NotSupportedException
	 */
	function __unset($name)
	{
		throw new Nette\NotSupportedException;
	}



	/** @return Reflection\EntityType */
	static function getReflection()
	{
		$class = get_called_class();
		if (!isset(self::$reflections[$class])) {
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}

}
