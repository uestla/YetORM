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
use Nette\Database\Table\ActiveRow as NActiveRow;
use Nette\Database\Table\GroupedSelection as NGroupedSelection;


class Record
{

	/** @var NActiveRow */
	private $row;

	/** @var array */
	private $values = array();

	/** @var array */
	private $modified = array();


	/** @param  NActiveRow $row */
	function __construct(NActiveRow $row = NULL)
	{
		$this->row = $row;
	}


	/** @return bool */
	function hasRow()
	{
		return $this->row !== NULL;
	}


	/** @return NActiveRow|NULL */
	function getRow()
	{
		return $this->row;
	}


	/**
	 * @param  NActiveRow $row
	 * @return Record
	 */
	function setRow(NActiveRow $row)
	{
		$this->reload($row);
		return $this;
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 * @return NActiveRow|NULL
	 */
	function ref($key, $throughColumn = NULL)
	{
		$this->checkRow();
		return $this->row->ref($key, $throughColumn);
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 * @return NGroupedSelection
	 */
	function related($key, $throughColumn = NULL)
	{
		$this->checkRow();
		return $this->row->related($key, $throughColumn);
	}


	/** @return array */
	function getModified()
	{
		return $this->modified;
	}


	/** @return bool */
	function update()
	{
		$this->checkRow();

		$status = TRUE;
		if (!$this->isPersisted()) {
			$status = $this->row->update($this->modified);
			$this->reload($this->row);
		}

		return $status;
	}


	/**
	 * @param  string $name
	 * @return mixed
	 */
	function & __get($name)
	{
		if (array_key_exists($name, $this->modified)) {
			return $this->modified[$name];
		}

		if (array_key_exists($name, $this->values)) {
			return $this->values[$name];
		}

		if ($this->row === NULL) {
			throw new Exception\MemberAccessException("The value of column '$name' not set.");
		}

		$value = $this->values[$name] = $this->row->$name;
		return $value;
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	function __set($name, $value)
	{
		$this->modified[$name] = $value;
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	function __isset($name)
	{
		return isset($this->modified[$name])
			|| isset($this->values[$name])
			|| isset($this->row->$name);
	}


	/** @return bool */
	private function isPersisted()
	{
		return $this->hasRow() && !count($this->modified);
	}


	/** @return void */
	private function checkRow()
	{
		if (!$this->hasRow()) {
			throw new Exception\InvalidStateException('Row not set yet.');
		}
	}


	/**
	 * @param  NActiveRow $row
	 * @return void
	 */
	private function reload(NActiveRow $row)
	{
		$this->row = $row;
		$this->modified = $this->values = array();
	}

}
