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
		$svn = new Svn(Svn::getRoot('.'));

		// Grab the relative path, as we need it in various places.
		$relativePath = $svn->relativePath();

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
		$workingCopyIsDirty = (bool) $svn->status()
			->getEntriesInStates(Svn_Entry::MODIFIED, Svn_Entry::MISSING)
			->count();

		if ($workingCopyIsDirty) {
			throw new Exception('You have uncommitted changes. Aborting.');
		}

		// Check that there are no eligible revisions.
		if ((bool) $eligible = $svn->eligible($upstreamPath)) {
			$eligibleCount = count($eligible);
			$word = $eligibleCount == 1
				? 'is'
				: 'are';
			throw new Exception('There ' . $word . ' ' . $eligibleCount
				. ' eligible revisions still to merge. Aborting.');
		}

		echo 'Reintegrating into ', $upstreamPath, PHP_EOL;

		$svn->switchTo($upstreamPath);
		$svn->merge($relativePath, null, null, true);

		if (! (bool) $this->_args->getNamedArgument('no-commit')) {
			echo 'Committing...', PHP_EOL;

			if ((bool) $commitMessage = $this->_args->getNamedArgument('commit')) {
				$svn->commit($commitMessage);
			} else {
				$svn->commit();
			}

			if (! (bool) $this->_args->getNamedArgument('no-remove')) {
				// Delete the now-reintegrated branch.
				echo 'Automatically removing the reintegrated branch.', PHP_EOL;
				$svn->rm($relativePath, 'Removing now-reintegrated branch');

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