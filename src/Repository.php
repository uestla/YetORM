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
use Nette\Database\Table\ActiveRow as NActiveRow;


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
		if ($entity === NULL) {
			if ($this->entity === NULL) {
				if (!$this->parseName($name)) {
					throw new Nette\InvalidStateException("Entity class not set.");
				}

				$this->entity = ucfirst($name);
			}

			$entity = $this->entity;
		}

		return new EntityCollection($selection, $entity, $refTable, $refColumn);
	}



	/**
	 * @param  string|NULL
	 * @return NSelection
	 */
	protected function getTable($table = NULL)
	{
		if ($table === NULL) {
			if ($this->table === NULL) {
				if (!$this->parseName($name)) {
					throw new Nette\InvalidStateException("Table name not set.");
				}

				$this->table = strtolower($name);
			}

			$table = $this->table;
		}

		return $this->connection->table($table);
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



	// === LOW-END CRUD OPERATIONS ====================================================

	/**
	 * @param  mixed
	 * @param  string
	 * @return NActiveRow
	 */
	protected function insertRow($values, $table = NULL)
	{
		$this->begin();
			$row = $this->getTable($table)->insert($values);
		$this->commit();

		return $row;
	}



	/**
	 * @param  NActiveRow
	 * @param  mixed
	 * @return int
	 */
	protected function updateRow(NActiveRow $row, $values)
	{
		$this->begin();

			foreach ($values as $key => $val) {
				$row->$key = $val;
			}

			$rows = $row->update();

		$this->commit();

		return $rows;
	}



	/**
	 * @param  NActiveRow
	 * @return int
	 */
	protected function deleteRow(NActiveRow $row)
	{
		$this->begin();
			$rows = $row->delete();
		$this->commit();

		return $rows;
	}



	// === TRANSACTIONS ====================================================

	/** @return void */
	final protected function begin()
	{
		if (self::$transactionCounter[$dsn = $this->connection->dsn] === 0) {
			$this->connection->beginTransaction();
		}

		self::$transactionCounter[$dsn]++;
	}



	/** @return void */
	final protected function commit()
	{
		if (!isset(self::$transactionCounter[$dsn = $this->connection->dsn])) {
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
