<?php


/** @return Nette\Database\Connection */
function getConnection()
{
	static $connection;

	if ($connection === NULL) {
		$connection = new Nette\Database\Connection('mysql:host=localhost;dbname=repository_test', 'root', '');
	}

	return $connection;
}
