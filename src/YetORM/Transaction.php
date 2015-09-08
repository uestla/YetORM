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

use Nette\Database\Connection as NdbConnection;


class Transaction
{

	/** @var NdbConnection */
	private $connection;

	/** @var array */
	private static $transactionCounter = array();


	/** @param  NdbConnection $connection */
	public function __construct(NdbConnection $connection)
	{
		$this->connection = $connection;

		if (!isset(self::$transactionCounter[$dsn = $this->getDsnKey()])) {
			self::$transactionCounter[$dsn] = 0;
		}
	}


	/**
	 * @param  \Closure $callback
	 * @return mixed
	 */
	public function transaction(\Closure $callback)
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


	/** @return void */
	public function begin()
	{
		if (self::$transactionCounter[$this->getDsnKey()]++ === 0) {
			$this->connection->beginTransaction();
		}
	}


	/** @return void */
	public function commit()
	{
		if (self::$transactionCounter[$dsn = $this->getDsnKey()] === 0) {
			throw new Exception\InvalidStateException('No transaction started.');
		}

		if (--self::$transactionCounter[$dsn] === 0) {
			$this->connection->commit();
		}
	}


	/** @return void */
	public function rollback()
	{
		if (self::$transactionCounter[$dsn = $this->getDsnKey()] !== 0) {
			$this->connection->rollBack();
		}

		self::$transactionCounter[$dsn] = 0;
	}


	/** @return string */
	private function getDsnKey()
	{
		return sha1($this->connection->getDsn());
	}

}
