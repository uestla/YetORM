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
use Nette\Utils\Reflection as NReflection;

class EntityType extends \ReflectionClass
{

	/** @var EntityProperty[]|NULL */
	private $properties = NULL;

	/** @var array <class> => AnnotationProperty[] */
	private static $annProps = [];


	public function getEntityProperties(): array
	{
		$this->loadEntityProperties();
		return $this->properties;
	}


	/**
	 * @param  string $name
	 */
	public function getEntityProperty($name, $default = NULL): ?EntityProperty
	{
		return $this->hasEntityProperty($name) ? $this->properties[$name] : $default;
	}


	/**
	 * @param  string $name
	 */
	public function hasEntityProperty($name): bool
	{
		$this->loadEntityProperties();
		return isset($this->properties[$name]);
	}


	private function loadEntityProperties(): void
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


	private function loadMethodProperties(): void
	{
		foreach ($this->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->getDeclaringClass()->getName() !== 'YetORM\\Entity'
					&& strlen($method->getName()) > 3 && substr($method->getName(), 0, 3) === 'get'
					&& !NStrings::contains($method->getDocComment(), '@internal')) {

				$name = lcfirst(substr($method->getName(), 3));

				$type = $method->getReturnType();

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


	private function getClassTree(): array
	{
		$tree = [];
		$current = $this->getName();

		do {
			$tree[] = $current;
			$current = get_parent_class($current);

		} while ($current !== FALSE && $current !== YetORM\Entity::class);

		return array_reverse($tree);
	}

	/**
	 * Returns an annotation value.
	 */
	public static function parseAnnotation(\Reflector $ref, string $name): ?string
	{
		if (!NReflection::areCommentsAvailable()) {
			throw new Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
		}
		$re = '#[\s*]@' . preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s]\S*))?#';
		if ($ref->getDocComment() && $m = NStrings::match(trim($ref->getDocComment(), '/*'), $re)) {

			return $m[1] ?? '';
		}
		return null;
	}


	/**
	 * @param  string $class
	 */
	private static function loadAnnotationProperties($class): void
	{
		if (!isset(self::$annProps[$class])) {
			self::$annProps[$class] = [];
			$matches = NStrings::matchAll(($class::getReflection())->getDocComment(), '/@(\S+) (\S+) ((?:(?!\*\/)\S(?: -> \S)*)*) ?((?:(?!\*\/).)*)/', PREG_SET_ORDER);

			/**
			 * 0 - @property-read int $id desc
			 * 1 - property-read
			 * 2 - int
			 * 3 - $id
			 * 4 - desc
			 */
			foreach ($matches as $match) {

				if ($match[1] === 'property' || $match[1] === 'property-read') {
					if (!(isset($match[3]) && strlen($match[3])) || !(isset($match[2]) && strlen($match[2]))) {
						throw new YetORM\Exception\InvalidPropertyDefinitionException('"@property[-read] <type> $<property> [-> <column>][ <description>]" expected, "' . trim($match[0]) . '" given.');
					}

					if (!NStrings::startsWith($match[3], '$')) {
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
								throw new YetORM\Exception\InvalidPropertyDefinitionException('Only one NULL is allowed, "' . $match[2] . '" given.');
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

					if (!EntityProperty::isNativeType($type)) {
					    $type = NReflection::expandClassName($type, $class::getReflection());
					}

					$readonly = $match[1] === 'property-read';
					$name = trim(substr(NStrings::contains($match[3], '->') ? NStrings::before($match[3], '->') : $match[3], 1));
					$column = trim(substr(NStrings::contains($match[3], '->') ? NStrings::after($match[3], '->') : $match[3], 1));
					$description = !empty($match[4]) && NStrings::trim($match[4]) ? NStrings::trim($match[4]) : NULL;

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

	/**
	 * @param  string|object
	 */
	public static function from($class): self
	{
		return new static($class);
	}
}
