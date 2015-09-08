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
use Nette\Utils\Callback as NCallback;
use Nette\Database\Table\Selection as NSelection;


class EntityCollection extends Nette\Object implements \Iterator, \Countable
{

	/** @var NSelection */
	protected $selection;

	/** @var string|NCallback */
	protected $entity;

	/** @var string|NULL */
	protected $refTable;

	/** @var string|NULL */
	protected $refColumn;

	/** @var Entity[] */
	protected $data = NULL;

	/** @var int */
	private $count = NULL;

	/** @var array */
	private $keys;


	const ASC = FALSE;
	const DESC = TRUE;


	/**
	 * @param  NSelection $selection
	 * @param  string|callable $entity
	 * @param  string $refTable
	 * @param  string $refColumn
	 */
	public function __construct(NSelection $selection, $entity, $refTable = NULL, $refColumn = NULL)
	{
		$this->selection = $selection;
		$this->refTable = $refTable;
		$this->refColumn = $refColumn;

		try {
			NCallback::check($entity);
			$this->entity = NCallback::closure($entity);

		} catch (\Exception $e) {
			$this->entity = $entity;
		}
	}


	/** @return void */
	private function loadData()
	{
		if ($this->data === NULL) {
			if ($this->entity instanceof \Closure) {
				$factory = $this->entity;

			} else {
				$class = $this->entity;
				$factory = function ($record) use ($class) {
					return new $class($record);
				};
			}

			$this->data = array();
			foreach ($this->selection as $row) {
				$record = $this->refTable === NULL ? $row : $row->ref($this->refTable, $this->refColumn);
				$this->data[] = NCallback::invoke($factory, $record);
			}
		}
	}


	/** @return array */
	public function toArray()
	{
		return iterator_to_array($this);
	}


	/**
	 * API:
	 *
	 * <code>
	 * $this->orderBy('column', EntityCollection::DESC); // ORDER BY [column] DESC
	 * // or
	 * $this->orderBy(array(
	 *	'first'  => EntityCollection::ASC,
	 *	'second' => EntityCollection::DESC,
	 * ); // ORDER BY [first], [second] DESC
	 * </code>
	 *
	 * @param  string|array $column
	 * @param  bool $dir
	 * @return EntityCollection
	 */
	public function orderBy($column, $dir = NULL)
	{
		if (is_array($column)) {
			foreach ($column as $col => $d) {
				$this->orderBy($col, $d);
			}

		} else {
			$dir === NULL && ($dir = static::ASC);
			$this->selection->order($column . ($dir === static::DESC ? ' DESC' : ''));
		}

		$this->invalidate();
		return $this;
	}


	/**
	 * @param  int $limit
	 * @param  int $offset
	 * @return EntityCollection
	 */
	public function limit($limit, $offset = NULL)
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
	public function rewind()
	{
		$this->loadData();
		$this->keys = array_keys($this->data);
		reset($this->keys);
	}


	/** @return Entity */
	public function current()
	{
		$key = current($this->keys);
		return $key === FALSE ? FALSE : $this->data[$key];
	}


	/** @return mixed */
	public function key()
	{
		return current($this->keys);
	}


	/** @return void */
	public function next()
	{
		next($this->keys);
	}


	/** @return bool */
	public function valid()
	{
		return current($this->keys) !== FALSE;
	}


	// === interface \Countable ======================================

	/** @return int */
	public function count()
	{
		if ($this->data !== NULL) {
			return count($this->data);
		}

		if ($this->count === NULL) {
			$this->count = $this->selection->count('*');
		}

		return $this->count;
	}

}
