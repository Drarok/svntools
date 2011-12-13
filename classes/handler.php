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
	 * Simple exception handler that just outputs the error message.
	 *
	 * @param Exception $exception Thrown exception.
	 *
	 * @return void
	 */
	public static function exceptionHandler($exception)
	{
		echo 'Error: ', $exception->getMessage(), PHP_EOL;
	}
}