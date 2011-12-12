<?php

/**
 * Command-line helper.
 *
 */
abstract class Cli
{
	/**
	 * Short => long argument map.
	 *
	 * @var array
	 */
	protected static $_map = array();
	
	protected static $_namedArguments = array();
	
	protected static $_unnamedArguments = array();
	
	public static function init(array $map = array())
	{
		self::$_map = $map;
		self::_parse();
	}
	
	public static function getNamedArgument($name, $default = null)
	{
		return array_key_exists($name, self::$_namedArguments)
			? self::$_namedArguments[$name]
			: $default;
	}
	
	public static function getUnnamedArgument($index = null, $default = null)
	{
		if ($index === null) {
			// Return all arguments if no index passed.
			return self::$_unnamedArguments;
		} else {
			// Return the unnamed argument if found, default otherwise.
			return array_key_exists($index, self::$_unnamedArguments)
				? self::$_unnamedArguments[$index]
				: $default;
		}
	}
	
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
	
	protected static function _addShortArgument($arg)
	{
		if (! array_key_exists($arg, self::$_map)) {
			// Give up if we don't recognise the option.
			return false;
		}
		
		// Pass to _addLongArgument for processing.
		self::_addLongArgument(self::$_map[$arg]);
	}
	
	protected static function _addUnnamedArgument($arg)
	{
		self::$_unnamedArguments[] = $arg;
	}
}

Cli::init(array(
	'v' => 'verbose',
	'u' => 'untracked-files',
	'f' => 'force',
));