<?php
/**
 * Save command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * Save command.
 *
 * Save modifications (and optionally untracked files) to a new stash, optionally
 * reverting the working copy in the process.
 */
class Command_Svnstash_Save extends Command
{
	/**
	 * Run the save command.
	 *
	 * @return void
	 */
	public function run()
	{
		$stashName = $this->_args->getUnnamedArgument(1);

		if (! (bool) $stashName) {
			echo 'You must supply a stash name.', PHP_EOL;
			exit(1);
		}

		$path = getcwd();
		$stash = new Stash($path);

		$includeUntracked = (bool) $this->_args->getNamedArgument('untracked-files');

		$stash->addStash($stashName, $includeUntracked, $this->_args->getNamedArgument('revert', FALSE));

		echo 'Created new stash: ', $stashName, PHP_EOL;
	}
}