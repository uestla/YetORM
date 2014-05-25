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

	/** @var Record */
	protected $record;

	/** @var array */
	private static $reflections = array();


	/** @param  NActiveRow $row */
	function __construct(NActiveRow $row = NULL)
	{
		$this->record = new Record($row);
	}


	/** @return Record */
	final function toRecord()
	{
		return $this->record;
	}


	/**
	 * @param  string $name
	 * @param  array $args
	 * @return mixed
	 */
	function __call($name, $args)
	{
		$class = get_class($this);
		throw new Exception\MemberAccessException("Call to undefined method $class::$name().");
	}


	/**
	 * @param  string $name
	 * @return mixed
	 */
	function & __get($name)
	{
		$prop = static::getReflection()->getEntityProperty($name);
		if ($prop instanceof Reflection\AnnotationProperty) {
			$value = $prop->setType($this->record->{$prop->column});
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
	function __set($name, $value)
	{
		$prop = static::getReflection()->getEntityProperty($name);
		if ($prop instanceof Reflection\AnnotationProperty && !$prop->readonly) {
			$prop->checkType($value);
			$this->record->{$prop->column} = $value;
			return ;
		}

		$class = get_class($this);
		throw new Exception\MemberAccessException("Cannot write to an undeclared property $class::\$$name.");
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	function __isset($name)
	{
		$prop = static::getReflection()->getEntityProperty($name);
		if ($prop instanceof Reflection\AnnotationProperty) {
			return $this->__get($name) !== NULL;
		}

		return FALSE;
	}


	/**
	 * @param  string $name
	 * @return void
	 * @throws Exception\NotSupportedException
	 */
	function __unset($name)
	{
		throw new Exception\NotSupportedException;
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
