<?php

/**
 * Help command for svneligible.
 */
class Command_Svneligible_Help extends Command_Svneligible
{
	/**
	 * True if this command requires a Subversion working copy to run.
	 *
	 * @var boolean
	 */
	protected $_requiresWorkingCopy = false;

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

		// This is the order we want to show commands in.
		$commands = array(
			'branches',
			'releases',
			'tags',
			'path',
			'upstream',
			'branch',
			'stack',
			'switch',
			'diff',
			'show',
			'merge',
			'reintegrate',
			'log',
			'help',
		);

		// Output the general help if in verbose mode or no specific command.
		if ($verbose || ! $command) {
			View::factory('svneligible/help')
				->set('verbose', $verbose)
				->set('commands', $commands)
				->render();
		}

		if ($verbose && ! $command) {
		} elseif ($command) {
			// Just show a single command's help.
			$commands = array($command);
		} else {
			// Show nothing.
			$commands = array();
		}

		foreach ($commands as $command) {
			if ($command == 'help') {
				// There's no additional help for the help command.
				continue;
			}

			View::factory('svneligible/help/' . $command, 'txt')->render();
			echo PHP_EOL, PHP_EOL;
		}
	}
}