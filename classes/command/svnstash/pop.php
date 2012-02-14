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
class Command_Svnstash_Pop extends Command
{
	/**
	 * Run the pop command.
	 *
	 * @return void
	 */
	public function run()
	{
		Command_Svnstash::factory('apply')->run();
		Command_Svnstash::factory('drop')->run();
	}
}