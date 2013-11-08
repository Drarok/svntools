<?php
/**
 * Delete a branch and switch to its upstream.
 */
class Command_Svneligible_Abandon extends Command_Svneligible
{
	/**
	 * This is the main entrypoint into the command.
	 *
	 * @return void
	 */
	public function run()
	{
		// Grab the current branch name.
		$abandonBranchPath = $this->_svn->relativePath();

		// Get the upstream path.
		if (! (bool) $upstreamPath = $this->_getUpstreamPath()) {
			throw new Exception('Cannot abandon branches with no upstream set.');
		}

		// Switch first.
		echo 'Switching to ', $upstreamPath, '...', PHP_EOL;
		$this->_svn->switchTo($upstreamPath);

		// Now delete the branch we were on.
		echo 'Removing abandoned branch ', $abandonBranchPath, '...', PHP_EOL;
		$commitMessage = sprintf('Deleting abandoned branch \'%s\', returning to \'%s\'',
			$abandonBranchPath, $upstreamPath);
		$this->_svn->rm($abandonBranchPath, $commitMessage);

		// Also remove the upstream entry for the now-deleted branch.
		echo 'Removing upstream for path ', $abandonBranchPath, ' (was ', $upstreamPath, ')', PHP_EOL;
		$upstream = new Upstream('.');
		$upstream->removeUpstream($abandonBranchPath);
	}
}