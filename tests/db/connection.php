<?php


/** @return Nette\Database\Connection */
function getConnection()
{
	static $connection;

	if ($connection === NULL) {
		$connection = new Nette\Database\Connection('mysql:host=localhost;dbname=repository_test', 'root', '');
		Nette\Database\Helpers::loadFromFile($connection, __DIR__ . '/db.sql');
	}

	return $connection;
}
