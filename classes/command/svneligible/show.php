<?php

/**
 * Show the revisions that are eligible for merging.
 *
 * Shows all eligible revisions, and optionally their log messages.
 */
class Command_Svneligible_Show extends Command_Svneligible_Filter
{
	protected function _setUp()
	{
		// Should we run update first?
		if (Config::get('svneligible.show.auto-update', false)) {
			if ($this->_isWorkingCopyDirty()) {
				throw new Exception('Refusing to update due to uncommitted changes.');
			}
			echo 'Updating...', PHP_EOL;
			$this->_svn->update();
		}
	}

	/**
	 * Check the state of the working copy.
	 *
	 * Returns true if there are modified or missing files.
	 *
	 * @return boolean
	 */
	protected function _isWorkingCopyDirty()
	{

		$entries = $this->_svn->status()->getEntriesInStates(
			Svn_Entry::MODIFIED, Svn_Entry::MISSING);

		return (bool) $entries->count();
	}

	/**
	 * Show the revisions in $revs.
	 *
	 * @param array $revs Filtered revisions to show.
	 *
	 * @return void
	 */
	protected function _run($revs)
	{
		// Should we list the affected paths?
		$showPaths = $this->_args->getNamedArgument('paths', false);

		// There are revs, grab the log messages.
		$logs = $this->_svn->log('^/', $revs, $showPaths);

		ksort($logs);

		foreach ($logs as $rev => $log) {
			echo '    r', $rev, PHP_EOL;

			echo '        ', str_pad($log->author, 10), ' | ', $log->date, PHP_EOL;
			foreach (explode(PHP_EOL, trim($log->msg)) as $line) {
				echo '        ', $line, PHP_EOL;
			}
			echo PHP_EOL;

			if ($showPaths) {
				foreach ($log->paths as $path) {
					echo '        ', $path->action, ' ', $path->path, PHP_EOL;
				}
			}

			echo PHP_EOL;
		}

	}
}