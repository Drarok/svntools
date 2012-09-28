<?php

/**
 * Display the commit log.
 *
 * Allows different formatting of the log output.
 */
class Command_Svneligible_Log extends Command_Svneligible
{
	/**
	 * This is the main entrypoint into the command.
	 *
	 * @return void
	 */
	public function run()
	{
		// Does the user want the log for a specific path?
		$path = $this->_args->getUnnamedArgument(1);

		// How about a specific range?
		$revisions = $this->_args->getNamedArgument('range');

		// Do they want to see the paths?
		$showPaths = $this->_args->getNamedArgument('paths');

		// Get the logs.
		$logs = $this->_svn->log($path, $revisions, $showPaths);

		// Set up the view.
		$viewName = $this->_args->getNamedArgument('format', 'default');
		$view = View::factory('svneligible/log/' . $viewName);
		$view->showPaths = false;

		// Output each revision.
		foreach ($logs as $rev => $log) {
			$view->rev = $rev;
			$view->log = $log;
			$view->render();
		}
	}
}