<?php

/**
 * Path command for svneligible, shows the current relative path.
 */
class Command_Svneligible_Path extends Command_Svneligible
{
	/**
	 * Output the relative path.
	 *
	 * @return void
	 */
	public function run()
	{
		echo $this->_svn->relativePath(), PHP_EOL;
	}
}