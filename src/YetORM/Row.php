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
use Nette\Database\SelectionFactory;


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
		$this->reload($row);
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



	/** @return int */
	function update()
	{
		$this->checkPersistence();

		$cnt = 0;
		if (count($this->modified)) {
			$cnt = $this->row->update($this->modified);
			$this->reload($this->row);
		}

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

		$value = $this->values[$name] = $this->row === NULL ? NULL : $this->row->$name;
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
			$this->row->update(array());
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
			throw new E\InvalidStateException("Row not set yet.");
		}
	}



	/**
	 * @param  NActiveRow
	 * @return void
	 */
	private function reload(NActiveRow $row)
	{
		// intentionally ugly as hell (looking forward to having stable Nette 2.1)
		$sf = new SelectionFactory($row->getTable()->getConnection());
		$this->row = $sf->table($row->getTable()->getName())
				->select('*')
				->get($row->getPrimary());

		$this->modified = $this->values = array();
	}

}
