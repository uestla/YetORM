<?php

namespace Model\Services;

use Model;
use YetORM\EntityCollection as EC;


class BookService
{

	/** @var Model\Repositories\BookRepository */
	protected $repository;



	/** @param  Model\Repositories\BookRepository */
	function __construct(Model\Repositories\BookRepository $repository)
	{
		$this->repository = $repository;
	}



	/** @return YetORM\EntityCollection */
	function getLatest()
	{
		return $this->repository->getAll()
				->orderBy('written', EC::DESC)
				->limit(3);
	}

}
