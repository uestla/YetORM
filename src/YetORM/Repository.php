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
use Nette\Utils\Strings as NStrings;
use Nette\Database\Context as NdbContext;
use Nette\Database\Table\Selection as NSelection;


abstract class Repository extends Nette\Object
{

	/** @var array */
	private static $transactionCounter = array();

	/** @var NdbContext */
	protected $dbContext;

	/** @var string */
	protected $table = NULL;

	/** @var string */
	protected $entity = NULL;



	/** @param  NdbContext $context */
	function __construct(NdbContext $context)
	{
		$this->dbContext = $context;

		if (!isset(self::$transactionCounter[$dsn = $context->getConnection()->getDsn()])) {
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
		return new EntityCollection($selection, $this->getEntityClass($entity), $refTable, $refColumn);
	}



	/**
	 * @param  string $table
	 * @return NSelection
	 */
	protected function getTable($table = NULL)
	{
		return $this->dbContext->table($this->getTableName($table));
	}



	/**
	 * @param  string $name
	 * @return bool
	 */
	private function parseName(& $name)
	{
		if (!($m = NStrings::match(static::getReflection()->name, '#([a-z0-9]+)repository$#i'))) {
			return FALSE;
		}

		$name = ucfirst($m[1]);
		return TRUE;
	}



	/**
	 * @param  string|NULL $table
	 * @return string
	 */
	private function getTableName($table)
	{
		if ($table === NULL) {
			if ($this->table === NULL) {
				if (($name = static::getReflection()->getAnnotation('table')) !== NULL) {
					$this->table = $name;

				} elseif (!$this->parseName($name)) {
					throw new Exception\InvalidStateException("Table name not set.");
				}

				$this->table = strtolower($name);
			}

			$table = $this->table;
		}

		return $table;
	}



	/**
	 * @param  string $entity
	 * @return string
	 */
	private function getEntityClass($entity = NULL)
	{
		if ($entity === NULL) {
			if ($this->entity === NULL) {
				$ref = static::getReflection();
				if (($name = $ref->getAnnotation('entity')) !== NULL) {
					$this->entity = Aliaser::getClass($name, $ref);

				} elseif ($this->parseName($name)) {
					$this->entity = $name;

				} else {
					throw new Exception\InvalidStateException('Entity class not set.');
				}
			}

			$entity = $this->entity;
		}

		return $entity;
	}



	/**
	 * @param  Entity $entity
	 * @return int
	 */
	function persist(Entity $entity)
	{
		$this->checkEntity($entity);

		try {
			$this->begin();

				$row = $entity->toRow();
				if ($row->hasNative()) {
					$rows = $row->update();

				} else {
					$inserted = $this->getTable()->insert($row->getModified());
					$row->setNative($inserted);
					$rows = 1;
				}

			$this->commit();

		} catch (\Exception $e) {
			$this->rollback();
			throw $e;
		}

		return $rows;
	}



	/**
	 * @param  Entity $entity
	 * @return int
	 */
	function delete(Entity $entity)
	{
		$this->checkEntity($entity);
		$row = $entity->toRow();

		if ($row->hasNative()) {
			try {
				$this->begin();
					$rows = $row->getNative()->delete();
				$this->commit();

			} catch (\Exception $e) {
				$this->rollback();
				throw $e;
			}

		} else {
			$rows = 1;
		}

		return $rows;
	}



	/** @return void */
	private function checkEntity(Entity $entity)
	{
		$class = $this->getEntityClass(NULL);
		if (!($entity instanceof $class)) {
			throw new Exception\InvalidArgumentException("Instance of '$class' expected, '"
				. get_class($entity) . "' given.");
		}
	}



	// === TRANSACTIONS ====================================================

	/** @return void */
	final protected function begin()
	{
		if (self::$transactionCounter[$this->dbContext->getConnection()->getDsn()]++ === 0) {
			$this->dbContext->beginTransaction();
		}
	}



	/** @return void */
	final protected function commit()
	{
		if (self::$transactionCounter[$dsn = $this->dbContext->getConnection()->getDsn()] === 0) {
			throw new Exception\InvalidStateException('No transaction started.');
		}

		if (--self::$transactionCounter[$dsn] === 0) {
			$this->dbContext->commit();
		}
	}



	/** @return void */
	final protected function rollback()
	{
		$this->dbContext->rollBack();
		self::$transactionCounter[$this->dbContext->getConnection()->getDsn()] = 0;
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
