<?php
/**
 * Error / exception handler functions.
 *
 * @category General
 * @package  svnstash
 */

/**
 * Helper class to wrap the exception handler.
 *
 * Add an error handler here if you find the need.
 */
abstract class Handler
{
	/**
	 * Whether we shold output extra debugging info or not.
	 *
	 * @var bool
	 */
	static protected $_debug = FALSE;

	/**
	 * Simple exception handler that just outputs the error message.
	 *
	 * @param Exception $exception Thrown exception.
	 *
	 * @return void
	 */
	public static function exceptionHandler($exception)
	{
		if (! static::$_debug) {
			echo 'Error: ', $exception->getMessage(), PHP_EOL;
		} else {
			echo $exception, PHP_EOL;
		}
	}

	/**
	 * Setter for the debug property.
	 *
	 * @param bool $debug Pass true to enable debugging.
	 */
	public static function setDebug($debug)
	{
		static::$_debug = (bool) $debug;
	}
}