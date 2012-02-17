<?php

/**
 * Show the revisions that are eligible for merging.
 * 
 * Shows all eligible revisions, and optionally their log messages.
 */
class Command_Svneligible_Show extends Command_Svneligible
{
	/**
	 * Command runner - does the actual work.
	 * 
	 * @return void
	 */
	public function run()
	{
		if (CLI::getNamedArgument('stable')) {
			// The --stable flag means to check against the 'newest' release branch.
			$svn = new Svn('.');
			$releases = $svn->ls('^/releases');
			natsort($releases);
			$path = '^/releases/' . array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$path = CLI::getUnnamedArgument(1);
		}

		if (! $path) {
			echo 'You must specify a path to use the \'show\' command.', PHP_EOL;
			exit(1);
		}

		echo $path, PHP_EOL;

		// Is the user after the log messages?
		$showLog = CLI::getNamedArgument('show-log', false);

		$svn = new Svn('.');

		$eligible = $svn->eligible($path);

		if (! (bool) $eligible) {
			echo '    No eligible revisions.', PHP_EOL;
		} else {
			// There are revs, grab the log messages if required.
			if ($showLog) {
				$logs = $svn->log('^/', $eligible);
			} else {
				$logs = array_fill_keys($eligible, null);
			}

			ksort($logs);
			foreach ($logs as $rev => $log) {
				echo '    r', $rev, PHP_EOL;
				
				if ($log) {
					echo '        ', str_pad($log->author, 10), ' | ', $log->date, PHP_EOL;
					foreach (explode(PHP_EOL, trim($log->msg)) as $line) {
						echo '        ', $line, PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}
	}
}