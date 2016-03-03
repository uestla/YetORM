<?php

namespace Model\Entities;

use YetORM;
use Nette\Utils\DateTime;
use Nette\Database\Table\ActiveRow as NActiveRow;


/**
 * @property-read int $id
 * @property string $bookTitle -> book_title Title   of	the		book
 * @property DateTime|NULL $written
 * @property bool $available
 */
class Book extends BaseEntity
{

	/** @var array */
	public $onPersist = array();

	/** @var Tag[] */
	private $addedTags = array();

	/** @var Tag[] */
	private $removedTags = array();

	/** @var string */
	private $imageDir;


	/**
	 * @param  string $imageDir
	 * @param  NActiveRow|YetORM\Record $row
	 */
	public function __construct($imageDir, $row = NULL)
	{
		parent::__construct($row);

		if (($this->imageDir = realpath($imageDir)) === FALSE) {
			throw new \InvalidArgumentException;
		}
	}


	/**
	 * @param  string $name
	 * @return Book
	 */
	public function addTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->addedTags[] = $tag;
		return $this;
	}


	/** @return Tag[] @internal */
	public function getAddedTags()
	{
		$tmp = $this->addedTags;
		return $tmp;
	}


	/**
	 * @param  string $name
	 * @return Book
	 */
	public function removeTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->removedTags[] = $tag;
		return $this;
	}


	/** @return Tag[] @internal */
	public function getRemovedTags()
	{
		$tmp = $this->removedTags;
		return $tmp;
	}


	/**
	 * Returns author of the book.
	 *
	 * What a useful method!
	 * Love it <3
	 *
	 * @todo just for description test purposes
	 * @return Author
	 */
	public function getAuthor()
	{
		return new Author($this->record->author);
	}


	/**
	 * @param  Author $author
	 * @return Book
	 */
	public function setAuthor(Author $author)
	{
		$this->record->author_id = $author->id;
		return $this;
	}


	/**
	 *
	 *
	 * @return YetORM\EntityCollection
	 */
	public function getTags()
	{
		$selection = $this->record->related('book_tag', 'book_id');
		return new YetORM\EntityCollection($selection, 'Model\Entities\Tag', 'tag');
	}


	/** @return string @internal */
	public function getImagePath()
	{
		return $this->imageDir . '/' . $this->id . '.jpg';
	}


	/** @return array */
	public function toArray()
	{
		$return = parent::toArray();
		$return['author'] = $this->getAuthor()->toArray();

		$return['tags'] = array();
		foreach ($this->getTags() as $tag) {
			$return['tags'][] = $tag->name;
		}

		return $return;
	}

}
