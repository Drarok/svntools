<?php

/**
 * Merge the revisions that are eligible.
 *
 * Merges revisions from a given branch into the working copy.
 */
class Command_Svneligible_Merge extends Command_Svneligible_Filter
{
	protected function _setUp()
	{
		// Only merge if the working copy is clean.
		if ($this->_svn->isDirty() &&
			! $this->_args->getNamedArgument('force')
		) {
			throw new Exception('Refusing to merge due to uncommitted changes.');
		}

		// Should we run update?
		if (! $this->_args->getNamedArgument('no-update')) {
			echo 'Updating...', PHP_EOL;
			$this->_svn->update(Config::get('svneligible.merge.update-parameters', array()));
		}
	}

	/**
	 * Merge all revisions in $revs into the working copy.
	 *
	 * @param array $revs Filtered revisions to merge.
	 *
	 * @return void
	 */
	protected function _run($revs)
	{
		// Detect if the user specified a record-only merge.
		$recordOnly = $this->_args->getNamedArgument('record-only', false);

		if ($this->_getAllEligibleRevisions() == $revs) {
			// If nothing was filtered, then allow Subversion to handle the revisions.
			$this->_svn->merge($this->_options->path, null, null, false, $recordOnly);
		} else {
			// Pass the revisions to only merge those specific ones.
			$this->_svn->merge($this->_options->path, $revs, null, false, $recordOnly);
		}

		// Check there are no conflicts. Subversion doesn't report this via exit code.
		if ($this->_svn->status()->getEntriesInStates(Svn_Entry::CONFLICTED)->count()) {
			throw new Exception('There are conflicts you must resolve.');
		}

		// Perform an automatic commit, if requested.
		if ($this->_args->getNamedArgument('auto', false)) {
			$commitMessage = sprintf('Automated merge from \'%s\' into \'%s\'.',
				$this->_getPath(), $this->_svn->relativePath()) . PHP_EOL . PHP_EOL;

			// Include the merged commits messages.
			$logs = $this->_svn->log($this->_options->path, $revs);

			// Set up the view.
			$view = View::factory('svneligible/log/default');

			// Add each revision to the commit message.
			foreach ($logs as $rev => $log) {
				$view->rev = $rev;
				$view->log = $log;
				$commitMessage .= $view->render(false);
			}

			$this->_svn->commit(trim($commitMessage));
		}
	}
}