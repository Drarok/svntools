<?php
/**
 * List command.
 *
 * @category Commands
 * @package  svnstash
 */

/**
 * List command - list all stashes.
 */
class Command_Svnstash_List extends Command
{
	/**
	 * Run the list command.
	 *
	 * @return void
	 */
	public function run()
	{
		$path = getcwd();
		$stash = new Stash($path);
		$path = $stash->getPath();
		$stashes = $stash->getStashes();

		if (! $stashes) {
			echo 'There are no stashed changes in ', $path, PHP_EOL;
			exit(1);
		}

		foreach ($stashes as $key => $stashName) {
			$time = filemtime($stash->getStashPath($stashName));
			echo sprintf('%d: [%s] %s', $key, date('Y-m-d H:i:s', $time), $stashName), PHP_EOL;
		}
	}
}