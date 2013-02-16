<?php


class BookRepository extends YetORM\Repository
{

	/**
	 * @param  string
	 * @param  int
	 * @param  string
	 * @param  bool
	 * @param  array
	 * @return Book
	 */
	function create($title, $author, $written, $available = TRUE, array $tags = array())
	{
		$this->begin();

			$row = $this->getTable()->insert(array(
				'author_id' => $author,
				'book_title' => $title,
				'written' => $written,
				'available' => $available,
			));

			$tagMap = $this->getTable('tag')->fetchPairs('name', 'id');
			foreach ($tags as $name) {
				$this->getTable('book_tag')->insert(array(
					'book_id' => $row->id,
					'tag_id' => $tagMap[$name],
				));
			}

		$this->commit();

		return new Book($row);
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
