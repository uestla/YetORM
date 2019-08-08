<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM;

use Nette\Database\Connection as NdbConnection;


class Transaction
{

	/** @var NdbConnection */
	private $connection;

	/** @var array */
	private static $transactionCounter = [];


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


	public function begin(): void
	{
		if (self::$transactionCounter[$this->getDsnKey()]++ === 0) {
			$this->connection->beginTransaction();
		}
	}


	public function commit(): void
	{
		if (self::$transactionCounter[$dsn = $this->getDsnKey()] === 0) {
			throw new Exception\InvalidStateException('No transaction started.');
		}

		if (--self::$transactionCounter[$dsn] === 0) {
			$this->connection->commit();
		}
	}


	public function rollback(): void
	{
		if (self::$transactionCounter[$dsn = $this->getDsnKey()] !== 0) {
			$this->connection->rollBack();
		}

		self::$transactionCounter[$dsn] = 0;
	}


	private function getDsnKey(): string
	{
		return sha1($this->connection->getDsn());
	}

}
