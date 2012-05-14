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
		// Note that this command passes its arguments instance directly into the other commands.
		Command_Svnstash::factory('apply', $this->_args)->run();
		Command_Svnstash::factory('drop', $this->_args)->run();
	}
}