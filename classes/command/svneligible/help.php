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
		View::factory('svneligible/help')->render();
	}
}