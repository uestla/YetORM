<?php

/**
 * This file is part of the YetORM library
 *
 * Copyright (c) 2013 Petr Kessler (http://kesspess.1991.cz)
 *
 * @license  MIT
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette;
use Nette\Utils\Strings as NStrings;
use Nette\Database\Connection as NConnection;
use Nette\Database\Table\Selection as NSelection;


abstract class Repository extends Nette\Object
{

	/** @var array */
	private static $transactionCounter = array();

	/** @var NConnection */
	protected $connection;

	/** @var string */
	protected $table = NULL;

	/** @var string */
	protected $entity = NULL;



	/** @param  NConnection */
	function __construct(NConnection $connection)
	{
		$this->connection = $connection;

		if (!isset(self::$transactionCounter[$dsn = $connection->dsn])) {
			self::$transactionCounter[$dsn] = 0;
		}
	}



	/**
	 * @param  NSelection
	 * @param  string|NULL
	 * @param  string|NULL
	 * @param  string|NULL
	 * @return EntityCollection
	 */
	protected function createCollection($selection, $entity = NULL, $refTable = NULL, $refColumn = NULL)
	{
		return new EntityCollection($selection, $this->getEntityClass($entity), $refTable, $refColumn);
	}



	/**
	 * @param  string|NULL
	 * @return NSelection
	 */
	protected function getTable($table = NULL)
	{
		return $this->connection->table($this->getTableName($table));
	}



	/**
	 * @param  string
	 * @return bool
	 */
	private function parseName(& $name)
	{
		if (!($m = NStrings::match($this->reflection->name, '#([a-z0-9]+)repository$#i'))) {
			return FALSE;
		}

		$name = $m[1];
		return TRUE;
	}



	/**
	 * @param  string|NULL
	 * @return string
	 */
	private function getTableName($table)
	{
		if ($table === NULL) {
			if ($this->table === NULL) {
				if (($name = static::getReflection()->getAnnotation('table')) !== NULL) {
					$this->table = $name;

				} elseif (!$this->parseName($name)) {
					throw new Nette\InvalidStateException("Table name not set.");
				}

				$this->table = strtolower($name);
			}

			$table = $this->table;
		}

		return $table;
	}



	/**
	 * @param  string|NULL
	 * @return string
	 */
	private function getEntityClass($entity)
	{
		if ($entity === NULL) {
			if ($this->entity === NULL) {
				if (($name = static::getReflection()->getAnnotation('entity')) !== NULL) {
					$this->entity = $name;

				} elseif (!$this->parseName($name)) {
					throw new Nette\InvalidStateException("Entity class not set.");
				}

				$this->entity = ucfirst($name);
			}

			$entity = $this->entity;
		}

		return $entity;
	}



	// === TRANSACTIONS ====================================================

	/** @return void */
	final protected function begin()
	{
		if (self::$transactionCounter[$this->connection->dsn]++ === 0) {
			$this->connection->beginTransaction();
		}
	}



	/** @return void */
	final protected function commit()
	{
		if (!isset(self::$transactionCounter[$dsn = $this->connection->dsn]) || self::$transactionCounter[$dsn] === 0) {
			throw new Nette\InvalidStateException("No transaction started.");
		}

		if (--self::$transactionCounter[$dsn] === 0) {
			$this->connection->commit();
		}
	}



	/** @return void */
	final protected function rollback()
	{
		$this->connection->rollBack();
		self::$transactionCounter[$this->connection->dsn] = 0;
	}

}
