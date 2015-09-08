<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2015 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Reflection;

use YetORM;
use Nette\Utils\Strings as NStrings;
use Nette\Reflection\Method as NMethod;
use Nette\Reflection\AnnotationsParser;
use Nette\Reflection\ClassType as NClassType;


class EntityType extends NClassType
{

	/** @var EntityProperty[] */
	private $properties = NULL;

	/** @var AnnotationProperty[] */
	private static $annProps = array();


	/** @return EntityProperty[] */
	public function getEntityProperties()
	{
		$this->loadEntityProperties();
		return $this->properties;
	}


	/**
	 * @param  string $name
	 * @return EntityProperty|NULL
	 */
	public function getEntityProperty($name, $default = NULL)
	{
		return $this->hasEntityProperty($name) ? $this->properties[$name] : $default;
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	public function hasEntityProperty($name)
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
				$type = $method->getAnnotation('return');

				if ($type !== NULL && !EntityProperty::isNativeType($type)) {
					$type = AnnotationsParser::expandClassName($type, $this);
				}

				$description = trim(preg_replace('#^\s*@.*#m', '', preg_replace('#^\s*\* ?#m', '', trim($method->getDocComment(), "/* \r\n\t"))));

				$this->properties[$name] = new MethodProperty(
					$this,
					$name,
					!$this->hasMethod('set' . ucfirst($name)),
					$type,
					strlen($description) ? $description : NULL
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
						$matches = NStrings::match($tmp, '#^[ \t]*(?P<type>\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*(?:\|\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(?:\\\\[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)*)?)[ \t]+(?P<property>\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(?:[ \t]+->[ \t]+(?P<column>[a-zA-Z0-9_-]+))?[ \t]*(?P<description>.*)\z#');

						if ($matches == NULL) {
							throw new YetORM\Exception\InvalidStateException('Invalid property definition - "@' . $ann . ' ' . $tmp . '" does not match "@property[-read] <type> $<property> [-> <column>][ <description>]" pattern.');
						}

						$nullable = FALSE;
						$type = $matches['type'];

						$types = explode('|', $type, 2);
						if (count($types) === 2) {
							if (strcasecmp($types[0], 'NULL') === 0) {
								$nullable = TRUE;
								$type = $types[1];
							}

							if (strcasecmp($types[1], 'NULL') === 0) {
								if ($nullable) {
									throw new YetORM\Exception\InvalidStateException('Invalid property type (double NULL).');
								}

								$nullable = TRUE;
								$type = $types[0];
							}

							if (!$nullable) {
								throw new YetORM\Exception\InvalidStateException('Invalid property type (multiple non-NULL types detected).');
							}
						}

						if ($type === 'bool') {
							$type = 'boolean';

						} elseif ($type === 'int') {
							$type = 'integer';
						}

						if (!EntityProperty::isNativeType($type)) {
							$type = AnnotationsParser::expandClassName($type, $ref);
						}

						$readonly = $ann === 'property-read';
						$name = substr($matches['property'], 1);
						$column = strlen($matches['column']) ? $matches['column'] : $name;
						$description = strlen($matches['description']) ? $matches['description'] : NULL;

						self::$annProps[$class][$name] = new AnnotationProperty(
								$ref,
								$name,
								$readonly,
								$type,
								$column,
								$nullable,
								$description
						);
					}
				}
			}
		}
	}

}
