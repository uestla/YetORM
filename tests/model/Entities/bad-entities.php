<?php

namespace Model\Entities;

use YetORM\Entity;


/** @property NULL|NULL $badOne */
class BadDoubleNullEntity extends Entity
{}


/** @property \DateTime|string $evil */
class BadMultipleTypeEntity extends Entity
{}


/** @property string nodollar */
class InvalidPropertyDefinitionEntity extends Entity
{}
