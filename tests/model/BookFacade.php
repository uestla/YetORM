<?php

use YetORM\EntityCollection as EC;


class BookFacade
{

	/** @var BookRepository */
	protected $repository;



	/** @param  BookRepository */
	function __construct(BookRepository $repository)
	{
		$this->repository = $repository;
	}



	/** @return YetORM\EntityCollection */
	function getLatest()
	{
		return $this->repository->findAll()
				->orderBy('written', EC::DESC)
				->limit(3);
	}

}
