<?php

/**
 * Help command for svneligible.
 */
class Command_Svneligible_Help extends Command_Svneligible
{
	/**
	 * Help command for svneligible.
	 * 
	 * @return void
	 */
	public function run()
	{
		// Parse options.
		$verbose = $this->_args->getNamedArgument('verbose');
		$command = $this->_args->getUnnamedArgument(1);

		// Output the general help first if in verbose mode or no command.
		if ($verbose || ! $command) {
			View::factory('svneligible/help')
				->set('verbose', $verbose)
				->render();
		}

		if ($verbose && ! $command) {
			// This is the order we want to show commands in verbose mode.
			$commands = array(
				'branches',
				'releases',
				'tags',
				'upstream',
				'show',
				'diff',
				'merge',
				'reintegrate',
				'help',
			);
		} elseif ($command) {
			// Just show a single command's help.
			$commands = array($command);
		} else {
			// Show nothing.
			$commands = array();
		}

		foreach ($commands as $command) {
			View::factory('svneligible/help/' . $command, 'txt')->render();
			echo PHP_EOL, PHP_EOL;
		}
	}
}