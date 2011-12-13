<?php
/**
 * Commands.
 *
 * @category Commands
 * @package  svnstash
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
	 * @return Command
	 */
	public static function factory($name)
	{
		$className = 'Command_' . ucfirst($name);
		return new $className();
	}
	
	/**
	 * Abstract method that all commands must implement to do their work.
	 *
	 * @return void
	 */
	abstract public function run();
}