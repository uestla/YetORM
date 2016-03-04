<?php

namespace Model\Repositories;

use YetORM;
use Model\Entities\Book;
use Nette\Database\Context as NdbContext;
use Nette\Database\Table\ActiveRow as NActiveRow;


/**
 * Books repository
 *
 * @author © 2014 kesspess
 *
 * @table  book
 * @entity Book
 * @method Book|NULL getByBookTitle(string $title)
 * @method YetORM\EntityCollection|Book[] findByAvailable(bool $available)
 */
class BookRepository extends YetORM\Repository
{

	/** @var array */
	public $onMyEvent = [];

	/** @var string */
	private $imageDir;


	/**
	 * @param  NdbContext $context
	 * @param  string $imageDir
	 */
	public function __construct(NdbContext $context, $imageDir)
	{
		parent::__construct($context);

		$realpath = realpath($imageDir);

		if ($realpath === FALSE || !is_dir($realpath)) {
			throw new \InvalidArgumentException;
		}

		$this->imageDir = $realpath;
	}


	/**
	 * @param  Book $book
	 * @return bool
	 */
	public function persist(YetORM\Entity $book)
	{
		return $this->transaction(function () use ($book) {

			$book->onPersist($book);
			$status = parent::persist($book);

			// persist tags
			if (count($book->getAddedTags()) || count($book->getRemovedTags())) {
				$tags = $this->getTable('tag')->fetchPairs('name', 'id');
				foreach ($book->getAddedTags() as $tag) {
					if (!isset($tags[$tag->name])) {
						$tags[$tag->name] = $tagID = $this->getTable('tag')->insert([
							'name' => $tag->name,
						])->id;
					}

					$this->getTable('book_tag')->insert([
						'book_id' => $book->id,
						'tag_id' => $tags[$tag->name],
					]);
				}

				$toDelete = [];
				foreach ($book->getRemovedTags() as $tag) {
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

			return $status;

		});
	}


	/** @return YetORM\EntityCollection|Book[] */
	public function findByTag($name)
	{
		return $this->findBy([
			':book_tag.tag.name' => $name,
		]);
	}


	/**
	 * @param  NActiveRow|Record $row
	 * @return Book
	 */
	public function createEntity($row = NULL)
	{
		return new Book($this->imageDir, $row);
	}


	/** @return string */
	public function getImageDir()
	{
		return $this->imageDir;
	}


	/**
	 * @param  \Exception $e
	 * @return void
	 */
	protected function handleException(\Exception $e)
	{
		if ($e instanceof \Nette\Database\UniqueConstraintViolationException) {
			throw new DuplicateEntryException;
		}
	}

}


class DuplicateEntryException extends \Exception
{}
