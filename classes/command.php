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
	 *
	 * @return object
	 */
	public static function factory($name)
	{
		$prefix = get_called_class() . '_';
		$className = $prefix . ucfirst($name);
		return new $className();
	}

	/**
	 * Abstract method that all commands must implement to do their work.
	 *
	 * @return void
	 */
	abstract public function run();
}