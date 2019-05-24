<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Reflection;

use YetORM;
use Nette\Utils\Strings as NStrings;

class EntityType extends \ReflectionClass
{

	/** @var EntityProperty[]|NULL */
	private $properties = NULL;

	/** @var array <class> => AnnotationProperty[] */
	private static $annProps = [];


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
			$this->properties = [];
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
		foreach ($this->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->getDeclaringClass()->getName() !== 'YetORM\\Entity'
					&& strlen($method->getName()) > 3 && substr($method->getName(), 0, 3) === 'get'
					&& !$method->hasAnnotation('internal')) {

				$name = lcfirst(substr($method->getName(), 3));

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
		$tree = [];
		$current = $this->getName();

		do {
			$tree[] = $current;
			$current = get_parent_class($current);

		} while ($current !== FALSE && $current !== YetORM\Entity::class);

		return array_reverse($tree);
	}


	private static function parseBlock($doc) {

	    $re = '/(?m)@(\S+) (\S+) (\S+)(.+)*$/';

	    preg_match_all($re, $doc, $matches, PREG_SET_ORDER, 0);

	    $properitiesList = [];

	    foreach($matches as $property)
	    {
		$properitiesList[$property[3]] = [
		    ''
		];
		dump($property);
	    }

	    die;


        foreach(preg_split("/(\r?\n)/", $doc) as $line)
	{
    $re = '/(?m)@(\S+) (\S+) (\S+)(.+)*$/';
    preg_match_all($re, $line, $matches, PREG_SET_ORDER, 0);


	    dump($matches);
	    die;

	    if(!empty($matches))
	    {
		dump($matches);
die;
	    }


	    //list($property, $type, $name, $description) = array_pad(explode(' ', $line), 4, null);
	    continue;
		if(false);
                $info = $matches[1];

                // remove wrapping whitespace
                $info = trim($info);

                // remove leading asterisk
                $info = preg_replace('/^(\*\s+?)/', '', $info);

                // if it doesn't start with an "@" symbol
                // then add to the description
                if( $info[0] !== "@" ) {

                    continue;
                }else {
                    // get the name of the param
                    preg_match('/@(\w+)/', $info, $matches);
                    $param_name = $matches[1];

                    // remove the param from the string
                    $value = str_replace("@$param_name ", '', $info);

                    // if the param hasn't been added yet, create a key for it
                    if( !isset($array[$param_name]) ) {
                        $array[$param_name] = array();
                    }

                    // push the param value into place
                    $array[$param_name][] = $value;

                    continue;
                }
            }


	//return $array;
    }

	/**
	 * @param  string $class
	 * @return void
	 */
	private static function loadAnnotationProperties($class)
	{
		if (!isset(self::$annProps[$class])) {
			self::$annProps[$class] = [];
			preg_match_all('/(?m)@(\S+) (\S+) (\S+)(.+)*$/', ($class::getReflection())->getDocComment(), $matches, PREG_SET_ORDER, 0);

			/**
			 * 0 - @property-read int $id desc
			 * 1 - property-read
			 * 2 - int
			 * 3 - $id
			 * 4 - desc
			 */
			foreach ($matches as $match) {

				if ($match[1] === 'property' || $match[1] === 'property-read') {

					if (NStrings::startsWith('$', $match[3])) {
						throw new YetORM\Exception\InvalidPropertyDefinitionException('Missing "$" in property name in "' . trim($match[0]) . '"');
					}

					$nullable = FALSE;
					$type = $match[2];

					$types = explode('|', $type, 2);
					if (count($types) === 2) {
						if (strcasecmp($types[0], 'NULL') === 0) {
							$nullable = TRUE;
							$type = $types[1];
						}

						if (strcasecmp($types[1], 'NULL') === 0) {
							if ($nullable) {
								throw new YetORM\Exception\InvalidPropertyDefinitionException('Only one NULL is allowed, "' . $matches['type'] . '" given.');
							}

							$nullable = TRUE;
							$type = $types[0];
						}

						if (!$nullable) {
							throw new YetORM\Exception\InvalidPropertyDefinitionException('Multiple non-NULL types detected.');
						}
					}

					if ($type === 'boolean') {
						$type = 'bool';

					} elseif ($type === 'integer') {
						$type = 'int';
					}

					$readonly = $match[1] === 'property-read';
					$name = substr($match[3], 1);
					$column = substr($match[3], 1); /** @todo Column setting & meaning? */
					$description = NStrings::trim($match[4]);

					self::$annProps[$class][$name] = new AnnotationProperty(
							$class::getReflection(),
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
