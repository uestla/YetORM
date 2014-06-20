<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013, 2014 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use Aliaser\Container as Aliaser;
use Nette\Database\Context as NdbContext;
use Nette\Database\Table\Selection as NSelection;


abstract class Repository extends Nette\Object
{

	/** @var array */
	private static $transactionCounter = array();

	/** @var NdbContext */
	protected $database;

	/** @var string */
	protected $table = NULL;

	/** @var string */
	protected $entity = NULL;


	/** @param  NdbContext $database */
	function __construct(NdbContext $database)
	{
		$this->database = $database;

		if (!isset(self::$transactionCounter[$dsn = $database->getConnection()->getDsn()])) {
			self::$transactionCounter[$dsn] = 0;
		}
	}


	/**
	 * @param  NSelection $selection
	 * @param  string $entity
	 * @param  string $refTable
	 * @param  string $refColumn
	 * @return EntityCollection
	 */
	protected function createCollection($selection, $entity = NULL, $refTable = NULL, $refColumn = NULL)
	{
		return new EntityCollection($selection, $entity === NULL ? $this->getEntityClass() : $entity, $refTable, $refColumn);
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

			$this->entity = Aliaser::getClass($annotation, $ref);
		}

		return $this->entity;
	}


	/**
	 * @param  Entity $entity
	 * @return bool
	 */
	function persist(Entity $entity)
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
			return $inserted instanceof \Nette\Database\IRow || $inserted > 0;

		});
	}


	/**
	 * @param  Entity $entity
	 * @return bool
	 */
	function delete(Entity $entity)
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


	/** @return void */
	private function checkEntity(Entity $entity)
	{
		$class = $this->getEntityClass();

		if (!($entity instanceof $class)) {
			throw new Exception\InvalidArgumentException("Instance of '$class' expected, '"
				. get_class($entity) . "' given.");
		}
	}


	// === TRANSACTIONS ====================================================

	/** @return void */
	final protected function begin()
	{
		if (self::$transactionCounter[$this->database->getConnection()->getDsn()]++ === 0) {
			$this->database->beginTransaction();
		}
	}


	/** @return void */
	final protected function commit()
	{
		if (self::$transactionCounter[$dsn = $this->database->getConnection()->getDsn()] === 0) {
			throw new Exception\InvalidStateException('No transaction started.');
		}

		if (--self::$transactionCounter[$dsn] === 0) {
			$this->database->commit();
		}
	}


	/** @return void */
	final protected function rollback()
	{
		$this->database->rollBack();
		self::$transactionCounter[$this->database->getConnection()->getDsn()] = 0;
	}


	/**
	 * @param  \Closure $callback
	 * @return mixed
	 */
	final protected function transaction(\Closure $callback)
	{
		try {
			$this->begin();
				$return = $callback();
			$this->commit();

			return $return;

		} catch (\Exception $e) {
			$this->rollback();
			throw $e;
		}
	}

}
