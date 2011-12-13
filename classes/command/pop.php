<?php
/**
 * Pop command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Pop command - apply, then drop a stash.
 */
class Command_Pop extends Command
{
	/**
	 * Run the pop command.
	 *
	 * @return void
	 */
	public function run()
	{
		Command::factory('apply')->run();
		Command::factory('drop')->run();
	}
}