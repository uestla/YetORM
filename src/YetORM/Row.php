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
use Nette\Database\Table\ActiveRow as NActiveRow;
use Nette\Database\Table\GroupedSelection as NGroupedSelection;


class Row
{

	/** @var NActiveRow */
	protected $row;

	/** @var array */
	private $values = array();

	/** @var array */
	private $modified = array();



	/** @param  NActiveRow|NULL */
	function __construct(NActiveRow $row = NULL)
	{
		$this->row = $row;
	}



	/** @return bool */
	function hasNative()
	{
		return $this->row !== NULL;
	}



	/** @return NActiveRow|NULL */
	function getNative()
	{
		return $this->row;
	}



	/**
	 * @param  NActiveRow
	 * @return Row
	 */
	function setNative(NActiveRow $row)
	{
		$this->row = $row;
		return $this;
	}



	/**
	 * @param  string
	 * @param  string
	 * @return NActiveRow|NULL
	 */
	function ref($key, $throughColumn = NULL)
	{
		$this->checkPersistence();
		return $this->row->ref($key, $throughColumn);
	}



	/**
	 * @param  string
	 * @param  string
	 * @return NGroupedSelection
	 */
	function related($key, $throughColumn = NULL)
	{
		$this->checkPersistence();
		return $this->row->related($key, $throughColumn);
	}



	/** @return array */
	function getModified()
	{
		return $this->modified;
	}



	/**
	 * @param  array
	 * @return int
	 */
	function update(array $data = NULL)
	{
		$this->checkPersistence();

		$cnt = 0;
		$data === NULL && ($data = $this->modified);
		if (count($data)) {
			foreach ($data as $key => $val) {
				$this->row->$key = $val;
			}

			$cnt = $this->row->update($data);

			$table = clone $this->row->getTable();
			$refreshed = $table->select('*')->find($this->row->getPrimary())->fetch();
			foreach ($refreshed->toArray() as $key => $val) {
				$this->$key = $val;
			}
		}

		$this->values = $data;
		$this->modified = array();

		return $cnt;
	}



	/**
	 * @param  string
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

		$value = $this->row === NULL ? NULL : ($this->values[$name] = $this->row->$name);
		return $value;
	}



	/**
	 * @param  string
	 * @param  mixed
	 * @return void
	 */
	function __set($name, $value)
	{
		$this->modified[$name] = $value;

		if ($this->row !== NULL) {
			$this->row->$name = $value;
		}
	}



	/**
	 * @param  string
	 * @return bool
	 */
	function __isset($name)
	{
		return array_key_exists($name, $this->modified)
			|| array_key_exists($name, $this->values)
			|| isset($this->row->$name);
	}



	/** @return void */
	private function checkPersistence()
	{
		if ($this->row === NULL) {
			throw new Nette\InvalidStateException("Row not set yet.");
		}
	}

}
