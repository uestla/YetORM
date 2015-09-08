<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2015 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use Nette\Utils\Callback as NCallback;
use Nette\Database\Table\ActiveRow as NActiveRow;


abstract class Entity
{

	/** @var Record */
	protected $record;

	/** @var array */
	private static $reflections = array();


	/** @param  NActiveRow|Record $row */
	public function __construct($row = NULL)
	{
		$this->record = Record::create($row);
	}


	/** @return Record */
	final public function toRecord()
	{
		return $this->record;
	}


	/**
	 * @param  string $name
	 * @param  array $args
	 * @return void
	 */
	public function __call($name, $args)
	{
		// events support
		$ref = static::getReflection();
		if (preg_match('#^on[A-Z]#', $name) && $ref->hasProperty($name)) {
			$prop = $ref->getProperty($name);
			if ($prop->isPublic() && !$prop->isStatic() && (is_array($this->$name) || $this->$name instanceof \Traversable)) {
				foreach ($this->$name as $cb) {
					NCallback::invokeArgs($cb, $args);
				}

				return ;
			}
		}

		$class = get_class($this);
		throw new Exception\MemberAccessException("Call to undefined method $class::$name().");
	}


	/**
	 * @param  string $name
	 * @return mixed
	 */
	public function & __get($name)
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
	public function __set($name, $value)
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
	public function __isset($name)
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
	public function __unset($name)
	{
		throw new Exception\NotSupportedException;
	}


	/** @return Reflection\EntityType */
	public static function getReflection()
	{
		$class = get_called_class();
		if (!isset(self::$reflections[$class])) {
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}

}
