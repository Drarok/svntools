<?php
/**
 * Drop command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Drop command - remove a single stash.
 */
class Command_Svnstash_Drop extends Command
{
	/**
	 * Run the drop command.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		// Grab the id from the command line.
		$id = CLI::getUnnamedArgument(1, null);
		
		// Remove it.
		$name = $stash->removeStash($id);
		
		echo 'Removed stash \'', $name, '\'.', PHP_EOL;
	}
}