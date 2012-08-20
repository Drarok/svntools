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
		if ($this->_args->getNamedArgument('stable')) {
			// The --stable flag means to fetch the 'newest' release branch.
			$releases = Command_Svneligible::factory('releases')->run(false);
			$path = array_pop($releases);
		} else {
			$path = $this->_svn->relativePath();
		}

		echo $path, PHP_EOL;
	}
}