<?php
/**
 * Command-line helper.
 *
 * @category Helpers
 * @package  svnstash
 */

/**
 * Command-line helper.
 *
 * This class eases the use of command-line options.
 */
abstract class CLI
{
	/**
	 * Short => long argument map.
	 *
	 * @var array
	 */
	protected static $_map = array();
	
	/**
	 * Cached named arguments.
	 *
	 * @var array
	 */
	protected static $_namedArguments = array();
	
	/**
	 * Cached unnamed arguments.
	 *
	 * @var array
	 */
	protected static $_unnamedArguments = array();
	
	/**
	 * Initialisation method.
	 *
	 * @param array $map Short => long argument name map.
	 *
	 * @return void
	 */
	public static function init(array $map = array())
	{
		self::$_map = $map;
		self::_parse();
	}
	
	/**
	 * Fetch a named argument's value, or the passed default.
	 *
	 * @param string $name    Name of the argument to fetch.
	 * @param mixed  $default Default value if the argument isn't found.
	 *
	 * @return mixed
	 */
	public static function getNamedArgument($name, $default = null)
	{
		return array_key_exists($name, self::$_namedArguments)
			? self::$_namedArguments[$name]
			: $default;
	}
	
	/**
	 * Fetch an unnamed argument's value, or the passed default.
	 *
	 * @param int   $index   Index of the argument to fetch.
	 * @param mixed $default Default value if the argument isn't found.
	 *
	 * @return mixed
	 */
	public static function getUnnamedArgument($index, $default = null)
	{
		// Return the unnamed argument if found, default otherwise.
		return array_key_exists($index, self::$_unnamedArguments)
			? self::$_unnamedArguments[$index]
			: $default;
	}
	
	/**
	 * Parse the command-line arguments into the cache arrays.
	 *
	 * @return void
	 */
	protected static function _parse()
	{
		foreach (array_slice($_SERVER['argv'], 1) as $arg) {
			if (substr($arg, 0, 2) == '--') {
				self::_addLongArgument(substr($arg, 2));
			} elseif (substr($arg, 0, 1) == '-') {
				self::_addShortArgument(substr($arg, 1));
			} else {
				self::_addUnnamedArgument($arg);
			}
		}
	}
	
	/**
	 * Add a long-style argument to the cache array.
	 *
	 * @param string $arg Raw argument from the command-line.
	 *
	 * @return void
	 */
	protected static function _addLongArgument($arg)
	{
		// Attempt to split on an equals sign.
		$parts = explode('=', $arg, 2);
		
		// Use the first part as the argument name.
		$name = $parts[0];
		
		if (count($parts) == 1) {
			// Use a true value if there's no equals sign.
			$value = true;
		} else {
			// Use the specified value otherwise.
			$value = $parts[1];
		}
		
		// Store the configured argument.
		self::$_namedArguments[$name] = $value;
	}
	
	/**
	 * Add a short-style argument to the cache array.
	 *
	 * @param string $arg Raw argument from the command-line.
	 *
	 * @return void
	 */
	protected static function _addShortArgument($arg)
	{
		if (! array_key_exists($arg, self::$_map)) {
			// Give up if we don't recognise the option.
			return;
		}
		
		// Pass to _addLongArgument for processing.
		self::_addLongArgument(self::$_map[$arg]);
	}
	
	/**
	 * Add an unnamed argument to the cache array.
	 *
	 * @param string $arg Raw argument from the command-line.
	 *
	 * @return void
	 */
	protected static function _addUnnamedArgument($arg)
	{
		self::$_unnamedArguments[] = $arg;
	}
}