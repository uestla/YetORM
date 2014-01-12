<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2014 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use Nette\Database\Table\ActiveRow as NActiveRow;


abstract class Entity
{

	/** @var Row */
	protected $row;

	/** @var array */
	private static $reflections = array();



	/** @param  NActiveRow $row */
	function __construct(NActiveRow $row = NULL)
	{
		$this->row = new Row($row);
	}



	/**
	 * @param  string|callable $entity
	 * @param  string $relTable
	 * @param  string $entityTable
	 * @param  string $throughColumn
	 * @return EntityCollection
	 */
	protected function getMany($entity, $relTable, $entityTable, $throughColumn = NULL)
	{
		return new EntityCollection($this->row->related($relTable), $entity, $entityTable, $throughColumn);
	}



	/** @return Row */
	final function toRow()
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

		foreach ($ref->getEntityProperties() as $name => $property) {
			if ($property instanceof Reflection\MethodProperty) {
				$value = $this->{'get' . $name}();

			} else {
				$value = $this->$name;
			}

			if (!($value instanceof EntityCollection || $value instanceof Entity)) {
				$values[$name] = $value;
			}
		}

		return $values;
	}



	/**
	 * @param  string $name
	 * @param  array $args
	 * @return mixed
	 */
	final function __call($name, $args)
	{
		$class = get_class($this);
		throw new Exception\MemberAccessException("Call to undefined method $class::$name().");
	}



	/**
	 * @param  string $name
	 * @return mixed
	 */
	final function & __get($name)
	{
		if (($prop = static::getReflection()->getEntityProperty($name))) {
			$value = $prop->setType($this->row->{$prop->column});
			return $value;
		}

		$class = get_class($this);
		throw new Exception\MemberAccessException("Cannot read an undeclared property $class::\$$name.");
	}



	/**
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	final function __set($name, $value)
	{
		$prop = static::getReflection()->getEntityProperty($name);
		if ($prop instanceof Reflection\AnnotationProperty && !$prop->readonly) {
			$prop->checkType($value);
			$this->row->{$prop->column} = $value;
			return ;
		}

		$class = get_class($this);
		throw new Exception\MemberAccessException("Cannot write to an undeclared property $class::\$$name.");
	}



	/**
	 * @param  string $name
	 * @return bool
	 */
	final function __isset($name)
	{
		return static::getReflection()->hasEntityProperty($name);
	}



	/**
	 * @param  string $name
	 * @return void
	 * @throws E\NotSupportedException
	 */
	final function __unset($name)
	{
		throw new Exception\NotSupportedException;
	}



	/** @return Reflection\EntityType */
	final static function getReflection()
	{
		$class = get_called_class();
		if (!isset(self::$reflections[$class])) {
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}

}
