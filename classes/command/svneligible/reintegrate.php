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

		// Don't forget that argument 0 is the command).
		if (! (bool) $upstreamPath = CLI::getUnnamedArgument(1)) {
			$upstream = new Upstream('.');
			$upstreamPath = $upstream->getUpstream($svn->relativePath());
		}

		if (! (bool) $upstreamPath) {
			throw new Exception('You must specify a parent branch to merge into.');
		}

		echo $upstreamPath, PHP_EOL;

		$showCommand = Command_Svneligible::factory('show');
		$showCommand->run();
	}
}