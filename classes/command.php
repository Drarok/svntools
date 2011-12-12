<?php

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
	 */
	abstract public function run();
}