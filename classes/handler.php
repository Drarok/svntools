<?php

/**
 * Helper class to wrap the exception handler.
 */
abstract class Handler
{
	public static function exceptionHandler($exception)
	{
		echo 'Error: ', $exception->getMessage(), PHP_EOL;
	}
}