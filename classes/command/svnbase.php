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
		// Check we have some work to do.
		$args = array_map('escapeshellarg', array_slice($_SERVER['argv'], 1));

		if (! $args) {
			echo 'Usage: svnbase.php <svn_arguments>', PHP_EOL;
			exit(1);
		}

		// Locate the root of the working copy, and change to it.
		$svn = new Svn(getcwd());
		chdir($svn->rootPath());

		array_unshift($args, escapeshellcmd('svn'));

		exec($cmd = implode(' ', $args), $output, $errorCode);

		if ($errorCode !== 0) {
			echo 'Subversion exited with error code: ', $errorCode, PHP_EOL;
			echo 'Command: ', $cmd, PHP_EOL;
		}

		echo implode(PHP_EOL, $output), PHP_EOL;
	}
}