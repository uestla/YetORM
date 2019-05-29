<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use YetORM\Reflection\AnnotationProperty;
use Nette\Database\Table\ActiveRow as NActiveRow;


abstract class Entity
{

	/** @var Record */
	protected $record;

	/** @var array */
	private static $reflections = [];


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
					$cb($args);
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
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			$value = $prop->getValue($this);
			return $value;
		}

		throw new Exception\MemberAccessException("Cannot read an undeclared property {$ref->getName()}::\$$name.");
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			$prop->setValue($this, $value);
			return ;
		}

		throw new Exception\MemberAccessException("Cannot write to an undeclared property {$ref->getName()}::\$$name.");
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$prop = static::getReflection()->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			return $prop->getValue($this) !== NULL;
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
