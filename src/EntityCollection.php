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
use Nette\Database\Table\Selection as NSelection;


class EntityCollection extends Nette\Object implements \Iterator, \Countable
{

	/** @var NSelection */
	protected $selection;

	/** @var string */
	protected $entity;

	/** @var string|NULL */
	protected $refTable;

	/** @var string|NULL */
	protected $refColumn;

	/** @var Entity[] */
	protected $data = NULL;

	/** @var array */
	protected $keys;



	/**
	 * @param  NSelection
	 * @param  string
	 * @param  string
	 * @param  string
	 */
	function __construct(NSelection $selection, $entity, $refTable = NULL, $refColumn = NULL)
	{
		$this->entity = $entity;
		$this->selection = $selection;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;
	}



	/** @return void */
	private function loadData()
	{
		if ($this->data === NULL) {
			$this->data = array();
			foreach ($this->selection as $row) {
				if ($this->refTable !== NULL) {
					$row = $this->refColumn !== NULL
						? $row->ref($this->refTable, $this->refColumn)
						: $row->{$this->refTable};
				}

				$class = $this->entity;
				$entity = new $class($row);
				$primary = $row->getPrimary(FALSE);

				if ($primary !== NULL && is_scalar($primary)) {
					$this->data[$primary] = $entity;

				} else {
					$this->data[] = $entity;
				}
			}
		}
	}



	/** @return array */
	function getData()
	{
		$this->loadData();
		return $this->data;
	}



	/**
	 * API:
	 *
	 * <code>
	 * $this->orderBy('column', TRUE); // ORDER BY [column] DESC
	 * // or
	 * $this->orderBy(array(
	 *	'first'  => FALSE,
	 *	'second' => TRUE,
	 * ); // ORDER BY [first], [second] DESC
	 * </code>
	 *
	 * @param  string|array
	 * @param  bool
	 * @return EntityCollection
	 */
	function orderBy($column, $desc = FALSE)
	{
		if (is_array($column)) {
			foreach ($column as $col => $desc) {
				$this->selection->order($col . ($desc ? ' DESC' : ''));
			}

		} else {
			$this->selection->order($column . ($desc ? ' DESC' : ''));
		}

		$this->invalidate();
		return $this;
	}



	/**
	 * @param  int
	 * @param  int|NULL
	 * @return EntityCollection
	 */
	function limit($limit, $offset = NULL)
	{
		$this->selection->limit($limit, $offset);
		$this->invalidate();
		return $this;
	}



	/** @return void */
	private function invalidate()
	{
		$this->data = NULL;
	}



	// === interface \Iterator ======================================

	/** @return void */
	function rewind()
	{
		$this->loadData();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}



	/** @return Entity */
	function current()
	{
		$key = current($this->keys);
		return $this->data[$key];
	}



	/** @return mixed */
	function key()
	{
		return current($this->keys);
	}



	/** @return void */
	function next()
	{
		next($this->keys);
	}



	/** @return bool */
	function valid()
	{
		return current($this->keys) !== FALSE;
	}



	// === interface \Countable ======================================

	/** @return int */
	function count()
	{
		if ($this->data !== NULL) {
			return count($this->data);
		}

		return $this->selection->count('*');
	}

}
