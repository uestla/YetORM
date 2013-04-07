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

use Nette\Utils\Strings as NStrings;
use Nette\Reflection\Method as NMethod;
use Nette\Reflection\ClassType as NClassType;


/**
 * @property-read EntityProperty[] $properties
 * @property-read NMethod[] $getters
 */
class EntityType extends NClassType
{

	/** @var EntityProperty[] */
	private $properties = NULL;

	/** @var NMethod[] */
	private $getters = NULL;



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
	function getProperty($name)
	{
		$this->loadProperties();
		return isset($this->properties[$name]) ? $this->properties[$name] : NULL;
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
			foreach ($this->getAnnotations() as $ann => $values) {
				if ($ann === 'property' || $ann === 'property-read') {
					foreach ($values as $tmp) {
						$split = NStrings::split($tmp, '#\s+#');

						if (count($split) >= 2) {
							list($type, $var) = $split;

							// unify type name
							if ($type === 'bool') {
								$type = 'boolean';

							} elseif ($type === 'int') {
								$type = 'integer';
							}

							// parse column name
							$column = NULL;
							if (isset($split[2]) && $split[2] === '->' && isset($split[3])) {
								$column = $split[3];
							}

							$name = substr($var, 1);
							$this->properties[$name] = new EntityProperty($name, $column, $type, $ann === 'property-read');
						}
					}
				}
			}
		}
	}



	/** @return NMethod[] */
	function getGetters()
	{
		$this->loadGetters();
		return $this->getters;
	}



	/** @return void */
	private function loadGetters()
	{
		if ($this->getters === NULL) {
			$this->getters = array();
			foreach ($this->getMethods(NMethod::IS_PUBLIC) as $method) {
				if ($method->declaringClass->name === $this->name
						&& substr($method->name, 0, 3) === 'get' && strlen($method->name) > 3) {

					$this->getters[] = $method;
				}
			}
		}
	}

}
