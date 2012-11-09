<?php
/**
 * Switch implementation, adding support for --stable.
 *
 * Switches the working copy to another branch, always from the base of the
 * working copy, never allowing subtree switching.
 */
class Command_Svneligible_Switch extends Command_Svneligible
{
	public function run()
	{
		// Get the path to switch to.
		$path = false;

		if ($this->_args->getNamedArgument('stable')) {
			// The --stable flag means to check against the 'newest' release branch.
			$releases = Command_Svneligible::factory('releases')->run(false);
			$path = array_pop($releases);
			unset($releases);
		} else {
			// Don't forget that argument 0 is the command.
			$path = $this->_args->getUnnamedArgument(1);
		}

		if (! $path) {
			// There's still no path. Look for an upstream.
			$upstream = new Upstream('.');
			$path = $upstream->getUpstream($this->_svn->relativePath());
		}

		if (! $path) {
			throw new Exception('You must specify a path to use the \'' . $this->getName() . '\' command.');
		}

		// Push the current branch onto the stack?
		if ($this->_args->getNamedArgument('push')) {
			$stack = new BranchStack(Svn::getRoot('.'));
			$this->_svn->relativePath();
		}

		// All the other commands show the branch, so this one will, too.
		echo $path, PHP_EOL;

		// Note that we *always* operate on the root of the working copy.
		$this->_svn->switchTo($path);
	}
}