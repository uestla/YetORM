<?php


class BookRepository extends YetORM\Repository
{

	/**
	 * @param  Book
	 * @return int
	 */
	function persist(Book $book)
	{
		$this->begin();

			$row = $book->toRow();
			if ($row->hasNative()) {
				$rows = $book->toRow()->update();

			} else {
				$inserted = $this->getTable()->insert($row->getModified());
				$refreshed = $this->getTable()->select('*')->get($inserted->getPrimary());

				$book->refresh($refreshed);
				$rows = 1;
			}

		$this->commit();

		return $rows;
	}



	/**
	 * @param  Book
	 * @return int
	 */
	function delete(Book $book)
	{
		$this->begin();

			$row = $book->toRow();
			$rows = 1;
			$row->hasNative() && ($rows = $row->getNative()->delete());

		$this->commit();

		return $rows;
	}



	/** @return Book */
	function findById($id)
	{
		return new Book($this->getTable()->get($id));
	}



	/** @return YetORM\EntityCollection */
	function findByTag($name)
	{
		return $this->createCollection($this->getTable()->where('book_tag:tag.name', $name));
	}



	/** @return YetORM\EntityCollection */
	function findAll()
	{
		return $this->createCollection($this->getTable());
	}

}
