<?php
/**
 * Show command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Show command - show the changes stored in a stash.
 */
class Command_Svnstash_Show extends Command
{
	/**
	 * Run the show command.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		// Grab the id from the command line.
		$id = $this->_args->getUnnamedArgument(1, null);
		
		// Find its path.
		$path = $stash->getStashPath($id);
		
		// Output a header.
		$name = basename($path, '.diff');
		echo 'Stash \'', $name, '\':', PHP_EOL, PHP_EOL;
		
		// Output the diff.
		echo file_get_contents($path), PHP_EOL;
	}
}