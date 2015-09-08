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
	public function __construct(NActiveRow $row = NULL)
	{
		$this->row = $row;
	}


	/**
	 * @param  NActiveRow|Record $row
	 * @return Record
	 */
	public static function create($row = NULL)
	{
		if ($row === NULL || $row instanceof NActiveRow) {
			return new static($row);

		} elseif ($row instanceof Record) {
			return $row;

		} else {
			throw new Exception\InvalidArgumentException("Instance of 'Nette\Database\Table\ActiveRow' or 'YetORM\Record' expected, '" . get_class($row) . "' given.");
		}
	}


	/** @return bool */
	public function hasRow()
	{
		return $this->row !== NULL;
	}


	/** @return NActiveRow|NULL */
	public function getRow()
	{
		return $this->row;
	}


	/**
	 * @param  NActiveRow $row
	 * @return Record
	 */
	public function setRow(NActiveRow $row)
	{
		$this->reload($row);
		return $this;
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 * @return Record|NULL
	 */
	public function ref($key, $throughColumn = NULL)
	{
		$this->checkRow();
		$native = $this->row->ref($key, $throughColumn);
		return $native instanceof NActiveRow ? new static($native) : NULL;
	}


	/**
	 * @param  string $key
	 * @param  string $throughColumn
	 * @return NGroupedSelection
	 */
	public function related($key, $throughColumn = NULL)
	{
		$this->checkRow();
		return $this->row->related($key, $throughColumn);
	}


	/** @return array */
	public function getModified()
	{
		return $this->modified;
	}


	/** @return bool */
	public function update()
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
	public function & __get($name)
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

		$native = $this->row->$name;
		$value = $this->values[$name] = $native instanceof NActiveRow ? new static($native) : $native;

		return $value;
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->modified[$name] = $value;
	}


	/**
	 * @param  string $name
	 * @return bool
	 */
	public function __isset($name)
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
