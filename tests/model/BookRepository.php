<?php

namespace Model\Repositories;

use YetORM;
use Model\Entities\Book;
use Nette\Database\Connection as NConnection;
use Nette\Database\Table\ActiveRow as NActiveRow;


/** @entity Book */
class BookRepository extends YetORM\Repository
{

	/** @var string */
	private $imageDir;



	/**
	 * @param  NConnection $connection
	 * @param  string $imageDir
	 */
	function __construct(NConnection $connection, $imageDir)
	{
		parent::__construct($connection);

		$realpath = realpath($imageDir);

		if ($realpath === FALSE || !is_dir($realpath)) {
			throw new \InvalidArgumentException;
		}

		$this->imageDir = $realpath;
	}



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



	/** @return Book|NULL */
	function findById($id)
	{
		$row = $this->getTable()->get($id);
		return $row === FALSE ? NULL : $this->createBook($this->getTable()->get($id));
	}



	/** @return YetORM\EntityCollection */
	function findByTag($name)
	{
		return $this->createCollection($this->getTable()->where('book_tag:tag.name', $name), $this->createBook);
	}



	/** @return YetORM\EntityCollection */
	function findAll()
	{
		return $this->createCollection($this->getTable(), $this->createBook);
	}



	/**
	 * @param  NActiveRow $row
	 * @return Book
	 */
	function createBook(NActiveRow $row = NULL)
	{
		return new Book($row, $this->imageDir);
	}



	/** @return string */
	function getImageDir()
	{
		return $this->imageDir;
	}

}
