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

		// Don't forget that argument 0 is the command.
		if (! (bool) $upstreamPath = $this->_args->getUnnamedArgument(1)) {
			// No upstreamPath specified on the command line, is there one stored?
			$upstream = new Upstream('.');
			$upstreamPath = $upstream->getUpstream($svn->relativePath());
		}

		if (! (bool) $upstreamPath) {
			throw new Exception('You must specify a parent branch to merge into.');
		}

		// Check that there are no eligible revisions.
		if ((bool) $eligible = $svn->eligible($upstreamPath)) {
			$eligibleCount = count($eligible);
			$word = $eligibleCount == 1
				? 'is'
				: 'are';
			throw new Exception('There ' . $word . ' ' . $eligibleCount . ' eligible revisions still to merge. Aborting.');
		}

		echo 'Reintegrating into ', $upstreamPath, PHP_EOL;
	}
}