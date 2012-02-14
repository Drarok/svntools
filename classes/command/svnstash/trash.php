<?php
/**
 * Trash command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Trash command - show (or delete) the contents of the trash.
 */
class Command_Svnstash_Trash extends Command
{
	/**
	 * Run the trash despatcher.
	 *
	 * @return void
	 */
	public function run()
	{
		$valid_subcommands = array('show', 'empty', 'restore');
		
		$subcommand = CLI::getUnnamedArgument(1, 'show');
		
		if (! in_array($subcommand, $valid_subcommands)) {
			throw new Exception('Invalid trash subcommand: ' . $subcommand);
		}
		
		// Prefix the subcommand and execute.
		$method = '_' . $subcommand;
		$this->$method();
	}
	
	/**
	 * Show the contents of the trash.
	 *
	 * @return void
	 */
	protected function _show()
	{
		$stash = new Stash(getcwd());
		
		foreach ($stash->getTrash() as $index => $filename) {
			echo $index, ': ', $filename, PHP_EOL;
		}
	}
	
	/**
	 *
	 */
	protected function _empty()
	{
		// Allow the user to bail.
		if (! CLI::getNamedArgument('force', false)) {
			echo 'Are you sure you want to empty the trash [y/N]? ';
			
			$input = strtolower(trim(fgets(STDIN)));
			
			if ($input !== 'y') {
				exit;
			}
		}
		
		$stash = new Stash(getcwd());
		
		foreach ($stash->getTrash() as $name) {
			$name = $stash->eraseFromTrash($name);
			echo 'Erased stash \'', $name, '\' from trash.', PHP_EOL;
		}
	}
	
	protected function _restore()
	{
		// Start from index 2, as 0 is the command, and 1 the subcommand.
		$index = 2;
		$count = 0;
		
		$stash = new Stash(getcwd());
		
		while (! is_null($id = CLI::getUnnamedArgument($index++))) {
			$name = $stash->restoreFromTrash($id);
			echo 'Restored stash \'', $name, '\' from trash.', PHP_EOL;
			$count++;
		}
		
		// Let the user know how to use the feature.
		if (! $count) {
			echo 'You must pass filenames or ids to trash-restore.', PHP_EOL;
		}
	}
}
