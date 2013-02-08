<?php


class Tag extends YetORM\Entity
{

	/** @return string */
	function getName()
	{
		return $this->row->name;
	}

}
