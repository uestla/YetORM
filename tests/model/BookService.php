<?php

namespace Model\Services;

use Model;
use YetORM\EntityCollection as EC;


class BookService
{

	/** @var Model\Repositories\BookRepository */
	private $repository;


	/** @param  Model\Repositories\BookRepository $repository */
	public function __construct(Model\Repositories\BookRepository $repository)
	{
		$this->repository = $repository;
	}


	/** @return YetORM\EntityCollection */
	public function getLatest()
	{
		return $this->repository->findAll()
				->orderBy('written', EC::DESC)
				->limit(3);
	}

}
