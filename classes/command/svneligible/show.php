<?php

/**
 * Show the revisions that are eligible for merging.
 *
 * Shows all eligible revisions, and optionally their log messages.
 */
class Command_Svneligible_Show extends Command_Svneligible_Filter
{
	/**
	 * Automatically update the working copy, if configured to do so.
	 *
	 * @return void
	 */
	protected function _setUp()
	{
		// Should we run update first?
		if (! $this->_args->getNamedArgument('no-update')) {
			if ($this->_svn->isDirty()) {
				throw new Exception('Refusing to update due to uncommitted changes.');
			}
			echo 'Updating...', PHP_EOL;
			$this->_svn->update(Config::get('svneligible.show.update-parameters', array()));
		}
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

		$viewName = $this->_args->getNamedArgument('format', 'default');
		$view = View::factory('svneligible/log/' . $viewName);
		$view->showPaths = $showPaths;

		foreach ($logs as $rev => $log) {
			$view->rev = $rev;
			$view->log = $log;
			$view->render();
		}

	}
}