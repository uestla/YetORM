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


class EntityType extends NClassType
{

	/** @var EntityProperty[] */
	private $properties = NULL;

	/** @var AnnotationProperty[] */
	private static $annProps = array();



	/** @return EntityProperty[] */
	function getEntityProperties()
	{
		$this->loadEntityProperties();
		return $this->properties;
	}



	/**
	 * @param  string $name
	 * @return EntityProperty|NULL
	 */
	function getEntityProperty($name, $default = NULL)
	{
		return $this->hasEntityProperty($name) ? $this->properties[$name] : $default;
	}



	/**
	 * @param  string $name
	 * @return bool
	 */
	function hasEntityProperty($name)
	{
		$this->loadEntityProperties();
		return isset($this->properties[$name]);
	}



	/** @return void */
	private function loadEntityProperties()
	{
		if ($this->properties === NULL) {
			$this->properties = array();
			$this->loadMethodProperties();

			foreach ($this->getClassTree() as $class) {
				self::loadAnnotationProperties($class);

				foreach (self::$annProps[$class] as $name => $property) {
					if (!isset($this->properties[$name])) {
						$this->properties[$name] = $property;
					}
				}
			}
		}
	}



	/** @return void */
	private function loadMethodProperties()
	{
		foreach ($this->getMethods(NMethod::IS_PUBLIC) as $method) {
			if ($method->declaringClass->name !== 'YetORM\\Entity'
					&& strlen($method->name) > 3 && substr($method->name, 0, 3) === 'get'
					&& !$method->hasAnnotation('internal')) {

				$name = lcfirst(substr($method->name, 3));
				$this->properties[$name] = new MethodProperty(
					$this,
					$name,
					!$this->hasMethod('set' . ucfirst($name))
				);
			}
		}
	}



	/** @return array */
	private function getClassTree()
	{
		$tree = array();
		$current = $this->name;

		do {
			$tree[] = $current;
			$current = get_parent_class($current);

		} while ($current !== FALSE && $current !== 'YetORM\\Entity');

		return array_reverse($tree);
	}



	/**
	 * @param  string $class
	 * @return void
	 */
	private static function loadAnnotationProperties($class)
	{
		if (!isset(self::$annProps[$class])) {
			self::$annProps[$class] = array();
			$ref = $class::getReflection();

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
										throw new YetORM\Exception\InvalidStateException('Invalid property type (double NULL).');
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
								$ref,
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
