<?php
/**
 * Help command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Help command - output the help view.
 */
class Command_Svnstash_Help extends Command
{
	/**
	 * Run the help command.
	 *
	 * @return void
	 */
	public function run()
	{
		View::factory('help')->render();
	}
}