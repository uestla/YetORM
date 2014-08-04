<?php

namespace Model\Entities;

use YetORM;
use Nette\Utils\DateTime;
use Nette\Database\Table\ActiveRow as NActiveRow;


/**
 * @property-read int $id
 * @property string $bookTitle -> book_title
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
	 * @param  NActiveRow|YetORM\Record $row
	 * @param  string $imageDir
	 */
	function __construct($row = NULL, $imageDir = NULL)
	{
		parent::__construct($row);

		if ($imageDir === NULL || ($this->imageDir = realpath($imageDir)) === FALSE) {
			throw new \InvalidArgumentException;
		}
	}


	/**
	 * @param  string $name
	 * @return Book
	 */
	function addTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->addedTags[] = $tag;
		return $this;
	}


	/** @return Tag[] @internal */
	function getAddedTags()
	{
		$tmp = $this->addedTags;
		return $tmp;
	}


	/**
	 * @param  string $name
	 * @return Book
	 */
	function removeTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->removedTags[] = $tag;
		return $this;
	}


	/** @return Tag[] @internal */
	function getRemovedTags()
	{
		$tmp = $this->removedTags;
		return $tmp;
	}


	/** @return Author */
	function getAuthor()
	{
		return new Author($this->record->author);
	}


	/**
	 * @param  Author $author
	 * @return Book
	 */
	function setAuthor(Author $author)
	{
		$this->record->author_id = $author->id;
		return $this;
	}


	/** @return YetORM\EntityCollection */
	function getTags()
	{
		$selection = $this->record->related('book_tag');
		return new YetORM\EntityCollection($selection, 'Model\Entities\Tag', 'tag');
	}


	/** @return string @internal */
	function getImagePath()
	{
		return $this->imageDir . '/' . $this->id . '.jpg';
	}


	/** @return array */
	function toArray()
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
