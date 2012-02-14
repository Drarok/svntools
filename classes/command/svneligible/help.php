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
		echo 'Usage: svneligible [<options>] [...] [<command>]', PHP_EOL, PHP_EOL;

		$commands_help = array(
			'branches' => '- List the contents of ^/branches (up to 2 levels deep).',
			'releases' => '- List the contents of ^/releases.',
			'tags'     => '- List the contents of ^/tags.',
			'show' => '    - Show the eligible revisions from the given branch/release.',
			'help' => '    - This help text.',
		);

		echo 'Available commands:', PHP_EOL;
		foreach ($commands_help as $command => $help) {
			echo '    ', $command, ' ', $help, PHP_EOL;
		}
		echo PHP_EOL;

		$options_help = array(
			'show-log' => '- Display the commit log for eligible revisions.',
		);

		echo 'Available options:', PHP_EOL;
		foreach ($options_help as $option => $help) {
			echo '    --', $option, ' ', $help, PHP_EOL;
		}
		echo PHP_EOL;

		exit(0);
	}
}