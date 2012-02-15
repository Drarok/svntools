<?php

/**
 * Implementation of the 'svnbase' command.
 *
 * This command simply wraps the command-line 'svn' tool, after ensuring that
 * the current working directory is that of the working copy root.
 */
class Command_Svnbase extends Command
{
	/**
	 * Command runner.
	 * 
	 * @return void
	 */
	public function run()
	{
		echo 'lulz', PHP_EOL;
	}
}