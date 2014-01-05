<?php

namespace Model\Entities;

use YetORM;
use Nette\DateTime;
use Nette\Database\Table\ActiveRow as NActiveRow;


/**
 * @property-read int $id
 * @property string $bookTitle -> book_title
 * @property DateTime|NULL $written
 * @property bool $available
 */
class Book extends YetORM\Entity
{

	/** @var Tag[] */
	protected $addedTags = array();

	/** @var Tag[] */
	protected $removedTags = array();

	/** @var string */
	private $imageDir;



	/**
	 * @param  NActiveRow $row
	 * @param  string $imageDir
	 */
	function __construct(NActiveRow $row = NULL, $imageDir = NULL)
	{
		parent::__construct($row);

		$this->imageDir = realpath($imageDir);

		if ($this->imageDir === FALSE || $imageDir === NULL) {
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
		return new Author($this->row->author);
	}



	/**
	 * @param  Author
	 * @return Book
	 */
	function setAuthor(Author $author)
	{
		$this->row->author_id = $author->id;
		return $this;
	}



	/** @return YetORM\EntityCollection */
	function getTags()
	{
		return $this->getMany('Model\Entities\Tag', 'book_tag', 'tag');
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
