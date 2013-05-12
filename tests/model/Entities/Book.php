<?php


/**
 * @property-read int $id
 * @property string $bookTitle -> book_title
 * @property Nette\DateTime|NULL $written
 * @property bool $available
 */
class Book extends YetORM\Entity
{

	/** @var Tag[] */
	protected $addedTags = array();

	/** @var Tag[] */
	protected $removedTags = array();



	/**
	 * @param  string
	 * @return Book
	 */
	function addTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->addedTags[] = $tag;
		return $this;
	}



	/**
	 * @param  bool
	 * @return Tag[]
	 * @internal
	 */
	function getAddedTags($reset = FALSE)
	{
		$tmp = $this->addedTags;
		$reset && ($this->addedTags = array());
		return $tmp;
	}



	/**
	 * @param  string
	 * @return Book
	 */
	function removeTag($name)
	{
		$tag = new Tag;
		$tag->name = $name;
		$this->removedTags[] = $tag;
		return $this;
	}



	/**
	 * @param  bool
	 * @return Tag[]
	 * @internal
	 */
	function getRemovedTags($reset = FALSE)
	{
		$tmp = $this->removedTags;
		$reset && ($this->removedTags = array());
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
		$this->row->author_id = $author->getId();
		return $this;
	}



	/** @return YetORM\EntityCollection */
	function getTags()
	{
		return $this->getMany('Tag', 'book_tag', 'tag');
	}



	/** @return array */
	function toArray()
	{
		$return = parent::toArray();
		$return['author'] = $this->getAuthor()->toArray();

		$return['tags'] = array();
		foreach ($this->getTags() as $tag) {
			$return['tags'][] = $tag->getName();
		}

		return $return;
	}

}
