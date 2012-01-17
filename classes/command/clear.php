<?php
/**
 * Clear command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Clear command - remove all stashed changes.
 */
class Command_Clear extends Command
{
	/**
	 * Run the clear command.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		
		if (! CLI::getNamedArgument('force', false)) {
			echo 'Are you sure you want to remove all stashes [y/N]? ';
			
			$input = strtolower(trim(fgets(STDIN)));
			
			if ($input !== 'y') {
				exit;
			}
		}
		
		$stash->removeAllStashes();
	}
}