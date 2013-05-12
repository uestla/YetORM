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

use YetORM;
use Nette\Utils\Strings as NStrings;
use Nette\Reflection\Method as NMethod;
use Nette\Reflection\ClassType as NClassType;


/** @property-read EntityProperty[] $properties */
class EntityType extends NClassType
{

	/** @var EntityProperty[] */
	private $properties = NULL;

	/** @var MethodProperty[] */
	private static $methodProps = array();

	/** @var AnnotationProperty[] */
	private static $annProps = array();



	/** @return EntityProperty[] */
	function getProperties($filter = NULL)
	{
		$this->loadProperties();
		return $this->properties;
	}



	/**
	 * @param  string
	 * @return EntityProperty|NULL
	 */
	function getProperty($name, $default = NULL)
	{
		return $this->hasProperty($name) ? $this->properties[$name] : $default;
	}



	/**
	 * @param  string
	 * @return bool
	 */
	function hasProperty($name)
	{
		$this->loadProperties();
		return isset($this->properties[$name]);
	}



	/** @return void */
	private function loadProperties()
	{
		if ($this->properties === NULL) {
			$this->properties = array();

			$tree = array();
			$current = $this->name;

			do {
				$tree[] = $current;
				$current = get_parent_class($current);

			} while ($current !== FALSE && $current !== 'YetORM\\Entity');


			foreach (array_reverse($tree) as $class) {
				self::loadMethodProperties($class);
				self::loadAnnotationProperties($class);

				foreach (self::$methodProps[$class] as $name => $property) {
					$this->properties[$name] = $property;
				}

				foreach (self::$annProps[$class] as $name => $property) {
					if (!isset($this->properties[$name])) {
						$this->properties[$name] = $property;
					}
				}
			}
		}
	}



	/**
	 * @param  string
	 * @return void
	 */
	private static function loadMethodProperties($class)
	{
		if (!isset(self::$methodProps[$class])) {
			$ref = NClassType::from($class);
			self::$methodProps[$class] = array();

			foreach ($ref->getMethods(NMethod::IS_PUBLIC) as $method) {
				if ($method->declaringClass->name !== 'YetORM\\Entity'
						&& strlen($method->name) > 3 && substr($method->name, 0, 3) === 'get'
						&& !$method->hasAnnotation('internal')) {

					$name = lcfirst(substr($method->name, 3));
					self::$methodProps[$class][$name] = new MethodProperty(
						$ref->name,
						$name,
						!$ref->hasMethod('set' . $name)
					);
				}
			}
		}
	}



	/**
	 * @param  string
	 * @return void
	 */
	private static function loadAnnotationProperties($class)
	{
		if (!isset(self::$annProps[$class])) {
			$ref = NClassType::from($class);
			self::$annProps[$class] = array();

			foreach ($ref->getAnnotations() as $ann => $values) {
				if ($ann === 'property' || $ann === 'property-read') {
					foreach ($values as $tmp) {
						$split = NStrings::split($tmp, '#\s#');

						if (count($split) >= 2) {
							list($type, $var) = $split;

							// support NULL type
							$nullable = FALSE;
							$types = explode('|', $type, 2);
							if (count($types) === 2) {
								if (strcasecmp($types[0], 'null') === 0) {
									$type = $types[1];
									$nullable = TRUE;
								}

								if (strcasecmp($types[1], 'null') === 0) {
									if ($nullable) {
										throw new YetORM\E\InvalidStateException('Invalid property type (double NULL).');
									}

									$type = $types[0];
									$nullable = TRUE;
								}
							}

							// unify type name
							if ($type === 'bool') {
								$type = 'boolean';

							} elseif ($type === 'int') {
								$type = 'integer';
							}

							$name = substr($var, 1);
							$readonly = $ann === 'property-read';

							// parse column name
							$column = $name;
							if (isset($split[2]) && $split[2] === '->' && isset($split[3])) {
								$column = $split[3];
							}

							self::$annProps[$class][$name] = new AnnotationProperty(
								$ref->name,
								$name,
								$readonly,
								$column,
								$type,
								$nullable
							);
						}
					}
				}
			}
		}
	}

}
