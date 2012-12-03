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
		// What are we comparing?
		$branchPath = $this->_svn->relativePath();

		// Get the passed-in path, parsing upstreams etc.
		$upstreamPath = $this->_getPath();

		$params = Config::get('svneligible.diff.additional_parameters', array());

		if ($this->_args->getNamedArgument('reverse')) {
			echo $this->_svn->diff($branchPath, $upstreamPath, $params);
		} else {
			echo $this->_svn->diff($upstreamPath, $branchPath, $params);
		}
	}
}