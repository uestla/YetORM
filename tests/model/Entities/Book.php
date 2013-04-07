<?php


/**
 * @property-read int $id
 * @property string $bookTitle -> book_title
 * @property DateTime|NULL $written
 * @property bool $available
 */
class Book extends YetORM\Entity
{

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
		$return['author'] = $this->getAuthor()->getName();

		$return['tags'] = array();
		foreach ($this->getTags() as $tag) {
			$return['tags'][] = $tag->getName();
		}

		return $return;
	}

}
