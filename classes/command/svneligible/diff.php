<?php
/**
 * Perform a diff between the current checked-out branch and another.
 *
 * If no other branch is given, upstreams are checked.
 */
class Command_Svneligible_Diff extends Command_Svneligible
{
	/**
	 * Run the diff command.
	 *
	 * @return void
	 */
	public function run()
	{
		$svn = new Svn(Svn::getRoot('.'));

		$branchPath = $svn->relativePath();

		if ($this->_args->getNamedArgument('stable')) {
			$releases = Command_Svneligible::factory('releases')->run(false);
			$upstreamPath = array_pop($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$upstreamPath = $this->_args->getUnnamedArgument(1);
		}

		if (! (bool) $upstreamPath) {
			// Still no path. Is there an upstream set?
			$upstream = new Upstream('.');
			$upstreamPath = $upstream->getUpstream($branchPath);
		}

		if (! (bool) $upstreamPath) {
			throw new Exception('You must specify a branch to compare with.');
		}

		echo $svn->diff($upstreamPath, $branchPath);
	}
}