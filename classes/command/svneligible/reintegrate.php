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
		// Grab the relative path, as we need it in various places.
		$relativePath = $this->_svn->relativePath();

		if ($this->_args->getNamedArgument('stable')) {
			$releases = Command_Svneligible::factory('releases')->run(false);
			$upstreamPath = array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$upstreamPath = $this->_args->getUnnamedArgument(1);
		}

		if (! (bool) $upstreamPath) {
			// No upstreamPath specified on the command line, is there one stored?
			$upstream = new Upstream('.');
			$upstreamPath = $upstream->getUpstream($relativePath);
		}

		if (! (bool) $upstreamPath) {
			throw new Exception('You must specify a parent branch to merge into.');
		}

		// Ensure there are no uncommitted changes.
		if ($this->_svn->isDirty()) {
			throw new Exception('You have uncommitted changes. Aborting.');
		}

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