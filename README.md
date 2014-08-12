YetORM
======

Lightweight ORM built on top of Nette\Database


Quickstart
----------

Consider following database schema:

![Database schema](http://i.imgur.com/EtR1bM4.png)

### Entities

Firstly we'll create entity classes according to the schema above. There are two ways of defining entity properties - via `@property[-read]` annotation, or simply via getter and setter.

#### Tag

```php
/** @property string $name */
class Tag extends YetORM\Entity
{}
```

#### Author

```php
/**
 * @property string $name
 * @property string $web
 * @property \DateTime $born
 */
class Author extends YetORM\Entity
{}
```

#### Book

There are some relations at the `Book` entity - two N:1 `Author` and M:N `Tag` relations. Every `YetORM\Entity` has an instance of `YetORM\Record` in it, which is a simple wrapper around `Nette\Database\Table\ActiveRow`. That means that we can access related records or column values through it.

```php
/**
 * @property string $title
 * @property string $web
 * @property string $slogan
 */
class Book extends YetORM\Entity
{
	function getAuthor()
	{
		return new Author($this->record->ref('author', 'author_id'));
	}

	function getMaintainer()
	{
		return new Author($this->record->ref('author', 'maintainer_id'));
	}

	function getTags()
	{
		$selection = $this->record->related('book_tag');
		return new YetORM\EntityCollection($selection, 'Tag', 'tag');
	}
}
```

With `$record->ref($table, $column)` we're accessing related table row in table `$table` through column `$column` - pretty simple.

The M:N relation is realized with `YetORM\EntityCollection` instance - which is a lazy collection of entities. In this case it iterates throw all related rows from `book_tag` table (first argument), creates instances of `Tag` (second argument) and on every related `book_tag` table row it accesses related `tag` table row, which then passes to the constructor of `Tag` entity :-)

This sounds crazy, but it's actually simple to get used to.

With this knowledge we can now simply add some helpful methods to `Author` entity:

```php
// class Author
function getBooksWritten()
{
	$selection = $this->record->related('book', 'author_id');
	return new YetORM\EntityCollection($selection, 'Book');
}

function getBooksMaintained()
{
	$selection = $this->record->related('book', 'maintainer_id');
	return new YetORM\EntityCollection($selection, 'Book');
}
```


### Repositories

Every repository has to have table and entity class name defined - either via `@table` and `@entity` annotaion, or via protected `$table` and `$entity` class property.

```php
/**
 * @table  book
 * @entity Book
 */
class BookRepository extends YetORM\Repository
{}
```

Now we can simply iterate through all books

```php
$books = new BookRepository($connection); // $connection instanceof Nette\Database\Context

foreach ($books->findAll() as $book) { // $book instanceof Book
	echo $book->title;
	echo $book->getAuthor()->name;
	foreach ($book->getTags() as $tag) { // $tag instanceof Tag
		echo $tag->name;
	}
}
```

And that's it!


More
----

For more examples please see [tests](https://github.com/uestla/YetORM/tree/master/tests).
