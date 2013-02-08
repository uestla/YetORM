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
	}



	/** @return NSelection */
	protected function getTable()
	{
		if ($this->table === NULL) {
			if (!$this->parseName($name)) {
				throw new Nette\InvalidStateException("Table name not set.");
			}

			$this->table = strtolower($name);
		}

		return $this->connection->table( $this->table );
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

}
