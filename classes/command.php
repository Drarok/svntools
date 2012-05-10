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
	 * @param object $args Arguments to pass to the Command instance.
	 *
	 * @return object
	 */
	public static function factory($name, Arguments $args)
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
	 * @param object $args Command arguments as an instance of Arguments.
	 */
	public function __construct(Arguments $args)
	{
		$this->_args = $args;
	}

	/**
	 * Abstract method that all commands must implement to do their work.
	 *
	 * @return void
	 */
	abstract public function run();
}