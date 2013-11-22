<?php
/**
 * Automatically reintegrate a branch into its parent branch.
 */
class Command_Svneligible_Reintegrate extends Command_Svneligible
{
	/**
	 * This is the main entrypoint into the command.
	 *
	 * @return void
	 */
	public function run()
	{
		// Ensure there are no uncommitted changes.
		if ($this->_svn->isDirty()) {
			throw new Exception('You have uncommitted changes. Aborting.');
		}

		// Grab the relative path, as we need it in various places.
		$relativePath = $this->_svn->relativePath();

		// Use the parent class method to get the upstream.
		$upstreamPath = $this->_getPath();

		// Check that there are no eligible revisions.
		if ((bool) $eligible = $this->_svn->eligible($upstreamPath)) {
			$eligibleCount = count($eligible);
			$word = $eligibleCount == 1
				? 'is'
				: 'are';
			throw new Exception('There ' . $word . ' ' . $eligibleCount
				. ' eligible revisions still to merge. Aborting.');
		}

		echo 'Reintegrating \'', $relativePath, '\', into \'', $upstreamPath, '\'', PHP_EOL;

		// Switch the working copy.
		$this->_svn->switchTo($upstreamPath);

		// Grab the commit messages that are eligible from the branch into the upstream.
		$revsToMerge = false;
		if ($this->_args->getNamedArgument('auto', false)) {
			$revsToMerge = $this->_svn->eligible($relativePath);
		}

		// Merge in the revisions.
		$this->_svn->merge($relativePath, null, null, true);

		if (! (bool) $this->_args->getNamedArgument('no-commit')) {
			echo 'Committing...', PHP_EOL;

			if ((bool) $commitMessage = $this->_args->getNamedArgument('commit')) {
				$this->_svn->commit($commitMessage);
			} elseif ($this->_args->getNamedArgument('auto', false)) {
				$commitMessage = $this->_getCommitMessage($relativePath, $upstreamPath, $revsToMerge);
				$this->_svn->commit($commitMessage);
			} else {
				$this->_svn->commit();
			}

			if (! (bool) $this->_args->getNamedArgument('no-remove')) {
				// Delete the now-reintegrated branch.
				echo 'Automatically removing the reintegrated branch.', PHP_EOL;
				$this->_svn->rm($relativePath, 'Removing now-reintegrated branch');

				// Also remove any upstream entries for the now-deleted branch.
				$upstream = new Upstream('.');
				if ((bool) $upstreamPath = $upstream->getUpstream($relativePath)) {
					echo 'Removing upstream for path ', $relativePath, ' (was ', $upstreamPath, ')', PHP_EOL;
					$upstream->removeUpstream($relativePath);
				}
			}
		}
	}

	/**
	 * Get the commit message, including all the merged revisions' messages.
	 *
	 * @param string $mergedFrom Branch that we merged from.
	 * @param string $mergedTo   Branch that we merged into.
	 * @param array  $revisions  Array of revision numbers that were merged.
	 *
	 * @return string
	 */
	protected function _getCommitMessage($mergedFrom, $mergedTo, $revisions)
	{
		// Start off the commit message.
		$commitMessage = sprintf('Automated reintegration of \'%s\' into \'%s\'.',
			$mergedFrom, $mergedTo) . PHP_EOL . PHP_EOL;

		// Include the merged commits messages.
		$logs = $this->_svn->log($mergedFrom, $revisions);

		// Set up the view.
		$view = View::factory('svneligible/log/default');

		// Add each revision to the commit message.
		foreach ($logs as $rev => $log) {
			$view->rev = $rev;
			$view->log = $log;
			$commitMessage .= $view->render(false);
		}

		return $commitMessage;
	}
}