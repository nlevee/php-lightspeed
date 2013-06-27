<?php
/**
 * @author: Nicolas Levée
 * @version 160520131310
 */

namespace Lightspeed;


/**
 * Class Exception
 * @package Lightspeed
 */
interface Exception {}

/**
 * Class DomainException
 * @package Lightspeed
 */
class RuntimeException
	extends \RuntimeException
	implements Exception
{}

/**
 * Class DomainException
 * @package Lightspeed
 */
class DomainException
	extends \DomainException
	implements Exception
{}

/**
 * Class InvalidArgumentException
 * @package Lightspeed
 */
class InvalidArgumentException
	extends \InvalidArgumentException
	implements Exception
{}

/**
 * Class UnexpectedValueException
 * @package Lightspeed
 */
class UnexpectedValueException
	extends \UnexpectedValueException
	implements Exception
{}

/**
 * Class OutOfBoundsException
 * @package Lightspeed
 */
class OutOfBoundsException
	extends \OutOfBoundsException
	implements Exception
{}

/**
 * Class BadMethodCallException
 * @package Lightspeed
 */
class BadMethodCallException
	extends \BadMethodCallException
	implements Exception
{}