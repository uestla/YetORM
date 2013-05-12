<?php


class BookRepository extends YetORM\Repository
{

	/**
	 * @param  Book
	 * @return int
	 */
	function persist(YetORM\Entity $book)
	{
		$this->begin();

			$cnt = parent::persist($book);

			// persist tags
			if (count($book->getAddedTags()) || count($book->getRemovedTags())) {

				$tags = $this->getTable('tag')->fetchPairs('name', 'id');
				foreach ($book->getAddedTags(TRUE) as $tag) {
					if (!isset($tags[$tag->name])) {
						$tags[$tag->name] = $tagID = $this->getTable('tag')->insert(array(
							'name' => $tag->name,
						))->id;
					}

					$this->getTable('book_tag')->insert(array(
						'book_id' => $book->id,
						'tag_id' => $tags[$tag->name],
					));
				}

				$toDelete = array();
				foreach ($book->getRemovedTags(TRUE) as $tag) {
					if (isset($tags[$tag->name])) {
						$toDelete[] = $tags[$tag->name];
					}
				}

				if (count($toDelete)) {
					$this->getTable('book_tag')
							->where('book_id', $book->id)
							->where('tag_id', $toDelete)
							->delete();
				}
			}

		$this->commit();

		return $cnt;
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
