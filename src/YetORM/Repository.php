<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use YetORM\Reflection\EntityType;
use Nette\Database\IRow as NIRow;
use YetORM\Reflection\AnnotationProperty;
use Nette\Database\Context as NdbContext;
use Nette\Utils\Reflection as NReflection;
use YetORM\Exception\InvalidArgumentException;
use Nette\Database\Table\ActiveRow as NActiveRow;
use Nette\Database\Table\Selection as NSelection;

abstract class Repository
{

	use Nette\SmartObject {
		__call as public netteCall;
	}

	/** @var NdbContext */
	protected $database;

	/** @var Transaction */
	private $transaction;

	/** @var string|NULL */
	protected $table = NULL;

	/** @var string|NULL */
	protected $entity = NULL;


	/** @param  NdbContext $database */
	public function __construct(NdbContext $database)
	{
		$this->database = $database;
		$this->transaction = new Transaction($database->getConnection());
	}


	/**
	 * @param  NActiveRow|Record $row
	 */
	public function createEntity($row = NULL): Entity
	{
		$class = $this->getEntityClass();
		return new $class($row);
	}


	/**
	 * @param  mixed $id
	 */
	public function getByID($id): ?Entity
	{
		$selection = $this->getTable()->wherePrimary($id);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param  array $criteria
	 */
	public function getBy(array $criteria): ?Entity
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param  array $criteria
	 */
	public function findBy(array $criteria): EntityCollection
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createCollection($selection);
	}


	public function findAll(): EntityCollection
	{
		return $this->findBy([]);
	}


	/**
	 * @param  NSelection $selection
	 */
	protected function createEntityFromSelection(NSelection $selection): ?Entity
	{
		$row = $selection->fetch();
		return $row === NULL ? NULL : $this->createEntity($row);
	}


	/**
	 * @param  NSelection $selection
	 * @param  string|callable $entity
	 * @param  string $refTable
	 * @param  string $refColumn
	 */
	protected function createCollection($selection, $entity = NULL, $refTable = NULL, $refColumn = NULL): EntityCollection
	{
		return new EntityCollection($selection, $entity === NULL ? [$this, 'createEntity'] : $entity, $refTable, $refColumn);
	}


	/**
	 * @param  Entity $entity
	 */
	public function persist(Entity $entity): bool
	{
		$this->checkEntity($entity);

		return $this->transaction(function () use ($entity) {

			$record = $entity->toRecord();
			if ($record->hasRow()) {
				return $record->update();
			}

			$inserted = $this->getTable()
					->insert($record->getModified());

			if (!$inserted instanceof NIRow) {
				throw new Exception\InvalidStateException('Insert did not return instance of ' . NIRow::class . '. '
						. 'Does table "' . $this->getTableName() . '" have primary key defined? If so, try cleaning cache.');
			}

			$record->setRow($inserted);
			return TRUE;

		});
	}


	/**
	 * @param  Entity $entity
	 */
	public function delete(Entity $entity): bool
	{
		$this->checkEntity($entity);
		$record = $entity->toRecord();

		if ($record->hasRow()) {
			return $this->transaction(function () use ($record) {
				return $record->getRow()->delete() > 0;
			});
		}

		return TRUE;
	}


	/**
	 * @param  string $table
	 */
	protected function getTable($table = NULL): NSelection
	{
		return $this->database->table($table === NULL ? $this->getTableName() : $table);
	}


	final protected function getTableName(): string
	{
		if ($this->table === NULL) {

			$ref = new \ReflectionClass($this);
			$this->table = EntityType::parseAnnotation($ref, 'table');

			if(!$this->table)  {
				throw new Exception\InvalidStateException('Table name not set. Use either annotation @table or class member ' . $ref->getName() . '::$table');
			}
		}

		return $this->table;
	}


	final protected function getEntityClass(): string
	{
		if ($this->entity === NULL) {
			$ref = new \ReflectionClass($this);
			$annotation = EntityType::parseAnnotation($ref, 'entity');

			if (!$annotation) {
				throw new Exception\InvalidStateException('Entity class not set. Use either annotation @entity or class member ' . $ref->getName() . '::$entity');
			}

			$this->entity = NReflection::expandClassName($annotation, $ref);
		}

		return $this->entity;
	}


	final protected function checkEntity(Entity $entity): void
	{
		$class = $this->getEntityClass();

		if (!$entity instanceof $class) {
			throw new Exception\InvalidArgumentException("Instance of '$class' expected, '"
				. get_class($entity) . "' given.");
		}
	}


	/**
	 * @param  string $name
	 * @param  array $args
	 */
	public function __call($name, $args)
	{
		if (strncmp($name, 'getBy', 5) === 0) {
			$selection = $this->getTable()->limit(1);
			$properties = explode('And', substr($name, 5));

			if (count($properties) !== count($args)) {
				throw new Exception\InvalidArgumentException('Wrong number of argument passed to ' . $name . ' method - ' . count($properties) . ' expected, ' . count($args) . ' given.');
			}

			$ref = Reflection\EntityType::from($class = $this->getEntityClass());
			foreach ($properties as $key => $property) {
				$property = lcfirst($property);
				$prop = $ref->getEntityProperty($property);

				if ($prop === NULL) {
					throw new Exception\InvalidArgumentException("Property '\$$property' not found in entity '$class'.");
				}

				if (!$prop instanceof AnnotationProperty) {
					throw new InvalidArgumentException("Cannot use " . static::getReflection()->getName() . "::$name() - missing @property definition of $class::\$$property.");
				}

				$selection->where($prop->getColumn(), $args[$key]);
			}

			return $this->createEntityFromSelection($selection);

		} elseif (strncmp($name, 'findBy', 6) === 0) {
			$properties = explode('And', substr($name, 6));

			if (count($properties) !== count($args)) {
				throw new Exception\InvalidArgumentException('Wrong number of argument passed to ' . $name . ' method - ' . count($properties) . ' expected, ' . count($args) . ' given.');
			}

			$criteria = [];
			$ref = Reflection\EntityType::from($class = $this->getEntityClass());

			foreach ($properties as $key => $property) {
				$property = lcfirst($property);
				$prop = $ref->getEntityProperty($property);

				if ($prop === NULL) {
					throw new Exception\InvalidArgumentException("Missing @property definition of $class::\$$property.");
				}

				if (!$prop instanceof AnnotationProperty) {
					$refs = Reflection\EntityType::from($this);
					throw new InvalidArgumentException("Cannot use " . $refs->getName() . "::$name() - missing @property definition of $class::\$$property.");
				}

				$criteria[$prop->getColumn()] = $args[$key];
			}

			return $this->findBy($criteria);
		}

		return $this->netteCall($name, $args);
	}


	// === TRANSACTION HELPERS ====================================================

	/**
	 * @param  \Closure $callback
	 */
	final protected function transaction(\Closure $callback)
	{
		try {
			return $this->transaction->transaction($callback);

		} catch (\Exception $e) {
			$this->handleException($e);
			throw $e;
		}
	}


	/**
	 * @param  \Exception $e
	 */
	protected function handleException(\Exception $e): void
	{}

}
