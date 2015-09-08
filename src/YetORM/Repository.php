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
use Nette\Reflection\AnnotationsParser;
use Nette\Database\Context as NdbContext;
use Nette\Database\Table\ActiveRow as NActiveRow;
use Nette\Database\Table\Selection as NSelection;


abstract class Repository extends Nette\Object
{

	/** @var NdbContext */
	protected $database;

	/** @var Transaction */
	private $transaction;

	/** @var string */
	protected $table = NULL;

	/** @var string */
	protected $entity = NULL;


	/** @param  NdbContext $database */
	public function __construct(NdbContext $database)
	{
		$this->database = $database;
		$this->transaction = new Transaction($database->getConnection());
	}


	/**
	 * @param  NActiveRow|Record $row
	 * @return Entity
	 */
	public function createEntity($row = NULL)
	{
		$class = $this->getEntityClass();
		return new $class($row);
	}


	/**
	 * @param  mixed $id
	 * @return Entity|NULL
	 */
	public function getByID($id)
	{
		$selection = $this->getTable()->wherePrimary($id);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param  array $criteria
	 * @return Entity|NULL
	 */
	public function getBy(array $criteria)
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createEntityFromSelection($selection);
	}


	/**
	 * @param  array $criteria
	 * @return EntityCollection
	 */
	public function findBy(array $criteria)
	{
		$selection = $this->getTable()->where($criteria);
		return $this->createCollection($selection);
	}


	/** @return EntityCollection */
	public function findAll()
	{
		return $this->findBy(array());
	}


	/**
	 * @param  NSelection $selection
	 * @return Entity|NULL
	 */
	protected function createEntityFromSelection(NSelection $selection)
	{
		$row = $selection->fetch();
		return $row === FALSE ? NULL : $this->createEntity($row);
	}


	/**
	 * @param  NSelection $selection
	 * @param  string|callable $entity
	 * @param  string $refTable
	 * @param  string $refColumn
	 * @return EntityCollection
	 */
	protected function createCollection($selection, $entity = NULL, $refTable = NULL, $refColumn = NULL)
	{
		return new EntityCollection($selection, $entity === NULL ? $this->createEntity : $entity, $refTable, $refColumn);
	}


	/**
	 * @param  Entity $entity
	 * @return bool
	 */
	public function persist(Entity $entity)
	{
		$this->checkEntity($entity);

		$me = $this;
		return $this->transaction(function () use ($me, $entity) {

			$record = $entity->toRecord();
			if ($record->hasRow()) {
				return $record->update();
			}

			$inserted = $me->getTable()->insert($record->getModified());
			$record->setRow($inserted);
			return $inserted instanceof Nette\Database\IRow || $inserted > 0;

		});
	}


	/**
	 * @param  Entity $entity
	 * @return bool
	 */
	public function delete(Entity $entity)
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
	 * @return NSelection
	 */
	protected function getTable($table = NULL)
	{
		return $this->database->table($table === NULL ? $this->getTableName() : $table);
	}


	/** @return string */
	final protected function getTableName()
	{
		if ($this->table === NULL) {
			if (($annotation = static::getReflection()->getAnnotation('table')) === NULL) {
				throw new Exception\InvalidStateException("Table name not set.");
			}

			$this->table = $annotation;
		}

		return $this->table;
	}


	/** @return string */
	final protected function getEntityClass()
	{
		if ($this->entity === NULL) {
			$ref = static::getReflection();
			if (($annotation = $ref->getAnnotation('entity')) === NULL) {
				throw new Exception\InvalidStateException('Entity class not set.');
			}

			$this->entity = AnnotationsParser::expandClassName($annotation, $ref);
		}

		return $this->entity;
	}


	/** @return void */
	private function checkEntity(Entity $entity)
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
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (strncmp($name, 'getBy', 5) === 0 && strlen($name) > 5) {
			$selection = $this->getTable()->limit(1);
			$properties = explode('And', substr($name, 5));

			if (count($properties) !== count($args)) {
				throw new Exception\InvalidArgumentException;
			}

			$ref = Reflection\EntityType::from($class = $this->getEntityClass());
			foreach ($properties as $key => $property) {
				$property = lcfirst($property);
				$prop = $ref->getEntityProperty($property);

				if ($prop === NULL) {
					throw new Exception\InvalidArgumentException("Property '\$$property' not found in entity '$class'.");
				}

				$selection->where($prop->getColumn(), $args[$key]);
			}

			return $this->createEntityFromSelection($selection);

		} elseif (strncmp($name, 'findBy', 6) === 0 && strlen($name) > 6) {
			$selection = $this->getTable();
			$properties = explode('And', substr($name, 6));

			if (count($properties) !== count($args)) {
				throw new Exception\InvalidArgumentException;
			}

			$criteria = array();
			$ref = Reflection\EntityType::from($class = $this->getEntityClass());

			foreach ($properties as $key => $property) {
				$property = lcfirst($property);
				$prop = $ref->getEntityProperty($property);

				if ($prop === NULL) {
					throw new Exception\InvalidArgumentException("Property '\$$property' not found in entity '$class'.");
				}

				$criteria[$prop->getColumn()] = $args[$key];
			}

			return $this->findBy($criteria);
		}

		return parent::__call($name, $args);
	}


	// === transaction helpers ====================================================

	/**
	 * @param  \Closure $callback
	 * @return mixed
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
	 * @return void
	 */
	protected function handleException(\Exception $e)
	{}


	/** @return void */
	final protected function begin()
	{
		$this->transaction->begin();
	}


	/** @return void */
	final protected function commit()
	{
		$this->transaction->commit();
	}


	/** @return void */
	final protected function rollback()
	{
		$this->transaction->rollback();
	}

}
