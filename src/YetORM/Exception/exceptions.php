<?php

/**
 * This file is part of the YetORM package
 *
 * @license  MIT
 * @author   Petr Kessler (https://kesspess.cz)
 * @link     https://github.com/uestla/YetORM
 */

namespace YetORM\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{}


class InvalidStateException extends \RuntimeException
{}


class InvalidPropertyDefinitionException extends InvalidStateException
{}


class MemberAccessException extends \LogicException
{}


class NotSupportedException extends \LogicException
{}
