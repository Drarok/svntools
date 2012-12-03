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

		echo 'Reintegrating into ', $upstreamPath, PHP_EOL;

		$this->_svn->switchTo($upstreamPath);
		$this->_svn->merge($relativePath, null, null, true);

		if (! (bool) $this->_args->getNamedArgument('no-commit')) {
			echo 'Committing...', PHP_EOL;

			if ((bool) $commitMessage = $this->_args->getNamedArgument('commit')) {
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
}