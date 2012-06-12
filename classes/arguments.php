<?php
/**
 * Arguments parsing and access.
 *
 * @category General
 * @package  svntools
 */

/**
 * Arguments class - parsing and access to command-line style arguments.
 */
class Arguments
{
	/**
	 * Array of passed-in arguments.
	 *
	 * @var array
	 */
	protected $_args;

	/**
	 * Parsed 'named' arguments.
	 *
	 * @var array
	 */
	protected $_namedArguments = array();

	/**
	 * Parsed 'unnamed' arguments.
	 *
	 * @var array
	 */
	protected $_unnamedArguments = array();

	/**
	 * Constructor.
	 *
	 * @param array $args    Arguments to parse.
	 * @param array $mapping Short to long name option mapping.
	 */
	public function __construct($args, $mapping = array())
	{
		$this->_parse($args, $mapping);
	}

	/**
	 * Public access to named arguments.
	 *
	 * @param string $key     Name of the argument to fetch.
	 * @param mixed  $default Default value to return if the argument was not passed.
	 *
	 * @return mixed
	 */
	public function getNamedArgument($key, $default = null)
	{
		return array_key_exists($key, $this->_namedArguments)
			? $this->_namedArguments[$key]
			: $default;
	}

	/**
	 * Count the number of named arguments that were parsed.
	 *
	 * @return int
	 */
	public function countNamedArguments()
	{
		return count($this->_namedArguments);
	}

	/**
	 * Public access to unnamed arguments.
	 *
	 * @param int   $index   Index of the argument to fetch the value of.
	 * @param mixed $default Default value to return if the argument was not passed.
	 *
	 * @return mixed
	 */
	public function getUnnamedArgument($index, $default = null)
	{
		return array_key_exists($index, $this->_unnamedArguments)
			? $this->_unnamedArguments[$index]
			: $default;
	}

	/**
	 * Count the number of unnamed arguments that were parsed.
	 *
	 * @return int
	 */
	public function countUnnamedArguments()
	{
		return count($this->_unnamedArguments);
	}

	/**
	 * Parse the named and unnamed arguments out into the relevant arrays.
	 *
	 * @param array $args    Argument array to parse.
	 * @param array $mapping Short to long name option mapping.
	 *
	 * @return void
	 */
	protected function _parse($args, $mapping)
	{
		foreach ($args as $arg) {
			if (substr($arg, 0, 2) == '--') {
				$this->_addLongArgument(substr($arg, 2));
			} elseif (substr($arg, 0, 1) == '-') {
				$this->_addShortArgument(substr($arg, 1), $mapping);
			} else {
				$this->_addUnnamedArgument($arg);
			}
		}
	}

	/**
	 * Add a long-style argument to the array.
	 *
	 * @param string $arg Raw argument string, minus leading hyphens.
	 *
	 * @return void
	 */
	protected function _addLongArgument($arg)
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
		$this->_namedArguments[$name] = $value;
	}

	/**
	 * Add a short-style argument to the array.
	 *
	 * @param string $arg     Raw argument string, minus leading hyphen.
	 * @param array  $mapping Short to long mapping array.
	 *
	 * @return void
	 */
	protected function _addShortArgument($arg, $mapping)
	{
		if (! array_key_exists($arg, $mapping)) {
			// Give up if we don't recognise the option.
			return;
		}

		// Pass to _addLongArgument for processing.
		$this->_addLongArgument($mapping[$arg]);
	}

	/**
	 * Add an unnamed argument to the array.
	 *
	 * @param string $arg Raw argument string.
	 *
	 * @return void
	 */
	protected function _addUnnamedArgument($arg)
	{
		$this->_unnamedArguments[] = $arg;
	}
}