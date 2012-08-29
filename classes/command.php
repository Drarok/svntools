<?php
/**
 * Commands.
 *
 * @category Commands
 * @package  svntools
 */

/**
 * Command base class.
 *
 * All commands must extend this class. Add any gloablly-useful methods here.
 */
abstract class Command
{
	/**
	 * Factory method for command subclass instances.
	 *
	 * @param string $name Name of the command to instantiate.
	 * @param mixed  $args Arguments instance, or null.
	 *
	 * @return object
	 */
	public static function factory($name, $args = null)
	{
		$prefix = get_called_class() . '_';
		$className = $prefix . ucfirst($name);
		return new $className($args);
	}

	/**
	 * Command arguments as an instance of Arguments.
	 *
	 * @var object
	 */
	protected $_args;

	/**
	 * Constructor.
	 *
	 * @param mixed $args Arguments instance, or null.
	 */
	public function __construct($args = null)
	{
		if ($args === null) {
			// If no arguments passed in, create an empty instance.
			$args = new Arguments(array());
		}

		$this->_args = $args;
	}

	/**
	 * Get the display name of the current instance.
	 *
	 * @return string
	 */
	public function getName()
	{
		$segments = explode('_', get_class($this));
		return strtolower(array_pop($segments));
	}

	/**
	 * Abstract method that all commands must implement to do their work.
	 *
	 * @return void
	 */
	abstract public function run();
}